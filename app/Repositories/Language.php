<?php

namespace App\Repositories;

use App\Database\Criteria;

class Language
{
    public static function byId(int $id): object
    {
        return Criteria::byFilter("language", ["idLanguage","=", $id])->first();
    }

    public static function listForSelection(){
        return Criteria::table("language")
            ->selectRaw("idLanguage,concat(language,' - ',description) as ldescription")
            ->where("idLanguage","<>","0")
            ->orderBy("ldescription")
            ->pluck('ldescription', 'idLanguage')
            ->all();
    }

}
