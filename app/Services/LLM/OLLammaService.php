<?php

namespace App\Services\LLM;

use OpenAI\Laravel\Facades\OpenAI;

class OLLammaService
{
    public static function test() {
        $prompt = "What are the main properties and characteristics of birds?";
        $response = OpenAI::chat()->create([
            'model' => 'llama3.1:8b',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 2000,
            'temperature' => 0.7,
        ]);

        $content = $response->choices[0]->message->content;
        debug($content);
    }

}
