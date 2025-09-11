# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**SOUL Framework 1.0** is a comprehensive cognitive AI system implementing Marvin Minsky's **Society of Mind** architecture with Fillmore's **Frame Semantics**. Built on Laravel PHP with Neo4j graph database, it provides sophisticated computational cognition through dual representation (graph + agents), spreading activation, and K-line learning.

**Current Status**: Production-ready cognitive architecture with complete implementation including:
- Complete Society of Mind implementation with MindService coordination
- Dual representation architecture (Neo4j graph + PHP agent services)
- Comprehensive spreading activation with K-line learning
- Agent-based cognitive processing with timeout protection
- YAML-based knowledge loading system with Laravel command
- Comprehensive SOUL API with full CRUD operations
- Neo4j database optimization with constraints and indexes
- Graph visualization and monitoring capabilities

## 🧠 Cognitive Architecture Overview

The SOUL Framework implements a sophisticated **dual-representation cognitive architecture**:

### Core Principles

1. **Society of Mind**: Intelligence emerges from interactions of simple agents
2. **Frame Semantics**: Knowledge represented as stereotyped situations (Fillmore)
3. **Dual Representation**: Concepts exist as both graph nodes (Neo4j) and executable agents (PHP)
4. **Spreading Activation**: Neural-inspired activation propagation through conceptual networks
5. **K-line Learning**: Successful cognitive patterns recorded and strengthened for reuse
6. **Agent Coordination**: Independent cognitive agents collaborate through graph-mediated communication

### Cognitive Processing Pipeline

```
Input → Concept Extraction → Spreading Activation → Agent Discovery → 
Agent Execution → Processing Rounds → Convergence Check → Response Generation → K-line Learning
```

1. **Input Analysis**: Extract initial concepts from user input
2. **Spreading Activation**: Propagate activation through conceptual network  
3. **Agent Discovery**: Identify relevant procedural agents from activated nodes
4. **Agent Execution**: Execute discovered agents with fail-fast error handling
5. **Iterative Processing**: Multiple rounds until convergence or timeout
6. **Response Generation**: Synthesize results from final activation state
7. **Learning**: Record successful patterns as K-lines for future use

## Development Environment

### Docker Setup
```bash
# Development environment with Neo4j
docker compose -f docker-compose-dev.yml up --build

# Production environment  
docker compose up --build

# Access the application at http://localhost:8001
# Access Neo4j Browser at http://localhost:7474
# Default Laravel credentials: user=webtool, password=test
# Default Neo4j credentials: username=neo4j, password=secret
```

### Asset Compilation
```bash
# Development with hot reload
npm run dev

# Build for production  
npm run build
```

### Laravel Commands
```bash
# Run migrations
php artisan migrate

# Clear caches
php artisan cache:clear && php artisan config:clear && php artisan view:clear

# Generate application key
php artisan key:generate

# Check application status
php artisan about
```

### Testing & Code Quality
```bash
# Run PHPUnit tests
php artisan test

# Laravel Pint for code formatting
vendor/bin/pint
```

## SOUL Framework Implementation

### Core Services Architecture

```
App\Soul\Services\
├── MindService.php              # Central cognitive coordinator (Society of Mind)
├── GraphService.php             # Graph operations and spreading activation
├── FrameService.php             # Frame-based cognitive processing
├── ImageSchemaService.php       # Spatial and embodied cognition
├── YamlLoaderService.php        # Conceptual data loading from YAML files
├── Neo4jFrameService.php        # Neo4j database operations
└── BaseAgentService.php         # Abstract base for all agent services
```

### Contract Interfaces

```
App\Soul\Contracts\
├── AgentServiceInterface.php      # Base agent service contract
├── GraphServiceInterface.php      # Graph operations contract
├── FrameServiceInterface.php      # Frame operations contract
├── ImageSchemaServiceInterface.php # Image schema contract
└── Neo4jService.php              # Database operations contract
```

### Core Components

#### 1. MindService - Central Coordinator

The **MindService** orchestrates the entire cognitive architecture implementing "Society of Mind":

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

#### 2. GraphService - Graph Operations

The **GraphService** handles all graph-based operations including spreading activation:

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

// Record successful processing patterns as K-lines
$klineId = $graphService->recordKLine($activationPath, 'purchase_context');
```

#### 3. Agent Services - Executable Cognition

**FrameService** - Frame-based Processing:
```php
use App\Soul\Services\FrameService;

$frameService = app(FrameService::class);

// Match input against frame patterns
$matches = $frameService->executeAgent('matchFrame', [
    'input' => ['text' => 'John buys a book'],
    'frame_candidates' => ['COMMERCIAL_TRANSACTION', 'MOTION'],
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

**ImageSchemaService** - Embodied Cognition:
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

#### 4. YamlLoaderService - Knowledge Management

Load conceptual data from YAML files:

```php
use App\Soul\Services\YamlLoaderService;

$yamlLoader = app(YamlLoaderService::class);

// Load all YAML files from configured directory
$results = $yamlLoader->loadAllYamlFiles();

// Load specific YAML file
$result = $yamlLoader->loadYamlFile(storage_path('soul/yaml/primitives.yml'));
```

## YAML-Based Knowledge System

### Laravel Command for YAML Loading

```bash
# Load all YAML files interactively
php artisan soul:load-yaml --interactive

# Load specific files
php artisan soul:load-yaml primitives.yml frames/commercial.yml

# Load by pattern
php artisan soul:load-yaml --pattern="primitive*" --pattern="*.yml"

# Dry run to preview
php artisan soul:load-yaml --dry-run

# Force reload existing files
php artisan soul:load-yaml --force

# Clear cache and reload
php artisan soul:load-yaml --clear-cache --force
```

### YAML File Structure

```yaml
metadata:
  title: "SOUL Framework Primitives"
  version: "1.0"
  description: "Core primitive concepts for cognitive processing"

concepts:
  - name: "CONTAINER"
    labels: ["Concept", "ImageSchema", "SpatialPrimitive"]
    properties:
      type: "image_schema"
      description: "Bounded region with interior and exterior"
      domain: "spatial"
      primitive: true

  - name: "COMMERCIAL_TRANSACTION"
    labels: ["Concept", "Frame"]
    properties:
      type: "frame"
      description: "Frame for buying and selling interactions"
      domain: "commerce"
      frame_elements:
        buyer: "Entity acquiring goods"
        seller: "Entity providing goods"
        goods: "Items being transacted"

procedural_agents:
  - name: "ContainerActivationAgent"
    code_reference: "ImageSchemaService::activateContainerSchema"
    description: "Activates CONTAINER schema for spatial reasoning"
    priority: 1

  - name: "FrameMatchingAgent"
    code_reference: "FrameService::matchFrame"
    description: "Matches input against frame patterns"
    priority: 2

relationships:
  - from: "BUYER"
    to: "PERSON"
    type: "IS_A"
    properties:
      strength: 0.9
      context: "commercial"
```

## Database Architecture

### Dual Database Setup
- **MySQL**: Legacy FrameNet data and traditional relational operations
- **Neo4j Community Edition**: Graph database for SOUL conceptual networks

### Neo4j Setup and Schema Management

```bash
# Apply Neo4j constraints and indexes
php artisan soul:neo4j-constraints

# Check existing constraints and indexes
php artisan soul:neo4j-constraints --check

# Drop existing constraints (use carefully!)
php artisan soul:neo4j-constraints --drop
```

This creates:
- Uniqueness constraints for concept names, agent code references, K-line IDs
- Performance indexes for frequently queried properties
- Full-text search indexes for concept names and descriptions
- Relationship indexes for spreading activation optimization

### Neo4j Integration

- **Container**: `neo4j:5.15-community` with APOC plugins
- **Access**: Web interface at `http://localhost:7474`, Bolt protocol on port `7687`
- **Laravel Integration**: `Neo4jServiceProvider` with singleton driver injection
- **Connection**: Laudis Neo4j PHP client for modern PHP 8+ support

### Neo4j Usage Examples

```php
use Laudis\Neo4j\Contracts\ClientInterface;

class ConceptController extends Controller
{
    public function __construct(private ClientInterface $neo4j)
    {
        // Neo4j client automatically injected via service provider
    }

    public function createConcept(Request $request)
    {
        $result = $this->neo4j->run(
            'CREATE (c:Concept {name: $name, type: $type}) RETURN c',
            ['name' => $request->name, 'type' => $request->type]
        );
        
        return response()->json(['created' => true, 'concept' => $result->first()->get('c')]);
    }
}
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

- **IS_A**: Hierarchical concept relationships
- **PART_OF**: Compositional relationships
- **CAUSES**: Causal relationships
- **ACTIVATES**: K-line to concept activation
- **SCHEMA_ACTIVATES**: Image schema activation
- **HAS_FRAME_ELEMENT**: Frame to element relationships

## SOUL API Endpoints

The comprehensive REST API is available at `/soul/*`:

```php
// Concept Management
GET    /soul/concepts                    # List/search concepts
POST   /soul/concepts                    # Create new concept
GET    /soul/concepts/{name}             # Get concept with relationships
PUT    /soul/concepts/{name}             # Update concept
DELETE /soul/concepts/{name}             # Delete concept

// Cognitive Operations
POST   /soul/relationships               # Create relationship
POST   /soul/initialize                  # Initialize SOUL primitives
GET    /soul/spreading-activation/{name} # Perform spreading activation
GET    /soul/graph/{name}                # Get graph visualization data
GET    /soul/statistics                  # Get graph statistics

// Data Management
GET    /soul/export                      # Export graph data
POST   /soul/import                      # Import graph data
POST   /soul/search                      # Advanced concept search

// Database Management
POST   /soul/database/constraints        # Create Neo4j constraints/indexes
GET    /soul/database/status             # Get database status
DELETE /soul/database/constraints        # Drop constraints/indexes
```

## Frontend Architecture

### Technology Stack
- **Laravel 12**: Primary framework with custom routing via annotations
- **HTMX**: Frontend reactivity via HX-* headers in controllers
- **Vite**: Asset bundling with Laravel Vite plugin
- **AlpineJS**: Frontend JavaScript framework  
- **Fomantic UI**: Primary CSS framework
- **JointJS**: Graph visualization library for SOUL graph implementation
- **Chart.js**: Data visualization

### Key Features
- HTMX-driven interactions with server-side rendering
- Blade templating with component-based UI
- Graph visualization using JointJS for conceptual relationships
- Real-time cognitive processing visualization

## Monitoring and Diagnostics

### System Statistics
```php
$mindService = app(MindService::class);
$stats = $mindService->getSystemStatistics();
// Returns: active sessions, agent services, graph stats, config
```

### Neo4j Monitoring
```cypher
// Check concept count
MATCH (c:Concept) RETURN count(c) as total_concepts;

// Monitor spreading activation patterns
MATCH (c:Concept)-[r]-(related:Concept) 
RETURN c.type, type(r), related.type, count(*) 
ORDER BY count(*) DESC LIMIT 10;

// Check K-line usage
MATCH (kline:KLine) 
RETURN kline.context, kline.usage_count, kline.success_rate 
ORDER BY kline.usage_count DESC LIMIT 10;
```

## Error Handling

Comprehensive exception hierarchy with rich context:

```php
SoulException                              // Base exception
├── ProcessingSessionException             // Session management errors
├── AgentCommunicationException           // Agent execution failures  
├── CognitiveProcessingException          // Pipeline failures
├── ProcessingTimeoutException            // Timeout violations
├── ActivationConvergenceException        // Convergence failures
├── Neo4jException                        // Database errors
└── FrameException                        // Frame-related errors
```

All exceptions include detailed context for debugging and monitoring.

## Performance Optimization

### Spreading Activation Optimization
- **Depth Limiting**: Configurable max depth (default: 3)
- **Activation Thresholds**: Filter low-relevance nodes (default: 0.1)
- **Graph Indexing**: Neo4j indexes on critical properties
- **Connection Pooling**: Persistent Neo4j connections

### Agent Execution Optimization  
- **Timeout Protection**: Configurable execution timeouts (default: 30s)
- **Parallel Execution**: Multiple agents can run concurrently
- **Service Singletons**: Reuse agent service instances
- **Method Caching**: Cache agent method metadata

## Development Guidelines

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

3. **Create YAML Agent Definition**:
```yaml
procedural_agents:
  - name: "CustomAgent"
    code_reference: "custom::customMethod"
    description: "Custom cognitive processing"
    priority: 1
```

### YAML Data Guidelines
- **Naming**: UPPERCASE concepts, PascalCase agents
- **Organization**: Group by domain (primitives/, frames/, domains/)
- **Documentation**: Include comprehensive descriptions
- **Validation**: Use strict mode during development
- **Version Control**: Track all YAML files in git

### Performance Best Practices
- Monitor spreading activation depth and thresholds
- Use Neo4j constraints and indexes effectively
- Configure appropriate agent timeouts
- Regular K-line cleanup and optimization

## Documentation References

For comprehensive implementation details, see:
- `docs/SOUL_ARCHITECTURE.md` - Complete architectural documentation
- `docs/YAML_STRUCTURE.md` - YAML file structure and examples
- `docs/SETUP_AND_USAGE.md` - Setup guide and usage patterns
- `docs/README.md` - Overview and quick start guide

## Implementation Status

### ✅ Completed Features
- Complete Society of Mind cognitive architecture
- Dual representation (Neo4j graph + PHP agents)
- Spreading activation with K-line learning
- Agent-based processing with comprehensive services
- YAML-based knowledge loading with Laravel command
- Neo4j constraints and performance optimization
- Comprehensive exception handling and monitoring
- REST API with full CRUD operations
- Graph visualization infrastructure

### Current Capabilities
- **Text Processing**: Cognitive processing of natural language input
- **Frame-based Analysis**: Automatic frame matching and instantiation
- **Spatial Reasoning**: Image schema activation for embodied cognition
- **Knowledge Learning**: K-line recording and pattern strengthening
- **Graph Operations**: Comprehensive spreading activation
- **Data Management**: YAML loading, validation, and caching

This SOUL Framework provides a production-ready cognitive AI system implementing established cognitive science principles (Minsky's Society of Mind, Fillmore's Frame Semantics) in a modern, extensible architecture suitable for research and practical applications.

## Environment Variables

```bash
# Neo4j Configuration
NEO4J_HOST=neo4j
NEO4J_PORT=7687
NEO4J_USER=neo4j
NEO4J_PASSWORD=secret
NEO4J_DATABASE=neo4j

# SOUL Framework Configuration
SOUL_MAX_ACTIVATION_DEPTH=3
SOUL_ACTIVATION_THRESHOLD=0.1
SOUL_AGENT_TIMEOUT=30
SOUL_MAX_PARALLEL_AGENTS=5
SOUL_SESSION_TIMEOUT=300
SOUL_AUTO_LOAD_YAML=false
SOUL_YAML_VALIDATION_STRICT=true
```

## Authentication & Authorization
- Configurable auth handlers (internal/Auth0)
- Role-based access control (ADMIN, MASTER, MANAGER levels)
- Laravel-based session authentication with custom guards and providers

The SOUL Framework represents a sophisticated implementation of computational cognition based on established cognitive science principles, providing both theoretical depth and practical utility for cognitive AI applications.