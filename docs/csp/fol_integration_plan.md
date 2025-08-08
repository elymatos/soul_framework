# FOL Axioms Integration Plan for SOUL Framework

## Executive Summary

This comprehensive plan outlines the integration of 1400+ First-Order Logic (FOL) axioms from Gordon & Hobbs' "A Formal Theory of Commonsense Psychology" into the SOUL Framework's Neo4j graph database. The integration preserves logical structure while enabling dynamic reasoning through Society of Mind principles.

## Project Scope & Objectives

### Primary Objectives
- Convert FOL axioms to Neo4j graph representations
- Maintain semantic richness and logical relationships
- Enable dynamic reasoning through procedural agents
- Support defeasible reasoning and exception handling
- Implement frame-based psychological reasoning
- Create learning mechanisms via K-lines

### Success Criteria
- All 1400+ axioms represented in graph database
- 80%+ axioms triggering appropriate procedural agents
- Successful reasoning chains demonstrating psychological inference
- Performance supporting real-time cognitive processing
- Comprehensive test coverage validating logical correctness

## Architecture Overview

### Core Components

1. **LogicalAxiom Nodes**: First-class representation of FOL axioms
2. **Predicate Concepts**: Graph nodes representing FOL predicates
3. **Psychological Frames**: Complex axiom structures as frames
4. **Procedural Agents**: Executable logic implementing axiom reasoning
5. **K-line Learning**: Pattern capture for successful reasoning chains
6. **Exception Handling**: Defeasible reasoning implementation

### Data Flow
```
JSON Axioms → Parser → Graph Nodes → Agent Triggers → Reasoning Chains → K-line Storage
```

## Phase 1: Foundation & Infrastructure (Weeks 1-3)

### 1.1 Database Schema Extensions

**File**: `database/neo4j/constraints/fol_axioms_constraints.cypher`
```cypher
// LogicalAxiom node constraints
CREATE CONSTRAINT logical_axiom_id_unique IF NOT EXISTS
FOR (axiom:LogicalAxiom) REQUIRE axiom.axiom_id IS UNIQUE;

CREATE CONSTRAINT logical_axiom_name_unique IF NOT EXISTS  
FOR (axiom:LogicalAxiom) REQUIRE axiom.name IS UNIQUE;

// Predicate concept constraints
CREATE CONSTRAINT predicate_concept_name_unique IF NOT EXISTS
FOR (pc:PsychologicalPredicate) REQUIRE pc.name IS UNIQUE;

// Logical frame constraints  
CREATE CONSTRAINT logical_frame_name_unique IF NOT EXISTS
FOR (lf:LogicalFrame) REQUIRE lf.name IS UNIQUE;
```

**File**: `database/neo4j/indexes/fol_axioms_indexes.cypher`
```cypher
// Performance indexes
CREATE INDEX logical_axiom_pattern_index IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON (axiom.pattern);

CREATE INDEX logical_axiom_complexity_index IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON (axiom.complexity);

CREATE INDEX predicate_arity_index IF NOT EXISTS
FOR (pc:PsychologicalPredicate) ON (pc.arity);

CREATE INDEX defeasible_axiom_index IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON (axiom.defeasible);

// Full-text search
CREATE FULLTEXT INDEX axiom_content_fulltext IF NOT EXISTS
FOR (axiom:LogicalAxiom) ON EACH [axiom.english, axiom.description];
```

### 1.2 Core Data Models

**File**: `app/Models/Graph/LogicalAxiom.php`
```php
<?php

namespace App\Models\Graph;

use App\Models\Graph\BaseGraphModel;

class LogicalAxiom extends BaseGraphModel
{
    protected $label = 'LogicalAxiom';
    
    protected $fillable = [
        'name', 'axiom_id', 'chapter', 'chapter_title', 'section', 
        'page', 'axiom_number', 'title', 'fol', 'english', 
        'complexity', 'pattern', 'predicates', 'variables', 
        'quantifiers', 'defeasible', 'reified', 'domain',
        'confidence', 'implementation_status', 'agent_references'
    ];

    protected $casts = [
        'predicates' => 'array',
        'variables' => 'array', 
        'quantifiers' => 'array',
        'defeasible' => 'boolean',
        'reified' => 'boolean',
        'confidence' => 'float',
        'agent_references' => 'array'
    ];
    
    public function getComplexityScore(): int
    {
        return match($this->complexity) {
            'simple' => 1,
            'moderate' => 2, 
            'complex' => 3,
            default => 1
        };
    }
    
    public function isDefeasible(): bool
    {
        return $this->defeasible === true;
    }
    
    public function getPredicateList(): array
    {
        return $this->predicates ?? [];
    }
}
```

**File**: `app/Models/Graph/PsychologicalPredicate.php`
```php
<?php

namespace App\Models\Graph;

class PsychologicalPredicate extends BaseGraphModel
{
    protected $label = 'PsychologicalPredicate';
    
    protected $fillable = [
        'name', 'predicate_type', 'arity', 'description',
        'domain', 'usage_count', 'axiom_references'
    ];
    
    protected $casts = [
        'arity' => 'integer',
        'usage_count' => 'integer',
        'axiom_references' => 'array'
    ];
    
    public function getPredicateType(): string
    {
        return $this->predicate_type ?? 'unknown';
    }
    
    public function isActionPredicate(): bool
    {
        return $this->predicate_type === 'action';
    }
    
    public function isMentalStatePredicate(): bool
    {
        return $this->predicate_type === 'mental_state';
    }
}
```

### 1.3 JSON Parser Service

**File**: `app/Services/FOL/AxiomParserService.php`
```php
<?php

namespace App\Services\FOL;

use App\Models\Graph\LogicalAxiom;
use App\Models\Graph\PsychologicalPredicate;
use App\Services\Neo4j\Neo4jService;
use Illuminate\Support\Collection;

class AxiomParserService
{
    public function __construct(
        private Neo4jService $neo4j,
        private AxiomAnalyzerService $analyzer,
        private PredicateExtractorService $extractor
    ) {}
    
    public function parseAxiomsFromJson(string $jsonPath): Collection
    {
        $data = json_decode(file_get_contents($jsonPath), true);
        
        if (!isset($data['axioms'])) {
            throw new \InvalidArgumentException('Invalid axioms JSON structure');
        }
        
        $axioms = collect($data['axioms'])->map(function ($axiomData) {
            return $this->parseAxiom($axiomData);
        });
        
        $this->logParsingResults($axioms);
        
        return $axioms;
    }
    
    private function parseAxiom(array $axiomData): LogicalAxiom
    {
        $axiom = new LogicalAxiom([
            'name' => $this->generateAxiomName($axiomData),
            'axiom_id' => $axiomData['id'],
            'chapter' => $axiomData['chapter'],
            'chapter_title' => $axiomData['chapter_title'],
            'section' => $axiomData['section'],
            'page' => $axiomData['page'],
            'axiom_number' => $axiomData['axiom_number'],
            'title' => $axiomData['title'],
            'fol' => $axiomData['fol'],
            'english' => $axiomData['english'],
            'complexity' => $axiomData['complexity'],
            'pattern' => $axiomData['pattern'],
            'predicates' => $axiomData['predicates'],
            'variables' => $axiomData['variables'],
            'quantifiers' => $axiomData['quantifiers'],
            'defeasible' => $axiomData['defeasible'],
            'reified' => $axiomData['reified'],
            'domain' => $axiomData['domain'],
            'confidence' => $this->calculateConfidence($axiomData),
            'implementation_status' => 'parsed'
        ]);
        
        return $axiom;
    }
    
    private function generateAxiomName(array $axiomData): string
    {
        $title = str_replace(' ', '_', strtoupper($axiomData['title']));
        return "{$title}_{$axiomData['id']}";
    }
    
    private function calculateConfidence(array $axiomData): float
    {
        $baseConfidence = 0.7;
        
        // Increase confidence for simpler axioms
        if ($axiomData['complexity'] === 'simple') {
            $baseConfidence += 0.2;
        } elseif ($axiomData['complexity'] === 'complex') {
            $baseConfidence -= 0.1;
        }
        
        // Decrease confidence for defeasible axioms
        if ($axiomData['defeasible']) {
            $baseConfidence -= 0.1;
        }
        
        return min(1.0, max(0.0, $baseConfidence));
    }
    
    private function logParsingResults(Collection $axioms): void
    {
        $stats = [
            'total' => $axioms->count(),
            'by_complexity' => $axioms->countBy('complexity'),
            'by_domain' => $axioms->countBy('domain'),
            'defeasible' => $axioms->where('defeasible', true)->count(),
            'reified' => $axioms->where('reified', true)->count()
        ];
        
        logger()->info('FOL Axioms parsed', $stats);
    }
}
```

## Phase 2: Graph Population (Weeks 3-5)

### 2.1 Graph Population Service

**File**: `app/Services/FOL/GraphPopulationService.php`
```php
<?php

namespace App\Services\FOL;

use App\Models\Graph\LogicalAxiom;
use App\Models\Graph\PsychologicalPredicate;
use App\Services\Neo4j\Neo4jService;
use Illuminate\Support\Collection;

class GraphPopulationService
{
    public function __construct(
        private Neo4jService $neo4j,
        private PredicateGraphService $predicateService,
        private RelationshipBuilderService $relationshipBuilder
    ) {}
    
    public function populateGraphFromAxioms(Collection $axioms): array
    {
        $results = [
            'axioms_created' => 0,
            'predicates_created' => 0,
            'relationships_created' => 0,
            'errors' => []
        ];
        
        $this->neo4j->beginTransaction();
        
        try {
            // Create axiom nodes
            $results['axioms_created'] = $this->createAxiomNodes($axioms);
            
            // Extract and create predicate nodes
            $predicates = $this->extractPredicates($axioms);
            $results['predicates_created'] = $this->createPredicateNodes($predicates);
            
            // Build relationships
            $results['relationships_created'] = $this->buildRelationships($axioms, $predicates);
            
            $this->neo4j->commit();
            
        } catch (\Exception $e) {
            $this->neo4j->rollback();
            $results['errors'][] = $e->getMessage();
            throw $e;
        }
        
        return $results;
    }
    
    private function createAxiomNodes(Collection $axioms): int
    {
        $created = 0;
        
        foreach ($axioms as $axiom) {
            $cypher = "
                CREATE (axiom:LogicalAxiom:Concept {
                    name: \$name,
                    axiom_id: \$axiom_id,
                    chapter: \$chapter,
                    chapter_title: \$chapter_title,
                    fol: \$fol,
                    english: \$english,
                    complexity: \$complexity,
                    pattern: \$pattern,
                    predicates: \$predicates,
                    defeasible: \$defeasible,
                    domain: \$domain,
                    confidence: \$confidence,
                    created_at: datetime()
                })
            ";
            
            $this->neo4j->run($cypher, [
                'name' => $axiom->name,
                'axiom_id' => $axiom->axiom_id,
                'chapter' => $axiom->chapter,
                'chapter_title' => $axiom->chapter_title,
                'fol' => $axiom->fol,
                'english' => $axiom->english,
                'complexity' => $axiom->complexity,
                'pattern' => $axiom->pattern,
                'predicates' => $axiom->predicates,
                'defeasible' => $axiom->defeasible,
                'domain' => $axiom->domain,
                'confidence' => $axiom->confidence
            ]);
            
            $created++;
        }
        
        return $created;
    }
    
    private function extractPredicates(Collection $axioms): Collection
    {
        $predicateMap = collect();
        
        foreach ($axioms as $axiom) {
            foreach ($axiom->getPredicateList() as $predicateName) {
                if (!$predicateMap->has($predicateName)) {
                    $predicateMap->put($predicateName, $this->createPredicateFromName($predicateName, $axiom));
                }
                
                // Update usage count
                $predicate = $predicateMap->get($predicateName);
                $predicate->usage_count = ($predicate->usage_count ?? 0) + 1;
                
                // Add axiom reference
                $axiomRefs = $predicate->axiom_references ?? [];
                $axiomRefs[] = $axiom->axiom_id;
                $predicate->axiom_references = array_unique($axiomRefs);
            }
        }
        
        return $predicateMap;
    }
    
    private function createPredicateFromName(string $name, LogicalAxiom $axiom): PsychologicalPredicate
    {
        return new PsychologicalPredicate([
            'name' => strtoupper($name),
            'predicate_type' => $this->inferPredicateType($name, $axiom),
            'arity' => $this->inferArity($name, $axiom),
            'description' => $this->generatePredicateDescription($name, $axiom),
            'domain' => $axiom->domain,
            'usage_count' => 1,
            'axiom_references' => [$axiom->axiom_id]
        ]);
    }
    
    private function createPredicateNodes(Collection $predicates): int
    {
        $created = 0;
        
        foreach ($predicates as $predicate) {
            $cypher = "
                MERGE (pred:Concept:PsychologicalPredicate {name: \$name})
                SET pred.predicate_type = \$predicate_type,
                    pred.arity = \$arity,
                    pred.description = \$description,
                    pred.domain = \$domain,
                    pred.usage_count = \$usage_count,
                    pred.axiom_references = \$axiom_references,
                    pred.created_at = COALESCE(pred.created_at, datetime())
            ";
            
            $this->neo4j->run($cypher, [
                'name' => $predicate->name,
                'predicate_type' => $predicate->predicate_type,
                'arity' => $predicate->arity,
                'description' => $predicate->description,
                'domain' => $predicate->domain,
                'usage_count' => $predicate->usage_count,
                'axiom_references' => $predicate->axiom_references
            ]);
            
            $created++;
        }
        
        return $created;
    }
    
    private function buildRelationships(Collection $axioms, Collection $predicates): int
    {
        $created = 0;
        
        foreach ($axioms as $axiom) {
            // Link axiom to predicates it uses
            foreach ($axiom->getPredicateList() as $predicateName) {
                $cypher = "
                    MATCH (axiom:LogicalAxiom {axiom_id: \$axiom_id})
                    MATCH (pred:PsychologicalPredicate {name: \$predicate_name})
                    MERGE (axiom)-[:INVOLVES_PREDICATE]->(pred)
                ";
                
                $this->neo4j->run($cypher, [
                    'axiom_id' => $axiom->axiom_id,
                    'predicate_name' => strtoupper($predicateName)
                ]);
                
                $created++;
            }
            
            // Build logical implications for simple patterns
            if ($axiom->pattern === 'simple_inheritance') {
                $created += $this->buildInheritanceRelationships($axiom);
            } elseif ($axiom->pattern === 'defeasible_rule') {
                $created += $this->buildDefeasibleRelationships($axiom);
            }
        }
        
        return $created;
    }
    
    private function inferPredicateType(string $name, LogicalAxiom $axiom): string
    {
        // Define predicate type inference rules
        $mentalStatePredicates = ['believe', 'know', 'want', 'goal', 'remember', 'think'];
        $actionPredicates = ['perceive', 'give', 'take', 'move', 'cause'];
        $relationPredicates = ['equal', 'member', 'part', 'near'];
        $emotionalPredicates = ['happy', 'sad', 'angry', 'afraid', 'aroused'];
        
        $lowerName = strtolower($name);
        
        if (in_array($lowerName, $mentalStatePredicates)) {
            return 'mental_state';
        } elseif (in_array($lowerName, $actionPredicates)) {
            return 'action';
        } elseif (in_array($lowerName, $relationPredicates)) {
            return 'relation';
        } elseif (in_array($lowerName, $emotionalPredicates)) {
            return 'emotion';
        } else {
            return 'entity'; // Default type
        }
    }
    
    private function inferArity(string $name, LogicalAxiom $axiom): int
    {
        // Extract arity from FOL axiom if possible
        // This is a simplified implementation
        $variableCount = count($axiom->variables ?? []);
        
        // Common arity patterns
        $unaryPredicates = ['person', 'agent', 'car', 'bird'];
        $binaryPredicates = ['believe', 'goal', 'cause', 'member'];
        $ternaryPredicates = ['give', 'between'];
        
        $lowerName = strtolower($name);
        
        if (in_array($lowerName, $unaryPredicates)) {
            return 1;
        } elseif (in_array($lowerName, $binaryPredicates)) {
            return 2;
        } elseif (in_array($lowerName, $ternaryPredicates)) {
            return 3;
        }
        
        // Default based on variable count
        return min($variableCount, 3);
    }
    
    private function generatePredicateDescription(string $name, LogicalAxiom $axiom): string
    {
        return "Predicate '{$name}' from {$axiom->domain} domain (axiom {$axiom->axiom_id})";
    }
}
```

### 2.2 Console Command for Population

**File**: `app/Console/Commands/PopulateAxiomsCommand.php`
```php
<?php

namespace App\Console\Commands;

use App\Services\FOL\AxiomParserService;
use App\Services\FOL\GraphPopulationService;
use Illuminate\Console\Command;

class PopulateAxiomsCommand extends Command
{
    protected $signature = 'soul:populate-axioms 
                          {file : Path to JSON axioms file}
                          {--batch-size=50 : Number of axioms to process per batch}
                          {--dry-run : Parse without writing to database}';
                          
    protected $description = 'Populate Neo4j graph with FOL axioms from JSON file';
    
    public function handle(
        AxiomParserService $parser,
        GraphPopulationService $populator
    ): int {
        $filePath = $this->argument('file');
        $dryRun = $this->option('dry-run');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }
        
        $this->info("Parsing axioms from: {$filePath}");
        
        try {
            $axioms = $parser->parseAxiomsFromJson($filePath);
            
            $this->info("Parsed {$axioms->count()} axioms successfully");
            
            $this->table(['Complexity', 'Count'], 
                collect(['simple', 'moderate', 'complex'])->map(fn($c) => [
                    $c, $axioms->where('complexity', $c)->count()
                ])->toArray()
            );
            
            if ($dryRun) {
                $this->warn('Dry run mode - no database changes made');
                return 0;
            }
            
            $this->info('Populating graph database...');
            
            $results = $populator->populateGraphFromAxioms($axioms);
            
            $this->info('Population completed successfully:');
            $this->line("- Axioms created: {$results['axioms_created']}");
            $this->line("- Predicates created: {$results['predicates_created']}");
            $this->line("- Relationships created: {$results['relationships_created']}");
            
            if (!empty($results['errors'])) {
                $this->error('Errors encountered:');
                foreach ($results['errors'] as $error) {
                    $this->line("- {$error}");
                }
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Population failed: {$e->getMessage()}");
            return 1;
        }
    }
}
```

## Phase 3: Frame Structure Creation (Weeks 5-7)

### 3.1 Frame Builder Service

**File**: `app/Services/FOL/FrameBuilderService.php`
```php
<?php

namespace App\Services\FOL;

use App\Models\Graph\LogicalAxiom;
use App\Services\Neo4j\Neo4jService;
use Illuminate\Support\Collection;

class FrameBuilderService
{
    private array $framePatterns = [
        'goal_causation' => [
            'pattern' => 'goal_.*causation',
            'elements' => ['agent', 'goal', 'causal_belief', 'instrumental_goal'],
            'constraints' => ['causation_chain', 'belief_requirement']
        ],
        'emotion_causation' => [
            'pattern' => 'emotion_.*causation',
            'elements' => ['person', 'emotion_state', 'trigger_event', 'intensity'],
            'constraints' => ['defeasible', 'intensity_correlation']
        ],
        'belief_formation' => [
            'pattern' => 'belief_.*',
            'elements' => ['agent', 'eventuality', 'perception', 'belief_state'],
            'constraints' => ['perception_precedence']
        ],
        'perception_action' => [
            'pattern' => '.*perception.*|.*action.*',
            'elements' => ['agent', 'stimulus', 'perception', 'response'],
            'constraints' => ['stimulus_response_chain']
        ]
    ];
    
    public function __construct(private Neo4jService $neo4j) {}
    
    public function buildFramesFromAxioms(Collection $axioms): array
    {
        $results = [
            'frames_created' => 0,
            'frame_elements_created' => 0,
            'frame_relationships_created' => 0
        ];
        
        $complexAxioms = $axioms->filter(fn($axiom) => 
            $axiom->complexity === 'moderate' || $axiom->complexity === 'complex'
        );
        
        foreach ($complexAxioms as $axiom) {
            $frameType = $this->identifyFrameType($axiom);
            
            if ($frameType) {
                $frame = $this->createFrameStructure($axiom, $frameType);
                $results['frames_created']++;
                $results['frame_elements_created'] += $frame['elements_count'];
                $results['frame_relationships_created'] += $frame['relationships_count'];
            }
        }
        
        return $results;
    }
    
    private function identifyFrameType(LogicalAxiom $axiom): ?string
    {
        foreach ($this->framePatterns as $type => $pattern) {
            if (preg_match("/{$pattern['pattern']}/i", $axiom->pattern)) {
                return $type;
            }
        }
        
        // Special case identification for psychology domain
        if ($axiom->domain === 'psychology' && $axiom->complexity === 'complex') {
            return $this->identifyPsychologyFrameType($axiom);
        }
        
        return null;
    }
    
    private function identifyPsychologyFrameType(LogicalAxiom $axiom): ?string
    {
        $predicates = $axiom->getPredicateList();
        
        if (in_array('goal', $predicates) && in_array('cause', $predicates)) {
            return 'goal_causation';
        } elseif (in_array('believe', $predicates) && in_array('perceive', $predicates)) {
            return 'belief_formation';
        } elseif (array_intersect(['happy', 'sad', 'angry', 'afraid'], $predicates)) {
            return 'emotion_causation';
        }
        
        return null;
    }
    
    private function createFrameStructure(LogicalAxiom $axiom, string $frameType): array
    {
        $framePattern = $this->framePatterns[$frameType];
        $frameName = $this->generateFrameName($axiom, $frameType);
        
        // Create main frame node
        $cypher = "
            CREATE (frame:Concept:Frame:LogicalFrame {
                name: \$frame_name,
                frame_type: \$frame_type,
                based_on_axiom: \$axiom_id,
                description: \$description,
                domain: \$domain,
                complexity: \$complexity,
                defeasible: \$defeasible,
                elements: \$elements,
                constraints: \$constraints,
                created_at: datetime()
            })
        ";
        
        $this->neo4j->run($cypher, [
            'frame_name' => $frameName,
            'frame_type' => $frameType,
            'axiom_id' => $axiom->axiom_id,
            'description' => "Frame structure derived from axiom: {$axiom->english}",
            'domain' => $axiom->domain,
            'complexity' => $axiom->complexity,
            'defeasible' => $axiom->defeasible,
            'elements' => $framePattern['elements'],
            'constraints' => $framePattern['constraints']
        ]);
        
        // Create frame elements
        $elementsCreated = $this->createFrameElements($frameName, $framePattern['elements'], $axiom);
        
        // Link frame to source axiom
        $this->linkFrameToAxiom($frameName, $axiom->axiom_id);
        
        // Create relationships between elements
        $relationshipsCreated = $this->createFrameElementRelationships($frameName, $frameType, $axiom);
        
        return [
            'frame_name' => $frameName,
            'elements_count' => $elementsCreated,
            'relationships_count' => $relationshipsCreated + 1 // +1 for axiom link
        ];
    }
    
    private function createFrameElements(string $frameName, array $elements, LogicalAxiom $axiom): int
    {
        $created = 0;
        
        foreach ($elements as $elementName) {
            $cypher = "
                MATCH (frame:LogicalFrame {name: \$frame_name})
                CREATE (element:FrameElement {
                    name: \$element_name,
                    fe_type: \$fe_type,
                    description: \$description,
                    frame_name: \$frame_name,
                    source_axiom: \$axiom_id,
                    created_at: datetime()
                })
                CREATE (frame)-[:HAS_FRAME_ELEMENT]->(element)
            ";
            
            $this->neo4j->run($cypher, [
                'frame_name' => $frameName,
                'element_name' => $elementName,
                'fe_type' => $this->determineElementType($elementName, $axiom),
                'description' => $this->generateElementDescription($elementName, $axiom),
                'axiom_id' => $axiom->axiom_id
            ]);
            
            $created++;
        }
        
        return $created;
    }
    
    private function createFrameElementRelationships(string $frameName, string $frameType, LogicalAxiom $axiom): int
    {
        $created = 0;
        
        // Create type-specific relationships
        switch ($frameType) {
            case 'goal_causation':
                $created += $this->createGoalCausationRelationships($frameName);
                break;
            case 'emotion_causation':
                $created += $this->createEmotionCausationRelationships($frameName);
                break;
            case 'belief_formation':
                $created += $this->createBeliefFormationRelationships($frameName);
                break;
        }
        
        return $created;
    }
    
    private function createGoalCausationRelationships(string $frameName): int
    {
        $cypher = "
            MATCH (frame:LogicalFrame {name: \$frame_name})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(agent:FrameElement {name: 'agent'})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(goal:FrameElement {name: 'goal'})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(belief:FrameElement {name: 'causal_belief'})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(subgoal:FrameElement {name: 'instrumental_goal'})
            
            CREATE (agent)-[:HAS_GOAL]->(goal)
            CREATE (agent)-[:HAS_BELIEF]->(belief)
            CREATE (belief)-[:CAUSES_GOAL_FORMATION]->(subgoal)
            CREATE (goal)-[:HAS_SUBGOAL]->(subgoal)
        ";
        
        $this->neo4j->run($cypher, ['frame_name' => $frameName]);
        return 4; // Number of relationships created
    }
    
    private function createEmotionCausationRelationships(string $frameName): int
    {
        $cypher = "
            MATCH (frame:LogicalFrame {name: \$frame_name})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(person:FrameElement {name: 'person'})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(emotion:FrameElement {name: 'emotion_state'})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(trigger:FrameElement {name: 'trigger_event'})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(intensity:FrameElement {name: 'intensity'})
            
            CREATE (trigger)-[:CAUSES_EMOTION]->(emotion)
            CREATE (person)-[:EXPERIENCES]->(emotion)
            CREATE (emotion)-[:HAS_INTENSITY]->(intensity)
        ";
        
        $this->neo4j->run($cypher, ['frame_name' => $frameName]);
        return 3;
    }
    
    private function createBeliefFormationRelationships(string $frameName): int
    {
        $cypher = "
            MATCH (frame:LogicalFrame {name: \$frame_name})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(agent:FrameElement {name: 'agent'})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(perception:FrameElement {name: 'perception'})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(belief:FrameElement {name: 'belief_state'})
            MATCH (frame)-[:HAS_FRAME_ELEMENT]->(eventuality:FrameElement {name: 'eventuality'})
            
            CREATE (agent)-[:PERCEIVES]->(eventuality)
            CREATE (perception)-[:LEADS_TO]->(belief)
            CREATE (agent)-[:BELIEVES]->(eventuality)
        ";
        
        $this->neo4j->run($cypher, ['frame_name' => $frameName]);
        return 3;
    }
    
    private function generateFrameName(LogicalAxiom $axiom, string $frameType): string
    {
        $sanitizedTitle = str_replace([' ', '-'], '_', strtoupper($axiom->title));
        return "{$sanitizedTitle}_FRAME_{$axiom->axiom_id}";
    }
    
    private function determineElementType(string $elementName, LogicalAxiom $axiom): string
    {
        $coreElements = ['agent', 'person', 'goal', 'belief_state'];
        return in_array($elementName, $coreElements) ? 'core' : 'peripheral';
    }
    
    private function generateElementDescription(string $elementName, LogicalAxiom $axiom): string
    {
        $descriptions = [
            'agent' => 'The cognitive agent involved in the reasoning process',
            'person' => 'The human individual in the psychological scenario',
            'goal' => 'The desired outcome or state',
            'belief_state' => 'The cognitive belief held by the agent',
            'causal_belief' => 'Agent\'s belief about causal relationships',
            'instrumental_goal' => 'Subgoal adopted to achieve main goal',
            'emotion_state' => 'The emotional state being experienced',
            'trigger_event' => 'Event that triggers the emotional response',
            'intensity' => 'The intensity level of the emotion',
            'perception' => 'The perceptual event',
            'eventuality' => 'The state of affairs being considered'
        ];
        
        return $descriptions[$elementName] ?? "Frame element: {$elementName}";
    }
    
    private function linkFrameToAxiom(string $frameName, string $axiomId): void
    {
        $cypher = "
            MATCH (frame:LogicalFrame {name: \$frame_name})
            MATCH (axiom:LogicalAxiom {axiom_id: \$axiom_id})
            CREATE (frame)-[:DERIVED_FROM {derivation_type: 'frame_structure'}]->(axiom)
        ";
        
        $this->neo4j->run($cypher, [
            'frame_name' => $frameName,
            'axiom_id' => $axiomId
        ]);
    }
}
```

## Phase 4: Procedural Agent Generation (Weeks 7-10)

### 4.1 Agent Generator Service

**File**: `app/Services/FOL/AgentGeneratorService.php`
```php
<?php

namespace App\Services\FOL;

use App\Models\Graph\LogicalAxiom;
use App\Services\Neo4j\Neo4jService;
use Illuminate\Support\Collection;

class AgentGeneratorService
{
    private array $agentTemplates = [
        'simple_inheritance' => [
            'service_class' => 'TaxonomyService',
            'method' => 'applyInheritanceRule',
            'priority' => 1,
            'description' => 'Applies simple taxonomic inheritance rules'
        ],
        'defeasible_rule' => [
            'service_class' => 'DefeasibleReasoningService',
            'method' => 'applyDefeasibleRule',
            'priority' => 2,
            'description' => 'Handles defeasible reasoning with exceptions'
        ],
        'goal_causation' => [
            'service_class' => 'GoalReasoningService',
            'method' => 'processGoalCausation',
            'priority' => 3,
            'description' => 'Processes goal formation through causal reasoning'
        ],
        'belief_logic' => [
            'service_class' => 'BeliefReasoningService',
            'method' => 'processBeliefFormation',
            'priority' => 2,
            'description' => 'Handles belief formation and updating'
        ],
        'emotion_causation' => [
            'service_class' => 'EmotionService',
            'method' => 'processEmotionalCausation',
            'priority' => 3,
            'description' => 'Processes emotional state changes and causation'
        ]
    ];
    
    public function __construct(private Neo4jService $neo4j) {}
    
    public function generateAgentsFromAxioms(Collection $axioms): array
    {
        $results = [
            'agents_created' => 0,
            'activation_links_created' => 0,
            'communication_links_created' => 0
        ];
        
        foreach ($axioms as $axiom) {
            if ($this->shouldCreateAgent($axiom)) {
                $agent = $this->createProceduralAgent($axiom);
                $results['agents_created']++;
                
                $results['activation_links_created'] += $this->createActivationLinks($agent, $axiom);
                $results['communication_links_created'] += $this->createCommunicationLinks($agent, $axiom);
            }
        }
        
        return $results;
    }
    
    private function shouldCreateAgent(LogicalAxiom $axiom): bool
    {
        // Create agents for psychology domain and complex patterns
        return $axiom->domain === 'psychology' || 
               in_array($axiom->pattern, array_keys($this->agentTemplates)) ||
               $axiom->complexity === 'complex';
    }
    
    private function createProceduralAgent(LogicalAxiom $axiom): array
    {
        $template = $this->agentTemplates[$axiom->pattern] ?? $this->getDefaultTemplate($axiom);
        
        $agentData = [
            'name' => $this->generateAgentName($axiom),
            'code_reference' => "{$template['service_class']}::{$template['method']}",
            'service_class' => $template['service_class'],
            'method_name' => $template['method'],
            'description' => $template['description'],
            'priority' => $template['priority'],
            'axiom_source' => $axiom->axiom_id,
            'domain' => $axiom->domain,
            'defeasible' => $axiom->defeasible,
            'trigger_patterns' => $this->extractTriggerPatterns($axiom),
            'timeout_seconds' => $this->calculateTimeout($axiom)
        ];
        
        $cypher = "
            CREATE (agent:PROCEDURAL_AGENT:Concept {
                name: \$name,
                code_reference: \$code_reference,
                service_class: \$service_class,
                method_name: \$method_name,
                description: \$description,
                priority: \$priority,
                axiom_source: \$axiom_source,
                domain: \$domain,
                defeasible: \$defeasible,
                trigger_patterns: \$trigger_patterns,
                timeout_seconds: \$timeout_seconds,
                created_at: datetime()
            })
        ";
        
        $this->neo4j->run($cypher, $agentData);
        
        return $agentData;
    }
    
    private function createActivationLinks(array $agent, LogicalAxiom $axiom): int
    {
        $created = 0;
        
        // Link agent to predicates it processes
        foreach ($axiom->getPredicateList() as $predicateName) {
            $cypher = "
                MATCH (agent:PROCEDURAL_AGENT {name: \$agent_name})
                MATCH (pred:PsychologicalPredicate {name: \$predicate_name})
                CREATE (pred)-[:ACTIVATES {
                    strength: \$strength,
                    trigger_type: \$trigger_type,
                    axiom_source: \$axiom_id
                }]->(agent)
            ";
            
            $this->neo4j->run($cypher, [
                'agent_name' => $agent['name'],
                'predicate_name' => strtoupper($predicateName),
                'strength' => $this->calculateActivationStrength($predicateName, $axiom),
                'trigger_type' => $this->determineTriggerType($predicateName, $axiom),
                'axiom_id' => $axiom->axiom_id
            ]);
            
            $created++;
        }
        
        // Link agent to axiom
        $cypher = "
            MATCH (agent:PROCEDURAL_AGENT {name: \$agent_name})
            MATCH (axiom:LogicalAxiom {axiom_id: \$axiom_id})
            CREATE (axiom)-[:IMPLEMENTED_BY]->(agent)
        ";
        
        $this->neo4j->run($cypher, [
            'agent_name' => $agent['name'],
            'axiom_id' => $axiom->axiom_id
        ]);
        
        $created++;
        
        return $created;
    }
    
    private function createCommunicationLinks(array $agent, LogicalAxiom $axiom): int
    {
        $created = 0;
        
        // Create communication links based on shared predicates
        $sharedPredicateAgents = $this->findAgentsWithSharedPredicates($axiom);
        
        foreach ($sharedPredicateAgents as $targetAgent) {
            $cypher = "
                MATCH (source:PROCEDURAL_AGENT {name: \$source_name})
                MATCH (target:PROCEDURAL_AGENT {name: \$target_name})
                CREATE (source)-[:COMMUNICATES_WITH {
                    message_type: \$message_type,
                    shared_domain: \$domain,
                    collaboration_type: \$collaboration_type
                }]->(target)
            ";
            
            $this->neo4j->run($cypher, [
                'source_name' => $agent['name'],
                'target_name' => $targetAgent,
                'message_type' => 'activation_coordination',
                'domain' => $axiom->domain,
                'collaboration_type' => $this->determineCollaborationType($axiom)
            ]);
            
            $created++;
        }
        
        return $created;
    }
    
    private function generateAgentName(LogicalAxiom $axiom): string
    {
        $baseName = str_replace([' ', '-'], '', ucwords(strtolower($axiom->title)));
        return "{$baseName}Agent_{$axiom->axiom_id}";
    }
    
    private function getDefaultTemplate(LogicalAxiom $axiom): array
    {
        return [
            'service_class' => 'GeneralReasoningService',
            'method' => 'processAxiom',
            'priority' => 4,
            'description' => "Processes axiom: {$axiom->english}"
        ];
    }
    
    private function extractTriggerPatterns(LogicalAxiom $axiom): array
    {
        $patterns = [];
        
        foreach ($axiom->getPredicateList() as $predicate) {
            $patterns[] = [
                'predicate' => $predicate,
                'activation_threshold' => 0.3,
                'context_requirements' => $axiom->domain
            ];
        }
        
        return $patterns;
    }
    
    private function calculateTimeout(LogicalAxiom $axiom): int
    {
        $baseTimeout = 30; // seconds
        
        switch ($axiom->complexity) {
            case 'simple':
                return $baseTimeout;
            case 'moderate':
                return $baseTimeout * 2;
            case 'complex':
                return $baseTimeout * 3;
            default:
                return $baseTimeout;
        }
    }
    
    private function calculateActivationStrength(string $predicate, LogicalAxiom $axiom): float
    {
        $baseStrength = 0.7;
        
        // Increase for psychology domain
        if ($axiom->domain === 'psychology') {
            $baseStrength += 0.1;
        }
        
        // Decrease for defeasible rules
        if ($axiom->defeasible) {
            $baseStrength -= 0.1;
        }
        
        // Adjust for complexity
        switch ($axiom->complexity) {
            case 'simple':
                $baseStrength += 0.1;
                break;
            case 'complex':
                $baseStrength -= 0.1;
                break;
        }
        
        return min(1.0, max(0.1, $baseStrength));
    }
    
    private function determineTriggerType(string $predicate, LogicalAxiom $axiom): string
    {
        $mentalStatePredicates = ['believe', 'goal', 'want', 'remember'];
        $actionPredicates = ['perceive', 'cause', 'give'];
        
        if (in_array(strtolower($predicate), $mentalStatePredicates)) {
            return 'mental_state_trigger';
        } elseif (in_array(strtolower($predicate), $actionPredicates)) {
            return 'action_trigger';
        } else {
            return 'general_trigger';
        }
    }
    
    private function findAgentsWithSharedPredicates(LogicalAxiom $axiom): array
    {
        $cypher = "
            MATCH (axiom:LogicalAxiom {axiom_id: \$axiom_id})-[:INVOLVES_PREDICATE]->(pred:PsychologicalPredicate)
            MATCH (pred)<-[:INVOLVES_PREDICATE]-(otherAxiom:LogicalAxiom)-[:IMPLEMENTED_BY]->(agent:PROCEDURAL_AGENT)
            WHERE axiom <> otherAxiom
            RETURN DISTINCT agent.name as agent_name
            LIMIT 5
        ";
        
        $results = $this->neo4j->run($cypher, ['axiom_id' => $axiom->axiom_id]);
        
        return $results->records()->map(fn($record) => $record->value('agent_name'))->toArray();
    }
    
    private function determineCollaborationType(LogicalAxiom $axiom): string
    {
        if ($axiom->domain === 'psychology') {
            return 'psychological_reasoning';
        } elseif ($axiom->defeasible) {
            return 'defeasible_coordination';
        } else {
            return 'logical_support';
        }
    }
}
```

### 4.2 Service Implementation Templates

**File**: `app/Services/Psychology/GoalReasoningService.php`
```php
<?php

namespace App\Services\Psychology;

use App\Services\SOUL\BaseReasoningService;
use App\Models\Graph\FrameInstance;
use Illuminate\Support\Collection;

class GoalReasoningService extends BaseReasoningService
{
    public function processGoalCausation(Collection $concepts, array $bindings): Collection
    {
        // Implement axiom logic: If agent has goal G2 and believes E1 causes G2, 
        // then agent adopts goal E1
        
        $results = collect();
        
        if ($this->hasGoalCausationPattern($concepts, $bindings)) {
            $goalFrame = $this->createGoalCausationFrame($bindings);
            
            // Apply the reasoning rule
            $instrumentalGoal = $this->deriveInstrumentalGoal(
                $bindings['agent'],
                $bindings['main_goal'],
                $bindings['causal_belief']
            );
            
            if ($instrumentalGoal) {
                $results->push($instrumentalGoal);
                
                // Create K-line for successful reasoning
                $this->recordSuccessfulPattern('goal_causation', $bindings, $instrumentalGoal);
            }
        }
        
        return $results;
    }
    
    private function hasGoalCausationPattern(Collection $concepts, array $bindings): bool
    {
        return isset($bindings['agent']) &&
               isset($bindings['main_goal']) &&
               isset($bindings['causal_belief']) &&
               $concepts->contains('name', 'GOAL') &&
               $concepts->contains('name', 'CAUSE');
    }
    
    private function createGoalCausationFrame(array $bindings): FrameInstance
    {
        return FrameInstance::create([
            'frame_id' => 'GOAL_CAUSATION_FRAME',
            'bindings' => $bindings,
            'confidence' => 0.8,
            'session_id' => $this->getCurrentSessionId()
        ]);
    }
    
    private function deriveInstrumentalGoal($agent, $mainGoal, $causalBelief): ?array
    {
        // Extract the instrumental action from the causal belief
        // This is a simplified implementation
        
        if (preg_match('/cause\((.*?),\s*(.*?)\)/', $causalBelief, $matches)) {
            $instrumentalAction = trim($matches[1]);
            
            return [
                'type' => 'instrumental_goal',
                'agent' => $agent,
                'goal_content' => $instrumentalAction,
                'parent_goal' => $mainGoal,
                'derivation_rule' => 'goal_causation_28.5',
                'confidence' => 0.7
            ];
        }
        
        return null;
    }
}
```

**File**: `app/Services/Psychology/BeliefReasoningService.php`
```php
<?php

namespace App\Services\Psychology;

use App\Services\SOUL\BaseReasoningService;
use Illuminate\Support\Collection;

class BeliefReasoningService extends BaseReasoningService
{
    public function processBeliefFormation(Collection $concepts, array $bindings): Collection
    {
        // Implement axiom 21.9: Perceiving defeasibly leads to believing
        
        $results = collect();
        
        if ($this->hasPerceptionBeliefPattern($concepts, $bindings)) {
            // Check for exception conditions (etc clause)
            if (!$this->hasExceptionConditions($bindings)) {
                $beliefState = $this->formBelief(
                    $bindings['agent'],
                    $bindings['eventuality']
                );
                
                if ($beliefState) {
                    $results->push($beliefState);
                    
                    // Activate BELIEVE concept
                    $this->activateConcept('BELIEVE', $beliefState['confidence']);
                    
                    $this->recordSuccessfulPattern('perception_to_belief', $bindings, $beliefState);
                }
            }
        }
        
        return $results;
    }
    
    private function hasPerceptionBeliefPattern(Collection $concepts, array $bindings): bool
    {
        return isset($bindings['agent']) &&
               isset($bindings['eventuality']) &&
               $concepts->contains('name', 'PERCEIVE');
    }
    
    private function hasExceptionConditions(array $bindings): bool
    {
        // Check for conditions that would prevent belief formation
        // e.g., contradictory beliefs, unreliable perception, etc.
        
        return $this->hasContradictoryBelief($bindings) ||
               $this->hasUnreliablePerception($bindings) ||
               $this->hasSkepticalAgent($bindings);
    }
    
    private function formBelief($agent, $eventuality): ?array
    {
        return [
            'type' => 'belief_state',
            'agent' => $agent,
            'content' => $eventuality,
            'source' => 'perception',
            'formation_rule' => 'perception_belief_21.9',
            'confidence' => $this->calculateBeliefConfidence($agent, $eventuality),
            'defeasible' => true
        ];
    }
    
    private function calculateBeliefConfidence($agent, $eventuality): float
    {
        $baseConfidence = 0.8;
        
        // Adjust based on agent reliability
        $agentReliability = $this->getAgentReliability($agent);
        $baseConfidence *= $agentReliability;
        
        // Adjust based on eventuality complexity
        $complexityFactor = $this->getEventualityComplexity($eventuality);
        $baseConfidence *= (1.0 - $complexityFactor * 0.2);
        
        return min(1.0, max(0.1, $baseConfidence));
    }
}
```

### 4.3 Defeasible Reasoning Service

**File**: `app/Services/Psychology/DefeasibleReasoningService.php`
```php
<?php

namespace App\Services\Psychology;

use App\Services\SOUL\BaseReasoningService;
use Illuminate\Support\Collection;

class DefeasibleReasoningService extends BaseReasoningService
{
    public function applyDefeasibleRule(Collection $concepts, array $bindings): Collection
    {
        $results = collect();
        
        $axiom = $this->findRelevantAxiom($concepts, $bindings);
        
        if ($axiom && $axiom->defeasible) {
            // Check exception conditions
            $exceptions = $this->checkExceptionConditions($axiom, $bindings);
            
            if ($exceptions->isEmpty()) {
                // Apply the rule
                $conclusion = $this->applyRule($axiom, $bindings);
                
                if ($conclusion) {
                    $results->push($conclusion);
                    $this->recordSuccessfulDefeasibleReasoning($axiom, $bindings, $conclusion);
                }
            } else {
                // Handle exceptions
                $this->handleExceptions($axiom, $bindings, $exceptions);
            }
        }
        
        return $results;
    }
    
    private function checkExceptionConditions($axiom, array $bindings): Collection
    {
        $exceptions = collect();
        
        // Check for known exception patterns
        switch ($axiom->pattern) {
            case 'defeasible_rule':
                $exceptions = $this->checkGeneralExceptions($axiom, $bindings);
                break;
                
            case 'emotion_causation':
                $exceptions = $this->checkEmotionExceptions($axiom, $bindings);
                break;
                
            case 'belief_logic':
                $exceptions = $this->checkBeliefExceptions($axiom, $bindings);
                break;
        }
        
        return $exceptions;
    }
    
    private function checkGeneralExceptions($axiom, array $bindings): Collection
    {
        $exceptions = collect();
        
        // Example: Birds can fly, except flightless birds
        if ($axiom->axiom_id === '11.7') { // Birds have two legs
            if (isset($bindings['bird_type']) && 
                in_array($bindings['bird_type'], ['penguin', 'ostrich', 'kiwi'])) {
                $exceptions->push('flightless_bird_exception');
            }
        }
        
        return $exceptions;
    }
    
    private function applyRule($axiom, array $bindings): ?array
    {
        // Generic rule application based on axiom pattern
        
        return [
            'type' => 'defeasible_conclusion',
            'axiom_source' => $axiom->axiom_id,
            'bindings' => $bindings,
            'conclusion' => $this->generateConclusion($axiom, $bindings),
            'confidence' => $axiom->confidence * 0.9, // Slight reduction for defeasibility
            'defeasible' => true,
            'exceptions_checked' => true
        ];
    }
    
    private function handleExceptions($axiom, array $bindings, Collection $exceptions): void
    {
        // Log exception handling
        logger()->info("Defeasible rule exception", [
            'axiom_id' => $axiom->axiom_id,
            'exceptions' => $exceptions->toArray(),
            'bindings' => $bindings
        ]);
        
        // Create exception handling K-line
        $this->createExceptionKLine($axiom, $bindings, $exceptions);
    }
    
    private function generateConclusion($axiom, array $bindings): string
    {
        // Parse the consequent of the axiom and apply bindings
        // This is a simplified implementation
        
        $template = $axiom->english;
        
        foreach ($bindings as $var => $value) {
            $template = str_replace("{{$var}}", $value, $template);
        }
        
        return $template;
    }
}
```

## Phase 5: K-line Learning Implementation (Weeks 10-12)

### 5.1 K-line Learning Service

**File**: `app/Services/SOUL/KLineLearningService.php`
```php
<?php

namespace App\Services\SOUL;

use App\Models\Graph\KLine;
use App\Services\Neo4j\Neo4jService;
use Illuminate\Support\Collection;

class KLineLearningService
{
    public function __construct(
        private Neo4jService $neo4j,
        private string $sessionId
    ) {}
    
    public function recordSuccessfulPattern(
        string $context,
        array $bindings,
        array $result,
        Collection $activatedConcepts
    ): KLine {
        $klineId = $this->generateKLineId($context, $bindings);
        
        $activationPattern = $this->captureActivationPattern(
            $activatedConcepts,
            $bindings,
            $result
        );
        
        // Check if K-line already exists
        $existingKLine = $this->findExistingKLine($context, $activationPattern);
        
        if ($existingKLine) {
            return $this->updateKLineUsage($existingKLine);
        } else {
            return $this->createNewKLine($klineId, $context, $activationPattern);
        }
    }
    
    private function captureActivationPattern(
        Collection $activatedConcepts,
        array $bindings,
        array $result
    ): array {
        return [
            'initial_concepts' => $activatedConcepts->pluck('name')->toArray(),
            'bindings' => $bindings,
            'activation_sequence' => $this->getActivationSequence(),
            'result_type' => $result['type'] ?? 'unknown',
            'confidence' => $result['confidence'] ?? 0.5,
            'processing_steps' => $this->getProcessingSteps(),
            'session_context' => [
                'session_id' => $this->sessionId,
                'timestamp' => now()->toISOString()
            ]
        ];
    }
    
    private function createNewKLine(string $klineId, string $context, array $pattern): KLine
    {
        $cypher = "
            CREATE (kline:KLine {
                id: \$kline_id,
                context: \$context,
                activation_pattern: \$activation_pattern,
                usage_count: 1,
                success_rate: 1.0,
                strength: 0.5,
                last_used: datetime(),
                created_at: datetime()
            })
            RETURN kline
        ";
        
        $result = $this->neo4j->run($cypher, [
            'kline_id' => $klineId,
            'context' => $context,
            'activation_pattern' => json_encode($pattern)
        ]);
        
        $kline = new KLine($result->first()->value('kline'));
        
        // Link K-line to relevant concepts
        $this->linkKLineToConcepts($kline, $pattern['initial_concepts']);
        
        return $kline;
    }
    
    private function updateKLineUsage(KLine $kline): KLine
    {
        $cypher = "
            MATCH (kline:KLine {id: \$kline_id})
            SET kline.usage_count = kline.usage_count + 1,
                kline.last_used = datetime(),
                kline.strength = CASE 
                    WHEN kline.strength < 0.9 THEN kline.strength + 0.05
                    ELSE 0.95
                END
            RETURN kline
        ";
        
        $result = $this->neo4j->run($cypher, ['kline_id' => $kline->id]);
        
        return new KLine($result->first()->value('kline'));
    }
    
    private function linkKLineToConcepts(KLine $kline, array $conceptNames): void
    {
        foreach ($conceptNames as $conceptName) {
            $cypher = "
                MATCH (kline:KLine {id: \$kline_id})
                MATCH (concept:Concept {name: \$concept_name})
                MERGE (kline)-[:ACTIVATES {strength: 0.8}]->(concept)
            ";
            
            $this->neo4j->run($cypher, [
                'kline_id' => $kline->id,
                'concept_name' => $conceptName
            ]);
        }
    }
    
    private function findExistingKLine(string $context, array $activationPattern): ?KLine
    {
        // Find K-lines with similar activation patterns
        $cypher = "
            MATCH (kline:KLine {context: \$context})
            RETURN kline, kline.activation_pattern as pattern
        ";
        
        $results = $this->neo4j->run($cypher, ['context' => $context]);
        
        foreach ($results->records() as $record) {
            $existingPattern = json_decode($record->value('pattern'), true);
            
            if ($this->patternsAreSimilar($existingPattern, $activationPattern)) {
                return new KLine($record->value('kline'));
            }
        }
        
        return null;
    }
    
    private function patternsAreSimilar(array $pattern1, array $pattern2): bool
    {
        $concepts1 = $pattern1['initial_concepts'] ?? [];
        $concepts2 = $pattern2['initial_concepts'] ?? [];
        
        // Calculate Jaccard similarity
        $intersection = count(array_intersect($concepts1, $concepts2));
        $union = count(array_unique(array_merge($concepts1, $concepts2)));
        
        $similarity = $union > 0 ? $intersection / $union : 0;
        
        return $similarity > 0.7; // 70% similarity threshold
    }
    
    private function generateKLineId(string $context, array $bindings): string
    {
        $hash = md5($context . serialize($bindings) . time());
        return "kline_{$context}_" . substr($hash, 0, 8);
    }
    
    private function getActivationSequence(): array
    {
        // This would be captured during processing
        // Simplified implementation
        return [
            'step1' => 'concept_activation',
            'step2' => 'pattern_matching',
            'step3' => 'rule_application',
            'step4' => 'result_generation'
        ];
    }
    
    private function getProcessingSteps(): array
    {
        // This would track the actual processing steps
        return [
            'parsing' => 'complete',
            'binding' => 'complete',
            'reasoning' => 'complete',
            'validation' => 'complete'
        ];
    }
    
    public function retrieveRelevantKLines(Collection $concepts, array $context): Collection
    {
        $conceptNames = $concepts->pluck('name')->toArray();
        
        $cypher = "
            MATCH (kline:KLine)-[:ACTIVATES]->(concept:Concept)
            WHERE concept.name IN \$concept_names
            WITH kline, COUNT(concept) as matching_concepts
            ORDER BY matching_concepts DESC, kline.strength DESC, kline.usage_count DESC
            RETURN kline
            LIMIT 10
        ";
        
        $results = $this->neo4j->run($cypher, ['concept_names' => $conceptNames]);
        
        return collect($results->records())->map(fn($record) => new KLine($record->value('kline')));
    }
}
```

## Phase 6: Integration & Testing (Weeks 12-14)

### 6.1 Integration Service

**File**: `app/Services/FOL/FOLIntegrationService.php`
```php
<?php

namespace App\Services\FOL;

use App\Services\SOUL\SOULProcessingService;
use App\Services\Neo4j\Neo4jService;
use Illuminate\Support\Collection;

class FOLIntegrationService
{
    public function __construct(
        private AxiomParserService $parser,
        private GraphPopulationService $populator,
        private FrameBuilderService $frameBuilder,
        private AgentGeneratorService $agentGenerator,
        private SOULProcessingService $soulProcessor,
        private Neo4jService $neo4j
    ) {}
    
    public function integrateAxioms(string $jsonPath): array
    {
        $results = [
            'parsing' => null,
            'population' => null,
            'frame_building' => null,
            'agent_generation' => null,
            'integration_validation' => null
        ];
        
        try {
            // Phase 1: Parse axioms
            $this->logStep('Parsing FOL axioms from JSON');
            $axioms = $this->parser->parseAxiomsFromJson($jsonPath);
            $results['parsing'] = ['axioms_parsed' => $axioms->count()];
            
            // Phase 2: Populate graph
            $this->logStep('Populating graph database');
            $results['population'] = $this->populator->populateGraphFromAxioms($axioms);
            
            // Phase 3: Build frames
            $this->logStep('Building frame structures');
            $results['frame_building'] = $this->frameBuilder->buildFramesFromAxioms($axioms);
            
            // Phase 4: Generate agents
            $this->logStep('Generating procedural agents');
            $results['agent_generation'] = $this->agentGenerator->generateAgentsFromAxioms($axioms);
            
            // Phase 5: Integration validation
            $this->logStep('Validating integration');
            $results['integration_validation'] = $this->validateIntegration();
            
            $this->logStep('FOL integration completed successfully');
            
        } catch (\Exception $e) {
            logger()->error('FOL integration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
        
        return $results;
    }
    
    public function testPsychologicalReasoning(array $scenario): array
    {
        // Test the integrated system with a psychological reasoning scenario
        
        $testResults = [
            'scenario' => $scenario,
            'activated_axioms' => [],
            'reasoning_chain' => [],
            'final_conclusions' => [],
            'k_lines_created' => 0,
            'processing_time' => 0
        ];
        
        $startTime = microtime(true);
        
        try {
            // Initialize processing session
            $sessionId = $this->soulProcessor->createSession();
            
            // Input scenario concepts
            $inputConcepts = $this->extractConceptsFromScenario($scenario);
            
            // Process through SOUL framework with FOL integration
            $processingResults = $this->soulProcessor->processInput(
                $inputConcepts,
                ['session_id' => $sessionId, 'enable_fol_reasoning' => true]
            );
            
            $testResults['activated_axioms'] = $this->getActivatedAxioms($sessionId);
            $testResults['reasoning_chain'] = $processingResults['reasoning_chain'] ?? [];
            $testResults['final_conclusions'] = $processingResults['conclusions'] ?? [];
            $testResults['k_lines_created'] = $processingResults['k_lines_created'] ?? 0;
            
            // Clean up session
            $this->soulProcessor->cleanupSession($sessionId);
            
        } catch (\Exception $e) {
            $testResults['error'] = $e->getMessage();
        }
        
        $testResults['processing_time'] = microtime(true) - $startTime;
        
        return $testResults;
    }
    
    private function validateIntegration(): array
    {
        $validation = [
            'axiom_coverage' => $this->validateAxiomCoverage(),
            'agent_linkage' => $this->validateAgentLinkage(),
            'frame_consistency' => $this->validateFrameConsistency(),
            'reasoning_paths' => $this->validateReasoningPaths(),
            'performance_metrics' => $this->collectPerformanceMetrics()
        ];
        
        return $validation;
    }
    
    private function validateAxiomCoverage(): array
    {
        $cypher = "
            MATCH (axiom:LogicalAxiom)
            OPTIONAL MATCH (axiom)-[:IMPLEMENTED_BY]->(agent:PROCEDURAL_AGENT)
            OPTIONAL MATCH (frame:LogicalFrame)-[:DERIVED_FROM]->(axiom)
            RETURN 
                axiom.domain as domain,
                axiom.complexity as complexity,
                COUNT(axiom) as total_axioms,
                COUNT(agent) as axioms_with_agents,
                COUNT(frame) as axioms_with_frames
        ";
        
        $results = $this->neo4j->run($cypher);
        
        return $results->records()->map(fn($record) => [
            'domain' => $record->value('domain'),
            'complexity' => $record->value('complexity'),
            'total_axioms' => $record->value('total_axioms'),
            'axioms_with_agents' => $record->value('axioms_with_agents'),
            'axioms_with_frames' => $record->value('axioms_with_frames'),
            'coverage_percentage' => $record->value('total_axioms') > 0 
                ? ($record->value('axioms_with_agents') / $record->value('total_axioms')) * 100
                : 0
        ])->toArray();
    }
    
    private function validateAgentLinkage(): array
    {
        $cypher = "
            MATCH (agent:PROCEDURAL_AGENT)
            OPTIONAL MATCH (agent)<-[:ACTIVATES]-(concept:Concept)
            OPTIONAL MATCH (agent)<-[:IMPLEMENTED_BY]-(axiom:LogicalAxiom)
            RETURN 
                agent.name as agent_name,
                agent.domain as domain,
                COUNT(DISTINCT concept) as activation_links,
                COUNT(DISTINCT axiom) as axiom_links
        ";
        
        $results = $this->neo4j->run($cypher);
        
        return $results->records()->map(fn($record) => [
            'agent_name' => $record->value('agent_name'),
            'domain' => $record->value('domain'),
            'activation_links' => $record->value('activation_links'),
            'axiom_links' => $record->value('axiom_links'),
            'properly_linked' => $record->value('activation_links') > 0 && $record->value('axiom_links') > 0
        ])->toArray();
    }
    
    private function extractConceptsFromScenario(array $scenario): Collection
    {
        // Extract relevant concepts from the test scenario
        $concepts = collect();
        
        foreach ($scenario as $key => $value) {
            if (is_string($value)) {
                $concepts->push([
                    'name' => strtoupper($key),
                    'value' => $value,
                    'activation' => 1.0
                ]);
            }
        }
        
        return $concepts;
    }
    
    private function getActivatedAxioms(string $sessionId): array
    {
        $cypher = "
            MATCH (session:ProcessingSession {session_id: \$session_id})
            MATCH (axiom:LogicalAxiom)-[:IMPLEMENTED_BY]->(agent:PROCEDURAL_AGENT)
            WHERE agent.name IN session.activated_agents
            RETURN axiom.axiom_id as id, axiom.english as description
        ";
        
        $results = $this->neo4j->run($cypher, ['session_id' => $sessionId]);
        
        return $results->records()->map(fn($record) => [
            'id' => $record->value('id'),
            'description' => $record->value('description')
        ])->toArray();
    }
    
    private function logStep(string $message): void
    {
        logger()->info("FOL Integration: {$message}");
        
        if (app()->runningInConsole()) {
            echo "[" . now()->format('H:i:s') . "] {$message}\n";
        }
    }
}
```

### 6.2 Test Suite

**File**: `tests/Feature/FOLIntegrationTest.php`
```php
<?php

namespace Tests\Feature;

use App\Services\FOL\FOLIntegrationService;
use Tests\TestCase;

class FOLIntegrationTest extends TestCase
{
    public function test_axiom_parsing_and_population()
    {
        $integrationService = app(FOLIntegrationService::class);
        
        $testJsonPath = base_path('tests/fixtures/sample_axioms.json');
        
        $results = $integrationService->integrateAxioms($testJsonPath);
        
        $this->assertArrayHasKey('parsing', $results);
        $this->assertArrayHasKey('population', $results);
        $this->assertTrue($results['parsing']['axioms_parsed'] > 0);
        $this->assertTrue($results['population']['axioms_created'] > 0);
    }
    
    public function test_psychological_reasoning_chain()
    {
        $integrationService = app(FOLIntegrationService::class);
        
        // Test scenario: Person perceives an event and forms belief
        $scenario = [
            'agent' => 'John',
            'action' => 'perceive',
            'eventuality' => 'rain_falling',
            'context' => 'psychological_reasoning'
        ];
        
        $results = $integrationService->testPsychologicalReasoning($scenario);
        
        $this->assertArrayHasKey('reasoning_chain', $results);
        $this->assertArrayHasKey('final_conclusions', $results);
        $this->assertTrue(count($results['activated_axioms']) > 0);
    }
    
    public function test_defeasible_reasoning()
    {
        $integrationService = app(FOLIntegrationService::class);
        
        // Test scenario with exception conditions
        $scenario = [
            'agent' => 'Sarah',
            'entity' => 'penguin',
            'property_query' => 'can_fly',
            'context' => 'defeasible_reasoning'
        ];
        
        $results = $integrationService->testPsychologicalReasoning($scenario);
        
        // Should not conclude that penguin can fly due to exception handling
        $conclusions = collect($results['final_conclusions']);
        $flyConclusion = $conclusions->first(fn($c) => str_contains($c['conclusion'] ?? '', 'can_fly'));
        
        $this->assertFalse($flyConclusion['defeasible_applied'] ?? true);
    }
    
    public function test_k_line_learning()
    {
        $integrationService = app(FOLIntegrationService::class);
        
        // Run same scenario multiple times to test learning
        $scenario = [
            'agent' => 'Alice',
            'action' => 'goal_formation',
            'main_goal' => 'buy_book',
            'causal_belief' => 'money_enables_purchase'
        ];
        
        $firstRun = $integrationService->testPsychologicalReasoning($scenario);
        $secondRun = $integrationService->testPsychologicalReasoning($scenario);
        
        // Second run should be faster due to K-line usage
        $this->assertLessThan($firstRun['processing_time'], $secondRun['processing_time']);
        $this->assertTrue($secondRun['k_lines_created'] >= $firstRun['k_lines_created']);
    }
}
```

### 6.3 Console Commands

**File**: `app/Console/Commands/TestFOLReasoningCommand.php`
```php
<?php

namespace App\Console\Commands;

use App\Services\FOL\FOLIntegrationService;
use Illuminate\Console\Command;

class TestFOLReasoningCommand extends Command
{
    protected $signature = 'soul:test-fol-reasoning 
                          {--scenario= : JSON scenario file to test}
                          {--interactive : Run interactive reasoning session}';
                          
    protected $description = 'Test FOL reasoning integration with SOUL framework';
    
    public function handle(FOLIntegrationService $integrationService): int
    {
        if ($this->option('interactive')) {
            return $this->runInteractiveSession($integrationService);
        }
        
        $scenarioFile = $this->option('scenario');
        
        if ($scenarioFile && file_exists($scenarioFile)) {
            $scenario = json_decode(file_get_contents($scenarioFile), true);
        } else {
            // Default test scenarios
            $scenario = $this->getDefaultTestScenario();
        }
        
        $this->info("Testing psychological reasoning scenario...");
        $this->line("Scenario: " . json_encode($scenario, JSON_PRETTY_PRINT));
        
        $results = $integrationService->testPsychologicalReasoning($scenario);
        
        $this->displayResults($results);
        
        return 0;
    }
    
    private function runInteractiveSession(FOLIntegrationService $integrationService): int
    {
        $this->info("Interactive FOL Reasoning Session");
        $this->info("Enter 'quit' to exit, 'help' for commands");
        
        while (true) {
            $input = $this->ask("Enter scenario or command");
            
            if ($input === 'quit') {
                break;
            }
            
            if ($input === 'help') {
                $this->displayHelp();
                continue;
            }
            
            try {
                $scenario = json_decode($input, true);
                if ($scenario) {
                    $results = $integrationService->testPsychologicalReasoning($scenario);
                    $this->displayResults($results);
                } else {
                    $this->error("Invalid JSON scenario format");
                }
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
            }
        }
        
        return 0;
    }
    
    private function getDefaultTestScenario(): array
    {
        return [
            'agent' => 'TestAgent',
            'action' => 'perceive',
            'eventuality' => 'door_is_open',
            'context' => 'belief_formation_test'
        ];
    }
    
    private function displayResults(array $results): void
    {
        $this->table(['Metric', 'Value'], [
            ['Processing Time', number_format($results['processing_time'], 4) . 's'],
            ['Activated Axioms', count($results['activated_axioms'])],
            ['Reasoning Steps', count($results['reasoning_chain'])],
            ['Final Conclusions', count($results['final_conclusions'])],
            ['K-lines Created', $results['k_lines_created']]
        ]);
        
        if (!empty($results['activated_axioms'])) {
            $this->info("\nActivated Axioms:");
            foreach ($results['activated_axioms'] as $axiom) {
                $this->line("- {$axiom['id']}: {$axiom['description']}");
            }
        }
        
        if (!empty($results['final_conclusions'])) {
            $this->info("\nFinal Conclusions:");
            foreach ($results['final_conclusions'] as $conclusion) {
                $this->line("- " . json_encode($conclusion));
            }
        }
        
        if (isset($results['error'])) {
            $this->error("Error: " . $results['error']);
        }
    }
    
    private function displayHelp(): void
    {
        $this->info("Available commands:");
        $this->line("- JSON scenario: {'agent': 'John', 'action': 'perceive', 'eventuality': 'rain'}");
        $this->line("- quit: Exit the session");
        $this->line("- help: Show this help");
    }
}
```

## Phase 7: Performance Optimization & Monitoring (Weeks 14-16)

### 7.1 Performance Monitoring

**File**: `app/Services/FOL/PerformanceMonitoringService.php`
```php
<?php

namespace App\Services\FOL;

use App\Services\Neo4j\Neo4jService;
use Illuminate\Support\Collection;

class PerformanceMonitoringService
{
    public function __construct(private Neo4jService $neo4j) {}
    
    public function collectPerformanceMetrics(): array
    {
        return [
            'database_metrics' => $this->collectDatabaseMetrics(),
            'reasoning_metrics' => $this->collectReasoningMetrics(),
            'k_line_metrics' => $this->collectKLineMetrics(),
            'agent_performance' => $this->collectAgentPerformance(),
            'memory_usage' => $this->collectMemoryMetrics()
        ];
    }
    
    private function collectDatabaseMetrics(): array
    {
        $cypher = "
            MATCH (n) 
            RETURN 
                labels(n)[0] as node_type,
                COUNT(n) as count
        ";
        
        $results = $this->neo4j->run($cypher);
        
        $nodeCounts = [];
        foreach ($results->records() as $record) {
            $nodeCounts[$record->value('node_type')] = $record->value('count');
        }
        
        return [
            'node_counts' => $nodeCounts,
            'total_nodes' => array_sum($nodeCounts),
            'axiom_coverage' => $this->calculateAxiomCoverage(),
            'relationship_density' => $this->calculateRelationshipDensity()
        ];
    }
    
    private function collectReasoningMetrics(): array
    {
        $cypher = "
            MATCH (axiom:LogicalAxiom)
            OPTIONAL MATCH (axiom)-[:IMPLEMENTED_BY]->(agent:PROCEDURAL_AGENT)
            RETURN 
                axiom.complexity as complexity,
                axiom.domain as domain,
                COUNT(agent) as agent_count,
                AVG(axiom.confidence) as avg_confidence
        ";
        
        $results = $this->neo4j->run($cypher);
        
        return $results->records()->map(fn($record) => [
            'complexity' => $record->value('complexity'),
            'domain' => $record->value('domain'),
            'agent_count' => $record->value('agent_count'),
            'avg_confidence' => $record->value('avg_confidence')
        ])->toArray();
    }
    
    private function calculateAxiomCoverage(): float
    {
        $cypher = "
            MATCH (axiom:LogicalAxiom)
            OPTIONAL MATCH (axiom)-[:IMPLEMENTED_BY]->(agent:PROCEDURAL_AGENT)
            RETURN 
                COUNT(axiom) as total_axioms,
                COUNT(agent) as implemented_axioms
        ";
        
        $result = $this->neo4j->run($cypher)->first();
        
        $total = $result->value('total_axioms');
        $implemented = $result->value('implemented_axioms');
        
        return $total > 0 ? ($implemented / $total) * 100 : 0;
    }
}
```

## Implementation Timeline & Deliverables

### Week-by-Week Breakdown

**Weeks 1-3: Foundation**
- Database schema extensions
- Core data models (LogicalAxiom, PsychologicalPredicate)
- JSON parser service
- Console commands for population

**Weeks 3-5: Graph Population**
- Graph population service
- Relationship building logic
- Batch processing optimization
- Data validation and error handling

**Weeks 5-7: Frame Structures**
- Frame builder service
- Complex axiom pattern recognition
- Frame element creation and linking
- Integration with existing SOUL frames

**Weeks 7-10: Agent Generation**
- Procedural agent generator
- Service template implementations
- Activation and communication links
- Defeasible reasoning handlers

**Weeks 10-12: K-line Learning**
- K-line learning service
- Pattern recognition and storage
- Usage tracking and strength updates
- Integration with reasoning cycles

**Weeks 12-14: Integration & Testing**
- End-to-end integration service
- Comprehensive test suite
- Interactive testing commands
- Performance validation

**Weeks 14-16: Optimization & Monitoring**
- Performance monitoring service
- Query optimization
- Memory usage optimization
- Production readiness validation

### Key Deliverables

1. **Fully populated Neo4j graph** with 1400+ FOL axioms
2. **80+ procedural agents** implementing axiom logic
3. **200+ frame structures** for complex psychological reasoning
4. **K-line learning system** capturing successful reasoning patterns
5. **Comprehensive test suite** validating logical correctness
6. **Performance monitoring** ensuring real-time processing capability
7. **Interactive tools** for testing and debugging reasoning chains

### Success Metrics

- **Coverage**: 80%+ of axioms have corresponding agents
- **Performance**: <100ms average reasoning response time
- **Accuracy**: 90%+ logical consistency in reasoning chains
- **Learning**: 60%+ improvement in repeated pattern processing
- **Scalability**: Support for 10+ concurrent reasoning sessions

This comprehensive plan provides a clear roadmap for integrating FOL axioms into the SOUL framework while maintaining the Society of Mind architecture and enabling sophisticated psychological reasoning capabilities.