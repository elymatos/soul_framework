<?php

namespace App\Services\Annotation;

use App\Data\Annotation\Dynamic\CreateObjectData;
use App\Data\Annotation\Dynamic\ObjectFrameData;
use App\Data\Annotation\Dynamic\CloneData;
use App\Data\Annotation\Dynamic\CreateBBoxData;
use App\Data\Annotation\Dynamic\ObjectAnnotationData;
use App\Data\Annotation\Dynamic\ObjectData;
use App\Data\Annotation\Dynamic\SentenceData;
use App\Data\Annotation\Dynamic\UpdateBBoxData;
use App\Data\Annotation\Dynamic\WordData;
use App\Database\Criteria;
use App\Repositories\AnnotationSet;
use App\Repositories\Task;
use App\Repositories\Timeline;
use App\Repositories\User;
use App\Repositories\Video;
use App\Services\AppService;
use App\Services\CommentService;
use Illuminate\Support\Facades\DB;


class DynamicService
{
    private static function deleteBBoxesByDynamicObject(int $idDynamicObject)
    {
        $bboxes = Criteria::table("view_dynamicobject_boundingbox as db")
            ->where("db.idDynamicObject", $idDynamicObject)
            ->select("db.idBoundingBox")
            ->chunkResult("idBoundingBox", "idBoundingBox");
        Criteria::table("dynamicobject_boundingbox")
            ->whereIn("idBoundingBox", $bboxes)
            ->delete();
        Criteria::table("boundingbox")
            ->whereIn("idBoundingBox", $bboxes)
            ->delete();
    }

    public static function getObject(int $idDynamicObject): object|null
    {
        $idLanguage = AppService::getCurrentIdLanguage();
        $object = Criteria::table("view_annotation_dynamic as ad")
            ->leftJoin("frameelement as fe", "ad.idFrameElement", "=", "fe.idFrameElement")
            ->leftJoin("color", "fe.idColor", "=", "color.idColor")
            ->where("ad.idLanguage", "left", $idLanguage)
            ->where("ad.idDynamicObject", $idDynamicObject)
            ->select("ad.idDynamicObject", "ad.name", "ad.startFrame", "ad.endFrame", "ad.startTime", "ad.endTime", "ad.status", "ad.origin",
                "ad.idAnnotationLU", "ad.idLU", "ad.lu", "ad.idAnnotationFE", "ad.idFrameElement", "ad.idFrame", "ad.frame", "ad.fe", "color.rgbBg", "color.rgbFg", "ad.idLanguage",
                "ad.idDocument")
            ->selectRaw("'Single_layer' as nameLayerType")
            ->first();
        if (!is_null($object)) {
            $object->comment = CommentService::getDynamicObjectComment($idDynamicObject);
            $object->textComment = $object->comment?->comment;
            $object->name = "";
            $object->bgColor = "white";
            $object->fgColor = "black";
            if ($object->lu != '') {
                $object->name .= $object->lu;
            }
            if ($object->fe != '') {
                $object->bgColor = "#{$object->rgbBg}";
                $object->fgColor = "#{$object->rgbFg}";
                $object->name .= ($object->name != "" ? " | " : "") . $object->frame . "." . $object->fe;
            }
//            $object->bboxes = Criteria::table("view_dynamicobject_boundingbox")
//                ->where("idDynamicObject", $idDynamicObject)
//                ->orderBy("frameNumber")
//                ->all();
            $countBBoxes = Criteria::table("view_dynamicobject_boundingbox")
                ->where("idDynamicObject", $idDynamicObject)
                ->count();
            $object->hasBBoxes = ($countBBoxes > 0);
        }
        return $object;
    }

    public static function getObjectsByDocument(int $idDocument): array
    {
        $idLanguage = AppService::getCurrentIdLanguage();
        $result = Criteria::table("view_annotation_dynamic as ad")
            ->leftJoin("view_lu", "ad.idLu", "=", "view_lu.idLU")
            ->leftJoin("frameelement as fe", "ad.idFrameElement", "=", "fe.idFrameElement")
            ->leftJoin("color", "fe.idColor", "=", "color.idColor")
            ->leftJoin("view_frame", "view_lu.idFrame", "=", "view_frame.idFrame")
            ->leftJoin("annotationcomment as ac", "ad.idDynamicObject", "=", "ac.idDynamicObject")
            ->where("ad.idLanguage", "left", $idLanguage)
            ->where("ad.idDocument", $idDocument)
            ->where("view_frame.idLanguage", "left", $idLanguage)
            ->select("ad.idDynamicObject", "ad.name", "startFrame", "endFrame", "startTime", "endTime", "status", "origin",
                "idAnnotationLU", "ad.idLU", "lu", "view_lu.name as luName", "view_frame.name as luFrameName",
                "idAnnotationFE", "idFrameElement", "ad.idFrame", "frame", "fe", "color.rgbBg", "color.rgbFg", "ac.comment")
            ->orderBy("startFrame")
            ->orderBy("endFrame")
            ->orderBy("ad.idDynamicObject")
            ->all();
        $oMM = [];
        $bboxes = [];
        foreach ($result as $row) {
            $oMM[] = $row->idDynamicObject;
        }
        if (count($result) > 0) {
            $bboxList = Criteria::table("view_dynamicobject_boundingbox")
                ->whereIN("idDynamicObject", $oMM)
                ->all();
            foreach ($bboxList as $bbox) {
                $bboxes[$bbox->idDynamicObject][] = $bbox;
            }
        }
        $objects = [];
        foreach ($result as $i => $row) {
            $row->order = $i + 1;
            $row->bboxes = $bboxes[$row->idDynamicObject] ?? [];
            $objects[] = $row;
        }
        return $objects;
    }

    public static function updateObjectAnnotation(ObjectAnnotationData $data): int
    {
        $usertask = Task::getCurrentUserTask($data->idDocument);
        $do = Criteria::byId("dynamicobject", "idDynamicObject", $data->idDynamicObject);
        Criteria::deleteById("annotation", "idDynamicObject", $do->idDynamicObject);
        if ($data->idFrameElement) {
            $fe = Criteria::byId("frameelement", "idFrameElement", $data->idFrameElement);
            $json = json_encode([
                'idEntity' => $fe->idEntity,
                'idDynamicObject' => $do->idDynamicObject,
                'idUserTask' => $usertask->idUserTask
            ]);
            $idAnnotation = Criteria::function("annotation_create(?)", [$json]);
            Timeline::addTimeline("annotation", $idAnnotation, "C");
        }
        if ($data->idLU) {
            $lu = Criteria::byId("lu", "idLU", $data->idLU);
            $json = json_encode([
                'idEntity' => $lu->idEntity,
                'idDynamicObject' => $do->idDynamicObject,
                'idUserTask' => $usertask->idUserTask
            ]);
            $idAnnotation = Criteria::function("annotation_create(?)", [$json]);
            Timeline::addTimeline("annotation", $idAnnotation, "C");
        }
//        if (($data->startFrame) && ($data->endFrame)) {
//            $idUser = AppService::getCurrentIdUser();
//            $bboxes = Criteria::table("view_dynamicobject_boundingbox")
//                ->where("idDynamicObject", $data->idDynamicObject)
//                ->orderBy("frameNumber")
//                ->all();
//            $iFirst = array_key_first($bboxes);
//            $firstBBox = $bboxes[$iFirst];
//            $isBlocked = $data->isBlocked;
//            // update first bbox because the status of blocked
//            Criteria::table("boundingbox")
//                ->where("idBoundingBox", $firstBBox->idBoundingBox)
//                ->update(["blocked" => $isBlocked]);
//            //
//            if ($data->startFrame >= $firstBBox->frameNumber) {
//                $iLast = array_key_last($bboxes);
//                $lastBBox = $bboxes[$iLast];
//                $lastFrame = $lastBBox->frameNumber;
//                foreach ($bboxes as $bbox) {
//                    if ($bbox->frameNumber < $data->startFrame) {
//                        $idBoundingBox = Criteria::function("boundingbox_dynamic_delete(?,?)", [$bbox->idBoundingBox, $idUser]);
//                    } else if ($bbox->frameNumber > $data->endFrame) {
//                        $idBoundingBox = Criteria::function("boundingbox_dynamic_delete(?,?)", [$bbox->idBoundingBox, $idUser]);
//                    }
//                }
//                if ($lastFrame < $data->endFrame) {
//                    for ($i = ($lastFrame + 1); $i <= $data->endFrame; $i++) {
//                        $json = json_encode([
//                            'frameNumber' => $i,
//                            'frameTime' => ($i - 1) * 0.04,
//                            'x' => (int)$lastBBox->x,
//                            'y' => (int)$lastBBox->y,
//                            'width' => (int)$lastBBox->width,
//                            'height' => (int)$lastBBox->height,
//                            'blocked' => (int)$isBlocked, //(int)$lastBBox->blocked,
//                            'idDynamicObject' => $data->idDynamicObject
//                        ]);
//                        $idBoundingBox = Criteria::function("boundingbox_dynamic_create(?)", [$json]);
//                    }
//                }
//                Criteria::table("dynamicobject")
//                    ->where("idDynamicObject", $data->idDynamicObject)
//                    ->update([
//                        "startFrame" => $data->startFrame,
//                        "endFrame" => $data->endFrame
//                    ]);
//            } else {
//                throw new \Exception("First BBox must be created mannualy.");
//            }
//        }
        return $data->idDynamicObject;
    }

    public static function updateObject(ObjectData $data): int
    {
        $idUser = AppService::getCurrentIdUser();
        // if idDynamicObject = null : object create
        if (is_null($data->idDynamicObject)) {
            $do = json_encode([
                'name' => $data->name,
                'startFrame' => (int)$data->startFrame,
                'endFrame' => (int)$data->endFrame,
                'startTime' => (float)$data->startTime,
                'endTime' => (float)$data->endTime,
                'status' => (int)$data->status,
                'origin' => (int)$data->origin,
                'idUser' => $idUser
            ]);
            $idDynamicObject = Criteria::function("dynamicobject_create(?)", [$do]);
            $dynamicObject = Criteria::byId("dynamicobject", "idDynamicObject", $idDynamicObject);
            $documentVideo = Criteria::table("view_document_video")
                ->where("idDocument", $data->idDocument)
                ->first();
            $video = Video::byId($documentVideo->idVideo);
            // create relation video_dynamicobject
            Criteria::create("video_dynamicobject", [
                "idVideo" => $video->idVideo,
                "idDynamicObject" => $idDynamicObject,
            ]);
            if (count($data->frames)) {
                foreach ($data->frames as $frame) {
                    $json = json_encode([
                        'frameNumber' => (int)$frame['frameNumber'],
                        'frameTime' => (float)$frame['frameTime'],
                        'x' => (int)$frame['x'],
                        'y' => (int)$frame['y'],
                        'width' => (int)$frame['width'],
                        'height' => (int)$frame['height'],
                        'blocked' => (int)$frame['blocked'],
                        'idDynamicObject' => (int)$idDynamicObject
                    ]);
                    $idBoundingBox = Criteria::function("boundingbox_dynamic_create(?)", [$json]);
                }
            }
        } else {
            // if idDynamicObject != null : update object and  boundingboxes
            $idDynamicObject = $data->idDynamicObject;
            Criteria::table("dynamicobject")
                ->where("idDynamicObject", $idDynamicObject)
                ->update([
                    'startFrame' => $data->startFrame,
                    'endFrame' => $data->endFrame,
                    'startTime' => $data->startTime,
                    'endTime' => $data->endTime,
                ]);
        }
        return $idDynamicObject;
    }

    public static function cloneObject(CloneData $data): int
    {
        $idUser = AppService::getCurrentIdUser();
        $idDynamicObject = $data->idDynamicObject;
        $do = self::getObject($idDynamicObject);
        $clone = json_encode([
            'name' => $do->name,
            'startFrame' => (int)$do->startFrame,
            'endFrame' => (int)$do->endFrame,
            'startTime' => (float)$do->startTime,
            'endTime' => (float)$do->endTime,
            'status' => (int)$do->status,
            'origin' => (int)$do->origin,
            'idUser' => $idUser
        ]);
        $idDynamicObjectClone = Criteria::function("dynamicobject_create(?)", [$clone]);
        $dynamicObjectClone = Criteria::byId("dynamicobject", "idDynamicObject", $idDynamicObjectClone);
        $documentVideo = Criteria::table("view_document_video")
            ->where("idDocument", $data->idDocument)
            ->first();
        $video = Video::byId($documentVideo->idVideo);
        // create relation video_dynamicobject
        Criteria::create("video_dynamicobject", [
            "idVideo" => $video->idVideo,
            "idDynamicObject" => $idDynamicObjectClone,
        ]);
        // cloning bboxes
        $bboxes = Criteria::table("view_dynamicobject_boundingbox")
            ->where("idDynamicObject", $idDynamicObject)
            ->all();
        foreach ($bboxes as $bbox) {
            $json = json_encode([
                'frameNumber' => (int)$bbox->frameNumber,
                'frameTime' => (float)$bbox->frameTime,
                'x' => (int)$bbox->x,
                'y' => (int)$bbox->y,
                'width' => (int)$bbox->width,
                'height' => (int)$bbox->height,
                'blocked' => (int)$bbox->blocked,
                'idDynamicObject' => (int)$idDynamicObjectClone
            ]);
            $idBoundingBox = Criteria::function("boundingbox_dynamic_create(?)", [$json]);
        }
        return $idDynamicObjectClone;
    }

    public static function deleteObject(int $idDynamicObject): void
    {
        // se pode remover o objeto se for Manager da task ou se for o criador do objeto
        $dynamicObjectAnnotation = Criteria::byId("view_annotation_dynamic", "idDynamicObject", $idDynamicObject);
        $taskManager = Task::getTaskManager($dynamicObjectAnnotation->idDocument);
        $idUser = AppService::getCurrentIdUser();
        $user = User::byId($idUser);
        if (!User::isManager($user)) {
            if ($taskManager->idUser != $idUser) {
                $tl = Criteria::table("timeline")
                    ->where("tablename", "dynamicobject")
                    ->where("id", $idDynamicObject)
                    ->select("idUser")
                    ->first();
                if ($tl->idUser != $idUser) {
                    throw new \Exception("Object can not be removed.");
                }
            }
        }
        DB::transaction(function () use ($idDynamicObject) {
            self::deleteBBoxesByDynamicObject($idDynamicObject);
            $idUser = AppService::getCurrentIdUser();
            Criteria::function("dynamicobject_delete(?,?)", [$idDynamicObject, $idUser]);
        });
    }

    public static function updateBBox(UpdateBBoxData $data): int
    {
        Criteria::table("boundingbox")
            ->where("idBoundingBox", $data->idBoundingBox)
            ->update($data->bbox);
        return $data->idBoundingBox;
    }

    public static function createBBox(CreateBBoxData $data): int
    {
        $boundingBox = Criteria::table("dynamicobject_boundingbox as dbb")
            ->join("boundingbox as bb", "dbb.idBoundingBox", "=", "bb.idBoundingBox")
            ->where("dbb.idDynamicObject", $data->idDynamicObject)
            ->where("bb.frameNumber", $data->frameNumber)
            ->first();
        if ($boundingBox) {
            Criteria::function("boundingbox_dynamic_delete(?,?)", [$boundingBox->idBoundingBox, AppService::getCurrentIdUser()]);;
        }
        $dynamicObject = Criteria::byId("dynamicobject", "idDynamicObject", $data->idDynamicObject);
        if ($dynamicObject->endFrame < $data->frameNumber) {
            Criteria::table("dynamicobject")
                ->where("idDynamicObject", $data->idDynamicObject)
                ->update(['endFrame' => $data->frameNumber]);
        }
        $json = json_encode([
            'frameNumber' => (int)$data->frameNumber,
            'frameTime' => $data->frameNumber * 0.04,
            'x' => (int)$data->bbox['x'],
            'y' => (int)$data->bbox['y'],
            'width' => (int)$data->bbox['width'],
            'height' => (int)$data->bbox['height'],
            'blocked' => (int)$data->bbox['blocked'],
            'idDynamicObject' => (int)$data->idDynamicObject
        ]);
        $idBoundingBox = Criteria::function("boundingbox_dynamic_create(?)", [$json]);
        return $idBoundingBox;
    }

    public static function listSentencesByDocument($idDocument): array
    {
        $sentences = Criteria::table("sentence")
            ->join("document_sentence as ds", "sentence.idSentence", "=", "ds.idSentence")
            ->join("view_sentence_timespan as st", "sentence.idSentence", "=", "st.idSentence")
            ->join("document as d", "ds.idDocument", "=", "d.idDocument")
            ->leftJoin("originmm as o", "sentence.idOriginMM", "=", "o.idOriginMM")
            ->where("d.idDocument", $idDocument)
            ->select("sentence.idSentence", "sentence.text", "ds.idDocumentSentence", "st.startTime", "st.endTime", "o.origin", "d.idDocument")
            ->orderBy("st.startTime")
            ->orderBy("st.endTime")
            ->limit(1000)
            ->get()->keyBy("idDocumentSentence")->all();
        if (!empty($sentences)) {
            $targets = collect(AnnotationSet::listTargetsForDocumentSentence(array_keys($sentences)))->groupBy('idDocumentSentence')->toArray();
            foreach ($targets as $idDocumentSentence => $spans) {
                $sentences[$idDocumentSentence]->text = self::decorateSentenceTarget($sentences[$idDocumentSentence]->text, $spans);
            }
        }
        return $sentences;
    }


    public static function decorateSentenceTarget($text, $spans)
    {
        $decorated = "";
        $i = 0;
        foreach ($spans as $span) {
            if ($span->startChar >= 0) {
                $decorated .= mb_substr($text, $i, $span->startChar - $i);
                $decorated .= "<span class='color_target' style='cursor:default' title='{$span->frameName}'>" . mb_substr($text, $span->startChar, $span->endChar - $span->startChar + 1) . "</span>";
                $i = $span->endChar + 1;
            }
        }
        $decorated = $decorated . mb_substr($text, $i);
        return $decorated;
    }

    public static function updateSentence(SentenceData $data): void
    {
        if ($data->idSentence > 0) {
            $sentence = Criteria::byId("view_sentence", "idSentence", $data->idSentence);
            // atualiza timespan associadp
            $timeSpan = Criteria::table("sentence_timespan")
                ->where("idSentence", $sentence->idSentence)
                ->first();
            if ($timeSpan) {
                Criteria::table("timespan")
                    ->where("idTimeSpan", $timeSpan->idTimeSpan)
                    ->update([
                        'startTime' => $data->startTime,
                        'endTime' => $data->endTime
                    ]);
                // atualiza sentence
                Criteria::table("sentence")
                    ->where("idSentence", $data->idSentence)
                    ->update([
                        'text' => trim($data->text),
                        'idOriginMM' => $data->idOriginMM
                    ]);
            }
        }
    }

    public static function createSentence(SentenceData $data): void
    {
        if ($data->idSentence == 0) {
            $idUser = AppService::getCurrentIdUser();
            $json = json_encode([
                'text' => trim($data->text),
                'idUser' => $idUser,
                'idDocument' => $data->idDocument,
                'idLanguage' => $data->idLanguage
            ]);
            $idSentence = Criteria::function("sentence_create(?)", [$json]);
            $sentence = Criteria::byId("view_sentence", "idSentence", $idSentence);
            Criteria::table("sentence")
                ->where("idSentence", $idSentence)
                ->update(['idOriginMM' => $data->idOriginMM]);
            $json = json_encode([
                'startTime' => (float)$data->startTime,
                'endTime' => (float)$data->endTime,
            ]);
            $idTimeSpan = Criteria::function("timespan_create(?)", [$json]);
            $timespan = Criteria::byId("timespan", "idTimeSpan", $idTimeSpan);
            // create relation sentence_timespan
            Criteria::create("sentence_timespan", [
                "idSentence" => $idSentence,
                "idTimeSpan" => $idTimeSpan,
            ]);
        }
    }

    public static function buildSentenceFromWords(WordData $data): int
    {
        $start = 100000;
        $end = 0;
        $text = '';
        $idWordMM = [];
        foreach ($data->words as $word) {
            $text .= $word->word . ' ';
            if ($word->startTime < $start) {
                $start = $word->startTime;
            }
            if ($word->endTime > $end) {
                $end = $word->endTime;
            }
            $idWordMM[] = $word->idWordMM;
        }
        debug($text, $start, $end, $idWordMM);
        $idUser = AppService::getCurrentIdUser();
        $json = json_encode([
            'text' => trim($text),
            'idUser' => $idUser,
            'idDocument' => $data->idDocument,
            'idLanguage' => $data->idLanguage
        ]);
        $idSentence = Criteria::function("sentence_create(?)", [$json]);
        $sentence = Criteria::byId("view_sentence", "idSentence", $idSentence);
        Criteria::table("sentence")
            ->where("idSentence", $idSentence)
            ->update(['idOriginMM' => 4]);
        $json = json_encode([
            'startTime' => (float)$start,
            'endTime' => (float)$end,
        ]);
        $idTimeSpan = Criteria::function("timespan_create(?)", [$json]);
        $timespan = Criteria::byId("timespan", "idTimeSpan", $idTimeSpan);
        // create relation sentence_timespan
        Criteria::create("sentence_timespan", [
            "idSentence" => $idSentence,
            "idTimeSpan" => $idTimeSpan,
        ]);
        $documentSentence = Criteria::table("document_sentence as ds")
            ->where("ds.idSentence", $idSentence)
            ->where("ds.idDocument", $data->idDocument)
            ->first();
        Criteria::table("wordmm")
            ->whereIn("idWordMM", $idWordMM)
            ->update([
                "idDocumentSentence" => $documentSentence->idDocumentSentence
            ]);
        return $idSentence;
    }

    public static function splitSentence(SentenceData $data): void
    {
        if ($data->idSentence > 0) {
            $idUser = AppService::getCurrentIdUser();
            Criteria::function("sentence_delete(?,?)", [$data->idSentence, $idUser]);
        }
    }

    public static function createNewObjectAtLayer(CreateObjectData $data): object
    {
        $idUser = AppService::getCurrentIdUser();
        $do = json_encode([
            'name' => "",
            'startFrame' => $data->startFrame,
            'endFrame' => $data->endFrame,
            'startTime' => ($data->startFrame - 1) * 0.040,
            'endTime' => ($data->endFrame) * 0.040,
            'status' => 0,
            'origin' => 2,
            'idUser' => $idUser
        ]);
        $idDynamicObject = Criteria::function("dynamicobject_create(?)", [$do]);
        $dynamicObject = Criteria::byId("dynamicobject", "idDynamicObject", $idDynamicObject);
        $dynamicObject->idDocument = $data->idDocument;
        Criteria::table("dynamicobject")
            ->where("idDynamicObject", $idDynamicObject)
            ->update(['idLayerType' => $data->idLayerType]);
        $documentVideo = Criteria::table("view_document_video")
            ->where("idDocument", $data->idDocument)
            ->first();
        $video = Video::byId($documentVideo->idVideo);
        // create relation video_dynamicobject
        Criteria::create("video_dynamicobject", [
            "idVideo" => $video->idVideo,
            "idDynamicObject" => $idDynamicObject,
        ]);
        return $dynamicObject;
    }

    public static function getLayersByDocument(int $idDocument): array
    {
        $idLanguage = AppService::getCurrentIdLanguage();
        $objects = Criteria::table("view_annotation_dynamic as ad")
            ->leftJoin("view_lu", "ad.idLu", "=", "view_lu.idLU")
            ->leftJoin("frameelement as fe", "ad.idFrameElement", "=", "fe.idFrameElement")
            ->leftJoin("color", "fe.idColor", "=", "color.idColor")
            ->leftJoin("view_frame", "view_lu.idFrame", "=", "view_frame.idFrame")
            ->leftJoin("annotationcomment as ac", "ad.idDynamicObject", "=", "ac.idDynamicObject")
            ->where("ad.idLanguage", "left", $idLanguage)
            ->where("ad.idDocument", $idDocument)
            ->where("view_frame.idLanguage", "left", $idLanguage)
            ->select("ad.idDynamicObject", "ad.name", "ad.startFrame", "ad.endFrame", "ad.startTime", "ad.endTime", "ad.status", "ad.origin",
                "ad.idAnnotationLU", "ad.idLU", "lu", "view_lu.name as luName", "view_frame.name as luFrameName", "idAnnotationFE", "ad.idFrameElement", "ad.idFrame", "ad.frame", "ad.fe",
                "color.rgbFg", "color.rgbBg", "ac.comment as textComment")
            ->orderBy("ad.startFrame")
            ->orderBy("ad.endFrame")
            ->orderBy("ad.idDynamicObject")
            ->keyBy("idDynamicObject")
            ->all();
        $bboxes = [];
        $idDynamicObjectList = array_keys($objects);
        if (count($idDynamicObjectList) > 0) {
            $bboxList = Criteria::table("view_dynamicobject_boundingbox")
                ->whereIN("idDynamicObject", $idDynamicObjectList)
                ->all();
            foreach ($bboxList as $bbox) {
                $bboxes[$bbox->idDynamicObject][] = $bbox;
            }
        }
        $order = 0;
        foreach ($objects as $object) {
            $object->order = ++$order;
            $object->startTime = (int)($object->startTime * 1000);
            $object->endTime = (int)($object->endTime * 1000);
            $object->bboxes = $bboxes[$object->idDynamicObject] ?? [];
            $object->name = "";
            $object->bgColor = "white";
            $object->fgColor = "black";
            if ($object->lu != '') {
                $object->name .= $object->lu;
            }
            if ($object->fe != '') {
                $object->bgColor = "#{$object->rgbBg}";
                $object->fgColor = "#{$object->rgbFg}";
                $object->name .= ($object->name != "" ? " | " : "") . $object->frame . "." . $object->fe;
            }
        }
        $objectsRows = [];
        $objectsRowsEnd = [];
        // Para manter o paralelismo com a Deixis annotation,
        // estou considerando que todos os objetos estão num "layer fictício", com idLayerType = 0 e idLabel (idLayer) = 0
        $idLayerTypeCurrent = -1;
        $idLayerType = 0;
        foreach ($objects as $i => $object) {
            if ($idLayerType != $idLayerTypeCurrent) {
                $idLayerTypeCurrent = $idLayerType;
                $objectsRows[$idLayerType][0][] = $object;
                $objectsRowsEnd[$idLayerType][0] = $object->endFrame;
            } else {
                $allocated = false;
                foreach ($objectsRows[$idLayerType] as $idLayer => $objectRow) {
                    if ($object->startFrame > $objectsRowsEnd[$idLayerType][$idLayer]) {
                        $objectsRows[$idLayerType][$idLayer][] = $object;
                        $objectsRowsEnd[$idLayerType][$idLayer] = $object->endFrame;
                        $allocated = true;
                        break;
                    }
                }
                if (!$allocated) {
                    $idLayer = count($objectsRows[$idLayerType]);
                    $objectsRows[$idLayerType][$idLayer][] = $object;
                    $objectsRowsEnd[$idLayerType][$idLayer] = $object->endFrame;
                }
            }
        }

        $result = [];
        foreach ($objectsRows as $layers) {
            foreach ($layers as $objects) {
                $result[] = [
                    'layer' => 'Single_layer',
                    'objects' => $objects
                ];
            }
        }
        return $result;
    }

    public static function updateObjectFrame(ObjectFrameData $data): int
    {
        $object = self::getObject($data->idDynamicObject);
        $object->bboxes = Criteria::table("view_dynamicobject_boundingbox")
            ->where("idDynamicObject", $data->idDynamicObject)
            ->orderBy("frameNumber")
            ->all();
        debug($object->bboxes);
        if (!empty($object->bboxes)) {
            $frameFirstBBox = $object->bboxes[0]->frameNumber;
            // se o novo startFrame for menor que o atual, remove todas as bboxes
            if ($data->startFrame < $frameFirstBBox) {
                self::deleteBBoxesByDynamicObject($data->idDynamicObject);
            } else {
                $idUser = AppService::getCurrentIdUser();
                // remove as bboxes em frames menores que o newStartFrame
                $bboxes = Criteria::table("view_dynamicobject_boundingbox")
                    ->where("idDynamicObject", $data->idDynamicObject)
                    ->where("frameNumber", "<", $data->startFrame)
                    ->chunkResult("idBoundingBox", "idBoundingBox");
                foreach ($bboxes as $idBoundingBox) {
                    Criteria::function("boundingbox_dynamic_delete(?,?)", [$idBoundingBox, $idUser]);
                }
                // remove as bboxes em frames maiores que o newEndFrame
                $bboxes = Criteria::table("view_dynamicobject_boundingbox")
                    ->where("idDynamicObject", $data->idDynamicObject)
                    ->where("frameNumber", ">", $data->endFrame)
                    ->chunkResult("idBoundingBox", "idBoundingBox");
                foreach ($bboxes as $idBoundingBox) {
                    Criteria::function("boundingbox_dynamic_delete(?,?)", [$idBoundingBox, $idUser]);
                }
            }
        }
        Criteria::table("dynamicobject")
            ->where("idDynamicObject", $data->idDynamicObject)
            ->update([
                'startFrame' => $data->startFrame,
                'endFrame' => $data->endFrame,
                'startTime' => $data->startTime,
                'endTime' => $data->endTime,
            ]);
        return $data->idDynamicObject;
    }

    public static function deleteBBoxesFromObject(int $idDynamicObject): int
    {
        $idUser = AppService::getCurrentIdUser();
        $bboxes = Criteria::table("view_dynamicobject_boundingbox")
            ->where("idDynamicObject", $idDynamicObject)
            ->chunkResult("idBoundingBox", "idBoundingBox");
        foreach ($bboxes as $idBoundingBox) {
            Criteria::function("boundingbox_dynamic_delete(?,?)", [$idBoundingBox, $idUser]);
        }
        return $idDynamicObject;
    }


}
