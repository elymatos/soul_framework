# Graph Editor - Neo4j Database Queries

The Graph Editor uses Neo4j as its backend database to store and manage graph data. This document provides comprehensive documentation for querying and interacting with the graph editor data through the Neo4j Browser.

## Database Access

### Neo4j Browser Access
- **URL:** http://localhost:7474/browser/
- **Username:** `neo4j`
- **Password:** `soul@202508`
- **Connect URL:** `neo4j://localhost:7687`

## Database Schema

### Node Structure
Graph editor nodes are stored with the label `:GraphEditorNode` and contain the following properties:

```cypher
(:GraphEditorNode {
    editorId: "node_12345",     // Unique identifier for frontend
    label: "Node Display Name", // Display name shown in UI
    name: "Node Display Name",  // Same as label (auto-generated)
    type: "frame|slot"         // Node type (frame or slot)
})
```

### Relationship Structure
Relationships between nodes use the type `:EDITOR_RELATION`:

```cypher
(:GraphEditorNode)-[:EDITOR_RELATION {
    editorId: "edge_12345",    // Unique identifier for frontend
    label: "relation_name"     // Optional relationship label
}]->(:GraphEditorNode)
```

## Basic Queries

### View All Nodes
```cypher
MATCH (n:GraphEditorNode) 
RETURN n
```
*Shows all graph editor nodes with their properties in visual format.*

### View All Relationships
```cypher
MATCH (a:GraphEditorNode)-[r:EDITOR_RELATION]->(b:GraphEditorNode)
RETURN a, r, b
```
*Displays all relationships between nodes with source and target nodes.*

### Complete Graph Visualization
```cypher
MATCH (n:GraphEditorNode)
OPTIONAL MATCH (n)-[r:EDITOR_RELATION]-(connected)
RETURN n, r, connected
```
*Shows the complete graph network including isolated nodes.*

## Data Inspection Queries

### Node Details Table
```cypher
MATCH (n:GraphEditorNode)
RETURN n.editorId as id, 
       n.label as label, 
       n.name as name, 
       n.type as type
ORDER BY n.label
```
*Returns node data in tabular format for detailed inspection.*

### Relationship Details Table
```cypher
MATCH (a:GraphEditorNode)-[r:EDITOR_RELATION]->(b:GraphEditorNode)
RETURN a.label as from_node, 
       r.label as relation_label, 
       b.label as to_node,
       r.editorId as relation_id
ORDER BY a.label, b.label
```
*Shows relationship details in an easy-to-read table format.*

### Node Properties Analysis
```cypher
MATCH (n:GraphEditorNode)
RETURN n.type as node_type, 
       count(n) as count,
       collect(n.label)[0..5] as sample_labels
ORDER BY count DESC
```
*Analyzes node distribution by type with sample labels.*

## Statistical Queries

### Graph Statistics Overview
```cypher
MATCH (n:GraphEditorNode)
WITH count(n) as nodeCount
MATCH ()-[r:EDITOR_RELATION]->()
RETURN nodeCount as total_nodes, 
       count(r) as total_relationships
```
*Provides basic statistics about the graph size.*

### Node Type Distribution
```cypher
MATCH (n:GraphEditorNode)
RETURN n.type as node_type, 
       count(n) as count
ORDER BY count DESC
```
*Shows distribution of frame vs slot nodes.*

### Relationship Label Statistics
```cypher
MATCH ()-[r:EDITOR_RELATION]->()
WHERE r.label IS NOT NULL AND r.label <> ""
RETURN r.label as relation_type, 
       count(r) as count
ORDER BY count DESC
```
*Analyzes the most commonly used relationship labels.*

### Node Connectivity Analysis
```cypher
MATCH (n:GraphEditorNode)
OPTIONAL MATCH (n)-[r:EDITOR_RELATION]-()
RETURN n.label as node_label,
       n.type as node_type,
       count(r) as total_connections
ORDER BY total_connections DESC
```
*Shows which nodes have the most connections.*

## Advanced Queries

### Find Isolated Nodes
```cypher
MATCH (n:GraphEditorNode)
WHERE NOT (n)-[:EDITOR_RELATION]-()
RETURN n.label as isolated_node, n.type as type
```
*Identifies nodes with no connections.*

### Find Nodes by Type
```cypher
// Find all frame nodes
MATCH (n:GraphEditorNode {type: "frame"})
RETURN n

// Find all slot nodes  
MATCH (n:GraphEditorNode {type: "slot"})
RETURN n
```
*Filters nodes by their type (frame or slot).*

### Find Specific Relationships
```cypher
// Find all relationships with a specific label
MATCH (a:GraphEditorNode)-[r:EDITOR_RELATION {label: "at"}]->(b:GraphEditorNode)
RETURN a, r, b

// Find relationships without labels
MATCH (a:GraphEditorNode)-[r:EDITOR_RELATION]->(b:GraphEditorNode)
WHERE r.label IS NULL OR r.label = ""
RETURN a.label as from_node, b.label as to_node
```
*Searches for relationships by their labels or finds unlabeled connections.*

### Path Analysis
```cypher
// Find shortest path between two nodes
MATCH (start:GraphEditorNode {label: "mode1"}), 
      (end:GraphEditorNode {label: "slot1"})
MATCH path = shortestPath((start)-[*]-(end))
RETURN path

// Find all paths of specific length
MATCH path = (n:GraphEditorNode)-[:EDITOR_RELATION*2]-(connected:GraphEditorNode)
RETURN path
LIMIT 10
```
*Analyzes paths and connections between nodes.*

## Data Maintenance Queries

### Verify Data Integrity
```cypher
// Check for orphaned relationships (should return 0)
MATCH ()-[r:EDITOR_RELATION]->()
WHERE NOT exists(()-[r]-(:GraphEditorNode))
RETURN count(r) as orphaned_relationships

// Check for nodes without required properties
MATCH (n:GraphEditorNode)
WHERE n.editorId IS NULL OR n.label IS NULL
RETURN n
```
*Validates data consistency and identifies potential issues.*

### Data Export Query
```cypher
// Export all graph data for backup
MATCH (n:GraphEditorNode)
OPTIONAL MATCH (n)-[r:EDITOR_RELATION]->(connected:GraphEditorNode)
RETURN {
    nodes: collect(DISTINCT {
        id: n.editorId,
        label: n.label,
        name: n.name,
        type: n.type
    }),
    edges: collect(DISTINCT {
        id: r.editorId,
        from: n.editorId,
        to: connected.editorId,
        label: r.label
    })
} as graph_data
```
*Exports complete graph data in JSON format for backup or analysis.*

## Visualization Tips

### Neo4j Browser Features
- **Graph View:** Click the graph icon to see visual representation
- **Table View:** Click the table icon for tabular data display
- **JSON View:** Click the code icon to see raw JSON data
- **Export:** Use the download icon to export results

### Visual Customization
```cypher
// Style nodes by type (run in Neo4j Browser)
MATCH (n:GraphEditorNode)
RETURN n
```
Then use the style panel to:
- Set different colors for frame vs slot nodes
- Adjust node sizes based on connection count
- Customize relationship arrow styles

### Performance Tips
- Use `LIMIT` clause for large datasets
- Add `PROFILE` or `EXPLAIN` before queries to analyze performance
- Use indexes on frequently queried properties:
  ```cypher
  CREATE INDEX ON :GraphEditorNode(editorId)
  CREATE INDEX ON :GraphEditorNode(type)
  ```

## Common Use Cases

### Development and Debugging
```cypher
// Quick health check
MATCH (n:GraphEditorNode)
RETURN count(n) as nodes, 
       count{(n)-[:EDITOR_RELATION]-()} as relationships

// Find specific node by editor ID
MATCH (n:GraphEditorNode {editorId: "node_12345"})
RETURN n
```

### Data Analysis
```cypher
// Analyze graph structure
MATCH (n:GraphEditorNode)
RETURN n.type as type,
       avg(size((n)-[:EDITOR_RELATION]-()))) as avg_connections,
       max(size((n)-[:EDITOR_RELATION]-()))) as max_connections,
       min(size((n)-[:EDITOR_RELATION]-()))) as min_connections
```

### Migration Verification
```cypher
// Verify migration completeness
MATCH (n:GraphEditorNode)
WHERE n.type IS NULL
RETURN count(n) as nodes_without_type

// Check for duplicate editor IDs
MATCH (n:GraphEditorNode)
WITH n.editorId as id, collect(n) as nodes
WHERE size(nodes) > 1
RETURN id, size(nodes) as duplicates
```

## Troubleshooting

### Connection Issues
If you cannot connect to Neo4j Browser:
1. Verify Neo4j container is running: `docker ps | grep neo4j`
2. Check port accessibility: `curl localhost:7474`
3. Verify credentials in Docker logs: `docker logs soul_framework-neo4j-1`

### Empty Results
If queries return no data:
1. Verify data migration: Run `php artisan graph-editor:test-data`
2. Check node labels: `CALL db.labels()` should include `GraphEditorNode`
3. Verify relationship types: `CALL db.relationshipTypes()` should include `EDITOR_RELATION`

### Performance Issues
For slow queries:
1. Add `PROFILE` before query to identify bottlenecks
2. Consider adding indexes on frequently queried properties
3. Use `LIMIT` to restrict result sets during development

---

This documentation covers the essential Neo4j queries for working with graph editor data. For more advanced Neo4j features, consult the [official Neo4j documentation](https://neo4j.com/docs/).