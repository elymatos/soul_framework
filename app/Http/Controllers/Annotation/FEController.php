<?php

namespace App\Http\Controllers\Annotation;

use App\Data\Annotation\Corpus\CreateASData;
use App\Data\Annotation\FE\AnnotationData;
use App\Data\Annotation\FE\DeleteFEData;
use App\Data\Annotation\FE\SearchData;
use App\Data\Annotation\FE\SelectionData;
use App\Data\Comment\CommentData;
use App\Database\Criteria;
use App\Http\Controllers\Controller;
use App\Repositories\AnnotationSet;
use App\Services\Annotation\CorpusService;
use App\Services\Annotation\FEService;
use App\Services\Annotation\BrowseService;
use App\Services\CommentService;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;

#[Middleware("auth")]
class FEController extends Controller
{
    #[Get(path: '/annotation/fe/script/{folder}')]
    public function jsObjects(string $folder)
    {
        return response()
            ->view("Annotation.FE.Scripts.{$folder}")
            ->header('Content-type', 'text/javascript');
    }

    #[Get(path: '/annotation/fe')]
    public function browse(SearchData $search)
    {
        $corpus = BrowseService::browseCorpusBySearch($search);
        return view("Annotation.FE.browse", [
            'data' => $corpus,
        ]);
    }

    #[Post(path: '/annotation/fe/tree')]
    public function tree(SearchData $search)
    {
        if (!is_null($search->idDocumentSentence)) {
            $data = BrowseService::browseSentence($search->idDocumentSentence);
        } else {
            if (!is_null($search->idDocument)) {
                $data = BrowseService::browseSentencesByDocument($search->idDocument);
            } else {
                if (!is_null($search->idCorpus) || ($search->document != '')) {
                    $data = BrowseService::browseDocumentBySearch($search);
                } else {
                    $data = BrowseService::browseCorpusBySearch($search);
                }
            }
        }
        return view("Annotation.FE.browse", [
            'data' => $data
        ])->fragment("tree");
    }

    #[Get(path: '/annotation/fe/sentence/{idDocumentSentence}/{idAnnotationSet?}')]
    public function sentence(int $idDocumentSentence, int $idAnnotationSet = null)
    {
        $data = CorpusService::getAnnotationData($idDocumentSentence,$idAnnotationSet);
        return view("Annotation.FE.annotation", $data);
    }

    #[Get(path: '/annotation/fe/lus/{idDocumentSentence}/{idWord}')]
    public function getLUs(int $idDocumentSentence, int $idWord)
    {
        $data = CorpusService::getLUs($idDocumentSentence, $idWord);
        return view("Annotation.FE.Panes.lus", $data);
    }

    #[Post(path: '/annotation/fe/createAS')]
    public function createAS(CreateASData $input)
    {
        $idAnnotationSet = CorpusService::createAnnotationSet($input);
        if (is_null($idAnnotationSet)) {
            return $this->renderNotify("error", "Error creating AnnotationSet.");
        } else {
            return $this->clientRedirect("/annotation/fe/sentence/{$input->idDocumentSentence}/{$idAnnotationSet}");
        }
    }

    #[Get(path: '/annotation/fe/as/{idAS}/{token?}')]
    public function annotationSet(int $idAS, string $token = '')
    {
        $data = FEService::getASData($idAS, $token);
        return view("Annotation.FE.Panes.annotationSet", $data);
    }

    #[Post(path: '/annotation/fe/annotate')]
    public function annotate(AnnotationData $input)
    {
        debug($input);
        try {
            $input->range = SelectionData::from(request("selection"));
            if ($input->range->end < $input->range->start) {
                throw new \Exception("Selection failed. Make new selection.");
            }
            if ($input->range->type != '') {
                $data = FEService::annotateFE($input);
                return view("Annotation.FE.Panes.asAnnotation", $data);
            } else {
                return $this->renderNotify("error", "No selection.");
            }
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/fe/frameElement')]
    public function deleteFE(DeleteFEData $data)
    {
        try {
            FEService::deleteFE($data);
            $data = FEService::getASData($data->idAnnotationSet, $data->token);
            return view("Annotation.FE.Panes.asAnnotation", $data);
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/fe/annotationset/{idAnnotationSet}')]
    public function deleteAS(int $idAnnotationSet)
    {
        try {
            $annotationSet = Criteria::byId("view_annotationset", "idAnnotationSet", $idAnnotationSet);
            AnnotationSet::delete($idAnnotationSet);
            return $this->clientRedirect("/annotation/fe/sentence/{$annotationSet->idDocumentSentence}");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    /*
     * Comment
     */

    #[Get(path: '/annotation/fe/formComment/{idAnnotationSet}')]
    public function getFormComment(int $idAnnotationSet)
    {
        $object = CommentService::getAnnotationSetComment($idAnnotationSet);
        return view("Annotation.FE.Panes.formComment", [
            'object' => $object
        ]);
    }

    #[Post(path: '/annotation/fe/updateObjectComment')]
    public function updateObjectComment(CommentData $data)
    {
        try {
            CommentService::updateAnnotationSetComment($data);
            $this->trigger('reload-annotationSet');
            return $this->renderNotify("success", "Comment registered.");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/fe/comment/{idAnnotationSet}')]
    public function deleteObjectComment(int $idAnnotationSet)
    {
        try {
            CommentService::deleteAnnotationSetComment($idAnnotationSet);
            $this->trigger('reload-annotationSet');
            return $this->renderNotify("success", "Object comment removed.");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }


}

