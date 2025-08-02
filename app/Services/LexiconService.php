<?php

namespace App\Services;

use App\Data\Lexicon\SearchData;
use App\Repositories\Lemma;
use App\Repositories\Lexeme;
use App\Repositories\WordForm;

class LexiconService
{

    public static function listWordForm(SearchData $data): array
    {
        return Wordform::listByFilter($data)->get()->keyBy('idWordform')->toArray();
    }

    public static function listLexeme(SearchData $data): array
    {
        return Lexeme::listByFilter($data)->get()->keyBy('idLexeme')->all();
    }

    public static function listLemma(SearchData $data): array
    {
        return Lemma::listByFilter($data)->get()->keyBy('idLemma')->all();
    }

}
