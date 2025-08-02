<?php

namespace App\Services\Annotation;

use App\Data\Annotation\FE\AnnotationData;
use App\Data\Annotation\FE\DeleteFEData;
use App\Database\Criteria;
use App\Repositories\AnnotationSet;
use App\Repositories\FrameElement;
use App\Repositories\LU;
use App\Repositories\Timeline;
use App\Services\AppService;
use App\Services\CommentService;
use Illuminate\Support\Facades\DB;


class FEService
{
    public static function getASData(int $idAS, string $token = ''): array
    {
        $it = Criteria::table("view_instantiationtype")
            ->where('idLanguage', AppService::getCurrentIdLanguage())
            ->all();
        $as = Criteria::table("view_annotationset")
            ->where('idAnnotationSet', $idAS)
            ->first();
        $sentence = Criteria::table("view_sentence as s")
            ->join("document_sentence as ds", "s.idSentence", "=", "ds.idSentence")
            ->where("ds.idDocumentSentence", $as->idDocumentSentence)
            ->select("s.idSentence", "s.text", "ds.idDocumentSentence", "ds.idDocument")
            ->first();
        $wordsChars = AnnotationSet::getWordsChars($sentence->text);
        foreach ($wordsChars->words as $i => $word) {
            $wordsChars->words[$i]['hasFE'] = false;
        }
        $lu = LU::byId($as->idLU);
        $alternativeLU = Criteria::table("view_lu as lu1")
            ->join("view_lu as lu2", "lu1.idLemma", "=", "lu2.idLemma")
            ->where("lu2.idLU", $lu->idLU)
            ->where("lu1.idLU", "<>", $lu->idLU)
            ->select("lu1.frameName", "lu1.name as lu")
            ->all();
        $fes = Criteria::table("view_frameelement")
            ->where('idLanguage', AppService::getCurrentIdLanguage())
            ->where("idFrame", $lu->idFrame)
            ->orderBy("name")
            ->keyBy("idEntity")
            ->all();
        $fesByType = [
            "Core" => [],
            "Peripheral" => [],
            "Extra-thematic" => [],
        ];
        foreach ($fes as $fe) {
            if (($fe->coreType == "cty_core") || ($fe->coreType == "cty_core-unexpressed")) {
                $fesByType["Core"][] = $fe;
            } else if ($fe->coreType == "cty_peripheral") {
                $fesByType["Peripheral"][] = $fe;
            } else {
                $fesByType["Extra-thematic"][] = $fe;
            }
        }
        $layers = AnnotationSet::getLayers($idAS);
        $target = array_filter($layers, fn($x) => ($x->layerTypeEntry == 'lty_target'));
        foreach ($target as $tg) {
            $tg->startWord = $wordsChars->chars[$tg->startChar]['order'];
            $tg->endWord = $wordsChars->chars[$tg->endChar]['order'];
        }
        $feSpans = array_filter($layers, fn($x) => $x->layerTypeEntry == 'lty_fe');
        $spans = [];
        $nis = [];
        $idLayers = [];
        $firstWord = array_key_first($wordsChars->words);
        $lastWord = array_key_last($wordsChars->words);
        $spansByLayer = collect($feSpans)->groupBy('idLayer')->all();
//        debug($fes);
        foreach ($spansByLayer as $idLayer => $existingSpans) {
            $idLayers[] = $idLayer;
            for ($i = $firstWord; $i <= $lastWord; $i++) {
                $spans[$i][$idLayer] = null;
            }
            foreach ($existingSpans as $span) {
                if ($span->idTextSpan != '') {
                    $span->startWord = ($span->startChar != -1) ? $wordsChars->chars[$span->startChar]['order'] : -1;
                    $span->endWord = ($span->endChar != -1) ? $wordsChars->chars[$span->endChar]['order'] : -1;
                    if ($span->layerTypeEntry == 'lty_fe') {
                        if ($span->startWord != -1) {
                            $hasLabel = false;
                            for ($i = $span->startWord; $i <= $span->endWord; $i++) {
                                $name = (!$hasLabel) ? $fes[$span->idEntity]->name : null;
                                $spans[$i][$idLayer] = [
                                    'idEntityFE' => $span->idEntity,
                                    'label' => $name
                                ];
                                $wordsChars->words[$i]['hasFE'] = true;
                                $hasLabel = true;
                            }
                        } else {
                            $name = $fes[$span->idEntity]->name;
                            $nis[$span->idInstantiationType][$span->idEntity] = [
                                'idEntityFE' => $span->idEntity,
                                'label' => $name
                            ];
                        }
                    }
                }
            }
        }
        //debug($baseLabels, $labels);
//        ksort($spans);
//        debug($labels);
//        debug($it);
//        debug($nis);
//        debug( $wordsChars->words);
//        debug($spans);
        return [
            'it' => $it,
            'idLayers' => $idLayers,
            'words' => $wordsChars->words,
            'idAnnotationSet' => $idAS,
            'lu' => $lu,
            'alternativeLU' => $alternativeLU,
            'target' => $target[0],
            'spans' => $spans,
            'fes' => $fes,
            'fesByType' => $fesByType,
            'nis' => $nis,
            'word' => $token,
            'comment' => CommentService::getAnnotationSetComment($idAS)
        ];

    }

    /**
     * @throws \Exception
     */
    public static function annotateFE(AnnotationData $data): array
    {
        DB::transaction(function () use ($data) {
            $annotationSet = Criteria::byId("view_annotationset", "idAnnotationSet", $data->idAnnotationSet);
            $userTask = Criteria::table("usertask as ut")
                ->join("task as t", "ut.idTask", "=", "t.idTask")
                ->where("ut.idUser", -2)
                ->where("t.name", 'Default Task')
                ->first();
            $fe = FrameElement::byId($data->idFrameElement);
            $spans = Criteria::table("view_annotation_text_fe")
                ->where('idAnnotationSet', $data->idAnnotationSet)
                ->where("layerTypeEntry", "lty_fe")
                ->where("idLanguage", AppService::getCurrentIdLanguage())
                ->select('idAnnotationSet', 'idLayerType', 'idLayer', 'startChar', 'endChar', 'idEntity', 'idTextSpan', 'layerTypeEntry', 'idInstantiationType')
                ->all();
            $layers = Criteria::table("view_layer")
                ->where('idAnnotationSet', $data->idAnnotationSet)
                ->where("entry", "lty_fe")
                ->where("idLanguage", AppService::getCurrentIdLanguage())
                ->all();
            // verify if exists a layer with no overlap, else create one
            $idLayer = 0;
            foreach ($layers as $layer) {
                $overlap = false;
                foreach ($spans as $span) {
                    if ($span->idLayer == $layer->idLayer) {
                        if (!(($data->range->end < $span->startChar) || ($data->range->start > $span->endChar))) {
                            $overlap |= true;
                        }
                    }
                }
                if (!$overlap) {
                    $idLayer = $layer->idLayer;
                    break;
                }
            }
            if ($idLayer == 0) {
                $layerType = Criteria::byId("layertype", "entry", "lty_fe");
                $idLayer = Criteria::create("layer", [
                    'rank' => 0,
                    'idLayerType' => $layerType->idLayerType,
                    'idAnnotationSet' => $data->idAnnotationSet

                ]);
            }
            //
            if ($data->range->type == 'word') {
                $it = Criteria::table("view_instantiationtype")
                    ->where('entry', 'int_normal')
                    ->first();
                $data = json_encode([
                    'startChar' => (int)$data->range->start,
                    'endChar' => (int)$data->range->end,
                    'multi' => 0,
                    'idLayer' => $idLayer,
                    'idInstantiationType' => $it->idInstantiationType,
                    'idSentence' => $annotationSet->idSentence,
                ]);
                $idTextSpan = Criteria::function("textspan_char_create(?)", [$data]);
                $ts = Criteria::table("textspan")
                    ->where("idTextSpan", $idTextSpan)
                    ->first();
                $data = json_encode([
                    'idTextSpan' => $ts->idTextSpan,
                    'idEntity' => $fe->idEntity,
                    'relationType' => 'rel_annotation',
                    'idUserTask' => $userTask->idUserTask
                ]);
                $idAnnotation = Criteria::function("annotation_create(?)", [$data]);
            } else if ($data->range->type == 'ni') {
                $data = json_encode([
                    'startChar' => -1,
                    'endChar' => -1,
                    'multi' => 0,
                    'idLayer' => $idLayer,
                    'idInstantiationType' => (int)$data->range->id,
                    'idSentence' => $annotationSet->idSentence,
                ]);
                $idTextSpan = Criteria::function("textspan_char_create(?)", [$data]);
                $ts = Criteria::table("textspan")
                    ->where("idTextSpan", $idTextSpan)
                    ->first();
                $data = json_encode([
                    'idTextSpan' => $ts->idTextSpan,
                    'idEntity' => $fe->idEntity,
                    'relationType' => 'rel_annotation',
                    'idUserTask' => $userTask->idUserTask
                ]);
                $idAnnotation = Criteria::function("annotation_create(?)", [$data]);
            }
            Timeline::addTimeline("annotation", $idAnnotation, "C");
        });
        return self::getASData($data->idAnnotationSet, $data->token);
    }

    public static function deleteFE(DeleteFEData $data): void
    {
        DB::transaction(function () use ($data) {
            // get FE spans for this idAnnotationSet
            $annotations = Criteria::table("view_annotation_text_fe")
                ->where("idAnnotationSet", $data->idAnnotationSet)
                ->where("idFrameElement", $data->idFrameElement)
                ->where("layerTypeEntry", "lty_fe")
                ->where("idLanguage", AppService::getCurrentIdLanguage())
                ->select("idAnnotation", "idTextSpan", "idLayer")
                ->all();
            foreach ($annotations as $annotation) {
                Criteria::deleteById("annotation", "idAnnotation", $annotation->idAnnotation);
            }
            foreach ($annotations as $annotation) {
                Criteria::deleteById("textspan", "idTextSpan", $annotation->idTextSpan);
            }
            // if FE layer was empty, remove it
            foreach ($annotations as $annotation) {
                $annotationsByLayer = Criteria::table("view_annotation_text_fe")
                    ->where("idLayer", $annotation->idLayer)
                    ->count();
                debug("count = " . $annotationsByLayer);
                if ($annotationsByLayer == 0) {
                    Criteria::deleteById("layer", "idLayer", $annotation->idLayer);
                }
            }
        });
    }

    public static function annotateFELOME(AnnotationData $data, int $idLanguage): void
    {
        DB::transaction(function () use ($data, $idLanguage) {
            $annotationSet = Criteria::byId("view_annotationset", "idAnnotationSet", $data->idAnnotationSet);
            $fe = Criteria::byFilterLanguage("view_frameelement", ['idFrameElement', '=', $data->idFrameElement])->first();
            if ($fe) {
                $spans = Criteria::table("view_annotation_text_fe")
                    ->where('idAnnotationSet', $data->idAnnotationSet)
                    ->where("layerTypeEntry", "lty_fe")
                    ->where("idLanguage", $idLanguage)
                    ->select('idAnnotationSet', 'idLayerType', 'idLayer', 'startChar', 'endChar', 'idEntity', 'idTextSpan', 'layerTypeEntry', 'idInstantiationType')
                    ->all();
                $layers = Criteria::table("view_layer")
                    ->where('idAnnotationSet', $data->idAnnotationSet)
                    ->where("entry", "lty_fe")
                    ->where("idLanguage", $idLanguage)
                    ->all();
                // verify if exists a layer with no overlap, else create one
                $idLayer = 0;
                foreach ($layers as $layer) {
                    $overlap = false;
                    foreach ($spans as $span) {
                        if ($span->idLayer == $layer->idLayer) {
                            if (!(($data->range->end < $span->startChar) || ($data->range->start > $span->endChar))) {
                                $overlap |= true;
                            }
                        }
                    }
                    if (!$overlap) {
                        $idLayer = $layer->idLayer;
                        break;
                    }
                }
                if ($idLayer == 0) {
                    $layerType = Criteria::byId("layertype", "entry", "lty_fe");
                    $idLayer = Criteria::create("layer", [
                        'rank' => 0,
                        'idLayerType' => $layerType->idLayerType,
                        'idAnnotationSet' => $data->idAnnotationSet

                    ]);
                }
                //
                $it = Criteria::table("view_instantiationtype")
                    ->where('entry', 'int_normal')
                    ->first();
                $data = json_encode([
                    'startChar' => (int)$data->range->start,
                    'endChar' => (int)$data->range->end,
                    'multi' => 0,
                    'idLayer' => $idLayer,
                    'idInstantiationType' => $it->idInstantiationType,
                    'idSentence' => $annotationSet->idSentence,
                ]);
                $idTextSpan = Criteria::function("textspan_char_create(?)", [$data]);
                $ts = Criteria::table("textspan")
                    ->where("idTextSpan", $idTextSpan)
                    ->first();
                $data = json_encode([
                    'idTextSpan' => $ts->idTextSpan,
                    'idEntity' => $fe->idEntity,
                    'idUserTask' => 1
                ]);
                $idAnnotation = Criteria::function("annotation_create(?)", [$data]);
            }
        });

    }

}
