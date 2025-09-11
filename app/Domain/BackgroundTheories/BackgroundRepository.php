<?php

namespace App\Domain\BackgroundTheories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Repository for Background Theory entities and predicates
 * 
 * Uses Laravel Query Builder (not Eloquent) for direct database operations
 * Handles all entity and predicate persistence across all Background Theory chapters
 */
class BackgroundRepository
{
    /**
     * Entity Operations
     */

    /**
     * Save an entity to the database
     */
    public function saveEntity(BackgroundEntity $entity): void
    {
        DB::table('background_entities')->updateOrInsert(
            ['id' => $entity->getId()],
            $entity->toDatabaseArray()
        );
    }

    /**
     * Find entity by ID
     */
    public function findEntityById(string $id): ?BackgroundEntity
    {
        $row = DB::table('background_entities')->where('id', $id)->first();
        
        if (!$row) {
            return null;
        }

        return $this->rowToEntity((array) $row);
    }

    /**
     * Find entities by type
     */
    public function findEntitiesByType(string $type): Collection
    {
        $rows = DB::table('background_entities')->where('type', $type)->get();
        
        return $rows->map(function ($row) {
            return $this->rowToEntity((array) $row);
        });
    }

    /**
     * Find all entities
     */
    public function findAllEntities(): Collection
    {
        $rows = DB::table('background_entities')->get();
        
        return $rows->map(function ($row) {
            return $this->rowToEntity((array) $row);
        });
    }

    /**
     * Find entities that really exist
     */
    public function findRealEntities(): Collection
    {
        $rows = DB::table('background_entities')
            ->whereRaw("JSON_EXTRACT(attributes, '$.really_exists') = true")
            ->get();
        
        return $rows->map(function ($row) {
            return $this->rowToEntity((array) $row);
        });
    }

    /**
     * Delete entity by ID
     */
    public function deleteEntity(string $id): bool
    {
        return DB::table('background_entities')->where('id', $id)->delete() > 0;
    }

    /**
     * Predicate Operations
     */

    /**
     * Save a predicate to the database
     */
    public function savePredicate(BackgroundPredicate $predicate): void
    {
        DB::table('background_predicates')->updateOrInsert(
            ['id' => $predicate->getId()],
            $predicate->toDatabaseArray()
        );
    }

    /**
     * Find predicate by ID
     */
    public function findPredicateById(string $id): ?BackgroundPredicate
    {
        $row = DB::table('background_predicates')->where('id', $id)->first();
        
        if (!$row) {
            return null;
        }

        return $this->rowToPredicate((array) $row);
    }

    /**
     * Find predicates by name
     */
    public function findPredicatesByName(string $name): Collection
    {
        $rows = DB::table('background_predicates')->where('name', $name)->get();
        
        return $rows->map(function ($row) {
            return $this->rowToPredicate((array) $row);
        });
    }

    /**
     * Find predicates that really exist
     */
    public function findRealPredicates(): Collection
    {
        $rows = DB::table('background_predicates')
            ->where('really_exists', true)
            ->get();
        
        return $rows->map(function ($row) {
            return $this->rowToPredicate((array) $row);
        });
    }

    /**
     * Find all predicates
     */
    public function findAllPredicates(): Collection
    {
        $rows = DB::table('background_predicates')->get();
        
        return $rows->map(function ($row) {
            return $this->rowToPredicate((array) $row);
        });
    }

    /**
     * Check if predicate exists with given name and arguments
     */
    public function predicateExists(string $name, array $arguments): bool
    {
        $serializedArgs = json_encode($this->serializeArguments($arguments));
        
        return DB::table('background_predicates')
            ->where('name', $name)
            ->where('arguments', $serializedArgs)
            ->exists();
    }

    /**
     * Delete predicate by ID
     */
    public function deletePredicate(string $id): bool
    {
        return DB::table('background_predicates')->where('id', $id)->delete() > 0;
    }

    /**
     * Axiom Execution Operations
     */

    /**
     * Log axiom execution
     */
    public function logAxiomExecution(array $executionData): void
    {
        DB::table('axiom_executions')->insert(array_merge($executionData, [
            'created_at' => now()->toDateTimeString(),
        ]));
    }

    /**
     * Get axiom execution history
     */
    public function getAxiomExecutionHistory(string $axiomId = null): Collection
    {
        $query = DB::table('axiom_executions');
        
        if ($axiomId) {
            $query->where('axiom_id', $axiomId);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * System Statistics
     */

    /**
     * Get system statistics
     */
    public function getStatistics(): array
    {
        return [
            'entities' => [
                'total' => DB::table('background_entities')->count(),
                'real_entities' => DB::table('background_entities')
                    ->whereRaw("JSON_EXTRACT(attributes, '$.really_exists') = true")
                    ->count(),
                'by_type' => DB::table('background_entities')
                    ->select('type', DB::raw('COUNT(*) as count'))
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
            ],
            'predicates' => [
                'total' => DB::table('background_predicates')->count(),
                'real_predicates' => DB::table('background_predicates')
                    ->where('really_exists', true)
                    ->count(),
                'by_name' => DB::table('background_predicates')
                    ->select('name', DB::raw('COUNT(*) as count'))
                    ->groupBy('name')
                    ->pluck('count', 'name')
                    ->toArray(),
            ],
            'axiom_executions' => [
                'total' => DB::table('axiom_executions')->count(),
                'last_24h' => DB::table('axiom_executions')
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
            ],
        ];
    }

    /**
     * Helper Methods
     */

    /**
     * Convert database row to entity instance
     */
    private function rowToEntity(array $row): BackgroundEntity
    {
        $className = $this->getEntityClass($row['type']);
        
        if (!class_exists($className)) {
            throw new \RuntimeException("Entity class not found: {$className}");
        }

        $entity = new $className();
        return $entity->fromDatabaseArray($row);
    }

    /**
     * Convert database row to predicate instance
     */
    private function rowToPredicate(array $row): BackgroundPredicate
    {
        $className = $this->getPredicateClass($row['name']);
        
        if (!class_exists($className)) {
            throw new \RuntimeException("Predicate class not found: {$className}");
        }

        // For now, create with empty arguments - would need proper deserialization
        $predicate = new $className([]);
        return $predicate->fromDatabaseArray($row);
    }

    /**
     * Get entity class name from type
     */
    private function getEntityClass(string $type): string
    {
        $className = Str::studly($type) . 'Entity';
        return "App\\Domain\\BackgroundTheories\\Entities\\{$className}";
    }

    /**
     * Get predicate class name from name
     */
    private function getPredicateClass(string $name): string
    {
        $className = Str::studly($name) . 'Predicate';
        return "App\\Domain\\BackgroundTheories\\Predicates\\{$className}";
    }

    /**
     * Serialize arguments for database storage
     */
    private function serializeArguments(array $arguments): array
    {
        return array_map(function ($arg) {
            if ($arg instanceof BackgroundEntity) {
                return ['type' => 'entity', 'id' => $arg->getId(), 'entity_type' => $arg->getType()];
            } elseif ($arg instanceof BackgroundPredicate) {
                return ['type' => 'predicate', 'id' => $arg->getId(), 'name' => $arg->getName()];
            } else {
                return ['type' => 'primitive', 'value' => $arg];
            }
        }, $arguments);
    }

    /**
     * Bulk operations
     */

    /**
     * Save multiple entities in a transaction
     */
    public function saveEntities(Collection $entities): void
    {
        DB::transaction(function () use ($entities) {
            foreach ($entities as $entity) {
                $this->saveEntity($entity);
            }
        });
    }

    /**
     * Save multiple predicates in a transaction
     */
    public function savePredicates(Collection $predicates): void
    {
        DB::transaction(function () use ($predicates) {
            foreach ($predicates as $predicate) {
                $this->savePredicate($predicate);
            }
        });
    }

    /**
     * Clear all data (for testing)
     */
    public function clearAll(): void
    {
        DB::transaction(function () {
            DB::table('axiom_executions')->truncate();
            DB::table('background_predicates')->truncate();
            DB::table('background_entities')->truncate();
        });
    }
}