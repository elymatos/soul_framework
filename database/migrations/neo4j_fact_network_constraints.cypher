// Neo4j Constraints and Indexes for Triplet Fact Network System
// Extension to SOUL Framework for semantic fact representation
// Execute these commands in Neo4j Browser or via Cypher-Shell

// ============================================================================
// Fact Network Node Constraints
// ============================================================================

// FactNode constraints - ensure unique fact IDs
CREATE CONSTRAINT fact_node_id_unique IF NOT EXISTS
FOR (f:FactNode) REQUIRE f.id IS UNIQUE;

// FactNode required properties
CREATE CONSTRAINT fact_node_id_not_null IF NOT EXISTS
FOR (f:FactNode) REQUIRE f.id IS NOT NULL;

CREATE CONSTRAINT fact_node_statement_not_null IF NOT EXISTS
FOR (f:FactNode) REQUIRE f.statement IS NOT NULL;

// Enhanced Concept constraints for fact network
CREATE CONSTRAINT concept_fact_role_valid IF NOT EXISTS
FOR (c:Concept) REQUIRE c.fact_roles IS NULL OR size(c.fact_roles) >= 0;

// ============================================================================
// Fact Network Relationship Constraints
// ============================================================================

// INVOLVES_CONCEPT relationship constraints
CREATE CONSTRAINT involves_concept_role_not_null IF NOT EXISTS
FOR ()-[r:INVOLVES_CONCEPT]-() REQUIRE r.role IS NOT NULL;

// Valid roles constraint (using property existence check)
CREATE CONSTRAINT involves_concept_role_valid IF NOT EXISTS
FOR ()-[r:INVOLVES_CONCEPT]-() REQUIRE r.role IN ['subject', 'predicate', 'object', 'modifier', 'temporal', 'spatial', 'causal', 'instrumental'];

// Sequence constraints for ordered triplets
CREATE CONSTRAINT involves_concept_sequence_valid IF NOT EXISTS
FOR ()-[r:INVOLVES_CONCEPT]-() REQUIRE r.sequence IS NULL OR (r.sequence >= 1 AND r.sequence <= 10);

// ============================================================================
// Performance Indexes for Fact Network
// ============================================================================

// FactNode indexes for fast retrieval
CREATE INDEX fact_node_statement_index IF NOT EXISTS
FOR (f:FactNode) ON (f.statement);

CREATE INDEX fact_node_created_at_index IF NOT EXISTS
FOR (f:FactNode) ON (f.created_at);

CREATE INDEX fact_node_confidence_index IF NOT EXISTS
FOR (f:FactNode) ON (f.confidence);

CREATE INDEX fact_node_domain_index IF NOT EXISTS
FOR (f:FactNode) ON (f.domain);

CREATE INDEX fact_node_source_index IF NOT EXISTS
FOR (f:FactNode) ON (f.source);

CREATE INDEX fact_node_verified_index IF NOT EXISTS
FOR (f:FactNode) ON (f.verified);

// Enhanced Concept indexes for fact network
CREATE INDEX concept_is_primitive_index IF NOT EXISTS
FOR (c:Concept) ON (c.is_primitive);

CREATE INDEX concept_fact_frequency_index IF NOT EXISTS
FOR (c:Concept) ON (c.fact_frequency);

CREATE INDEX concept_semantic_category_index IF NOT EXISTS
FOR (c:Concept) ON (c.semantic_category);

// ============================================================================
// Fact Network Relationship Indexes
// ============================================================================

// INVOLVES_CONCEPT relationship indexes
CREATE INDEX involves_concept_role_index IF NOT EXISTS
FOR ()-[r:INVOLVES_CONCEPT]-() ON (r.role);

CREATE INDEX involves_concept_sequence_index IF NOT EXISTS
FOR ()-[r:INVOLVES_CONCEPT]-() ON (r.sequence);

CREATE INDEX involves_concept_strength_index IF NOT EXISTS
FOR ()-[r:INVOLVES_CONCEPT]-() ON (r.strength);

CREATE INDEX involves_concept_created_at_index IF NOT EXISTS
FOR ()-[r:INVOLVES_CONCEPT]-() ON (r.created_at);

// Temporal and spatial context relationship indexes
CREATE INDEX temporal_context_index IF NOT EXISTS
FOR ()-[r:TEMPORAL_CONTEXT]-() ON (r.created_at);

CREATE INDEX spatial_context_index IF NOT EXISTS
FOR ()-[r:SPATIAL_CONTEXT]-() ON (r.created_at);

CREATE INDEX causal_context_index IF NOT EXISTS
FOR ()-[r:CAUSAL_CONTEXT]-() ON (r.strength);

CREATE INDEX modal_context_index IF NOT EXISTS
FOR ()-[r:MODAL_CONTEXT]-() ON (r.modality);

// ============================================================================
// Full-Text Search Indexes for Fact Network
// ============================================================================

// Full-text search on fact statements and descriptions
CREATE FULLTEXT INDEX fact_node_fulltext_index IF NOT EXISTS
FOR (f:FactNode) ON EACH [f.statement, f.description, f.tags];

// Enhanced concept full-text search including semantic categories
CREATE FULLTEXT INDEX concept_semantic_fulltext_index IF NOT EXISTS
FOR (c:Concept) ON EACH [c.name, c.description, c.semantic_category, c.synonyms];

// ============================================================================
// Data Integrity Validation Queries
// ============================================================================

// Verify each FactNode has exactly 3 core concepts (subject, predicate, object)
// MATCH (f:FactNode)
// OPTIONAL MATCH (f)-[r:INVOLVES_CONCEPT]->(c:Concept)
// WHERE r.role IN ['subject', 'predicate', 'object']
// WITH f, count(r) as core_concepts
// WHERE core_concepts <> 3
// RETURN f.id, f.statement, core_concepts;

// Verify no orphaned FactNodes
// MATCH (f:FactNode) WHERE NOT (f)-[:INVOLVES_CONCEPT]->(:Concept) RETURN count(f);

// Check for concepts without semantic categories
// MATCH (c:Concept) WHERE c.semantic_category IS NULL RETURN count(c);

// Verify fact statement consistency
// MATCH (f:FactNode)-[r:INVOLVES_CONCEPT]->(c:Concept)
// WHERE r.role = 'subject'
// WITH f, collect(c.name) as subjects
// WHERE size(subjects) <> 1
// RETURN f.id, f.statement, subjects;

// ============================================================================
// Performance Monitoring Queries for Fact Network
// ============================================================================

// Monitor fact creation patterns by domain
// MATCH (f:FactNode) RETURN f.domain, count(f) as fact_count ORDER BY fact_count DESC;

// Monitor concept usage in facts by role
// MATCH (c:Concept)<-[r:INVOLVES_CONCEPT]-(f:FactNode)
// RETURN c.name, r.role, count(f) as usage_count
// ORDER BY usage_count DESC LIMIT 20;

// Monitor most frequent triplet patterns
// MATCH (f:FactNode)-[r1:INVOLVES_CONCEPT {role: 'subject'}]->(s:Concept),
//       (f)-[r2:INVOLVES_CONCEPT {role: 'predicate'}]->(p:Concept),
//       (f)-[r3:INVOLVES_CONCEPT {role: 'object'}]->(o:Concept)
// RETURN s.name, p.name, o.name, count(f) as frequency
// ORDER BY frequency DESC LIMIT 10;

// Monitor fact confidence distribution
// MATCH (f:FactNode) RETURN f.confidence, count(f) as count ORDER BY f.confidence DESC;

// ============================================================================
// Cleanup and Maintenance Queries
// ============================================================================

// Clean up orphaned relationships
// MATCH ()-[r:INVOLVES_CONCEPT]->()
// WHERE NOT EXISTS { MATCH (f:FactNode)-[r]->() }
// DELETE r;

// Update concept fact frequency counts
// MATCH (c:Concept)
// OPTIONAL MATCH (c)<-[:INVOLVES_CONCEPT]-(f:FactNode)
// WITH c, count(f) as freq
// SET c.fact_frequency = freq;

// Recalculate fact confidence based on concept primitive status
// MATCH (f:FactNode)-[:INVOLVES_CONCEPT]->(c:Concept)
// WITH f, avg(CASE WHEN c.is_primitive THEN 1.0 ELSE 0.8 END) as calculated_confidence
// SET f.calculated_confidence = calculated_confidence;

// ============================================================================
// Schema Validation
// ============================================================================

// Verify all new constraints and indexes are created
// Run after executing the above commands:
//
// SHOW CONSTRAINTS;
// SHOW INDEXES;
//
// Expected additional constraints: 6-8 new constraints
// Expected additional indexes: 12-15 new indexes