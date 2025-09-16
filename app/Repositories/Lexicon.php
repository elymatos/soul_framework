<?php

namespace App\Repositories;

use App\Database\Criteria;

class Lexicon
{

    public static function byId(int $id): object
    {
        return Criteria::byFilterLanguage("lexicon", ['idLexicon', '=', $id])->first();
    }

    public static function lemmaById(int $id): object
    {
        return Criteria::byFilter("view_lexicon_lemma", [
            "idLexicon", "=", $id
        ])->first();
    }

}
