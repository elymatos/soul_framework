<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;
use Exception;

class OpenAITestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:test {--model=gpt-4o-mini : The OpenAI model to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test OpenAI integration with various examples including chat completions and text generation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Testing OpenAI Laravel Integration...');
        $this->newLine();

        // Check API key configuration
        if (empty(config('openai.api_key'))) {
            $this->error('❌ OpenAI API key is not configured. Please set OPENAI_API_KEY in your .env file.');
            return 1;
        }

        $model = $this->option('model');
        $this->info("Using model: {$model}");
        $this->newLine();

        try {
            // Test 1: Simple Chat Completion
            $this->testChatCompletion($model);
            
            // Test 2: Conversation Flow
            $this->testConversationFlow($model);
            
            // Test 3: Text Generation with System Message
            $this->testTextGenerationWithSystem($model);
            
            // Test 4: List Available Models
            $this->testListModels();

        } catch (Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('✅ All OpenAI tests completed successfully!');
        return 0;
    }

    private function testChatCompletion(string $model): void
    {
        $this->info('🔸 Test 1: Simple Chat Completion');
        
        $response = OpenAI::chat()->create([
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello! Can you tell me what is Laravel in one sentence?'],
            ],
            'max_tokens' => 100,
        ]);

        $content = $response->choices[0]->message->content;
        $this->line("Response: {$content}");
        $this->line("Tokens used: {$response->usage->totalTokens}");
        $this->newLine();
    }

    private function testConversationFlow(string $model): void
    {
        $this->info('🔸 Test 2: Multi-turn Conversation');
        
        $response = OpenAI::chat()->create([
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => 'What is FrameNet?'],
                ['role' => 'assistant', 'content' => 'FrameNet is a lexical database of English that is both a lexicon and a thesaurus, organized around semantic frames.'],
                ['role' => 'user', 'content' => 'Can you give me a practical example?'],
            ],
            'max_tokens' => 150,
        ]);

        $content = $response->choices[0]->message->content;
        $this->line("Multi-turn response: {$content}");
        $this->line("Tokens used: {$response->usage->totalTokens}");
        $this->newLine();
    }

    private function testTextGenerationWithSystem(string $model): void
    {
        $this->info('🔸 Test 3: Text Generation with System Message');
        
        $response = OpenAI::chat()->create([
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system', 
                    'content' => 'You are a helpful assistant specialized in linguistic annotation and FrameNet. Provide concise, technical responses.'
                ],
                [
                    'role' => 'user', 
                    'content' => 'Explain semantic roles in one sentence.'
                ],
            ],
            'max_tokens' => 80,
            'temperature' => 0.7,
        ]);

        $content = $response->choices[0]->message->content;
        $this->line("System-guided response: {$content}");
        $this->line("Tokens used: {$response->usage->totalTokens}");
        $this->newLine();
    }

    private function testListModels(): void
    {
        $this->info('🔸 Test 4: Available Models');
        
        $response = OpenAI::models()->list();
        
        $gptModels = collect($response->data)
            ->filter(fn($model) => str_contains($model->id, 'gpt'))
            ->take(5)
            ->map(fn($model) => $model->id);

        $this->line('Available GPT models (first 5):');
        foreach ($gptModels as $model) {
            $this->line("  • {$model}");
        }
        $this->newLine();
    }
}
