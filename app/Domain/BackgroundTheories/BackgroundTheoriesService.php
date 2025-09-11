<?php

namespace App\Domain\BackgroundTheories;

use Illuminate\Support\Collection;

/**
 * Main public API service for Background Theories
 * 
 * Provides high-level interface for interacting with the FOL axiom system
 * and managing Background Theory entities and predicates.
 */
class BackgroundTheoriesService
{
    private BackgroundReasoningContext $context;
    private BackgroundRepository $repository;

    public function __construct(BackgroundReasoningContext $context, BackgroundRepository $repository)
    {
        $this->context = $context;
        $this->repository = $repository;
    }

    /**
     * Primary Operations
     */

    /**
     * Execute all background theories
     */
    public function executeAllTheories(): Collection
    {
        return $this->context->executeAllAxioms();
    }

    /**
     * Execute a specific axiom
     */
    public function executeAxiom(string $axiomId): Collection
    {
        return $this->context->executeAxiom($axiomId);
    }

    /**
     * Register an axiom executor
     */
    public function registerAxiomExecutor(string $axiomId, BackgroundAxiomExecutor $executor): void
    {
        $this->context->registerAxiomExecutor($axiomId, $executor);
    }

    /**
     * Entity Management
     */

    /**
     * Add entity to the system
     */
    public function addEntity(BackgroundEntity $entity): void
    {
        $this->context->addEntity($entity);
    }

    /**
     * Get entity by ID
     */
    public function getEntity(string $id): ?BackgroundEntity
    {
        return $this->context->getEntityById($id);
    }

    /**
     * Get entities by type
     */
    public function getEntities(string $type = null): Collection
    {
        if ($type) {
            return $this->context->getEntitiesByType($type);
        }
        return $this->context->getAllEntities();
    }

    /**
     * Get entities that really exist (Rexist predicate)
     */
    public function getRealEntities(): Collection
    {
        return $this->context->getRealEntities();
    }

    /**
     * Create entity of specific type
     */
    public function createEntity(string $type, array $attributes = []): BackgroundEntity
    {
        $className = $this->getEntityClassName($type);
        
        if (!class_exists($className)) {
            throw new \RuntimeException("Entity class not found: {$className}");
        }

        $entity = new $className($attributes);
        $this->addEntity($entity);
        
        return $entity;
    }

    /**
     * Predicate Management
     */

    /**
     * Add predicate to the system
     */
    public function addPredicate(BackgroundPredicate $predicate): void
    {
        $this->context->addPredicate($predicate);
    }

    /**
     * Get predicate by ID
     */
    public function getPredicate(string $id): ?BackgroundPredicate
    {
        return $this->context->getPredicateById($id);
    }

    /**
     * Get predicates by name
     */
    public function getPredicates(string $name = null): Collection
    {
        if ($name) {
            return $this->context->getPredicatesByName($name);
        }
        return $this->context->getAllPredicates();
    }

    /**
     * Check if predicate exists
     */
    public function predicateExists(string $name, array $arguments): bool
    {
        return $this->context->predicateExists($name, $arguments);
    }

    /**
     * Create predicate
     */
    public function createPredicate(string $name, array $arguments): BackgroundPredicate
    {
        $className = $this->getPredicateClassName($name);
        
        if (!class_exists($className)) {
            throw new \RuntimeException("Predicate class not found: {$className}");
        }

        $predicate = new $className(...$arguments);
        $this->addPredicate($predicate);
        
        return $predicate;
    }

    /**
     * Reasoning Operations
     */

    /**
     * Evaluate a predicate in the current context
     */
    public function evaluate(BackgroundPredicate $predicate): bool
    {
        return $predicate->evaluate($this->context);
    }

    /**
     * Check if an entity really exists
     */
    public function reallyExists(BackgroundEntity $entity): bool
    {
        return $entity->reallyExists();
    }

    /**
     * Make an entity really exist
     */
    public function realize(BackgroundEntity $entity): void
    {
        $entity->realize();
        $this->repository->saveEntity($entity);
    }

    /**
     * Cross-Theory Reasoning
     */

    /**
     * Perform complex reasoning across multiple theories
     */
    public function performCrossTheoryReasoning(array $initialEntities = [], array $initialPredicates = []): array
    {
        $results = [];
        
        // Add initial entities and predicates
        foreach ($initialEntities as $entity) {
            $this->addEntity($entity);
        }
        
        foreach ($initialPredicates as $predicate) {
            $this->addPredicate($predicate);
        }
        
        // Execute all applicable axioms
        $axiomResults = $this->executeAllTheories();
        
        // Analyze results
        $results['initial_entities'] = count($initialEntities);
        $results['initial_predicates'] = count($initialPredicates);
        $results['axiom_results'] = $axiomResults->count();
        $results['new_predicates'] = $axiomResults->whereInstanceOf(BackgroundPredicate::class)->count();
        $results['new_entities'] = $axiomResults->whereInstanceOf(BackgroundEntity::class)->count();
        $results['final_state'] = $this->getSystemState();
        
        return $results;
    }

    /**
     * System Introspection and Management
     */

    /**
     * Get current system state
     */
    public function getSystemState(): array
    {
        return $this->context->getCurrentState();
    }

    /**
     * Get system statistics
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Get axiom execution history
     */
    public function getAxiomExecutionHistory(string $axiomId = null): Collection
    {
        return $this->repository->getAxiomExecutionHistory($axiomId);
    }

    /**
     * Get registered axiom executors
     */
    public function getAxiomExecutors(): array
    {
        return $this->context->getAxiomExecutors();
    }

    /**
     * Get execution trace
     */
    public function getExecutionTrace(int $limit = 50): array
    {
        return $this->context->getRecentTrace($limit);
    }

    /**
     * Clear execution trace
     */
    public function clearExecutionTrace(): void
    {
        $this->context->clearTrace();
    }

    /**
     * Configuration Management
     */

    /**
     * Set system configuration
     */
    public function setConfiguration(array $config): void
    {
        $this->context->setConfiguration($config);
    }

    /**
     * Get system configuration
     */
    public function getConfiguration(): array
    {
        return $this->context->getConfiguration();
    }

    /**
     * Set entity creation capability
     */
    public function setCanCreateEntities(bool $canCreate): void
    {
        $this->context->setCanCreateEntities($canCreate);
    }

    /**
     * Set predicate creation capability
     */
    public function setCanCreatePredicates(bool $canCreate): void
    {
        $this->context->setCanCreatePredicates($canCreate);
    }

    /**
     * Import/Export Operations
     */

    /**
     * Export system state to array
     */
    public function exportSystemState(): array
    {
        return [
            'entities' => $this->getEntities()->map->jsonSerialize()->toArray(),
            'predicates' => $this->getPredicates()->map->jsonSerialize()->toArray(),
            'statistics' => $this->getStatistics(),
            'configuration' => $this->getConfiguration(),
            'exported_at' => now()->toISOString(),
        ];
    }

    /**
     * Import system state from array
     */
    public function importSystemState(array $data): array
    {
        $results = [
            'entities_imported' => 0,
            'predicates_imported' => 0,
            'errors' => []
        ];

        // Import entities
        foreach ($data['entities'] ?? [] as $entityData) {
            try {
                $entity = $this->createEntityFromData($entityData);
                $this->addEntity($entity);
                $results['entities_imported']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Entity import error: " . $e->getMessage();
            }
        }

        // Import predicates
        foreach ($data['predicates'] ?? [] as $predicateData) {
            try {
                $predicate = $this->createPredicateFromData($predicateData);
                $this->addPredicate($predicate);
                $results['predicates_imported']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Predicate import error: " . $e->getMessage();
            }
        }

        // Import configuration
        if (isset($data['configuration'])) {
            $this->setConfiguration($data['configuration']);
        }

        return $results;
    }

    /**
     * Bulk Operations
     */

    /**
     * Save multiple entities at once
     */
    public function saveEntities(Collection $entities): void
    {
        $this->repository->saveEntities($entities);
    }

    /**
     * Save multiple predicates at once
     */
    public function savePredicates(Collection $predicates): void
    {
        $this->repository->savePredicates($predicates);
    }

    /**
     * Clear all data (for testing)
     */
    public function clearAll(): void
    {
        $this->repository->clearAll();
        $this->context->clearTrace();
    }

    /**
     * Helper Methods
     */

    /**
     * Get entity class name from type
     */
    private function getEntityClassName(string $type): string
    {
        $className = ucfirst(\Illuminate\Support\Str::camel($type)) . 'Entity';
        return "App\\Domain\\BackgroundTheories\\Entities\\{$className}";
    }

    /**
     * Get predicate class name from name
     */
    private function getPredicateClassName(string $name): string
    {
        $className = ucfirst(\Illuminate\Support\Str::camel($name)) . 'Predicate';
        return "App\\Domain\\BackgroundTheories\\Predicates\\{$className}";
    }

    /**
     * Create entity from data array
     */
    private function createEntityFromData(array $data): BackgroundEntity
    {
        $type = $data['type'];
        $attributes = $data['attributes'] ?? [];
        
        return $this->createEntity($type, $attributes);
    }

    /**
     * Create predicate from data array
     */
    private function createPredicateFromData(array $data): BackgroundPredicate
    {
        $name = $data['name'];
        $arguments = $data['arguments'] ?? [];
        
        return $this->createPredicate($name, $arguments);
    }

    /**
     * Validate system integrity
     */
    public function validateSystemIntegrity(): array
    {
        $issues = [];

        // Check for orphaned predicates (referencing non-existent entities)
        $predicates = $this->getPredicates();
        foreach ($predicates as $predicate) {
            foreach ($predicate->getArguments() as $arg) {
                if ($arg instanceof BackgroundEntity) {
                    if (!$this->getEntity($arg->getId())) {
                        $issues[] = "Orphaned predicate {$predicate->getId()} references missing entity {$arg->getId()}";
                    }
                }
            }
        }

        // Check for duplicate entities with same attributes
        $entities = $this->getEntities();
        $duplicates = $entities->duplicates(function ($entity) {
            return $entity->getType() . '::' . json_encode($entity->getAttributes());
        });

        if ($duplicates->isNotEmpty()) {
            $issues[] = "Found {$duplicates->count()} duplicate entities";
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'checked_entities' => $entities->count(),
            'checked_predicates' => $predicates->count(),
        ];
    }
}