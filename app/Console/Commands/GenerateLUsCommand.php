<?php

namespace App\Console\Commands;

use App\Repositories\Frame;
use App\Services\AppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use App\Database\Criteria;
use Exception;
use Carbon\Carbon;

class GenerateLUsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:generate-lus
                            {frame-id : The ID of the frame to generate LUs for}
                            {--target-n=5 : Number of LU suggestions to generate}
                            {--target-pos=VERB,NOUN,ADJ : POS types to generate (comma-separated: VERB,NOUN,ADJ)}
                            {--model=gpt-4o : The OpenAI model to use}
                            {--dry-run : Show processed prompt without calling OpenAI API}
                            {--save-to-db : Save suggestions as LU candidates in database}
                            {--export-json : Export results to JSON file (default: true)}
                            {--debug : Save raw OpenAI response for debugging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new Brazilian Portuguese lexical units (LUs) for a FrameNet frame using OpenAI (VERB, NOUN, ADJ only)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        AppService::setCurrentLanguage(1);

        $frameId = (int) $this->argument('frame-id');
        $targetN = (int) $this->option('target-n');
        $targetPos = $this->option('target-pos');
        $model = $this->option('model');
        $dryRun = $this->option('dry-run');
        
        // Validate POS types
        $validPos = $this->validatePosTypes($targetPos);

        $this->info("🚀 Generating LUs for Frame ID: {$frameId}");
        $this->info("📊 Target suggestions: {$targetN}");
        $this->info("🏷️  Target POS: {$targetPos}");
        $this->info("🤖 Model: {$model}");

        if ($dryRun) {
            $this->warn("🧪 DRY RUN MODE - No API calls will be made");
        }

        $this->newLine();

        try {
            // Validate frame exists
            $frame = $this->validateFrame($frameId);

            // Load prompt template
            $promptTemplate = $this->loadPromptTemplate();

            // Get current LUs for this frame
            $currentLUs = $this->getCurrentLUs($frameId, $validPos);

            // Build complete prompt
            $fullPrompt = $this->buildPrompt($promptTemplate, $frame, $currentLUs, $targetN, $targetPos);

            if ($dryRun) {
                $this->displayDryRun($fullPrompt);
                return 0;
            }

            // Check API key
            if (empty(config('openai.api_key'))) {
                $this->error('❌ OpenAI API key not configured. Please set OPENAI_API_KEY in your .env file.');
                return 1;
            }

            // Call OpenAI API
            $response = $this->callOpenAI($fullPrompt, $model);

            // Process and display results
            $results = $this->processResponse($response, $frame);

            // Export results
            $this->exportResults($results, $frameId, $frame->name, $targetPos);

            $this->newLine();
            $this->info('✅ LU generation completed successfully!');

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }

    private function loadPromptTemplate(): string
    {
        $promptPath = 'prompts/lu_prompt.txt';

        if (!Storage::disk('local')->exists($promptPath)) {
            throw new Exception("Prompt template not found at storage/app/{$promptPath}");
        }

        $template = Storage::disk('local')->get($promptPath);

        if (empty($template)) {
            throw new Exception("Prompt template is empty");
        }

        $this->info("📄 Loaded prompt template from storage");
        return $template;
    }

    private function validateFrame(int $frameId): object
    {
        try {
            $frame = Frame::byId($frameId);

            if (empty($frame)) {
                throw new Exception("Frame with ID {$frameId} not found");
            }

            $this->info("🎯 Frame: {$frame->name}");
            return $frame;

        } catch (Exception $e) {
            throw new Exception("Invalid frame ID {$frameId}: " . $e->getMessage());
        }
    }

    private function validatePosTypes(string $targetPos): array
    {
        $validPosTypes = ['VERB', 'NOUN', 'ADJ'];
        $requestedPos = array_map('trim', explode(',', strtoupper($targetPos)));
        
        foreach ($requestedPos as $pos) {
            if (!in_array($pos, $validPosTypes)) {
                throw new Exception("Invalid POS type '{$pos}'. Valid types are: VERB, NOUN, ADJ");
            }
        }
        
        return $requestedPos;
    }

    private function getCurrentLUs(int $frameId, array $validPos): array
    {
        $lus = Criteria::table("view_lu as lu")
            ->join("udpos", "lu.idUDPOS", "=", "udpos.idUDPOS")
            ->select(['lu.name as lemma', 'udpos.POS', 'lu.senseDescription'])
            ->where('lu.idFrame', $frameId)
            ->where('lu.idLanguage', 1) // Portuguese
            ->whereIn('udpos.POS', $validPos) // Filter by valid POS types
            ->orderBy('lu.name')
            ->get()
            ->all();

        $this->info("📋 Found " . count($lus) . " existing LUs for this frame");

        return $lus;
    }

    private function buildPrompt(string $template, object $frame, array $currentLUs, int $targetN, string $targetPos): string
    {
        // Format current LUs
        $lusText = '';
        foreach ($currentLUs as $lu) {
            $lusText .= "- {$lu->lemma}.{$lu->POS}";
            if (!empty($lu->senseDescription)) {
                $lusText .= " - {$lu->senseDescription}";
            }
            $lusText .= "\n";
        }

        if (empty($lusText)) {
            $lusText = "[No existing LUs for this frame]";
        }

        // Replace template variables
        $prompt = str_replace('<TARGET_FRAME>', $frame->name, $template);
        $prompt = str_replace('<TARGET_N>', (string)$targetN, $prompt);
        $prompt = str_replace('<TARGET_POS>', $targetPos, $prompt);
        $prompt = str_replace('<CURRENT_LUS>', trim($lusText), $prompt);

        return $prompt;
    }

    private function displayDryRun(string $prompt): void
    {
        $this->newLine();
        $this->info('📝 Generated Prompt:');
        $this->line('═══════════════════════════════════════════════════════════════');
        $this->line($prompt);
        $this->line('═══════════════════════════════════════════════════════════════');
    }

    private function callOpenAI(string $prompt, string $model): string
    {
        $this->info("🤖 Calling OpenAI API with model: {$model}");

        $response = OpenAI::chat()->create([
            'model' => $model,
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
        $this->info("💫 Received response ({$response->usage->totalTokens} tokens)");

        // Save raw response for debugging if requested
        if ($this->option('debug')) {
            $debugFile = 'debug/openai-response-' . Carbon::now()->format('Y-m-d_H-i-s') . '.txt';
            Storage::disk('local')->put($debugFile, $content);
            $this->warn("🐛 Raw response saved to: storage/app/{$debugFile}");
        }

        return $content;
    }

    private function processResponse(string $response, object $frame): array
    {
        // Try to extract JSON from response
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');

        if ($jsonStart === false || $jsonEnd === false) {
            $this->error("Raw OpenAI response:");
            $this->line($response);
            throw new Exception("No JSON found in OpenAI response");
        }

        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
        $results = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("JSON parsing failed. Raw JSON extracted:");
            $this->line($jsonString);
            $this->newLine();
            $this->error("Full OpenAI response:");
            $this->line($response);
            throw new Exception("Invalid JSON in OpenAI response: " . json_last_error_msg());
        }

        if (!isset($results['items']) || !is_array($results['items'])) {
            throw new Exception("Invalid response format: 'items' array not found");
        }

        $this->info("✅ Generated " . count($results['items']) . " LU suggestions");

        // Display results table
        $this->displayResults($results);

        return $results;
    }

    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info("📋 Generated LU Suggestions:");

        $tableData = [];
        foreach ($results['items'] as $item) {
            $confidence = $item['confidence'] ?? 0;
            $confidenceColor = $confidence > 0.8 ? 'green' : ($confidence > 0.6 ? 'yellow' : 'red');

            $tableData[] = [
                $item['lemma'] ?? '',
                $item['pos'] ?? '',
                substr($item['gloss_pt'] ?? '', 0, 50) . (strlen($item['gloss_pt'] ?? '') > 50 ? '...' : ''),
                number_format($confidence, 2),
                substr($item['rationale_short'] ?? '', 0, 60) . (strlen($item['rationale_short'] ?? '') > 60 ? '...' : ''),
            ];
        }

        $this->table(
            ['Lemma', 'POS', 'Gloss (PT)', 'Confidence', 'Rationale'],
            $tableData
        );

        if (!empty($results['excluded_notes'])) {
            $this->newLine();
            $this->warn("ℹ️  Notes: " . $results['excluded_notes']);
        }
    }

    private function exportResults(array $results, int $frameId, string $frameName, string $targetPos): void
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "lu-suggestions/frame-{$frameId}_{$frameName}_{$timestamp}.json";

        // Ensure directory exists
        Storage::disk('local')->makeDirectory(dirname($filename));

        // Prepare export data
        $exportData = [
            'meta' => [
                'frame_id' => $frameId,
                'frame_name' => $frameName,
                'target_pos' => $targetPos,
                'generated_at' => Carbon::now()->toISOString(),
                'model' => $this->option('model'),
                'target_n' => $this->option('target-n'),
            ],
            'results' => $results
        ];

        Storage::disk('local')->put($filename, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("💾 Results exported to: storage/app/{$filename}");
    }
}
