<?php

namespace App\Http\Controllers\Annotation;

use App\Data\Annotation\DynamicMode\CloneData;
use App\Data\Annotation\DynamicMode\CreateBBoxData;
use App\Data\Annotation\DynamicMode\DocumentData;
use App\Data\Annotation\DynamicMode\ObjectAnnotationData;
use App\Data\Annotation\DynamicMode\ObjectData;
use App\Data\Annotation\DynamicMode\SearchData;
use App\Data\Annotation\DynamicMode\SentenceData;
use App\Data\Annotation\DynamicMode\UpdateBBoxData;
use App\Data\Annotation\DynamicMode\WordData;
use App\Data\Comment\CommentData;
use App\Database\Criteria;
use App\Http\Controllers\Controller;
use App\Repositories\Corpus;
use App\Repositories\Document;
use App\Repositories\Video;
use App\Services\Annotation\DynamicService;
use App\Services\CommentService;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;


#[Middleware(name: 'auth')]
class DynamicModeController extends Controller
{
    #[Get(path: '/annotation/dynamicMode')]
    public function browse()
    {
        $search = session('searchCorpus') ?? SearchData::from();
        return view("Annotation.DynamicMode.browse", [
            'search' => $search
        ]);
    }

    #[Post(path: '/annotation/dynamicMode/grid')]
    public function grid(SearchData $search)
    {
        return view("Annotation.DynamicMode.grid", [
            'search' => $search
        ]);
    }

    private function getData(int $idDocument): DocumentData
    {
        $document = Document::byId($idDocument);
        $corpus = Corpus::byId($document->idCorpus);
        $documentVideo = Criteria::table("view_document_video")
            ->where("idDocument", $idDocument)
            ->first();
        $video = Video::byId($documentVideo->idVideo);
        return DocumentData::from([
            'idDocument' => $idDocument,
            'document' => $document,
            'corpus' => $corpus,
            'video' => $video,
            'fragment' => 'fe',
        ]);
    }

    #[Get(path: '/annotation/dynamicMode/object/{idDynamicObject}')]
    public function getObject(int $idDynamicObject)
    {
        return DynamicService::getObject($idDynamicObject ?? 0);
    }


    #[Post(path: '/annotation/dynamicMode/formObject')]
    public function formObject(ObjectData $data)
    {
        debug($data);
        $object = DynamicService::getObject($data->idDynamicObject ?? 0);
        return view("Annotation.DynamicMode.Panes.formPane", [
            'order' => $data->order,
            'object' => $object
        ]);
    }


    #[Get(path: '/annotation/dynamicMode/gridObjects/{idDocument}')]
    public function objectsForGrid(int $idDocument)
    {
        return DynamicService::getObjectsByDocument($idDocument);
    }

    #[Get(path: '/annotation/dynamicMode/formObject/{idDynamicObject}/{order}')]
    public function getFormObject(int $idDynamicObject, int $order)
    {
        $object = DynamicService::getObject($idDynamicObject ?? 0);
        return view("Annotation.DynamicMode.Panes.formPane", [
            'order' => $order,
            'object' => $object
        ]);
    }
    #[Post(path: '/annotation/dynamicMode/updateObject')]
    public function updateObject(ObjectData $data)
    {
        debug($data);
        try {
            $idDynamicObject = DynamicService::updateObject($data);
            return Criteria::byId("dynamicobject", "idDynamicObject", $idDynamicObject);
        } catch (\Exception $e) {
            debug($e->getMessage());
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Post(path: '/annotation/dynamicMode/updateObjectAnnotation')]
    public function updateObjectAnnotation(ObjectAnnotationData $data)
    {
        debug($data);
        try {
            $idDynamicObject = DynamicService::updateObjectAnnotation($data);
            $this->trigger('updateObjectAnnotationEvent');
            //return Criteria::byId("dynamicobject", "idDynamicObject", $idDynamicObject);
            return $this->renderNotify("success", "Object updated.");
        } catch (\Exception $e) {
            debug($e->getMessage());
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Post(path: '/annotation/dynamicMode/cloneObject')]
    public function cloneObject(CloneData $data)
    {
        debug($data);
        try {
            $idDynamicObject = DynamicService::cloneObject($data);
            return Criteria::byId("dynamicobject", "idDynamicObject", $idDynamicObject);
        } catch (\Exception $e) {
            debug($e->getMessage());
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/dynamicMode/{idDynamicObject}')]
    public function deleteObject(int $idDynamicObject)
    {
        try {
            DynamicService::deleteObject($idDynamicObject);
            return $this->renderNotify("success", "Object removed.");
        } catch (\Exception $e) {
            debug($e->getMessage());
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Post(path: '/annotation/dynamicMode/updateBBox')]
    public function updateBBox(UpdateBBoxData $data)
    {
        try {
            debug($data);
            $idBoundingBox = DynamicService::updateBBox($data);
            return Criteria::byId("boundingbox", "idBoundingBox", $idBoundingBox);
        } catch (\Exception $e) {
            debug($e->getMessage());
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Post(path: '/annotation/dynamicMode/createBBox')]
    public function createBBox(CreateBBoxData $data)
    {
        try {
            debug($data);
            return DynamicService::createBBox($data);
        } catch (\Exception $e) {
            debug($e->getMessage());
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Get(path: '/annotation/dynamicMode/fes/{idFrame}')]
    public function feCombobox(int $idFrame)
    {
        return view("Annotation.DynamicMode.Panes.fes", [
            'idFrame' => $idFrame
        ]);
    }

    #[Get(path: '/annotation/dynamicMode/sentences/{idDocument}')]
    public function gridSentences(int $idDocument)
    {
        $sentences = DynamicService::listSentencesByDocument($idDocument);
        return view("Annotation.DynamicMode.Panes.sentences", [
            'sentences' => $sentences
        ]);
    }

    /*
     * Build sentence
     */

    #[Get(path: '/annotation/dynamicMode/buildSentences/{idDocument}')]
    public function buildSentences(int $idDocument)
    {
        $data = $this->getData($idDocument);
        return view("Annotation.DynamicMode.buildSentences", $data->toArray());
    }

    #[Get(path: '/annotation/dynamicMode/formSentence/{idDocument}/{idSentence}')]
    public function formSentence(int $idDocument, int $idSentence)
    {
        $sentence = Criteria::byId("view_sentence", "idSentence", $idSentence);
        if (is_null($sentence)) {
            $sentence = (object)[
                "idSentence" => 0,
                "text" => ''
            ];
        } else {
            $ts = Criteria::byId("view_sentence_timespan", "idSentence", $idSentence);
            $sentence->startTime = $ts->startTime;
            $sentence->endTime = $ts->endTime;
        }
        $documentVideo = Criteria::table("view_document_video")
            ->where("idDocument", $idDocument)
            ->first();
        $video = Video::byId($documentVideo->idVideo);
        return view("Annotation.DynamicMode.Panes.formSentencePane", [
            'idDocument' => $idDocument,
            "sentence" => $sentence,
            'idLanguage' => $video->idLanguage
        ]);
    }

    #[Post(path: '/annotation/dynamicMode/formSentence')]
    public function sentence(SentenceData $data)
    {
        try {
            if ($data->text == "") {
                return $this->renderNotify("error", "No data.");
            }
            debug($data);
            if ($data->idSentence == 0) {
                DynamicService::createSentence($data);
            } else {
                DynamicService::updateSentence($data);
            }
            $this->trigger('reload-gridSentence');
            return $this->renderNotify("success", "Sentence updated.");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Get(path: '/annotation/dynamicMode/words/{idVideo}')]
    public function words(int $idVideo)
    {
        $words = Criteria::table("view_video_wordmm")
            ->where("idVideo", "=", $idVideo)
            ->whereNull("idDocumentSentence")
            ->all();
        return $words;
    }

    #[Post(path: '/annotation/dynamicMode/joinWords')]
    public function joinWords(WordData $data)
    {
        try {
            debug($data);
            $idSentence = DynamicService::buildSentenceFromWords($data);
            if ($idSentence == 0) {
                throw new \Exception("Error joining words.");
            }
            return $idSentence;
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Get(path: '/annotation/dynamicMode/buildSentences/sentences/{idDocument}')]
    public function buildSentenceSentences(int $idDocument)
    {

        $sentences = DynamicService::listSentencesByDocument($idDocument);
        return view("Annotation.DynamicMode.Panes.buildSentences", [
            'idDocument' => $idDocument,
            'sentences' => $sentences
        ]);
    }

    #[Post(path: '/annotation/dynamicMode/splitSentence')]
    public function splitSentence(SentenceData $data)
    {
        try {
            DynamicService::splitSentence($data);
            $this->trigger('reload-gridSentence');
            return $this->renderNotify("success", "Sentence updated.");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    /*
     * Comment
     */

    #[Get(path: '/annotation/dynamicMode/formComment')]
    public function getFormComment(CommentData $data)
    {
        $object = CommentService::getDynamicObjectComment($data->idDynamicObject);
        return view("Annotation.DynamicMode.Panes.formComment", [
            'idDocument' => $data->idDocument,
            'order' => $data->order,
            'object' => $object
        ]);
    }
    #[Post(path: '/annotation/dynamicMode/updateObjectComment')]
    public function updateObjectComment(CommentData $data)
    {
        try {
            debug($data);
            CommentService::updateDynamicObjectComment($data);
            $this->trigger('updateObjectAnnotationEvent');
            return $this->renderNotify("success", "Comment registered.");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }
    #[Delete(path: '/annotation/dynamicMode/comment/{idDocument}/{idDynamicObject}')]
    public function deleteObjectComment(int $idDocument,int $idDynamicObject)
    {
        try {
            CommentService::deleteDynamicObjectComment($idDocument,$idDynamicObject);
            return $this->renderNotify("success", "Object comment removed.");
        } catch (\Exception $e) {
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    /*
     * get Object
     */

    #[Get(path: '/annotation/dynamicMode/{idDocument}/{idDynamicObject?}')]
    public function annotation(int|string $idDocument, int $idDynamicObject = null)
    {
        $data = $this->getData($idDocument);
        if (!is_null($idDynamicObject)) {
            $data->idDynamicObject = $idDynamicObject;
        }
        return view("Annotation.DynamicMode.annotation", $data->toArray());
    }



}
