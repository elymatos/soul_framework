<?php

namespace App\Soul\Services;

use App\Soul\Contracts\GraphServiceInterface;
use App\Soul\Contracts\Neo4jService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * GraphService - Main graph operations coordinator for Society of Mind architecture
 *
 * This service acts as the central coordinator for all graph-based operations,
 * implementing Minsky's Society of Mind principles with spreading activation,
 * K-line learning, and procedural agent management.
 */
class GraphService implements GraphServiceInterface
{
    protected Neo4jService $neo4jService;
    protected array $config;

    public function __construct(Neo4jService $neo4jService)
    {
        $this->neo4jService = $neo4jService;
        $this->config = Config::get('soul', []);
    }

    /**
     * Run spreading activation from initial concepts
     */
    public function runSpreadingActivation(array $initialConcepts, array $options = []): array
    {
        $mergedOptions = array_merge([
            'max_depth' => $this->config['graph']['spreading_activation']['max_depth'] ?? 3,
            'activation_threshold' => $this->config['graph']['spreading_activation']['threshold'] ?? 0.1,
            'include_procedural_agents' => $options['include_procedural_agents'] ?? true
        ], $options);

        Log::info("Starting spreading activation", [
            'initial_concepts' => $initialConcepts,
            'options' => $mergedOptions
        ]);

        try {
            $result = $this->neo4jService->runSpreadingActivation($initialConcepts, $mergedOptions);
            
            Log::info("Spreading activation completed", [
                'total_nodes' => $result['total_nodes'] ?? 0,
                'initial_concepts_count' => count($initialConcepts)
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error("Spreading activation failed", [
                'initial_concepts' => $initialConcepts,
                'error' => $e->getMessage()
            ]);

            return [
                'activated_nodes' => [],
                'initial_concepts' => $initialConcepts,
                'total_nodes' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Find procedural agents by code reference pattern
     */
    public function findProceduralAgents(string $pattern): array
    {
        Log::debug("Finding procedural agents", ['pattern' => $pattern]);
        
        try {
            // For exact matches
            $agent = $this->neo4jService->findProceduralAgent($pattern);
            if ($agent) {
                return [$agent];
            }
            
            // If no exact match, could implement pattern matching here
            return [];

        } catch (\Exception $e) {
            Log::error("Failed to find procedural agents", [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Create concept node in the graph
     */
    public function createConcept(array $conceptData): string
    {
        Log::info("Creating concept node", [
            'name' => $conceptData['name'] ?? 'unnamed',
            'type' => $conceptData['type'] ?? 'general'
        ]);

        try {
            $nodeId = $this->neo4jService->createConceptNode($conceptData);
            
            Log::info("Concept node created", [
                'node_id' => $nodeId,
                'name' => $conceptData['name'] ?? 'unnamed'
            ]);
            
            return $nodeId;

        } catch (\Exception $e) {
            Log::error("Failed to create concept node", [
                'concept_data' => $conceptData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create procedural agent in the graph
     */
    public function createProceduralAgent(array $agentData): string
    {
        Log::info("Creating procedural agent", [
            'name' => $agentData['name'] ?? 'unnamed',
            'code_reference' => $agentData['code_reference'] ?? 'unknown'
        ]);

        try {
            $nodeId = $this->neo4jService->createProceduralAgent($agentData);
            
            Log::info("Procedural agent created", [
                'node_id' => $nodeId,
                'name' => $agentData['name'] ?? 'unnamed'
            ]);
            
            return $nodeId;

        } catch (\Exception $e) {
            Log::error("Failed to create procedural agent", [
                'agent_data' => $agentData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Record successful activation path as K-line for learning
     */
    public function recordKLine(array $activationPath, string $context): string
    {
        Log::info("Recording K-line", [
            'context' => $context,
            'activated_nodes_count' => count($activationPath['activation_pattern']['activated_nodes'] ?? [])
        ]);

        try {
            $klineId = $this->neo4jService->recordKLine($activationPath, $context);
            
            Log::info("K-line recorded", [
                'kline_id' => $klineId,
                'context' => $context
            ]);
            
            return $klineId;

        } catch (\Exception $e) {
            Log::error("Failed to record K-line", [
                'context' => $context,
                'error' => $e->getMessage()
            ]);
            
            // Return a fallback ID even if recording failed
            return 'kline_' . uniqid() . '_failed';
        }
    }

    /**
     * Strengthen K-line based on successful reuse
     */
    public function strengthenKLine(string $klineId): bool
    {
        $minUsageForStrengthening = $this->config['graph']['klines']['min_usage_for_strengthening'] ?? 3;
        
        Log::debug("Strengthening K-line", [
            'kline_id' => $klineId,
            'min_usage_threshold' => $minUsageForStrengthening
        ]);

        try {
            $this->neo4jService->strengthenKLine($klineId);
            
            Log::debug("K-line strengthened", ['kline_id' => $klineId]);
            
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to strengthen K-line", [
                'kline_id' => $klineId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get graph statistics for monitoring
     */
    public function getGraphStatistics(): array
    {
        Log::debug("Retrieving graph statistics");

        try {
            $stats = $this->neo4jService->getDatabaseStatistics();
            
            Log::debug("Graph statistics retrieved", [
                'frame_instances' => $stats['frame_instances'] ?? 0,
                'relationships' => $stats['relationships'] ?? 0
            ]);
            
            return array_merge($stats, [
                'timestamp' => now()->toISOString(),
                'service_version' => '1.0'
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to retrieve graph statistics", [
                'error' => $e->getMessage()
            ]);
            
            return [
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
                'service_version' => '1.0'
            ];
        }
    }

    /**
     * Create relationship between nodes
     */
    public function createRelationship(string $fromNodeId, string $toNodeId, string $relationshipType, array $properties = []): bool
    {
        Log::debug("Creating relationship", [
            'from' => $fromNodeId,
            'to' => $toNodeId,
            'type' => $relationshipType
        ]);

        try {
            // Use Neo4j service instance relationship method
            $success = $this->neo4jService->createInstanceRelationship(
                $fromNodeId,
                $toNodeId,
                $relationshipType,
                $properties
            );
            
            if ($success) {
                Log::debug("Relationship created successfully", [
                    'from' => $fromNodeId,
                    'to' => $toNodeId,
                    'type' => $relationshipType
                ]);
            }
            
            return $success;

        } catch (\Exception $e) {
            Log::error("Failed to create relationship", [
                'from' => $fromNodeId,
                'to' => $toNodeId,
                'type' => $relationshipType,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Query nodes by criteria
     */
    public function queryNodes(array $criteria): Collection
    {
        Log::debug("Querying nodes", ['criteria' => $criteria]);

        try {
            // Use Neo4j service frame instances query method
            $results = $this->neo4jService->queryFrameInstances($criteria);
            
            Log::debug("Node query completed", [
                'criteria' => $criteria,
                'results_count' => $results->count()
            ]);
            
            return $results;

        } catch (\Exception $e) {
            Log::error("Failed to query nodes", [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    /**
     * Get node relationships
     */
    public function getNodeRelationships(string $nodeId): Collection
    {
        Log::debug("Getting node relationships", ['node_id' => $nodeId]);

        try {
            $relationships = $this->neo4jService->getInstanceRelationships($nodeId);
            
            Log::debug("Node relationships retrieved", [
                'node_id' => $nodeId,
                'relationships_count' => $relationships->count()
            ]);
            
            return $relationships;

        } catch (\Exception $e) {
            Log::error("Failed to get node relationships", [
                'node_id' => $nodeId,
                'error' => $e->getMessage()
            ]);
            return new Collection();
        }
    }

    /**
     * Analyze spreading activation results for insights
     */
    public function analyzeActivationResults(array $activationResults): array
    {
        $activatedNodes = $activationResults['activated_nodes'] ?? [];
        $totalNodes = count($activatedNodes);
        
        if ($totalNodes === 0) {
            return [
                'analysis' => 'no_activation',
                'insights' => ['No nodes were activated'],
                'recommendations' => ['Check initial concepts and graph connectivity']
            ];
        }

        // Analyze activation patterns
        $nodesByType = [];
        $activationStrengths = [];
        
        foreach ($activatedNodes as $node) {
            $type = $node['type'] ?? 'unknown';
            $strength = $node['activation_strength'] ?? 0.0;
            
            if (!isset($nodesByType[$type])) {
                $nodesByType[$type] = 0;
            }
            $nodesByType[$type]++;
            $activationStrengths[] = $strength;
        }

        $avgActivation = array_sum($activationStrengths) / count($activationStrengths);
        $maxActivation = max($activationStrengths);
        $minActivation = min($activationStrengths);

        return [
            'analysis' => 'successful_activation',
            'metrics' => [
                'total_nodes' => $totalNodes,
                'avg_activation_strength' => round($avgActivation, 3),
                'max_activation_strength' => round($maxActivation, 3),
                'min_activation_strength' => round($minActivation, 3),
                'nodes_by_type' => $nodesByType
            ],
            'insights' => [
                "Activated {$totalNodes} nodes across " . count($nodesByType) . " types",
                "Average activation strength: " . round($avgActivation, 3),
                "Most active type: " . array_search(max($nodesByType), $nodesByType)
            ],
            'recommendations' => $this->generateActivationRecommendations($nodesByType, $avgActivation)
        ];
    }

    /**
     * Generate recommendations based on activation analysis
     */
    protected function generateActivationRecommendations(array $nodesByType, float $avgActivation): array
    {
        $recommendations = [];
        
        if ($avgActivation < 0.3) {
            $recommendations[] = "Consider increasing activation threshold or improving graph connectivity";
        }
        
        if (isset($nodesByType['PROCEDURAL_AGENT']) && $nodesByType['PROCEDURAL_AGENT'] > 0) {
            $recommendations[] = "Procedural agents were activated - consider executing relevant methods";
        }
        
        if (count($nodesByType) === 1) {
            $recommendations[] = "Activation limited to single node type - consider expanding graph diversity";
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "Activation pattern looks healthy - ready for processing";
        }
        
        return $recommendations;
    }
}