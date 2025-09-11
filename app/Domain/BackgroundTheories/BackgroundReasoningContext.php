<?php

namespace App\Domain\BackgroundTheories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Main reasoning engine for Background Theories
 * 
 * Orchestrates the execution of FOL axioms and manages the knowledge base
 * of entities and predicates across all Background Theory chapters.
 */
class BackgroundReasoningContext
{
    private Collection $entities;
    private Collection $predicates;
    private BackgroundRepository $repository;
    private array $axiomExecutors;
    private array $executionTrace;
    private bool $canCreateEntities;
    private bool $canCreatePredicates;
    private array $configuration;

    public function __construct(BackgroundRepository $repository)
    {
        $this->entities = collect();
        $this->predicates = collect();
        $this->repository = $repository;
        $this->axiomExecutors = [];
        $this->executionTrace = [];
        $this->canCreateEntities = true;
        $this->canCreatePredicates = true;
        $this->configuration = [];
        
        $this->loadExistingData();
    }

    /**
     * Entity Management
     */

    /**
     * Add entity to the reasoning context
     */
    public function addEntity(BackgroundEntity $entity): void
    {
        if (!$this->canCreateEntities) {
            throw new \RuntimeException("Entity creation is disabled in this context");
        }

        $this->entities->put($entity->getId(), $entity);
        $this->repository->saveEntity($entity);
        
        $this->addTrace('entity_added', [
            'entity_id' => $entity->getId(),
            'entity_type' => $entity->getType()
        ]);
    }

    /**
     * Get entity by ID
     */
    public function getEntityById(string $id): ?BackgroundEntity
    {
        return $this->entities->get($id) ?? $this->repository->findEntityById($id);
    }

    /**
     * Get entities by type
     */
    public function getEntitiesByType(string $type): Collection
    {
        // Get from memory and database
        $memoryEntities = $this->entities->filter(fn($entity) => $entity->getType() === $type);
        $dbEntities = $this->repository->findEntitiesByType($type);
        
        return $memoryEntities->merge($dbEntities)->unique('id');
    }

    /**
     * Get all entities
     */
    public function getAllEntities(): Collection
    {
        $dbEntities = $this->repository->findAllEntities();
        return $this->entities->merge($dbEntities)->unique('id');
    }

    /**
     * Get entities that really exist (Rexist predicate)
     */
    public function getRealEntities(): Collection
    {
        return $this->getAllEntities()->filter(fn($entity) => $entity->reallyExists());
    }

    /**
     * Predicate Management
     */

    /**
     * Add predicate to the reasoning context
     */
    public function addPredicate(BackgroundPredicate $predicate): void
    {
        if (!$this->canCreatePredicates) {
            throw new \RuntimeException("Predicate creation is disabled in this context");
        }

        $this->predicates->push($predicate);
        $this->repository->savePredicate($predicate);
        
        $this->addTrace('predicate_added', [
            'predicate_id' => $predicate->getId(),
            'predicate_name' => $predicate->getName()
        ]);
    }

    /**
     * Get predicate by ID
     */
    public function getPredicateById(string $id): ?BackgroundPredicate
    {
        return $this->predicates->first(fn($p) => $p->getId() === $id) 
            ?? $this->repository->findPredicateById($id);
    }

    /**
     * Get predicates by name
     */
    public function getPredicatesByName(string $name): Collection
    {
        $memoryPredicates = $this->predicates->filter(fn($p) => $p->getName() === $name);
        $dbPredicates = $this->repository->findPredicatesByName($name);
        
        return $memoryPredicates->merge($dbPredicates)->unique('id');
    }

    /**
     * Check if predicates exist by name
     */
    public function hasPredicatesByName(string $name): bool
    {
        return $this->getPredicatesByName($name)->isNotEmpty();
    }

    /**
     * Check if specific predicate exists
     */
    public function predicateExists(string $name, array $arguments): bool
    {
        return $this->repository->predicateExists($name, $arguments);
    }

    /**
     * Get all predicates
     */
    public function getAllPredicates(): Collection
    {
        $dbPredicates = $this->repository->findAllPredicates();
        return $this->predicates->merge($dbPredicates)->unique('id');
    }

    /**
     * Axiom Executor Management
     */

    /**
     * Register an axiom executor
     */
    public function registerAxiomExecutor(string $axiomId, BackgroundAxiomExecutor $executor): void
    {
        $this->axiomExecutors[$axiomId] = $executor;
        
        $this->addTrace('axiom_executor_registered', [
            'axiom_id' => $axiomId,
            'description' => $executor->getDescription()
        ]);
    }

    /**
     * Get registered axiom executors
     */
    public function getAxiomExecutors(): array
    {
        return $this->axiomExecutors;
    }

    /**
     * Check if axiom executor is registered
     */
    public function hasAxiomExecutor(string $axiomId): bool
    {
        return isset($this->axiomExecutors[$axiomId]);
    }

    /**
     * Axiom Execution
     */

    /**
     * Execute a specific axiom
     */
    public function executeAxiom(string $axiomId): Collection
    {
        if (!isset($this->axiomExecutors[$axiomId])) {
            throw new \RuntimeException("Axiom executor not found: {$axiomId}");
        }

        $executor = $this->axiomExecutors[$axiomId];
        
        // Pre-execution validation
        $errors = $executor->validate($this);
        if (!empty($errors)) {
            throw new \RuntimeException("Axiom validation failed: " . implode(', ', $errors));
        }

        // Check if axiom is applicable
        if (!$executor->isApplicable($this)) {
            $this->addTrace('axiom_not_applicable', ['axiom_id' => $axiomId]);
            return collect();
        }

        $startTime = microtime(true);
        
        try {
            $this->addTrace('axiom_execution_started', ['axiom_id' => $axiomId]);
            
            $results = $executor->execute($this);
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
            
            // Log execution
            $this->repository->logAxiomExecution([
                'axiom_id' => $axiomId,
                'input_entities' => json_encode($this->getInputSummary()),
                'output_predicates' => json_encode($results->toArray()),
                'predicates_created' => $results->whereInstanceOf(BackgroundPredicate::class)->count(),
                'entities_created' => $results->whereInstanceOf(BackgroundEntity::class)->count(),
                'execution_time_ms' => $executionTime,
                'reasoning_trace' => json_encode($this->getRecentTrace(10)),
            ]);
            
            $this->addTrace('axiom_execution_completed', [
                'axiom_id' => $axiomId,
                'results_count' => $results->count(),
                'execution_time_ms' => $executionTime
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            $this->addTrace('axiom_execution_failed', [
                'axiom_id' => $axiomId,
                'error' => $e->getMessage()
            ]);
            
            Log::error("Axiom execution failed", [
                'axiom_id' => $axiomId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Execute all applicable axioms
     */
    public function executeAllAxioms(): Collection
    {
        $results = collect();
        $executionOrder = $this->getAxiomExecutionOrder();
        
        foreach ($executionOrder as $axiomId) {
            try {
                $axiomResults = $this->executeAxiom($axiomId);
                $results = $results->merge($axiomResults);
            } catch (\Exception $e) {
                Log::warning("Skipping axiom due to error", [
                    'axiom_id' => $axiomId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $results;
    }

    /**
     * Get axiom execution order (sorted by priority)
     */
    public function getAxiomExecutionOrder(): array
    {
        $executors = collect($this->axiomExecutors);
        
        return $executors->sortBy(function ($executor) {
            return $executor->getPriority();
        })->keys()->toArray();
    }

    /**
     * System State and Configuration
     */

    /**
     * Get current system state
     */
    public function getCurrentState(): array
    {
        return [
            'entities_count' => $this->getAllEntities()->count(),
            'real_entities_count' => $this->getRealEntities()->count(),
            'predicates_count' => $this->getAllPredicates()->count(),
            'axiom_executors_count' => count($this->axiomExecutors),
            'execution_trace_length' => count($this->executionTrace),
            'statistics' => $this->repository->getStatistics(),
        ];
    }

    /**
     * Context capabilities
     */
    public function canCreateEntities(): bool
    {
        return $this->canCreateEntities;
    }

    public function canCreatePredicates(): bool
    {
        return $this->canCreatePredicates;
    }

    public function setCanCreateEntities(bool $canCreate): void
    {
        $this->canCreateEntities = $canCreate;
    }

    public function setCanCreatePredicates(bool $canCreate): void
    {
        $this->canCreatePredicates = $canCreate;
    }

    /**
     * Configuration
     */
    public function setConfiguration(array $config): void
    {
        $this->configuration = $config;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * Execution Trace Management
     */
    
    /**
     * Add entry to execution trace
     */
    private function addTrace(string $event, array $data = []): void
    {
        $this->executionTrace[] = [
            'timestamp' => microtime(true),
            'event' => $event,
            'data' => $data
        ];
        
        // Keep trace limited to avoid memory issues
        if (count($this->executionTrace) > 1000) {
            $this->executionTrace = array_slice($this->executionTrace, -500);
        }
    }

    /**
     * Get recent trace entries
     */
    public function getRecentTrace(int $count = 50): array
    {
        return array_slice($this->executionTrace, -$count);
    }

    /**
     * Get full execution trace
     */
    public function getFullTrace(): array
    {
        return $this->executionTrace;
    }

    /**
     * Clear execution trace
     */
    public function clearTrace(): void
    {
        $this->executionTrace = [];
    }

    /**
     * Helper Methods
     */

    /**
     * Load existing data from repository
     */
    private function loadExistingData(): void
    {
        // Load entities into memory for faster access
        $entities = $this->repository->findAllEntities();
        foreach ($entities as $entity) {
            $this->entities->put($entity->getId(), $entity);
        }
        
        // Load predicates into memory
        $predicates = $this->repository->findAllPredicates();
        $this->predicates = $predicates;
    }

    /**
     * Get input summary for logging
     */
    private function getInputSummary(): array
    {
        return [
            'entities_by_type' => $this->getAllEntities()->groupBy('type')->map->count()->toArray(),
            'predicates_by_name' => $this->getAllPredicates()->groupBy('name')->map->count()->toArray(),
        ];
    }
}