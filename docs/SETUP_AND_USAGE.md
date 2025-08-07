# SOUL Framework - Setup and Usage Guide

## Initial Setup

### Prerequisites

Ensure you have the following components running:
- **Laravel 12**: PHP framework
- **Neo4j 5.15+**: Graph database (via Docker)
- **PHP 8.4+**: Runtime environment
- **Composer**: PHP dependency manager

### 1. Neo4j Database Setup

Start Neo4j using Docker Compose:

```bash
# Start development environment with Neo4j
docker compose -f docker-compose-dev.yml up

# OR start production environment
docker compose up

# Access Neo4j Browser at http://localhost:7474
# Default credentials: username=neo4j, password=secret
```

### 2. Apply Neo4j Constraints and Indexes

Apply the SOUL Framework database schema:

```bash
# Apply all Neo4j constraints and indexes
php artisan soul:neo4j-constraints

# Check existing constraints and indexes only
php artisan soul:neo4j-constraints --check

# Drop existing constraints before applying new ones (use carefully!)
php artisan soul:neo4j-constraints --drop
```

This command will:
- Create uniqueness constraints for concept names, agent code references, K-line IDs
- Create performance indexes for frequently queried properties
- Set up full-text search indexes for concept names and descriptions
- Configure relationship indexes for spreading activation

Expected output:
```
SOUL Framework - Neo4j Constraints Management
=============================================
Applying Neo4j constraints and indexes...
Found 45 Cypher statements to execute
Executing statement 1...
  ✅ Created: concept_name_unique
...
Execution Summary:
  ✅ Successful: 42
  ⏭️  Skipped: 3
  ❌ Errors: 0
```

### 3. Create YAML Data Directory

Set up the directory structure for YAML concept files:

```bash
# Create base directory for YAML files
mkdir -p storage/soul/yaml

# Create subdirectories for organization
mkdir -p storage/soul/yaml/{primitives,frames,domains,agents}

# Set proper permissions
chmod -R 755 storage/soul/
```

### 4. Environment Configuration

Add SOUL-specific environment variables to your `.env` file:

```env
# Neo4j Configuration (should already exist)
NEO4J_HOST=neo4j
NEO4J_PORT=7687
NEO4J_USER=neo4j
NEO4J_PASSWORD=secret
NEO4J_DATABASE=neo4j

# SOUL Framework Configuration
SOUL_MAX_ACTIVATION_DEPTH=3
SOUL_ACTIVATION_THRESHOLD=0.1
SOUL_ACTIVATION_DECAY=0.8
SOUL_KLINE_MIN_USAGE=3
SOUL_KLINE_STRENGTH_INCREMENT=0.1
SOUL_AGENT_TIMEOUT=30
SOUL_MAX_PARALLEL_AGENTS=5
SOUL_AGENT_RETRY_ATTEMPTS=2
SOUL_AUTO_LOAD_YAML=false
SOUL_YAML_VALIDATION_STRICT=true
SOUL_SESSION_TIMEOUT=300
SOUL_MAX_CONCURRENT_SESSIONS=10
SOUL_CLEANUP_FREQUENCY=3600
```

## Creating Initial Data

### 1. Create Basic Primitives YAML File

Create `storage/soul/yaml/primitives.yml`:

```yaml
metadata:
  title: "SOUL Framework Basic Primitives"
  version: "1.0"
  description: "Core primitive concepts for cognitive processing"
  author: "SOUL Framework"

concepts:
  # Image Schema Primitives
  - name: "CONTAINER"
    labels: ["Concept", "ImageSchema", "SpatialPrimitive"]
    properties:
      type: "image_schema"
      description: "Bounded region with interior and exterior"
      domain: "spatial"
      primitive: true
      image_schema: true

  - name: "PATH"
    labels: ["Concept", "ImageSchema", "SpatialPrimitive"]
    properties:
      type: "image_schema"
      description: "Trajectory from source to goal"
      domain: "spatial"
      primitive: true
      image_schema: true

  - name: "FORCE"
    labels: ["Concept", "ImageSchema", "SpatialPrimitive"]
    properties:
      type: "image_schema"
      description: "Causal force and agency relationships"
      domain: "causal"
      primitive: true
      image_schema: true

  # CSP Primitives
  - name: "EMOTION"
    labels: ["Concept", "CSPPrimitive"]
    properties:
      type: "csp_primitive"
      description: "Affective states and emotional processing"
      domain: "affective"
      primitive: true
      csp_primitive: true

  - name: "SCALE"
    labels: ["Concept", "CSPPrimitive"]
    properties:
      type: "csp_primitive"
      description: "Scalar quantities and magnitude"
      domain: "quantitative"
      primitive: true
      csp_primitive: true

  # Meta-schemas
  - name: "ENTITY"
    labels: ["Concept", "MetaSchema"]
    properties:
      type: "meta_schema"
      description: "Things with persistent identity"
      domain: "ontological"
      meta_schema: "ENTITY"

  - name: "PROCESS"
    labels: ["Concept", "MetaSchema"]
    properties:
      type: "meta_schema"
      description: "Dynamic sequences over time"
      domain: "temporal"
      meta_schema: "PROCESS"

  # Basic Entities
  - name: "PERSON"
    labels: ["Concept", "Entity"]
    properties:
      type: "entity"
      description: "Human individual with agency"
      domain: "social"

  - name: "OBJECT"
    labels: ["Concept", "Entity"]
    properties:
      type: "entity"
      description: "Physical thing with properties"
      domain: "physical"

procedural_agents:
  - name: "ContainerActivationAgent"
    code_reference: "ImageSchemaService::activateContainerSchema"
    description: "Activates CONTAINER schema for spatial reasoning"
    priority: 1

  - name: "PathActivationAgent"
    code_reference: "ImageSchemaService::activatePathSchema"
    description: "Activates PATH schema for motion reasoning"
    priority: 1

  - name: "ForceActivationAgent"
    code_reference: "ImageSchemaService::activateForceSchema"
    description: "Activates FORCE schema for causal reasoning"
    priority: 1

  - name: "FrameMatchingAgent"
    code_reference: "FrameService::matchFrame"
    description: "Matches input against frame patterns"
    priority: 2

relationships:
  - from: "PERSON"
    to: "ENTITY"
    type: "IS_A"
    properties:
      strength: 1.0
      
  - from: "OBJECT"
    to: "ENTITY"
    type: "IS_A"
    properties:
      strength: 1.0
```

### 2. Create Commercial Frame YAML File

Create `storage/soul/yaml/frames/commercial.yml`:

```yaml
metadata:
  title: "Commercial Transaction Frames"
  version: "1.0"
  description: "Frames for commercial and economic interactions"

concepts:
  - name: "COMMERCIAL_TRANSACTION"
    labels: ["Concept", "Frame"]
    properties:
      type: "frame"
      description: "Frame for buying and selling interactions"
      domain: "commerce"
      frame_elements:
        buyer: "Entity acquiring goods or services"
        seller: "Entity providing goods or services"
        goods: "Items or services being transacted"
        money: "Payment or consideration"
        time: "When transaction occurs"
        place: "Where transaction occurs"
      core_elements: ["buyer", "seller", "goods"]
      peripheral_elements: ["money", "time", "place"]

  - name: "BUYER"
    labels: ["Concept", "Role"]
    properties:
      type: "role"
      description: "Entity that acquires goods in transaction"
      domain: "commerce"

  - name: "SELLER"  
    labels: ["Concept", "Role"]
    properties:
      type: "role"
      description: "Entity that provides goods in transaction"
      domain: "commerce"

procedural_agents:
  - name: "CommercialTransactionMatcher"
    code_reference: "FrameService::matchFrame"
    description: "Identifies commercial transaction patterns in input"
    priority: 1

  - name: "TransactionFrameInstantiator"
    code_reference: "FrameService::instantiateFrame"
    description: "Creates commercial transaction frame instances"
    priority: 2

relationships:
  - from: "BUYER"
    to: "PERSON"
    type: "IS_A"
    properties:
      strength: 0.9
      context: "commercial"

  - from: "SELLER"
    to: "PERSON"
    type: "IS_A"
    properties:
      strength: 0.9
      context: "commercial"
      
  - from: "COMMERCIAL_TRANSACTION"
    to: "PROCESS"
    type: "IS_A"
    properties:
      strength: 0.8
```

### 3. Load Data into Neo4j

Load the YAML files into the Neo4j database:

```bash
# Load all YAML files from configured directory
php artisan tinker
```

```php
use App\Soul\Services\YamlLoaderService;

$yamlLoader = app(YamlLoaderService::class);

// Load all YAML files
$results = $yamlLoader->loadAllYamlFiles();

// Check results
print_r($results);
// Should show: loaded_files, errors, concepts_created, agents_created
```

Expected output:
```php
Array
(
    [loaded_files] => Array
    (
        [0] => Array
        (
            [file] => /path/to/storage/soul/yaml/primitives.yml
            [concepts_created] => 9
            [agents_created] => 4
            [relationships_created] => 2
        )
        [1] => Array
        (
            [file] => /path/to/storage/soul/yaml/frames/commercial.yml
            [concepts_created] => 3
            [agents_created] => 2
            [relationships_created] => 3
        )
    )
    [errors] => Array()
    [concepts_created] => 12
    [agents_created] => 6
)
```

### 4. Verify Database Setup

Check that data was loaded correctly:

```bash
# Access Neo4j Browser at http://localhost:7474
# Run these Cypher queries:
```

```cypher
// Check concept count
MATCH (c:Concept) RETURN count(c) as total_concepts;

// List all concepts
MATCH (c:Concept) RETURN c.name, labels(c), c.type ORDER BY c.name;

// Check procedural agents
MATCH (agent:PROCEDURAL_AGENT) RETURN agent.name, agent.code_reference;

// Check relationships
MATCH (a)-[r]->(b) RETURN type(r), a.name, b.name LIMIT 10;

// Verify constraints
SHOW CONSTRAINTS;

// Verify indexes  
SHOW INDEXES;
```

## Service Registration and Usage

### 1. Register Agent Services with MindService

Create a bootstrap file or service provider to register agent services:

```php
// In a service provider or bootstrap file
use App\Soul\Services\MindService;
use App\Soul\Services\FrameService;
use App\Soul\Services\ImageSchemaService;

$mindService = app(MindService::class);
$frameService = app(FrameService::class);
$imageSchemaService = app(ImageSchemaService::class);

// Register agent services
$mindService->registerAgentService('FrameService', $frameService);
$mindService->registerAgentService('ImageSchemaService', $imageSchemaService);
```

### 2. Basic Cognitive Processing Session

```php
use App\Soul\Services\MindService;

$mindService = app(MindService::class);

// Start processing session
$sessionId = $mindService->startProcessingSession([
    'text' => 'John buys a book from Mary for ten dollars',
    'context' => ['commercial', 'social'],
    'concepts' => ['PERSON', 'COMMERCIAL_TRANSACTION', 'OBJECT']
]);

// Process the input
$response = $mindService->processInput([
    'text' => 'John buys a book from Mary for ten dollars'
], $sessionId);

// Check results
print_r($response);

// End session
$sessionData = $mindService->endProcessingSession($sessionId);
```

Expected response structure:
```php
Array
(
    [session_id] => session_...
    [status] => success
    [processing_summary] => Array
    (
        [nodes_activated] => 15
        [agents_executed] => 3
        [processing_rounds] => 2
        [converged] => true
    )
    [activated_concepts] => Array
    (
        [0] => Array([name] => COMMERCIAL_TRANSACTION, [activation_strength] => 0.85)
        [1] => Array([name] => PERSON, [activation_strength] => 0.72)
        ...
    )
    [agent_results] => Array(...)
    [insights] => Array(...)
    [recommendations] => Array(...)
    [processing_time_ms] => 145.67
)
```

### 3. Direct Service Usage

#### GraphService Usage

```php
use App\Soul\Contracts\GraphServiceInterface;

$graphService = app(GraphServiceInterface::class);

// Run spreading activation
$activation = $graphService->runSpreadingActivation(
    ['COMMERCIAL_TRANSACTION', 'PERSON'], 
    ['max_depth' => 2, 'activation_threshold' => 0.2]
);

// Create new concept
$nodeId = $graphService->createConcept([
    'name' => 'BOOK',
    'labels' => ['Concept', 'Object'],
    'properties' => [
        'type' => 'object',
        'description' => 'Written work for reading'
    ]
]);

// Record successful processing pattern
$klineId = $graphService->recordKLine([
    'activation_pattern' => $activation,
    'agent_sequence' => ['FrameService::matchFrame'],
    'success_metrics' => ['success_rate' => 1.0]
], 'commercial_transaction_context');
```

#### FrameService Usage

```php
use App\Soul\Services\FrameService;

$frameService = app(FrameService::class);

// Match frame patterns
$matches = $frameService->executeAgent('matchFrame', [
    'input' => ['text' => 'John buys a book'],
    'frame_candidates' => ['COMMERCIAL_TRANSACTION', 'MOTION'],
    'threshold' => 0.5
]);

// Instantiate frame
$instance = $frameService->executeAgent('instantiateFrame', [
    'frame_type' => 'COMMERCIAL_TRANSACTION',
    'initial_elements' => [
        'buyer' => 'John',
        'seller' => 'Mary',
        'goods' => 'book'
    ]
]);
```

#### ImageSchemaService Usage

```php
use App\Soul\Services\ImageSchemaService;

$imageService = app(ImageSchemaService::class);

// Activate container schema
$container = $imageService->executeAgent('activateContainerSchema', [
    'concepts' => ['box', 'book', 'inside'],
    'containment_type' => 'physical'
]);

// Activate path schema  
$path = $imageService->executeAgent('activatePathSchema', [
    'concepts' => ['walk', 'home', 'store'],
    'source' => 'home',
    'goal' => 'store'
]);
```

## Monitoring and Diagnostics

### 1. System Statistics

```php
$mindService = app(MindService::class);
$graphService = app(GraphServiceInterface::class);

// Get system statistics
$systemStats = $mindService->getSystemStatistics();
print_r($systemStats);

// Get graph statistics
$graphStats = $graphService->getGraphStatistics();
print_r($graphStats);
```

### 2. Session Monitoring

```php
// Check active sessions
$activeCount = $mindService->getActiveSessionsCount();

// Get session details
$sessionStatus = $mindService->getSessionStatus($sessionId);
```

### 3. Neo4j Performance Monitoring

```cypher
// Check database performance
CALL db.stats.retrieve('GRAPH COUNTS');

// Monitor spreading activation patterns
MATCH (c:Concept)-[r]-(related:Concept) 
RETURN c.type, type(r), related.type, count(*) 
ORDER BY count(*) DESC LIMIT 10;

// Check K-line usage
MATCH (kline:KLine) 
RETURN kline.context, kline.usage_count, kline.success_rate 
ORDER BY kline.usage_count DESC LIMIT 10;
```

## Common Usage Patterns

### 1. Text Processing Pipeline

```php
function processCognitiveInput(string $text, array $context = []): array
{
    $mindService = app(MindService::class);
    
    // Start session
    $sessionId = $mindService->startProcessingSession([
        'text' => $text,
        'context' => $context
    ]);
    
    try {
        // Process input
        $response = $mindService->processInput(['text' => $text], $sessionId);
        
        // Extract key insights
        $insights = [
            'activated_frames' => $this->extractFrames($response['activated_concepts']),
            'agents_used' => array_column($response['agent_results'], 'agent'),
            'processing_time' => $response['processing_time_ms'],
            'confidence' => $this->calculateConfidence($response)
        ];
        
        return $insights;
        
    } finally {
        // Always cleanup session
        $mindService->endProcessingSession($sessionId);
    }
}
```

### 2. Frame-based Analysis

```php
function analyzeCommercialTransaction(array $input): array
{
    $frameService = app(FrameService::class);
    
    // Match against commercial frames
    $matches = $frameService->executeAgent('matchFrame', [
        'input' => $input,
        'frame_candidates' => ['COMMERCIAL_TRANSACTION'],
        'threshold' => 0.6
    ]);
    
    if ($matches['status'] === 'success' && !empty($matches['result']['matches'])) {
        $bestMatch = $matches['result']['best_match'];
        
        // Instantiate the frame
        $instance = $frameService->executeAgent('instantiateFrame', [
            'frame_type' => $bestMatch['frame_type'],
            'initial_elements' => $this->extractElements($input)
        ]);
        
        return [
            'frame_matched' => true,
            'confidence' => $bestMatch['confidence'],
            'frame_instance' => $instance['result']['frame_id']
        ];
    }
    
    return ['frame_matched' => false];
}
```

### 3. Spatial Reasoning

```php
function performSpatialReasoning(array $spatialConcepts): array
{
    $imageService = app(ImageSchemaService::class);
    
    // Try different spatial schemas
    $schemas = ['CONTAINER', 'PATH', 'FORCE'];
    $results = [];
    
    foreach ($schemas as $schema) {
        $method = 'activate' . ucfirst(strtolower($schema)) . 'Schema';
        
        try {
            $result = $imageService->executeAgent($method, [
                'concepts' => $spatialConcepts
            ]);
            
            if ($result['status'] === 'success') {
                $results[$schema] = $result['result'];
            }
        } catch (\Exception $e) {
            Log::warning("Schema activation failed", [
                'schema' => $schema,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    return $results;
}
```

## Troubleshooting

### Common Issues

**1. Neo4j Connection Errors**
```bash
# Check if Neo4j is running
docker ps | grep neo4j

# Check Neo4j logs  
docker logs <neo4j_container_id>

# Verify environment variables
env | grep NEO4J
```

**2. YAML Loading Errors**
```php
// Enable detailed logging
Log::debug("YAML loading started", ['directory' => storage_path('soul/yaml')]);

// Check file permissions
$yamlFiles = glob(storage_path('soul/yaml/*.yml'));
foreach ($yamlFiles as $file) {
    if (!is_readable($file)) {
        Log::error("Cannot read YAML file", ['file' => $file]);
    }
}
```

**3. Agent Execution Failures**
```php
// Check agent registration
$mindService = app(MindService::class);
$stats = $mindService->getSystemStatistics();
Log::info("Registered agent services", $stats['registered_agent_services']);

// Enable agent execution logging
config(['logging.channels.single.level' => 'debug']);
```

**4. Spreading Activation Performance**
```cypher
// Check graph connectivity
MATCH (c:Concept) 
WITH c, size((c)-[]->()) as out_degree, size((c)<-[]-()) as in_degree
WHERE out_degree = 0 AND in_degree = 0
RETURN count(c) as isolated_nodes;

// Monitor activation depth
MATCH path = (start:Concept)-[*1..5]-(end:Concept)
WHERE start.name IN $initial_concepts
RETURN length(path), count(*) as path_count
ORDER BY length(path);
```

This setup guide provides everything needed to initialize and use the SOUL Framework's Society of Mind architecture for sophisticated cognitive processing.