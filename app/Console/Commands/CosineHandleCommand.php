<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use App\Services\AppService;
use App\Services\CosineService;
use Illuminate\Console\Command;

class CosineHandleCommand extends Command
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

        /* Construções Natália - 04/07/2025 */
        //CosineService::createMoccaNetwork();
        /*
        idConstruction,cxIdLanguage,idEntity,name
        372,1,1561790,Futuro_do_Indicativo
        390,1,2740344,Futuro_do_pretérito_do_indicativo
        371,1,1561788,Presente_do_Indicativo
        376,1,1561815,Pretérito_Imperfeito_do_Indicativo
        408,1,2740610,Pretérito_mais_que_perfeito_composto_do_indicativo
        377,1,1561818,Pretérito_Mais_Que_Perfeito_do_Indicativo
        418,1,2740715,Pretérito_perfeito_composto_do_indicativo
        373,1,1561805,Pretérito_Perfeito_do_Indicativo
        401,3,2740562,Condicional_del_indicativo
        381,3,1561832,Futuro_de_indicativo
        380,3,1561829,Presente_de_indicativo
        384,3,1561839,Pretérito_imperfecto_de_indicativo
        396,3,2740381,Pretérito_perfecto_compuesto_de_indicativo
        382,3,1561835,Pretérito_perfecto_simple_de_indicativo
        397,3,2740394,Pretérito_pluscuamperfecto_de_indicativo
         */
        $constructions = [
            '372' => 'Futuro_do_Indicativo',
            '390' => 'Futuro_do_pretérito_do_indicativo',
            '371' => 'Presente_do_Indicativo',
            '376' => 'Pretérito_Imperfeito_do_Indicativo',
            '408' => 'Pretérito_mais_que_perfeito_composto_do_indicativo',
            '377' => 'Pretérito_Mais_Que_Perfeito_do_Indicativo',
            '418' => 'Pretérito_perfeito_composto_do_indicativo',
            '373' => 'Pretérito_Perfeito_do_Indicativo',
            '401' => 'Condicional_del_indicativo',
            '381' => 'Futuro_de_indicativo',
            '380' => 'Presente_de_indicativo',
            '384' => 'Pretérito_imperfecto_de_indicativo',
            '396' => 'Pretérito_perfecto_compuesto_de_indicativo',
            '382' => 'Pretérito_perfecto_simple_de_indicativo',
            '397' => 'Pretérito_pluscuamperfecto_de_indicativo',
        ];

        CosineService::createLinkCxnCeToConcept(372);
        CosineService::createLinkCxnCeToConcept(390);
        CosineService::createLinkCxnCeToConcept(371);
        CosineService::createLinkCxnCeToConcept(376);
        CosineService::createLinkCxnCeToConcept(408);
        CosineService::createLinkCxnCeToConcept(377);
        CosineService::createLinkCxnCeToConcept(418);
        CosineService::createLinkCxnCeToConcept(373);
        CosineService::createLinkCxnCeToConcept(401);
        CosineService::createLinkCxnCeToConcept(381);
        CosineService::createLinkCxnCeToConcept(380);
        CosineService::createLinkCxnCeToConcept(384);
        CosineService::createLinkCxnCeToConcept(396);
        CosineService::createLinkCxnCeToConcept(382);
        CosineService::createLinkCxnCeToConcept(397);

        $array1 = [372, 390, 371, 376, 408, 377, 418, 373];
        $array2 = [401, 381, 380, 384, 396, 382, 397];
        $handle = fopen(__DIR__ . "/natalia_cxn_cosine.csv", "w");
        foreach ($array1 as $idConstruction1) {
            foreach ($array2 as $idConstruction2) {
                $result = CosineService::compareConstructions($idConstruction1, $idConstruction2);
                fputcsv($handle, [$constructions[$idConstruction1], $constructions[$idConstruction2], $result->cosine]);;
            }
        }
        fclose($handle);

        /* Audion - PPM - 23/06/2025 */
        //CosineService::createFrameNetwork();
//        CosineService::createLinkSentenceAnnotationTimeToFrame(614);
//        CosineService::createLinkSentenceAnnotationTimeToFrame(638);
//        CosineService::createLinkSentenceAnnotationTimeToFrame(617);
//        CosineService::createLinkSentenceAnnotationTimeToFrame(619);
//        CosineService::createLinkSentenceAnnotationTimeToFrame(631);
//        CosineService::createLinkSentenceAnnotationTimeToFrame(626);
//
//        $array = [614,638,617,619,631,626,502,507,508,509,510,511,512,513,515,516];
        //$array = [619,631,626,502,507,508,509,510,511,512,513,515,516];
        //$array = [502,507,508,509,510,511,512,513,515,516];
        //$array = [614];
//        foreach($array as $idDocument){
//            CosineService::createLinkSentenceAnnotationTimeToFrame($idDocument);
//            CosineService::createLinkObjectAnnotationTimeToFrame($idDocument);
//            $document = Criteria::byId("document","idDocument",$idDocument);
//            CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_original_full_2.csv", CosineService::compareTimespan($idDocument, 4, ''));
//            CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_original_lu_2.csv", CosineService::compareTimespan($idDocument, 4, 'lu'));
//            CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_original_fe_2.csv", CosineService::compareTimespan($idDocument, 4, 'fe'));
//            if ($idDocument > 520) {
//                CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_description_full_2.csv", CosineService::compareTimespan($idDocument, 7, ''));
//                CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_description_lu_2.csv", CosineService::compareTimespan($idDocument, 7, 'lu'));
//                CosineService::writeToCSV(__DIR__ . "/{$document->entry}_audio_description_fe_2.csv", CosineService::compareTimespan($idDocument, 7, 'fe'));
//            }
//        }

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
