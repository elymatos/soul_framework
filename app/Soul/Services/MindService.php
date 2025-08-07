<?php

namespace App\Soul\Services;

use App\Soul\Contracts\GraphServiceInterface;
use App\Soul\Contracts\AgentServiceInterface;
use App\Soul\Contracts\Neo4jService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Soul\Exceptions\ProcessingSessionException;
use App\Soul\Exceptions\AgentCommunicationException;
use App\Soul\Exceptions\CognitiveProcessingException;
use App\Soul\Exceptions\ProcessingTimeoutException;
use App\Soul\Exceptions\ActivationConvergenceException;

/**
 * MindService - Complete replacement implementing Society of Mind principles
 *
 * This service coordinates the entire cognitive architecture using:
 * - Dual representation (graph nodes + executable agents)
 * - Spreading activation for conceptual processing
 * - K-line learning for successful path strengthening
 * - Microservice-oriented agent execution
 * - Fail-fast error handling with rich exceptions
 */
class MindService
{
    protected GraphServiceInterface $graphService;
    protected Collection $agentServices;
    protected Collection $activeSessions;
    protected array $config;
    protected Neo4jService $neo4jService;

    public function __construct(
        GraphServiceInterface $graphService,
        Neo4jService $neo4jService
    ) {
        $this->graphService = $graphService;
        $this->neo4jService = $neo4jService;
        $this->agentServices = new Collection();
        $this->activeSessions = new Collection();
        $this->config = Config::get('soul', []);
        
        Log::info("Society of Mind service initialized", [
            'max_sessions' => $this->config['processing']['max_concurrent_sessions'] ?? 10
        ]);
    }

    // ===========================================
    // EXTERNAL WORLD INTERFACE
    // ===========================================

    /**
     * Start cognitive processing session - main entry point
     */
    public function startProcessingSession(array $input, ?string $sessionId = null): string
    {
        $sessionId = $sessionId ?? $this->generateSessionId();
        
        // Check session limits
        if ($this->activeSessions->count() >= ($this->config['processing']['max_concurrent_sessions'] ?? 10)) {
            throw new ProcessingSessionException("Maximum concurrent sessions exceeded");
        }

        $session = [
            'id' => $sessionId,
            'started_at' => now(),
            'input' => $input,
            'status' => 'initializing',
            'activation_history' => [],
            'agent_executions' => [],
            'klines_used' => [],
            'statistics' => [
                'nodes_activated' => 0,
                'agents_executed' => 0,
                'processing_rounds' => 0
            ]
        ];

        $this->activeSessions->put($sessionId, $session);

        Log::info("Society of Mind: Processing session started", [
            'session_id' => $sessionId,
            'input_keys' => array_keys($input),
            'active_sessions_count' => $this->activeSessions->count()
        ]);

        return $sessionId;
    }

    /**
     * Main cognitive processing pipeline
     */
    public function processInput(array $input, string $sessionId): array
    {
        if (!$this->activeSessions->has($sessionId)) {
            throw new ProcessingSessionException("Session not found: {$sessionId}");
        }

        $session = $this->activeSessions->get($sessionId);
        $session['status'] = 'processing';
        $startTime = microtime(true);

        try {
            Log::info("Society of Mind: Starting cognitive pipeline", [
                'session_id' => $sessionId,
                'input_size' => count($input)
            ]);

            // Phase 1: Conceptual Analysis & Initial Activation
            $initialConcepts = $this->extractInitialConcepts($input, $sessionId);
            $activationResult = $this->performSpreadingActivation($initialConcepts, $sessionId);

            // Phase 2: Agent Discovery & Execution
            $agentResults = $this->discoverAndExecuteAgents($activationResult, $input, $sessionId);

            // Phase 3: Iterative Processing Rounds
            $convergenceResult = $this->runProcessingRounds($activationResult, $agentResults, $sessionId);

            // Phase 4: Response Generation & Learning
            $response = $this->generateCognitiveResponse($convergenceResult, $sessionId);
            $this->performLearning($convergenceResult, $sessionId);

            // Update session
            $session['status'] = 'completed';
            $session['processing_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $session['final_result'] = $response;
            $this->activeSessions->put($sessionId, $session);

            Log::info("Society of Mind: Processing completed", [
                'session_id' => $sessionId,
                'processing_time_ms' => $session['processing_time_ms'],
                'nodes_activated' => $session['statistics']['nodes_activated'],
                'agents_executed' => $session['statistics']['agents_executed']
            ]);

            return $response;

        } catch (\Exception $e) {
            $session['status'] = 'failed';
            $session['error'] = $e->getMessage();
            $session['processing_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $this->activeSessions->put($sessionId, $session);

            Log::error("Society of Mind: Processing failed", [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'processing_time_ms' => $session['processing_time_ms']
            ]);

            throw new CognitiveProcessingException(
                "Cognitive processing failed for session {$sessionId}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * End processing session with cleanup
     */
    public function endProcessingSession(string $sessionId): array
    {
        if (!$this->activeSessions->has($sessionId)) {
            throw new ProcessingSessionException("Session not found: {$sessionId}");
        }

        $session = $this->activeSessions->get($sessionId);
        $session['ended_at'] = now();
        
        // Archive session if configured
        if ($this->config['processing']['archive_sessions'] ?? true) {
            $this->archiveSession($session);
        }

        $sessionData = $session;
        $this->activeSessions->forget($sessionId);

        Log::info("Society of Mind: Session ended", [
            'session_id' => $sessionId,
            'total_duration_ms' => $session['processing_time_ms'] ?? 0,
            'final_status' => $session['status']
        ]);

        return $sessionData;
    }

    // ===========================================
    // COGNITIVE PROCESSING PIPELINE
    // ===========================================

    /**
     * Extract initial concepts from input for activation
     */
    protected function extractInitialConcepts(array $input, string $sessionId): array
    {
        $concepts = [];

        // Text analysis
        if (isset($input['text']) && is_string($input['text'])) {
            $words = $this->analyzeTextForConcepts($input['text']);
            $concepts = array_merge($concepts, $words);
        }

        // Direct concept specification
        if (isset($input['concepts']) && is_array($input['concepts'])) {
            $concepts = array_merge($concepts, $input['concepts']);
        }

        // Context-based concepts
        if (isset($input['context'])) {
            $contextConcepts = $this->extractContextConcepts($input['context']);
            $concepts = array_merge($concepts, $contextConcepts);
        }

        $uniqueConcepts = array_unique(array_filter($concepts));

        Log::debug("Society of Mind: Initial concepts extracted", [
            'session_id' => $sessionId,
            'concepts' => $uniqueConcepts,
            'concepts_count' => count($uniqueConcepts)
        ]);

        return $uniqueConcepts;
    }

    /**
     * Perform spreading activation from initial concepts
     */
    protected function performSpreadingActivation(array $initialConcepts, string $sessionId): array
    {
        if (empty($initialConcepts)) {
            Log::warning("Society of Mind: No initial concepts for activation", [
                'session_id' => $sessionId
            ]);
            
            return [
                'activated_nodes' => [],
                'initial_concepts' => [],
                'total_nodes' => 0,
                'analysis' => 'no_activation'
            ];
        }

        $activationOptions = [
            'max_depth' => $this->config['graph']['spreading_activation']['max_depth'] ?? 3,
            'activation_threshold' => $this->config['graph']['spreading_activation']['threshold'] ?? 0.1,
            'include_procedural_agents' => true
        ];

        $activationResult = $this->graphService->runSpreadingActivation($initialConcepts, $activationOptions);
        
        // Analyze results
        $analysis = $this->graphService->analyzeActivationResults($activationResult);
        $activationResult['analysis'] = $analysis;

        // Update session statistics
        $session = $this->activeSessions->get($sessionId);
        $session['statistics']['nodes_activated'] = $activationResult['total_nodes'] ?? 0;
        $session['activation_history'][] = $activationResult;
        $this->activeSessions->put($sessionId, $session);

        Log::info("Society of Mind: Spreading activation completed", [
            'session_id' => $sessionId,
            'initial_concepts' => $initialConcepts,
            'nodes_activated' => $activationResult['total_nodes'] ?? 0,
            'analysis_type' => $analysis['analysis'] ?? 'unknown'
        ]);

        return $activationResult;
    }

    /**
     * Discover and execute relevant procedural agents
     */
    protected function discoverAndExecuteAgents(array $activationResult, array $input, string $sessionId): array
    {
        $agentResults = [];
        $proceduralAgents = $this->extractProceduralAgents($activationResult);

        if (empty($proceduralAgents)) {
            Log::debug("Society of Mind: No procedural agents found", [
                'session_id' => $sessionId
            ]);
            return $agentResults;
        }

        foreach ($proceduralAgents as $agent) {
            try {
                $result = $this->executeProceduralAgent($agent, $input, $sessionId);
                $agentResults[$agent['code_reference']] = $result;
                
                // Update session statistics
                $session = $this->activeSessions->get($sessionId);
                $session['statistics']['agents_executed']++;
                $session['agent_executions'][] = [
                    'agent' => $agent['name'],
                    'code_reference' => $agent['code_reference'],
                    'result' => $result,
                    'executed_at' => now()
                ];
                $this->activeSessions->put($sessionId, $session);

            } catch (\Exception $e) {
                Log::error("Society of Mind: Agent execution failed", [
                    'session_id' => $sessionId,
                    'agent' => $agent['name'],
                    'code_reference' => $agent['code_reference'],
                    'error' => $e->getMessage()
                ]);

                throw new AgentCommunicationException(
                    "Failed to execute agent {$agent['name']}: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        Log::info("Society of Mind: Agent execution phase completed", [
            'session_id' => $sessionId,
            'agents_executed' => count($agentResults),
            'successful_executions' => count(array_filter($agentResults, fn($r) => $r['status'] === 'success'))
        ]);

        return $agentResults;
    }

    /**
     * Run iterative processing rounds until convergence
     */
    protected function runProcessingRounds(array $activationResult, array $agentResults, string $sessionId): array
    {
        $maxRounds = $this->config['processing']['max_processing_rounds'] ?? 5;
        $convergenceThreshold = $this->config['processing']['convergence_threshold'] ?? 0.1;
        
        $round = 0;
        $converged = false;
        $roundResults = [];
        $lastActivationLevel = $this->calculateActivationLevel($activationResult);

        while (!$converged && $round < $maxRounds) {
            $round++;
            
            Log::debug("Society of Mind: Processing round started", [
                'session_id' => $sessionId,
                'round' => $round,
                'max_rounds' => $maxRounds
            ]);

            // Re-run activation based on agent results
            $newConcepts = $this->extractConceptsFromAgentResults($agentResults);
            if (!empty($newConcepts)) {
                $roundActivation = $this->graphService->runSpreadingActivation($newConcepts);
                $currentActivationLevel = $this->calculateActivationLevel($roundActivation);
                
                // Check for convergence
                $change = abs($currentActivationLevel - $lastActivationLevel);
                if ($change < $convergenceThreshold) {
                    $converged = true;
                    Log::debug("Society of Mind: Convergence achieved", [
                        'session_id' => $sessionId,
                        'round' => $round,
                        'change' => $change,
                        'threshold' => $convergenceThreshold
                    ]);
                }
                
                $lastActivationLevel = $currentActivationLevel;
                $roundResults[] = $roundActivation;
            } else {
                // No new concepts, consider converged
                $converged = true;
            }
        }

        // Update session statistics
        $session = $this->activeSessions->get($sessionId);
        $session['statistics']['processing_rounds'] = $round;
        $this->activeSessions->put($sessionId, $session);

        if (!$converged) {
            Log::warning("Society of Mind: Failed to converge", [
                'session_id' => $sessionId,
                'rounds_completed' => $round,
                'max_rounds' => $maxRounds
            ]);
            
            throw new ActivationConvergenceException(
                "Processing failed to converge within {$maxRounds} rounds for session {$sessionId}"
            );
        }

        return [
            'converged' => $converged,
            'rounds' => $round,
            'final_activation' => end($roundResults) ?: $activationResult,
            'round_history' => $roundResults
        ];
    }

    /**
     * Generate final cognitive response
     */
    protected function generateCognitiveResponse(array $convergenceResult, string $sessionId): array
    {
        $session = $this->activeSessions->get($sessionId);
        $finalActivation = $convergenceResult['final_activation'];

        return [
            'session_id' => $sessionId,
            'status' => 'success',
            'processing_summary' => [
                'nodes_activated' => $session['statistics']['nodes_activated'],
                'agents_executed' => $session['statistics']['agents_executed'],
                'processing_rounds' => $session['statistics']['processing_rounds'],
                'converged' => $convergenceResult['converged']
            ],
            'activated_concepts' => array_slice(
                $finalActivation['activated_nodes'] ?? [], 
                0, 
                10
            ), // Top 10 most activated
            'agent_results' => $session['agent_executions'],
            'insights' => $finalActivation['analysis']['insights'] ?? [],
            'recommendations' => $finalActivation['analysis']['recommendations'] ?? [],
            'processing_time_ms' => $session['processing_time_ms'] ?? 0
        ];
    }

    /**
     * Perform K-line learning from successful processing
     */
    protected function performLearning(array $convergenceResult, string $sessionId): void
    {
        if (!$convergenceResult['converged']) {
            Log::debug("Society of Mind: Skipping learning - no convergence", [
                'session_id' => $sessionId
            ]);
            return;
        }

        $session = $this->activeSessions->get($sessionId);
        $context = json_encode($session['input']);

        // Create K-line from successful activation path
        $klineData = [
            'activation_pattern' => $convergenceResult['final_activation'],
            'agent_sequence' => array_column($session['agent_executions'], 'code_reference'),
            'success_metrics' => [
                'processing_rounds' => $convergenceResult['rounds'],
                'success_rate' => 1.0 // Successful completion
            ]
        ];

        try {
            $klineId = $this->graphService->recordKLine($klineData, $context);
            
            $session['klines_used'][] = $klineId;
            $this->activeSessions->put($sessionId, $session);

            Log::info("Society of Mind: K-line recorded for learning", [
                'session_id' => $sessionId,
                'kline_id' => $klineId,
                'context' => substr($context, 0, 100) . '...'
            ]);

        } catch (\Exception $e) {
            Log::warning("Society of Mind: Failed to record K-line", [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ===========================================
    // AGENT SERVICE MANAGEMENT
    // ===========================================

    /**
     * Register an agent service
     */
    public function registerAgentService(string $serviceKey, AgentServiceInterface $service): void
    {
        $this->agentServices->put($serviceKey, $service);
        
        Log::debug("Society of Mind: Agent service registered", [
            'service_key' => $serviceKey,
            'available_agents' => $service->getAvailableAgents()
        ]);
    }

    /**
     * Execute a specific agent via registered services
     */
    protected function executeProceduralAgent(array $agent, array $input, string $sessionId): array
    {
        $codeRef = $agent['code_reference'];
        $parts = explode('::', $codeRef);
        
        if (count($parts) !== 2) {
            throw new AgentCommunicationException("Invalid code reference format: {$codeRef}");
        }

        [$serviceKey, $method] = $parts;
        
        if (!$this->agentServices->has($serviceKey)) {
            throw new AgentCommunicationException("Agent service not found: {$serviceKey}");
        }

        $service = $this->agentServices->get($serviceKey);
        $parameters = array_merge($input, ['session_id' => $sessionId]);

        $timeout = $this->config['agents']['execution_timeout'] ?? 30;
        
        Log::debug("Society of Mind: Executing procedural agent", [
            'session_id' => $sessionId,
            'agent' => $agent['name'],
            'service' => $serviceKey,
            'method' => $method,
            'timeout' => $timeout
        ]);

        // Execute with timeout protection
        $startTime = microtime(true);
        try {
            $result = $service->executeAgent($method, $parameters);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($executionTime > ($timeout * 1000)) {
                throw new ProcessingTimeoutException(
                    "Agent execution timeout: {$codeRef} took {$executionTime}ms"
                );
            }
            
            return [
                'status' => 'success',
                'result' => $result,
                'execution_time_ms' => $executionTime,
                'agent' => $agent['name']
            ];

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error("Society of Mind: Agent execution failed", [
                'session_id' => $sessionId,
                'agent' => $agent['name'],
                'service' => $serviceKey,
                'method' => $method,
                'execution_time_ms' => $executionTime,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    // ===========================================
    // UTILITY METHODS
    // ===========================================

    /**
     * Extract procedural agents from activation results
     */
    protected function extractProceduralAgents(array $activationResult): array
    {
        $agents = [];
        $activatedNodes = $activationResult['activated_nodes'] ?? [];

        foreach ($activatedNodes as $node) {
            if (($node['type'] ?? '') === 'PROCEDURAL_AGENT') {
                $agents[] = $node;
            }
        }

        return $agents;
    }

    /**
     * Analyze text for conceptual content
     */
    protected function analyzeTextForConcepts(string $text): array
    {
        // Simple word extraction - in practice, would use NLP
        $words = array_filter(
            array_map('trim', 
                explode(' ', strtolower($text))
            ),
            fn($word) => strlen($word) > 2
        );

        return array_slice($words, 0, 10); // Limit initial concepts
    }

    /**
     * Extract context-based concepts
     */
    protected function extractContextConcepts(mixed $context): array
    {
        if (is_string($context)) {
            return [$context];
        }
        
        if (is_array($context)) {
            return array_values($context);
        }
        
        return [];
    }

    /**
     * Calculate activation level for convergence detection
     */
    protected function calculateActivationLevel(array $activationResult): float
    {
        $nodes = $activationResult['activated_nodes'] ?? [];
        if (empty($nodes)) {
            return 0.0;
        }

        $totalActivation = array_sum(
            array_column($nodes, 'activation_strength')
        );

        return $totalActivation / count($nodes);
    }

    /**
     * Extract concepts from agent execution results
     */
    protected function extractConceptsFromAgentResults(array $agentResults): array
    {
        $concepts = [];
        
        foreach ($agentResults as $result) {
            if (isset($result['result']['concepts']) && is_array($result['result']['concepts'])) {
                $concepts = array_merge($concepts, $result['result']['concepts']);
            }
        }

        return array_unique($concepts);
    }

    /**
     * Generate unique session ID
     */
    protected function generateSessionId(): string
    {
        return 'session_' . uniqid() . '_' . time();
    }

    /**
     * Archive completed session
     */
    protected function archiveSession(array $session): void
    {
        try {
            $this->neo4jService->archiveProcessingSession($session);
        } catch (\Exception $e) {
            Log::warning("Society of Mind: Failed to archive session", [
                'session_id' => $session['id'],
                'error' => $e->getMessage()
            ]);
        }
    }

    // ===========================================
    // PUBLIC GETTERS & MONITORING
    // ===========================================

    public function getActiveSessionsCount(): int
    {
        return $this->activeSessions->count();
    }

    public function getSessionStatus(string $sessionId): ?array
    {
        return $this->activeSessions->get($sessionId);
    }

    public function getSystemStatistics(): array
    {
        $graphStats = $this->graphService->getGraphStatistics();
        
        return [
            'active_sessions' => $this->activeSessions->count(),
            'registered_agent_services' => $this->agentServices->count(),
            'graph_statistics' => $graphStats,
            'config' => [
                'max_sessions' => $this->config['processing']['max_concurrent_sessions'] ?? 10,
                'session_timeout' => $this->config['processing']['session_timeout'] ?? 300,
                'max_activation_depth' => $this->config['graph']['spreading_activation']['max_depth'] ?? 3
            ]
        ];
    }
}