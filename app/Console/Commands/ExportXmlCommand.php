<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use App\Repositories\Corpus;
use App\Repositories\Document;
use App\Services\AppService;
use Illuminate\Console\Command;

class ExportXmlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:xml {idDocument}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $idDocument = $this->argument('idDocument');
        debug($idDocument);
        $idLanguage = 2;
        //$idLanguage = $this->data->idLanguage;
        AppService::setCurrentLanguage($idLanguage);
        debug("idLanguage = " . $idLanguage);
        $document = Criteria::table("view_document")
            ->where("idDocument", $idDocument)
            ->where("idLanguage", AppService::getCurrentIdLanguage())
            ->first();
        $corpus = Criteria::table("view_corpus")
            ->where("idCorpus", $document->idCorpus)
            ->where("idLanguage", AppService::getCurrentIdLanguage())
            ->first();

        $xmlStr = <<<HERE
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<?xml-stylesheet type="text/xsl" href="fullText.xsl"?>
<fullTextAnnotation>
    <header>
        <corpus description="{$corpus->description}" name="{$corpus->name}" ID="{$corpus->idCorpus}">
            <document></document>
        </corpus>
    </header>
</fullTextAnnotation>

HERE;

        $sxe = simplexml_load_string($xmlStr);
        $sxe->header->corpus->document->addAttribute('description', $document->description);
        $sxe->header->corpus->document->addAttribute('name', $document->name);
        $sxe->header->corpus->document->addAttribute('ID', $document->idDocument);
        $sentences = Criteria::table("document_sentence as ds")
            ->join("sentence as s", "ds.idSentence", "=", "s.idSentence")
            ->where("ds.idDocument", $document->idDocument)
            ->select("s.idSentence", "s.text")
            ->all();
        $i = 0;
        foreach ($sentences as $sentence) {
            debug($sentence->idSentence . ' - ' . $sentence->text);
            $s = $sxe->addChild('sentence');
            $s->addAttribute('ID', $sentence->idSentence);
            $t = $s->addChild('text', $sentence->text);
            $annotationSets = Criteria::table("view_annotationset")
                ->where("idSentence", $sentence->idSentence)
                ->whereNotNull("idLU")
                ->all();
            foreach ($annotationSets as $annotationSet) {
                debug($annotationSet);
                $lu = Criteria::table("lu")
                    ->join("view_frame as f", "lu.idFrame", "=", "f.idFrame")
                    ->where("idLU", $annotationSet->idLU)
                    ->where("f.idLanguage", AppService::getCurrentIdLanguage())
                    ->select("lu.idLU", "lu.name", "lu.idFrame", "f.name as frameName")
                    ->first();
                $target = Criteria::table("view_annotation_text_gl")
                    ->where("idAnnotationSet", $annotationSet->idAnnotationSet)
                    ->where("name", "Target")
                    ->first();
                $aset = $s->addChild('annotationSet');
                $aset->addAttribute('ID', $annotationSet->idAnnotationSet);
                $aset->addAttribute('luID', $lu->idLU);
                $aset->addAttribute('luName', $lu->name);
                $aset->addAttribute('frameID', $lu->idFrame);
                $aset->addAttribute('frameName', $lu->frameName);
                if ($target) {
                    $aset->addAttribute('start', $target->startChar);
                    $aset->addAttribute('end', $target->endChar);
                }
                $ly = $aset->addChild('layer');
                $ly->addAttribute('name', "FE");
                $fes = Criteria::table("view_annotation_text_fe as fe")
                    ->join("view_instantiationtype as it", "it.idInstantiationType", "=", "fe.idInstantiationType")
                    ->where("idAnnotationSet", $annotationSet->idAnnotationSet)
                    ->where("it.idLanguage", AppService::getCurrentIdLanguage())
                    ->where("fe.idLanguage", AppService::getCurrentIdLanguage())
                    ->select("fe.idFrameElement", "fe.name", "fe.startChar", "fe.endChar", "it.name as itName")
                    ->all();
                foreach ($fes as $fe) {
                    $lb = $ly->addChild('label');
                    $lb->addAttribute('ID', $fe->idFrameElement);
                    $lb->addAttribute('name', $fe->name);
                    $lb->addAttribute('start', $fe->startChar);
                    $lb->addAttribute('end', $fe->endChar);
                    if ($fe->startChar == -1) {
                        $lb->addAttribute('itype', $fe->itName);
                    }
                }
                $layerTypes = Criteria::table("view_layertype")
                    ->where("idLanguage", AppService::getCurrentIdLanguage())
                    ->all();
                foreach ($layerTypes as $layerType) {
                    $gls = Criteria::table("view_annotation_text_gl")
                        ->where("idAnnotationSet", $annotationSet->idAnnotationSet)
                        ->where("name", "<>", "Target")
                        ->where("layerTypeEntry", "=", $layerType->entry)
                        ->all();
                    if (count($gls) != 0) {
                        $ly = $aset->addChild('layer');
                        $ly->addAttribute('name', $layerType->name);
                        foreach ($gls as $gl) {
                            $lb = $ly->addChild('label');
                            $lb->addAttribute('ID', $gl->idGenericLabel);
                            $lb->addAttribute('name', $gl->name);
                            $lb->addAttribute('start', $gl->startChar);
                            $lb->addAttribute('end', $gl->endChar);
                        }
                    }
                }
            }


            // debug(count($queryAS));
            $idAS = 0;
            $layer = '';
//            $labels = $queryAS->getResult();
//            foreach ($labels as $label) {
//                if ($label['idAnnotationSet'] != $idAS) {
//                    $idAS = $label['idAnnotationSet'];
//                    $aset = $s->addChild('annotationSet');
//                    $aset->addAttribute('ID', $label['idAnnotationSet']);
//                    $aset->addAttribute('luID', $label['idLU']);
//                    $aset->addAttribute('luName', $label['luName']);
//                    $aset->addAttribute('frameID', $label['idFrame']);
//                    $aset->addAttribute('frameName', $label['frameName']);
//                    $layer = '';
//                }
//                if ($layer != $label['layerTypeEntry']) {
//                    $layer = $label['layerTypeEntry'];
//                    $ly = $aset->addChild('layer');
//                    $ly->addAttribute('name', str_replace('lty_', '', $label['layerTypeEntry']));
//                }
//                $lb = $ly->addChild('label');
//                $lb->addAttribute('ID', $label['idFrameElement'] . $label['idGenericLabel']);
//                $lb->addAttribute('name', $label['feName'] . $label['glName']);
//                $lb->addAttribute('start', $label['startChar']);
//                $lb->addAttribute('end', $label['endChar']);
//                if ($label['startChar'] == -1) {
//                    $lb->addAttribute('itype', $label['instantiationType']);
//                }
//            }
            if ((++$i % 5) == 0) {
                debug($i . ' sentence(s)');
            }
        }
        $filename = "teste.xml";
        debug($filename);
        file_put_contents(__DIR__ . "/" . $filename, $sxe->asXML());

    }
}
