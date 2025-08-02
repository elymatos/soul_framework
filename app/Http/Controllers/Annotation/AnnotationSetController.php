<?php

namespace App\Http\Controllers\Annotation;

use App\Data\Annotation\FE\SearchData;
use App\Http\Controllers\Controller;
use App\Repositories\Document;
use App\Services\Annotation\ASService;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;

#[Middleware("auth")]
class AnnotationSetController extends Controller
{
    #[Get(path: '/annotation/as')]
    public function browse()
    {
        $search = session('searchFEAnnotation') ?? SearchData::from();
        return view("Annotation.AS.browse", [
            'search' => $search
        ]);
    }

    #[Post(path: '/annotation/as/grid')]
    public function grid(SearchData $search)
    {
        return view("Annotation.AS.grids", [
            'search' => $search,
            'sentences' => [],
        ]);
    }

    #[Get(path: '/annotation/as/grid/{idDocument}/sentences')]
    public function documentSentences(int $idDocument)
    {
        $document = Document::byId($idDocument);
        $sentences = ASService::listSentences($idDocument);
        return view("Annotation.AS.sentences", [
            'document' => $document,
            'sentences' => $sentences
        ]);
    }

    #[Get(path: '/annotation/as/sentence/{idDocumentSentence}')]
    public function sentence(int $idDocumentSentence)
    {
        $data = ASService::getAnnotationData($idDocumentSentence);
//        if (!is_null($idAnnotationSet)) {
//            $data['idAnnotationSet'] = $idAnnotationSet;
//        }
        return view("Annotation.AS.annotationSentence", $data);
    }

//    #[Get(path: '/annotation/as/annotationSets/{idSentence}')]
//    public function annotationSets(int $idSentence)
//    {
//        $data = AnnotationASService::getAnnotationData($idSentence);
//        return view("Annotation.AS.Panes.annotations", $data);
//    }

    #[Get(path: '/annotation/as/as/{idAS}')]
    public function annotationSet(int $idAS)
    {
        $data = ASService::getASData($idAS, '');
        return view("Annotation.AS.Panes.annotationSet", $data);
    }

    #[Get(path: '/annotation/as/lus/{idDocumentSentence}/{idWord}')]
    public function getLUs(int $idDocumentSentence, int $idWord)
    {
        $data = ASService::getLUs($idDocumentSentence, $idWord);
        $data['idWord'] = $idWord;
        $data['idDocumentSentence'] = $idDocumentSentence;
        return view("Annotation.AS.Panes.lus", $data);
    }

    #[Post(path: '/annotation/as/annotate')]
    public function annotate(AnnotationData $input)
    {
        try {
            $input->range = SelectionData::from(request("selection"));
            if ($input->range->end < $input->range->start) {
                throw new \Exception("Wrong selection.");
            }
            if ($input->range->type != '') {
                $data = AnnotationFEService::annotateFE($input);
                //$data['alternativeLU'] = [];
                debug("#######################################################");

//                $this->trigger('reload-annotationSet');
//                $this->trigger('reload-annotationSet');
                return view("Annotation.AS.Panes.annotationSet", $data);
//                return $input->idAnnotationSet;
            } else {
                return $this->renderNotify("error", "No selection.");
            }
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/as/frameElement')]
    public function deleteFE(DeleteFEData $data)
    {
        try {
            ASService::deleteFE($data);
            $data = AnnotationFEService::getASData($data->idAnnotationSet, $data->token);
            debug("--------------------------------------------------------");
            //$data['alternativeLU'] = [];
            return view("Annotation.AS.Panes.annotationSet", $data);
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Post(path: '/annotation/as/create')]
    public function createAS(CreateASData $input)
    {
        $idAnnotationSet = ASService::createAnnotationSet($input);
        if (is_null($idAnnotationSet)) {
            return $this->renderNotify("error", "Error creating AnnotationSet.");
        } else {
            //$data = AnnotationFEService::getASData($idAnnotationSet);
//            $this->trigger('reload-sentence');
//            return view("Annotation.AS.Panes.annotationSet", $data);
            return $this->clientRedirect("/annotation/as/sentence/{$input->idDocumentSentence}/{$idAnnotationSet}");

        }
    }

    #[Delete(path: '/annotation/as/annotationset/{idAnnotationSet}')]
    public function deleteAS(int $idAnnotationSet)
    {
        try {
            $annotationSet = Criteria::byId("view_annotationset","idAnnotationSet", $idAnnotationSet);
            AnnotationSet::delete($idAnnotationSet);
            return $this->clientRedirect("/annotation/as/sentence/{$annotationSet->idDocumentSentence}");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

}

