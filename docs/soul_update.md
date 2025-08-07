SOUL Framework Refactoring Plan: Society of Mind Implementation
Overview
This document provides detailed implementation instructions for refactoring the SOUL framework to implement Minsky's Society of Mind principles with a microservice-oriented agent architecture, graph-centric knowledge representation, and YAML-based initialization.
Implementation Phases
Phase 1: Create Agent Service Interfaces
1.1 Define Core Service Contracts
File: app/Soul/Contracts/AgentServiceInterface.php
php<?php

namespace App\Soul\Contracts;

interface AgentServiceInterface
{
    /**
     * Execute an agent method with given parameters
     */
    public function executeAgent(string $method, array $parameters = []): mixed;
    
    /**
     * Get available agent methods for this service
     */
    public function getAvailableAgents(): array;
    
    /**
     * Validate parameters for a specific agent method
     */
    public function validateParameters(string $method, array $parameters): bool;
}
File: app/Soul/Contracts/GraphServiceInterface.php
php<?php

namespace App\Soul\Contracts;

use Illuminate\Support\Collection;

interface GraphServiceInterface
{
    /**
     * Run spreading activation from initial concepts
     */
    public function runSpreadingActivation(array $initialConcepts, array $options = []): array;
    
    /**
     * Find procedural agent nodes by code reference
     */
    public function findProceduralAgent(string $codeReference): ?array;
    
    /**
     * Record successful activation path as K-line
     */
    public function recordKLine(array $activationPath, string $context): string;
    
    /**
     * Create or update concept node
     */
    public function createConcept(array $conceptData): string;
    
    /**
     * Create relationship between concepts
     */
    public function createRelationship(string $fromId, string $toId, string $type, array $properties = []): bool;
}
1.2 Define Specialized Service Interfaces
File: app/Soul/Contracts/ImageSchemaServiceInterface.php
php<?php

namespace App\Soul\Contracts;

interface ImageSchemaServiceInterface extends AgentServiceInterface
{
    public function createPath(array $parameters): array;
    public function calculateDistance(array $parameters): float;
    public function checkContainment(array $parameters): bool;
    public function applyForce(array $parameters): array;
    public function defineRegion(array $parameters): array;
}
File: app/Soul/Contracts/FrameServiceInterface.php
php<?php

namespace App\Soul\Contracts;

interface FrameServiceInterface extends AgentServiceInterface
{
    public function instantiateFrame(array $parameters): array;
    public function addFrameElement(array $parameters): array;
    public function resolveInheritance(array $parameters): array;
    public function matchFrame(array $parameters): float;
    public function adaptFrame(array $parameters): array;
}
File: app/Soul/Contracts/ConceptualSpaceServiceInterface.php
php<?php

namespace App\Soul\Contracts;

interface ConceptualSpaceServiceInterface extends AgentServiceInterface
{
    public function placeConceptInRegion(array $parameters): array;
    public function findClosestNeighbor(array $parameters): ?array;
    public function projectSpace(array $parameters): array;
    public function calculateSimilarity(array $parameters): float;
}
File: app/Soul/Contracts/CognitiveProcessServiceInterface.php
php<?php

namespace App\Soul\Contracts;

interface CognitiveProcessServiceInterface extends AgentServiceInterface
{
    public function runSpreadingActivation(array $parameters): array;
    public function performBlending(array $parameters): array;
    public function executeInference(array $parameters): array;
    public function manageAttention(array $parameters): array;
}
File: app/Soul/Contracts/LanguageServiceInterface.php
php<?php

namespace App\Soul\Contracts;

interface LanguageServiceInterface extends AgentServiceInterface
{
    public function parseSentence(array $parameters): array;
    public function conceptualizeWord(array $parameters): array;
    public function generateSentenceFromFrame(array $parameters): string;
    public function extractSemanticRoles(array $parameters): array;
}
Phase 2: Refactor MindService as Orchestrator/Router
2.1 Remove Current MindService and Create New Orchestrator
File: app/Soul/Services/MindService.php (COMPLETE REPLACEMENT)
php<?php

namespace App\Soul\Services;

use App\Soul\Contracts\GraphServiceInterface;
use App\Soul\Contracts\AgentServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Container\Container;

/**
 * MindService - Central Orchestrator for Society of Mind
 * 
 * Responsibilities:
 * 1. Receive external requests
 * 2. Use spreading activation to find relevant agents
 * 3. Route agent calls to specialized services
 * 4. Record K-lines (successful activation paths)
 * 5. Coordinate cognitive processing pipeline
 */
class MindService
{
    protected GraphServiceInterface $graphService;
    protected Container $container;
    protected Collection $agentServices;
    protected array $processingSession;
    protected array $statistics;
    
    public function __construct(
        GraphServiceInterface $graphService,
        Container $container
    ) {
        $this->graphService = $graphService;
        $this->container = $container;
        $this->agentServices = new Collection();
        $this->statistics = [
            'requests_processed' => 0,
            'agents_activated' => 0,
            'klines_created' => 0,
            'processing_sessions' => 0
        ];
    }
    
    /**
     * Main entry point for processing requests
     */
    public function processRequest(string $requestType, array $requestData): array
    {
        $sessionId = $this->startProcessingSession($requestType, $requestData);
        
        try {
            // Step 1: Initial conceptualization using spreading activation
            $initialActivation = $this->performInitialActivation($requestData);
            
            // Step 2: Identify relevant procedural agents
            $relevantAgents = $this->identifyRelevantAgents($initialActivation);
            
            // Step 3: Execute agent sequence
            $executionResults = $this->executeAgentSequence($relevantAgents, $requestData);
            
            // Step 4: Record K-line for successful path
            $klineId = $this->recordSuccessfulPath($initialActivation, $relevantAgents, $executionResults);
            
            // Step 5: Generate response
            $response = $this->generateResponse($executionResults, $sessionId);
            
            $this->endProcessingSession($sessionId, true);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error("MindService processing error", [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->endProcessingSession($sessionId, false);
            throw $e;
        }
    }
    
    /**
     * Perform initial spreading activation
     */
    protected function performInitialActivation(array $requestData): array
    {
        $initialConcepts = $this->extractInitialConcepts($requestData);
        
        return $this->graphService->runSpreadingActivation($initialConcepts, [
            'max_depth' => 3,
            'activation_threshold' => 0.1,
            'include_procedural_agents' => true
        ]);
    }
    
    /**
     * Identify relevant procedural agents from activation results
     */
    protected function identifyRelevantAgents(array $activationResults): array
    {
        $agents = [];
        
        foreach ($activationResults['activated_nodes'] as $node) {
            if ($node['type'] === 'PROCEDURAL_AGENT') {
                $agents[] = [
                    'node_id' => $node['id'],
                    'code_reference' => $node['properties']['code_reference'],
                    'activation_strength' => $node['activation_strength'],
                    'priority' => $node['properties']['priority'] ?? 1
                ];
            }
        }
        
        // Sort by activation strength and priority
        usort($agents, function($a, $b) {
            return ($b['activation_strength'] * $b['priority']) <=> ($a['activation_strength'] * $a['priority']);
        });
        
        return $agents;
    }
    
    /**
     * Execute sequence of agents
     */
    protected function executeAgentSequence(array $agents, array $context): array
    {
        $results = [];
        $currentContext = $context;
        
        foreach ($agents as $agent) {
            try {
                $result = $this->executeAgent($agent, $currentContext);
                $results[] = [
                    'agent' => $agent,
                    'result' => $result,
                    'timestamp' => now()
                ];
                
                // Update context with result
                $currentContext = array_merge($currentContext, $result);
                
                $this->statistics['agents_activated']++;
                
            } catch (\Exception $e) {
                Log::warning("Agent execution failed", [
                    'agent' => $agent['code_reference'],
                    'error' => $e->getMessage()
                ]);
                
                // Continue with other agents (graceful degradation)
                continue;
            }
        }
        
        return $results;
    }
    
    /**
     * Execute individual agent
     */
    protected function executeAgent(array $agent, array $parameters): array
    {
        $codeRef = $agent['code_reference'];
        list($serviceName, $methodName) = $this->parseCodeReference($codeRef);
        
        $service = $this->getAgentService($serviceName);
        
        if (!$service) {
            throw new \Exception("Agent service not found: {$serviceName}");
        }
        
        return $service->executeAgent($methodName, $parameters);
    }
    
    /**
     * Parse code reference into service and method
     */
    protected function parseCodeReference(string $codeRef): array
    {
        if (strpos($codeRef, '::') === false) {
            throw new \Exception("Invalid code reference format: {$codeRef}");
        }
        
        return explode('::', $codeRef, 2);
    }
    
    /**
     * Get or create agent service instance
     */
    protected function getAgentService(string $serviceName): ?AgentServiceInterface
    {
        if ($this->agentServices->has($serviceName)) {
            return $this->agentServices->get($serviceName);
        }
        
        $serviceClass = "App\\Soul\\Services\\{$serviceName}";
        
        if (!class_exists($serviceClass)) {
            return null;
        }
        
        $service = $this->container->make($serviceClass);
        $this->agentServices->put($serviceName, $service);
        
        return $service;
    }
    
    /**
     * Extract initial concepts from request data
     */
    protected function extractInitialConcepts(array $requestData): array
    {
        $concepts = [];
        
        // Extract from text if present
        if (isset($requestData['text'])) {
            // Simple keyword extraction - can be enhanced with NLP
            $words = str_word_count($requestData['text'], 1);
            foreach ($words as $word) {
                $concepts[] = strtoupper($word);
            }
        }
        
        // Extract from explicit concept list
        if (isset($requestData['concepts'])) {
            $concepts = array_merge($concepts, $requestData['concepts']);
        }
        
        // Extract from frame references
        if (isset($requestData['frames'])) {
            $concepts = array_merge($concepts, $requestData['frames']);
        }
        
        return array_unique($concepts);
    }
    
    /**
     * Record successful activation path as K-line
     */
    protected function recordSuccessfulPath(array $activation, array $agents, array $results): string
    {
        $klineData = [
            'activation_pattern' => $activation,
            'agent_sequence' => array_column($agents, 'code_reference'),
            'success_metrics' => $this->calculateSuccessMetrics($results),
            'context_type' => $this->processingSession['request_type'] ?? 'unknown'
        ];
        
        $klineId = $this->graphService->recordKLine($klineData, $this->processingSession['id']);
        $this->statistics['klines_created']++;
        
        return $klineId;
    }
    
    /**
     * Calculate success metrics for K-line strength
     */
    protected function calculateSuccessMetrics(array $results): array
    {
        $totalResults = count($results);
        $successfulResults = count(array_filter($results, function($r) {
            return isset($r['result']['status']) && $r['result']['status'] === 'success';
        }));
        
        return [
            'success_rate' => $totalResults > 0 ? $successfulResults / $totalResults : 0,
            'execution_time' => $this->processingSession['execution_time'] ?? 0,
            'agent_count' => $totalResults
        ];
    }
    
    /**
     * Generate final response
     */
    protected function generateResponse(array $executionResults, string $sessionId): array
    {
        return [
            'session_id' => $sessionId,
            'status' => 'completed',
            'results' => $executionResults,
            'statistics' => [
                'agents_executed' => count($executionResults),
                'processing_time' => now()->diffInMilliseconds($this->processingSession['started_at']),
                'activation_nodes' => $this->processingSession['activation_count'] ?? 0
            ]
        ];
    }
    
    /**
     * Start processing session
     */
    protected function startProcessingSession(string $type, array $data): string
    {
        $sessionId = 'session_' . uniqid() . '_' . time();
        
        $this->processingSession = [
            'id' => $sessionId,
            'request_type' => $type,
            'started_at' => now(),
            'status' => 'active'
        ];
        
        $this->statistics['processing_sessions']++;
        
        return $sessionId;
    }
    
    /**
     * End processing session
     */
    protected function endProcessingSession(string $sessionId, bool $success): void
    {
        $this->processingSession['ended_at'] = now();
        $this->processingSession['status'] = $success ? 'completed' : 'failed';
        $this->processingSession['execution_time'] = now()->diffInMilliseconds($this->processingSession['started_at']);
        
        if ($success) {
            $this->statistics['requests_processed']++;
        }
    }
    
    /**
     * Get processing statistics
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }
    
    /**
     * Get current processing session
     */
    public function getCurrentSession(): array
    {
        return $this->processingSession;
    }
}
Phase 3: Implement Graph Schema Updates
3.1 Create Graph Service Implementation
File: app/Soul/Services/GraphService.php
php<?php

namespace App\Soul\Services;

use App\Soul\Contracts\GraphServiceInterface;
use Laudis\Neo4j\Contracts\ClientInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GraphService implements GraphServiceInterface
{
    protected ClientInterface $neo4j;
    
    public function __construct(ClientInterface $neo4j)
    {
        $this->neo4j = $neo4j;
    }
    
    public function runSpreadingActivation(array $initialConcepts, array $options = []): array
    {
        $maxDepth = $options['max_depth'] ?? 3;
        $threshold = $options['activation_threshold'] ?? 0.1;
        $includeProcAgents = $options['include_procedural_agents'] ?? false;
        
        $query = "
            WITH \$concepts AS initial_concepts
            UNWIND initial_concepts AS concept_name
            MATCH (start:Concept {name: concept_name})
            
            CALL apoc.path.subgraphAll(start, {
                maxLevel: \$maxDepth,
                relationshipFilter: 'IS_A|PART_OF|CAUSES|SIMILAR_TO|HAS_FE',
                labelFilter: '" . ($includeProcAgents ? 'Concept|PROCEDURAL_AGENT' : 'Concept') . "'
            })
            YIELD nodes, relationships
            
            UNWIND nodes AS node
            WITH node, 
                 CASE 
                     WHEN node IN [start] THEN 1.0
                     ELSE 1.0 / (apoc.node.degree(node) + 1.0)
                 END AS activation_strength
            WHERE activation_strength >= \$threshold
            
            RETURN collect({
                id: id(node),
                name: node.name,
                type: labels(node)[0],
                properties: properties(node),
                activation_strength: activation_strength
            }) AS activated_nodes
        ";
        
        $result = $this->neo4j->run($query, [
            'concepts' => $initialConcepts,
            'maxDepth' => $maxDepth,
            'threshold' => $threshold
        ]);
        
        $activatedNodes = $result->first()->get('activated_nodes');
        
        return [
            'activated_nodes' => $activatedNodes,
            'initial_concepts' => $initialConcepts,
            'total_nodes' => count($activatedNodes),
            'max_depth' => $maxDepth
        ];
    }
    
    public function findProceduralAgent(string $codeReference): ?array
    {
        $query = "
            MATCH (agent:PROCEDURAL_AGENT {code_reference: \$codeRef})
            RETURN {
                id: id(agent),
                name: agent.name,
                code_reference: agent.code_reference,
                properties: properties(agent)
            } AS agent
        ";
        
        $result = $this->neo4j->run($query, ['codeRef' => $codeReference]);
        
        return $result->first()?->get('agent');
    }
    
    public function recordKLine(array $activationPath, string $context): string
    {
        $klineId = 'kline_' . uniqid();
        
        $query = "
            CREATE (kline:KLine {
                id: \$klineId,
                context: \$context,
                activation_pattern: \$activationPattern,
                agent_sequence: \$agentSequence,
                success_rate: \$successRate,
                usage_count: 1,
                created_at: datetime(),
                last_used: datetime()
            })
            
            WITH kline
            UNWIND \$nodeIds AS nodeId
            MATCH (node) WHERE id(node) = nodeId
            CREATE (kline)-[:ACTIVATES {strength: 1.0}]->(node)
            
            RETURN kline.id AS id
        ";
        
        $nodeIds = array_column($activationPath['activation_pattern']['activated_nodes'] ?? [], 'id');
        
        $result = $this->neo4j->run($query, [
            'klineId' => $klineId,
            'context' => $context,
            'activationPattern' => json_encode($activationPath['activation_pattern']),
            'agentSequence' => $activationPath['agent_sequence'],
            'successRate' => $activationPath['success_metrics']['success_rate'],
            'nodeIds' => $nodeIds
        ]);
        
        return $result->first()->get('id');
    }
    
    public function createConcept(array $conceptData): string
    {
        $labels = $conceptData['labels'] ?? ['Concept'];
        $properties = $conceptData['properties'] ?? [];
        $properties['created_at'] = now()->toISOString();
        
        $labelStr = ':' . implode(':', $labels);
        $query = "
            CREATE (c{$labelStr} \$properties)
            RETURN id(c) AS id, c.name AS name
        ";
        
        $result = $this->neo4j->run($query, ['properties' => $properties]);
        
        return $result->first()->get('id');
    }
    
    public function createRelationship(string $fromId, string $toId, string $type, array $properties = []): bool
    {
        $properties['created_at'] = now()->toISOString();
        
        $query = "
            MATCH (from), (to)
            WHERE id(from) = \$fromId AND id(to) = \$toId
            CREATE (from)-[r:${type} \$properties]->(to)
            RETURN id(r) AS relationship_id
        ";
        
        try {
            $result = $this->neo4j->run($query, [
                'fromId' => (int)$fromId,
                'toId' => (int)$toId,
                'properties' => $properties
            ]);
            
            return $result->first() !== null;
        } catch (\Exception $e) {
            Log::error("Failed to create relationship", [
                'from' => $fromId,
                'to' => $toId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Create procedural agent node
     */
    public function createProceduralAgent(array $agentData): string
    {
        $query = "
            CREATE (agent:PROCEDURAL_AGENT:Concept {
                name: \$name,
                code_reference: \$codeReference,
                description: \$description,
                priority: \$priority,
                created_at: datetime()
            })
            RETURN id(agent) AS id
        ";
        
        $result = $this->neo4j->run($query, [
            'name' => $agentData['name'],
            'codeReference' => $agentData['code_reference'],
            'description' => $agentData['description'] ?? '',
            'priority' => $agentData['priority'] ?? 1
        ]);
        
        return $result->first()->get('id');
    }
    
    /**
     * Strengthen K-line based on usage
     */
    public function strengthenKLine(string $klineId): void
    {
        $query = "
            MATCH (kline:KLine {id: \$klineId})
            SET kline.usage_count = kline.usage_count + 1,
                kline.last_used = datetime()
            
            MATCH (kline)-[r:ACTIVATES]->(node)
            SET r.strength = r.strength * 1.1
        ";
        
        $this->neo4j->run($query, ['klineId' => $klineId]);
    }
}
3.2 Update Neo4j Schema
File: database/migrations/soul/2025_001_create_procedural_agents_schema.cypher
cypher// Create constraints and indexes for PROCEDURAL_AGENT nodes
CREATE CONSTRAINT procedural_agent_code_ref IF NOT EXISTS FOR (p:PROCEDURAL_AGENT) REQUIRE p.code_reference IS UNIQUE;
CREATE CONSTRAINT procedural_agent_name IF NOT EXISTS FOR (p:PROCEDURAL_AGENT) REQUIRE p.name IS UNIQUE;

// Create indexes for performance
CREATE INDEX procedural_agent_priority IF NOT EXISTS FOR (p:PROCEDURAL_AGENT) ON p.priority;
CREATE INDEX concept_name IF NOT EXISTS FOR (c:Concept) ON c.name;

// Create constraints for K-lines
CREATE CONSTRAINT kline_id IF NOT EXISTS FOR (k:KLine) REQUIRE k.id IS UNIQUE;
CREATE INDEX kline_context IF NOT EXISTS FOR (k:KLine) ON k.context;
CREATE INDEX kline_usage IF NOT EXISTS FOR (k:KLine) ON k.usage_count;

// Create sample PROCEDURAL_AGENT nodes
CREATE (agent1:PROCEDURAL_AGENT:Concept {
    name: "FRAME_INSTANTIATOR",
    code_reference: "FrameService::instantiateFrame",
    description: "Creates new frame instances with filled frame elements",
    priority: 1,
    created_at: datetime()
});

CREATE (agent2:PROCEDURAL_AGENT:Concept {
    name: "SPREADING_ACTIVATOR", 
    code_reference: "CognitiveProcessService::runSpreadingActivation",
    description: "Performs spreading activation across concept network",
    priority: 2,
    created_at: datetime()
});

CREATE (agent3:PROCEDURAL_AGENT:Concept {
    name: "SENTENCE_PARSER",
    code_reference: "LanguageService::parseSentence", 
    description: "Parses natural language sentences into semantic structures",
    priority: 1,
    created_at: datetime()
});

// Connect agents to relevant concepts
MATCH (agent:PROCEDURAL_AGENT {name: "FRAME_INSTANTIATOR"}), (concept:Concept {name: "FRAME"})
CREATE (agent)-[:OPERATES_ON]->(concept);

MATCH (agent:PROCEDURAL_AGENT {name: "SENTENCE_PARSER"}), (concept:Concept {name: "LANGUAGE"})
CREATE (agent)-[:OPERATES_ON]->(concept);
Phase 4: Build YAML Loader System
4.1 Create YAML Schema Validator
File: app/Soul/Services/YamlLoaderService.php
php<?php

namespace App\Soul\Services;

use App\Soul\Contracts\GraphServiceInterface;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class YamlLoaderService
{
    protected GraphServiceInterface $graphService;
    protected array $loadedFiles = [];
    protected array $nodeCache = [];
    
    public function __construct(GraphServiceInterface $graphService)
    {
        $this->graphService = $graphService;
    }
    
    /**
     * Load all YAML files from directory
     */
    public function loadFromDirectory(string $directory): array
    {
        $results = [];
        $yamlFiles = File::glob($directory . '/**/*.yaml');
        
        // First pass: create all nodes
        foreach ($yamlFiles as $file) {
            try {
                $result = $this->loadFile($file, false); // false = don't create relationships yet
                $results[] = $result;
            } catch (\Exception $e) {
                Log::error("Failed to load YAML file", [
                    'file' => $file,
                    'error' => $e->getMessage()
                ]);
                $results[] = ['file' => $file, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }
        
        // Second pass: create all relationships
        foreach ($yamlFiles as $file) {
            try {
                $this->createRelationshipsFromFile($file);
            } catch (\Exception $e) {
                Log::error("Failed to create relationships from YAML file", [
                    'file' => $file,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $results;
    }
    
    /**
     * Load single YAML file
     */
    public function loadFile(string $filepath, bool $createRelationships = true): array
    {
        if (!File::exists($filepath)) {
            throw new \Exception("YAML file not found: {$filepath}");
        }
        
        $yamlContent = File::get($filepath);
        $data = Yaml::parse($yamlContent);
        
        $this->validateYamlStructure($data, $filepath);
        
        // Create main node
        $mainNodeId = $this->createMainNode($data);
        $this->nodeCache[$data['name']] = $mainNodeId;
        
        $relationshipCount = 0;
        
        // Create related nodes and relationships
        if (isset($data['relations']) && $createRelationships) {
            foreach ($data['relations'] as $relation) {
                $this->createRelationFromYaml($mainNodeId, $relation);
                $relationshipCount++;
            }
        }
        
        $this->loadedFiles[] = $filepath;
        
        return [
            'file' => $filepath,
            'status' => 'success',
            'main_node_id' => $mainNodeId,
            'relationships_created' => $relationshipCount
        ];
    }
    
    /**
     * Validate YAML structure
     */
    protected function validateYamlStructure(array $data, string $filepath): void
    {
        $requiredFields = ['type', 'name'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Missing required field '{$field}' in {$filepath}");
            }
        }
        
        $validTypes = ['Frame', 'FrameElement', 'ImageSchema', 'ProceduralAgent', 'Concept'];
        
        if (!in_array($data['type'], $validTypes)) {
            throw new \Exception("Invalid type '{$data['type']}' in {$filepath}. Must be one of: " . implode(', ', $validTypes));
        }
    }
    
    /**
     * Create main node from YAML data
     */
    protected function createMainNode(array $data): string
    {
        $labels = [$data['type']];
        
        // Add Concept label to all nodes for consistency
        if ($data['type'] !== 'Concept') {
            $labels[] = 'Concept';
        }
        
        $properties = [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'type' => $data['type']
        ];
        
        // Add type-specific properties
        switch ($data['type']) {
            case 'ProceduralAgent':
                $properties['code_reference'] = $data['code_reference'] ?? '';
                $properties['priority'] = $data['priority'] ?? 1;
                $labels[] = 'PROCEDURAL_AGENT';
                break;
                
            case 'Frame':
                $properties['inherits_from'] = $data['inherits_from'] ?? [];
                break;
                
            case 'FrameElement':
                $properties['allowed_concepts'] = $data['allowed_concepts'] ?? [];
                $properties['required'] = $data['required'] ?? false;
                break;
                
            case 'ImageSchema':
                $properties['dimensions'] = $data['dimensions'] ?? [];
                $properties['primitive_type'] = $data['primitive_type'] ?? 'spatial';
                break;
        }
        
        return $this->graphService->createConcept([
            'labels' => $labels,
            'properties' => $properties
        ]);
    }
    
    /**
     * Create relationship from YAML relation definition
     */
    protected function createRelationFromYaml(string $mainNodeId, array $relation): void
    {
        // Create target node if specified
        $targetNodeId = null;
        
        if (isset($relation['target_node'])) {
            $targetNodeId = $this->createMainNode($relation['target_node']);
            $this->nodeCache[$relation['target_node']['name']] = $targetRetryEEContinueEditphp           $this->nodeCache[$relation['target_node']['name']] = $targetNodeId;
       } elseif (isset($relation['target_name'])) {
           // Reference to existing node
           if (isset($this->nodeCache[$relation['target_name']])) {
               $targetNodeId = $this->nodeCache[$relation['target_name']];
           } else {
               // Try to find in graph
               $targetNodeId = $this->findNodeByName($relation['target_name']);
           }
       }
       
       if (!$targetNodeId) {
           throw new \Exception("Cannot resolve target node for relation");
       }
       
       // Create relationships
       if (isset($relation['relations_to_parent'])) {
           foreach ($relation['relations_to_parent'] as $relDef) {
               $this->graphService->createRelationship(
                   $mainNodeId,
                   $targetNodeId,
                   $relDef['name'],
                   $relDef['properties'] ?? []
               );
           }
       }
   }
   
   /**
    * Create relationships from file (second pass)
    */
   protected function createRelationshipsFromFile(string $filepath): void
   {
       $yamlContent = File::get($filepath);
       $data = Yaml::parse($yamlContent);
       
       if (!isset($data['relations'])) {
           return;
       }
       
       $mainNodeId = $this->nodeCache[$data['name']] ?? null;
       if (!$mainNodeId) {
           return;
       }
       
       foreach ($data['relations'] as $relation) {
           if (!isset($relation['target_node'])) {
               continue; // Skip if no target node to create relationships with
           }
           
           $targetNodeId = $this->nodeCache[$relation['target_node']['name']] ?? null;
           if (!$targetNodeId) {
               continue;
           }
           
           // Create relationships between main node and target
           if (isset($relation['relations_to_parent'])) {
               foreach ($relation['relations_to_parent'] as $relDef) {
                   $this->graphService->createRelationship(
                       $mainNodeId,
                       $targetNodeId,
                       $relDef['name'],
                       $relDef['properties'] ?? []
                   );
               }
           }
       }
   }
   
   /**
    * Find node by name in graph
    */
   protected function findNodeByName(string $name): ?string
   {
       // This would use Neo4j to find existing node
       // Implementation depends on your specific graph structure
       return null;
   }
   
   /**
    * Get load statistics
    */
   public function getLoadStatistics(): array
   {
       return [
           'files_loaded' => count($this->loadedFiles),
           'nodes_cached' => count($this->nodeCache),
           'loaded_files' => $this->loadedFiles
       ];
   }
}
4.2 Create YAML Template Examples
File: storage/soul/yaml/frames/BUYING.yaml
yaml# BUYING Frame Definition
type: Frame
name: BUYING
description: A COMMERCIAL_TRANSACTION where a BUYER acquires a GOOD from a SELLER, paying a PRICE.

inherits_from:
  - COMMERCIAL_TRANSACTION
  - TRANSFER

relations:
  # BUYER Frame Element
  - target_node:
      type: FrameElement
      name: BUYER
      description: The person or entity who pays and receives the good.
      allowed_concepts: [PERSON, ENTITY]
      required: true
    relations_to_parent:
      - name: HAS_FE
        properties:
          role_type: "core"
          semantic_role: "agent"
      - name: IS_A
        properties:
          inheritance_source: "COMMERCIAL_TRANSACTION::BUYER"

  # SELLER Frame Element  
  - target_node:
      type: FrameElement
      name: SELLER
      description: The person or entity who receives the payment and gives the good.
      allowed_concepts: [PERSON, ENTITY]
      required: true
    relations_to_parent:
      - name: HAS_FE
        properties:
          role_type: "core"
          semantic_role: "recipient"

  # GOOD Frame Element
  - target_node:
      type: FrameElement
      name: GOOD
      description: The object or service that is transferred.
      allowed_concepts: [OBJECT, SERVICE]
      required: true
    relations_to_parent:
      - name: HAS_FE
        properties:
          role_type: "core"
          semantic_role: "theme"

  # PRICE Frame Element
  - target_node:
      type: FrameElement
      name: PRICE
      description: The monetary value transferred for the good.
      allowed_concepts: [MONEY, VALUE]
      required: true
    relations_to_parent:
      - name: HAS_FE
        properties:
          role_type: "core"
          semantic_role: "instrument"
File: storage/soul/yaml/image_schemas/CONTAINER.yaml
yaml# CONTAINER Image Schema Definition
type: ImageSchema
name: CONTAINER
description: Basic spatial schema for containment relationships.

primitive_type: spatial
dimensions: [INTERIOR, BOUNDARY, EXTERIOR]

relations:
  # Interior region
  - target_node:
      type: Concept
      name: INTERIOR
      description: The inside space of the container.
    relations_to_parent:
      - name: PART_OF
        properties:
          spatial_role: "interior_region"
      - name: HAS_PROPERTY
        properties:
          property_type: "bounded_space"

  # Boundary
  - target_node:
      type: Concept  
      name: BOUNDARY
      description: The edge that separates interior from exterior.
    relations_to_parent:
      - name: PART_OF
        properties:
          spatial_role: "boundary"
      - name: HAS_PROPERTY
        properties:
          property_type: "separating_surface"

  # Exterior region
  - target_node:
      type: Concept
      name: EXTERIOR  
      description: The outside space of the container.
    relations_to_parent:
      - name: PART_OF
        properties:
          spatial_role: "exterior_region"
      - name: HAS_PROPERTY
        properties:
          property_type: "unbounded_space"
File: storage/soul/yaml/agents/FrameInstantiator.yaml
yaml# Frame Instantiator Agent Definition
type: ProceduralAgent
name: FRAME_INSTANTIATOR
description: Creates new frame instances with filled frame elements from input data.

code_reference: "FrameService::instantiateFrame"
priority: 1

relations:
  # Operates on Frame concepts
  - target_name: FRAME
    relations_to_parent:
      - name: OPERATES_ON
        properties:
          operation_type: "instantiation"
          
  # Uses FrameElement concepts
  - target_name: FRAME_ELEMENT
    relations_to_parent:
      - name: USES
        properties:
          usage_type: "element_filling"

  # Connected to instantiation process
  - target_node:
      type: Concept
      name: INSTANTIATION_PROCESS
      description: The cognitive process of creating specific instances from general templates.
    relations_to_parent:
      - name: IMPLEMENTS
        properties:
          process_type: "cognitive_instantiation"
Phase 5: Implement Specialized Agent Services
5.1 Create Base Agent Service Class
File: app/Soul/Services/BaseAgentService.php
php<?php

namespace App\Soul\Services;

use App\Soul\Contracts\AgentServiceInterface;
use App\Soul\Contracts\GraphServiceInterface;
use Illuminate\Support\Facades\Log;

abstract class BaseAgentService implements AgentServiceInterface
{
    protected GraphServiceInterface $graphService;
    protected array $agentMethods = [];
    
    public function __construct(GraphServiceInterface $graphService)
    {
        $this->graphService = $graphService;
        $this->initializeAgentMethods();
    }
    
    public function executeAgent(string $method, array $parameters = []): mixed
    {
        if (!$this->isValidAgent($method)) {
            throw new \Exception("Unknown agent method: {$method}");
        }
        
        if (!$this->validateParameters($method, $parameters)) {
            throw new \Exception("Invalid parameters for agent method: {$method}");
        }
        
        Log::info("Executing agent", [
            'service' => static::class,
            'method' => $method,
            'parameters_count' => count($parameters)
        ]);
        
        try {
            return $this->$method($parameters);
        } catch (\Exception $e) {
            Log::error("Agent execution failed", [
                'service' => static::class,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function getAvailableAgents(): array
    {
        return array_keys($this->agentMethods);
    }
    
    public function validateParameters(string $method, array $parameters): bool
    {
        if (!isset($this->agentMethods[$method])) {
            return false;
        }
        
        $requiredParams = $this->agentMethods[$method]['required_parameters'] ?? [];
        
        foreach ($requiredParams as $param) {
            if (!isset($parameters[$param])) {
                return false;
            }
        }
        
        return true;
    }
    
    protected function isValidAgent(string $method): bool
    {
        return method_exists($this, $method) && isset($this->agentMethods[$method]);
    }
    
    /**
     * Initialize agent methods registry - to be implemented by subclasses
     */
    abstract protected function initializeAgentMethods(): void;
    
    /**
     * Create standardized success response
     */
    protected function createSuccessResponse(array $data): array
    {
        return array_merge([
            'status' => 'success',
            'timestamp' => now()->toISOString(),
            'service' => class_basename(static::class)
        ], $data);
    }
    
    /**
     * Create standardized error response
     */
    protected function createErrorResponse(string $message, array $context = []): array
    {
        return [
            'status' => 'error',
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toISOString(),
            'service' => class_basename(static::class)
        ];
    }
}
5.2 Create FrameService Implementation
File: app/Soul/Services/FrameService.php
php<?php

namespace App\Soul\Services;

use App\Soul\Contracts\FrameServiceInterface;

class FrameService extends BaseAgentService implements FrameServiceInterface
{
    protected function initializeAgentMethods(): void
    {
        $this->agentMethods = [
            'instantiateFrame' => [
                'description' => 'Create a new frame instance with filled elements',
                'required_parameters' => ['frame_name', 'context'],
                'optional_parameters' => ['frame_elements', 'inheritance_data']
            ],
            'addFrameElement' => [
                'description' => 'Add a frame element to an existing frame instance',
                'required_parameters' => ['frame_instance_id', 'element_name', 'element_value'],
                'optional_parameters' => ['element_type', 'constraints']
            ],
            'resolveInheritance' => [
                'description' => 'Resolve frame inheritance relationships',
                'required_parameters' => ['frame_name'],
                'optional_parameters' => ['inheritance_depth']
            ],
            'matchFrame' => [
                'description' => 'Calculate matching confidence between input and frame',
                'required_parameters' => ['frame_name', 'input_data'],
                'optional_parameters' => ['matching_threshold']
            ],
            'adaptFrame' => [
                'description' => 'Adapt frame to fit new situation using Minsky\'s accommodation strategies',
                'required_parameters' => ['frame_instance_id', 'new_data'],
                'optional_parameters' => ['adaptation_strategy']
            ]
        ];
    }
    
    public function instantiateFrame(array $parameters): array
    {
        $frameName = $parameters['frame_name'];
        $context = $parameters['context'];
        $frameElements = $parameters['frame_elements'] ?? [];
        
        // Find frame definition in graph
        $frameQuery = "
            MATCH (frame:Frame {name: \$frameName})
            OPTIONAL MATCH (frame)-[:HAS_FE]->(fe:FrameElement)
            RETURN frame, collect(fe) AS frame_elements
        ";
        
        $result = $this->graphService->neo4j->run($frameQuery, ['frameName' => $frameName]);
        $frameData = $result->first();
        
        if (!$frameData || !$frameData->get('frame')) {
            return $this->createErrorResponse("Frame not found: {$frameName}");
        }
        
        // Create frame instance
        $instanceId = 'frame_instance_' . uniqid();
        
        $createInstanceQuery = "
            MATCH (frame:Frame {name: \$frameName})
            CREATE (instance:FrameInstance {
                id: \$instanceId,
                frame_name: \$frameName,
                context: \$context,
                created_at: datetime(),
                status: 'active'
            })
            CREATE (instance)-[:INSTANCE_OF]->(frame)
            RETURN instance.id AS id
        ";
        
        $this->graphService->neo4j->run($createInstanceQuery, [
            'frameName' => $frameName,
            'instanceId' => $instanceId,
            'context' => json_encode($context)
        ]);
        
        // Fill frame elements
        $filledElements = [];
        $frameElementsData = $frameData->get('frame_elements');
        
        foreach ($frameElementsData as $feData) {
            $feName = $feData->get('name');
            $elementValue = $frameElements[$feName] ?? $this->findDefaultValue($feName, $context);
            
            if ($elementValue !== null) {
                $this->fillFrameElement($instanceId, $feName, $elementValue);
                $filledElements[] = ['name' => $feName, 'value' => $elementValue];
            }
        }
        
        return $this->createSuccessResponse([
            'frame_instance_id' => $instanceId,
            'frame_name' => $frameName,
            'filled_elements' => $filledElements,
            'context' => $context
        ]);
    }
    
    public function addFrameElement(array $parameters): array
    {
        $instanceId = $parameters['frame_instance_id'];
        $elementName = $parameters['element_name'];
        $elementValue = $parameters['element_value'];
        
        return $this->createSuccessResponse([
            'element_added' => $this->fillFrameElement($instanceId, $elementName, $elementValue)
        ]);
    }
    
    public function resolveInheritance(array $parameters): array
    {
        $frameName = $parameters['frame_name'];
        $depth = $parameters['inheritance_depth'] ?? 5;
        
        $query = "
            MATCH path = (frame:Frame {name: \$frameName})-[:IS_A*0..{$depth}]->(parent:Frame)
            RETURN collect(parent.name) AS inheritance_chain
        ";
        
        $result = $this->graphService->neo4j->run($query, ['frameName' => $frameName]);
        $inheritanceChain = $result->first()->get('inheritance_chain');
        
        return $this->createSuccessResponse([
            'frame_name' => $frameName,
            'inheritance_chain' => $inheritanceChain
        ]);
    }
    
    public function matchFrame(array $parameters): array
    {
        $frameName = $parameters['frame_name'];
        $inputData = $parameters['input_data'];
        $threshold = $parameters['matching_threshold'] ?? 0.5;
        
        // Simple matching algorithm - can be enhanced
        $matchScore = $this->calculateMatchingScore($frameName, $inputData);
        
        return $this->createSuccessResponse([
            'frame_name' => $frameName,
            'match_score' => $matchScore,
            'matches' => $matchScore >= $threshold,
            'threshold' => $threshold
        ]);
    }
    
    public function adaptFrame(array $parameters): array
    {
        $instanceId = $parameters['frame_instance_id'];
        $newData = $parameters['new_data'];
        $strategy = $parameters['adaptation_strategy'] ?? 'auto';
        
        // Implement Minsky's accommodation strategies
        $adaptationResult = $this->performFrameAdaptation($instanceId, $newData, $strategy);
        
        return $this->createSuccessResponse([
            'frame_instance_id' => $instanceId,
            'adaptation_strategy' => $strategy,
            'adaptations_made' => $adaptationResult
        ]);
    }
    
    /**
     * Helper method to fill frame element
     */
    protected function fillFrameElement(string $instanceId, string $elementName, $elementValue): bool
    {
        $query = "
            MATCH (instance:FrameInstance {id: \$instanceId})
            CREATE (feInstance:FrameElementInstance {
                id: apoc.create.uuid(),
                element_name: \$elementName,
                value: \$elementValue,
                created_at: datetime()
            })
            CREATE (instance)-[:HAS_FE_INSTANCE]->(feInstance)
            RETURN feInstance.id AS id
        ";
        
        try {
            $result = $this->graphService->neo4j->run($query, [
                'instanceId' => $instanceId,
                'elementName' => $elementName,
                'elementValue' => is_string($elementValue) ? $elementValue : json_encode($elementValue)
            ]);
            
            return $result->first() !== null;
        } catch (\Exception $e) {
            Log::error("Failed to fill frame element", [
                'instance_id' => $instanceId,
                'element_name' => $elementName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Find default value for frame element
     */
    protected function findDefaultValue(string $elementName, array $context): mixed
    {
        // Simple default value logic - can be enhanced
        if (isset($context[$elementName])) {
            return $context[$elementName];
        }
        
        // Could query graph for default values
        return null;
    }
    
    /**
     * Calculate matching score between frame and input
     */
    protected function calculateMatchingScore(string $frameName, array $inputData): float
    {
        // Simple implementation - calculate based on how many frame elements can be filled
        // This could be much more sophisticated
        
        $query = "
            MATCH (frame:Frame {name: \$frameName})-[:HAS_FE]->(fe:FrameElement)
            RETURN count(fe) AS total_elements, collect(fe.name) AS element_names
        ";
        
        $result = $this->graphService->neo4j->run($query, ['frameName' => $frameName]);
        $frameInfo = $result->first();
        
        if (!$frameInfo) {
            return 0.0;
        }
        
        $totalElements = $frameInfo->get('total_elements');
        $elementNames = $frameInfo->get('element_names');
        
        if ($totalElements === 0) {
            return 0.0;
        }
        
        $matchingElements = 0;
        foreach ($elementNames as $elementName) {
            if (isset($inputData[strtolower($elementName)])) {
                $matchingElements++;
            }
        }
        
        return $matchingElements / $totalElements;
    }
    
    /**
     * Perform frame adaptation using Minsky's strategies
     */
    protected function performFrameAdaptation(string $instanceId, array $newData, string $strategy): array
    {
        $adaptations = [];
        
        switch ($strategy) {
            case 'matching':
                // Find similar frames and adapt
                $adaptations[] = 'Applied matching strategy';
                break;
                
            case 'excuse':
                // Explain discrepancies
                $adaptations[] = 'Applied excuse strategy';
                break;
                
            case 'advice':
                // Use embedded knowledge
                $adaptations[] = 'Applied advice strategy';
                break;
                
            case 'auto':
            default:
                // Try all strategies
                $adaptations = ['Applied automatic adaptation'];
                break;
        }
        
        return $adaptations;
    }
}
5.3 Create Additional Agent Services
File: app/Soul/Services/ImageSchemaService.php
php<?php

namespace App\Soul\Services;

use App\Soul\Contracts\ImageSchemaServiceInterface;

class ImageSchemaService extends BaseAgentService implements ImageSchemaServiceInterface
{
    protected function initializeAgentMethods(): void
    {
        $this->agentMethods = [
            'createPath' => [
                'description' => 'Create a path structure with source, path, and goal',
                'required_parameters' => ['source', 'goal'],
                'optional_parameters' => ['path_type', 'obstacles']
            ],
            'calculateDistance' => [
                'description' => 'Calculate conceptual distance between two points',
                'required_parameters' => ['point1', 'point2'],
                'optional_parameters' => ['distance_type', 'dimensions']
            ],
            'checkContainment' => [
                'description' => 'Check if one concept is contained within another',
                'required_parameters' => ['container', 'content'],
                'optional_parameters' => ['containment_type']
            ],
            'applyForce' => [
                'description' => 'Apply force dynamics to concept relationships',
                'required_parameters' => ['force_type', 'source', 'target'],
                'optional_parameters' => ['magnitude', 'direction']
            ],
            'defineRegion' => [
                'description' => 'Define a conceptual region with boundaries',
                'required_parameters' => ['region_name', 'center'],
                'optional_parameters' => ['boundaries', 'dimensions']
            ]
        ];
    }
    
    public function createPath(array $parameters): array
    {
        $source = $parameters['source'];
        $goal = $parameters['goal'];
        $pathType = $parameters['path_type'] ?? 'direct';
        
        $pathId = 'path_' . uniqid();
        
        $query = "
            CREATE (path:Path:Concept {
                id: \$pathId,
                source: \$source,
                goal: \$goal,
                path_type: \$pathType,
                created_at: datetime()
            })
            RETURN path.id AS id
        ";
        
        $result = $this->graphService->neo4j->run($query, [
            'pathId' => $pathId,
            'source' => $source,
            'goal' => $goal,
            'pathType' => $pathType
        ]);
        
        return $this->createSuccessResponse([
            'path_id' => $pathId,
            'source' => $source,
            'goal' => $goal,
            'path_type' => $pathType
        ]);
    }
    
    public function calculateDistance(array $parameters): float
    {
        $point1 = $parameters['point1'];
        $point2 = $parameters['point2'];
        $distanceType = $parameters['distance_type'] ?? 'semantic';
        
        // Simple semantic distance calculation
        // In a full implementation, this would use sophisticated graph algorithms
        
        $query = "
            MATCH (p1:Concept {name: \$point1}), (p2:Concept {name: \$point2})
            MATCH path = shortestPath((p1)-[*1..5]-(p2))
            RETURN length(path) AS distance
        ";
        
        $result = $this->graphService->neo4j->run($query, [
            'point1' => $point1,
            'point2' => $point2
        ]);
        
        $distance = $result->first()?->get('distance') ?? -1;
        
        return $distance >= 0 ? (float) $distance : 999.0; // Return high distance if no path found
    }
    
    public function checkContainment(array $parameters): bool
    {
        $container = $parameters['container'];
        $content = $parameters['content'];
        $containmentType = $parameters['containment_type'] ?? 'spatial';
        
        $query = "
            MATCH (container:Concept {name: \$container}), (content:Concept {name: \$content})
            OPTIONAL MATCH (content)-[:PART_OF|CONTAINED_IN*1..3]->(container)
            RETURN count(*) > 0 AS is_contained
        ";
        
        $result = $this->graphService->neo4j->run($query, [
            'container' => $container,
            'content' => $content
        ]);
        
        return (bool) $result->first()->get('is_contained');
    }
    
    public function applyForce(array $parameters): array
    {
        $forceType = $parameters['force_type'];
        $source = $parameters['source'];
        $target = $parameters['target'];
        $magnitude = $parameters['magnitude'] ?? 1.0;
        
        $forceId = 'force_' . uniqid();
        
        $query = "
            MATCH (s:Concept {name: \$source}), (t:Concept {name: \$target})
            CREATE (force:Force:Concept {
                id: \$forceId,
                force_type: \$forceType,
                magnitude: \$magnitude,
                created_at: datetime()
            })
            CREATE (s)-[:APPLIES_FORCE]->(force)-[:AFFECTS]->(t)
            RETURN force.id AS id
        ";
        
        $result = $this->graphService->neo4j->run($query, [
            'forceId' => $forceId,
            'source' => $source,
            'target' => $target,
            'forceType' => $forceType,
            'magnitude' => $magnitude
        ]);
        
        return $this->createSuccessResponse([
            'force_id' => $forceId,
            'force_type' => $forceType,
            'source' => $source,
            'target' => $target,
            'magnitude' => $magnitude
        ]);
    }
    
    public function defineRegion(array $parameters): array
    {
        $regionName = $parameters['region_name'];
        $center = $parameters['center'];
        $boundaries = $parameters['boundaries'] ?? [];
        
        $regionId = 'region_' . uniqid();
        
        $query = "
            CREATE (region:Region:Concept {
                id: \$regionId,
                name: \$regionName,
                center: \$center,
                boundaries: \$boundaries,
                created_at: datetime()
            })
            RETURN region.id AS id
        ";
        
        $result = $this->graphService->neo4j->run($query, [
            'regionId' => $regionId,
            'regionName' => $regionName,
            'center' => $center,
            'boundaries' => json_encode($boundaries)
        ]);
        
        return $this->createSuccessResponse([
            'region_id' => $regionId,
            'region_name' => $regionName,
            'center' => $center,
            'boundaries' => $boundaries
        ]);
    }
}
Phase 6: Integration and Testing Setup
6.1 Update Service Provider
File: app/Providers/SoulServiceProvider.php (Update existing or create new)
php<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Soul\Contracts\GraphServiceInterface;
use App\Soul\Contracts\FrameServiceInterface;
use App\Soul\Contracts\ImageSchemaServiceInterface;
use App\Soul\Services\GraphService;
use App\Soul\Services\FrameService;
use App\Soul\Services\ImageSchemaService;
use App\Soul\Services\MindService;
use App\Soul\Services\YamlLoaderService;

class SoulServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register core services
        $this->app->bind(GraphServiceInterface::class, GraphService::class);
        
        // Register agent services
        $this->app->bind(FrameServiceInterface::class, FrameService::class);
        $this->app->bind(ImageSchemaServiceInterface::class, ImageSchemaService::class);
        
        // Register MindService as singleton
        $this->app->singleton(MindService::class, function ($app) {
            return new MindService(
                $app->make(GraphServiceInterface::class),
                $app
            );
        });
        
        // Register YAML loader
        $this->app->bind(YamlLoaderService::class, function ($app) {
            return new YamlLoaderService(
                $app->make(GraphServiceInterface::class)
            );
        });
    }
    
    public function boot()
    {
        // Register configuration
        $this->publishes([
            __DIR__.'/../../config/soul.php' => config_path('soul.php'),
        ], 'soul-config');
        
        // Register YAML directory
        $this->publishes([
            __DIR__.'/../../storage/soul/yaml' => storage_path('soul/yaml'),
        ], 'soul-yaml');
    }
}
6.2 Create Configuration File
File: config/soul.php
php<?php

return [
    'graph' => [
        'spreading_activation' => [
            'max_depth' => env('SOUL_MAX_ACTIVATION_DEPTH', 3),
            'threshold' => env('SOUL_ACTIVATION_THRESHOLD', 0.1),
            'decay_factor' => env('SOUL_ACTIVATION_DECAY', 0.8),
        ],
        
        'klines' => [
            'min_usage_for_strengthening' => env('SOUL_KLINE_MIN_USAGE', 3),
            'strength_increment' => env('SOUL_KLINE_STRENGTH_INCREMENT', 0.1),
        ],
    ],
    
    'agents' => [
        'execution_timeout' => env('SOUL_AGENT_TIMEOUT', 30),
        'max_parallel_agents' => env('SOUL_MAX_PARALLEL_AGENTS', 5),
        'retry_attempts' => env('SOUL_AGENT_RETRY_ATTEMPTS', 2),
    ],
    
    'yaml' => [
        'base_directory' => storage_path('soul/yaml'),
        'auto_load_on_boot' => env('SOUL_AUTO_LOAD_YAML', false),
        'validation_strict' => env('SOUL_YAML_VALIDATION_STRICT', true),
    ],
    
    'processing' => [
        'session_timeout' => env('SOUL_SESSION_TIMEOUT', 300), // 5 minutes
        'max_concurrent_sessions' => env('SOUL_MAX_CONCURRENT_SESSIONS', 10),
        'cleanup_frequency' => env('SOUL_CLEANUP_FREQUENCY', 3600), // 1 hour
    ],
];
6.3 Create Artisan Commands
**File: `appRetryEEContinueEditFile: app/Console/Commands/SoulLoadYamlCommand.php
php<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Soul\Services\YamlLoaderService;

class SoulLoadYamlCommand extends Command
{
    protected $signature = 'soul:load-yaml 
                            {directory? : Directory to load YAML files from}
                            {--file= : Load specific YAML file}
                            {--force : Force reload even if already loaded}
                            {--validate-only : Only validate, don\'t load}';

    protected $description = 'Load SOUL framework data from YAML files';

    public function handle(YamlLoaderService $yamlLoader)
    {
        $this->info('Starting SOUL YAML loading process...');

        if ($this->option('file')) {
            return $this->loadSingleFile($yamlLoader, $this->option('file'));
        }

        $directory = $this->argument('directory') ?? storage_path('soul/yaml');

        if (!is_dir($directory)) {
            $this->error("Directory not found: {$directory}");
            return 1;
        }

        $this->info("Loading YAML files from: {$directory}");

        if ($this->option('validate-only')) {
            $this->info('Validation mode - no data will be loaded');
        }

        try {
            $results = $yamlLoader->loadFromDirectory($directory);
            
            $this->displayResults($results);
            $this->displayStatistics($yamlLoader->getLoadStatistics());
            
            $this->info('YAML loading completed successfully!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Loading failed: " . $e->getMessage());
            return 1;
        }
    }

    protected function loadSingleFile(YamlLoaderService $yamlLoader, string $file): int
    {
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        try {
            $result = $yamlLoader->loadFile($file);
            
            $this->table(
                ['Property', 'Value'],
                [
                    ['File', $result['file']],
                    ['Status', $result['status']],
                    ['Main Node ID', $result['main_node_id'] ?? 'N/A'],
                    ['Relationships Created', $result['relationships_created'] ?? 0]
                ]
            );
            
            $this->info('File loaded successfully!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error("File loading failed: " . $e->getMessage());
            return 1;
        }
    }

    protected function displayResults(array $results): void
    {
        $headers = ['File', 'Status', 'Node ID', 'Relationships'];
        $rows = [];

        foreach ($results as $result) {
            $rows[] = [
                basename($result['file']),
                $result['status'],
                $result['main_node_id'] ?? 'N/A',
                $result['relationships_created'] ?? 0
            ];
        }

        $this->table($headers, $rows);
    }

    protected function displayStatistics(array $stats): void
    {
        $this->info("\n=== Load Statistics ===");
        $this->line("Files loaded: " . $stats['files_loaded']);
        $this->line("Nodes cached: " . $stats['nodes_cached']);
    }
}
File: app/Console/Commands/SoulProcessCommand.php
php<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Soul\Services\MindService;

class SoulProcessCommand extends Command
{
    protected $signature = 'soul:process 
                            {type : Request type (text, concept, frame)}
                            {data : Request data (JSON string or text)}
                            {--format=table : Output format (table, json)}
                            {--verbose : Show detailed processing information}';

    protected $description = 'Process a request through the SOUL cognitive system';

    public function handle(MindService $mindService)
    {
        $requestType = $this->argument('type');
        $requestData = $this->parseRequestData($this->argument('data'));

        if (!$requestData) {
            $this->error('Invalid request data format');
            return 1;
        }

        $this->info("Processing {$requestType} request through SOUL...");

        if ($this->option('verbose')) {
            $this->line("Request data: " . json_encode($requestData, JSON_PRETTY_PRINT));
        }

        try {
            $response = $mindService->processRequest($requestType, $requestData);
            
            $this->displayResponse($response);
            
            if ($this->option('verbose')) {
                $this->displayStatistics($mindService->getStatistics());
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Processing failed: " . $e->getMessage());
            
            if ($this->option('verbose')) {
                $this->line($e->getTraceAsString());
            }
            
            return 1;
        }
    }

    protected function parseRequestData(string $data): ?array
    {
        // Try to parse as JSON first
        $jsonData = json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $jsonData;
        }

        // Treat as plain text
        return ['text' => $data];
    }

    protected function displayResponse(array $response): void
    {
        if ($this->option('format') === 'json') {
            $this->line(json_encode($response, JSON_PRETTY_PRINT));
            return;
        }

        $this->info("\n=== Processing Results ===");
        
        $this->table(['Property', 'Value'], [
            ['Session ID', $response['session_id']],
            ['Status', $response['status']],
            ['Agents Executed', $response['statistics']['agents_executed'] ?? 0],
            ['Processing Time (ms)', $response['statistics']['processing_time'] ?? 0],
            ['Activation Nodes', $response['statistics']['activation_nodes'] ?? 0]
        ]);

        if (isset($response['results']) && !empty($response['results'])) {
            $this->info("\n=== Agent Execution Results ===");
            
            foreach ($response['results'] as $i => $result) {
                $this->line("Agent " . ($i + 1) . ": " . ($result['agent']['code_reference'] ?? 'Unknown'));
                $this->line("  Status: " . ($result['result']['status'] ?? 'Unknown'));
                $this->line("  Timestamp: " . ($result['timestamp'] ?? 'Unknown'));
            }
        }
    }

    protected function displayStatistics(array $stats): void
    {
        $this->info("\n=== System Statistics ===");
        
        $this->table(['Metric', 'Value'], [
            ['Requests Processed', $stats['requests_processed']],
            ['Agents Activated', $stats['agents_activated']],
            ['K-lines Created', $stats['klines_created']],
            ['Processing Sessions', $stats['processing_sessions']]
        ]);
    }
}
File: app/Console/Commands/SoulStatusCommand.php
php<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Soul\Services\MindService;
use App\Soul\Contracts\GraphServiceInterface;
use Laudis\Neo4j\Contracts\ClientInterface;

class SoulStatusCommand extends Command
{
    protected $signature = 'soul:status {--detailed : Show detailed system information}';

    protected $description = 'Display SOUL framework system status';

    public function handle(
        MindService $mindService,
        GraphServiceInterface $graphService,
        ClientInterface $neo4j
    ) {
        $this->info('SOUL Framework System Status');
        $this->line(str_repeat('=', 50));

        // Basic system info
        $this->displayBasicStatus($mindService);

        // Graph database status
        $this->displayGraphStatus($neo4j);

        if ($this->option('detailed')) {
            $this->displayDetailedStatus($mindService, $graphService);
        }

        $this->line(str_repeat('=', 50));
        $this->info('Status check completed');
    }

    protected function displayBasicStatus(MindService $mindService): void
    {
        $stats = $mindService->getStatistics();
        $currentSession = $mindService->getCurrentSession();

        $this->info("\n📊 Processing Statistics");
        $this->table(['Metric', 'Value'], [
            ['Total Requests Processed', $stats['requests_processed']],
            ['Agents Activated', $stats['agents_activated']],
            ['K-lines Created', $stats['klines_created']],
            ['Processing Sessions', $stats['processing_sessions']],
            ['Current Session Status', $currentSession['status'] ?? 'None']
        ]);
    }

    protected function displayGraphStatus(ClientInterface $neo4j): void
    {
        try {
            // Test Neo4j connection
            $result = $neo4j->run('RETURN "Connection OK" as status');
            $connectionStatus = $result->first()->get('status');
            
            // Get node counts
            $nodeCountResult = $neo4j->run('
                MATCH (n) 
                RETURN 
                    count(n) as total_nodes,
                    count{(n:Concept)} as concepts,
                    count{(n:Frame)} as frames,
                    count{(n:PROCEDURAL_AGENT)} as agents,
                    count{(n:KLine)} as klines
            ');
            
            $nodeCounts = $nodeCountResult->first();
            
            $this->info("\n🗄️ Graph Database Status");
            $this->table(['Component', 'Status/Count'], [
                ['Neo4j Connection', $connectionStatus],
                ['Total Nodes', $nodeCounts->get('total_nodes')],
                ['Concept Nodes', $nodeCounts->get('concepts')],
                ['Frame Nodes', $nodeCounts->get('frames')],
                ['Procedural Agents', $nodeCounts->get('agents')],
                ['K-line Nodes', $nodeCounts->get('klines')]
            ]);

        } catch (\Exception $e) {
            $this->error("\n❌ Graph Database Error: " . $e->getMessage());
        }
    }

    protected function displayDetailedStatus(MindService $mindService, GraphServiceInterface $graphService): void
    {
        $this->info("\n🔍 Detailed System Information");
        
        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        
        $this->table(['System Metric', 'Value'], [
            ['Memory Usage', $this->formatBytes($memoryUsage)],
            ['Peak Memory', $this->formatBytes($peakMemory)],
            ['PHP Version', PHP_VERSION],
            ['Laravel Version', app()->version()]
        ]);

        // Configuration status
        $this->info("\n⚙️ Configuration Status");
        $config = config('soul');
        
        $this->table(['Configuration', 'Value'], [
            ['Max Activation Depth', $config['graph']['spreading_activation']['max_depth']],
            ['Activation Threshold', $config['graph']['spreading_activation']['threshold']],
            ['Agent Timeout', $config['agents']['execution_timeout'] . 's'],
            ['Max Parallel Agents', $config['agents']['max_parallel_agents']],
            ['YAML Auto Load', $config['yaml']['auto_load_on_boot'] ? 'Enabled' : 'Disabled']
        ]);
    }

    protected function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor(log($size, 1024));
        return sprintf("%.2f %s", $size / pow(1024, $factor), $units[$factor] ?? 'GB');
    }
}
6.4 Create Updated ResourceController
File: app/Http/Controllers/SOUL/ResourceController.php (UPDATE EXISTING)
php<?php

namespace App\Http\Controllers\SOUL;

use App\Http\Controllers\Controller;
use App\Soul\Services\MindService;
use App\Soul\Services\YamlLoaderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ResourceController extends Controller
{
    protected MindService $mindService;
    protected YamlLoaderService $yamlLoader;

    public function __construct(
        MindService $mindService,
        YamlLoaderService $yamlLoader
    ) {
        $this->mindService = $mindService;
        $this->yamlLoader = $yamlLoader;
    }

    /**
     * Process cognitive request through SOUL system
     */
    public function processCognitive(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:text,concept,frame,image_schema',
            'data' => 'required|array',
            'options' => 'sometimes|array'
        ]);

        try {
            $response = $this->mindService->processRequest(
                $request->input('type'),
                $request->input('data')
            );

            return response()->json([
                'success' => true,
                'data' => $response,
                'statistics' => $this->mindService->getStatistics()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'statistics' => $this->mindService->getStatistics()
            ], 500);
        }
    }

    /**
     * Load YAML data into system
     */
    public function loadYaml(Request $request): JsonResponse
    {
        $request->validate([
            'directory' => 'sometimes|string',
            'file' => 'sometimes|string',
            'force' => 'sometimes|boolean'
        ]);

        try {
            if ($request->has('file')) {
                $result = $this->yamlLoader->loadFile($request->input('file'));
                return response()->json(['success' => true, 'data' => $result]);
            }

            $directory = $request->input('directory', storage_path('soul/yaml'));
            $results = $this->yamlLoader->loadFromDirectory($directory);
            
            return response()->json([
                'success' => true,
                'data' => $results,
                'statistics' => $this->yamlLoader->getLoadStatistics()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system status and statistics
     */
    public function status(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'mind_statistics' => $this->mindService->getStatistics(),
                    'current_session' => $this->mindService->getCurrentSession(),
                    'yaml_statistics' => $this->yamlLoader->getLoadStatistics(),
                    'system_info' => [
                        'memory_usage' => memory_get_usage(true),
                        'peak_memory' => memory_get_peak_usage(true),
                        'php_version' => PHP_VERSION,
                        'laravel_version' => app()->version()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute specific agent with parameters
     */
    public function executeAgent(Request $request): JsonResponse
    {
        $request->validate([
            'service' => 'required|string',
            'method' => 'required|string',
            'parameters' => 'required|array'
        ]);

        try {
            // This would require enhancing MindService to expose direct agent execution
            // For now, we'll process it through the normal cognitive pipeline
            $response = $this->mindService->processRequest('agent_execution', [
                'agent_service' => $request->input('service'),
                'agent_method' => $request->input('method'),
                'parameters' => $request->input('parameters')
            ]);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
6.5 Update Routes
File: routes/web.php (ADD TO EXISTING)
php// SOUL Framework Routes
Route::prefix('soul')->name('soul.')->group(function () {
    // Cognitive processing
    Route::post('/process', [ResourceController::class, 'processCognitive'])->name('process');
    Route::post('/execute-agent', [ResourceController::class, 'executeAgent'])->name('execute-agent');
    
    // YAML management
    Route::post('/load-yaml', [ResourceController::class, 'loadYaml'])->name('load-yaml');
    
    // System status
    Route::get('/status', [ResourceController::class, 'status'])->name('status');
});
Phase 7: Final Integration and Testing
7.1 Create Test Suite
File: tests/Feature/Soul/SoulFrameworkTest.php
php<?php

namespace Tests\Feature\Soul;

use Tests\TestCase;
use App\Soul\Services\MindService;
use App\Soul\Services\YamlLoaderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SoulFrameworkTest extends TestCase
{
    use RefreshDatabase;

    protected MindService $mindService;
    protected YamlLoaderService $yamlLoader;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mindService = app(MindService::class);
        $this->yamlLoader = app(YamlLoaderService::class);
    }

    /** @test */
    public function it_can_process_simple_text_request()
    {
        $response = $this->mindService->processRequest('text', [
            'text' => 'John is buying a book'
        ]);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('session_id', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('completed', $response['status']);
    }

    /** @test */
    public function it_can_load_yaml_frame_definition()
    {
        $yamlContent = <<<YAML
type: Frame
name: TEST_FRAME
description: A test frame for unit testing

relations:
  - target_node:
      type: FrameElement
      name: TEST_ELEMENT
      description: A test frame element
    relations_to_parent:
      - name: HAS_FE
YAML;

        $tempFile = tempnam(sys_get_temp_dir(), 'soul_test_') . '.yaml';
        file_put_contents($tempFile, $yamlContent);

        try {
            $result = $this->yamlLoader->loadFile($tempFile);
            
            $this->assertArrayHasKey('status', $result);
            $this->assertEquals('success', $result['status']);
            $this->assertArrayHasKey('main_node_id', $result);
            
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function it_can_execute_frame_instantiation_agent()
    {
        // First load a test frame
        $this->loadTestFrame();

        $response = $this->mindService->processRequest('frame', [
            'frame_name' => 'TEST_FRAME',
            'context' => ['test_element' => 'test_value']
        ]);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('results', $response);
    }

    /** @test */
    public function it_maintains_statistics_across_requests()
    {
        $initialStats = $this->mindService->getStatistics();

        $this->mindService->processRequest('text', ['text' => 'test']);

        $newStats = $this->mindService->getStatistics();
        
        $this->assertGreaterThan(
            $initialStats['requests_processed'],
            $newStats['requests_processed']
        );
    }

    protected function loadTestFrame(): void
    {
        $yamlContent = <<<YAML
type: Frame
name: TEST_FRAME
description: A test frame

relations:
  - target_node:
      type: FrameElement
      name: TEST_ELEMENT
      description: Test element
    relations_to_parent:
      - name: HAS_FE
YAML;

        $tempFile = tempnam(sys_get_temp_dir(), 'soul_test_') . '.yaml';
        file_put_contents($tempFile, $yamlContent);

        try {
            $this->yamlLoader->loadFile($tempFile);
        } finally {
            unlink($tempFile);
        }
    }
}
7.2 Create README for Implementation
File: SOUL_IMPLEMENTATION.md
markdown# SOUL Framework Implementation Guide

## Overview

This document describes the completed implementation of the SOUL (Structured Object-Oriented Understanding Language) framework based on Minsky's Society of Mind principles.

## Architecture Summary

### Core Components

1. **MindService** - Central orchestrator that coordinates cognitive processing
2. **GraphService** - Handles Neo4j graph operations and spreading activation
3. **Agent Services** - Specialized services (FrameService, ImageSchemaService, etc.)
4. **YamlLoaderService** - Loads conceptual data from YAML files
5. **ResourceController** - REST API endpoints for external access

### Key Features

- **Society of Mind Implementation**: Agents as both conceptual nodes and executable code
- **Spreading Activation**: Graph-based concept activation and discovery
- **K-line Recording**: Automatic learning from successful processing paths
- **YAML-Based Data Loading**: Human-readable conceptual data definitions
- **Microservice Architecture**: Modular, specialized agent services

## Installation & Setup

### 1. Update Dependencies
```bash
composer require symfony/yaml
2. Register Service Provider
Add to config/app.php:
php'providers' => [
    // ...
    App\Providers\SoulServiceProvider::class,
],
3. Publish Configuration
bashphp artisan vendor:publish --tag=soul-config
php artisan vendor:publish --tag=soul-yaml
4. Update Neo4j Schema
Run the Cypher commands from the schema file to create constraints and sample agents.
5. Load Initial Data
bashphp artisan soul:load-yaml storage/soul/yaml
Usage Examples
Command Line Interface
bash# Process text through cognitive system
php artisan soul:process text "John is buying a book"

# Load YAML data
php artisan soul:load-yaml storage/soul/yaml/frames

# Check system status
php artisan soul:status --detailed
REST API
bash# Process cognitive request
curl -X POST /soul/process \
  -H "Content-Type: application/json" \
  -d '{
    "type": "text",
    "data": {"text": "John is buying a book"}
  }'

# Load YAML data
curl -X POST /soul/load-yaml \
  -H "Content-Type: application/json" \
  -d '{"directory": "storage/soul/yaml"}'

# Get system status
curl /soul/status
Development Workflow
Adding New Agent Services

Create service class extending BaseAgentService
Implement required interface methods
Register in SoulServiceProvider
Create corresponding YAML agent definitions
Add tests

Adding New Frame Definitions

Create YAML file in appropriate directory
Define frame structure with relations
Load using soul:load-yaml command
Test through cognitive processing

Monitoring and Debugging

Use soul:status for system health checks
Check Laravel logs for agent execution details
Monitor Neo4j browser for graph visualization
Use --verbose flags for detailed output

Performance Considerations

Graph Indexes: Ensure proper Neo4j indexes are created
Agent Timeouts: Configure appropriate timeouts for complex operations
Memory Usage: Monitor memory usage during large-scale processing
Concurrent Sessions: Limit concurrent processing sessions

Troubleshooting
Common Issues

Neo4j Connection: Check database connectivity and credentials
YAML Loading Errors: Validate YAML syntax and structure
Agent Execution Failures: Check agent parameter validation
Memory Issues: Increase PHP memory limit for large datasets

Debug Commands
bash# Test Neo4j connection
php artisan soul:status

# Validate YAML without loading
php artisan soul:load-yaml --validate-only

# Process with verbose output
php artisan soul:process text "test" --verbose
Next Steps

Enhanced Agent Services: Implement remaining cognitive services
Learning Mechanisms: Enhance K-line strengthening algorithms
Performance Optimization: Implement caching and optimization
UI Development: Create web interface for system management
Integration Testing: Comprehensive test suite development

Configuration Reference
See config/soul.php for all configuration options including:

Spreading activation parameters
Agent execution settings
YAML loading options
Processing session limits

This implementation provides a solid foundation for building cognitive AI systems based on established cognitive science principles while maintaining practical computational efficiency.

---

## Summary

This detailed implementation plan provides a complete refactoring of the SOUL framework to implement Society of Mind principles with:

1. **Dual Agent Representation**: Agents as both graph nodes and executable code
2. **Microservice Architecture**: Specialized agent services for different cognitive functions
3. **Graph-Centric Processing**: Neo4j as the single source of truth with spreading activation
4. **YAML-Based Initialization**: Human-readable data loading system
5. **K-line Learning**: Automatic strengthening of successful processing paths
6. **Comprehensive Tooling**: CLI commands, REST API, and testing framework

The implementation maintains backward compatibility where possible while fundamentally restructuring the system to better reflect Minsky's theoretical framework. Each phase builds upon the previous one, allowing for incremental development and testing.RetryClaude can make mistakes. Please double-check responses.Research Sonnet 4
