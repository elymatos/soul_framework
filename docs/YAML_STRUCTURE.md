# SOUL Framework - YAML Data Structure Documentation

## Overview

The SOUL Framework uses YAML files to define conceptual knowledge, procedural agents, and their relationships. This document provides comprehensive guidance on structuring YAML files for optimal cognitive processing.

## File Structure

### Basic YAML File Format

```yaml
# metadata (optional)
metadata:
  title: "Basic Concepts"
  version: "1.0"
  description: "Fundamental conceptual primitives"
  author: "SOUL Framework"
  created: "2024-01-01"

# concept definitions
concepts:
  - name: "CONCEPT_NAME"
    labels: ["Concept", "AdditionalLabel"]
    properties:
      type: "concept_type"
      description: "Detailed description"
      domain: "cognitive_domain"
    
# procedural agents  
procedural_agents:
  - name: "AgentName"
    code_reference: "ServiceName::methodName"
    description: "What this agent does"
    priority: 1

# relationships between concepts
relationships:
  - from: "SOURCE_CONCEPT"
    to: "TARGET_CONCEPT"  
    type: "RELATIONSHIP_TYPE"
    properties:
      strength: 0.8
      context: "specific_context"
```

## Concepts Section

### Basic Concept Definition

```yaml
concepts:
  - name: "PERSON"
    labels: ["Concept", "Entity"]
    properties:
      type: "entity"
      description: "Human individual with agency and consciousness"
      domain: "social"
      primitive: false
      image_schema: false
```

### Required Fields

- **name**: Unique identifier for the concept (UPPERCASE convention)
- **labels**: Neo4j node labels (array, always include "Concept")
- **properties**: Additional metadata as key-value pairs

### Optional Properties

- **type**: Concept classification (entity, frame, relation, primitive, etc.)
- **description**: Human-readable explanation
- **domain**: Cognitive domain (spatial, social, temporal, causal, etc.)
- **primitive**: Boolean indicating if this is a primitive concept
- **image_schema**: Boolean indicating if this is an image schema
- **csp_primitive**: Boolean indicating Conceptual Space primitive
- **meta_schema**: Meta-schema classification (ENTITY, STATE, PROCESS, CHANGE)

### Image Schema Concepts

```yaml
concepts:
  - name: "CONTAINER"
    labels: ["Concept", "ImageSchema", "SpatialPrimitive"]
    properties:
      type: "image_schema"
      description: "Spatial schema for containment relationships"
      domain: "spatial"
      primitive: true
      image_schema: true
      schema_type: "container"
      spatial_properties:
        - "bounded_region"
        - "interior"
        - "exterior" 
        - "boundary"

  - name: "PATH"
    labels: ["Concept", "ImageSchema", "SpatialPrimitive"]
    properties:
      type: "image_schema"
      description: "Schema for motion and trajectories"
      domain: "spatial"
      primitive: true
      image_schema: true
      schema_type: "path"
      path_elements:
        - "source"
        - "goal"
        - "trajectory"
```

### Frame Concepts

```yaml
concepts:
  - name: "COMMERCIAL_TRANSACTION"
    labels: ["Concept", "Frame"]
    properties:
      type: "frame"
      description: "Frame for buying and selling interactions"
      domain: "commerce"
      frame_elements:
        - "buyer"
        - "seller"
        - "goods"
        - "money"
        - "transaction_time"
      core_elements: ["buyer", "seller", "goods"]
      peripheral_elements: ["money", "transaction_time"]
      
  - name: "MOTION"
    labels: ["Concept", "Frame"]
    properties:
      type: "frame"
      description: "Frame for movement from one location to another"
      domain: "spatial"
      frame_elements:
        - "mover"
        - "source"
        - "path"
        - "goal"
        - "manner"
      core_elements: ["mover", "source", "goal"]
```

### CSP (Conceptual Space) Primitives

```yaml
concepts:
  - name: "EMOTION"
    labels: ["Concept", "CSPPrimitive"]
    properties:
      type: "csp_primitive"
      description: "Emotional state and affective processing"
      domain: "affective"
      csp_primitive: true
      dimensions:
        - "valence"
        - "arousal"
        - "intensity"

  - name: "SCALE"
    labels: ["Concept", "CSPPrimitive"]  
    properties:
      type: "csp_primitive"
      description: "Scalar quantities and magnitude relationships"
      domain: "quantitative"
      csp_primitive: true
      scale_properties:
        - "magnitude"
        - "direction" 
        - "units"
```

### Meta-Schema Concepts

```yaml
concepts:
  - name: "ENTITY"
    labels: ["Concept", "MetaSchema"]
    properties:
      type: "meta_schema"
      description: "Things that exist with persistent identity"
      domain: "ontological"
      meta_schema: "ENTITY"
      
  - name: "PROCESS"
    labels: ["Concept", "MetaSchema"]
    properties:
      type: "meta_schema" 
      description: "Dynamic sequences of events over time"
      domain: "temporal"
      meta_schema: "PROCESS"
```

## Procedural Agents Section

### Basic Agent Definition

```yaml
procedural_agents:
  - name: "FrameMatchingAgent"
    code_reference: "FrameService::matchFrame"
    description: "Matches input against frame patterns using confidence scoring"
    priority: 1
    timeout: 10
    retry_attempts: 2
    
  - name: "ContainerSchemaAgent"
    code_reference: "ImageSchemaService::activateContainerSchema"  
    description: "Activates CONTAINER image schema for spatial reasoning"
    priority: 2
    domain: "spatial"
```

### Required Fields

- **name**: Human-readable agent name
- **code_reference**: Service and method in format "ServiceName::methodName"
- **description**: What the agent does

### Optional Fields

- **priority**: Execution priority (lower numbers = higher priority)
- **timeout**: Maximum execution time in seconds
- **retry_attempts**: Number of retry attempts on failure
- **domain**: Cognitive domain for this agent
- **required_concepts**: List of concepts that must be activated

### Service References

Must match registered agent services:

- **FrameService**: Frame-related operations
  - `FrameService::matchFrame`
  - `FrameService::instantiateFrame`
  - `FrameService::bindFrameElements`
  - `FrameService::propagateConstraints`
  - `FrameService::resolveFrameConflicts`

- **ImageSchemaService**: Image schema operations
  - `ImageSchemaService::activateContainerSchema`
  - `ImageSchemaService::activatePathSchema`
  - `ImageSchemaService::activateForceSchema`
  - `ImageSchemaService::activateBalanceSchema`
  - `ImageSchemaService::projectImageSchema`

## Relationships Section

### Basic Relationship Definition

```yaml
relationships:
  - from: "PERSON"
    to: "ENTITY" 
    type: "IS_A"
    properties:
      strength: 1.0
      inheritance: true
      
  - from: "BUYER"
    to: "PERSON"
    type: "IS_A"
    properties:
      strength: 0.9
      context: "commercial"
```

### Required Fields

- **from**: Source concept name
- **to**: Target concept name  
- **type**: Relationship type

### Relationship Types

**Hierarchical Relationships**:
- `IS_A`: Taxonomic relationships (Person IS_A Entity)
- `PART_OF`: Compositional relationships (Wheel PART_OF Car)

**Semantic Relationships**:
- `SIMILAR_TO`: Conceptual similarity
- `OPPOSITE_OF`: Conceptual opposition
- `CAUSES`: Causal relationships
- `ENABLES`: Enablement relationships

**Frame Relationships**:
- `HAS_FRAME_ELEMENT`: Frame to element relationships
- `FILLS_ROLE`: Concept fills frame element role
- `EVOKES`: Concept evokes frame

**Schema Relationships**:
- `ACTIVATES`: Agent activates concept
- `PROJECTS_TO`: Schema projection relationships
- `MAPS_TO`: Metaphorical mappings

### Relationship Properties

```yaml
relationships:
  - from: "CONTAINER"
    to: "OBJECT"
    type: "CAN_CONTAIN"
    properties:
      strength: 0.8
      context: "spatial_containment"
      bidirectional: false
      constraints:
        - "size_compatible"
        - "physical_access"
```

## Example Files

### primitives.yml - Basic Primitives

```yaml
metadata:
  title: "SOUL Framework Primitives"
  version: "1.0"
  description: "Core primitive concepts for cognitive processing"

concepts:
  # Image Schema Primitives
  - name: "CONTAINER"
    labels: ["Concept", "ImageSchema", "SpatialPrimitive"]
    properties:
      type: "image_schema"
      description: "Bounded region with interior/exterior"
      domain: "spatial"
      primitive: true

  - name: "PATH"  
    labels: ["Concept", "ImageSchema", "SpatialPrimitive"]
    properties:
      type: "image_schema"
      description: "Trajectory from source to goal"
      domain: "spatial"
      primitive: true

  # CSP Primitives  
  - name: "EMOTION"
    labels: ["Concept", "CSPPrimitive"]
    properties:
      type: "csp_primitive"
      description: "Affective states and emotional processing"
      domain: "affective"
      primitive: true

  # Meta-schemas
  - name: "ENTITY"
    labels: ["Concept", "MetaSchema"]
    properties:
      type: "meta_schema"
      description: "Things with persistent identity"
      domain: "ontological"

procedural_agents:
  - name: "ContainerActivationAgent"
    code_reference: "ImageSchemaService::activateContainerSchema"
    description: "Activates container schema for spatial reasoning"
    priority: 1

relationships:
  - from: "CONTAINER"
    to: "ENTITY"
    type: "IS_A"
    properties:
      strength: 0.9
```

### frames.yml - Frame Definitions

```yaml
metadata:
  title: "SOUL Framework Frames"
  version: "1.0"
  description: "Frame-semantic knowledge structures"

concepts:
  - name: "COMMERCIAL_TRANSACTION"
    labels: ["Concept", "Frame"]
    properties:
      type: "frame"
      description: "Buying and selling interactions"
      domain: "commerce"
      frame_elements:
        buyer: 
          type: "core"
          description: "Entity acquiring goods"
        seller:
          type: "core"  
          description: "Entity providing goods"
        goods:
          type: "core"
          description: "Items being transacted"
        money:
          type: "peripheral"
          description: "Payment medium"

  - name: "MOTION"
    labels: ["Concept", "Frame"]
    properties:
      type: "frame"
      description: "Movement from source to goal"
      domain: "spatial"

procedural_agents:
  - name: "CommercialTransactionMatcher"
    code_reference: "FrameService::matchFrame"
    description: "Matches input to commercial transaction frame"
    priority: 1
    required_concepts: ["COMMERCIAL_TRANSACTION"]

  - name: "FrameInstantiator"
    code_reference: "FrameService::instantiateFrame"
    description: "Creates frame instances with element bindings"
    priority: 2

relationships:
  - from: "BUYER"
    to: "PERSON"
    type: "IS_A"
    properties:
      strength: 0.9
      
  - from: "SELLER"
    to: "PERSON" 
    type: "IS_A"
    properties:
      strength: 0.9
```

## Loading and Validation

### Configuration

Set YAML directory and validation mode in `config/soul.php`:

```php
'yaml' => [
    'base_directory' => storage_path('soul/yaml'),
    'auto_load_on_boot' => false,
    'validation_strict' => true,
],
```

### Loading Methods

```php
use App\Soul\Services\YamlLoaderService;

$yamlLoader = app(YamlLoaderService::class);

// Load all YAML files
$results = $yamlLoader->loadAllYamlFiles();

// Load specific file  
$result = $yamlLoader->loadYamlFile(storage_path('soul/yaml/primitives.yml'));
```

### Validation Rules

**Strict Validation** (recommended for development):
- Validates all required fields
- Checks field types and constraints
- Verifies relationship references
- Reports detailed error messages

**Permissive Validation** (for production):
- Allows unknown fields
- Skips optional validations
- Continues processing on minor errors

### Error Handling

```php
try {
    $result = $yamlLoader->loadYamlFile($filePath);
    if (!empty($result['errors'])) {
        foreach ($result['errors'] as $error) {
            Log::warning("YAML loading error", $error);
        }
    }
} catch (\Exception $e) {
    Log::error("YAML loading failed", [
        'file' => $filePath,
        'error' => $e->getMessage()
    ]);
}
```

## Best Practices

### File Organization

```
storage/soul/yaml/
├── primitives.yml          # Image schemas, CSP primitives, meta-schemas
├── frames/
│   ├── spatial.yml         # Spatial frames (MOTION, LOCATION)
│   ├── social.yml          # Social frames (CONVERSATION, RELATIONSHIP)  
│   └── commerce.yml        # Commercial frames (TRANSACTION, PURCHASE)
├── domains/
│   ├── medical.yml         # Domain-specific concepts
│   └── legal.yml
└── agents/
    ├── core_agents.yml     # Essential procedural agents
    └── specialized.yml     # Domain-specific agents
```

### Naming Conventions

- **Concepts**: UPPERCASE with underscores (COMMERCIAL_TRANSACTION)
- **Properties**: lowercase with underscores (frame_elements)
- **Agents**: PascalCase with descriptive suffix (FrameMatchingAgent)
- **Files**: lowercase with domain grouping (spatial_frames.yml)

### Documentation Standards

- Include comprehensive descriptions for all concepts
- Document frame elements and their roles
- Specify agent purposes and requirements
- Use consistent terminology across files

### Version Control

- Version all YAML files in git
- Include metadata with version information
- Tag releases for stable concept sets
- Document breaking changes in commit messages

### Performance Optimization

- Group related concepts in same files
- Use efficient relationship structures
- Avoid deeply nested hierarchies
- Balance file sizes (aim for 50-200 concepts per file)

### Quality Assurance

- Validate all files before deployment
- Test concept loading in development
- Monitor loading performance
- Review concept consistency across files

This YAML structure provides the foundation for defining rich conceptual knowledge that drives the Society of Mind cognitive architecture.