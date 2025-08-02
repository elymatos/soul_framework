<?php

namespace App\Http\Controllers\Annotation;

use App\Data\Annotation\Dynamic\CloneData;
use App\Data\Annotation\Dynamic\CreateBBoxData;
use App\Data\Annotation\Dynamic\CreateObjectData;
use App\Data\Annotation\Dynamic\GetBBoxData;
use App\Data\Annotation\Dynamic\ObjectAnnotationData;
use App\Data\Annotation\Dynamic\ObjectFrameData;
use App\Data\Annotation\Dynamic\ObjectSearchData;
use App\Data\Annotation\Dynamic\SearchData;
use App\Data\Annotation\Dynamic\UpdateBBoxData;
use App\Data\Comment\CommentData;
use App\Database\Criteria;
use App\Http\Controllers\Controller;
use App\Repositories\Corpus;
use App\Repositories\Document;
use App\Repositories\Video;
use App\Services\Annotation\DynamicService;
use App\Services\Annotation\BrowseService;
use App\Services\AppService;
use App\Services\CommentService;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;

#[Middleware(name: 'auth')]
class DynamicController extends Controller
{
    #[Get(path: '/annotation/dynamic/script/{folder}')]
    public function jsObjects(string $folder)
    {
        return response()
            ->view("Annotation.Dynamic.Scripts.{$folder}")
            ->header('Content-type', 'text/javascript')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    #[Get(path: '/annotation/dynamic')]
    public function browse(SearchData $search)
    {
        $corpus = BrowseService::browseCorpusBySearch($search, [], 'DynamicAnnotation');

        return view('Annotation.Dynamic.browse', [
            'data' => $corpus,
        ]);
    }

    #[Post(path: '/annotation/dynamic/tree')]
    public function tree(SearchData $search)
    {
        if (!is_null($search->idCorpus) || ($search->document != '')) {
            $data = BrowseService::browseDocumentBySearch($search, [], 'DynamicAnnotation', leaf: true);
        } else {
            $data = BrowseService::browseCorpusBySearch($search, [], 'DynamicAnnotation');
        }

        return view('Annotation.Dynamic.browse', [
            'data' => $data,
        ])->fragment('tree');
    }

    private function getData(int $idDocument, int $idDynamicObject = null): array
    {
        $document = Document::byId($idDocument);
        $corpus = Corpus::byId($document->idCorpus);
        $documentVideo = Criteria::table('view_document_video')
            ->where('idDocument', $idDocument)
            ->first();
        $video = Video::byId($documentVideo->idVideo);
        $timelineData = DynamicService::getLayersByDocument($idDocument);
        $timelineConfig = $this->getTimelineConfig($timelineData);
        $groupedLayers = $this->groupLayersByName($timelineData);

        return [
            'idDocument' => $idDocument,
            'document' => $document,
            'corpus' => $corpus,
            'video' => $video,
            'fragment' => 'fe',
            'searchResults' => [],
            'timeline' => [
                'data' => $timelineData,
                'config' => $timelineConfig,
            ],
            'groupedLayers' => $groupedLayers,
            'idDynamicObject' => is_null($idDynamicObject) ? 0 : $idDynamicObject
        ];
    }

    #[Get(path: '/annotation/dynamic/object')]
    public function getObject(ObjectSearchData $data)
    {
        if ($data->idDynamicObject == 0) {
            return view('Annotation.Dynamic.Forms.formNewObject');
        }
        $object = DynamicService::getObject($data->idDynamicObject ?? 0);
        if (is_null($object)) {
            return $this->renderNotify('error', 'Object not found.');
        }

        return response()
            ->view('Annotation.Dynamic.Panes.objectPane', [
                'object' => $object,
            ])->header('HX-Push-Url', "/annotation/dynamic/{$object->idDocument}/{$object->idDynamicObject}");
    }

    #[Post(path: '/annotation/dynamic/object/search')]
    public function objectSearch(ObjectSearchData $data)
    {
        debug($data);
        $searchResults = [];

        if (!empty($data->frame) || !empty($data->lu) || !empty($data->searchIdLayerType) || ($data->idDynamicObject > 0)) {
            $idLanguage = AppService::getCurrentIdLanguage();

            $query = Criteria::table('view_annotation_dynamic as ad')
                ->where('ad.idLanguage', 'left', $idLanguage)
//                ->leftJoin('layertype as lt', 'ad.idLayerType', '=', 'lt.idLayerType')
//                ->leftJoin('layergroup as lg', 'lt.idLayerGroup', '=', 'lg.idLayerGroup')
//                ->where('ad.idLanguageFE', 'left', $idLanguage)
//                ->where('ad.idLanguageGL', 'left', $idLanguage)
//                ->where('ad.idLanguageLT', 'left', $idLanguage)
                ->where('ad.idDocument', $data->idDocument);

            // Apply search filters
//            if (!empty($data->searchIdLayerType) && $data->searchIdLayerType > 0) {
//                $query->where('ad.idLayerType', $data->searchIdLayerType);
//            }

            if (!empty($data->frame)) {
                $query->whereRaw('(ad.frame LIKE ? OR ad.fe LIKE ?)', [
                    $data->frame . '%',
                    $data->frame . '%'
                ]);
            }

            if (!empty($data->lu)) {
                $searchTerm = '%' . $data->lu . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('ad.lu', 'like', $searchTerm);
                });
            }

            if ($data->idDynamicObject != 0) {
                $query->where('ad.idDynamicObject', $data->idDynamicObject);
            }

            $searchResults = $query
                ->select(
                    'ad.idDynamicObject',
                    'ad.name',
                    'ad.startFrame',
                    'ad.endFrame',
                    'ad.startTime',
                    'ad.endTime',
//                    'ad.nameLayerType',
                    'ad.lu',
                    'ad.frame',
                    'ad.fe',
//                    'ad.gl',
//                    'ad.layerGroup'
                )
//                ->orderBy('lg.name')
//                ->orderBy('ad.nameLayerType')
                ->orderBy('ad.idDynamicObject')
                ->orderBy('ad.startFrame')
                ->orderBy('ad.endFrame')
                ->all();

            // Format search results for display
            foreach ($searchResults as $object) {
                $object->displayName = '';
//                if (!empty($object->gl)) {
//                    $object->displayName = $object->gl;
//                }
                if (!empty($object->lu)) {
                    $object->displayName .= ($object->displayName ? ' | ' : '') . $object->lu;
                }
                if (!empty($object->fe)) {
                    $object->displayName .= ($object->displayName ? ' | ' : '') . $object->frame . '.' . $object->fe;
                }
                if (empty($object->displayName)) {
                    $object->displayName = 'None';
                }
            }
        }

        return view('Annotation.Dynamic.Panes.searchPane', [
            'searchResults' => $searchResults,
            'idDocument' => $data->idDocument,
        ])->fragment('search');
    }

    #[Post(path: '/annotation/dynamic/createNewObjectAtLayer')]
    public function createNewObjectAtLayer(CreateObjectData $data)
    {
        try {
            $object = DynamicService::createNewObjectAtLayer($data);
            return $this->redirect("/annotation/dynamic/{$object->idDocument}/{$object->idDynamicObject}");
        } catch (\Exception $e) {
            debug($e->getMessage());

            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Post(path: '/annotation/dynamic/updateObjectRange')]
    public function updateObjectRange(ObjectFrameData $data)
    {
        try {
            debug($data);
            DynamicService::updateObjectFrame($data);
            return $this->redirect("/annotation/dynamic/{$data->idDocument}/{$data->idDynamicObject}");
        } catch (\Exception $e) {
            debug($e->getMessage());

            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Post(path: '/annotation/dynamic/updateObjectAnnotation')]
    public function updateObjectAnnotation(ObjectAnnotationData $data)
    {
        try {
            DynamicService::updateObjectAnnotation($data);
            $object = DynamicService::getObject($data->idDynamicObject);
            $this->notify('success', 'Object updated.');
            return $this->render('Annotation.Dynamic.Panes.timeline.object', [
                'duration' => $object->endFrame - $object->startFrame,
                'objectData' => $object,
            ],'object');
        } catch (\Exception $e) {
            debug($e->getMessage());

            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/dynamic/deleteAllBBoxes/{idDocument}/{idDynamicObject}')]
    public function deleteAllBBoxes(int $idDocument, int $idDynamicObject)
    {
        try {
            DynamicService::deleteBBoxesFromObject($idDynamicObject);

            return $this->redirect("/annotation/dynamic/{$idDocument}/{$idDynamicObject}");
        } catch (\Exception $e) {
            debug($e->getMessage());

            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/dynamic/{idDocument}/{idDynamicObject}')]
    public function deleteObject(int $idDocument, int $idDynamicObject)
    {
        try {
            DynamicService::deleteObject($idDynamicObject);

            return $this->redirect("/annotation/dynamic/{$idDocument}");
        } catch (\Exception $e) {
            debug($e->getMessage());

            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Post(path: '/annotation/dynamic/cloneObject')]
    public function cloneObject(CloneData $data)
    {
        debug($data);
        try {
            $idDynamicObjectClone = DynamicService::cloneObject($data);
            return $this->redirect("/annotation/dynamic/{$data->idDocument}/{$idDynamicObjectClone}");
        } catch (\Exception $e) {
            debug($e->getMessage());
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    /*
     * BBox
     */

    #[Get(path: '/annotation/dynamic/getBBox')]
    public function getBBox(GetBBoxData $data)
    {
        try {
            debug($data);
            return Criteria::table("view_dynamicobject_boundingbox")
                ->where("idDynamicObject", $data->idDynamicObject)
                ->where("frameNumber", $data->frameNumber)
                ->first();
        } catch (\Exception $e) {
            debug($e->getMessage());
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Get(path: '/annotation/dynamic/getBoxesContainer/{idDynamicObject}')]
    public function getBoxesContainer(int $idDynamicObject)
    {
        try {
            $dynamicObject = Criteria::byId("dynamicObject","idDynamicObject", $idDynamicObject);
            return view("Annotation.Dynamic.Forms.boxesContainer",[
                'object' => $dynamicObject,
            ]);
        } catch (\Exception $e) {
            debug($e->getMessage());
            return $this->renderNotify("error", $e->getMessage());
        }
    }

    #[Post(path: '/annotation/dynamic/createBBox')]
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

    #[Post(path: '/annotation/dynamic/updateBBox')]
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


    /*
     * Comment
     */

    #[Get(path: '/annotation/dynamic/formComment')]
    public function getFormComment(CommentData $data)
    {
        $object = CommentService::getDynamicObjectComment($data->idDynamicObject);

        return view('Annotation.Dynamic.Panes.formComment', [
            'idDocument' => $data->idDocument,
            'order' => $data->order,
            'object' => $object,
        ]);
    }

    #[Post(path: '/annotation/dynamic/updateObjectComment')]
    public function updateObjectComment(CommentData $data)
    {
        try {
            debug($data);
            CommentService::updateDynamicObjectComment($data);
            $this->trigger('updateObjectAnnotationEvent');

            return $this->renderNotify('success', 'Comment registered.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/dynamic/comment/{idDocument}/{idDynamicObject}')]
    public function deleteObjectComment(int $idDocument, int $idDynamicObject)
    {
        try {
            CommentService::deleteDynamicObjectComment($idDocument, $idDynamicObject);

            return $this->renderNotify('success', 'Object comment removed.');
        } catch (\Exception $e) {
            return $this->renderNotify('error', $e->getMessage());
        }
    }

    /**
     * timeline
     */
    private function getTimelineConfig($timelineData): array
    {
        $minFrame = PHP_INT_MAX;
        $maxFrame = PHP_INT_MIN;

        foreach ($timelineData as $layer) {
            foreach ($layer['objects'] as $object) {
                $minFrame = min($minFrame, $object->startFrame);
                $maxFrame = max($maxFrame, $object->endFrame);
            }
        }

        // Add padding
        $minFrame = max(0, $minFrame - 100);
        $maxFrame = $maxFrame + 100;

        return [
            'minFrame' => $minFrame,
            'maxFrame' => $maxFrame,
            'frameToPixel' => 1,
            'minObjectWidth' => 16,
            'objectHeight' => 24,
            'labelWidth' => 150,
            'timelineWidth' => ($maxFrame - $minFrame) * 1,
            'timelineHeight' => (24 * count($timelineData)) + 10,
        ];
    }

    private function groupLayersByName($timelineData): array
    {
        $layerGroups = [];

        foreach ($timelineData as $originalIndex => $layer) {
            $layerName = $layer['layer'];

            if (!isset($layerGroups[$layerName])) {
                $layerGroups[$layerName] = [
                    'name' => $layerName,
                    'lines' => [],
                ];
            }

            $layerGroups[$layerName]['lines'][] = array_merge($layer, [
                'originalIndex' => $originalIndex,
            ]);
        }

        return array_values($layerGroups);
    }

    /**
     * Page
     */
    #[Get(path: '/annotation/dynamic/{idDocument}/{idDynamicObject?}')]
    public function annotation(int|string $idDocument, ?int $idDynamicObject = null)
    {
        $data = $this->getData($idDocument, $idDynamicObject);
        return response()
            ->view('Annotation.Dynamic.main', $data)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}
