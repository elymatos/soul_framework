<?php

namespace App\Services;

use App\Data\Annotation\Corpus\CreateASData;
use App\Data\Annotation\Corpus\DeleteLabelData;
use App\Data\Annotation\Corpus\SaveLabelData;
use App\Data\Corpus\SearchData;
use App\Repositories\AnnotationSet;
use App\Repositories\Base;
use App\Repositories\Color;
use App\Repositories\Corpus;
use App\Repositories\Document;
use App\Repositories\Label;
use App\Repositories\Language;
use App\Repositories\LayerType;
use App\Repositories\Sentence;
use App\Repositories\Type;
use App\Repositories\UserAnnotation;
use App\Repositories\ViewAnnotationSet;
use App\Repositories\WordForm;


class CorpusService
{

    public static function listSentences(SearchData $data): array
    {
        $sentences = Sentence::listByFilter($data)->get()->keyBy('idSentence')->toArray();
//        if (!empty($sentences)) {
//            $targets = collect(AnnotationSet::listTargetsForSentence(array_keys($sentences)))->groupBy('idSentence')->toArray();
//            foreach ($sentences as $idSentence => $sentence) {
//                if (isset($targets[$idSentence])) {
//                    $sentence->text = self::decorateSentence($sentence->text, $targets[$idSentence]);
//                }
//            }
//        }
        return $sentences;
    }

    public static function listDocuments(SearchData $data): array
    {
        return Document::listByFilter($data)->get()->keyBy('idDocument')->all();
    }

    public static function listCorpus(SearchData $data): array
    {
        return Corpus::listByFilter($data)->get()->keyBy('idCorpus')->all();
    }

}
