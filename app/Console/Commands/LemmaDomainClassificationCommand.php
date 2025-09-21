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
                           {--batch-number= : Process specific batch number (optional)}
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
        $specificBatchNumber = $this->option('batch-number') ? (int) $this->option('batch-number') : null;
        $testMode = $this->option('test');

        $this->info('🔍 Starting Lemma Domain Classification...');
        $this->info("Language ID: {$languageId}");
        $this->info('POS Types: '.implode(', ', $posTypes));
        $this->info("Batch Size: {$batchSize}");
        $this->info("Model: {$model}");
        if ($specificBatchNumber) {
            $this->info("🎯 Processing specific batch: {$specificBatchNumber}");
        } else {
            $this->info('📦 Processing all batches sequentially');
        }
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

            // Calculate total number of batches
            $totalBatches = (int) ceil($totalCount / $batchSize);
            $this->info("Total batches: {$totalBatches}");

            // Determine which batches to process
            $batchesToProcess = [];
            if ($specificBatchNumber !== null) {
                if ($specificBatchNumber < 1 || $specificBatchNumber > $totalBatches) {
                    $this->error("Invalid batch number. Must be between 1 and {$totalBatches}");
                    return 1;
                }
                $batchesToProcess = [$specificBatchNumber];
                $this->info("Processing batch {$specificBatchNumber} of {$totalBatches}");
            } else {
                $batchesToProcess = range(1, $totalBatches);
                $this->info("Processing all {$totalBatches} batches");
            }

            $this->newLine();

            // Initialize statistics
            $conversationResets = 0;
            $skippedBatches = 0;
            $totalProcessedCount = 0;

            // Process each batch
            foreach ($batchesToProcess as $currentBatchNumber) {
                $this->info("🔄 Processing batch {$currentBatchNumber}/{$totalBatches}...");
                
                // Calculate offset for this batch
                $batchOffset = ($currentBatchNumber - 1) * $batchSize;
                $currentBatchSize = min($batchSize, $totalCount - $batchOffset);
                
                if ($currentBatchSize <= 0) {
                    break;
                }

                // Initialize conversation history for this batch
                $conversationHistory = [];
                $batchProcessedCount = 0;
                $conversationalBatchNumber = 0;

                // Process lemmas in conversational batches of 10 within this batch
                while ($batchProcessedCount < $currentBatchSize) {
                    $remainingInBatch = $currentBatchSize - $batchProcessedCount;
                    $conversationalBatchSize = min(10, $remainingInBatch); // Fixed at 10 for conversational approach
                    $conversationalOffset = $batchOffset + $batchProcessedCount;

                    $lemmas = $this->getLemmasBatch($languageId, $posTypes, $conversationalBatchSize, $conversationalOffset);

                    if (empty($lemmas)) {
                        break;
                    }

                    $conversationalBatchNumber++;
                    $isFirstConversationalBatch = ($conversationalBatchNumber === 1);
                    $previousHistoryLength = count($conversationHistory);

                    // Process this conversational batch
                    $classifications = $this->processConversationalBatch(
                        $lemmas,
                        $baseUrl,
                        $model,
                        $domainTaxonomy,
                        $conversationHistory,
                        $isFirstConversationalBatch
                    );

                    // Check if conversation was reset during processing
                    $currentHistoryLength = count($conversationHistory);
                    if ($previousHistoryLength > 0 && $currentHistoryLength < $previousHistoryLength) {
                        $conversationResets++;
                        $this->line("📊 Conversation reset #$conversationResets for better context");
                    }

                    // Save classifications to batch-specific file (only if we got results)
                    if (! empty($classifications)) {
                        $this->saveClassifications($classifications, $currentBatchNumber);
                    } else {
                        $skippedBatches++;
                    }

                    $batchCount = count($lemmas);
                    $batchProcessedCount += $batchCount;
                    $totalProcessedCount += $batchCount;
                }

                $this->info("✅ Batch {$currentBatchNumber} completed: {$batchProcessedCount} lemmas processed");
                $this->newLine();
            }

            $this->info("✅ Processed {$totalProcessedCount} lemmas successfully!");
            if ($specificBatchNumber !== null) {
                $this->info("📁 Classifications saved to: storage/app/lemma_batch_" . str_pad($specificBatchNumber, 3, '0', STR_PAD_LEFT) . ".json");
            } else {
                $this->info("📁 Classifications saved to batch files: storage/app/lemma_batch_*.json");
            }

            // Report conversation statistics
            if ($conversationResets > 0 || $skippedBatches > 0) {
                $this->newLine();
                $this->info('📊 Processing Statistics:');
                if ($conversationResets > 0) {
                    $this->info("🔄 Conversation resets: {$conversationResets}");
                }
                if ($skippedBatches > 0) {
                    $this->warn("⚠️  Skipped conversational batches: {$skippedBatches}");
                }
            }

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
            ->orderBy('name')
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
     * Process a batch of lemmas with conversational LLM classification
     */
    private function processConversationalBatch(
        array $lemmas,
        string $baseUrl,
        string $model,
        string $domainTaxonomy,
        array &$conversationHistory,
        bool $isFirstBatch,
        int $retryCount = 0
    ): array {
        $lemmaNames = array_map(fn ($lemma) => $lemma->name, $lemmas);

        if ($isFirstBatch) {
            // Initialize conversation with system message and full taxonomy
            $conversationHistory = [
                [
                    'role' => 'system',
                    'content' => 'You are an expert linguist specialized in semantic domain classification. You must classify words according to the provided taxonomy and respond only with valid JSON. Remember the taxonomy for future classification requests in this conversation.',
                ],
                [
                    'role' => 'user',
                    'content' => $this->createInitialPrompt($lemmaNames, $domainTaxonomy),
                ],
            ];
        } else {
            // Add follow-up request to existing conversation
            $conversationHistory[] = [
                'role' => 'user',
                'content' => $this->createFollowUpPrompt($lemmaNames),
            ];
        }

        $payload = [
            'model' => $model,
            'messages' => $conversationHistory,
            'max_tokens' => 6000,
            'temperature' => 0.3,
        ];

        $response = $this->makeCurlRequest("{$baseUrl}/v1/chat/completions", $payload);

        if ($response && isset($response['choices'][0]['message']['content'])) {
            $responseContent = $response['choices'][0]['message']['content'];

            try {
                $classifications = $this->parseClassificationResponse($responseContent, $lemmaNames);

                // Add LLM response to conversation history only if parsing succeeded
                $conversationHistory[] = [
                    'role' => 'assistant',
                    'content' => $responseContent,
                ];

                // Log successful processing
                if ($retryCount > 0) {
                    $this->info('✅ Batch succeeded after conversation reset');
                }

                return $classifications;
            } catch (Exception $e) {
                // Handle JSON parsing errors with conversation reset
                if ($retryCount < 2) { // Maximum 2 retries
                    $this->info('🔄 Context window full, restarting conversation (attempt '.($retryCount + 1).'/2)');
                    $this->warn("JSON error: {$e->getMessage()}");

                    // Reset conversation history to start fresh
                    $conversationHistory = [];

                    // Retry with fresh context (recursive call)
                    return $this->processConversationalBatch(
                        $lemmas,
                        $baseUrl,
                        $model,
                        $domainTaxonomy,
                        $conversationHistory,
                        true, // Force fresh start
                        $retryCount + 1
                    );
                } else {
                    // Maximum retries reached, log error and continue
                    $this->error('❌ Failed after 2 retry attempts, skipping batch');
                    $this->warn("Final error: {$e->getMessage()}");
                    $this->warn('Response preview: '.substr($responseContent, 0, 200).'...');

                    return [];
                }
            }
        }

        throw new Exception('Failed to get valid response from LLM');
    }

    /**
     * Create initial prompt with balanced taxonomy and first batch of lemmas
     */
    private function createInitialPrompt(array $lemmaNames, string $domainTaxonomy): string
    {
        $lemmaList = implode(', ', $lemmaNames);

        // Create a balanced taxonomy with key descriptions
        $balancedTaxonomy = $this->createBalancedTaxonomy();

        return "
I need you to classify words into semantic domains using the taxonomy below. Please remember this taxonomy for our entire conversation.

SEMANTIC DOMAIN TAXONOMY:
{$balancedTaxonomy}

CLASSIFICATION INSTRUCTIONS:
1. Each word can have POLYSEMY (multiple meanings)
2. Provide 1-3 different domain/subdomain classifications per word
3. Nature types: 'Perceptual' (perceived through senses/emotions) or 'Conceptual' (abstract categorization)
4. Use exact domain and subdomain names from the taxonomy above

FIRST BATCH OF WORDS TO CLASSIFY: {$lemmaList}

Respond with ONLY valid JSON in this exact format:

{
  \"classifications\": [
    {
      \"lemma\": \"example\",
      \"classifications\": [
        {
          \"domain\": \"Physical_domain\",
          \"subdomain\": \"Matter and Substances\",
          \"nature\": \"Perceptual\"
        }
      ]
    }
  ]
}

Please classify these ".count($lemmaNames).' words and remember the taxonomy for future requests in this conversation.';
    }

    /**
     * Create balanced taxonomy with key descriptions
     */
    private function createBalancedTaxonomy(): string
    {
        return '
# Physical_domain - Material world, objects, matter, forces, energy, spatial configurations
- Matter and Substances: Material composition, building blocks, observable characteristics
- Motion and Location: Movement, positioning, spatial relationships
- Physical Transformation: Changes in form, state, properties of matter
- Natural Phenomena: Weather, geological events, astronomical phenomena
- Manipulation and Interaction with Objects: Physical engagement, tools, arrangements

# Biological_domain - Living organisms, biological systems, anatomy, physiology
- Biological Entities and Life Processes: Living beings, life cycles, essential activities
- Health and Illness: Wellness to disease spectrum, health conditions
- Body and Anatomy: Bodily structure, body parts, spatial relationships
- Physiological Functions: Biological processes, vital functions, bodily activities
- Ecological Systems: Interconnected relationships, habitats, environmental interdependence

# Social_domain - Interpersonal relationships, institutions, social roles, governance
- Social Roles and Identities: Positions, functions, social personas, status
- Institutions and Organizations: Formal/informal structures, collective activities
- Economic and Commercial Practices: Exchange, trade, production, consumption
- Governance and Law: Authority, rule-making, social control, legal obligations
- Social Interaction and Relationships: Communication, connections, social engagement
- Conflict and Cooperation: Competitive/collaborative dynamics, social tensions
- Group Dynamics and Collective Behavior: Group formation, collective action

# Cultural_domain - Symbolic systems, traditions, arts, heritage, belief systems
- Beliefs and Worldviews: Perspectives on reality, philosophical orientations
- Rituals and Traditions: Ceremonial practices, customary behaviors
- Arts and Creative Practices: Creative expression, aesthetic appreciation, artistic production
- Cultural Narratives and Memory: Stories, histories, collective memories
- Heritage and Identity: Cultural inheritance, group membership, cultural markers

# Psychological_domain - Perceptual, emotional, affective experiences
- Perception and Sensation: Sensory information processing, perceptual organization
- Emotions and Affective States: Feeling states, emotional responses, moods
- Needs and Drives and Motivations: Internal forces, biological/psychological needs
- Subjective Experience of the Body and Environment: First-person embodied experience

# Cognitive_domain - Higher-order mental processes, reasoning, memory, decision-making
- Attention and Awareness: Consciousness, selective attention, awareness states
- Memory and Learning: Information acquisition, storage, retrieval, skill development
- Reasoning and Problem-Solving: Logical thinking, analytical processes, inference
- Decision-Making and Planning: Choice processes, future-oriented thinking
- Beliefs and Knowledge and Assumptions: Information base, worldview, knowledge forms
- Intention and Goal-Oriented Action: Purposive behavior, goal formation, agency
- Cognitive Judgments and Evaluations: Assessment processes, evaluative thinking

# Representational_domain - Abstract representations, information, symbolic systems
- Signs and Communication and Language: Symbolic systems, communicative processes
- Information Structures: Organization, categorization of information
- Media and Digital Systems: Technological systems, electronic media
- Formal Systems and Abstract Representations: Rule-based systems, mathematical frameworks
- Knowledge Representation: Encoding, storing, expressing knowledge

# Space-time_domain - Spatial and temporal structures, location, duration
- Spatial Configuration: Arrangement, geometric relationships, layouts
- Temporal Structure: Time organization, temporal relationships, sequences
- Motion and Trajectory: Movement through space-time, paths, dynamics
- Change over Time: Transformation, development, evolution processes

# Moral_domain - Values, norms, moral judgments, ethical principles
- Conventions and Ethical Norms and Principles: Moral rules, ethical frameworks
- Moral Judgments and Evaluations: Moral assessment, ethical decision-making
- Rights and Duties and Justice: Entitlements, obligations, fair treatment
- Moral Emotions and Responses: Guilt, shame, pride, empathy in moral contexts';
    }

    /**
     * Create follow-up prompt for subsequent batches
     */
    private function createFollowUpPrompt(array $lemmaNames): string
    {
        $lemmaList = implode(', ', $lemmaNames);

        return '
Continue classifying these '.count($lemmaNames)." words using the same domain taxonomy and polysemy approach as before: {$lemmaList}

Respond with the same JSON format as previous classifications.";
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
     * Save classifications to batch-specific JSON file
     */
    private function saveClassifications(array $classifications, int $batchNumber): void
    {
        $filename = 'lemma_batch_' . str_pad($batchNumber, 3, '0', STR_PAD_LEFT) . '.json';
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
                    'batch_number' => $batchNumber,
                    'batch_size' => 100,
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
