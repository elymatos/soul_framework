// ============================================================================
// Cortical Column-Based Cognitive Framework - Neo4j Schema Definition
// ============================================================================
// This schema defines the graph database structure for cortical columns,
// neurons, and their relationships following Lamb's neurocognitive linguistics
// and the three-layer cortical column architecture.
//
// Date: 2025-09-23
// Phase: 1.4 - Database Schema Design
// ============================================================================

// ----------------------------------------------------------------------------
// NODE CONSTRAINTS AND PROPERTIES
// ----------------------------------------------------------------------------

// CorticalColumn Node - Represents a complete cortical column (cognitive unit)
// Properties:
//   - network_id: Reference to MariaDB cortical_networks.id (REQUIRED, UNIQUE)
//   - name: Human-readable column name (REQUIRED)
//   - column_type: Type of column (concept, frame, schema, etc.)
//   - created_at: Timestamp of creation
//   - status: Column status (active, inactive, archived)

CREATE CONSTRAINT cortical_column_network_id IF NOT EXISTS
FOR (c:CorticalColumn)
REQUIRE c.network_id IS UNIQUE;

CREATE CONSTRAINT cortical_column_name IF NOT EXISTS
FOR (c:CorticalColumn)
REQUIRE c.name IS NOT NULL;

// Neuron Node - Individual neurons within cortical columns
// Properties:
//   - name: Unique neuron identifier (REQUIRED)
//   - layer: Cortical layer (4, 23, or 5) (REQUIRED)
//   - column_id: Parent column network_id (REQUIRED)
//   - activation_level: Current activation (0.0 to 1.0, default 0.0)
//   - threshold: Activation threshold (0.0 to 1.0, default 0.5)
//   - neuron_type: Type (input, processing, output, cardinal)
//   - created_at: Timestamp of creation

CREATE CONSTRAINT neuron_name IF NOT EXISTS
FOR (n:Neuron)
REQUIRE n.name IS UNIQUE;

CREATE CONSTRAINT neuron_layer IF NOT EXISTS
FOR (n:Neuron)
REQUIRE n.layer IS NOT NULL;

CREATE CONSTRAINT neuron_column_id IF NOT EXISTS
FOR (n:Neuron)
REQUIRE n.column_id IS NOT NULL;

// ----------------------------------------------------------------------------
// INDEXES FOR QUERY PERFORMANCE
// ----------------------------------------------------------------------------

// Index on CorticalColumn network_id for fast lookups
CREATE INDEX cortical_column_network_idx IF NOT EXISTS
FOR (c:CorticalColumn)
ON (c.network_id);

// Index on CorticalColumn name for name-based searches
CREATE INDEX cortical_column_name_idx IF NOT EXISTS
FOR (c:CorticalColumn)
ON (c.name);

// Index on Neuron column_id for column-neuron queries
CREATE INDEX neuron_column_idx IF NOT EXISTS
FOR (n:Neuron)
ON (n.column_id);

// Index on Neuron layer for layer-specific queries
CREATE INDEX neuron_layer_idx IF NOT EXISTS
FOR (n:Neuron)
ON (n.layer);

// Index on Neuron activation_level for activation queries
CREATE INDEX neuron_activation_idx IF NOT EXISTS
FOR (n:Neuron)
ON (n.activation_level);

// Composite index for column + layer queries
CREATE INDEX neuron_column_layer_idx IF NOT EXISTS
FOR (n:Neuron)
ON (n.column_id, n.layer);

// ----------------------------------------------------------------------------
// RELATIONSHIP TYPES AND SEMANTICS
// ----------------------------------------------------------------------------

// Column-Neuron Relationships
// ----------------------------

// (:CorticalColumn)-[:HAS_NEURON]->(:Neuron)
// - Represents neuron membership in a cortical column
// - Direction: Column -> Neuron
// - Properties: position (optional), created_at

// (:CorticalColumn)-[:HAS_CARDINAL]->(:Neuron)
// - Identifies the cardinal node (Layer 5) of a column
// - Cardinal nodes represent complete concepts and broadcast activation
// - Direction: Column -> Cardinal Neuron
// - Constraint: Only one cardinal node per column
// - Properties: created_at

// Inter-Column Relationships
// --------------------------

// (:CorticalColumn)-[:ACTIVATES_COLUMN]->(:CorticalColumn)
// - Concept-level activation between columns
// - Represents high-level cognitive connections
// - Direction: Source Column -> Target Column
// - Properties: weight (0.0-1.0), strength, created_at

// (:CorticalColumn)-[:INHIBITS_COLUMN]->(:CorticalColumn)
// - Competitive inhibition between alternative concepts
// - Prevents runaway activation
// - Direction: Source Column -> Target Column
// - Properties: weight (0.0-1.0), strength, created_at

// Neuron-Level Relationships
// --------------------------

// (:Neuron)-[:CONNECTS_TO]->(:Neuron)
// - General connection between neurons
// - Bidirectional information flow
// - Properties: weight (0.0-1.0), created_at

// (:Neuron)-[:ACTIVATES]->(:Neuron)
// - Excitatory connection (positive activation)
// - Increases target neuron activation
// - Properties: weight (0.0-1.0), strength, created_at

// (:Neuron)-[:INHIBITS]->(:Neuron)
// - Inhibitory connection (negative activation)
// - Decreases target neuron activation
// - Properties: weight (0.0-1.0), strength, created_at

// ----------------------------------------------------------------------------
// CORTICAL COLUMN LAYER ARCHITECTURE
// ----------------------------------------------------------------------------

// Layer 4 (Input Layer)
// - Receives signals from other columns
// - Properties: neuron_type = 'input'
// - Connectivity: Receives from Layer 5 of other columns
// - Function: External stimuli reception

// Layers 2/3 (Processing/Feature Layer)
// - Feature detection and relationship identification
// - Properties: neuron_type = 'processing'
// - Connectivity: Lateral connections, receives from Layer 4
// - Function: Frame elements, feature extraction

// Layer 5 (Output/Cardinal Layer)
// - Contains cardinal nodes representing complete concepts
// - Properties: neuron_type = 'output' or 'cardinal'
// - Connectivity: Broadcasts to Layer 4 of other columns
// - Function: Concept broadcasting, integration point

// ----------------------------------------------------------------------------
// EXAMPLE QUERIES
// ----------------------------------------------------------------------------

// Find all neurons in a cortical column
// MATCH (c:CorticalColumn {network_id: 1})-[:HAS_NEURON]->(n:Neuron)
// RETURN n

// Get the cardinal node of a column
// MATCH (c:CorticalColumn {network_id: 1})-[:HAS_CARDINAL]->(cardinal:Neuron)
// RETURN cardinal

// Find all columns activated by a specific column
// MATCH (c:CorticalColumn {name: 'CONTAINER'})-[:ACTIVATES_COLUMN]->(target:CorticalColumn)
// RETURN target

// Get active neurons above threshold
// MATCH (n:Neuron)
// WHERE n.column_id = 1 AND n.activation_level > 0.5
// RETURN n

// Find shortest activation path between two columns
// MATCH path = shortestPath(
//   (start:CorticalColumn {name: 'SOURCE'})-[:ACTIVATES_COLUMN*..5]-(end:CorticalColumn {name: 'TARGET'})
// )
// RETURN path

// Count neurons by layer in a column
// MATCH (c:CorticalColumn {network_id: 1})-[:HAS_NEURON]->(n:Neuron)
// RETURN n.layer, count(n) as neuron_count
// ORDER BY n.layer

// ----------------------------------------------------------------------------
// DATA VALIDATION RULES
// ----------------------------------------------------------------------------

// 1. Activation Level: Must be between 0.0 and 1.0
// 2. Layer Values: Must be 4, 23, or 5
// 3. Cardinal Node: Only Layer 5 neurons can be cardinal nodes
// 4. Column Membership: Every neuron must belong to exactly one column
// 5. Relationship Weights: Must be between 0.0 and 1.0
// 6. Network ID: Must reference existing cortical_networks.id in MariaDB

// ----------------------------------------------------------------------------
// INTEGRATION WITH MARIADB
// ----------------------------------------------------------------------------

// MariaDB Tables:
// - cortical_networks: Network metadata, configuration, status
// - cortical_metadata: Additional metadata
// - activation_sessions: Activation session tracking
// - activation_snapshots: Temporal state snapshots
// - network_snapshots: Network structure snapshots

// Hybrid Database Strategy:
// - Neo4j: Network topology, connections, activations (THIS SCHEMA)
// - MariaDB: Metadata, configurations, user data, snapshots

// Synchronization:
// - CorticalColumn.network_id = cortical_networks.id
// - Neuron.column_id = cortical_networks.id
// - Application layer ensures consistency between databases

// ============================================================================
// END OF SCHEMA DEFINITION
// ============================================================================