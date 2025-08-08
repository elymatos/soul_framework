# SOUL Framework Neo4j Graph Structure Documentation

## Table of Contents

1. [Overview & Introduction](#overview--introduction)
2. [Core Node Types](#core-node-types)
3. [Node Properties & Labels](#node-properties--labels)
4. [Relationship Types](#relationship-types)
5. [YAML-to-Graph Mapping](#yaml-to-graph-mapping)
6. [Execution Model](#execution-model)
7. [Performance Optimization](#performance-optimization)
8. [Examples & Patterns](#examples--patterns)
9. [Database Schema Maintenance](#database-schema-maintenance)
10. [Development Guidelines](#development-guidelines)

---

## Overview & Introduction

The SOUL Framework implements Marvin Minsky's **Society of Mind** architecture with Fillmore's **Frame Semantics** using a sophisticated Neo4j graph database structure. This dual-representation approach combines:

- **Graph Representation**: Concepts, relationships, and patterns stored as Neo4j nodes and edges
- **Agent Representation**: Executable cognitive services that process and manipulate the graph

The graph serves as both the **conceptual memory** of the system and the **coordination medium** for agent communication, implementing spreading activation, K-line learning, and frame-based reasoning.

### Key Design Principles

1. **Society of Mind**: Intelligence emerges from interactions between simple agents
2. **Dual Representation**: Every concept exists as both a graph node and potential agent behavior
3. **Spreading Activation**: Neural-inspired activation propagation through conceptual networks
4. **K-line Learning**: Successful cognitive patterns are recorded and strengthened
5. **Frame Semantics**: Knowledge represented as stereotyped situations with roles and elements

---

## Core Node Types

The SOUL Framework uses several distinct node types, each serving specific cognitive functions:

### 1. Concept Nodes

**Labels**: `[:Concept]` (plus additional semantic labels)

Basic conceptual entities that represent ideas, objects, or abstract notions in the cognitive system.

```cypher
CREATE (c:Concept:Entity {
  name: "PERSON",
  type: "entity",
  description: "Human individual with agency",
  domain: "social",
  created_at: datetime(),
  primitive: false
})
```

**Common Additional Labels**:
- `:Entity` - Physical or abstract entities
- `:ImageSchema` - Spatial and embodied cognition patterns
- `:SpatialPrimitive` - Basic spatial concepts
- `:Role` - Participant roles in frames

### 2. Frame Nodes

**Labels**: `[:Concept:Frame]`

Implement Fillmore's Frame Semantics - structured representations of stereotypical situations.

```cypher
CREATE (f:Concept:Frame {
  name: "COMMERCIAL_TRANSACTION",
  type: "frame",
  description: "Frame for buying and selling interactions",
  domain: "commerce",
  frame_elements: {
    buyer: "Entity acquiring goods or services",
    seller: "Entity providing goods or services", 
    goods: "Items or services being transacted",
    money: "Payment or consideration"
  },
  core_elements: ["buyer", "seller", "goods"],
  peripheral_elements: ["money"],
  created_at: datetime()
})
```

### 3. PROCEDURAL_AGENT Nodes

**Labels**: `[:PROCEDURAL_AGENT:Concept]`

Represent executable cognitive agents that can process inputs and manipulate the graph.

```cypher
CREATE (agent:PROCEDURAL_AGENT:Concept {
  name: "CommercialTransactionMatcher",
  code_reference: "FrameService::matchFrame",
  description: "Identifies commercial transaction patterns in input",
  priority: 1,
  service_class: "FrameService",
  method_name: "matchFrame",
  timeout_seconds: 30,
  created_at: datetime()
})
```

### 4. FrameElement Nodes

**Labels**: `[:FrameElement]`

Individual elements within frames, representing roles or components.

```cypher
CREATE (fe:FrameElement {
  name: "buyer",
  fe_type: "core",
  description: "Entity that acquires goods in transaction", 
  required: true,
  frame_name: "COMMERCIAL_TRANSACTION",
  created_at: datetime()
})
```

### 5. FrameInstance Nodes

**Labels**: `[:FrameInstance]`

Runtime instantiations of frames with specific bindings during cognitive processing.

```cypher
CREATE (fi:FrameInstance {
  instance_id: "frame_12345",
  frame_id: "COMMERCIAL_TRANSACTION", 
  session_id: "session_67890",
  status: "active",
  bindings: {
    buyer: "John",
    seller: "Mary",
    goods: "book"
  },
  confidence: 0.85,
  created_at: datetime()
})
```

### 6. KLine Nodes

**Labels**: `[:KLine]`

Implement Minsky's K-lines - learned patterns that record successful cognitive processing sequences.

```cypher
CREATE (kline:KLine {
  id: "kline_purchase_001",
  context: "commercial_transaction_recognition",
  activation_pattern: "{'concepts': ['PERSON', 'COMMERCIAL_TRANSACTION'], 'path': '...'}",
  usage_count: 3,
  success_rate: 0.9,
  strength: 0.7,
  last_used: datetime(),
  created_at: datetime()
})
```

### 7. ProcessingSession Nodes

**Labels**: `[:ProcessingSession]`

Track cognitive processing sessions for monitoring and cleanup.

```cypher
CREATE (ps:ProcessingSession {
  session_id: "session_12345",
  status: "active",
  started_at: datetime(),
  input_concepts: ["PERSON", "BUY"],
  statistics: {
    nodes_activated: 15,
    agents_executed: 3,
    processing_rounds: 2
  }
})
```

---

## Node Properties & Labels

### Standard Property Patterns

All node types follow consistent property patterns:

#### Required Properties
- `name`: Primary identifier (unique within type)
- `type`: Semantic type classification
- `created_at`: Timestamp of creation

#### Common Optional Properties
- `description`: Human-readable explanation
- `domain`: Conceptual domain (spatial, social, commerce, etc.)
- `primitive`: Boolean indicating basic/derived status
- `confidence`: Numeric confidence score (0.0-1.0)
- `metadata`: Additional structured data

### Label Hierarchy

Labels create semantic hierarchies:

```
:Concept (root category)
├── :Entity (concrete entities)
├── :Frame (frame structures)  
├── :Role (participant roles)
└── :ImageSchema (embodied concepts)
    ├── :SpatialPrimitive (basic spatial)
    └── :Container (containment schema)
```

---

## Relationship Types

The graph uses directed relationships to encode cognitive connections:

### Conceptual Relationships

#### IS_A (Taxonomic Hierarchy)
```cypher
(buyer:Concept)-[:IS_A {strength: 0.9}]->(person:Concept)
```

#### PART_OF (Compositional Structure)
```cypher
(room:Concept)-[:PART_OF {strength: 1.0}]->(building:Concept)
```

#### CAUSES (Causal Relationships)
```cypher
(force:Concept)-[:CAUSES {strength: 0.8}]->(motion:Concept)
```

#### SIMILAR_TO (Analogical Connections)
```cypher
(container:Concept)-[:SIMILAR_TO {strength: 0.6}]->(mind:Concept)
```

### Frame Relationships

#### HAS_FRAME_ELEMENT (Frame Structure)
```cypher
(transaction:Frame)-[:HAS_FRAME_ELEMENT]->(buyer:FrameElement)
```

#### INSTANTIATED_FROM (Frame Instantiation)
```cypher
(instance:FrameInstance)-[:INSTANTIATED_FROM]->(frame:Frame)
```

### Agent Relationships

#### ACTIVATES (K-line Activation)
```cypher
(kline:KLine)-[:ACTIVATES {strength: 0.8}]->(concept:Concept)
```

#### COMMUNICATES_WITH (Agent Interaction)
```cypher
(agent1:PROCEDURAL_AGENT)-[:COMMUNICATES_WITH]->(agent2:PROCEDURAL_AGENT)
```

### Session Relationships

#### BELONGS_TO_SESSION (Session Management)
```cypher
(instance:FrameInstance)-[:BELONGS_TO_SESSION]->(session:ProcessingSession)
```

---

## YAML-to-Graph Mapping

The framework loads conceptual knowledge from YAML files, transforming them into graph structures:

### YAML Concept → Concept Node

```yaml
concepts:
  - name: "COMMERCIAL_TRANSACTION"
    labels: ["Concept", "Frame"]
    properties:
      type: "frame"
      description: "Frame for buying and selling interactions"
      domain: "commerce"
```

Becomes:

```cypher
CREATE (c:Concept:Frame {
  name: "COMMERCIAL_TRANSACTION",
  type: "frame", 
  description: "Frame for buying and selling interactions",
  domain: "commerce",
  created_at: datetime()
})
```

### YAML Agent → PROCEDURAL_AGENT Node

```yaml
procedural_agents:
  - name: "CommercialTransactionMatcher"
    code_reference: "FrameService::matchFrame"
    description: "Identifies commercial transaction patterns"
    priority: 1
```

Becomes:

```cypher
CREATE (agent:PROCEDURAL_AGENT:Concept {
  name: "CommercialTransactionMatcher",
  code_reference: "FrameService::matchFrame",
  description: "Identifies commercial transaction patterns",
  priority: 1,
  created_at: datetime()
})
```

### YAML Relationship → Graph Edge

```yaml
relationships:
  - from: "BUYER"
    to: "PERSON"
    type: "IS_A"
    properties:
      strength: 0.9
      context: "commercial"
```

Becomes:

```cypher
MATCH (buyer:Concept {name: "BUYER"})
MATCH (person:Concept {name: "PERSON"})
CREATE (buyer)-[:IS_A {strength: 0.9, context: "commercial"}]->(person)
```

---

## Execution Model

The graph supports sophisticated cognitive processing through multiple execution patterns:

### 1. Spreading Activation

Starting from initial concepts, activation spreads through relationships:

```cypher
// Initial activation
MATCH (start:Concept {name: "PERSON"})
SET start.activation = 1.0

// Spread activation through relationships
MATCH (source:Concept)-[r]-(target:Concept)
WHERE source.activation > 0.1
SET target.activation = COALESCE(target.activation, 0) + 
    (source.activation * r.strength * 0.8)
```

### 2. Agent Discovery

Activated concepts trigger associated procedural agents:

```cypher
// Find agents activated by current concepts
MATCH (concept:Concept)-[:ACTIVATES]-(agent:PROCEDURAL_AGENT)
WHERE concept.activation > $threshold
RETURN agent ORDER BY agent.priority
```

### 3. Frame Instantiation

When frame patterns are matched, instances are created:

```cypher
CREATE (fi:FrameInstance {
  instance_id: $instance_id,
  frame_id: $frame_name,
  session_id: $session_id,
  bindings: $element_bindings,
  confidence: $match_confidence,
  created_at: datetime()
})
```

### 4. K-line Learning

Successful processing patterns are recorded:

```cypher
CREATE (kline:KLine {
  id: $kline_id,
  context: $processing_context,
  activation_pattern: $pattern_json,
  usage_count: 1,
  success_rate: 1.0,
  created_at: datetime()
})
```

### 5. Session Cleanup

After processing, temporary structures are cleaned:

```cypher
// Remove activation values
MATCH (c:Concept) REMOVE c.activation

// Archive or delete frame instances
MATCH (fi:FrameInstance {session_id: $session_id})
SET fi.status = "archived"
```

---

## Performance Optimization

The framework includes comprehensive optimization through constraints and indexes:

### Primary Constraints

```cypher
// Uniqueness constraints
CREATE CONSTRAINT concept_name_unique IF NOT EXISTS
FOR (c:Concept) REQUIRE c.name IS UNIQUE;

CREATE CONSTRAINT procedural_agent_code_ref_unique IF NOT EXISTS  
FOR (agent:PROCEDURAL_AGENT) REQUIRE agent.code_reference IS UNIQUE;

CREATE CONSTRAINT kline_id_unique IF NOT EXISTS
FOR (kline:KLine) REQUIRE kline.id IS UNIQUE;
```

### Performance Indexes

```cypher
// Concept lookup optimization
CREATE INDEX concept_type_index IF NOT EXISTS
FOR (c:Concept) ON (c.type);

// Agent priority ordering
CREATE INDEX procedural_agent_priority_index IF NOT EXISTS
FOR (agent:PROCEDURAL_AGENT) ON (agent.priority);

// K-line retrieval optimization
CREATE INDEX kline_context_index IF NOT EXISTS
FOR (kline:KLine) ON (kline.context);
```

### Full-text Search

```cypher
// Concept search
CREATE FULLTEXT INDEX concept_fulltext_index IF NOT EXISTS
FOR (c:Concept) ON EACH [c.name, c.description];

// Agent search  
CREATE FULLTEXT INDEX procedural_agent_fulltext_index IF NOT EXISTS
FOR (agent:PROCEDURAL_AGENT) ON EACH [agent.name, agent.description];
```

---

## Examples & Patterns

### Commercial Transaction Frame

Complete example of a commercial transaction represented in the graph:

```cypher
// Create the frame
CREATE (ct:Concept:Frame {
  name: "COMMERCIAL_TRANSACTION",
  type: "frame",
  domain: "commerce",
  description: "Frame for buying and selling interactions"
})

// Create frame elements
CREATE (buyer:FrameElement {
  name: "buyer", 
  fe_type: "core",
  frame_name: "COMMERCIAL_TRANSACTION"
})

CREATE (seller:FrameElement {
  name: "seller",
  fe_type: "core", 
  frame_name: "COMMERCIAL_TRANSACTION"
})

CREATE (goods:FrameElement {
  name: "goods",
  fe_type: "core",
  frame_name: "COMMERCIAL_TRANSACTION"  
})

// Connect elements to frame
CREATE (ct)-[:HAS_FRAME_ELEMENT]->(buyer)
CREATE (ct)-[:HAS_FRAME_ELEMENT]->(seller)
CREATE (ct)-[:HAS_FRAME_ELEMENT]->(goods)

// Create associated agent
CREATE (matcher:PROCEDURAL_AGENT:Concept {
  name: "CommercialTransactionMatcher",
  code_reference: "FrameService::matchFrame",
  priority: 1
})

// Link agent to frame
CREATE (ct)-[:ACTIVATES]->(matcher)
```

### Image Schema Pattern

CONTAINER schema with spatial relationships:

```cypher
// Create container schema
CREATE (container:Concept:ImageSchema:SpatialPrimitive {
  name: "CONTAINER",
  type: "image_schema",
  domain: "spatial",
  primitive: true
})

// Create related concepts
CREATE (interior:Concept {name: "INTERIOR", domain: "spatial"})
CREATE (exterior:Concept {name: "EXTERIOR", domain: "spatial"})
CREATE (boundary:Concept {name: "BOUNDARY", domain: "spatial"})

// Create spatial relationships
CREATE (container)-[:HAS_PART]->(interior)
CREATE (container)-[:HAS_PART]->(exterior)
CREATE (container)-[:HAS_PART]->(boundary)

// Create activation agent
CREATE (activator:PROCEDURAL_AGENT:Concept {
  name: "ContainerActivationAgent",
  code_reference: "ImageSchemaService::activateContainerSchema",
  priority: 1
})

CREATE (container)-[:ACTIVATES]->(activator)
```

### K-line Learning Pattern

Recording successful processing patterns:

```cypher
// Create K-line for commercial transaction recognition
CREATE (kline:KLine {
  id: "kline_commercial_001",
  context: "commercial_transaction_recognition",
  activation_pattern: JSON.stringify({
    initial_concepts: ["PERSON", "BUY", "OBJECT"],
    activated_path: ["PERSON", "BUYER", "COMMERCIAL_TRANSACTION"],
    agents_used: ["FrameService::matchFrame"],
    success_indicators: ["frame_matched", "elements_bound"]
  }),
  usage_count: 1,
  success_rate: 1.0,
  strength: 0.5
})

// Link to concepts that trigger this pattern
MATCH (person:Concept {name: "PERSON"})
MATCH (buy:Concept {name: "BUY"})
CREATE (kline)-[:ACTIVATES {strength: 0.8}]->(person)
CREATE (kline)-[:ACTIVATES {strength: 0.9}]->(buy)
```

### Agent Communication Pattern

Agents coordinating through the graph:

```cypher
// Create communicating agents
CREATE (frameAgent:PROCEDURAL_AGENT:Concept {
  name: "FrameMatcher",
  code_reference: "FrameService::matchFrame"
})

CREATE (imageAgent:PROCEDURAL_AGENT:Concept {
  name: "ImageSchemaActivator", 
  code_reference: "ImageSchemaService::activateContainerSchema"
})

// Create communication link
CREATE (frameAgent)-[:COMMUNICATES_WITH {
  message_type: "activation_request",
  priority: 2
}]->(imageAgent)
```

---

## Database Schema Maintenance

### Constraint Management

Apply all constraints using the provided Cypher file:

```bash
# Apply constraints and indexes
php artisan soul:neo4j-constraints

# Check existing constraints
php artisan soul:neo4j-constraints --check

# Drop and recreate (use carefully!)
php artisan soul:neo4j-constraints --drop
```

### Data Integrity Checks

Regular queries to verify data integrity:

```cypher
// Check for orphaned frame elements
MATCH (fe:FrameElement) 
WHERE NOT (fe)<-[:HAS_FRAME_ELEMENT]-()
RETURN count(fe) as orphaned_elements;

// Verify agent code references
MATCH (agent:PROCEDURAL_AGENT)
WHERE agent.code_reference IS NULL OR agent.code_reference = ''
RETURN count(agent) as invalid_agents;

// Check for duplicate concept names
MATCH (c:Concept)
WITH c.name as name, collect(c) as concepts
WHERE size(concepts) > 1
RETURN name, size(concepts) as duplicates;
```

### Performance Monitoring

Monitor system performance with these queries:

```cypher
// K-line usage patterns
MATCH (kline:KLine)
RETURN kline.context, kline.usage_count, kline.success_rate
ORDER BY kline.usage_count DESC LIMIT 10;

// Spreading activation patterns  
MATCH (c:Concept)-[r]-(related:Concept)
RETURN c.type, type(r), related.type, count(*)
ORDER BY count(*) DESC LIMIT 10;

// Agent activation frequency
MATCH (agent:PROCEDURAL_AGENT)
RETURN agent.name, agent.priority, 
       count{(agent)<-[:ACTIVATES]-()} as activation_count
ORDER BY activation_count DESC;
```

---

## Development Guidelines

### Adding New Node Types

1. **Define Labels**: Use semantic label hierarchies
2. **Create Constraints**: Ensure uniqueness and data integrity
3. **Add Indexes**: Optimize common query patterns
4. **Update Documentation**: Document properties and relationships

### Creating Relationships

1. **Use Semantic Names**: Relationship types should be self-explanatory
2. **Include Properties**: Add strength, context, and metadata
3. **Consider Direction**: Ensure consistent directionality
4. **Index When Needed**: Add indexes for frequently queried relationships

### YAML Integration

1. **Follow Standards**: Use established YAML structure patterns
2. **Validate Data**: Implement validation rules for YAML content
3. **Handle Errors**: Provide clear error messages for invalid data
4. **Test Thoroughly**: Verify YAML-to-graph transformation

### Performance Considerations

1. **Use Prepared Statements**: Parameterize Cypher queries
2. **Batch Operations**: Group related operations for efficiency
3. **Monitor Memory**: Watch for memory usage during large operations
4. **Cache Results**: Cache frequently accessed patterns

### Error Handling

1. **Use SOUL Exceptions**: Leverage the rich exception hierarchy
2. **Log Comprehensively**: Include context in error logs  
3. **Fail Fast**: Detect and report errors early
4. **Clean Up**: Ensure proper cleanup on failures

---

## Conclusion

The SOUL Framework's Neo4j graph structure provides a sophisticated foundation for cognitive AI applications. By combining conceptual representation, frame semantics, agent coordination, and learning mechanisms, it creates a powerful platform for implementing Society of Mind principles in practical systems.

The dual representation approach—where concepts exist both as graph nodes and executable agent behaviors—enables rich cognitive processing while maintaining performance and scalability. The comprehensive constraint and index system ensures data integrity and optimal query performance.

This graph structure serves as both a knowledge repository and a coordination medium, enabling the emergence of intelligent behavior from the interaction of simple components—the essence of Minsky's Society of Mind vision.