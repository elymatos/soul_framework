<?php

namespace App\Console\Commands;

use App\Data\Annotation\FE\AnnotationData;
use App\Data\Annotation\FE\SelectionData;
use App\Database\Criteria;
use App\Repositories\AnnotationSet;
use App\Services\Annotation\FEService;
use App\Services\LOME\LOMEService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LomeProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:lome-process-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process sentences from database to LOME tables';

    public function init()
    {
        ini_set("memory_limit", "10240M");
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->init();
//            $frameNames = Criteria::table("view_frame as f")
//                ->select("f.idFrame", "f.name")
//                ->where("f.idLanguage", 1)
//                ->chunkResult("idFrame", "name");
//            $feNames = Criteria::table("view_frameelement as fe")
//                ->select("fe.idFrameElement", "fe.name")
//                ->where("fe.idLanguage", 1)
//                ->chunkResult("idFrameElement", "name");
//            $idSpan = 0;
            $lome = new LOMEService();
            $sentences = DB::connection('webtool')
                ->select("
                select s.idSentence, s.text,s.idOriginMM,ds.idDocumentSentence,s.idLanguage
from sentence s
join document_sentence ds on (s.idSentence = ds.idSentence)
join document d on (ds.idDocument = d.idDocument)
where d.idCorpus between 204 and 217 LIMIT 10
                ");
            debug(count($sentences));
            $s = 0;
            foreach ($sentences as $sentence) {
                ++$s;
                try {
                    $text = trim($sentence->text);
                    $as = Criteria::table("annotationset")
                        ->where("idSentence", $sentence->idSentence)
                        ->where("lome", "S")
                        ->first();
                    if ($as) {
                        Criteria::function("annotationset_delete", [$as->idAnnotationSet, 6]);
                    }
                    $result = $lome->parse($text);
                    if (is_array($result)) {
                        $result = $result[0];
                        $tokens = $result->tokens;
                        $annotations = $result->annotations;
//                        print_r($annotations);
//                        print_r($tokens);
                        foreach ($annotations as $annotation) {
//                        print_r($annotation);
                            $x = explode('_', strtolower($annotation->label));
                            $idFrame = $x[1];
                            $startChar = $annotation->char_span[0];
                            $endChar = $annotation->char_span[1];
                            $word = '';
                            for ($t = $annotation->span[0]; $t <= $annotation->span[1]; $t++) {
                                $word .= $tokens[$t] . ' ';
                            }
                            $lexicon = Criteria::table("view_lexicon_form as lf")
                                ->where("idLanguage", $sentence->idLanguage)
                                ->where("form", trim($word))
                                ->first();
                            $idLexicon = $lexicon ? $lexicon->idLexicon : null;
                            $idAnnotationSet = AnnotationSet::createForLOME($sentence->idDocumentSentence, $idFrame, $startChar, $endChar, $sentence->idLanguage,$idLexicon);
                            foreach ($annotation->children as $fe) {
                                $x = explode('_', strtolower($fe->label));
                                $idFrameElement = $x[1];
                                $startChar = $fe->char_span[0];
                                $endChar = $fe->char_span[1];
//                                $word = '';
//                                for ($t = $fe->span[0]; $t <= $fe->span[1]; $t++) {
//                                    $word .= $tokens[$t] . ' ';
//                                }
//                                $selectionData = SelectionData::from([
//                                    "type" => "word",
//                                    "start" => $startChar,
//                                    "end" => $endChar,
//                                ]);
                                $selectionData = new SelectionData("word","",$startChar,$endChar);
                                $annotationData = AnnotationData::from([
                                    "idAnnotationSet" => $idAnnotationSet,
                                    "idFrameElement" => $idFrameElement,
                                    "range" => $selectionData
                                ]);
                                FEService::annotateFELOME($annotationData, $sentence->idLanguage);
                            }
                        }
                    }
                    //if ($s > 5) die;
                } catch (\Exception $e) {
                    print_r($sentence->idSentence . ":" . $e->getMessage());
                    die;
                }
                //break;
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }
}
