<?php

namespace App\Http\Controllers\Annotation;

use App\Data\Annotation\Corpus\AddFELayerData;
use App\Data\Annotation\Corpus\CreateASData;
use App\Data\Annotation\Corpus\DeleteASData;
use App\Data\Annotation\Corpus\DeleteLabelData;
use App\Data\Annotation\Corpus\DeleteLastFELayerData;
use App\Data\Annotation\Corpus\SaveLabelData;
use App\Data\Annotation\Corpus\SearchData;
use App\Http\Controllers\Controller;
use App\Repositories\Corpus;
use App\Repositories\Document;
use App\Repositories\Sentence;
use App\Services\Annotation\CorpusService;
use App\Services\AppService;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;
use Collective\Annotations\Routing\Attributes\Attributes\Put;

#[Middleware(name: 'auth')]
class CorpusController extends Controller
{
    #[Get(path: '/annotation/corpus')]
    public function browse()
    {
        $search = session('searchCorpus') ?? SearchData::from();
        return view("Panes.Corpus.tree", [
            'search' => $search
        ]);
    }

    #[Post(path: '/annotation/corpus/grid')]
    public function grid(SearchData $search)
    {
        debug($search);
        $display = 'corpus';
        $corpus = [];
        $sentences = [];
        $documents = [];
        $corpusName = 'Corpus';
        $documentName = '';
        if ($search->idCorpus != '') {
            $corpus = CorpusService::listCorpus(new SearchData);
            $corpusName = $corpus[$search->idCorpus]->name;
            $documents = CorpusService::listDocuments($search);
            if (!empty($documents)) {
                $key = array_key_first($documents);
                $search->idDocument = $documents[$key]->idDocument;
                $documentName = $documents[$key]->name;
                $sentences = CorpusService::listSentences($search);
            }
        } elseif ($search->idDocument != '') {
            $document = Document::getById($search->idDocument);
            $corpus = CorpusService::listCorpus(new SearchData);
            $search->idDocument = null;
            $search->idCorpus = $document->idCorpus;
            $documents = CorpusService::listDocuments($search);
            $search->idDocument = $document->idDocument;
            $corpusName = Corpus::getById($document->idCorpus)->name;
            $documentName = $document->name;
            $sentences = CorpusService::listSentences($search);
        } else {
            if (($search->sentence != '') || ($search->idSentence != '')) {
                $sentences = CorpusService::listSentences($search);
                $display = 'sentence';
            } else {
                if (($search->document != '')) {
                    $documents = CorpusService::listDocuments($search);
                    if (!empty($documents)) {
                        $key = array_key_first($documents);
                        $search->idDocument = $documents[$key]->idDocument;
                        $sentences = CorpusService::listSentences($search);
                    }
                    $corpusName = $search->document . '*';
                    $display = 'document';
                } else {
                    $corpus = CorpusService::listCorpus($search);
                    if (!empty($corpus)) {
                        $key = array_key_first($corpus);
                        $search->idCorpus = $corpus[$key]->idCorpus;
                        $documents = CorpusService::listDocuments($search);
                        if (!empty($documents)) {
                            $key = array_key_first($documents);
                            $search->idDocument = $documents[$key]->idDocument;
                            $corpusName = Corpus::getById($documents[$search->idDocument]->idCorpus)->name;
                            $documentName = $documents[$search->idDocument]->name;
                            $sentences = CorpusService::listSentences($search);
                        }
                    }
                }
            }
        }
        return view("Panes.Corpus.grids", [
            'search' => $search,
            'display' => $display,
            'corpus' => $corpus,
            'documents' => $documents,
            'sentences' => $sentences,
            'corpusName' => $corpusName,
            'documentName' => $documentName,
        ]);
    }

    #[Post(path: '/annotation/corpus/listForTree')]
    public function listForTree()
    {
        return CorpusService::listForTree();
    }

    #[Get(path: '/annotation/corpus/sentence/{idSentence}')]
    public function annotationSentence(int $idSentence)
    {
        $data['sessionTimeout'] = 300;// Manager::getConf('session.timeout');
        $canSave = true;
        $data['canSave'] = true;//$canSave && Manager::checkAccess('BEGINNER', A_EXECUTE);
        //$data['isSenior'] = $data['isMaster'];////Manager::checkAccess('SENIOR', A_EXECUTE) ? 'true' : 'false';
//        $data['rgbColors'] = AnnotationService::getColor();
//        $data['colorsArray = AnnotationService::getColorArray();
        $data['layerType'] = CorpusService::getLayerType();
        $it = CorpusService::getInstantiationType();
        $data['instantiationType'] = $it['array'];
        $data['instantiationTypeObj'] = $it['obj'];
        $data['idInstantiationType'] = $it['id'];
        $data['coreIcon'] = config('webtool.fe.icon.grid');
        $data['idSentence'] = $idSentence;
        $sentence = new Sentence($idSentence);
        $data['idLanguage'] = AppService::getCurrentIdLanguage();
        $layersData = CorpusService::getLayers($data);
        $data['metadata'] = $layersData['metadata'];
        $data['words'] = $layersData['words'];
//        $data['chars = $layersData['chars'];
        $data['annotationSets'] = $layersData['annotationSets'];
        $data['layers'] = $layersData['layers'];
        $data['labelTypes'] = $layersData['labelTypes'];
        $data['layerLabels'] = $layersData['layerLabels'];
        $data['nis'] = $layersData['nis'];
//        $data['lus'] = $layersData['lus'];
        $data['layersToShow'] = [];//MUtil::php2js(fnbr\models\Base::getCurrentUser()->getConfigObject('fnbrLayers'));
//        $data['columns'] = $layersData['jsColumns'];
//        $data['frozenColumns'] = $layersData['jsFrozenColumns'];
        $data['columns'] = $layersData['columns'];
        $data['frozenColumns'] = $layersData['frozenColumns'];
        $data['keyEvent'] = null;

        return view("Panes.Corpus.annotationSentence", [
            'idSentence' => $idSentence,
            'data' => $data,
            'idSentencePrevious' => null,
            'idSentenceNext' => null,
        ]);
    }

    #[Get(path: '/annotation/corpus/lus/{idSentence}/{idWord}')]
    public function getLUs(int $idSentence, int $idWord)
    {
        $data = CorpusService::getLUs($idSentence, $idWord);
        $data['idWord'] = $idWord;
        $data['idSentence'] = $idSentence;
        return view("Panes.Corpus.lus", $data);
    }

    #[Get(path: '/annotation/corpus/sentence/{idSentence}/data')]
    public function annotationSentenceData(int $idSentence)
    {
        return CorpusService::getLayersData($idSentence);
    }

    #[Put(path: '/annotation/corpus/label')]
    public function saveLabel(SaveLabelData $data)
    {
        try {
            CorpusService::saveLabel($data);
            return $this->notify('success', 'Label updated.');
        } catch (\Exception $e) {
            return $this->notify('error', $e->getMessage());
        }
    }

    #[Put(path: '/annotation/corpus/ni')]
    public function saveNI(SaveLabelData $data)
    {
        try {
            $idLabel = CorpusService::saveNI($data);
            return response(['idLabel' => $idLabel])
                ->header('HX-Trigger', $this->notify('success', 'NI updated.'));
//            return $this->notify('success', 'NI updated.');
        } catch (\Exception $e) {
            return $this->notify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/corpus/label')]
    public function deleteLabel(DeleteLabelData $data)
    {
        try {
            CorpusService::deleteLabel($data);
            return $this->notify('success', 'Label deleted.');
        } catch (\Exception $e) {
            return $this->notify('error', $e->getMessage());
        }
    }

    #[Post(path: '/annotation/corpus/createAnnotationSet')]
    public function createAnnotationSet(CreateASData $data)
    {
        try {
            $idAnnotationSet = CorpusService::createAnnotationSet($data);
            return $this->clientRedirect("/annotation/corpus/sentence/" . $data->idSentence);
        } catch (\Exception $e) {
            return $this->notify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/corpus/annotationSet')]
    public function deleteAnnotationSet(DeleteASData $data)
    {
        try {
            CorpusService::deleteAnnotationSet($data->idAnnotationSet);
            return $this->notify('success', 'AnnotationSet deleted.');
        } catch (\Exception $e) {
            return $this->notify('error', $e->getMessage());
        }
    }

    #[Delete(path: '/annotation/corpus/annotationSet/lastFELayer')]
    public function deleteLastFELayer(DeleteLastFELayerData $data)
    {
        try {
            CorpusService::deleteLastFELayer($data->idLayer);
            return $this->notify('success', 'Layer FE deleted.');
        } catch (\Exception $e) {
            return $this->notify('error', $e->getMessage());
        }
    }

    #[Put(path: '/annotation/corpus/annotationSet/feLayer')]
    public function addFELayer(AddFELayerData $data)
    {
        try {
            CorpusService::addFELayer($data->idAnnotationSet);
            return $this->notify('success', 'New FE Layer created.');
        } catch (\Exception $e) {
            return $this->notify('error', $e->getMessage());
        }
    }

}
