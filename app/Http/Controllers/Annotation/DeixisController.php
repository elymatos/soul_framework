<?php

namespace App\Http\Controllers\Annotation;

use App\Data\Annotation\Deixis\CreateObjectData;
use App\Data\Annotation\Deixis\ObjectAnnotationData;
use App\Data\Annotation\Deixis\ObjectData;
use App\Data\Annotation\Deixis\ObjectFrameData;
use App\Data\Annotation\Deixis\ObjectSearchData;
use App\Data\Annotation\Deixis\SearchData;
use App\Data\Comment\CommentData;
use App\Database\Criteria;
use App\Http\Controllers\Controller;
use App\Repositories\Corpus;
use App\Repositories\Document;
use App\Repositories\Video;
use App\Services\Annotation\DeixisService;
use App\Services\Annotation\BrowseService;
use App\Services\AppService;
use App\Services\CommentService;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;

#[Middleware(name: 'auth')]
class DeixisController extends Controller
{
    #[Get(path: '/annotation/deixis/script/{folder}')]
    public function jsObjects(string $folder)
    {
        return response()
            ->view("Annotation.Deixis.Scripts.{$folder}")
            ->header('Content-type', 'text/javascript')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    #[Get(path: '/annotation/deixis')]
    public function browse(SearchData $search)
    {
        $corpus = BrowseService::browseCorpusBySearch($search, [], 'DeixisAnnotation');

        return view('Annotation.Deixis.browse', [
            'data' => $corpus,
        ]);
    }

    #[Post(path: '/annotation/deixis/tree')]
    public function tree(SearchData $search)
    {
        if (!is_null($search->idCorpus) || ($search->document != '')) {
            $data = BrowseService::browseDocumentBySearch($search, [], 'DeixisAnnotation', leaf: true);
        } else {
            $data = BrowseService::browseCorpusBySearch($search, [], 'DeixisAnnotation');
        }

        return view('Annotation.Deixis.browse', [
            'data' => $data,
        ])->fragment('tree');
    }

    private function getData(int $idDocument): array // DocumentData
    {
        $document = Document::byId($idDocument);
        $corpus = Corpus::byId($document->idCorpus);
        $documentVideo = Criteria::table('view_document_video')
            ->where('idDocument', $idDocument)
            ->first();
        $video = Video::byId($documentVideo->idVideo);
        $timelineData = DeixisService::getLayersByDocument($idDocument);
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
        ];
    }

    #[Get(path: '/annotation/deixis/object')]
    public function getObject(ObjectSearchData $data)
    {
        if ($data->idDynamicObject == 0) {
            return view('Annotation.Deixis.Forms.formNewObject');
        }
        $object = DeixisService::getObject($data->idDynamicObject ?? 0);
        if (is_null($object)) {
            return $this->renderNotify('error', 'Object not found.');
        }

        return response()
            ->view('Annotation.Deixis.Panes.objectPane', [
                'object' => $object,
            ])->header('HX-Push-Url', "/annotation/deixis/{$object->idDocument}/{$object->idDynamicObject}");
    }

    #[Post(path: '/annotation/deixis/object/search')]
    public function objectSearch(ObjectSearchData $data)
    {
        debug($data);
        $searchResults = [];

        if (!empty($data->frame) || !empty($data->lu) || !empty($data->searchIdLayerType) || ($data->idDynamicObject > 0)) {
            $idLanguage = AppService::getCurrentIdLanguage();

            $query = Criteria::table('view_annotation_deixis as ad')
                ->join('layertype as lt', 'ad.idLayerType', '=', 'lt.idLayerType')
                ->join('layergroup as lg', 'lt.idLayerGroup', '=', 'lg.idLayerGroup')
                ->where('ad.idLanguageFE', 'left', $idLanguage)
                ->where('ad.idLanguageGL', 'left', $idLanguage)
                ->where('ad.idLanguageLT', '=', $idLanguage)
                ->where('ad.idDocument', $data->idDocument);

            // Apply search filters
            if (!empty($data->searchIdLayerType) && $data->searchIdLayerType > 0) {
                $query->where('ad.idLayerType', $data->searchIdLayerType);
            }

            if (!empty($data->frame)) {
                $query->whereRaw('(ad.frame LIKE ? OR ad.lu LIKE ?)', [
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
                    'ad.nameLayerType',
                    'ad.lu',
                    'ad.frame',
                    'ad.fe',
                    'ad.gl',
                    'ad.layerGroup'
                )
                ->orderBy('lg.name')
                ->orderBy('ad.nameLayerType')
                ->orderBy('ad.startFrame')
                ->orderBy('ad.endFrame')
                ->all();

            // Format search results for display
            foreach ($searchResults as $object) {
                $object->displayName = '';
                if (!empty($object->gl)) {
                    $object->displayName = $object->gl;
                }
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

        return view('Annotation.Deixis.Panes.searchPane', [
            'searchResults' => $searchResults,
            'idDocument' => $data->idDocument,
        ])->fragment('search');
    }

    #[Post(path: '/annotation/deixis/createNewObjectAtLayer')]
    public function createNewObjectAtLayer(CreateObjectData $data)
    {
        try {
            $object = DeixisService::createNewObjectAtLayer($data);
            return $this->redirect("/annotation/deixis/{$object->idDocument}/{$object->idDynamicObject}");
        } catch (\Exception $e) {
            debug($e->getMessage());

            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Post(path: '/annotation/deixis/formAnnotation')]
    public function formAnnotation(ObjectData $data)
    {
        $object = DeixisService::getObject($data->idDynamicObject ?? 0);
        return $this->redirect("/annotation/deixis/{$object->idDocument}/{$object->idDynamicObject}");
    }

    #[Get(path: '/annotation/deixis/formAnnotation/{idDynamicObject}')]
    public function getFormAnnotation(int $idDynamicObject)
    {
        $object = DeixisService::getObject($idDynamicObject ?? 0);
        return view('Annotation.Deixis.Panes.formAnnotation', [
            'object' => $object,
        ]);
    }

    #[Get(path: '/annotation/deixis/loadLayerList/{idDocument}')]
    public function loadLayerList(int $idDocument)
    {
        return DeixisService::getLayersByDocument($idDocument);
    }

    #[Post(path: '/annotation/deixis/updateObjectRange')]
    public function updateObjectRange(ObjectFrameData $data)
    {
        try {
            debug($data);
            DeixisService::updateObjectFrame($data);
            return $this->redirect("/annotation/deixis/{$data->idDocument}/{$data->idDynamicObject}");
        } catch (\Exception $e) {
            debug($e->getMessage());

            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Post(path: '/annotation/deixis/updateObjectAnnotation')]
    public function updateObjectAnnotation(ObjectAnnotationData $data)
    {
        try {
            DeixisService::updateObjectAnnotation($data);
            $object = DeixisService::getObject($data->idDynamicObject);
            $this->notify('success', 'Object updated.');
            debug($object);

            return view('Annotation.Deixis.Panes.timeline.object', [
                'duration' => $object->endFrame - $object->startFrame,
                'objectData' => $object,
            ])->fragment('object');
        } catch (\Exception $e) {
            debug($e->getMessage());

            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/deixis/deleteAllBBoxes/{idDocument}/{idDynamicObject}')]
    public function deleteAllBBoxes(int $idDocument, int $idDynamicObject)
    {
        try {
            DeixisService::deleteBBoxesFromObject($idDynamicObject);

            return $this->redirect("/annotation/deixis/{$idDocument}/{$idDynamicObject}");
        } catch (\Exception $e) {
            debug($e->getMessage());

            return $this->renderNotify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/deixis/{idDocument}/{idDynamicObject}')]
    public function deleteObject(int $idDocument, int $idDynamicObject)
    {
        try {
            DeixisService::deleteObject($idDynamicObject);

            return $this->redirect("/annotation/deixis/{$idDocument}");
        } catch (\Exception $e) {
            debug($e->getMessage());

            return $this->renderNotify('error', $e->getMessage());
        }
    }

    /*
     * Comment
     */

    #[Get(path: '/annotation/deixis/formComment')]
    public function getFormComment(CommentData $data)
    {
        $object = CommentService::getDynamicObjectComment($data->idDynamicObject);

        return view('Annotation.Deixis.Panes.formComment', [
            'idDocument' => $data->idDocument,
            'order' => $data->order,
            'object' => $object,
        ]);
    }

    #[Post(path: '/annotation/deixis/updateObjectComment')]
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

    #[Delete(path: '/annotation/deixis/comment/{idDocument}/{idDynamicObject}')]
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
    #[Get(path: '/annotation/deixis/{idDocument}/{idDynamicObject?}')]
    public function annotation(int|string $idDocument, ?int $idDynamicObject = null)
    {
        $data = $this->getData($idDocument);
        $data['idDynamicObject'] = is_null($idDynamicObject) ? 0 : $idDynamicObject;
        return response()
            ->view('Annotation.Deixis.annotation', $data)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}
