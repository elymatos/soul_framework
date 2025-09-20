<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

class LLamaTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llama:test {--model=llama3.2 : The Llama model to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Llama integration via Ollama server with various examples including chat completions and text generation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🦙 Testing Llama via Ollama Integration...');
        $this->newLine();

        // Check base URL configuration
        $baseUrl = config('openai.base_uri');
        if (empty($baseUrl)) {
            $this->error('❌ Ollama base URL is not configured. Please set OPENAI_BASE_URL in your .env file.');

            return 1;
        }

        // Remove trailing /v1 if present and add it back consistently
        $baseUrl = rtrim($baseUrl, '/v1');
        $this->info("Using Ollama server: {$baseUrl}");

        $model = $this->option('model');
        $this->info("Using model: {$model}");
        $this->newLine();

        try {
            // Test 1: Simple Chat Completion
            $this->testChatCompletion($baseUrl, $model);

            // Test 2: Conversation Flow
            $this->testConversationFlow($baseUrl, $model);

            // Test 3: Text Generation with System Message
            $this->testTextGenerationWithSystem($baseUrl, $model);

            // Test 4: List Available Models
            $this->testListModels($baseUrl);

        } catch (Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());

            return 1;
        }

        $this->newLine();
        $this->info('✅ All Llama tests completed successfully!');

        return 0;
    }

    private function testChatCompletion(string $baseUrl, string $model): void
    {
        $this->info('🔸 Test 1: Simple Chat Completion');

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello! Can you tell me what is Laravel in one sentence?'],
            ],
            'max_tokens' => 100,
        ];

        $response = $this->makeCurlRequest("{$baseUrl}/v1/chat/completions", $payload);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];
            $this->line("Response: {$content}");

            if (isset($response['usage']['total_tokens'])) {
                $this->line("Tokens used: {$response['usage']['total_tokens']}");
            }
        } else {
            $this->error('Failed to get response from Ollama server');
        }

        $this->newLine();
    }

    private function testConversationFlow(string $baseUrl, string $model): void
    {
        $this->info('🔸 Test 2: Multi-turn Conversation');

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => 'What is FrameNet?'],
                ['role' => 'assistant', 'content' => 'FrameNet is a lexical database of English that is both a lexicon and a thesaurus, organized around semantic frames.'],
                ['role' => 'user', 'content' => 'Can you give me a practical example?'],
            ],
            'max_tokens' => 150,
        ];

        $response = $this->makeCurlRequest("{$baseUrl}/v1/chat/completions", $payload);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];
            $this->line("Multi-turn response: {$content}");

            if (isset($response['usage']['total_tokens'])) {
                $this->line("Tokens used: {$response['usage']['total_tokens']}");
            }
        } else {
            $this->error('Failed to get response from Ollama server');
        }

        $this->newLine();
    }

    private function testTextGenerationWithSystem(string $baseUrl, string $model): void
    {
        $this->info('🔸 Test 3: Text Generation with System Message');

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant specialized in linguistic annotation and FrameNet. Provide concise, technical responses.',
                ],
                [
                    'role' => 'user',
                    'content' => 'Explain semantic roles in one sentence.',
                ],
            ],
            'max_tokens' => 80,
            'temperature' => 0.7,
        ];

        $response = $this->makeCurlRequest("{$baseUrl}/v1/chat/completions", $payload);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];
            $this->line("System-guided response: {$content}");

            if (isset($response['usage']['total_tokens'])) {
                $this->line("Tokens used: {$response['usage']['total_tokens']}");
            }
        } else {
            $this->error('Failed to get response from Ollama server');
        }

        $this->newLine();
    }

    private function testListModels(string $baseUrl): void
    {
        $this->info('🔸 Test 4: Available Models via Ollama');

        try {
            $response = $this->makeCurlRequest("{$baseUrl}/v1/models");

            if ($response && isset($response['data'])) {
                $llamaModels = collect($response['data'])
                    ->filter(fn ($model) => str_contains(strtolower($model['id']), 'llama'))
                    ->take(10)
                    ->pluck('id');

                $this->line('Available Llama models (first 10):');
                foreach ($llamaModels as $model) {
                    $this->line("  • {$model}");
                }

                if ($llamaModels->isEmpty()) {
                    $this->line('Available models (first 10):');
                    $allModels = collect($response['data'])
                        ->take(10)
                        ->pluck('id');

                    foreach ($allModels as $model) {
                        $this->line("  • {$model}");
                    }
                }
            } else {
                $this->warn('Could not retrieve model list from Ollama server');
            }
        } catch (Exception $e) {
            $this->warn('⚠️  Could not list models: '.$e->getMessage());
            $this->line('This is expected if Ollama server does not support the models endpoint.');
        }

        $this->newLine();
    }

    /**
     * Make a curl request to the Ollama server
     */
    private function makeCurlRequest(string $url, ?array $payload = null): ?array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new Exception("Curl error: {$error}");
        }

        if ($httpCode !== 200) {
            throw new Exception("HTTP error: {$httpCode} - Response: {$response}");
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from server');
        }

        return $decoded;
    }
}
