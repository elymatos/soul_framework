<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use Exception;
use Illuminate\Console\Command;

class LemmaDomainClassificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lemma:domain-classification 
                           {--batch-size=100 : Number of lemmas to process per batch}
                           {--language=1 : Language ID to process}
                           {--pos=NOUN,VERB : POS tags to include (comma-separated)}
                           {--model=llama3.2 : The Llama model to use}
                           {--output-file=lemma_classifications.json : Output JSON file name}
                           {--test : Process only first 100 lemmas for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Classify lemmas by domain using AI model analysis';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $batchSize = (int) $this->option('batch-size');
        $languageId = (int) $this->option('language');
        $posTypes = explode(',', $this->option('pos'));
        $model = $this->option('model');
        $outputFile = $this->option('output-file');
        $testMode = $this->option('test');

        $this->info('🔍 Starting Lemma Domain Classification...');
        $this->info("Language ID: {$languageId}");
        $this->info('POS Types: '.implode(', ', $posTypes));
        $this->info("Batch Size: {$batchSize}");
        $this->info("Model: {$model}");
        $this->info("Output File: storage/app/{$outputFile}");
        if ($testMode) {
            $this->info('🧪 TEST MODE: Processing only first 100 lemmas');
        }
        $this->newLine();

        try {
            // Check Ollama configuration
            $baseUrl = config('openai.base_uri');
            if (empty($baseUrl)) {
                $this->error('❌ Ollama base URL is not configured. Please set OPENAI_BASE_URL in your .env file.');

                return 1;
            }

            $baseUrl = rtrim($baseUrl, '/v1');
            $this->info("Using Ollama server: {$baseUrl}");
            $this->newLine();

            // Load domain taxonomy
            $domainTaxonomy = $this->loadDomainTaxonomy();

            // Get total count first
            $totalCount = $this->getTotalLemmaCount($languageId, $posTypes);

            // Apply test mode limit
            if ($testMode && $totalCount > 100) {
                $totalCount = 100;
                $this->info('Test mode: limiting to 100 lemmas');
            }

            $this->info("Total lemmas to process: {$totalCount}");

            if ($totalCount === 0) {
                $this->warn('No lemmas found matching the criteria.');

                return 0;
            }

            $processedCount = 0;
            $offset = 0;
            $progressBar = $this->output->createProgressBar($totalCount);
            $progressBar->start();

            // Process lemmas in batches
            while ($processedCount < $totalCount) {
                $remainingCount = $totalCount - $processedCount;
                $currentBatchSize = min($batchSize, $remainingCount);

                $lemmas = $this->getLemmasBatch($languageId, $posTypes, $currentBatchSize, $offset);

                if (empty($lemmas)) {
                    break;
                }

                // Process this batch with LLM
                $classifications = $this->processBatch($lemmas, $baseUrl, $model, $domainTaxonomy);

                // Save classifications to file
                $this->saveClassifications($classifications, $outputFile);

                $batchCount = count($lemmas);
                $processedCount += $batchCount;
                $offset += $batchSize;

                $progressBar->advance($batchCount);

                // Break if test mode and we've processed 100
                if ($testMode && $processedCount >= 100) {
                    break;
                }
            }

            $progressBar->finish();
            $this->newLine();
            $this->info("✅ Processed {$processedCount} lemmas successfully!");
            $this->info("📁 Classifications saved to: storage/app/{$outputFile}");

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Get total count of lemmas matching criteria
     */
    private function getTotalLemmaCount(int $languageId, array $posTypes): int
    {
        return Criteria::table('view_lexicon_lemma')
            ->whereIn('udPOS', $posTypes)
            ->where('idLanguage', $languageId)
            ->count();
    }

    /**
     * Get a batch of lemmas from the database
     */
    private function getLemmasBatch(int $languageId, array $posTypes, int $limit, int $offset): array
    {
        return Criteria::table('view_lexicon_lemma')
            ->select('name')
            ->whereIn('udPOS', $posTypes)
            ->where('idLanguage', $languageId)
            ->limit($limit)
            ->offset($offset)
            ->all();
    }

    /**
     * Load domain taxonomy from file
     */
    private function loadDomainTaxonomy(): string
    {
        $taxonomyPath = base_path('docs/fn3/domains_subdomains.txt');

        if (! file_exists($taxonomyPath)) {
            throw new Exception("Domain taxonomy file not found: {$taxonomyPath}");
        }

        return file_get_contents($taxonomyPath);
    }

    /**
     * Process a batch of lemmas with LLM classification
     */
    private function processBatch(array $lemmas, string $baseUrl, string $model, string $domainTaxonomy): array
    {
        $lemmaNames = array_map(fn ($lemma) => $lemma->name, $lemmas);

        $prompt = $this->createClassificationPrompt($lemmaNames, $domainTaxonomy);

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert linguist specialized in semantic domain classification. You must classify words according to the provided taxonomy and respond only with valid JSON.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 4000,
            'temperature' => 0.3,
        ];

        $response = $this->makeCurlRequest("{$baseUrl}/v1/chat/completions", $payload);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            return $this->parseClassificationResponse($response['choices'][0]['message']['content'], $lemmaNames);
        }

        throw new Exception('Failed to get valid response from LLM');
    }

    /**
     * Create classification prompt for batch of lemmas
     */
    private function createClassificationPrompt(array $lemmaNames, string $domainTaxonomy): string
    {
        $lemmaList = implode(', ', $lemmaNames);

        // Create a condensed version of the taxonomy for the prompt
        $condensedTaxonomy = $this->createCondensedTaxonomy();

        return "
Classify each word into semantic domains, considering POLYSEMY (multiple meanings).

DOMAINS & SUBDOMAINS:
{$condensedTaxonomy}

NATURE TYPES:
- Perceptual: Can be perceived through the five senses or as an emotional state
- Conceptual: Abstract categorization or concepts that are not perceptual

WORDS TO CLASSIFY: {$lemmaList}

POLYSEMY INSTRUCTIONS:
Due to polysemy, each word can have multiple meanings. Provide 1-3 different domain/subdomain classifications per word based on its different possible meanings or uses.

Respond with ONLY valid JSON in this exact format:

{
  \"classifications\": [
    {
      \"lemma\": \"banco\",
      \"classifications\": [
        {
          \"domain\": \"Physical_domain\",
          \"subdomain\": \"Motion and Location\",
          \"nature\": \"Perceptual\"
        },
        {
          \"domain\": \"Social_domain\",
          \"subdomain\": \"Economic and Commercial Practices\",
          \"nature\": \"Conceptual\"
        }
      ]
    }
  ]
}

Classify all ".count($lemmaNames).' words. Each word can have 1-3 classifications. Use exact domain/subdomain names above.';
    }

    /**
     * Create condensed taxonomy for prompt
     */
    private function createCondensedTaxonomy(): string
    {
        return '
Physical_domain: Matter and Substances, Motion and Location, Physical Transformation, Natural Phenomena, Manipulation and Interaction with Objects

Biological_domain: Biological Entities and Life Processes, Health and Illness, Body and Anatomy, Physiological Functions, Ecological Systems

Social_domain: Social Roles and Identities, Institutions and Organizations, Economic and Commercial Practices, Governance and Law, Social Interaction and Relationships, Conflict and Cooperation, Group Dynamics and Collective Behavior

Cultural_domain: Beliefs and Worldviews, Rituals and Traditions, Arts and Creative Practices, Cultural Narratives and Memory, Heritage and Identity

Psychological_domain: Perception and Sensation, Emotions and Affective States, Needs and Drives and Motivations, Subjective Experience of the Body and Environment

Cognitive_domain: Attention and Awareness, Memory and Learning, Reasoning and Problem-Solving, Decision-Making and Planning, Beliefs and Knowledge and Assumptions, Intention and Goal-Oriented Action, Cognitive Judgments and Evaluations

Representational_domain: Signs and Communication and Language, Information Structures, Media and Digital Systems, Formal Systems and Abstract Representations, Knowledge Representation

Space-time_domain: Spatial Configuration, Temporal Structure, Motion and Trajectory, Change over Time

Moral_domain: Conventions and Ethical Norms and Principles, Moral Judgments and Evaluations, Rights and Duties and Justice, Moral Emotions and Responses';
    }

    /**
     * Parse LLM classification response
     */
    private function parseClassificationResponse(string $response, array $expectedLemmas): array
    {
        // Clean the response to extract JSON
        $response = trim($response);

        // Remove markdown code blocks if present
        $response = preg_replace('/```json\s*/', '', $response);
        $response = preg_replace('/```\s*$/', '', $response);

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from LLM: '.json_last_error_msg());
        }

        if (! isset($decoded['classifications']) || ! is_array($decoded['classifications'])) {
            throw new Exception('Invalid response structure: missing classifications array');
        }

        // Flatten the polysemic structure for storage
        $flatClassifications = [];
        foreach ($decoded['classifications'] as $lemmaGroup) {
            if (! isset($lemmaGroup['lemma']) || ! isset($lemmaGroup['classifications'])) {
                continue; // Skip malformed entries
            }

            $lemma = $lemmaGroup['lemma'];
            $classifications = $lemmaGroup['classifications'];

            // Validate that we have 1-3 classifications
            if (! is_array($classifications) || count($classifications) === 0 || count($classifications) > 3) {
                continue; // Skip invalid classification counts
            }

            foreach ($classifications as $classification) {
                if (isset($classification['domain'], $classification['subdomain'], $classification['nature'])) {
                    $flatClassifications[] = [
                        'lemma' => $lemma,
                        'domain' => $classification['domain'],
                        'subdomain' => $classification['subdomain'],
                        'nature' => $classification['nature'],
                    ];
                }
            }
        }

        return $flatClassifications;
    }

    /**
     * Save classifications to JSON file
     */
    private function saveClassifications(array $classifications, string $filename): void
    {
        $filepath = storage_path("app/{$filename}");

        $existingData = [];
        if (file_exists($filepath)) {
            $content = file_get_contents($filepath);
            $existingData = json_decode($content, true) ?: [];
        }

        // Initialize structure if empty
        if (empty($existingData)) {
            $existingData = [
                'metadata' => [
                    'created' => now()->toISOString(),
                    'taxonomy_version' => 'fn3',
                    'total_processed' => 0,
                ],
                'classifications' => [],
            ];
        }

        // Update metadata
        $existingData['metadata']['last_updated'] = now()->toISOString();
        $existingData['metadata']['total_processed'] += count($classifications);

        // Add new classifications
        $existingData['classifications'] = array_merge(
            $existingData['classifications'] ?? [],
            $classifications
        );

        // Save back to file
        file_put_contents($filepath, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
