<?php

namespace App\Soul\Services\FOL;

use App\Soul\Models\PsychologicalPredicate;
use App\Soul\Contracts\Neo4jService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * PredicateGraphService - Manage predicate-specific graph operations
 *
 * This service handles specialized graph operations for psychological
 * predicates including clustering, semantic relationships, and activation patterns.
 */
class PredicateGraphService
{
    protected Neo4jService $neo4jService;
    
    public function __construct(Neo4jService $neo4jService)
    {
        $this->neo4jService = $neo4jService;
    }
    
    /**
     * Create semantic relationships between predicates
     */
    public function createSemanticRelationships(Collection $predicates): int
    {
        $relationshipsCreated = 0;
        
        Log::info("FOL PredicateGraph: Creating semantic relationships", [
            'predicate_count' => $predicates->count()
        ]);
        
        // Group predicates by semantic fields
        $semanticGroups = $predicates->groupBy('semantic_field');
        
        foreach ($semanticGroups as $field => $groupPredicates) {
            $relationshipsCreated += $this->createFieldRelationships($field, $groupPredicates);
        }
        
        // Create cross-domain relationships
        $relationshipsCreated += $this->createCrossDomainRelationships($predicates);
        
        return $relationshipsCreated;
    }
    
    /**
     * Create relationships within semantic field
     */
    protected function createFieldRelationships(string $field, Collection $predicates): int
    {
        if ($predicates->count() < 2) {
            return 0;
        }
        
        $relationshipsCreated = 0;
        
        // Create similarity relationships between predicates in same field
        $predicateArray = $predicates->toArray();
        
        for ($i = 0; $i < count($predicateArray); $i++) {
            for ($j = $i + 1; $j < count($predicateArray); $j++) {
                $pred1 = $predicateArray[$i];
                $pred2 = $predicateArray[$j];
                
                $similarity = $this->calculateSemanticSimilarity($pred1, $pred2);
                
                if ($similarity > 0.5) {
                    $relationshipsCreated += $this->createSimilarityRelationship($pred1, $pred2, $similarity);
                }
            }
        }
        
        return $relationshipsCreated;
    }
    
    /**
     * Calculate semantic similarity between predicates
     */
    protected function calculateSemanticSimilarity(PsychologicalPredicate $pred1, PsychologicalPredicate $pred2): float
    {
        $similarity = 0.0;
        
        // Same type increases similarity
        if ($pred1->predicate_type === $pred2->predicate_type) {
            $similarity += 0.4;
        }
        
        // Same arity increases similarity
        if ($pred1->arity === $pred2->arity) {
            $similarity += 0.2;
        }
        
        // Same domain increases similarity
        if ($pred1->domain === $pred2->domain) {
            $similarity += 0.2;
        }
        
        // Shared axiom references
        $sharedAxioms = array_intersect(
            $pred1->axiom_references ?? [],
            $pred2->axiom_references ?? []
        );
        
        if (!empty($sharedAxioms)) {
            $similarity += min(0.3, count($sharedAxioms) * 0.1);
        }
        
        // Name similarity (simple string matching)
        $nameSimilarity = $this->calculateNameSimilarity($pred1->name, $pred2->name);
        $similarity += $nameSimilarity * 0.1;
        
        return min(1.0, $similarity);
    }
    
    /**
     * Calculate name similarity between predicates
     */
    protected function calculateNameSimilarity(string $name1, string $name2): float
    {
        $name1 = strtolower($name1);
        $name2 = strtolower($name2);
        
        // Exact match
        if ($name1 === $name2) {
            return 1.0;
        }
        
        // Contains relationship
        if (str_contains($name1, $name2) || str_contains($name2, $name1)) {
            return 0.7;
        }
        
        // Levenshtein distance
        $maxLen = max(strlen($name1), strlen($name2));
        if ($maxLen === 0) return 1.0;
        
        $distance = levenshtein($name1, $name2);
        return 1.0 - ($distance / $maxLen);
    }
    
    /**
     * Create similarity relationship in Neo4j
     */
    protected function createSimilarityRelationship(
        PsychologicalPredicate $pred1, 
        PsychologicalPredicate $pred2, 
        float $similarity
    ): int {
        $cypher = "
            MATCH (p1:PsychologicalPredicate {name: \$name1})
            MATCH (p2:PsychologicalPredicate {name: \$name2})
            MERGE (p1)-[r:SEMANTICALLY_SIMILAR]->(p2)
            ON CREATE SET r.similarity = \$similarity,
                          r.created_at = datetime()
            RETURN COUNT(r) as count
        ";
        
        $result = $this->neo4jService->run($cypher, [
            'name1' => $pred1->name,
            'name2' => $pred2->name,
            'similarity' => $similarity
        ]);
        
        return $result->first()->get('count', 0);
    }
    
    /**
     * Create cross-domain relationships
     */
    protected function createCrossDomainRelationships(Collection $predicates): int
    {
        $relationshipsCreated = 0;
        
        // Group by domain
        $domainGroups = $predicates->groupBy('domain');
        
        // Create bridging relationships between psychology and other domains
        if ($domainGroups->has('psychology')) {
            $psychPredicates = $domainGroups->get('psychology');
            
            foreach ($domainGroups as $domain => $domainPredicates) {
                if ($domain !== 'psychology') {
                    $relationshipsCreated += $this->createBridgingRelationships(
                        $psychPredicates, 
                        $domainPredicates, 
                        $domain
                    );
                }
            }
        }
        
        return $relationshipsCreated;
    }
    
    /**
     * Create bridging relationships between domains
     */
    protected function createBridgingRelationships(
        Collection $psychPredicates,
        Collection $domainPredicates,
        string $targetDomain
    ): int {
        $relationshipsCreated = 0;
        
        // Find predicates that could bridge psychology with other domains
        $bridgePatterns = [
            'physics' => ['perceive', 'see', 'hear', 'move', 'cause'],
            'social' => ['communicate', 'interact', 'give', 'tell', 'show'],
            'temporal' => ['remember', 'expect', 'plan', 'before', 'after']
        ];
        
        if (!isset($bridgePatterns[$targetDomain])) {
            return 0;
        }
        
        $bridgeTerms = $bridgePatterns[$targetDomain];
        
        foreach ($psychPredicates as $psychPred) {
            foreach ($bridgeTerms as $bridgeTerm) {
                if (str_contains(strtolower($psychPred->name), $bridgeTerm)) {
                    // Find matching predicates in target domain
                    $matchingPredicates = $domainPredicates->filter(function($pred) use ($bridgeTerm) {
                        return str_contains(strtolower($pred->name), $bridgeTerm);
                    });
                    
                    foreach ($matchingPredicates as $matchPred) {
                        $relationshipsCreated += $this->createBridgeRelationship(
                            $psychPred, 
                            $matchPred, 
                            $bridgeTerm
                        );
                    }
                }
            }
        }
        
        return $relationshipsCreated;
    }
    
    /**
     * Create bridge relationship between domains
     */
    protected function createBridgeRelationship(
        PsychologicalPredicate $psychPred,
        PsychologicalPredicate $domainPred,
        string $bridgeType
    ): int {
        $cypher = "
            MATCH (p1:PsychologicalPredicate {name: \$psych_name})
            MATCH (p2:PsychologicalPredicate {name: \$domain_name})
            MERGE (p1)-[r:BRIDGES_DOMAIN]->(p2)
            ON CREATE SET r.bridge_type = \$bridge_type,
                          r.strength = 0.6,
                          r.created_at = datetime()
            RETURN COUNT(r) as count
        ";
        
        $result = $this->neo4jService->run($cypher, [
            'psych_name' => $psychPred->name,
            'domain_name' => $domainPred->name,
            'bridge_type' => $bridgeType
        ]);
        
        return $result->first()->get('count', 0);
    }
    
    /**
     * Create predicate clusters based on usage patterns
     */
    public function createPredicateClusters(Collection $predicates): array
    {
        $clusters = [];
        
        // Cluster by type
        $typeClusters = $predicates->groupBy('predicate_type');
        
        foreach ($typeClusters as $type => $typePredicates) {
            if ($typePredicates->count() >= 3) {
                $clusterId = $this->createClusterNode($type . '_cluster', [
                    'type' => 'predicate_type_cluster',
                    'predicate_type' => $type,
                    'member_count' => $typePredicates->count()
                ]);
                
                // Link predicates to cluster
                foreach ($typePredicates as $predicate) {
                    $this->linkPredicateToCluster($predicate, $clusterId);
                }
                
                $clusters[] = [
                    'id' => $clusterId,
                    'type' => $type,
                    'members' => $typePredicates->count()
                ];
            }
        }
        
        return $clusters;
    }
    
    /**
     * Create cluster node in Neo4j
     */
    protected function createClusterNode(string $clusterName, array $properties): string
    {
        $cypher = "
            CREATE (cluster:PredicateCluster {
                name: \$name,
                type: \$type,
                predicate_type: \$predicate_type,
                member_count: \$member_count,
                created_at: datetime()
            })
            RETURN cluster.name as name
        ";
        
        $result = $this->neo4jService->run($cypher, array_merge([
            'name' => $clusterName
        ], $properties));
        
        return $result->first()->get('name');
    }
    
    /**
     * Link predicate to cluster
     */
    protected function linkPredicateToCluster(PsychologicalPredicate $predicate, string $clusterId): void
    {
        $cypher = "
            MATCH (pred:PsychologicalPredicate {name: \$predicate_name})
            MATCH (cluster:PredicateCluster {name: \$cluster_name})
            MERGE (pred)-[r:MEMBER_OF]->(cluster)
            ON CREATE SET r.created_at = datetime()
        ";
        
        $this->neo4jService->run($cypher, [
            'predicate_name' => $predicate->name,
            'cluster_name' => $clusterId
        ]);
    }
    
    /**
     * Calculate activation strength for predicates
     */
    public function calculateActivationStrengths(Collection $predicates): Collection
    {
        return $predicates->map(function($predicate) {
            $strength = $predicate->getActivationStrength();
            
            // Update in database
            $this->updatePredicateActivationStrength($predicate, $strength);
            
            return $predicate;
        });
    }
    
    /**
     * Update predicate activation strength in Neo4j
     */
    protected function updatePredicateActivationStrength(PsychologicalPredicate $predicate, float $strength): void
    {
        $cypher = "
            MATCH (pred:PsychologicalPredicate {name: \$name})
            SET pred.activation_strength = \$strength,
                pred.updated_at = datetime()
        ";
        
        $this->neo4jService->run($cypher, [
            'name' => $predicate->name,
            'strength' => $strength
        ]);
    }
    
    /**
     * Get predicate graph statistics
     */
    public function getPredicateGraphStatistics(): array
    {
        $cypher = "
            MATCH (pred:PsychologicalPredicate)
            OPTIONAL MATCH (pred)-[r:SEMANTICALLY_SIMILAR]-()
            OPTIONAL MATCH (pred)-[b:BRIDGES_DOMAIN]-()
            OPTIONAL MATCH (pred)-[c:MEMBER_OF]->(cluster:PredicateCluster)
            RETURN 
                COUNT(DISTINCT pred) as total_predicates,
                COUNT(DISTINCT r) as similarity_relationships,
                COUNT(DISTINCT b) as bridge_relationships,
                COUNT(DISTINCT cluster) as clusters,
                AVG(pred.activation_strength) as avg_activation_strength
        ";
        
        $result = $this->neo4jService->run($cypher);
        $record = $result->first();
        
        return [
            'total_predicates' => $record->get('total_predicates', 0),
            'similarity_relationships' => $record->get('similarity_relationships', 0),
            'bridge_relationships' => $record->get('bridge_relationships', 0),
            'clusters' => $record->get('clusters', 0),
            'avg_activation_strength' => $record->get('avg_activation_strength', 0.0)
        ];
    }
}