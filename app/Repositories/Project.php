<?php

namespace App\Repositories;

use App\Database\Criteria;
use App\Services\AppService;

class Project
{
    public static function byId(int $id): object
    {
        return Criteria::byFilter("project", ["idProject", "=", $id])->first();
    }

    public static function getAllowedDocsForUser(array $projects = [], string $taskGroupName = ''): array
    {
        $idUser = AppService::getCurrentIdUser();
        $user = User::byId($idUser);
        //debug($projects);
        if (User::isManager($user)) {
            $criteria = Criteria::table("view_project_docs as pd")
                ->join("view_project_tasks as pt","pd.idProject","=","pt.idProject")
                ->where("idLanguage", AppService::getCurrentIdLanguage())
                ->where("pd.idProject","<>", 1)
                ->select("idCorpus","corpusName","idDocument","documentName")
                ->orderBy("corpusName")
                ->orderBy("documentName");
            if (!empty($projects)) {
                $criteria = $criteria
                    ->whereIn('projectName', $projects);
            }
            if ($taskGroupName != '') {
                $criteria = $criteria
                    ->where('pt.taskGroupName', $taskGroupName);
            }
            $docs = $criteria
                ->all();
        } else {
            $criteria = Criteria::table("view_alloweddocs as ad")
                ->join("view_project_docs as pd","pd.idCorpus","=","ad.idCorpus")
                ->join("view_project_tasks as pt","pd.idProject","=","pt.idProject")
                ->where("ad.idUser", $idUser)
                ->where("pd.idProject","<>", 1)
                ->where("ad.idLanguage", AppService::getCurrentIdLanguage())
                ->where("pd.idLanguage", AppService::getCurrentIdLanguage())
                ->select("ad.idCorpus","ad.corpusName","ad.idDocument","ad.documentName")
                ->orderBy("ad.corpusName")
                ->orderBy("ad.documentName");
            if (!empty($projects)) {
                $criteria = $criteria
                    ->whereIn('projectName', $projects);
            }
            if ($taskGroupName != '') {
                $criteria = $criteria
                    ->where('pt.taskGroupName', $taskGroupName);
            }
            $docs = $criteria
                ->all();
        }
        return $docs;
    }


}
