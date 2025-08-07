# SOUL Framework - Society of Mind Architecture Documentation

## Overview

The SOUL Framework implements Marvin Minsky's **Society of Mind** cognitive architecture, providing a sophisticated system for computational cognition based on:

- **Dual Representation**: Concepts exist both as graph nodes (Neo4j) and executable agents (PHP classes)
- **Spreading Activation**: Neural-inspired activation propagation through conceptual networks
- **K-line Learning**: Successful cognitive patterns are recorded and strengthened for reuse
- **Agent-based Processing**: Independent cognitive agents collaborate through graph-mediated communication
- **Emergent Intelligence**: Complex behaviors emerge from simple agent interactions

## Architectural Principles

### 1. Society of Mind Core Concepts

**Agents**: Independent cognitive units that process specific aspects of cognition
- Each agent is both a graph node and executable code
- Agents communicate through graph relationships and method calls
- No central control - intelligence emerges from agent interactions

**Frames**: Knowledge structures representing stereotyped situations (Fillmore's Frame Semantics)
- Stored as graph nodes with dynamic frame elements
- Instantiated independently with context-specific bindings
- Support Minsky's matching and default assignment processes

**K-lines**: Learning mechanisms that record successful cognitive patterns
- Capture activation paths that led to successful problem solving
- Strengthened through repeated use (Hebbian-like learning)
- Enable faster processing of similar future problems

### 2. Dual Representation Architecture

```
Conceptual Level (Neo4j Graph)     ←→     Procedural Level (PHP Agents)
├── Concepts                       ←→     ├── Agent Services
├── Relationships                  ←→     ├── Agent Methods  
├── Activation Patterns            ←→     ├── Processing Logic
└── K-lines                        ←→     └── Communication Protocols
```

**Graph Layer (Neo4j)**:
- Persistent conceptual relationships
- Spreading activation computation
- K-line storage and retrieval
- Session archival and statistics

**Agent Layer (PHP)**:
- Dynamic processing logic
- Method execution with parameters
- Service-oriented architecture
- Timeout protection and error handling

### 3. Cognitive Processing Pipeline

```
Input → Concept Extraction → Spreading Activation → Agent Discovery → 
Agent Execution → Processing Rounds → Convergence Check → Response Generation → K-line Learning
```

1. **Input Analysis**: Extract initial concepts from user input (text, structured data, context)
2. **Spreading Activation**: Propagate activation through conceptual network
3. **Agent Discovery**: Identify relevant procedural agents from activated nodes
4. **Agent Execution**: Execute discovered agents with fail-fast error handling  
5. **Iterative Processing**: Multiple rounds until convergence or timeout
6. **Response Generation**: Synthesize results from final activation state
7. **Learning**: Record successful patterns as K-lines for future use

## Core Components

### MindService - Central Coordinator

The `MindService` orchestrates the entire cognitive architecture:

```php
use App\Soul\Services\MindService;

$mindService = app(MindService::class);

// Start cognitive processing session
$sessionId = $mindService->startProcessingSession([
    'text' => 'John buys a book from Mary',
    'context' => ['commercial', 'transaction'],
    'concepts' => ['PERSON', 'PURCHASE', 'OBJECT']
]);

// Process input through cognitive pipeline
$response = $mindService->processInput([
    'text' => 'John buys a book from Mary'
], $sessionId);

// End session and cleanup
$result = $mindService->endProcessingSession($sessionId);
```

**Key Methods**:
- `startProcessingSession()`: Initialize new cognitive session
- `processInput()`: Main processing pipeline execution  
- `endProcessingSession()`: Cleanup and archival
- `registerAgentService()`: Register executable agent services
- `getSystemStatistics()`: Monitoring and diagnostics

### GraphService - Graph Operations

The `GraphService` handles all graph-based operations:

```php
use App\Soul\Contracts\GraphServiceInterface;

$graphService = app(GraphServiceInterface::class);

// Spreading activation from initial concepts
$activation = $graphService->runSpreadingActivation(['PERSON', 'BUY'], [
    'max_depth' => 3,
    'activation_threshold' => 0.1,
    'include_procedural_agents' => true
]);

// Create new concepts in graph
$nodeId = $graphService->createConcept([
    'name' => 'COMMERCIAL_TRANSACTION',
    'labels' => ['Concept', 'Frame'],
    'properties' => ['type' => 'frame', 'domain' => 'commerce']
]);

// Record successful processing patterns
$klineId = $graphService->recordKLine($activationPath, 'purchase_context');
```

**Key Methods**:
- `runSpreadingActivation()`: Neural-inspired activation propagation
- `createConcept()`: Add new conceptual nodes
- `createProceduralAgent()`: Add executable agent nodes
- `recordKLine()`: Store successful patterns for learning
- `analyzeActivationResults()`: Insights from activation patterns

### Agent Services - Executable Cognition

Agent services provide executable cognitive capabilities:

#### FrameService - Frame-based Processing
```php
use App\Soul\Services\FrameService;

$frameService = app(FrameService::class);

// Match input against frame patterns
$matches = $frameService->executeAgent('matchFrame', [
    'input' => ['text' => 'John buys a book'],
    'frame_candidates' => ['COMMERCIAL_TRANSACTION', 'MOTION', 'PERSON'],
    'threshold' => 0.6
]);

// Instantiate frame with bindings
$instance = $frameService->executeAgent('instantiateFrame', [
    'frame_type' => 'COMMERCIAL_TRANSACTION',
    'initial_elements' => [
        'buyer' => 'John',
        'seller' => 'Mary', 
        'goods' => 'book'
    ]
]);
```

#### ImageSchemaService - Embodied Cognition
```php
use App\Soul\Services\ImageSchemaService;

$imageService = app(ImageSchemaService::class);

// Activate CONTAINER schema for spatial reasoning
$container = $imageService->executeAgent('activateContainerSchema', [
    'concepts' => ['box', 'book', 'inside'],
    'spatial_context' => ['3d_space']
]);

// Project schema onto abstract domain (metaphorical mapping)
$projection = $imageService->executeAgent('projectImageSchema', [
    'source_schema' => 'CONTAINER',
    'target_domain' => 'MIND',
    'projection_type' => 'metaphorical'
]);
```

### YamlLoaderService - Data Loading

Load conceptual data from YAML files:

```php
use App\Soul\Services\YamlLoaderService;

$yamlLoader = app(YamlLoaderService::class);

// Load all YAML files from configured directory
$results = $yamlLoader->loadAllYamlFiles();

// Load specific YAML file
$result = $yamlLoader->loadYamlFile(storage_path('soul/yaml/primitives.yml'));
```

## Configuration

The framework is configured through `config/soul.php`:

```php
return [
    'graph' => [
        'spreading_activation' => [
            'max_depth' => 3,
            'threshold' => 0.1,
            'decay_factor' => 0.8,
        ],
        'klines' => [
            'min_usage_for_strengthening' => 3,
            'strength_increment' => 0.1,
        ],
    ],
    'agents' => [
        'execution_timeout' => 30,
        'max_parallel_agents' => 5,
        'retry_attempts' => 2,
    ],
    'processing' => [
        'session_timeout' => 300,
        'max_concurrent_sessions' => 10,
        'max_processing_rounds' => 5,
        'convergence_threshold' => 0.1,
    ],
    'yaml' => [
        'base_directory' => storage_path('soul/yaml'),
        'auto_load_on_boot' => false,
        'validation_strict' => true,
    ],
];
```

## Neo4j Database Schema

### Node Types

**Concept Nodes**:
```cypher
CREATE (c:Concept {
    name: "COMMERCIAL_TRANSACTION",
    type: "frame",
    description: "Frame for buying/selling interactions",
    created_at: datetime()
})
```

**Procedural Agent Nodes**:
```cypher
CREATE (agent:PROCEDURAL_AGENT:Concept {
    name: "MatchFrameAgent",
    code_reference: "FrameService::matchFrame",
    priority: 1,
    created_at: datetime()
})
```

**K-line Nodes**:
```cypher
CREATE (kline:KLine {
    id: "kline_12345",
    context: "purchase_scenario",
    activation_pattern: "{...json...}",
    usage_count: 1,
    success_rate: 1.0,
    created_at: datetime()
})
```

### Relationships

**Conceptual Relationships**:
- `IS_A`: Hierarchical concept relationships
- `PART_OF`: Compositional relationships
- `CAUSES`: Causal relationships
- `SIMILAR_TO`: Similarity relationships

**Processing Relationships**:
- `ACTIVATES`: K-line to concept activation
- `SCHEMA_ACTIVATES`: Image schema activation
- `HAS_FRAME_ELEMENT`: Frame to element relationships

## Error Handling

The framework implements fail-fast error handling with rich exception context:

### Exception Hierarchy

```php
SoulException                              // Base exception
├── ProcessingSessionException             // Session management errors
├── AgentCommunicationException           // Agent execution failures  
├── CognitiveProcessingException          // Pipeline failures
├── ProcessingTimeoutException            // Timeout violations
├── ActivationConvergenceException        // Convergence failures
├── Neo4jException                        // Database errors
│   ├── Neo4jConnectionException          
│   └── Neo4jQueryException
└── FrameException                        // Frame-related errors
    ├── FrameNotFoundException
    ├── FrameInstantiationException
    └── FrameElementException
```

### Error Context

All exceptions include rich context for debugging:

```php
try {
    $response = $mindService->processInput($input, $sessionId);
} catch (CognitiveProcessingException $e) {
    Log::error("Cognitive processing failed", [
        'session_id' => $sessionId,
        'error' => $e->getMessage(),
        'context' => $e->getContext(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

## Performance Considerations

### Spreading Activation Optimization

- **Depth Limiting**: Configurable max depth to prevent exponential expansion
- **Activation Thresholds**: Filter low-relevance nodes early
- **Graph Indexing**: Neo4j indexes on critical properties
- **Connection Pooling**: Persistent Neo4j connections

### Agent Execution Optimization  

- **Timeout Protection**: Configurable execution timeouts
- **Parallel Execution**: Multiple agents can run concurrently
- **Service Singletons**: Reuse agent service instances
- **Method Caching**: Cache agent method metadata

### Memory Management

- **Session Cleanup**: Automatic cleanup of expired sessions  
- **Graph Pruning**: Periodic cleanup of unused nodes
- **K-line Pruning**: Remove unsuccessful or old K-lines
- **Connection Limits**: Bounded Neo4j connection pools

## Monitoring and Observability

### System Statistics

```php
$stats = $mindService->getSystemStatistics();
// Returns: active sessions, agent services, graph stats, config
```

### Session Monitoring

```php
$sessionStatus = $mindService->getSessionStatus($sessionId);
// Returns: processing state, statistics, execution history
```

### Neo4j Metrics

```php
$graphStats = $graphService->getGraphStatistics();
// Returns: node counts, relationship counts, performance metrics
```

## Extensibility

### Adding New Agent Services

1. **Create Service Class**:
```php
class CustomAgentService extends BaseAgentService implements CustomServiceInterface
{
    protected function initializeAgentMethods(): void {
        $this->agentMethods = [
            'customMethod' => [
                'description' => 'Custom cognitive operation',
                'required_parameters' => ['input'],
                'optional_parameters' => ['context']
            ]
        ];
    }
    
    public function customMethod(array $parameters): array {
        // Implementation
        return $this->createSuccessResponse($result);
    }
}
```

2. **Register Service**:
```php
// In AppServiceProvider
$this->app->singleton(CustomAgentService::class);

// Register with MindService
$mindService->registerAgentService('custom', app(CustomAgentService::class));
```

3. **Create Procedural Agents**:
```cypher
CREATE (agent:PROCEDURAL_AGENT:Concept {
    name: "CustomAgent",
    code_reference: "custom::customMethod",
    description: "Custom cognitive processing"
})
```

### Adding New Node Types

1. **Update Neo4j Schema**: Add constraints and indexes
2. **Extend GraphService**: Add creation methods
3. **Update Spreading Activation**: Handle new node types
4. **Add YAML Support**: Define loading rules

## Best Practices

### Agent Development

- **Single Responsibility**: Each agent method should have one clear purpose
- **Parameter Validation**: Validate all input parameters
- **Rich Responses**: Return detailed results with metadata
- **Error Handling**: Throw appropriate exceptions with context
- **Logging**: Log all significant operations

### Graph Design

- **Meaningful Relationships**: Use semantically rich relationship types
- **Balanced Hierarchy**: Avoid overly deep or flat concept hierarchies  
- **Consistent Naming**: Use consistent concept naming conventions
- **Performance Indexes**: Index frequently queried properties

### YAML Data Design

- **Modular Files**: Separate different domains into different files
- **Validation**: Use strict validation mode during development
- **Documentation**: Include descriptions for all concepts
- **Versioning**: Version YAML files for reproducible builds

## Security Considerations

### Input Validation

- All user input is validated before processing
- YAML files are validated against strict schemas
- Agent parameters are type-checked and sanitized

### Access Control

- Neo4j connections use authenticated users
- Agent execution is sandboxed with timeouts
- Session isolation prevents cross-contamination

### Data Protection

- Sensitive data is never logged
- Graph queries use parameterized statements
- K-lines are anonymized for privacy

This architecture provides a robust foundation for implementing sophisticated cognitive AI systems based on established cognitive science principles while maintaining practical considerations for deployment, monitoring, and extensibility.