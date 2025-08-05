<?php

namespace App\Soul\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Soul\Frame;
use App\Soul\FrameInstance;
use App\Soul\Contracts\FrameDefinitionRegistry;
use App\Soul\Contracts\Neo4jService;
use App\Soul\Exceptions\FrameNotFoundException;
use App\Soul\Exceptions\FrameInstanceNotFoundException;
use App\Soul\Exceptions\FrameInstantiationException;

/**
 * MindService - The central coordinator of the SOUL framework
 *
 * Responsibilities:
 * 1. Interface between external world and cognitive system
 * 2. Frame definition registry and instantiation
 * 3. Frame instance lifecycle management
 * 4. Agent communication facilitation
 * 5. Processing session coordination
 */
class MindService
{
    protected Collection $frameDefinitions;
    protected Collection $activeInstances;
    protected Collection $processingSessions;
    protected Neo4jService $neo4jService;
    protected FrameDefinitionRegistry $frameRegistry;
    protected string $currentSessionId;
    protected array $statistics;

    public function __construct(
        Neo4jService $neo4jService,
        FrameDefinitionRegistry $frameRegistry
    ) {
        $this->frameDefinitions = new Collection();
        $this->activeInstances = new Collection();
        $this->processingSessions = new Collection();
        $this->neo4jService = $neo4jService;
        $this->frameRegistry = $frameRegistry;
        $this->currentSessionId = null;
        $this->statistics = [
            'instances_created' => 0,
            'instances_destroyed' => 0,
            'agent_communications' => 0,
            'sessions_started' => 0
        ];

        $this->loadFrameDefinitions();
    }

    // ===========================================
    // EXTERNAL WORLD INTERFACE
    // ===========================================

    /**
     * Start a new cognitive processing session
     * This is the main entry point from the external world
     */
    public function startProcessingSession(array $input, ?string $sessionId = null): string
    {
        $sessionId = $sessionId ?? $this->generateSessionId();
        $this->currentSessionId = $sessionId;

        $session = [
            'id' => $sessionId,
            'started_at' => now(),
            'input' => $input,
            'instances' => new Collection(),
            'status' => 'active'
        ];

        $this->processingSessions->put($sessionId, $session);
        $this->statistics['sessions_started']++;

        Log::info("SOUL: Started processing session", ['session_id' => $sessionId]);

        return $sessionId;
    }

    /**
     * Process input and return cognitive response
     * Main cognitive processing pipeline
     */
    public function processInput(array $input, ?string $sessionId = null): array
    {
        $sessionId = $sessionId ?? $this->currentSessionId;

        if (!$sessionId || !$this->processingSessions->has($sessionId)) {
            throw new \Exception("No active processing session found");
        }

        try {
            // 1. Analyze input and identify relevant frames
            $relevantFrames = $this->analyzeInput($input);

            // 2. Instantiate initial frames
            $initialInstances = $this->instantiateInitialFrames($relevantFrames, $input, $sessionId);

            // 3. Activate initial agents (Minsky's frame matching process)
            $activationResults = $this->activateInitialAgents($initialInstances);

            // 4. Let the cognitive network process (spreading activation)
            $processingResults = $this->runCognitiveProcessing($sessionId);

            // 5. Extract final state and generate response
            $response = $this->generateResponse($sessionId);

            return $response;

        } catch (\Exception $e) {
            Log::error("SOUL: Processing error", [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * End processing session and cleanup
     */
    public function endProcessingSession(?string $sessionId = null): array
    {
        $sessionId = $sessionId ?? $this->currentSessionId;

        if (!$this->processingSessions->has($sessionId)) {
            throw new \Exception("Session not found: {$sessionId}");
        }

        $session = $this->processingSessions->get($sessionId);
        $session['ended_at'] = now();
        $session['status'] = 'completed';

        // Cleanup session instances
        $this->cleanupSessionInstances($sessionId);

        // Archive session in Neo4j if needed
        $this->archiveSession($session);

        $this->processingSessions->forget($sessionId);

        if ($this->currentSessionId === $sessionId) {
            $this->currentSessionId = null;
        }

        Log::info("SOUL: Ended processing session", ['session_id' => $sessionId]);

        return $session;
    }

    // ===========================================
    // FRAME DEFINITION MANAGEMENT
    // ===========================================

    /**
     * Register a frame definition
     */
    public function registerFrameDefinition(Frame $frame): void
    {
        $this->frameDefinitions->put($frame->getId(), $frame);
        Log::debug("SOUL: Registered frame definition", ['frame_id' => $frame->getId()]);
    }

    /**
     * Get frame definition by ID
     */
    public function getFrameDefinition(string $frameId): Frame
    {
        if (!$this->frameDefinitions->has($frameId)) {
            throw new FrameNotFoundException("Frame definition not found: {$frameId}");
        }

        return $this->frameDefinitions->get($frameId);
    }

    /**
     * Check if frame definition exists
     */
    public function hasFrameDefinition(string $frameId): bool
    {
        return $this->frameDefinitions->has($frameId);
    }

    /**
     * Get all frame definitions of a specific type
     */
    public function getFrameDefinitionsByType(string $type): Collection
    {
        return $this->frameDefinitions->filter(function($frame) use ($type) {
            return $frame->getType() === $type;
        });
    }

    // ===========================================
    // FRAME INSTANCE MANAGEMENT
    // ===========================================

    /**
     * Instantiate a frame - main method used by agents
     */
    public function instantiateFrame(
        string $frameId,
        array $context = [],
        ?string $sessionId = null,
        ?string $instanceId = null
    ): FrameInstance {
        $sessionId = $sessionId ?? $this->currentSessionId;

        if (!$this->hasFrameDefinition($frameId)) {
            throw new FrameNotFoundException("Cannot instantiate unknown frame: {$frameId}");
        }

        try {
            $frameDefinition = $this->getFrameDefinition($frameId);
            $instance = $frameDefinition->instantiate($context, $instanceId);

            // Register instance
            $this->registerFrameInstance($instance, $sessionId);

            // Store in Neo4j for this session
            $this->persistInstanceToNeo4j($instance, $sessionId);

            $this->statistics['instances_created']++;

            Log::debug("SOUL: Instantiated frame", [
                'frame_id' => $frameId,
                'instance_id' => $instance->getInstanceId(),
                'session_id' => $sessionId
            ]);

            return $instance;

        } catch (\Exception $e) {
            throw new FrameInstantiationException(
                "Failed to instantiate frame {$frameId}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Register a frame instance (called by Frame::instantiate)
     */
    public function registerFrameInstance(FrameInstance $instance, ?string $sessionId = null): void
    {
        $sessionId = $sessionId ?? $this->currentSessionId;

        // Add to global registry
        $this->activeInstances->put($instance->getInstanceId(), $instance);

        // Add to session registry
        if ($sessionId && $this->processingSessions->has($sessionId)) {
            $session = $this->processingSessions->get($sessionId);
            $session['instances']->put($instance->getInstanceId(), $instance);
        }
    }

    /**
     * Get frame instance by ID - used by agents for communication
     */
    public function getFrameInstance(string $instanceId): ?FrameInstance
    {
        return $this->activeInstances->get($instanceId);
    }

    /**
     * Check if frame instance exists
     */
    public function hasFrameInstance(string $instanceId): bool
    {
        return $this->activeInstances->has($instanceId);
    }

    /**
     * Get all instances of a specific frame type
     */
    public function getInstancesByFrameId(string $frameId, ?string $sessionId = null): Collection
    {
        $instances = $sessionId && $this->processingSessions->has($sessionId)
            ? $this->processingSessions->get($sessionId)['instances']
            : $this->activeInstances;

        return $instances->filter(function($instance) use ($frameId) {
            return $instance->getFrameId() === $frameId;
        });
    }

    /**
     * Remove frame instance
     */
    public function destroyFrameInstance(string $instanceId): bool
    {
        if (!$this->activeInstances->has($instanceId)) {
            return false;
        }

        $instance = $this->activeInstances->get($instanceId);

        // Remove from Neo4j
        $this->removeInstanceFromNeo4j($instanceId);

        // Remove from all session registries
        foreach ($this->processingSessions as $session) {
            $session['instances']->forget($instanceId);
        }

        // Remove from global registry
        $this->activeInstances->forget($instanceId);

        $this->statistics['instances_destroyed']++;

        Log::debug("SOUL: Destroyed frame instance", ['instance_id' => $instanceId]);

        return true;
    }

    // ===========================================
    // AGENT COMMUNICATION SUPPORT
    // ===========================================

    /**
     * Facilitate agent communication - called by FrameInstance::sendMessageToAgent
     */
    public function facilitateAgentCommunication(
        string $fromInstanceId,
        string $toInstanceId,
        string $method,
        array $params = []
    ): mixed {
        $this->statistics['agent_communications']++;

        $fromInstance = $this->getFrameInstance($fromInstanceId);
        $toInstance = $this->getFrameInstance($toInstanceId);

        if (!$fromInstance || !$toInstance) {
            throw new FrameInstanceNotFoundException(
                "Cannot facilitate communication: instance not found"
            );
        }

        Log::debug("SOUL: Agent communication", [
            'from' => $fromInstanceId,
            'to' => $toInstanceId,
            'method' => $method
        ]);

        // Validate method exists
        if (!method_exists($toInstance, $method)) {
            throw new \Exception("Method {$method} not found in {$toInstanceId}");
        }

        // Execute the method call
        return $toInstance->$method($fromInstance, ...$params);
    }

    // ===========================================
    // COGNITIVE PROCESSING PIPELINE
    // ===========================================

    /**
     * Analyze input to identify relevant frames
     */
    protected function analyzeInput(array $input): array
    {
        $relevantFrames = [];

        // Simple keyword-based frame identification
        // In a full implementation, this would be much more sophisticated
        foreach ($input as $key => $value) {
            if ($key === 'text' && is_string($value)) {
                // Analyze text for frame triggers
                $words = explode(' ', strtolower($value));
                foreach ($words as $word) {
                    $frameId = $this->findFrameByTrigger($word);
                    if ($frameId) {
                        $relevantFrames[] = $frameId;
                    }
                }
            }
        }

        return array_unique($relevantFrames);
    }

    /**
     * Instantiate initial frames for processing
     */
    protected function instantiateInitialFrames(array $frameIds, array $context, string $sessionId): Collection
    {
        $instances = new Collection();

        foreach ($frameIds as $frameId) {
            try {
                $instance = $this->instantiateFrame($frameId, $context, $sessionId);
                $instances->put($instance->getInstanceId(), $instance);
            } catch (\Exception $e) {
                Log::warning("SOUL: Failed to instantiate initial frame", [
                    'frame_id' => $frameId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $instances;
    }

    /**
     * Activate initial agents (Minsky's matching process)
     */
    protected function activateInitialAgents(Collection $instances): array
    {
        $results = [];

        foreach ($instances as $instance) {
            // Call the instance's initial activation method if it exists
            if (method_exists($instance, 'initialActivation')) {
                try {
                    $result = $instance->initialActivation();
                    $results[$instance->getInstanceId()] = $result;
                } catch (\Exception $e) {
                    Log::warning("SOUL: Initial activation failed", [
                        'instance_id' => $instance->getInstanceId(),
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $results;
    }

    /**
     * Run cognitive processing (spreading activation)
     */
    protected function runCognitiveProcessing(string $sessionId): array
    {
        $session = $this->processingSessions->get($sessionId);
        $instances = $session['instances'];

        $processingRounds = 0;
        $maxRounds = 10; // Prevent infinite loops
        $hasActivity = true;

        while ($hasActivity && $processingRounds < $maxRounds) {
            $hasActivity = false;
            $processingRounds++;

            foreach ($instances as $instance) {
                // Call the instance's processing method if it exists
                if (method_exists($instance, 'cognitiveProcess')) {
                    try {
                        $activity = $instance->cognitiveProcess();
                        if ($activity) {
                            $hasActivity = true;
                        }
                    } catch (\Exception $e) {
                        Log::warning("SOUL: Cognitive processing error", [
                            'instance_id' => $instance->getInstanceId(),
                            'round' => $processingRounds,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        return [
            'rounds' => $processingRounds,
            'completed' => !$hasActivity
        ];
    }

    /**
     * Generate response from final cognitive state
     */
    protected function generateResponse(string $sessionId): array
    {
        $session = $this->processingSessions->get($sessionId);

        return [
            'session_id' => $sessionId,
            'instances_count' => $session['instances']->count(),
            'processing_time' => now()->diffInMilliseconds($session['started_at']),
            'result' => 'processed', // Placeholder for actual results
            'statistics' => $this->getSessionStatistics($sessionId)
        ];
    }

    // ===========================================
    // SUPPORT METHODS
    // ===========================================

    /**
     * Load frame definitions from registry
     */
    protected function loadFrameDefinitions(): void
    {
        $definitions = $this->frameRegistry->getAllFrameDefinitions();

        foreach ($definitions as $frame) {
            $this->registerFrameDefinition($frame);
        }

        Log::info("SOUL: Loaded frame definitions", ['count' => $definitions->count()]);
    }

    /**
     * Find frame by trigger word (simple implementation)
     */
    protected function findFrameByTrigger(string $word): ?string
    {
        // This is a placeholder - in reality, this would be much more sophisticated
        $triggers = [
            'buy' => 'COMMERCIAL_TRANSACTION',
            'sell' => 'COMMERCIAL_TRANSACTION',
            'person' => 'PERSON',
            'walk' => 'MOTION',
            'container' => 'CONTAINER'
        ];

        return $triggers[$word] ?? null;
    }

    /**
     * Generate unique session ID
     */
    protected function generateSessionId(): string
    {
        return 'session_' . uniqid() . '_' . time();
    }

    /**
     * Cleanup instances for a session
     */
    protected function cleanupSessionInstances(string $sessionId): void
    {
        if (!$this->processingSessions->has($sessionId)) {
            return;
        }

        $session = $this->processingSessions->get($sessionId);
        $instances = $session['instances'];

        foreach ($instances as $instance) {
            $this->destroyFrameInstance($instance->getInstanceId());
        }
    }

    /**
     * Get statistics for a session
     */
    protected function getSessionStatistics(string $sessionId): array
    {
        $session = $this->processingSessions->get($sessionId);

        return [
            'instances_created' => $session['instances']->count(),
            'duration_ms' => now()->diffInMilliseconds($session['started_at']),
            'status' => $session['status']
        ];
    }

    // ===========================================
    // NEO4J INTEGRATION METHODS
    // ===========================================

    /**
     * Persist frame instance to Neo4j
     */
    protected function persistInstanceToNeo4j(FrameInstance $instance, string $sessionId): void
    {
        try {
            $this->neo4jService->createFrameInstanceNode($instance, $sessionId);
        } catch (\Exception $e) {
            Log::warning("SOUL: Failed to persist instance to Neo4j", [
                'instance_id' => $instance->getInstanceId(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove instance from Neo4j
     */
    protected function removeInstanceFromNeo4j(string $instanceId): void
    {
        try {
            $this->neo4jService->deleteFrameInstanceNode($instanceId);
        } catch (\Exception $e) {
            Log::warning("SOUL: Failed to remove instance from Neo4j", [
                'instance_id' => $instanceId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Archive completed session
     */
    protected function archiveSession(array $session): void
    {
        try {
            $this->neo4jService->archiveProcessingSession($session);
        } catch (\Exception $e) {
            Log::warning("SOUL: Failed to archive session", [
                'session_id' => $session['id'],
                'error' => $e->getMessage()
            ]);
        }
    }

    // ===========================================
    // PUBLIC GETTERS & STATISTICS
    // ===========================================

    public function getCurrentSessionId(): ?string
    {
        return $this->currentSessionId;
    }

    public function getStatistics(): array
    {
        return array_merge($this->statistics, [
            'active_instances' => $this->activeInstances->count(),
            'active_sessions' => $this->processingSessions->count(),
            'registered_frames' => $this->frameDefinitions->count()
        ]);
    }

    public function getActiveInstances(): Collection
    {
        return $this->activeInstances;
    }

    public function getFrameDefinitions(): Collection
    {
        return $this->frameDefinitions;
    }

    public function getProcessingSessions(): Collection
    {
        return $this->processingSessions;
    }
}
