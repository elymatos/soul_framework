// Neo4j Constraints and Indexes for SOUL Framework
// This file contains all necessary constraints and indexes for optimal performance
// Execute these commands in Neo4j Browser or via Cypher-Shell

// ============================================================================
// SOUL Framework Core Constraints
// ============================================================================

// Concept nodes - ensure unique names within types
CREATE CONSTRAINT concept_name_unique IF NOT EXISTS
FOR (c:Concept) REQUIRE c.name IS UNIQUE;

// PROCEDURAL_AGENT nodes - ensure unique code references
CREATE CONSTRAINT procedural_agent_code_ref_unique IF NOT EXISTS
FOR (agent:PROCEDURAL_AGENT) REQUIRE agent.code_reference IS UNIQUE;

// K-Line nodes - ensure unique IDs
CREATE CONSTRAINT kline_id_unique IF NOT EXISTS
FOR (kline:KLine) REQUIRE kline.id IS UNIQUE;

// Frame Instance nodes - ensure unique instance IDs
CREATE CONSTRAINT frame_instance_id_unique IF NOT EXISTS
FOR (fi:FrameInstance) REQUIRE fi.instance_id IS UNIQUE;

// Processing Session nodes - ensure unique session IDs
CREATE CONSTRAINT processing_session_id_unique IF NOT EXISTS
FOR (ps:ProcessingSession) REQUIRE ps.session_id IS UNIQUE;

// ============================================================================
// Required Properties Constraints
// ============================================================================

// Ensure critical properties are not null
CREATE CONSTRAINT concept_name_not_null IF NOT EXISTS
FOR (c:Concept) REQUIRE c.name IS NOT NULL;

CREATE CONSTRAINT procedural_agent_name_not_null IF NOT EXISTS
FOR (agent:PROCEDURAL_AGENT) REQUIRE agent.name IS NOT NULL;

CREATE CONSTRAINT procedural_agent_code_ref_not_null IF NOT EXISTS
FOR (agent:PROCEDURAL_AGENT) REQUIRE agent.code_reference IS NOT NULL;

CREATE CONSTRAINT kline_context_not_null IF NOT EXISTS
FOR (kline:KLine) REQUIRE kline.context IS NOT NULL;

CREATE CONSTRAINT frame_instance_id_not_null IF NOT EXISTS
FOR (fi:FrameInstance) REQUIRE fi.instance_id IS NOT NULL;

// ============================================================================
// Performance Indexes
// ============================================================================

// Concept indexes for fast lookups
CREATE INDEX concept_type_index IF NOT EXISTS
FOR (c:Concept) ON (c.type);

CREATE INDEX concept_created_at_index IF NOT EXISTS
FOR (c:Concept) ON (c.created_at);

// PROCEDURAL_AGENT indexes
CREATE INDEX procedural_agent_priority_index IF NOT EXISTS
FOR (agent:PROCEDURAL_AGENT) ON (agent.priority);

CREATE INDEX procedural_agent_name_index IF NOT EXISTS
FOR (agent:PROCEDURAL_AGENT) ON (agent.name);

// K-Line indexes for learning and retrieval
CREATE INDEX kline_context_index IF NOT EXISTS
FOR (kline:KLine) ON (kline.context);

CREATE INDEX kline_usage_count_index IF NOT EXISTS
FOR (kline:KLine) ON (kline.usage_count);

CREATE INDEX kline_success_rate_index IF NOT EXISTS
FOR (kline:KLine) ON (kline.success_rate);

CREATE INDEX kline_last_used_index IF NOT EXISTS
FOR (kline:KLine) ON (kline.last_used);

// Frame Instance indexes for session management
CREATE INDEX frame_instance_session_id_index IF NOT EXISTS
FOR (fi:FrameInstance) ON (fi.session_id);

CREATE INDEX frame_instance_frame_id_index IF NOT EXISTS
FOR (fi:FrameInstance) ON (fi.frame_id);

CREATE INDEX frame_instance_type_index IF NOT EXISTS
FOR (fi:FrameInstance) ON (fi.type);

CREATE INDEX frame_instance_status_index IF NOT EXISTS
FOR (fi:FrameInstance) ON (fi.status);

CREATE INDEX frame_instance_created_at_index IF NOT EXISTS
FOR (fi:FrameInstance) ON (fi.created_at);

// Frame Element indexes
CREATE INDEX frame_element_name_index IF NOT EXISTS
FOR (fe:FrameElement) ON (fe.name);

CREATE INDEX frame_element_fe_type_index IF NOT EXISTS
FOR (fe:FrameElement) ON (fe.fe_type);

CREATE INDEX frame_element_required_index IF NOT EXISTS
FOR (fe:FrameElement) ON (fe.required);

// Processing Session indexes
CREATE INDEX processing_session_started_at_index IF NOT EXISTS
FOR (ps:ProcessingSession) ON (ps.started_at);

CREATE INDEX processing_session_status_index IF NOT EXISTS
FOR (ps:ProcessingSession) ON (ps.status);

// ============================================================================
// Relationship Type Indexes (Neo4j 4.3+)
// ============================================================================

// Activation relationships for K-Lines
CREATE INDEX activates_relationship_strength_index IF NOT EXISTS
FOR ()-[r:ACTIVATES]-() ON (r.strength);

// Frame element relationships
CREATE INDEX has_frame_element_index IF NOT EXISTS
FOR ()-[r:HAS_FRAME_ELEMENT]-() ON (r.created_at);

// Instance communication relationships
CREATE INDEX communicates_with_index IF NOT EXISTS
FOR ()-[r:COMMUNICATES_WITH]-() ON (r.created_at);

// Conceptual relationships
CREATE INDEX is_a_relationship_index IF NOT EXISTS
FOR ()-[r:IS_A]-() ON (r.strength);

CREATE INDEX part_of_relationship_index IF NOT EXISTS
FOR ()-[r:PART_OF]-() ON (r.strength);

CREATE INDEX causes_relationship_index IF NOT EXISTS
FOR ()-[r:CAUSES]-() ON (r.strength);

CREATE INDEX similar_to_relationship_index IF NOT EXISTS
FOR ()-[r:SIMILAR_TO]-() ON (r.strength);

// ============================================================================
// Full-Text Search Indexes
// ============================================================================

// Full-text search on concept names and descriptions
CREATE FULLTEXT INDEX concept_fulltext_index IF NOT EXISTS
FOR (c:Concept) ON EACH [c.name, c.description];

// Full-text search on procedural agent names and descriptions
CREATE FULLTEXT INDEX procedural_agent_fulltext_index IF NOT EXISTS
FOR (agent:PROCEDURAL_AGENT) ON EACH [agent.name, agent.description];

// Full-text search on K-Line contexts
CREATE FULLTEXT INDEX kline_fulltext_index IF NOT EXISTS
FOR (kline:KLine) ON EACH [kline.context];

// ============================================================================
// Schema Information Query
// ============================================================================

// Query to verify all constraints and indexes are created
// Run this after executing the above commands:
//
// SHOW CONSTRAINTS;
// SHOW INDEXES;
//
// Expected constraints: 10-15 constraints
// Expected indexes: 20-25 indexes

// ============================================================================
// Data Integrity Checks
// ============================================================================

// Verify no orphaned frame elements
// MATCH (fe:FrameElement) WHERE NOT (fe)<-[:HAS_FRAME_ELEMENT]-() RETURN count(fe);

// Verify all procedural agents have valid code references
// MATCH (agent:PROCEDURAL_AGENT) WHERE agent.code_reference IS NULL OR agent.code_reference = '' RETURN count(agent);

// Check for duplicate concept names
// MATCH (c:Concept) WITH c.name as name, collect(c) as concepts WHERE size(concepts) > 1 RETURN name, size(concepts);

// ============================================================================
// Performance Monitoring Queries
// ============================================================================

// Monitor K-Line usage patterns
// MATCH (kline:KLine) RETURN kline.context, kline.usage_count, kline.success_rate ORDER BY kline.usage_count DESC LIMIT 10;

// Monitor spreading activation patterns
// MATCH (c:Concept)-[r]-(related:Concept) RETURN c.type, type(r), related.type, count(*) ORDER BY count(*) DESC;

// Monitor procedural agent activation
// MATCH (agent:PROCEDURAL_AGENT) RETURN agent.name, agent.priority, count{(agent)<-[:ACTIVATES]-()} as activation_count ORDER BY activation_count DESC;