<?php

namespace App\Http\Controllers\Annotation;

use App\Data\Annotation\Browse\SearchData;
use App\Data\Annotation\Browse\TreeData;
use App\Http\Controllers\Controller;
use App\Services\Annotation\BrowseService;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;

#[Middleware("auth")]
class BrowseController extends Controller
{
    #[Post(path: '/annotation/browse/searchSentence')]
    public function search(SearchData $search)
    {
        debug($search);
        if (!is_null($search->idCorpus)) {
            $data = BrowseService::browseDocumentsByCorpus($search->idCorpus, [], "CorpusAnnotation");
        } else if (!is_null($search->idDocument)) {
            $data = BrowseService::browseSentencesByDocument($search->idDocument);
        } else if ($search->idDocumentSentence != '') {
            $data = BrowseService::browseSentence($search->idDocumentSentence);
//            $title = "Sentence";
        } elseif ($search->document != '') {
            $data = BrowseService::browseDocumentBySearch($search, [], "CorpusAnnotation");
//            $title = "Documents";
        } else {
            $data = BrowseService::browseCorpusBySearch($search, [], "CorpusAnnotation");
//            $title = "Corpora";
        }

        return view('Annotation.treeSentences', [
            'data' => $data,
        ]);
    }

    #[Post(path: '/annotation/browse/searchDocument/{taskGroupName}')]
    public function searchDocument(SearchData $search, string $taskGroupName = null)
    {
        $search->taskGroupName ??= $taskGroupName;
        debug($search);
        if (!is_null($search->idCorpus)) {
            $data = BrowseService::browseDocumentsByCorpus($search->idCorpus, [], $search->taskGroupName, leaf: true);
        } else if ($search->document != '') {
            $data = BrowseService::browseDocumentBySearch($search, [], $search->taskGroupName, leaf: true);
            $title = "Documents";
        } else {
            $data = BrowseService::browseCorpusBySearch($search, [], $search->taskGroupName);
            $title = "Corpora";
        }

        return view('Annotation.treeDocuments', [
            'data' => $data,
            'taskGroupName' => $search->taskGroupName,
        ]);
    }

}

