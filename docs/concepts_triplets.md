# Triplet-Based Fact Network System

## Overview

The Triplet-Based Fact Network System is an experimental extension to the SOUL Framework that implements semantic knowledge representation through triplet relationships. Each fact is represented as a core subject-predicate-object triplet with optional extended context through modifiers, temporal, spatial, and causal concepts.

## Core Concepts

### Triplet Structure

Every fact consists of a mandatory triplet:
- **Subject**: The entity performing the action or being described
- **Predicate**: The action, relationship, or state
- **Object**: The target entity, state, or value

### Extended Context (Optional)

Facts can include additional context through:
- **Modifiers**: Adjectives, adverbs, or qualifying concepts
- **Temporal**: Time-related concepts (past, present, future, specific times)
- **Spatial**: Location or spatial concepts
- **Causal**: Cause-effect relationships

### Example

**Basic Triplet**: "car is big"
- Subject: `car`
- Predicate: `is`
- Object: `big`

**Extended**: "car is very big currently"
- Subject: `car`
- Predicate: `is`
- Object: `big`
- Modifier: `very`
- Temporal: `currently`

## Database Schema

### Neo4j Nodes

#### FactNode
```cypher
(:FactNode {
    id: "fact_uuid",
    statement: "car is big",
    confidence: 0.9,
    verified: true,
    fact_type: "fact",
    priority: "medium",
    domain: "general",
    source: "observation",
    description: "Description text",
    tags: ["automotive", "size"],
    created_at: datetime(),
    updated_at: datetime(),
    concept_count: 3,
    has_modifiers: false,
    has_temporal: false,
    has_spatial: false,
    has_causal: false
})
```

#### Enhanced Concept Nodes
```cypher
(:Concept {
    name: "car",
    is_primitive: false,
    fact_frequency: 15,
    semantic_category: "object",
    created_at: datetime(),
    updated_at: datetime()
})
```

### Relationships

#### INVOLVES_CONCEPT
Links facts to their constituent concepts with role information:
```cypher
(:FactNode)-[:INVOLVES_CONCEPT {
    role: "subject|predicate|object|modifier|temporal|spatial|causal",
    sequence: 1,
    strength: 1.0,
    required: true|false,
    created_at: datetime()
}]->(:Concept)
```

## API Endpoints

### Fact Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/facts` | Main facts interface |
| POST | `/facts` | Create new fact |
| GET | `/facts/{id}` | View specific fact |
| PUT | `/facts/{id}` | Update fact |
| DELETE | `/facts/{id}` | Delete fact |
| POST | `/facts/browse` | Browse facts with filters |
| POST | `/facts/search` | Advanced fact search |

### Network and Visualization

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/facts/{id}/network` | Get fact network data |
| GET | `/facts/concepts/available` | Get available concepts |
| POST | `/facts/validate-triplet` | Validate triplet structure |
| GET | `/facts/statistics` | Get system statistics |

### Data Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/facts/export` | Export facts (JSON/CSV/Cypher) |
| POST | `/facts/import` | Import facts data |

## Usage Guide

### 1. Creating Facts

#### Via Web Interface
1. Navigate to `/facts/create`
2. Fill the core triplet (Subject, Predicate, Object)
3. Optionally add extended concepts
4. Set fact properties (confidence, domain, etc.)
5. Preview and create

#### Via API
```php
use App\Data\Facts\CreateFactData;
use App\Services\Facts\TripletFactService;

$factData = new CreateFactData(
    statement: 'car is big',
    subject_concept: 'car',
    predicate_concept: 'is',
    object_concept: 'big',
    confidence: 0.9,
    domain: 'general'
);

$factService = app(TripletFactService::class);
$result = $factService->createFact($factData);
```

### 2. Browsing Facts

#### Web Interface
- Navigate to `/facts` → Browse tab
- Use filters: domain, type, verification status, etc.
- View as list, grid, or network

#### Programmatic Access
```php
use App\Data\Facts\BrowseFactData;

$browseData = new BrowseFactData(
    search: 'car',
    domain: 'automotive',
    verification: 'verified',
    sort: 'confidence_high',
    limit: 20
);

$results = $factService->browseFacts($browseData);
```

### 3. Network Visualization

#### Interactive Graph
1. Go to `/facts` → Network tab
2. Load fact by ID or search
3. Use controls to:
   - Toggle physics simulation
   - Show/hide role labels
   - Adjust network depth
   - Export as image

#### Programmatic Network Access
```php
$networkData = $factService->getFactNetwork($factId, $depth = 2);
// Returns: ['nodes' => [...], 'links' => [...]]
```

### 4. Advanced Search

#### Search Parameters
```php
use App\Data\Facts\SearchFactData;

$searchData = new SearchFactData(
    statement_search: 'big',
    subject_concept: 'car',
    min_confidence: 0.7,
    verified: true,
    has_modifiers: true,
    sort_by: 'confidence',
    sort_direction: 'desc'
);

$results = $factService->searchFacts($searchData);
```

## Database Setup

### Install Neo4j Constraints

```bash
# Apply all constraints and indexes
php artisan facts:neo4j-constraints

# Check existing constraints
php artisan facts:neo4j-constraints --check

# Drop all constraints (caution!)
php artisan facts:neo4j-constraints --drop
```

### Manual Neo4j Setup

If needed, you can manually apply constraints:

```cypher
// Core constraints
CREATE CONSTRAINT fact_node_id_unique IF NOT EXISTS
FOR (f:FactNode) REQUIRE f.id IS UNIQUE;

CREATE CONSTRAINT involves_concept_role_not_null IF NOT EXISTS
FOR ()-[r:INVOLVES_CONCEPT]-() REQUIRE r.role IS NOT NULL;

// Performance indexes
CREATE INDEX fact_node_statement_index IF NOT EXISTS
FOR (f:FactNode) ON (f.statement);

CREATE INDEX involves_concept_role_index IF NOT EXISTS
FOR ()-[r:INVOLVES_CONCEPT]-() ON (r.role);
```

## Frontend Components

### JavaScript Modules

#### TripletNetworkVisualization
```javascript
import TripletNetworkVisualization from '/scripts/facts/TripletNetworkVisualization.js';

const network = new TripletNetworkVisualization('container-id', {
    height: '600px',
    physics: true,
    showRoleLabels: true,
    highlightTriplets: true
});

// Load fact network
await network.loadFactNetwork('fact_123', 2);

// Event handlers
network.on('selectFact', (fact, factId) => {
    console.log('Selected:', fact);
});
```

#### ConceptSelector
```javascript
import ConceptSelector from '/scripts/facts/ConceptSelector.js';

const selector = new ConceptSelector('selector-id', {
    role: 'subject',
    required: true,
    multiSelect: false,
    apiBaseUrl: '/facts'
});

// Event handlers
selector.on('conceptSelect', (concept, text, allConcepts) => {
    console.log('Selected concept:', concept);
});
```

## Configuration

### Environment Variables

```bash
# Neo4j Configuration (existing)
NEO4J_HOST=neo4j
NEO4J_PORT=7687
NEO4J_USER=neo4j
NEO4J_PASSWORD=secret

# Optional: Fact Network specific configs
FACTS_MAX_CONCEPTS_PER_FACT=10
FACTS_DEFAULT_CONFIDENCE=1.0
FACTS_AUTO_VERIFY=false
```

### Laravel Configuration

Add to `config/soul.php`:

```php
'facts' => [
    'max_triplet_depth' => 5,
    'default_confidence' => 1.0,
    'auto_verify_primitives' => true,
    'enable_concept_suggestions' => true,
    'network_cache_duration' => 300, // 5 minutes
],
```

## Data Export/Import

### Export Formats

#### JSON Export
```bash
curl "/facts/export?format=json" > facts.json
```

#### CSV Export
```bash
curl "/facts/export?format=csv" > facts.csv
```

#### Cypher Export
```bash
curl "/facts/export?format=cypher" > facts.cypher
```

### Import Process

1. Prepare data in supported format
2. Use API endpoint: `POST /facts/import`
3. Validate imported facts
4. Update concept statistics

## Performance Considerations

### Optimization Tips

1. **Concept Reuse**: Use existing concepts instead of creating new ones
2. **Batch Operations**: Group related facts for bulk creation
3. **Index Usage**: Leverage Neo4j indexes for search performance
4. **Network Depth**: Limit visualization depth for large networks

### Monitoring

```cypher
// Check fact distribution
MATCH (f:FactNode) 
RETURN f.domain, count(f) as fact_count 
ORDER BY fact_count DESC;

// Monitor concept usage
MATCH (c:Concept) 
RETURN c.name, c.fact_frequency 
ORDER BY c.fact_frequency DESC LIMIT 10;

// Check relationship patterns
MATCH ()-[r:INVOLVES_CONCEPT]->() 
RETURN r.role, count(r) as count 
ORDER BY count DESC;
```

## Troubleshooting

### Common Issues

#### 1. Constraint Errors
```bash
# Check constraint status
php artisan facts:neo4j-constraints --check

# Reapply if needed
php artisan facts:neo4j-constraints --force
```

#### 2. Network Visualization Not Loading
- Check browser console for JavaScript errors
- Verify vis-network library is loaded
- Ensure API endpoints are accessible

#### 3. Concept Selection Not Working
- Verify `/facts/concepts/available` endpoint
- Check network connectivity
- Clear browser cache

#### 4. Neo4j Connection Issues
- Verify Neo4j service is running
- Check connection credentials
- Test basic Neo4j connectivity

### Debugging

#### Enable Verbose Logging
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Neo4j query logging (in config)
'neo4j' => [
    'log_queries' => true,
    'log_level' => 'debug'
]
```

#### Test Basic Functionality
```php
// Test fact creation
$result = app(TripletFactService::class)->createFact($testData);

// Test Neo4j connection
$result = app(ClientInterface::class)->run('RETURN 1 as test');
```

## Integration with SOUL Framework

### Backup and Restoration

The original SOUL Framework is preserved in the backup branch:

```bash
# View backup
git checkout backup/soul-framework-stable-v1.0

# Return to facts system
git checkout main

# Reset to backup if needed
git reset --hard backup/soul-framework-stable-v1.0
```

### Coexistence

The fact network system is designed to coexist with the existing SOUL Framework:

- Uses same Neo4j database with different node types
- Shares concept infrastructure
- Independent routing (`/facts/*` vs `/soul/*`)
- Separate service layer

## Future Enhancements

### Planned Features

1. **Fact Reasoning**: Automatic inference of new facts
2. **Confidence Learning**: AI-based confidence adjustment
3. **Temporal Reasoning**: Time-based fact evolution
4. **Fact Clustering**: Automatic grouping of related facts
5. **Natural Language Processing**: Text-to-triplet conversion

### Extension Points

1. **Custom Concept Types**: Add domain-specific concept categories
2. **Relationship Types**: Extend beyond INVOLVES_CONCEPT
3. **Validation Rules**: Custom triplet validation logic
4. **Export Formats**: Additional export options
5. **Visualization Layouts**: Alternative network layouts

## Best Practices

### Fact Creation

1. **Use Primitive Concepts**: Prefer existing primitive concepts
2. **Clear Statements**: Write clear, unambiguous statements
3. **Appropriate Confidence**: Set realistic confidence levels
4. **Consistent Domains**: Use consistent domain categorization
5. **Rich Context**: Add relevant modifiers and context

### Network Design

1. **Balanced Networks**: Avoid overly dense or sparse networks
2. **Meaningful Relationships**: Ensure relationships add value
3. **Performance Limits**: Consider visualization performance
4. **Regular Cleanup**: Remove obsolete or incorrect facts

### Data Quality

1. **Verification Process**: Implement fact verification workflows
2. **Source Tracking**: Always record fact sources
3. **Regular Audits**: Periodically review fact accuracy
4. **Duplicate Detection**: Check for and merge duplicate facts

## License and Credits

This Triplet-Based Fact Network System is built as an experimental extension to the SOUL Framework, maintaining compatibility with the original MIT license and cognitive science foundations.

---

**Generated**: 2025-01-15
**Version**: 1.0
**Compatibility**: SOUL Framework 1.0+