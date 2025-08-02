<?php

namespace App\Services;

use App\Data\Comment\CommentData;
use App\Database\Criteria;
use App\Repositories\Document;

class CommentService
{
    /*
     * DynamicObject
     */

    public static function getDynamicObjectComment(int $idDynamicObject): object|null
    {
        $do = Criteria::table("dynamicobject as do")
            ->leftJoin("annotationcomment as ac", "do.idDynamicObject", "=", "ac.idDynamicObject")
            ->leftJoin("user as u", "ac.idUser", "=", "u.idUser")
            ->where("do.idDynamicObject", $idDynamicObject)
            ->select("do.idDynamicObject", "do.startFrame", "do.endFrame", "ac.comment", "ac.createdAt", "ac.updatedAt", "u.email")
            ->first();
        return $do;
    }

    public static function deleteDynamicObjectComment(int $idDocument, int $idDynamicObject): void
    {
        $comment = Criteria::byId("annotationcomment", "idDynamicObject", $idDynamicObject);
        if (!is_null($comment)) {
            $document = Document::byId($idDocument);
            $idProject = Criteria::table("view_project_docs as pd")
                ->where("pd.idDocument", $idDocument)
                ->first()->idProject;
            $users = Criteria::table("user as u")
                ->join("project_manager as pm", "u.idUser", "=", "pm.idUser")
                ->select("u.idUser", "u.email")
                ->where("pm.idProject", $idProject)
                ->get()->pluck("idUser")->all();
            $users[] = $idUserCurrent = AppService::getCurrentIdUser();
            $users[] = $comment->idUser;
            Criteria::deleteById("annotationcomment", "idDynamicObject", $idDynamicObject);
            $link = "<a href=\"/annotation/dynamicMode/{$idDocument}/{$idDynamicObject}\">[#{$idDynamicObject}]</a>.";
            foreach($users as $idUser) {
                if ($idUser != $idUserCurrent) {
                    MessageService::sendMessage((object)[
                        'idUserFrom' => $idUserCurrent,
                        'idUserTo' => $idUser,
                        'class' => 'error',
                        'text' => "Comment deleted at document [{$document->name}] object {$link}.",
                    ]);
                }
            }
        }
    }

    public static function updateDynamicObjectComment(CommentData $data): int
    {
        $idDynamicObject = $data->idDynamicObject;
        $document = Document::byId($data->idDocument);
        $idProject = Criteria::table("view_project_docs as pd")
            ->where("pd.idDocument", $data->idDocument)
            ->first()->idProject;
        $users = Criteria::table("user as u")
            ->join("project_manager as pm", "u.idUser", "=", "pm.idUser")
            ->select("u.idUser", "u.email")
            ->where("pm.idProject", $idProject)
            ->get()->pluck("idUser")->all();
        $users[] = AppService::getCurrentIdUser();
        $comment = Criteria::byId("annotationcomment", "idDynamicObject", $idDynamicObject);
        if (is_null($comment)) {
            Criteria::create("annotationcomment", [
                "idDynamicObject" => $idDynamicObject,
                "comment" => $data->comment,
                "idUser" => $data->idUser,
                "createdAt" => $data->createdAt,
                "updatedAt" => $data->updatedAt,
            ]);
        } else {
            $users[] = $comment->idUser;
            Criteria::table("annotationcomment")
                ->where("idDynamicObject", $idDynamicObject)
                ->update([
                    "comment" => $data->comment,
                    "updatedAt" => $data->updatedAt,
                ]);
        }
        $idUserCurrent = AppService::getCurrentIdUser();
        $link = "<a href=\"/annotation/dynamicMode/{$document->idDocument}/{$idDynamicObject}\">[#{$idDynamicObject}]</a>.";
        foreach($users as $idUser) {
            if ($idUser != $idUserCurrent) {
                MessageService::sendMessage((object)[
                    'idUserFrom' => $idUserCurrent,
                    'idUserTo' => $idUser,
                    'class' => 'warning',
                    'text' => "Comment created/updated at document [{$document->name}] object {$link}.",
                ]);
            }
        }
        return $idDynamicObject;
    }

    /*
     * StaticObject
     */

    public static function getStaticObjectComment(int $idStaticObject): object|null
    {
        $do = Criteria::table("staticobject as so")
            ->leftJoin("annotationcomment as ac", "so.idStaticObject", "=", "ac.idStaticObject")
            ->leftJoin("user as u", "ac.idUser", "=", "u.idUser")
            ->where("so.idStaticObject", $idStaticObject)
            ->select("so.idStaticObject", "ac.comment", "ac.createdAt", "ac.updatedAt", "u.email")
            ->first();
        return $do;
    }

    public static function deleteStaticObjectComment(int $idDocument, int $idStaticObject): void
    {
        $comment = Criteria::byId("annotationcomment", "idStaticObject", $idStaticObject);
        if (!is_null($comment)) {
            $document = Document::byId($idDocument);
            $idProject = Criteria::table("view_project_docs as pd")
                ->where("pd.idDocument", $idDocument)
                ->first()->idProject;
            $users = Criteria::table("user as u")
                ->join("project_manager as pm", "u.idUser", "=", "pm.idUser")
                ->select("u.idUser", "u.email")
                ->where("pm.idProject", $idProject)
                ->get()->pluck("idUser")->all();
            $users[] = $idUserCurrent = AppService::getCurrentIdUser();
            $users[] = $comment->idUser;
            Criteria::deleteById("annotationcomment", "idStaticObject", $idStaticObject);
            $link = "<a href=\"/annotation/staticBBox/{$idDocument}/{$idStaticObject}\">[#{$idStaticObject}]</a>.";
            foreach($users as $idUser) {
                if ($idUser != $idUserCurrent) {
                    MessageService::sendMessage((object)[
                        'idUserFrom' => $idUserCurrent,
                        'idUserTo' => $idUser,
                        'class' => 'error',
                        'text' => "Comment deleted at document [{$document->name}] object {$link}.",
                    ]);
                }
            }
        }
    }

    public static function updateStaticObjectComment(CommentData $data): int
    {
        $idStaticObject = $data->idStaticObject;
        $document = Document::byId($data->idDocument);
        $idProject = Criteria::table("view_project_docs as pd")
            ->where("pd.idDocument", $data->idDocument)
            ->first()->idProject;
        $users = Criteria::table("user as u")
            ->join("project_manager as pm", "u.idUser", "=", "pm.idUser")
            ->select("u.idUser", "u.email")
            ->where("pm.idProject", $idProject)
            ->get()->pluck("idUser")->all();
        $users[] = AppService::getCurrentIdUser();
        $comment = Criteria::byId("annotationcomment", "idStaticObject", $idStaticObject);
        if (is_null($comment)) {
            Criteria::create("annotationcomment", [
                "idStaticObject" => $idStaticObject,
                "comment" => $data->comment,
                "idUser" => $data->idUser,
                "createdAt" => $data->createdAt,
                "updatedAt" => $data->updatedAt,
            ]);
        } else {
            $users[] = $comment->idUser;
            Criteria::table("annotationcomment")
                ->where("idStaticObject", $idStaticObject)
                ->update([
                    "comment" => $data->comment,
                    "updatedAt" => $data->updatedAt,
                ]);
        }
        $idUserCurrent = AppService::getCurrentIdUser();
        $link = "<a href=\"/annotation/staticBBox/{$document->idDocument}/{$idStaticObject}\">[#{$idStaticObject}]</a>.";
        foreach($users as $idUser) {
            if ($idUser != $idUserCurrent) {
                MessageService::sendMessage((object)[
                    'idUserFrom' => $idUserCurrent,
                    'idUserTo' => $idUser,
                    'class' => 'warning',
                    'text' => "Comment created/updated at document [{$document->name}] object {$link}.",
                ]);
            }
        }
        return $idStaticObject;
    }

    /*
     * StaticObject
     */

    public static function getAnnotationSetComment(int $idAnnotationSet): object|null
    {
        $do = Criteria::table("annotationset as aset")
            ->leftJoin("annotationcomment as ac", "aset.idAnnotationSet", "=", "ac.idAnnotationSet")
            ->leftJoin("user as u", "ac.idUser", "=", "u.idUser")
            ->where("aset.idAnnotationSet", $idAnnotationSet)
            ->select("aset.idAnnotationSet", "ac.comment", "ac.createdAt", "ac.updatedAt", "u.email")
            ->first();
        return $do;
    }

    public static function deleteAnnotationSetComment(int $idAnnotationSet): void
    {
        $annotationSet = Criteria::byId("view_annotationset","idAnnotationSet", $idAnnotationSet);
        $document = Document::byId($annotationSet->idDocument);
        $comment = Criteria::byId("annotationcomment", "idAnnotationSet", $idAnnotationSet);
        if (!is_null($comment)) {
            $idProject = Criteria::table("view_project_docs as pd")
                ->where("pd.idDocument", $document->idDocument)
                ->first()->idProject;
            $users = Criteria::table("user as u")
                ->join("project_manager as pm", "u.idUser", "=", "pm.idUser")
                ->select("u.idUser", "u.email")
                ->where("pm.idProject", $idProject)
                ->get()->pluck("idUser")->all();
            $users[] = $idUserCurrent = AppService::getCurrentIdUser();
            $users[] = $comment->idUser;
            Criteria::deleteById("annotationcomment", "idAnnotationSet", $idAnnotationSet);
            $link = "<a href=\"/annotation/fullText/sentence/{$annotationSet->idDocumentSentence}/{$idAnnotationSet}\">[#{$idAnnotationSet}]</a>.";
            foreach($users as $idUser) {
                if ($idUser != $idUserCurrent) {
                    MessageService::sendMessage((object)[
                        'idUserFrom' => $idUserCurrent,
                        'idUserTo' => $idUser,
                        'class' => 'error',
                        'text' => "Comment deleted at document [{$document->name}] AnnotationSet {$link}.",
                    ]);
                }
            }
        }
    }

    public static function updateAnnotationSetComment(CommentData $data): int
    {
        $idAnnotationSet = $data->idAnnotationSet;
        $annotationSet = Criteria::byId("view_annotationset","idAnnotationSet", $idAnnotationSet);
        $document = Document::byId($annotationSet->idDocument);
        $idProject = Criteria::table("view_project_docs as pd")
            ->where("pd.idDocument", $document->idDocument)
            ->first()->idProject;
        $users = Criteria::table("user as u")
            ->join("project_manager as pm", "u.idUser", "=", "pm.idUser")
            ->select("u.idUser", "u.email")
            ->where("pm.idProject", $idProject)
            ->get()->pluck("idUser")->all();
        $users[] = AppService::getCurrentIdUser();
        $comment = Criteria::byId("annotationcomment", "idAnnotationSet", $idAnnotationSet);
        if (is_null($comment)) {
            Criteria::create("annotationcomment", [
                "idAnnotationSet" => $idAnnotationSet,
                "comment" => $data->comment,
                "idUser" => $data->idUser,
                "createdAt" => $data->createdAt,
                "updatedAt" => $data->updatedAt,
            ]);
        } else {
            $users[] = $comment->idUser;
            Criteria::table("annotationcomment")
                ->where("idAnnotationSet", $idAnnotationSet)
                ->update([
                    "comment" => $data->comment,
                    "updatedAt" => $data->updatedAt,
                ]);
        }
        $idUserCurrent = AppService::getCurrentIdUser();
        $link = "<a href=\"/annotation/fullText/sentence/{$annotationSet->idDocumentSentence}/{$idAnnotationSet}\">[#{$idAnnotationSet}]</a>.";
        foreach($users as $idUser) {
            if ($idUser != $idUserCurrent) {
                MessageService::sendMessage((object)[
                    'idUserFrom' => $idUserCurrent,
                    'idUserTo' => $idUser,
                    'class' => 'warning',
                    'text' => "Comment created/updated at document [{$document->name}] AnnotationSet {$link}.",
                ]);
            }
        }
        return $idAnnotationSet;
    }

}
