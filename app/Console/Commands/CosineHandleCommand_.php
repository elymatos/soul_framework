<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use App\Services\AppService;
use App\Services\CosineService;
use Illuminate\Console\Command;

class CosineHandleCommand_ extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cosine:handle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cosine similarity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set("memory_limit", "10240M");
        AppService::setCurrentLanguage(1);
        //CosineService::createFrameNetwork();
//        CosineService::createLinkSentenceAnnotationTimeToFrame(614);
//        CosineService::createLinkSentenceAnnotationTimeToFrame(638);
//        CosineService::createLinkSentenceAnnotationTimeToFrame(617);
//        CosineService::createLinkSentenceAnnotationTimeToFrame(619);
//        CosineService::createLinkSentenceAnnotationTimeToFrame(631);
//        CosineService::createLinkSentenceAnnotationTimeToFrame(626);
//
        $array = [614,638,617,619,631,626,502,507,508,509,510,511,512,513,515,516];
        //$array = [619,631,626,502,507,508,509,510,511,512,513,515,516];
        //$array = [502,507,508,509,510,511,512,513,515,516];
        //$array = [614];
        foreach($array as $idDocument){
            CosineService::createLinkSentenceAnnotationTimeToFrame($idDocument);
            CosineService::createLinkObjectAnnotationTimeToFrame($idDocument);
            $document = Criteria::byId("document","idDocument",$idDocument);
            CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_original_full_2.csv", CosineService::compareTimespan($idDocument, 4, ''));
            CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_original_lu_2.csv", CosineService::compareTimespan($idDocument, 4, 'lu'));
            CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_original_fe_2.csv", CosineService::compareTimespan($idDocument, 4, 'fe'));
            if ($idDocument > 520) {
                CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_description_full_2.csv", CosineService::compareTimespan($idDocument, 7, ''));
                CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_description_lu_2.csv", CosineService::compareTimespan($idDocument, 7, 'lu'));
                CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_description_fe_2.csv", CosineService::compareTimespan($idDocument, 7, 'fe'));
            }
        }

//        CosineService::createLinkObjectAnnotationTimeToFrame(614);
//        CosineService::createLinkObjectAnnotationTimeToFrame(638);
//        CosineService::createLinkObjectAnnotationTimeToFrame(617);
//        CosineService::createLinkObjectAnnotationTimeToFrame(619);
//        CosineService::createLinkObjectAnnotationTimeToFrame(631);
//        CosineService::createLinkObjectAnnotationTimeToFrame(626);

//        CosineService::createLinkSentenceAnnotationToFrame(1478);
//        CosineService::createLinkSentenceAnnotationToFrame(1479);
//        $pairs = [
//            [602476, 602485],
//            [602477, 602486],
//            [602478, 602487],
//            [602479, 602488],
//            [602480, 602489],
//            [602481, 602490],
//            [602482, 602491],
//            [602483, 602492],
//            [602484, 602493]
//        ];
//        foreach($pairs as $pair) {
//            CosineService::compareSentences($pair[0], $pair[1]);
//        }


    }
}
