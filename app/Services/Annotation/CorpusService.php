<?php

namespace App\Services\Annotation;

use App\Database\Criteria;
use App\Repositories\AnnotationSet;
use App\Repositories\Corpus;
use App\Repositories\Document;
use App\Repositories\WordForm;
use App\Data\Annotation\Corpus\CreateASData;

class CorpusService
{
    public static function getAnnotationData(int $idDocumentSentence, ?int $idAnnotationSet = 0): array
    {
        $sentence = Criteria::table("view_sentence as s")
            ->join("document_sentence as ds", "s.idSentence", "=", "ds.idSentence")
            ->where("ds.idDocumentSentence", $idDocumentSentence)
            ->select("s.idSentence", "s.text", "ds.idDocumentSentence", "ds.idDocument")
            ->first();
        $words = self::getWords($sentence);
        foreach ($words as $i => $word) {
            if (!$word['hasLU']) {
                $words[$i]['hasLU'] = WordForm::wordHasLU($word['word']);
            }
        }
//        debug($words);
        $document = Document::byId($sentence->idDocument);
        $corpus = Corpus::byId($document->idCorpus);

        // previous/next
        $previousNext = BrowseService::getPreviousNext($idDocumentSentence);

        // tokens
        $tokens = [];
        $word = '';
        foreach ($words as $i => $token) {
            $tokens[$i] = $token;
            if ($token['idAS'] == $idAnnotationSet) {
                $word = $token['word'];
            }
        }

        return [
            'idDocumentSentence' => $idDocumentSentence,
            'idPrevious' => $previousNext->previous,
            'idNext' => $previousNext->next,
            'corpus' => $corpus,
            'document' => $document,
            'sentence' => $sentence,
            'text' => $sentence->text,
            'tokens' => $tokens,
            'idAnnotationSet' => $idAnnotationSet,
            'word' => $word
        ];

    }

    public static function getWords(object $sentence): array
    {
        $targets = AnnotationSet::getTargets($sentence->idDocumentSentence);
        // get words/chars
        $wordsChars = AnnotationSet::getWordsChars(htmlspecialchars_decode($sentence->text));
        $words = $wordsChars->words;
        $wordsByChar = [];
        foreach ($words as $word) {
            $wordsByChar[$word['startChar']] = $word;
        }
//        debug($wordsChars->chars);
//        debug($wordsByChar);
        $wordTarget = [];
        foreach ($targets as $target) {
            $wordTarget[$target->startChar] = [
                'word' => mb_substr($sentence->text, $target->startChar, ($target->endChar - $target->startChar + 1)),
                'startChar' => $target->startChar,
                'endChar' => $target->endChar,
                'hasLU' => true,
                'idAS' => $target->idAnnotationSet
            ];
        }
        $wordList = [];
        $nextChar = 0;
        while ($nextChar < count($wordsChars->chars)) {
            if (isset($wordTarget[$nextChar])) {
                $wordList[] = $wordTarget[$nextChar];
                $nextChar = $wordTarget[$nextChar]['endChar'] + 1;
            } else {
                $wordList[] = [
                    'word' => $wordsByChar[$nextChar]['word'],
                    'startChar' => $wordsByChar[$nextChar]['startChar'],
                    'endChar' => $wordsByChar[$nextChar]['endChar'],
                    'hasLU' => false,
                    'idAS' => -1
                ];
                $nextChar = $wordsByChar[$nextChar]['endChar'] + 1;
            }
        }
        return $wordList;
    }

    public static function getLUs(int $idDocumentSentence, int $idWord): array
    {
        $sentence = Criteria::table("view_sentence as s")
            ->join("document_sentence as ds", "s.idSentence", "=", "ds.idSentence")
            ->where("ds.idDocumentSentence", $idDocumentSentence)
            ->select("s.idSentence", "s.text", "ds.idDocumentSentence", "ds.idDocument")
            ->first();
        $words = self::getWords($sentence);
        $wordsToShow = [];
        for ($i = $idWord - 10; $i <= $idWord + 10; $i++) {
            if (isset($words[$i])) {
                if($words[$i]['idAS'] != -1) {
                    if ($i >= $idWord) {
                        break;
                    } else {
                        $wordsToShow = [];
                        continue;
                    }
                };
                if ($words[$i]['word'] != ' ') {
                    $wordsToShow[$i] = $words[$i];
                }
            }
        }
        return [
            'lus' => WordForm::getLUs($words[$idWord]['word']),
            'words' => $wordsToShow,
            'idWord' => $idWord,
            'idDocumentSentence' => $idDocumentSentence
        ];
    }

    public static function createAnnotationSet(CreateASData $data): ?int
    {
        $startChar = 4000;
        $endChar = -1;
        foreach ($data->wordList as $word) {
            if ($word->startChar < $startChar) {
                $startChar = $word->startChar;
            }
            if ($word->endChar > $endChar) {
                $endChar = $word->endChar;
            }
        }
        $idAnnotationSet = null;
        if (($startChar != -1) && ($endChar != 4000)) {
            $idAnnotationSet = AnnotationSet::createForLU($data->idDocumentSentence, $data->idLU, $startChar, $endChar);
        }
        return $idAnnotationSet;
    }





//
//
//    public static function listSentences(SearchData $data): array
//    {
//        $sentences = Sentence::listByFilter($data)->get()->keyBy('idSentence')->toArray();
//        if (!empty($sentences)) {
//            $targets = collect(AnnotationSet::listTargetsForSentence(array_keys($sentences)))->groupBy('idSentence')->toArray();
//            foreach ($sentences as $idSentence => $sentence) {
//                if (isset($targets[$idSentence])) {
//                    $sentence->text = self::decorateSentence($sentence->text, $targets[$idSentence]);
//                }
//            }
//        }
//        return $sentences;
//    }
//
//    public static function listDocuments(SearchData $data): array
//    {
//        return Document::listByFilter($data)->get()->keyBy('idDocument')->all();
//    }
//
//    public static function listCorpus(SearchData $data): array
//    {
//        return Corpus::listByFilter($data)->get()->keyBy('idCorpus')->all();
//    }
//
//    public static function getLayerType(): object
//    {
//        $lts = LayerType::list();
//        $result = new \stdclass();
//        foreach ($lts as $row) {
//            $node = new \stdclass();
//            $node->entry = $row->entry;
//            $node->name = $row->name;
//            $idLT = $row->idLayerType;
//            $result->$idLT = $node;
//        }
//        return $result;
//    }
//
//    public static function getInstantiationType(): array
//    {
//        $instances = Type::getInstantiationType()->all();
//        $array = [];
//        $id = [];
//        $obj = new \stdclass();
//        foreach ($instances as $instance) {
//            if ($instance->instantiationType != 'APos') {
//                $value = $instance->idInstantiationType;
//                $obj->$value = $instance->instantiationType;
//                $node = new \stdclass();
//                $id[$instance->instantiationType] = $instance->idInstantiationType;
//                $node->value = $instance->idInstantiationType;
//                $node->label = $instance->instantiationType;
//                $array[] = $node;
//            }
//        }
//        $result = [
//            'id' => $id,
//            'array' => $array,
//            'obj' => $obj
//        ];
//        return $result;
//    }
//
//    public static function getColor()
//    {
//        $colors = Color::list();
//        $result = new \stdclass();
//        foreach ($colors as $c) {
//            $node = new \stdclass();
//            $node->rgbFg = '#' . $c->rgbFg;
//            $node->rgbBg = '#' . $c->rgbBg;
//            $idColor = $c->idColor;
//            $result->$idColor = $node;
//        }
//        return $result;
//    }
//
//    public static function getColorArray()
//    {
//        $colors = Color::list();
//        $result = [];
//        foreach ($colors as $c) {
//            $node = new \stdclass();
//            $node->rgbFg = '#' . $c->rgbFg;
//            $node->rgbBg = '#' . $c->rgbBg;
//            $idColor = $c->idColor;
//            $result[$idColor] = $node;
//        }
//        return $result;
//    }
//
//    public static function decorateSentence($sentence, $labels)
//    {
//        $decorated = "";
//        $ni = "";
//        $i = 0;
//        foreach ($labels as $label) {
//            //$style = 'background-color:#' . $label['rgbBg'] . ';color:#' . $label['rgbFg'] . ';';
//            if ($label->startChar >= 0) {
//                $decorated .= mb_substr($sentence, $i, $label->startChar - $i);
//                $decorated .= "<span class='color_target'>" . mb_substr($sentence, $label->startChar, $label->endChar - $label->startChar + 1) . "</span>";
//                $i = $label->endChar + 1;
//            } else { // null instantiation
//                $ni .= "<span class='color_target'>" . $label->instantiationType . "</span> " . $decorated;
//            }
//        }
//        $decorated = $ni . $decorated . mb_substr($sentence, $i);
//        return $decorated;
//    }
//
//    public static function getWordsChars(object $sentence): object
//    {
//        $idSentence = $sentence->idSentence;
//        $targets = AnnotationSet::getTargets($idSentence);
//        // get words/chars
//        $wordsChars = AnnotationSet::getWordsChars($idSentence);
////        debug($wordsChars);
//        $words = $wordsChars->words;
//        $wordsByChar = [];
//        foreach ($words as $word) {
//            $wordsByChar[$word['startChar']] = $word;
//        }
////        debug($wordsChars->chars);
//        $wordTarget = [];
//        foreach ($targets as $target) {
//            $wordTarget[$target->startChar] = [
//                'word' => mb_substr($sentence->text, $target->startChar, ($target->endChar - $target->startChar + 1)),
//                'startChar' => $target->startChar,
//                'endChar' => $target->endChar,
//                'hasLU' => true,
//                'idAS' => $target->idAnnotationSet
//            ];
//        }
//        $wordList = [];
//        $nextChar = 0;
//        while ($nextChar < count($wordsChars->chars)) {
//            if (isset($wordTarget[$nextChar])) {
//                $wordList[] = $wordTarget[$nextChar];
//                $nextChar = $wordTarget[$nextChar]['endChar'] + 1;
//            } else {
//                $wordList[] = [
//                    'word' => $wordsByChar[$nextChar]['word'],
//                    'startChar' => $wordsByChar[$nextChar]['startChar'],
//                    'endChar' => $wordsByChar[$nextChar]['endChar'],
//                    'hasLU' => false
//                ];
//                $nextChar = $wordsByChar[$nextChar]['endChar'] + 1;
//            }
//        }
//
//        return (object)[
//            'words' => $wordList,
//            'chars' => $wordsChars->chars
//        ];
//    }
//
//    public static function getLUs(int $idSentence, int $idWord): array
//    {
//        $sentence = Sentence::getById($idSentence);
//        $words = self::getWordsChars($sentence)->words;
//        $wordsToShow = [];
//        for ($i = $idWord - 10; $i <= $idWord + 10; $i++) {
//            if (isset($words[$i])) {
//                if ($words[$i]['word'] != ' ') {
//                    $wordsToShow[$i] = $words[$i];
//                }
//            }
//        }
//        return [
//            'lus' => WordForm::getLUs($words[$idWord]['word']),
//            'words' => $wordsToShow,
//        ];
//
//    }
//
//
//    public static function getLayers($data)
//    {
//        $layers = [];
//        $params = (object)$data;
//        $idSentence = $params->idSentence;
//        $sentence = Sentence::getById($idSentence);
//        $documents = Sentence::getAssociation('documents', $sentence->idSentence);
//        debug($documents);
//        $layers['metadata'] = [
//            'sentence' => '#' . $idSentence,
//            'documents' => []
//        ];
//        foreach ($documents as $doc) {
//            $document = Document::getById($doc->idDocument);
//            $corpus = Corpus::getById($document->idCorpus);
//            $layers['metadata']['documents'][] = $corpus->name . '.' . $document->name;
//        }
//
//        $language = Language::getById($params->idLanguage);
//        // get words/chars
//
//        $wordsChars = self::getWordsChars($sentence);
//        //debug($wordsChars);
//        /*
//        $words = $wordsChars->words;
//        $wordList = [];
//        foreach ($words as $i => $word) {
//            $words[$i]['hasLU'] = false;
//            $wordList[$i] = trim(strtolower($word['word']));
//        }
//        $wordLU = [];
//        $listLUs = WordForm::listLU($wordList);
//        foreach ($wordList as $i => $word) {
//            if (isset($listLUs[$word])) {
//                $words[$i]['hasLU'] = true;
//                $wordLU[$i] = $listLUs[$word];
//            }
//        }
//        $layers['lus'] = $wordLU;
//        $chars = $wordsChars->chars;
//        $result = [];
//        foreach ($words as $i => $word) {
//            $fieldData = $i;
//            $result[$fieldData] = (object)[
//                'word' => $word['word'],
//                'startChar' => $word['startChar'],
//                'endChar' => $word['endChar'],
//                'hasLU' => $word['hasLU']
//            ];
//        }
//        $layers['words'] = $result;
//        */
//        $words = $wordsChars->words;
//        foreach ($words as $i => $word) {
//            if (!$word['hasLU']) {
//                $words[$i]['hasLU'] = WordForm::wordHasLU($word['word']);
//            }
//        }
//        $layers['words'] = $words;
//
//        $header = "[Sentence: #{$idSentence}] ";
//        // get hiddenColumns/frozenColumns/Columns using $words
//        $frozenColumns[] = array(
//            "field" => "layer",
//            "width" => '70',
//            "title" => $header,
//            "formatter" => ":annotationMethods.cellLayerFormatter",
//            "styler" => ":annotationMethods.cellStyler"
//        );
//        $columns[] = array("field" => "idAnnotationSet", "type" => 'data');
//        $columns[] = array("field" => "idLayerType", "type" => 'data');
//        $columns[] = array("field" => "idLayer", "type" => 'data');
//
//        // charWidth
//        $charWidth = 16;
////        if ($language == 'jp') {
////            $charWidth = 18;
////        }
////        if ($language == 'hi') {
////            $charWidth = 18;
////        }
////        if ($language == 'te') {
////            $charWidth = 18;
////        }
////        if ($language == 'kn') {
////            $charWidth = 18;
////        }
////        if ($language == 'zh') {
////            $charWidth = 18;
////        }
////        if ($language == 'fa') {
////            $charWidth = 18;
////        }
//
//        foreach ($wordsChars->chars as $i => $char) {
//            $columns[] = array(
//                "type" => "char",
//                "width" => $charWidth,
//                "title" => $char['char'],
//                'offset' => (int)$char['offset'],
//                'char' => $char['char'],
//                'index' => $i,
//                'field' => 'c' . $i,
//                'word' => $char['order'] - 1,
//                'hasLU' => $wordsChars->words[$char['order'] - 1]['hasLU']
//            );
//        }
//        $layers['columns'] = $columns;
//        $layers['frozenColumns'] = $frozenColumns;
//
////        $layers['jsColumns'] = $columns;
////        $layers['jsFrozenColumns'] = $frozenColumns;
//
//        // get Layers
//        $result = [];
//        $asLayers = AnnotationSet::getLayersSentence($idSentence);
//        foreach ($asLayers as $row) {
//            $result[$row->idLayer] = [
//                'idAnnotationSet' => $row->idAnnotationSet,
//                'nameLayer' => $row->name,
//                'currentLabel' => '0',
//                'currentLabelPos' => 0
//            ];
//        }
//
//        // CE-FE is a "artificial" layer; it needs to be inserts manually
//        $queryLabelType = AnnotationSet::getLabelTypesCEFE($idSentence);
//        $rowsCEFE = $queryLabelType;//->getResult();
//        foreach ($rowsCEFE as $row) {
//            $result[$row->idLayer] = [
//                'idAnnotationSet' => $row->idAnnotationSet,
//                'nameLayer' => $row->idLayer,
//                'currentLabel' => '0',
//                'currentLabelPos' => 0
//            ];
//        }
//
//        $layers['layers'] = $result;//json_encode($result);
//
//        // get AnnotationSets
//        $result = [];
//        $annotationSets = AnnotationSet::getAnnotationSetsBySentence($idSentence);
//        foreach ($annotationSets as $row) {
//            $result[$row->idAnnotationSet] = [
//                'idAnnotationSet' => $row->idAnnotationSet,
//                'name' => $row->name,
//                'type' => $row->type,
//                'show' => true,
//                'annotatedFEs' => (object)[]
//            ];
//        }
//        $layers['annotationSets'] = $result;
//
//        // get LabelTypes
//        $result = [];
//        $layerLabels = [];
//        $layerLabelsTemp = [];
//
//        // GL-GF
//        $queryLabelType = AnnotationSet::getLabelTypesGLGF($idSentence);
//        $rows = $queryLabelType->all();
//        foreach ($rows as $row) {
//            $layerLabels[$row->entry][$row->idLabelType] = $row->idLabelType;
//            $result[$row->idLabelType] = [
//                'label' => $row->labelType,
//                'idColor' => $row->idColor,
//                'coreType' => $row->coreType
//            ];
//            if (!isset($layers['annotationSets'][$row->idAnnotationSet]['idLayerGF'])) {
//                $layers['annotationSets'][$row->idAnnotationSet]['idLayerGF'] = $row->idLayer;
//            }
//        }
//        // GL
//        $queryLabelType = AnnotationSet::getLabelTypesGL($idSentence);
//        $rows = $queryLabelType->all();
//        foreach ($rows as $row) {
//            $layerLabels[$row->entry][$row->idLabelType] = $row->idLabelType;
//            $result[$row->idLabelType] = [
//                'label' => $row->labelType,
//                'idColor' => $row->idColor,
//                'coreType' => $row->coreType
//            ];
//            if ($row->entry == 'lty_pt') {
//                if (!isset($layers['annotationSets'][$row->idAnnotationSet]['idLayerPT'])) {
//                    $layers['annotationSets'][$row->idAnnotationSet]['idLayerPT'] = $row->idLayer;
//                }
//            }
//        }
//        // FE
//        $queryLabelType = AnnotationSet::getLabelTypesFE($idSentence);
//        $rows = $queryLabelType;//->getResult();
//        foreach ($rows as $row) {
//            $layerLabels['lty_fe'][$row->idAnnotationSet][$row->idLabelType] = $row->idLabelType;
//            $result[$row->idLabelType] = [
//                'label' => $row->labelType,
//                'idColor' => $row->idColor,
//                'coreType' => $row->coreType
//            ];
//        }
//        // CE
//        $queryLabelType = AnnotationSet::getLabelTypesCE($idSentence);
//        $rows = $queryLabelType;
//        foreach ($rows as $row) {
//            $layerLabels['lty_fe'][$row->idAnnotationSet][$row->idLabelType] = $row->idLabelType;
//            $result[$row->idLabelType] = [
//                'label' => $row->labelType,
//                'idColor' => $row->idColor,
//                'coreType' => $row->coreType
//            ];
//        }
//        // CE-FE - $rowsCEFE is obtained via query for layer above
//        foreach ($rowsCEFE as $row) {
////            if (!isset($layerLabelsTemp[$row['idLayer']][$row['idLabelType']])) {
////                $layerLabels[$row['idLayer']][] = $row['idLabelType'];
////                $layerLabelsTemp[$row['idLayer']][$row['idLabelType']] = 1;
////            }
//            $result[$row->idLabelType] = [
//                'label' => $row->labelType,
//                'idColor' => $row->idColor,
//                'coreType' => $row->coreType
//            ];
//        }
//
//
//        // UDTree
//        $UDTreeLayer = [];
//        $UDTreeLayer['none'] = '';
//        /*
//        $queryUDTree = $as->getUDTree($idSentence);
//        $rows = $queryUDTree->getResult();
//        foreach ($rows as $row) {
//            if (!isset($UDTree[$row['idLayer']])) {
//                $UDTree[$row['idLayer']][$row['idLabel']] = $row['idLabelParent'];
//            }
//        }
//        */
//
//        $layers['labelTypes'] = $result;
//        $layers['layerLabels'] = $layerLabels;
//        $layers['UDTreeLayer'] = $UDTreeLayer;
//
//        // get NIs
//        $result = [];
//        $queryNI = AnnotationSet::getNI($idSentence, $params->idLanguage);
//        $rows = $queryNI;//->getResult();
//        foreach ($rows as $row) {
//            $result[$row->idLayer][$row->idLabelType] = [
//                'idLabel' => $row->idLabel,
//                'fe' => $row->feName,
//                'idEntityFE' => $row->idLabelType,
//                'idInstantiationType' => (int)$row->idInstantiationType,
//                'label' => $row->instantiationType,
//                'idColor' => (int)$row->idColor
//            ];
//        }
//        $layers['nis'] = (count($result) > 0) ? $result : [];
//        $layers['data'] = 'null';
////        debug($layers['annotationSets']);
//        return $layers;
//    }
//
//    public static function getLayersData(int $idSentence)
//    {
////        $idSentence = $params->idSentence;
//        //$idAnnotationSet = $params->idAnnotationSet ?? null;
//
//        //$as = new AnnotationSet();
////        if (is_null($idAnnotationSet)) {
////            $idLU = $idCxn = NULL;
////        } else {
////            $as->getById($idAnnotationSet);
////            $idLU = $as->getLU()->getIdLU();
////            $idCxn = $as->getCxn()->getIdConstruction();
////        }
////        $isCxn = ($idLU == NULL) && ($idCxn != NULL);
////
////        $result = array();
//        // dados de todos os labels de todos os AS
//        $labels = AnnotationSet::getLayersData($idSentence);
//
//        // get the annotationsets - first ordered by target then the other which has no target (cxn)
//        $layersOrderedByTarget = AnnotationSet::getLayersOrderByTarget($idSentence);
//        $aSet = [];
//        $aTarget = [];
//        foreach ($layersOrderedByTarget as $layersOrdered) {
//            $aTarget[$layersOrdered->idAnnotationSet] = 1;
//            foreach ($labels as $label) {
//                if ($layersOrdered->idAnnotationSet == $label->idAnnotationSet) {
//                    $aSet[$label->idAnnotationSet][] = $label;
//                }
//            }
//        }
//        foreach ($labels as $label) {
//            if ($aTarget[$label->idAnnotationSet] == '') {
//                $aSet[$label->idAnnotationSet][] = $label;
//            }
//        }
//
//        // reorder layers to put Target on top of each annotatioset
//        $labels = [];
//        $idHeaderLayer = -1;
//
//        foreach ($aSet as $asRows) {
//            $hasTarget = false;
//            foreach ($asRows as $asRow) {
//                if ($asRow->layerTypeEntry == 'lty_target') {
//                    $asRow->idLayerType = 0;
//                    $labels[] = $asRow;
//                    $hasTarget = true;
//                }
//            }
//            if ($hasTarget) {
//                foreach ($asRows as $asRow) {
//                    if ($asRow->layerTypeEntry != 'lty_target') {
//                        $labels[] = $asRow;
//                    }
//                }
//            } else {
//                $headerLayer = $asRows[0];
//                $headerLayer['layer'] = 'x';
//                $headerLayer['startChar'] = -1;
//                $headerLayer['idLayerType'] = 0;
//                $headerLayer['layerTypeEntry'] = 'lty_as';
//                $headerLayer['idLayer'] = $idHeaderLayer--;
//                $labels[] = $headerLayer;
//                foreach ($asRows as $asRow) {
//                    $labels[] = $asRow;
//                }
//            }
//        }
//        //mdump($rows);
//        // CE-FE
//        $ltCEFE = LayerType::getByEntry('lty_cefe');
//        $queryLabelType = AnnotationSet::getLayerNameCnxFrame($idSentence);
//        $cefe = $queryLabelType;
//
//        $user = AppService::getCurrentUser() ?? null;
//        $layersToShow = $user ? (session('fnbrLayers') ?? unserialize($user->config)->fnbrLayers) : [];
//
//        $wordsChars = AnnotationSet::getWordsChars($idSentence);
//        $chars = $wordsChars->chars;
//
//        $line = [];
//// line for targets
////        $line[-1] = (object)[
////            'idAnnotationSet' => -1,
////            'idLayerType' => -1,
////            'layerTypeEntry' => 'lty_all_targets',
////            'idLayer' => 0,
////            'layer' => '',
////            'ni' => 'NI',
////            'show' => true,
////        ];
////        foreach ($chars as $i => $char) {
////            $field = 'c' . $i;
////            $line[-1]->$field = (object)[
////                'char' => $char['char'],
////                'idLabelType' => 0,
////                'status' => 0,
////                'order' => $i
////            ];
////        }
//        $targetLabels = [];
//        foreach ($labels as $label) {
//            if ($label->layerTypeEntry == 'lty_target') {
//                $targetLabels[$label->idAnnotationSet] = [$label->startChar, $label->endChar];
//            }
//        }
//
//        $idLayerRef = 0;
//        $idAnnotationSetRef = 0;
//        $countFELayer = 0;
//        $currentIdLayerFE = 0;
//        $feChars = [];
//        // the loop aggregates labels in Layers
//        foreach ($labels as $label) {
//            $idLT = $label->idLayerType;
//            if ($idLT != 0) {
//                if (!in_array($idLT, $layersToShow)) {
//                    continue;
//                }
//            }
//            if ($label->idAnnotationSet != $idAnnotationSetRef) {
//                $countFELayer = 0;
//                $idAnnotationSetRef = $label->idAnnotationSet;
//                $currentIdLayerFE = 0;
//                $feChars = [];
//            }
//            $idLayer = $label->idLayer;
//            if ($idLayer != $idLayerRef) {
//                if (($label->layerTypeEntry == 'lty_fe') && ($currentIdLayerFE == 0)) {
//                    $currentIdLayerFE = $idLayer;
//                }
//                $line[$idLayer] = new \stdclass();
//                // preenche todos os fields com o valor default
//                foreach ($chars as $i => $char) {
//                    $field = 'c' . $i;
//                    $status = 0;
//                    $startChar = $endChar = -1;
//                    if (($label->layerTypeEntry == 'lty_gf') || ($label->layerTypeEntry == 'lty_pt')) {
//                        if (!empty($feChars)) {
//                            if (in_array($i, $feChars)) {
//                                $status = 1;
//                                $startChar = $line[$currentIdLayerFE]->$field->startChar;
//                                $endChar = $line[$currentIdLayerFE]->$field->endChar;
//                            }
//                        }
//                    }
//                    if (($i >= $targetLabels[$idAnnotationSetRef][0]) && ($i <= $targetLabels[$idAnnotationSetRef][1])) {
//                        $status = 3;
//                    }
//                    $line[$idLayer]->$field = (object)[
//                        'char' => $char['char'],
//                        'idLabelType' => 0,
//                        'status' => $status,
//                        'index' => $i,
//                        'word' => $char['order'] - 1,
//                        'column' => $i + 3,
//                        'startChar' => $startChar,
//                        'endChar' => $endChar
//                    ];
//                }
//                $lastLayerTypeEntry = $label->layerTypeEntry;
//                $extraFELayer = false;
//                if ($lastLayerTypeEntry == 'lty_fe') {
//                    ++$countFELayer;
//                    if ($countFELayer > 1) {
//                        $extraFELayer = true;
//                    }
//                }
//                $line[$idLayer]->idAnnotationSet = (int)$label->idAnnotationSet;
//                $line[$idLayer]->idLayerType = (int)$label->idLayerType;
//                $line[$idLayer]->layerTypeEntry = $label->layerTypeEntry;
//                $line[$idLayer]->idLayer = (int)$idLayer;
//                $line[$idLayer]->extraFELayer = $extraFELayer;
//                if ($label->idLayerType == 0) { // Target
//                    $line[$idLayer]->layer = 'AS_' . $label->idAnnotationSet;
//                } else {
//                    $line[$idLayer]->layer = $label->layer;
//                }
//                $line[$idLayer]->show = true;
//                $idLayerRef = $idLayer;
//                // if lastLayer=CE, try to add the layers for CE-FE
//                if ($lastLayerTypeEntry == 'lty_ce') {
//                    foreach ($cefe as $frame) {
//                        if ($frame['idAnnotationSet'] == $label['idAnnotationSet']) {
//                            $idLayerCEFE = $frame['idLayer'];
//                            $line[$idLayerCEFE] = new \stdclass();
//                            $line[$idLayerCEFE]->idAnnotationSet = (int)$label['idAnnotationSet'];
//                            $line[$idLayerCEFE]->idLayerType = $ltCEFE->getId();
//                            $line[$idLayerCEFE]->layerTypeEntry = $idLayerCEFE;
//                            $line[$idLayerCEFE]->idLayer = (int)$idLayerCEFE;
//                            $line[$idLayerCEFE]->layer = $frame['name'] . '.FE';
//                            $line[$idLayerCEFE]->show = true;
//                            $cefeData = AnnotationSet::getCEFEData($idSentence, $idLayerCEFE, $label['idAnnotationSet']);
//                            foreach ($cefeData as $labelCEFE) {
//                                if ($labelCEFE['startChar'] > -1) {
//                                    $posChar = $labelCEFE['startChar'];
//                                    while ($posChar <= $labelCEFE['endChar']) {
//                                        $field = 'c' . $posChar;
//                                        $line[$idLayerCEFE]->$field = (object)[
//                                            'char' => '',
//                                            'idLabelType' => $labelCEFE['idLabelType']
//                                        ];
//                                        $posChar += 1;
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//            $targetChars = [];
//            if ($label->startChar > -1) {
//                $posChar = $label->startChar;
//                while ($posChar <= $label->endChar) {
//                    $field = 'c' . $posChar;
////                    $line[$idLayer]->$field = (object)[
////                        'char' => $chars[$posChar]['char'],
////                        'idLabelType' => $label->idLabelType,
////                        'status' => 2
////                    ];
//                    $line[$idLayer]->$field->idLabelType = (int)$label->idLabelType;
//                    $line[$idLayer]->$field->status = 2;
//                    $line[$idLayer]->$field->startChar = (int)$label->startChar;
//                    $line[$idLayer]->$field->endChar = (int)$label->endChar;
//                    if ($currentIdLayerFE == $idLayer) {
//                        $feChars[] = $posChar;
//                    };
////                    if ($label->layerTypeEntry == 'lty_target') {
////                        $targetChars[$label->idAnnotationSet][] = $posChar;
////                    }
//                    $posChar += 1;
//                }
//            }
//        }
//
//// last, create data
//        $data = [];
//        foreach ($line as $layer) {
//            //if (($idAnnotationSet == 0) || ($idAnnotationSet == $layer->idAnnotationSet)) {
//            $data[] = $layer;
//            //}
//        }
////        mdump($data);
//        return $data;
//    }
//
//    public static function saveLabel(SaveLabelData $data): void
//    {
//        Label::update($data);
//    }
//
//    public static function saveNI(SaveLabelData $data): int
//    {
//        return Label::save($data);
//    }
//
//    public static function deleteLabel(DeleteLabelData $data): void
//    {
//        debug($data);
//        if (!is_null($data->idLabel)) {
//            Label::delete($data->idLabel);
//        } else {
//            Label::getCriteria()
//                ->where("idLayer", "=", $data->idLayer)
//                ->where("startChar", "=", $data->startChar)
//                ->delete();
//        }
//        /*
//        $label = new Label();
//        $criteria = $label->getCriteria()
//            ->where("idLayer", "=", $data->idLayer)
//            ->where("startChar", "=", $data->startChar);
//        $label->retrieveFromCriteria($criteria);
//        $label->delete();
//        */
//    }
//
////    public static function createAnnotationSet(object $data): void
////    {
////        $as = new AnnotationSet();
////        $as->createForLU($data->idSentence, $data->idLU, $data->startChar, $data->endChar);
////    }
//
//    public static function createAnnotationSet(CreateASData $data): ?int
//    {
//        $startChar = 4000;
//        $endChar = -1;
//        foreach ($data->wordList as $word) {
//            if ($word->startChar < $startChar) {
//                $startChar = $word->startChar;
//            }
//            if ($word->endChar > $endChar) {
//                $endChar = $word->endChar;
//            }
//        }
//        $idAnnotationSet = null;
//        if (($startChar != -1) && ($endChar != 4000)) {
//            $idAnnotationSet = AnnotationSet::createForLU($data->idSentence, $data->idLU, $startChar, $endChar);
//        }
//        return $idAnnotationSet;
//    }
//
//
//    public static function deleteAnnotationSet(int $idAnnotationSet): void
//    {
//        AnnotationSet::delete($idAnnotationSet);
//    }
//
//    public static function addFELayer(int $idAnnotationSet): void
//    {
//        AnnotationSet::addFELayer($idAnnotationSet);
//    }
//
//    public static function deleteLastFELayer(int $idLayer): void
//    {
//        AnnotationSet::deleteLastFELayer($idLayer);
//    }

}
