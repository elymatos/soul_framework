<?php

namespace App\Repositories;

use App\Database\Criteria;
use App\Services\AppService;

class Task
{
    public static function byId(int $id): object
    {
        return Criteria::byFilter("task", ["idTask","=", $id])->first();
    }

    public static function listUsersToGrid(string $user): array
    {
        $criteria = Criteria::table("user")
            ->join("usertask","user.idUser","=","usertask.idUser")
            ->join("task","usertask.idTask","=","task.idTask")
            ->distinct()
            ->select('usertask.idTask', 'user.idUser','usertask.idUserTask','user.name','user.email')
//            ->selectRaw("concat('#', user.idUser,'  ', user.name, ' [', user.email,']') as name")
            ->orderBy('task.name')
            ->orderBy('user.name');
        $criteria->orWhere('user.name', 'startswith', $user);
        return $criteria->get()->groupBy('idTask')->toArray();
    }

    public static function listToGrid(string $name = ''): array
    {
        return Criteria::table("task")
            ->select('idTask','name')
            ->where('name', 'startswith', $name)
            ->orderBy('name')
            ->keyBy('idTask')
            ->all();
    }

    public static function getCurrentUserTask(int $idDocument): object|null
    {
        $document = Document::byId($idDocument);
        $idUser = AppService::getCurrentIdUser();
        $user = User::byId($idUser);
        // get usertask for this document
        $usertask = Criteria::table("usertask_document")
            ->join("usertask as ut", "ut.idUserTask", "=", "usertask_document.idUserTask")
            ->where("usertask_document.idDocument", $idDocument)
            ->where("ut.idUser", $idUser)
            ->select("ut.idUserTask", "ut.idTask")
            ->first();
        if (empty($usertask)) { // try to get for Corpus
            $usertask = Criteria::table("usertask_document")
                ->join("usertask as ut", "ut.idUserTask", "=", "usertask_document.idUserTask")
                ->where("usertask_document.idCorpus", $document->idCorpus)
                ->where("ut.idUser", $idUser)
                ->select("ut.idUserTask", "ut.idTask")
                ->first();
            if (empty($usertask)) { // check privileges
                if (User::isManager($user) || User::isMemberOf($user, 'MASTER')) {
                    $usertask = Criteria::table("usertask_document")
                        ->join("usertask as ut", "ut.idUserTask", "=", "usertask_document.idUserTask")
                        ->where("usertask_document.idDocument", $idDocument)
                        ->where("ut.idUser", -2)
                        ->select("ut.idUserTask", "ut.idTask")
                        ->first();
                    if (empty($usertask)) { // try to get for Corpus
                        $usertask = Criteria::table("usertask_document")
                            ->join("usertask as ut", "ut.idUserTask", "=", "usertask_document.idUserTask")
                            ->where("usertask_document.idCorpus", $document->idCorpus)
                            ->where("ut.idUser", -2)
                            ->select("ut.idUserTask", "ut.idTask")
                            ->first();
                        if (empty($usertask)) {
                            $usertask = (object)[
                                "idUserTask" => 1
                            ];
                        }
                    }
                }
            }
        }
        return $usertask;
    }

    public static function getTaskManager(int $idDocument): object|null
    {
        $document = Document::byId($idDocument);
        $userManager = Criteria::table("usertask_document as ud")
            ->join("usertask as ut", "ud.idUserTask", "=", "ut.idUserTask")
            ->join("task_manager as tm", "ut.idTask", "=", "tm.idTask")
            ->where("ud.idDocument", $idDocument)
            ->select("tm.idUser")
            ->first();
        if (empty($userManager)) { // try to get for Corpus
            $userManager = Criteria::table("usertask_document as ud")
                ->join("usertask as ut", "ud.idUserTask", "=", "ut.idUserTask")
                ->join("task_manager as tm", "ut.idTask", "=", "tm.idTask")
                ->where("ud.idCorpus", $document->idCorpus)
                ->select("tm.iduser")
                ->first();
            if (empty($userManager)) {
                return null;
            }
        }
        $manager = User::byId($userManager->idUser);
        return $manager;
    }

}
