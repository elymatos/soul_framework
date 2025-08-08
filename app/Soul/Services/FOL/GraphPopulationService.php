<?php

namespace App\Soul\Services\FOL;

use App\Soul\Models\LogicalAxiom;
use App\Soul\Models\PsychologicalPredicate;
use App\Soul\Contracts\Neo4jService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * GraphPopulationService - Populate Neo4j graph with FOL axioms and predicates
 *
 * This service manages the population of the Neo4j graph database with
 * parsed FOL axioms, predicates, and their relationships for the SOUL framework.
 */
class GraphPopulationService
{
    protected Neo4jService $neo4jService;
    protected PredicateGraphService $predicateService;
    protected RelationshipBuilderService $relationshipBuilder;
    protected array $config;
    
    public function __construct(
        Neo4jService $neo4jService,
        PredicateGraphService $predicateService,
        RelationshipBuilderService $relationshipBuilder
    ) {
        $this->neo4jService = $neo4jService;
        $this->predicateService = $predicateService;
        $this->relationshipBuilder = $relationshipBuilder;
        $this->config = Config::get('soul.fol', []);
    }
    
    /**
     * Populate graph from collection of LogicalAxiom models
     */
    public function populateGraphFromAxioms(Collection $axioms): array
    {
        $results = [
            'axioms_created' => 0,
            'predicates_created' => 0,
            'relationships_created' => 0,
            'errors' => [],
            'processing_time' => 0,
            'batch_results' => []
        ];
        
        $startTime = microtime(true);
        
        Log::info("FOL GraphPopulation: Starting graph population", [
            'total_axioms' => $axioms->count(),
            'batch_size' => $this->config['axiom_processing']['batch_size'] ?? 50
        ]);
        
        try {
            // Process in batches if enabled
            if ($this->config['integration']['neo4j_batch_operations'] ?? true) {
                $results = $this->populateInBatches($axioms, $results);
            } else {
                $results = $this->populateSequentially($axioms, $results);
            }
            
            $results['processing_time'] = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info("FOL GraphPopulation: Population completed successfully", [
                'axioms_created' => $results['axioms_created'],
                'predicates_created' => $results['predicates_created'],
                'relationships_created' => $results['relationships_created'],
                'processing_time_ms' => $results['processing_time'],
                'errors_count' => count($results['errors'])
            ]);
            
        } catch (\Exception $e) {
            Log::error("FOL GraphPopulation: Population failed", [
                'error' => $e->getMessage(),
                'processed_axioms' => $results['axioms_created']
            ]);
            
            $results['errors'][] = [
                'type' => 'fatal_error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
            
            throw $e;
        }
        
        return $results;
    }
    
    /**
     * Populate graph in batches for better performance
     */
    protected function populateInBatches(Collection $axioms, array $results): array
    {
        $batchSize = $this->config['axiom_processing']['batch_size'] ?? 50;
        $batches = $axioms->chunk($batchSize);
        
        Log::info("FOL GraphPopulation: Processing in batches", [
            'total_batches' => $batches->count(),
            'batch_size' => $batchSize
        ]);
        
        foreach ($batches as $batchIndex => $batch) {
            $batchStartTime = microtime(true);
            
            try {
                // Start transaction for batch
                $this->neo4jService->beginTransaction();
                
                $batchResults = [
                    'axioms_created' => 0,
                    'predicates_created' => 0,
                    'relationships_created' => 0,
                    'errors' => []
                ];
                
                // Process axioms in this batch
                $batchResults = $this->processAxiomBatch($batch, $batchResults);
                
                // Extract and create predicates
                $predicates = $this->extractPredicatesFromBatch($batch);
                $batchResults = $this->processPredicateBatch($predicates, $batchResults);
                
                // Build relationships
                $batchResults = $this->buildBatchRelationships($batch, $predicates, $batchResults);
                
                // Commit transaction
                $this->neo4jService->commit();
                
                // Aggregate results
                $results['axioms_created'] += $batchResults['axioms_created'];
                $results['predicates_created'] += $batchResults['predicates_created'];
                $results['relationships_created'] += $batchResults['relationships_created'];
                $results['errors'] = array_merge($results['errors'], $batchResults['errors']);
                
                $batchTime = round((microtime(true) - $batchStartTime) * 1000, 2);
                
                $results['batch_results'][] = [
                    'batch_index' => $batchIndex,
                    'batch_size' => $batch->count(),
                    'processing_time_ms' => $batchTime,
                    'axioms_created' => $batchResults['axioms_created'],
                    'predicates_created' => $batchResults['predicates_created'],
                    'relationships_created' => $batchResults['relationships_created']
                ];
                
                Log::debug("FOL GraphPopulation: Batch completed", [
                    'batch' => $batchIndex + 1,
                    'batch_time_ms' => $batchTime,
                    'axioms_processed' => $batchResults['axioms_created']
                ]);
                
            } catch (\Exception $e) {
                // Rollback transaction on error
                $this->neo4jService->rollback();
                
                $error = [
                    'type' => 'batch_error',
                    'batch_index' => $batchIndex,
                    'message' => $e->getMessage(),
                    'axioms_in_batch' => $batch->count()
                ];
                
                $results['errors'][] = $error;
                
                Log::error("FOL GraphPopulation: Batch failed", $error);
                
                // Continue with next batch or fail completely based on configuration
                if (!($this->config['axiom_processing']['continue_on_error'] ?? true)) {
                    throw $e;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Populate graph sequentially (one by one)
     */
    protected function populateSequentially(Collection $axioms, array $results): array
    {
        // Extract all predicates first
        $allPredicates = $this->extractPredicatesFromAxioms($axioms);
        $results = $this->processPredicateBatch($allPredicates, $results);
        
        // Process each axiom individually
        foreach ($axioms as $index => $axiom) {
            try {
                $results = $this->processSingleAxiom($axiom, $allPredicates, $results);
                
                if (($index + 1) % 50 === 0) {
                    Log::debug("FOL GraphPopulation: Progress update", [
                        'processed' => $index + 1,
                        'total' => $axioms->count(),
                        'percentage' => round((($index + 1) / $axioms->count()) * 100, 1)
                    ]);
                }
                
            } catch (\Exception $e) {
                $error = [
                    'type' => 'axiom_error',
                    'axiom_id' => $axiom->axiom_id,
                    'axiom_name' => $axiom->name,
                    'message' => $e->getMessage()
                ];
                
                $results['errors'][] = $error;
                
                Log::warning("FOL GraphPopulation: Axiom processing failed", $error);
            }
        }
        
        return $results;
    }
    
    /**
     * Process a batch of axioms
     */
    protected function processAxiomBatch(Collection $batch, array $results): array
    {
        foreach ($batch as $axiom) {
            try {
                $this->createAxiomNode($axiom);
                $results['axioms_created']++;
                
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'type' => 'axiom_creation_error',
                    'axiom_id' => $axiom->axiom_id,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Process a batch of predicates
     */
    protected function processPredicateBatch(Collection $predicates, array $results): array
    {
        foreach ($predicates as $predicate) {
            try {
                $this->createPredicateNode($predicate);
                $results['predicates_created']++;
                
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'type' => 'predicate_creation_error',
                    'predicate_name' => $predicate->name,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Create axiom node in Neo4j
     */
    protected function createAxiomNode(LogicalAxiom $axiom): string
    {
        $cypher = "
            MERGE (axiom:LogicalAxiom:Concept {axiom_id: \$axiom_id})
            SET axiom.name = \$name,
                axiom.chapter = \$chapter,
                axiom.chapter_title = \$chapter_title,
                axiom.section = \$section,
                axiom.page = \$page,
                axiom.axiom_number = \$axiom_number,
                axiom.title = \$title,
                axiom.fol = \$fol,
                axiom.english = \$english,
                axiom.complexity = \$complexity,
                axiom.pattern = \$pattern,
                axiom.predicates = \$predicates,
                axiom.variables = \$variables,
                axiom.quantifiers = \$quantifiers,
                axiom.defeasible = \$defeasible,
                axiom.reified = \$reified,
                axiom.domain = \$domain,
                axiom.confidence = \$confidence,
                axiom.implementation_status = \$implementation_status,
                axiom.updated_at = datetime()
            ON CREATE SET axiom.created_at = datetime()
            RETURN axiom.axiom_id as id
        ";
        
        $result = $this->neo4jService->run($cypher, $axiom->toNeo4jArray());
        
        return $result->first()->get('id');
    }
    
    /**
     * Create predicate node in Neo4j
     */
    protected function createPredicateNode(PsychologicalPredicate $predicate): string
    {
        $cypher = "
            MERGE (pred:Concept:PsychologicalPredicate {name: \$name})
            SET pred.predicate_type = \$predicate_type,
                pred.arity = \$arity,
                pred.description = \$description,
                pred.domain = \$domain,
                pred.usage_count = \$usage_count,
                pred.axiom_references = \$axiom_references,
                pred.activation_strength = \$activation_strength,
                pred.semantic_field = \$semantic_field,
                pred.argument_types = \$argument_types,
                pred.conceptual_role = \$conceptual_role,
                pred.examples = \$examples,
                pred.updated_at = datetime()
            ON CREATE SET pred.created_at = datetime()
            RETURN pred.name as name
        ";
        
        $result = $this->neo4jService->run($cypher, $predicate->toNeo4jArray());
        
        return $result->first()->get('name');
    }
    
    /**
     * Extract predicates from axiom batch
     */
    protected function extractPredicatesFromBatch(Collection $batch): Collection
    {
        $predicateMap = collect();
        
        foreach ($batch as $axiom) {
            foreach ($axiom->getPredicateList() as $predicateName) {
                if (!$predicateMap->has($predicateName)) {
                    $predicate = $this->createPredicateFromName($predicateName, $axiom);
                    $predicateMap->put($predicateName, $predicate);
                } else {
                    // Update existing predicate
                    $predicate = $predicateMap->get($predicateName);
                    $predicate->addAxiomReference($axiom->axiom_id);
                }
            }
        }
        
        return $predicateMap;
    }
    
    /**
     * Extract predicates from all axioms
     */
    protected function extractPredicatesFromAxioms(Collection $axioms): Collection
    {
        return $this->extractPredicatesFromBatch($axioms);
    }
    
    /**
     * Create PsychologicalPredicate from predicate name and context
     */
    protected function createPredicateFromName(string $name, LogicalAxiom $axiom): PsychologicalPredicate
    {
        $predicateType = PsychologicalPredicate::inferPredicateType($name, [
            'domain' => $axiom->domain,
            'variables' => $axiom->getVariableList()
        ]);
        
        $arity = PsychologicalPredicate::inferArity($name, [
            'variables' => $axiom->getVariableList(),
            'fol' => $axiom->fol
        ]);
        
        return new PsychologicalPredicate([
            'name' => strtoupper($name),
            'predicate_type' => $predicateType,
            'arity' => $arity,
            'description' => $this->generatePredicateDescription($name, $axiom),
            'domain' => $axiom->domain,
            'usage_count' => 1,
            'axiom_references' => [$axiom->axiom_id],
            'activation_strength' => 0.5, // Default, will be calculated later
            'semantic_field' => $this->inferSemanticField($name, $axiom),
            'conceptual_role' => $this->inferConceptualRole($predicateType),
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ]);
    }
    
    /**
     * Build relationships for a batch
     */
    protected function buildBatchRelationships(
        Collection $batch, 
        Collection $predicates, 
        array $results
    ): array {
        foreach ($batch as $axiom) {
            try {
                $relationshipsCreated = $this->buildAxiomRelationships($axiom, $predicates);
                $results['relationships_created'] += $relationshipsCreated;
                
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'type' => 'relationship_error',
                    'axiom_id' => $axiom->axiom_id,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Build relationships for a single axiom
     */
    protected function buildAxiomRelationships(LogicalAxiom $axiom, Collection $predicates): int
    {
        $relationshipsCreated = 0;
        
        // Link axiom to predicates it involves
        foreach ($axiom->getPredicateList() as $predicateName) {
            $cypher = "
                MATCH (axiom:LogicalAxiom {axiom_id: \$axiom_id})
                MATCH (pred:PsychologicalPredicate {name: \$predicate_name})
                MERGE (axiom)-[r:INVOLVES_PREDICATE]->(pred)
                ON CREATE SET r.created_at = datetime(),
                              r.strength = \$strength,
                              r.context = \$context
                RETURN COUNT(r) as count
            ";
            
            $result = $this->neo4jService->run($cypher, [
                'axiom_id' => $axiom->axiom_id,
                'predicate_name' => strtoupper($predicateName),
                'strength' => 0.8,
                'context' => $axiom->domain
            ]);
            
            $relationshipsCreated += $result->first()->get('count', 0);
        }
        
        // Build pattern-specific relationships
        $relationshipsCreated += $this->buildPatternSpecificRelationships($axiom);
        
        return $relationshipsCreated;
    }
    
    /**
     * Build pattern-specific relationships
     */
    protected function buildPatternSpecificRelationships(LogicalAxiom $axiom): int
    {
        $relationshipsCreated = 0;
        
        switch ($axiom->pattern) {
            case 'simple_inheritance':
                $relationshipsCreated += $this->buildInheritanceRelationships($axiom);
                break;
                
            case 'defeasible_rule':
                $relationshipsCreated += $this->buildDefeasibleRelationships($axiom);
                break;
                
            case 'goal_causation':
                $relationshipsCreated += $this->buildGoalCausationRelationships($axiom);
                break;
                
            case 'belief_logic':
                $relationshipsCreated += $this->buildBeliefLogicRelationships($axiom);
                break;
        }
        
        return $relationshipsCreated;
    }
    
    /**
     * Build inheritance relationships
     */
    protected function buildInheritanceRelationships(LogicalAxiom $axiom): int
    {
        // Implementation for inheritance patterns
        // This is a simplified version - real implementation would parse FOL more carefully
        $predicates = $axiom->getPredicateList();
        
        if (count($predicates) >= 2) {
            $cypher = "
                MATCH (pred1:PsychologicalPredicate {name: \$pred1})
                MATCH (pred2:PsychologicalPredicate {name: \$pred2})
                MERGE (pred1)-[r:INHERITS_FROM]->(pred2)
                ON CREATE SET r.created_at = datetime(),
                              r.axiom_source = \$axiom_id,
                              r.strength = \$strength
                RETURN COUNT(r) as count
            ";
            
            $result = $this->neo4jService->run($cypher, [
                'pred1' => strtoupper($predicates[0]),
                'pred2' => strtoupper($predicates[1]),
                'axiom_id' => $axiom->axiom_id,
                'strength' => $axiom->confidence
            ]);
            
            return $result->first()->get('count', 0);
        }
        
        return 0;
    }
    
    /**
     * Build defeasible relationships
     */
    protected function buildDefeasibleRelationships(LogicalAxiom $axiom): int
    {
        // Implementation for defeasible reasoning patterns
        return 0; // Placeholder
    }
    
    /**
     * Build goal causation relationships
     */
    protected function buildGoalCausationRelationships(LogicalAxiom $axiom): int
    {
        // Implementation for goal causation patterns
        return 0; // Placeholder
    }
    
    /**
     * Build belief logic relationships
     */
    protected function buildBeliefLogicRelationships(LogicalAxiom $axiom): int
    {
        // Implementation for belief logic patterns
        return 0; // Placeholder
    }
    
    /**
     * Process single axiom (for sequential processing)
     */
    protected function processSingleAxiom(
        LogicalAxiom $axiom, 
        Collection $predicates, 
        array $results
    ): array {
        // Create axiom node
        $this->createAxiomNode($axiom);
        $results['axioms_created']++;
        
        // Build relationships
        $relationshipsCreated = $this->buildAxiomRelationships($axiom, $predicates);
        $results['relationships_created'] += $relationshipsCreated;
        
        return $results;
    }
    
    /**
     * Generate predicate description
     */
    protected function generatePredicateDescription(string $name, LogicalAxiom $axiom): string
    {
        return "Predicate '{$name}' from {$axiom->domain} domain (axiom {$axiom->axiom_id})";
    }
    
    /**
     * Infer semantic field for predicate
     */
    protected function inferSemanticField(string $name, LogicalAxiom $axiom): ?string
    {
        $lowerName = strtolower($name);
        
        if (in_array($lowerName, ['happy', 'sad', 'angry', 'afraid'])) {
            return 'emotion';
        }
        
        if (in_array($lowerName, ['believe', 'know', 'think'])) {
            return 'cognition';
        }
        
        if (in_array($lowerName, ['see', 'hear', 'perceive'])) {
            return 'perception';
        }
        
        if (in_array($lowerName, ['move', 'go', 'come', 'do'])) {
            return 'action';
        }
        
        return $axiom->domain;
    }
    
    /**
     * Infer conceptual role based on predicate type
     */
    protected function inferConceptualRole(string $predicateType): string
    {
        return match($predicateType) {
            'mental_state' => 'Represents internal cognitive states',
            'action' => 'Represents physical or mental actions',
            'emotion' => 'Represents emotional states and reactions',
            'perception' => 'Represents perceptual processes',
            'relation' => 'Represents abstract relationships',
            default => 'General predicate role'
        };
    }
    
    /**
     * Get population statistics
     */
    public function getPopulationStatistics(): array
    {
        return [
            'service_version' => '1.0',
            'batch_processing_enabled' => $this->config['integration']['neo4j_batch_operations'] ?? true,
            'default_batch_size' => $this->config['axiom_processing']['batch_size'] ?? 50,
            'relationship_building_enabled' => true,
            'pattern_specific_relationships' => [
                'simple_inheritance', 'defeasible_rule', 
                'goal_causation', 'belief_logic'
            ]
        ];
    }
}