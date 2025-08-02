<?php

namespace App\Services\LOME;

use App\Services\AppService;
use GuzzleHttp\Client;

class LOMEService extends AppService
{
    public function process($sentence, $idLanguage = 1)
    {

        $client = new Client([
            'base_uri' => 'http://server4.framenetbr.ufjf.br:8410',
            //'base_uri' => 'http://200.131.61.134:80',
            'timeout' => 300.0,
        ]);

        try {
            $response = $client->post('http://server4.framenetbr.ufjf.br:8410/parser', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'articles' => [
                        ['text' => $sentence]
                    ],
                    "model" => 'portuguese'
                ]
            ]);
//            echo $response->getBody();

            $body = json_decode($response->getBody());
//            //debug($body);
            return $body;
        } catch (\Exception $e) {

            echo $e->getMessage() . "\n";
            return '';
        }
    }

    public function parse($sentence, $idLanguage = 1)
    {

        $client = new Client([
            'base_uri' => 'http://localhost:7749',
            //'base_uri' => 'http://200.131.61.134:80',
            'timeout' => 300.0,
        ]);

        try {
            $response = $client->post('http://server5.frame.net.br:7749/parse', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'sentences' => [
                        $sentence
                    ],
                ]
            ]);
//            echo $response->getBody();

            $body = json_decode($response->getBody());
//            //debug($body);
            return $body;
        } catch (\Exception $e) {

            echo $e->getMessage() . "\n";
            return '';
        }
    }

//    public function process2($sentence, $idLanguage = 1)
//    {
//
//        $client = new Client([
//            'base_uri' => 'http://server4.framenetbr.ufjf.br:8410',
//            'timeout' => 300.0,
//        ]);
//
//        try {
//            $response = $client->request('post', 'parser2', [
//                'headers' => [
//                    'Accept' => 'application/text',
//                ],
//                'body' => json_encode([
//                    'articles' => [
//                        ['text' => $sentence]
//                    ],
//                    "model" => ''
//                ])
//            ]);
//
//            $body = json_decode($response->getBody());
//            return $body;
//        } catch (\Exception $e) {
//
//            echo $e->getMessage() . "\n";
//            return '';
//        }
//    }

}
