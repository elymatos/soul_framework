<?php

namespace App\Repositories;

use App\Database\Criteria;
use App\Services\AppService;
use \Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnnotationSet
{
    public static function listTargetsForDocumentSentence(array $idDocumentSentences): Collection
    {
        $idLanguage = AppService::getCurrentIdLanguage();
        return Criteria::table("view_annotationset as a")
            ->join("view_annotation_text_gl as gl", "a.idAnnotationSet", "=", "gl.idAnnotationSet")
            ->join("lu","a.idLU","=", "lu.idLU")
            ->join("view_frame as f","lu.idFrame","=","f.idFrame")
            ->select('a.idDocumentSentence', 'gl.startChar', 'gl.endChar', 'a.idAnnotationSet','f.name as frameName')
            ->where("gl.layerTypeEntry", 'lty_target')
            ->whereIn("a.idDocumentSentence", $idDocumentSentences)
            ->where("f.idLanguage", $idLanguage)
            ->orderby("gl.startChar")
            ->get();
    }

    public static function getTargets(int $idDocumentSentence): array
    {
        return Criteria::table("view_annotationset as a")
            ->join("view_annotation_text_gl as gl", "a.idAnnotationSet", "=", "gl.idAnnotationSet")
            ->select('a.idDocumentSentence', 'gl.startChar', 'gl.endChar', 'a.idAnnotationSet')
            ->where("gl.layerTypeEntry", 'lty_target')
            ->where("a.idDocumentSentence", $idDocumentSentence)
            ->orderby("gl.startChar")
            ->all();
    }

    public static function getWordsChars(string $text): object
    {
        $array = array();
        $punctuation = " .,;:?/'][\{\}\"!@#$%&*\(\)-_+=“”";
        mb_internal_encoding("UTF-8"); // this IS A MUST!! PHP has trouble with multibyte when no internal encoding is set!
        $last = mb_substr($text, -1);
        if (mb_strpos($punctuation, $last) === false) {
            $text .= '.';
        }
        $i = 0;
        for ($j = 0; $j < mb_strlen($text); $j++) {
            $char = mb_substr($text, $j, 1);
            $break = (mb_strpos($punctuation, $char) !== false);
            if ($break) {
                $word = mb_substr($text, $i, $j - $i);
                $array[$i] = $word;
                $array[$j] = $char;
                $i = $j + 1;
            }
        }
        $words = [];
        $chars = [];
        $order = 1;
        foreach ($array as $startChar => $wordForm) {
            $endChar = $startChar + mb_strlen($wordForm) - 1;
            $lWordForm = $wordForm;//str_replace("'", "\\'", $wordForm);
            $words[(string)$order] = [
                'order' => $order,
                'word' => $lWordForm,
                'startChar' => $startChar,
                'endChar' => $endChar
            ];
            for ($pos = (int)$startChar; $pos <= $endChar; $pos++) {
                $o = $pos - $startChar;
                $chars[$pos] = [
                    'offset' => (string)$o,
                    'char' => mb_substr($wordForm, $o, 1), // tf8_encode($wordForm{$o}), //str_replace("'", "\\'", $wordForm{$o}),
                    'order' => $order
                ];
            }
            ++$order;
        }
        $wordsChars = new \StdClass();
        $wordsChars->words = $words;
        $wordsChars->chars = $chars;
        return $wordsChars;
    }

    public static function getLayers(int $idAnnotationSet): array
    {
        $idLanguage = AppService::getCurrentIdLanguage();
        $cmd = <<<HERE

        SELECT a.idAnnotationSet,
            l.idLayerType,
            l.idLayer,
            l.name AS layer,
            ifnull(ts.startChar,-1) AS startChar,
            ifnull(ts.endChar,-1) AS endChar,
            ifnull(gl.idEntity, ifnull(fe.idEntity, ce.idEntity)) AS idEntity,
            ifnull(gl.name, ifnull(fe.name, ce.name)) AS name,
            ifnull(gl.idColor, ifnull(fe.idColor, ce.idColor)) AS idColor,
            ts.idTextSpan,
            l.entry as layerTypeEntry,
            ts.idInstantiationType,
            it.name instantiationType
        FROM view_annotationset a
            INNER JOIN view_layer l ON (a.idAnnotationSet = l.idAnnotationSet)
            LEFT JOIN textspan ts ON (l.idLayer = ts.idLayer)
            LEFT JOIN view_annotation_text_gl gl ON (ts.idTextSpan = gl.idTextSpan)
            LEFT JOIN view_annotation_text_fe fe ON (ts.idTextSpan = fe.idTextSpan)
            LEFT JOIN view_annotation_text_ce ce ON (ts.idTextSpan = ce.idTextSpan)
            LEFT JOIN view_instantiationtype it ON (ts.idInstantiationType = it.idType)
        WHERE (l.idLanguage = {$idLanguage})
            AND (a.idAnnotationSet = {$idAnnotationSet})

            AND ((fe.idLanguage = {$idLanguage}) or (fe.idLanguage is null))
            AND ((ce.idLanguage = {$idLanguage}) or (ce.idLanguage is null))
            AND ((it.idLanguage = {$idLanguage}) or (it.idLanguage is null))
        ORDER BY a.idAnnotationSet, l.layerOrder, ts.startChar

HERE;

        return DB::select($cmd);
    }

    public static function listFECEByAS(array|int $idAnnotationSet)
    {
        $idLanguage = AppService::getCurrentIdLanguage();
        if (!is_array($idAnnotationSet)) {
            $idAnnotationSet = [$idAnnotationSet];
        }
        $set = implode(',', $idAnnotationSet);
        $cmd = <<<HERE
 SELECT ds.idSentence, fece.startChar, fece.endChar, fece.idInstantiationType, fece.idColor, fece.name feName, fece.layerTypeEntry, it.name instantiationType
 from view_annotationset a
 join document_sentence ds on (a.idDocumentSentence = ds.idDocumentSentence)
left join (
     select idAnnotationSet, name, idColor, layerTypeEntry, startChar, endChar,idInstantiationType
     from view_annotation_text_fe afe
     where (idAnnotationSet IN ({$set})) and (idLanguage = 1)
     union
     select idAnnotationSet, name, idColor, layerTypeEntry, startChar, endChar,idInstantiationType
     from view_annotation_text_ce ace
     where (idAnnotationSet IN ({$set})) and  (idLanguage = {$idLanguage})
     union
     select idAnnotationSet, name, idColor, layerTypeEntry, startChar, endChar,idInstantiationType
     from view_annotation_text_gl agl
     where (idAnnotationSet IN ({$set})) and  (idLanguage = {$idLanguage}) and (layerTypeEntry = 'lty_target')
) fece on (a.idAnnotationSet = fece.idAnnotationSet)
left join view_instantiationtype it on (fece.idInstantiationType = it.idInstantiationType)
 where (a.idAnnotationSet IN ({$set}))
 and ((it.idLanguage = {$idLanguage}) or (it.idLanguage is null))
        ORDER BY ds.idSentence,fece.startChar

HERE;
        return collect(DB::select($cmd))->groupBy('idSentence')->all();
    }

    public static function listSentencesByAS(int|array $idAnnotationSet)
    {
        $criteria = Criteria::table("view_annotationset as a")
            ->join("document_sentence as ds", "a.idDocumentSentence", "=", "ds.idDocumentSentence")
            ->join("view_sentence as s", "ds.idSentence", "=", "s.idSentence")
            ->select('a.idAnnotationSet', 'ds.idDocumentSentence', 's.idSentence', 's.text');
        if (is_array($idAnnotationSet)) {
            $criteria->where('idAnnotationSet', 'IN', $idAnnotationSet);
        } else {
            $criteria->where('idAnnotationSet', '=', $idAnnotationSet);
        }
        $criteria->orderBy('idAnnotationSet');
//        $criteria->where('entries.idLanguage','=', AppService::getCurrentIdLanguage());
        return $criteria->all();
    }

    public static function createForLU(int $idDocumentSentence, int $idLU, int $startChar, int $endChar): ?int
    {
        DB::beginTransaction();
        try {
            $documentSentence = Criteria::byId("document_sentence","idDocumentSentence",$idDocumentSentence);
            $lu = LU::byId($idLU);
            $ti = Criteria::byId("type", "entry", 'ast_unann');
            $annotationSet = [
                'idSentence' => $documentSentence->idSentence,
                'idLU' => $lu->idLU,
                'idAnnotationStatus' => $ti->idType,
                'idFrame' => $lu->idFrame
            ];
            $idAnnotationSet = Criteria::create("annotationset", $annotationSet);
            $ti = Criteria::byId("type", "entry", 'int_normal');
            $layerTypes = LayerType::listToLU($lu);
            foreach ($layerTypes as $lt) {
                $layer = [
                    'rank' => 1,
                    'idLayerType' => $lt->idLayerType,
                    'idAnnotationSet' => $idAnnotationSet
                ];
                $idLayer = Criteria::create("layer", $layer);
                if ($lt->entry == 'lty_target') {
                    $target = Criteria::table("genericlabel")
                        ->where("name", "Target")
                        ->where("idLanguage", AppService::getCurrentIdLanguage())
                        ->first();
                    $textspan = json_encode([
                        'startChar' => $startChar,
                        'endChar' => $endChar,
                        'multi' => 0,
                        'idInstantiationType' => $ti->idType,
                        'idLayer' => $idLayer,
                        'idSentence' => $documentSentence->idSentence,
                    ]);
                    $idTextSpan = Criteria::function("textspan_char_create(?)", [$textspan]);
                    $ts = Criteria::table("textspan")
                        ->where("idTextSpan", $idTextSpan)
                        ->first();
                    $userTask = Criteria::table("usertask as ut")
                        ->join("task as t", "ut.idTask", "=", "t.idTask")
                        ->where("ut.idUser", -2)
                        ->where("t.name", 'Default Task')
                        ->first();
                    $data = json_encode([
                        'idTextSpan' => $ts->idTextSpan,
                        'idEntity' => $target->idEntity,
                        'idUserTask' => $userTask->idUserTask
                    ]);
                    Criteria::function("annotation_create(?)", [$data]);
                }
            }
            DB::commit();
            return $idAnnotationSet;
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }

    }

    public static function createForLOME(int $idDocumentSentence, int $idFrame,  int $startChar, int $endChar, int $idLanguage,?int $idLexicon = null ): ?int
    {
        DB::beginTransaction();
        try {
            $documentSentence = Criteria::byId("document_sentence","idDocumentSentence",$idDocumentSentence);
            $ti = Criteria::byId("type", "entry", 'ast_unann');
            $annotationSet = [
                'idSentence' => $documentSentence->idSentence,
                'idLexicon' => $idLexicon,
                'idAnnotationStatus' => $ti->idType,
                'idFrame' => $idFrame
            ];
            $idAnnotationSet = Criteria::create("annotationset", $annotationSet);
            $ti = Criteria::byId("type", "entry", 'int_normal');
            // target
            $layerTypeTarget = Criteria::table("layertype")
                ->select('idLayerType','entry')
                ->where('entry', '=', 'lty_target')
                ->first();
            $layer = [
                'rank' => 1,
                'idLayerType' => $layerTypeTarget->idLayerType,
                'idAnnotationSet' => $idAnnotationSet
            ];
            $idLayer = Criteria::create("layer", $layer);
            $target = Criteria::table("genericlabel")
                ->where("name", "Target")
                ->where("idLanguage", $idLanguage)
                ->first();
            $textspan = json_encode([
                'startChar' => $startChar,
                'endChar' => $endChar,
                'multi' => 0,
                'idInstantiationType' => $ti->idType,
                'idLayer' => $idLayer,
                'idSentence' => $documentSentence->idSentence,
            ]);
            $idTextSpan = Criteria::function("textspan_char_create(?)", [$textspan]);
            $ts = Criteria::table("textspan")
                ->where("idTextSpan", $idTextSpan)
                ->first();
            $data = json_encode([
                'idTextSpan' => $ts->idTextSpan,
                'idEntity' => $target->idEntity,
                'idUserTask' => 1
            ]);
            Criteria::function("annotation_create(?)", [$data]);
            DB::commit();
            return $idAnnotationSet;
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public static function delete(int $idAnnotationSet): int
    {
        DB::beginTransaction();
        try {
            $idAnnotationSet = Criteria::function("annotationset_delete(?,?)",[$idAnnotationSet, AppService::getCurrentIdUser()]);
            DB::commit();
            return $idAnnotationSet;
        } catch (\Exception $e) {
            DB::rollback();
            debug($e->getMessage());
            throw new \Exception("Operation denied. Check if AS has spans or comments.");
        }
    }


//    public function allowManyAnnotationSet()
//    {
//        return true;
//    }
//

//
//

//
//    public static function addFELayer(int $idAnnotationSet): int
//    {
//        $layerType = LayerType::getByEntry('lty_fe');
//        return Layer::save((object)[
//            'rank' => 0,
//            'idLayerType' => $layerType->idLayerType,
//            'idAnnotationSet' => $idAnnotationSet
//        ]);
//    }
//

//
//    public static function createLayersForLU(int $idAnnotationSet, object $lu, int $startChar, int $endChar)
//    {
//        $ti = type::getByEntry('int_normal');
//        $layerTypes = LayerType::listToLU($lu);
//        foreach ($layerTypes as $lt) {
//            $idLayer = Layer::save((object)[
//                'rank' => 1,
//                'idLayerType' => $lt->idLayerType,
//                'idAnnotationSet' => $idAnnotationSet
//            ]);
//            if ($lt->entry == 'lty_target') {
//                $target = GenericLabel::getTarget();
//                Label::save((object)[
//                    'startChar' => $startChar,
//                    'endChar' => $endChar,
//                    'multi' => 0,
//                    'idInstantiationType' => $ti->idType,
//                    'idLayer' => $idLayer,
//                    'idLabelType' => $target->idEntity
//                ]);
//            }
//        }
//    }
//
//
//    public static function deleteFE(DeleteFEData $data): int
//    {
//        self::beginTransaction();
//        try {
//            self::commit();
//            return $data->idAnnotationSet;
//        } catch (\Exception $e) {
//            self::rollback();
//            throw new \Exception($e->getMessage());
//        }
//    }
//
//    /**
//     * Corpus annotation
//     */
//
//    public static function getLayersSentence(int $idSentence)
//    {
//        $criteria = self::getCriteria()
//            ->select(['layers.idLayer', 'layers.layerType.name', 'idAnnotationSet'])
//            ->where("idSentence", "=", $idSentence)
//            ->where("layers.layerType.idLanguage", "=", AppService::getCurrentIdLanguage());
////        if (!$this->allowManyAnnotationSet()) {
////            $criteria->where("idAnnotationSet = {$this->getId()}");
////        }
//        return $criteria->all();
//    }
//
//
//    public static function getlabelTypesCEFE(int $idSentence)
//    {
//        $condition = '';
////        if (!$this->allowManyAnnotationSet()) {
////            $condition = "AND (a.idAnnotationSet = {$this->getId()})";
////        }
//
//        $idLanguage = AppService::getCurrentIdLanguage();
//        $cmd = <<<HERE
//        SELECT a.idAnnotationSet,
//            concat('lty_cefe_', fe.idFrame, '_', a.idAnnotationSet) as idLayer,
//            fe.idEntity AS idLabelType,
//            e.name AS labelType,
//            fe.idColor,
//            '' AS coreType
//        FROM view_annotationset a
//            INNER JOIN view_constructionelement ce on (a.idConstruction = ce.idConstruction)
//            INNER JOIN view_relation r on (ce.idEntity = r.idEntity1)
//            INNER JOIN view_frameelement fe on (r.idEntity2 = fe.idEntity)
//            INNER JOIN view_entrylanguage e on (fe.entry = e.entry)
//        WHERE (e.idLanguage = {$idLanguage})
//            AND (r.relationType = 'rel_evokes')
//            {$condition}
//            AND (a.idSentence = {$idSentence})
//        ORDER BY a.idAnnotationSet, idLayer, e.name
//
//HERE;
//        $query = self::query($cmd);//$this->getDb()->getQueryCommand($cmd);
//        return $query;
//    }
//
//
//    public static function getAnnotationSetsBySentence(int $idSentence)
//    {
//        $idLanguage = AppService::getCurrentIdLanguage();
//        $criteriaLU = ViewAnnotationSet::getCriteria()
//            ->distinct(true)
//            ->select(["idAnnotationSet", "concat(lu.frame.name, '.', lu.name) as name", "'lu' type"])
//            ->where("idSentence", "=", $idSentence)
//            ->where("lu.frame.idLanguage", "=", $idLanguage);
////        if (!$this->allowManyAnnotationSet()) {
////            $criteriaLU->where("idAnnotationSet = {$this->getId()}");
////        }
//        $criteriaCxn = ViewAnnotationSet::getCriteria()
//            ->distinct(true)
//            ->select(["idAnnotationSet", "cxn.name", "'cxn' as type"])
//            ->where("idSentence", "=", $idSentence)
//            ->where("cxn.idLanguage", "=", $idLanguage);
////        if (!$this->allowManyAnnotationSet()) {
////            $criteriaCxn->where("idAnnotationSet = {$this->getId()}");
////        }
//        $cmd = "({$criteriaLU->toRawSql()}) UNION ({$criteriaCxn->toRawSql()})";
//        return self::query($cmd);
//    }
//
//
//    public static function getlabelTypesGLGF(int $idSentence): Criteria
//    {
//        $criteria = self::getCriteria()
//            ->select([
//                "idAnnotationSet",
//                "layers.idLayer",
//                "layers.layerType.entry",
//                "layers.layerType.relations.genericLabel.idEntity as idLabelType",
//                "layers.layerType.relations.genericLabel.name as labelType",
//                "layers.layerType.relations.genericLabel.idColor",
//                "'' as coreType"
//            ])
//            ->where("idSentence", "=", $idSentence)
//            ->where("layers.layerType.entry", "=", "lty_gf")
//            ->where("layers.layerType.relations.genericLabel.idLanguage", "=", AppService::getCurrentIdLanguage());
////        if (!$this->allowManyAnnotationSet()) {
////            $criteria->where("idAnnotationSet = {$this->getId()}");
////        }
//        $criteria->order([["idAnnotationSet"], ["layers.idLayer"], ["layers.layerType.relations.genericLabel.name"]]);
//        return $criteria;
//    }
//
//    public static function getlabelTypesGL(int $idSentence): Criteria
//    {
//        $criteria = self::getCriteria()
//            ->select([
//                "idAnnotationSet",
//                "layers.idLayer",
//                "layers.layerType.entry",
//                "layers.layerType.relations.genericLabel.idEntity as idLabelType",
//                "layers.layerType.relations.genericLabel.name as labelType",
//                "layers.layerType.relations.genericLabel.idColor",
//                "'' as coreType"
//            ])
//            ->where("idSentence", "=", $idSentence)
//            ->where("layers.layerType.entry", "<>", "lty_gf")
//            ->where("layers.layerType.relations.genericLabel.idLanguage", "=", AppService::getCurrentIdLanguage());
////        if (!$this->allowManyAnnotationSet()) {
////            $criteria->where("idAnnotationSet = {$this->getId()}");
////        }
//        $criteria->order([["idAnnotationSet"], ["layers.idLayer"], ["layers.layerType.relations.genericLabel.name"]]);
//        return $criteria;
//    }
//
//
//    public static function getlabelTypesFE($idSentence, $forceId = false)
//    {
//        $condition = '';
////        if (!$this->allowManyAnnotationSet() || $forceId) {
////            $condition = "AND (a.idAnnotationSet = {$this->getId()})";
////        }
//
//        $idLanguage = AppService::getCurrentIdLanguage();
//        $cmd = <<<HERE
//
//        SELECT distinct a.idAnnotationSet,
//            l.idLayer,
//            fe.idEntity AS idLabelType,
//            e.name AS labelType,
//            fe.idColor,
//            fe.typeEntry AS coreType,
//            ti.info
//        FROM view_annotationset a
//            INNER JOIN view_layer l on (a.idAnnotationSet = l.idAnnotationSet)
//            INNER JOIN view_lu lu on (a.idLU = lu.idLU)
//            INNER JOIN view_frameelement fe on (lu.idFrame = fe.idFrame)
//            INNER JOIN type ti on (fe.typeEntry=ti.entry)
//            INNER JOIN view_entrylanguage e on (fe.entry = e.entry)
//        WHERE (e.idLanguage = {$idLanguage} )
//            AND (l.entry = 'lty_fe' )
//            AND (a.idSentence = {$idSentence}) {$condition}
//        ORDER BY a.idAnnotationSet, l.idLayer, ti.info, fe.typeEntry, e.name
//HERE;
//        return self::query($cmd);
//    }
//
//    public static function getlabelTypesCE(int $idSentence)
//    {
//        $condition = '';
////        if (!$this->allowManyAnnotationSet()) {
////            $condition = "AND (a.idAnnotationSet = {$this->getId()})";
////        }
//        $idLanguage = AppService::getCurrentIdLanguage();
//        $cmd = <<<HERE
//
//        SELECT a.idAnnotationSet,
//            l.idLayer,
//            ce.idEntity AS idLabelType,
//            e.name AS labelType,
//            ce.idColor,
//            '' AS coreType
//        FROM view_annotationset a
//            INNER JOIN view_layer l on (a.idAnnotationSet = l.idAnnotationSet)
//            INNER JOIN view_constructionelement ce on (a.idConstruction = ce.idConstruction)
//            INNER JOIN view_entrylanguage e on (ce.entry = e.entry)
//        WHERE (e.idLanguage = {$idLanguage} )
//            AND (l.entry = 'lty_ce' )
//            AND (a.idSentence = {$idSentence})
//            {$condition}
//        ORDER BY a.idAnnotationSet, l.idLayer, e.name
//HERE;
//        return self::query($cmd);
//    }
//
//    public static function getNI($idSentence, $idLanguage)
//    {
//        $condition = '';
////        if (!$this->allowManyAnnotationSet()) {
////            $condition = "AND (a.idAnnotationSet = {$this->getId()})";
////        }
//
//        $idLanguage = AppService::getCurrentIdLanguage();
//
//        $cmd = <<<HERE
//
//        SELECT lb.idLabel, l.idLayer, lb.idInstantiationType, eit.name as instantiationType, entry_fe.name as feName, fe.idColor as idColor, lb.idLabelType
//        FROM label lb
//            INNER JOIN view_layer l ON (lb.idLayer = l.idLayer)
//            INNER JOIN view_annotationset a ON (l.idAnnotationSet = a.idAnnotationSet)
//            INNER JOIN view_instantiationtype it ON (lb.idInstantiationType = it.idType)
//            INNER JOIN view_entrylanguage eit on (it.entry = eit.entry)
//            INNER JOIN view_frameelement fe
//                ON (lb.idLabelType = fe.idEntity)
//            INNER JOIN entry entry_fe
//                ON (fe.entry = entry_fe.entry)
//        WHERE (it.entry <> 'int_normal')
//            AND ((entry_fe.idLanguage = {$idLanguage}) or (entry_fe.idLanguage is null))
//            AND ((eit.idLanguage = {$idLanguage}) or (eit.idLanguage is null))
//            {$condition}
//            AND (a.idSentence = {$idSentence})
//
//HERE;
//        return self::query($cmd);
//    }
//
//
//    public static function getLayersData(int $idSentence)
//    {
//        $condition = '';
////            if (!$this->allowManyAnnotationSet()) {
////                $condition = "AND (a.idAnnotationSet = {$this->getId()})";
////            }
//        $idLanguage = AppService::getCurrentIdLanguage();
//        $cmd = <<<HERE
//
//            SELECT a.idAnnotationSet,
//                l.idLayerType,
//                l.idLayer,
//                el.name AS layer,
//                ifnull(lb.startChar,-1) AS startChar,
//                ifnull(lb.endChar,-1) AS endChar,
//                ifnull(gl.idEntity, ifnull(fe.idEntity, ce.idEntity)) AS idLabelType,
//                lb.idLabel,
//                l.entry as layerTypeEntry
//            FROM view_annotationset a
//                INNER JOIN view_layer l ON (a.idAnnotationSet = l.idAnnotationSet)
//                INNER JOIN view_entrylanguage el on (l.entry = el.entry)
//                LEFT JOIN label lb ON (l.idLayer=lb.idLayer)
//                LEFT JOIN genericlabel gl ON (lb.idLabelType=gl.idEntity)
//                LEFT JOIN view_frameelement fe ON (lb.idLabelType=fe.idEntity)
//                LEFT JOIN view_constructionelement ce ON (lb.idLabelType=ce.idEntity)
//            WHERE (el.idLanguage = {$idLanguage} )
//                {$condition}
//                AND (a.idSentence = {$idSentence} )
//            ORDER BY a.idAnnotationSet, l.layerOrder, l.idLayer, ifnull(lb.startChar,-1)
//
//    HERE;
//
//        return self::query($cmd);
//    }
//
//    public static function getLayersOrderByTarget($idSentence)
//    {
//        $condition = '';
////            if (!$this->allowManyAnnotationSet()) {
////                $condition = "AND (a.idAnnotationSet = {$this->getId()})";
////            }
//        $idLanguage = AppService::getCurrentIdLanguage();
//        $cmd = <<<HERE
//
//            SELECT a.idAnnotationSet
//            FROM view_annotationset a
//                INNER JOIN view_layer l ON (a.idAnnotationSet = l.idAnnotationSet)
//                INNER JOIN view_entrylanguage el on (l.entry = el.entry)
//                LEFT JOIN label lb ON (l.idLayer=lb.idLayer)
//                LEFT JOIN genericlabel gl ON (lb.idLabelType=gl.idEntity)
//                LEFT JOIN view_frameelement fe ON (lb.idLabelType=fe.idEntity)
//                LEFT JOIN view_constructionelement ce ON (lb.idLabelType=ce.idEntity)
//            WHERE (el.idLanguage = {$idLanguage} )
//                {$condition} AND  (l.entry = 'lty_target')
//                AND (a.idSentence = {$idSentence} )
//            ORDER BY ifnull(lb.startChar,-1), a.idAnnotationSet
//
//    HERE;
//
//        return self::query($cmd);
//    }
//
//    public static function getLayerNameCnxFrame(int $idSentence)
//    {
//        $condition = '';
////            if (!$this->allowManyAnnotationSet()) {
////                $condition = "AND (a.idAnnotationSet = {$this->getId()})";
////            }
//
//        $idLanguage = AppService::getCurrentIdLanguage();
//        $cmd = <<<HERE
//                SELECT concat('lty_cefe_', f.idFrame, '_', a.idAnnotationSet) as idLayer, f.idFrame, e.name, a.idAnnotationSet
//                FROM view_annotationset a
//                    INNER JOIN view_construction c on (a.idConstruction = c.idConstruction)
//                    INNER JOIN view_relation r on (c.idEntity = r.idEntity1)
//                    INNER JOIN view_frame f on (r.idEntity2 = f.idEntity)
//                    INNER JOIN view_entrylanguage e on (f.entry = e.entry)
//                WHERE (e.idLanguage = {$idLanguage})
//                    AND (r.relationType = 'rel_evokes' )
//                    {$condition}
//                    AND (a.idSentence = {$idSentence})
//    HERE;
//
//        return self::query($cmd);
//    }
//
//    public static function getCEFEData($idSentence, $idCEFELayer, $idAnnotationSet)
//    {
//        $cmd = <<<HERE
//            SELECT ifnull(lb.startChar,-1) AS startChar,
//                ifnull(lb.endChar,-1) AS endChar,
//                fe.idEntity AS idLabelType
//            FROM view_annotationset a
//                INNER JOIN view_layer l  ON (a.idAnnotationSet = l.idAnnotationSet)
//                LEFT JOIN label lb ON (l.idLayer=lb.idLayer)
//                LEFT JOIN view_constructionelement ce on (lb.idLabelType = ce.idEntity)
//                LEFT JOIN view_relation r on (ce.idEntity = r.idEntity1)
//                LEFT JOIN view_frameelement fe on (r.idEntity2 = fe.idEntity)
//            WHERE (r.relationType = 'rel_evokes')
//                AND (a.idSentence = {$idSentence})
//                AND (a.idAnnotationSet = {$idAnnotationSet})
//                AND (concat('lty_cefe_', fe.idFrame, '_', a.idAnnotationSet) = '{$idCEFELayer}')
//
//    HERE;
//        return self::query($cmd);
//    }
//
//    public static function deleteLastFELayer(int $idLayer): void
//    {
//        try {
//            Layer::delete($idLayer);
//        } catch (\Exception $e) {
//            throw new \Exception("Error deleting layer. Check if labels exist on layer.");
//        }
////        $rows = self::getCriteria()
////            ->select('layers.idLayer')
////            ->where("idAnnotationSet", "=", $idAnnotationSet)
////            ->where("layers.layerType.entry", "=", 'lty_fe')
////            ->getResult();
////        $maxIdLayer = 0;
////        foreach ($rows as $row) {
////            if ($row->idLayer > $maxIdLayer) {
////                $maxIdLayer = $row->idLayer;
////            }
////        }
////        if ($maxIdLayer > 0) {
////            Layer::delete($maxIdLayer);
////        }
//    }
//
//    /*
//
//
//
//
//
//
//
//            /*
//            public function getDescription()
//            {
//                return $this->getIdAnnotationSet();
//            }
//
//            public function setIdAnnotationStatus($value)
//            {
//                if (substr($value, 0, 3) == 'ast') {
//                    $ti = new type();
//                    $filter = (object)['entry' => $value];
//                    $idStatus = $ti->listAnnotationStatus($filter)->asQuery()->getResult()[0]['idAnnotationStatus'];
//                } else {
//                    $idStatus = $value;
//                }
//                parent::setIdAnnotationStatus($idStatus);
//            }
//
//            public function getFullAnnotationStatus()
//            {
//                $idAnnotationStatus = ($this->getIdAnnotationStatus() ?: '0');
//                $criteria = $this->getCriteria()->
//                select('idAnnotationStatus, annotationStatus.entry, annotationStatus.entries.name as annotationStatus, annotationStatus.idType,' .
//                    'annotationStatus.color.rgbBg');
//                if ($idAnnotationStatus) {
//                    $criteria->where("idAnnotationStatus = {$idAnnotationStatus}");
//                }
//                Base::entryLanguage($criteria, 'annotationStatus');
//                return $criteria->asQuery()->asObjectArray()[0];
//            }
//
//            public function listByFilter($filter)
//            {
//                $criteria = $this->getCriteria()->select('*')->orderBy('idAnnotationSet');
//                if ($filter->idAnnotationSet) {
//                    $criteria->where("idAnnotationSet LIKE '{$filter->idAnnotationSet}%'");
//                }
//                return $criteria;
//            }
//
//            //    public function listBySubCorpus($idSubCorpus)
//            //    {
//            //        $criteria = $this->getCriteria()->select('*');
//            //        $criteria->where("idSubCorpus = {$idSubCorpus}");
//            //        return $criteria;
//            //    }
//
//            public function getIdLU()
//            {
//                //return $this->getSubCorpus()->getIdLU();
//                return $this->getLU()->getIdLU();
//            }
//
//            public function getLUFullName()
//            {
//            //        $idLU = $this->getSubCorpus()->getIdLU();
//            //        if ($idLU) {
//            //            $lu = new LU($this->getSubCorpus()->getIdLU());
//            //            return $lu->getFullName();
//            //        } else {
//            //            return '';
//            //        }
//                $lu = $this->getLU();
//                return ($lu ? $lu->getFullName() : '');
//            }
//
//            public function getWords($idSentence)
//            {
//                $criteria = $this->getCriteria()
//                    ->select('sentence.text')
//                    ->where("idSentence = {$idSentence}");
//                $result = $criteria->asQuery()->getResult();
//                //$text = utf8_decode($result[0][0]);
//                $text = $result[0][0];
//                $array = array();
//                $punctuation = " .,;:?/'][\{\}\"!@#$%&*\(\)-_+=";
//                $word = "";
//
//                mb_internal_encoding("UTF-8"); // this IS A MUST!! PHP has trouble with multibyte when no internal encoding is set!
//                $i = 0;
//                for ($j = 0; $j < mb_strlen($text); $j++) {
//                    $char = mb_substr($text, $j, 1);
//                    $break = (mb_strpos($punctuation, $char) !== false);
//                    if ($break) {
//                        $word = mb_substr($text, $i, $j - $i);
//                        $array[$i] = $word;
//                        $array[$j] = $char;
//                        $i = $j + 1;
//                    }
//                }
//                $values[-1] = [0, '', -1];
//                $order = 0;
//                foreach ($array as $startChar => $wordForm) {
//                    $endChar = $startChar + mb_strlen($wordForm) - 1;
//                    $lWordForm = str_replace("'", "\\'", $wordForm);
//                    ++$order;
//                    $values[$startChar] = [$order, $lWordForm, $startChar, $endChar]; //"{$order}, {$startChar}, {$endChar}, {$idSentence}, 0, '{$lWordForm}'";
//                }
//                return $values;
//            }
//
//
//
//            public function getLayers($idSentence)
//            {
//                $criteria = $this->getCriteria();
//                $criteria->select('layers.idLayer, layers.layertype.entries.name as name, idAnnotationSet');
//                $criteria->where("idSentence = {$idSentence}");
//                Base::entryLanguage($criteria, 'layers.layertype');
//                if (!$this->allowManyAnnotationSet()) {
//                    $criteria->where("idAnnotationSet = {$this->getId()}");
//                }
//                return $criteria->asQuery()->getResult();
//            }
//
//            public function getlabels($idSentence)
//            {
//                $criteria = $this->getCriteria();
//                $criteria->select('layers.idLayer, layers.labels.idLabel, layers.labels.idLabelType');
//                $criteria->where("idSentence = {$idSentence}");
//                if (!$this->allowManyAnnotationSet()) {
//                    $criteria->where("idAnnotationSet = {$this->getId()}");
//                }
//                return $criteria->asQuery()->getResult();
//            }
//
//
//
//
//            public function getElementCxnFrame($idFrame)
//            {
//                $cmd = <<<HERE
//                SELECT
//                    ce.idEntity idEntityCE, fe.idEntity idEntityFE
//                FROM
//                    view_constructionelement ce
//                        INNER JOIN view_relation r on (ce.idEntity = r.idEntity1)
//                        INNER JOIN view_frameelement fe on (r.idEntity2 = fe.idEntity)
//                    WHERE (r.relationType = 'rel_evokes' )
//                        AND (fe.idFrame = {$idFrame})
//            HERE;
//
//                $query = $this->getDb()->getQueryCommand($cmd);
//                return $query;
//            }
//
//
//
//
//
//            public function delFELayer()
//            {
//                $criteria = $this->getCriteria();
//                $criteria->select('layers.idLayer');
//                $criteria->where("idAnnotationSet = {$this->getId()}");
//                $criteria->where("layers.layertype.entry = 'lty_fe'");
//                $query = $criteria->asQuery();
//                $rows = $query->getResult();
//                $maxIdLayer = 0;
//                foreach ($rows as $row) {
//                    if ($row['idLayer'] > $maxIdLayer) {
//                        $maxIdLayer = $row['idLayer'];
//                    }
//                }
//                if ($maxIdLayer > 0) {
//                    $layer = new Layer($maxIdLayer);
//                    $layer->delete();
//                }
//            }
//
//            public function save(): ?int
//            {
//                ddump('==================' . $this->getIdAnnotationStatus());
//                if ($this->getIdAnnotationStatus() == 6) {
//                    parent::save();
//                    Timeline::addTimeline("annotationset", $this->getId(), "S");
//                }
//                return $this->getId();
//            }
//
//            public function putLayers($layers)
//            {
//                //ddump($layers);
//                $layerCE = new \StdClass();
//                $type = new Type();
//                $instances = $type->getInstantiationType('int_normal')->asQuery()->getResult();
//                $itNormal = $instances[0]['idInstantiationType'];
//                $hasFE = [];
//                $transaction = $this->beginTransaction();
//                try {
//                    $label = new label();
//                    foreach ($layers as $layer) {
//                        $idLayer = $layer->idLayer;
//                        if ($idLayer == '') {
//                            continue;
//                        }
//                        if ($layer->layerTypeEntry == 'lty_ce') {
//                            $layerCE = $layer;
//                        }
//                        $labels = array();
//                        if ($layer->idLayerType != 0) {
//                            if (substr($layer->layerTypeEntry, 0, 8) == 'lty_cefe') {
//                                $idFrame = substr($layer->layerTypeEntry, 9);
//                                ddump('************* ' . $idFrame);
//                                $idFrame = substr($idFrame, 0, strpos($idFrame, '_'));
//                                ddump('************* ' . $idFrame);
//                                unset($layer);
//                                $layer = clone $layerCE;
//                                $layer->layerTypeEntry = 'lty_cefe';
//                                $queryCEFE = $this->getElementCxnFrame($idFrame);
//                                $cefe = $queryCEFE->chunkResult('idEntityCE', 'idEntityFE');
//                                foreach ($layerCE as $key => $value) {
//                                    if (substr($key, 0, 2) == 'wf') {
//                                        if ($cefe[$value]) {
//                                            $layer->$key = $cefe[$value];
//                                        }
//                                    }
//                                }
//                            } else {
//                                $label->getDeleteCriteria()->where("idLayer = {$idLayer}")->delete();
//                            }
//                            $i = -1;
//                            $l = 0;
//                            $o = -1;
//                            foreach ($layer as $key => $value) {
//                                if (substr($key, 0, 2) == 'wf') {
//                                    $idLabelType = $layer->$key;
//                                    if ($idLabelType == '') {
//                                        continue;
//                                    }
//                                    $pos = (int)(substr($key, 2));
//                                    if (($idLabelType != $l) || ($pos != $o)) {
//                                        $i++;
//                                        $labels[$i] = (object)['idLabelType' => $idLabelType, 'startChar' => $pos, 'endChar' => $pos];
//                                        $l = $idLabelType;
//                                    } else {
//                                        $labels[$i]->endChar = $pos;
//                                    }
//                                    $o = $pos + 1;
//                                }
//                            }
//                        }
//                        if (count($labels)) {
//                            if (substr($layer->layerTypeEntry, 0, 8) == 'lty_cefe') {
//                                continue;
//                            }
//                            if ($layer->layerTypeEntry == 'lty_fe') {
//                                $hasFE[$layer->idAnnotationSet] = true;
//                            }
//                            if ($layer->layerTypeEntry == 'lty_ce') {
//                                $hasFE[$layer->idAnnotationSet] = true;
//                            }
//                            foreach ($labels as $labelObj) {
//                                $label->setPersistent(FALSE);
//                                $label->setIdLayer($idLayer);
//                                $label->setidLabelType($labelObj->idLabelType);
//                                $label->setStartChar($labelObj->startChar);
//                                $label->setEndChar($labelObj->endChar);
//                                $label->setMulti(0);
//                                $label->setIdInstantiationType($itNormal);
//                                $label->save();
//                            }
//                        }
//                        if ($layer->ni->$idLayer) {
//                            $hasFE[$layer->idAnnotationSet] = true;
//                            //ddump($layer->ni->$idLayer);
//                            foreach ($layer->ni->$idLayer as $idLabelType => $ni) {
//                                $label->setPersistent(FALSE);
//                                $label->setIdLayer($idLayer);
//                                $label->setidLabelType($idLabelType);
//                                $label->setidInstantiationType($ni->idInstantiationType);
//                                $label->setStartChar(NULL);
//                                $label->setEndChar(NULL);
//                                $label->setMulti(0);
//                                $label->save();
//                            }
//                        }
//                    }
//                    $transaction->commit();
//                    return $hasFE;
//                } catch (\Exception $e) {
//                    $transaction->rollback();
//                    throw new \Exception($e->getMessage());
//                }
//            }
//
//            public function addLU($data)
//            {
//                $transaction = $this->beginTransaction();
//                try {
//                    $lu = LU::create($data->idLU);
//                    $annotationSet = new AnnotationSet();
//                    $annotationSet->setIdSentence($data->idSentence);
//                    $annotationSet->setIdAnnotationStatus('ast_manual');
//                    $annotationSet->setIdEntityLU($lu->getIdEntity());
//                    $annotationSet->save();
//                    $annotationSet->createLayersForLU($lu, $data);
//                    $transaction->commit();
//                } catch (\Exception $ex) {
//                    $transaction->rollback();
//                    ddump($ex->getMessage());
//                    throw new \Exception($ex->getMessage());
//                }
//            }
//
//            public function createLayersForLU($lu, $data)
//            {
//                $layerType = new LayerType();
//                $layerTypes = $layerType->listToLU($lu);
//                foreach ($layerTypes as $lt) {
//                    $layer = new Layer();
//                    $layer->setIdLayerType($lt['idLayerType']);
//                    $layer->setIdAnnotationSet($this->getId());
//                    $layer->setRank(1);
//                    $layer->save();
//                    if ($lt['entry'] == 'lty_target') {
//                        if (isset($data->chars)) {
//                            $startChar = $endChar = -1;
//                            array_push($data->chars, '');
//                            foreach ($data->chars as $i => $char) {
//                                if ($char == '') {
//                                    if ($startChar != -1) {
//                                        $label = new label();
//                                        $label->setMulti(0);
//                                        $label->setIdInstantiationTypeFromEntry('int_normal');
//                                        $idLabelType = $layerType->listlabelType((object)['entry' => 'lty_target'])->asQuery()->getResult()[0]['idLabelType'];
//                                        $label->setidLabelType($idLabelType);
//                                        $label->setIdLayer($layer->getId());
//                                        $label->setStartChar($startChar);
//                                        $label->setEndChar($endChar);
//                                        $label->save();
//                                        $startChar = $endChar = 0;
//                                    }
//                                } else {
//                                    if ($startChar == -1) {
//                                        $startChar = $i;
//                                    }
//                                    $endChar = $i;
//                                }
//                            }
//
//                        } else {
//                            $label = new label();
//                            $label->setMulti(0);
//                            $label->setIdInstantiationTypeFromEntry('int_normal');
//                            $idLabelType = $layerType->listlabelType((object)['entry' => 'lty_target'])->asQuery()->getResult()[0]['idLabelType'];
//                            $label->setidLabelType($idLabelType);
//                            $label->setIdLayer($layer->getId());
//                            $label->setStartChar($data->startChar);
//                            $label->setEndChar($data->endChar);
//                            $label->save();
//                        }
//                    }
//                }
//            }
//
//            public function addCxn($data)
//            {
//                $transaction = $this->beginTransaction();
//                try {
//                    $cxn = Construction::create($data->idConstruction);
//                    $annotationSet = new AnnotationSet();
//                    $annotationSet->setIdSentence($data->idSentence);
//                    $annotationSet->setIdAnnotationStatus('ast_manual');
//                    $annotationSet->setIdEntityCxn($cxn->getIdEntity());
//                    $annotationSet->save();
//                    $annotationSet->createLayersForCxn($cxn, $data);
//                    $transaction->commit();
//                } catch (\Exception $ex) {
//                    $transaction->rollback();
//                    ddump($ex->getMessage());
//                    throw new \Exception($ex->getMessage());
//                }
//            }
//
//
//            public function createLayersForCxn($cxn, $data)
//            {
//                $layerType = new LayerType();
//                $layerTypes = $layerType->listToConstruction();
//                foreach ($layerTypes as $lt) {
//                    $layer = new Layer();
//                    $layer->setIdLayerType($lt['idLayerType']);
//                    $layer->setIdAnnotationSet($this->getId());
//                    $layer->setRank(1);
//                    $layer->save();
//                }
//            }
//
//            //    public function deleteBySubCorpus($idSubCorpus)
//            //    {
//            //        $transaction = $this->beginTransaction();
//            //        try {
//            //            $layer = new Layer();
//            //            $criteria = $this->listBySubCorpus($idSubCorpus);
//            //            $result = $criteria->asQuery()->getResult();
//            //            foreach ($result as $as) {
//            //                $idAS = $as['idAnnotationSet'];
//            //                $layer->deleteByAnnotationSet($idAS);
//            //                $deleteCriteria = $this->getDeleteCriteria()->where("idAnnotationSet = {$idAS}");
//            //                $deleteCriteria->delete();
//            //            }
//            //            $transaction->commit();
//            //        } catch (\Exception $e) {
//            //            $transaction->rollback();
//            //            throw new \Exception($e->getMessage());
//            //        }
//            //    }
//
//            public function delete()
//            {
//                $transaction = $this->beginTransaction();
//                try {
//                    $idAnnotationSet = $this->getId();
//                    $asComments = new ASComments();
//                    $asComments->deleteByAnnotationSet($idAnnotationSet);
//                    $layer = new Layer();
//                    $layer->deleteByAnnotationSet($idAnnotationSet);
//                    Timeline::addTimeline("annotationset", $this->getId(), "D");
//                    parent::delete();
//                    $transaction->commit();
//                } catch (\Exception $e) {
//                    $transaction->rollback();
//                    throw new \Exception($e->getMessage());
//                }
//            }
//
//            */
}
