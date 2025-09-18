# Chapter 5 FOL-to-PHP Cognitive Framework Implementation Guide

## Project Overview

This project converts First-Order Logic axioms from Chapter 5 of "A Formal Theory of Commonsense Psychology" into a PHP-based cognitive framework using Neo4j for graph storage and spreading activation for concept discovery.

### Key Architectural Decisions

**Cognitive Framework Approach**: Unlike traditional database systems, this implements a spreading activation network where concepts (including relationships) can accumulate and propagate activation to discover emergent conceptual connections.

**Hyper-Reified Relationships**: All relationships become nodes in the graph, allowing the system to reason about relationships as first-class concepts. For example, `atTime(event, time)` creates four nodes: `atTime`, `event`, `time`, and an anonymous relation node.

**Inheritance Strategy**: Reified predicates (e.g., `give'`) become classes that inherit from base predicate classes (e.g., `give`). Since PHP doesn't support multiple inheritance, the conceptual structure is represented in the Neo4j graph using `IS_A` relationships.

## Chapter 5 Axioms Analysis

### Predicate Classification

Based on analysis of the 19 axioms in Chapter 5, predicates fall into these categories:

**Reified Action Predicates** (Classes inheriting from Model):
- `give'`, `run'`, `go'` - Events/actions that become eventuality classes

**Structural/Meta Predicates** (Methods or Properties):
- `argn` - "x is the nth argument of e" - Method `getArgument(n)`
- `pred` - "p is the predicate of e" - Property `getPredicate()`
- `arity` - "n is the arity of e" - Property `getArity()`
- `arg` - "x is an argument of e" - Method `hasArgument(x)`

**Fundamental Predicates** (Base Classes/Interfaces):
- `eventuality` - Base interface for all reified events
- `Rexist` - "really exists" - Property/method `exists()`

**Relational Predicates** (Graph Relationships):
- `gen` - "e1 generates e2" - Relationship between eventuality nodes
- `atTime`, `atLoc` - Temporal/spatial relationships as nodes

**Type Predicates** (Validation Methods):
- `nonNegInteger`, `posInteger` - Type constraints for validation

### Key Axioms and Their Implementation

**Axiom 5.1**: Relation between primed and unprimed predicates
```php
// p is true of x iff there exists eventuality e where p'(e,x) and e really exists
public function exists(): bool {
    return $this->exists && $this->validate();
}
```

**Axiom 5.6**: Eventuality is self-argument
```php
public function __construct() {
    $this->arguments[0] = $this; // Self-reference as 0th argument
}
```

**Axiom 5.17**: Gen is antireflexive
```php
if ($source->getId() === $target->getId()) {
    throw new ValidationException("An eventuality cannot generate itself");
}
```

## Core Implementation Files

### 1. Base Conceptual Framework

**File: `app/CognitiveFramework/ConceptualNode.php`**

```php
<?php

namespace App\CognitiveFramework;

abstract class ConceptualNode {
    protected string $id;
    protected string $type;
    protected float $activationLevel = 0.0;
    protected array $connections = [];
    protected bool $isActive = false;
    
    public function __construct(string $id, string $type) {
        $this->id = $id;
        $this->type = $type;
    }
    
    public function setActivation(float $level): self {
        $this->activationLevel = max(0.0, min(1.0, $level));
        $this->isActive = $this->activationLevel > 0.1;
        return $this;
    }
    
    public function getActivation(): float {
        return $this->activationLevel;
    }
    
    public function addActivation(float $amount): self {
        return $this->setActivation($this->activationLevel + $amount);
    }
    
    public function decay(float $decayRate = 0.1): self {
        $this->activationLevel *= (1.0 - $decayRate);
        $this->isActive = $this->activationLevel > 0.1;
        return $this;
    }
    
    abstract public function toGraphNode(): array;
}
```

### 2. Eventuality Base Implementation

**File: `app/CognitiveFramework/Eventuality.php`**

```php
<?php

namespace App\CognitiveFramework;

use Illuminate\Validation\ValidationException;

trait EventualityArgumentsTrait {
    protected array $arguments = [];
    protected int $arity = 0;
    
    public function setArgument(int $n, $value): self {
        if ($n < 0) {
            throw new ValidationException("Argument position must be non-negative integer");
        }
        $this->arguments[$n] = $value;
        return $this;
    }
    
    public function getArgument(int $n) {
        return $this->arguments[$n] ?? null;
    }
    
    public function hasArgument($value): bool {
        return in_array($value, $this->arguments);
    }
    
    public function getArguments(): array {
        return $this->arguments;
    }
    
    public function getArity(): int {
        return $this->arity;
    }
}

abstract class Eventuality extends ConceptualNode {
    use EventualityArgumentsTrait;
    
    protected string $predicate;
    protected bool $exists = false;
    
    public function __construct(string $id, string $predicate, int $arity) {
        parent::__construct($id, 'eventuality');
        $this->predicate = $predicate;
        $this->arity = $arity;
        $this->arguments[0] = $this; // Axiom 5.6: Self-reference
    }
    
    public function getPredicate(): string {
        return $this->predicate;
    }
    
    public function setExists(bool $exists): self {
        $this->exists = $exists;
        return $this;
    }
    
    public function exists(): bool {
        return $this->exists;
    }
    
    public function validate(): bool {
        // Axiom 5.15: Complete structure validation
        $nonNullArgs = array_filter($this->arguments, fn($arg) => $arg !== null);
        if (count($nonNullArgs) - 1 !== $this->arity) {
            throw new ValidationException(
                "Eventuality must have exactly {$this->arity} arguments, got " . 
                (count($nonNullArgs) - 1)
            );
        }
        return true;
    }
    
    public function toGraphNode(): array {
        return [
            'id' => $this->id,
            'type' => 'Eventuality',
            'predicate' => $this->predicate,
            'arity' => $this->arity,
            'exists' => $this->exists,
            'activation' => $this->activationLevel,
            'is_active' => $this->isActive
        ];
    }
}
```

### 3. Chapter 5 Specific Implementations

**File: `app/CognitiveFramework/Chapter5/GiveEventuality.php`**

```php
<?php

namespace App\CognitiveFramework\Chapter5;

use App\CognitiveFramework\Eventuality;

class GiveEventuality extends Eventuality {
    
    public function __construct(string $id = null) {
        parent::__construct($id ?? uniqid('give_'), 'give', 3);
    }
    
    public function setGiving($giver, $gift, $receiver): self {
        $this->setArgument(1, $giver);   // Axiom 5.3: first argument
        $this->setArgument(2, $gift);    // Axiom 5.4: second argument  
        $this->setArgument(3, $receiver); // Axiom 5.5: third argument
        return $this;
    }
    
    public function getGiver() {
        return $this->getArgument(1);
    }
    
    public function getGift() {
        return $this->getArgument(2);
    }
    
    public function getReceiver() {
        return $this->getArgument(3);
    }
}
```

**File: `app/CognitiveFramework/Chapter5/RunEventuality.php`**

```php
<?php

namespace App\CognitiveFramework\Chapter5;

use App\CognitiveFramework\Eventuality;

class RunEventuality extends Eventuality {
    
    public function __construct(string $id = null) {
        parent::__construct($id ?? uniqid('run_'), 'run', 1);
    }
    
    public function setRunner($runner): self {
        $this->setArgument(1, $runner);
        return $this;
    }
    
    public function getRunner() {
        return $this->getArgument(1);
    }
    
    // Axiom 5.2: Running generates going
    public function generateGoing(): GoEventuality {
        $going = new GoEventuality();
        $going->setGoer($this->getRunner());
        
        return $going;
    }
}
```

**File: `app/CognitiveFramework/Chapter5/GoEventuality.php`**

```php
<?php

namespace App\CognitiveFramework\Chapter5;

use App\CognitiveFramework\Eventuality;

class GoEventuality extends Eventuality {
    
    public function __construct(string $id = null) {
        parent::__construct($id ?? uniqid('go_'), 'go', 1);
    }
    
    public function setGoer($goer): self {
        $this->setArgument(1, $goer);
        return $this;
    }
    
    public function getGoer() {
        return $this->getArgument(1);
    }
}
```

### 4. Generation Relationship Handler

**File: `app/CognitiveFramework/Chapter5/GenerationRelationship.php`**

```php
<?php

namespace App\CognitiveFramework\Chapter5;

use App\CognitiveFramework\ConceptualNode;
use App\CognitiveFramework\Eventuality;
use Illuminate\Validation\ValidationException;

class GenerationRelationship extends ConceptualNode {
    private Eventuality $source;
    private Eventuality $target;
    
    public function __construct(Eventuality $source, Eventuality $target) {
        parent::__construct(uniqid('gen_'), 'generation');
        $this->source = $source;
        $this->target = $target;
        $this->validate();
    }
    
    public function validate(): bool {
        // Axiom 5.17: Gen is antireflexive
        if ($this->source->getId() === $this->target->getId()) {
            throw new ValidationException("An eventuality cannot generate itself");
        }
        
        // Axiom 5.18: Gen modus ponens with Rexist
        if ($this->source->exists()) {
            $this->target->setExists(true);
        }
        
        return true;
    }
    
    public function getSource(): Eventuality {
        return $this->source;
    }
    
    public function getTarget(): Eventuality {
        return $this->target;
    }
    
    public function toGraphNode(): array {
        return [
            'id' => $this->id,
            'type' => 'Generation',
            'source_id' => $this->source->getId(),
            'target_id' => $this->target->getId(),
            'preserves_time' => true,  // Axiom 5.20
            'preserves_location' => true, // Axiom 5.21
            'activation' => $this->activationLevel,
            'is_active' => $this->isActive
        ];
    }
}
```

### 5. Spreading Activation Engine

**File: `app/CognitiveFramework/SpreadingActivationEngine.php`**

```php
<?php

namespace App\CognitiveFramework;

class SpreadingActivationEngine {
    private array $nodes = [];
    private array $relationships = [];
    private float $spreadingThreshold = 0.1;
    private float $decayRate = 0.1;
    private int $maxIterations = 10;
    
    public function addNode(ConceptualNode $node): self {
        $this->nodes[$node->getId()] = $node;
        return $this;
    }
    
    public function addRelationship(ConceptualNode $relationship): self {
        $this->relationships[$relationship->getId()] = $relationship;
        return $this;
    }
    
    public function setInitialActivation(array $seedNodes, float $activation = 1.0): self {
        foreach ($seedNodes as $nodeId) {
            if (isset($this->nodes[$nodeId])) {
                $this->nodes[$nodeId]->setActivation($activation);
            }
        }
        return $this;
    }
    
    public function spreadActivation(): array {
        $activationHistory = [];
        
        for ($iteration = 0; $iteration < $this->maxIterations; $iteration++) {
            $currentActivations = $this->getCurrentActivations();
            $activationHistory[] = $currentActivations;
            
            // Spread activation through relationships
            $this->propagateActivation();
            
            // Apply decay to all nodes
            foreach ($this->nodes as $node) {
                $node->decay($this->decayRate);
            }
            
            foreach ($this->relationships as $relationship) {
                $relationship->decay($this->decayRate);
            }
            
            // Check for convergence
            if ($this->hasConverged($currentActivations)) {
                break;
            }
        }
        
        return $activationHistory;
    }
    
    private function propagateActivation(): void {
        // Simple spreading: each active node spreads to its relationships
        foreach ($this->relationships as $relationship) {
            if (method_exists($relationship, 'getSource') && method_exists($relationship, 'getTarget')) {
                $source = $relationship->getSource();
                $target = $relationship->getTarget();
                
                // Bidirectional spreading
                if ($source->isActive()) {
                    $target->addActivation($source->getActivation() * 0.3);
                    $relationship->addActivation($source->getActivation() * 0.2);
                }
                
                if ($target->isActive()) {
                    $source->addActivation($target->getActivation() * 0.3);
                    $relationship->addActivation($target->getActivation() * 0.2);
                }
            }
        }
    }
    
    private function getCurrentActivations(): array {
        $activations = [];
        foreach ($this->nodes as $id => $node) {
            $activations[$id] = $node->getActivation();
        }
        foreach ($this->relationships as $id => $relationship) {
            $activations[$id] = $relationship->getActivation();
        }
        return $activations;
    }
    
    private function hasConverged(array $currentActivations): bool {
        $totalActivation = array_sum($currentActivations);
        return $totalActivation < 0.01;
    }
    
    public function getTopActivatedNodes(int $limit = 10): array {
        $allNodes = array_merge($this->nodes, $this->relationships);
        
        usort($allNodes, function($a, $b) {
            return $b->getActivation() <=> $a->getActivation();
        });
        
        return array_slice($allNodes, 0, $limit);
    }
    
    public function getNodes(): array {
        return $this->nodes;
    }
    
    public function getRelationships(): array {
        return $this->relationships;
    }
}
```

### 6. Neo4j Graph Manager

**File: `app/CognitiveFramework/GraphManager.php`**

```php
<?php

namespace App\CognitiveFramework;

use Laudis\Neo4j\ClientBuilder;

class GraphManager {
    private $neo4jClient;
    
    public function __construct(string $neo4jUri = 'bolt://localhost:7687', string $username = 'neo4j', string $password = 'cognitive123') {
        $this->neo4jClient = ClientBuilder::create()
            ->withDriver('bolt', $neo4jUri, \Laudis\Neo4j\Authentication\Authenticate::basic($username, $password))
            ->build();
    }
    
    public function saveCognitiveNetwork(SpreadingActivationEngine $engine): void {
        // Clear existing data
        $this->neo4jClient->run("MATCH (n) DETACH DELETE n");
        
        // Save all nodes
        foreach ($engine->getNodes() as $node) {
            $this->saveNode($node);
        }
        
        // Save all relationships
        foreach ($engine->getRelationships() as $relationship) {
            $this->saveRelationshipAsNode($relationship);
        }
        
        // Create inheritance relationships
        $this->createInheritanceStructure();
    }
    
    private function saveNode(ConceptualNode $node): void {
        $nodeData = $node->toGraphNode();
        
        $cypher = "
            CREATE (n:ConceptualNode {
                id: \$id,
                type: \$type,
                activation: \$activation,
                is_active: \$is_active
            })
            SET n += \$properties
        ";
        
        $this->neo4jClient->run($cypher, [
            'id' => $nodeData['id'],
            'type' => $nodeData['type'],
            'activation' => $nodeData['activation'] ?? 0.0,
            'is_active' => $nodeData['is_active'] ?? false,
            'properties' => $nodeData
        ]);
    }
    
    private function saveRelationshipAsNode(ConceptualNode $relationship): void {
        $relData = $relationship->toGraphNode();
        
        // Save relationship as a node
        $cypher = "
            CREATE (r:RelationshipNode {
                id: \$id,
                type: \$type,
                activation: \$activation,
                is_active: \$is_active
            })
            SET r += \$properties
        ";
        
        $this->neo4jClient->run($cypher, [
            'id' => $relData['id'],
            'type' => $relData['type'],
            'activation' => $relData['activation'] ?? 0.0,
            'is_active' => $relData['is_active'] ?? false,
            'properties' => $relData
        ]);
        
        // Connect to source and target if it's a generation relationship
        if (method_exists($relationship, 'getSource') && method_exists($relationship, 'getTarget')) {
            $this->connectGenerationNodes($relationship);
        }
    }
    
    private function connectGenerationNodes($relationship): void {
        $source = $relationship->getSource();
        $target = $relationship->getTarget();
        
        $cypher = "
            MATCH (r:RelationshipNode {id: \$rel_id})
            MATCH (s:ConceptualNode {id: \$source_id})
            MATCH (t:ConceptualNode {id: \$target_id})
            CREATE (r)-[:CONNECTS_FROM]->(s)
            CREATE (r)-[:CONNECTS_TO]->(t)
        ";
        
        $this->neo4jClient->run($cypher, [
            'rel_id' => $relationship->getId(),
            'source_id' => $source->getId(),
            'target_id' => $target->getId()
        ]);
    }
    
    private function createInheritanceStructure(): void {
        // Create IS_A relationships for eventualities
        $cypher = "
            MATCH (e:ConceptualNode {type: 'eventuality'})
            WHERE e.predicate IS NOT NULL
            MERGE (p:PredicateType {name: e.predicate})
            CREATE (e)-[:IS_A]->(p)
        ";
        
        $this->neo4jClient->run($cypher);
        
        // Create EVENTUALITY base type
        $cypher = "
            MATCH (e:ConceptualNode {type: 'eventuality'})
            MERGE (base:ConceptualType {name: 'Eventuality'})
            CREATE (e)-[:INSTANCE_OF]->(base)
        ";
        
        $this->neo4jClient->run($cypher);
    }
    
    public function loadCognitiveNetwork(): SpreadingActivationEngine {
        $engine = new SpreadingActivationEngine();
        
        // Load nodes
        $nodesCypher = "MATCH (n:ConceptualNode) RETURN n";
        $nodesResult = $this->neo4jClient->run($nodesCypher);
        
        foreach ($nodesResult as $record) {
            $nodeData = $record->get('n')->getProperties();
            // Would need factory pattern to reconstruct proper node types
            // For now, simplified reconstruction
        }
        
        return $engine;
    }
    
    public function visualizeActivation(): array {
        $cypher = "
            MATCH (n)
            WHERE n.activation > 0
            RETURN n.id, n.type, n.activation, n.is_active
            ORDER BY n.activation DESC
        ";
        
        $result = $this->neo4jClient->run($cypher);
        return $result->toArray();
    }
}
```

## Testing and Usage

### 7. Test Implementation

**File: `tests/Feature/Chapter5CognitiveTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\CognitiveFramework\Chapter5\GiveEventuality;
use App\CognitiveFramework\Chapter5\RunEventuality;
use App\CognitiveFramework\Chapter5\GenerationRelationship;
use App\CognitiveFramework\SpreadingActivationEngine;
use App\CognitiveFramework\GraphManager;

class Chapter5CognitiveTest extends TestCase {
    
    public function test_give_eventuality_creation() {
        $give = new GiveEventuality('give_001');
        $give->setGiving('John', 'Book', 'Mary');
        
        $this->assertEquals('give', $give->getPredicate());
        $this->assertEquals(3, $give->getArity());
        $this->assertEquals('John', $give->getGiver());
        $this->assertEquals('Book', $give->getGift());
        $this->assertEquals('Mary', $give->getReceiver());
        
        $this->assertTrue($give->validate());
    }
    
    public function test_generation_relationship() {
        $run = new RunEventuality('run_001');
        $run->setRunner('John')->setExists(true);
        
        $go = $run->generateGoing();
        
        $generation = new GenerationRelationship($run, $go);
        
        // Test Axiom 5.18: Gen modus ponens
        $this->assertTrue($go->exists());
        
        // Test Axiom 5.17: Antireflexivity
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        new GenerationRelationship($run, $run);
    }
    
    public function test_spreading_activation() {
        $engine = new SpreadingActivationEngine();
        
        $john = new \App\CognitiveFramework\EntityConcept('john');
        $give = new GiveEventuality('give_001');
        $give->setGiving($john, 'book', 'mary');
        
        $engine->addNode($john);
        $engine->addNode($give);
        
        // Activate John concept
        $engine->setInitialActivation(['john'], 1.0);
        
        $history = $engine->spreadActivation();
        
        $this->assertGreaterThan(0, count($history));
        $this->assertTrue($john->isActive());
    }
    
    public function test_neo4j_integration() {
        $engine = new SpreadingActivationEngine();
        
        $give = new GiveEventuality('give_test');
        $give->setGiving('Alice', 'flower', 'Bob');
        
        $engine->addNode($give);
        
        $graphManager = new GraphManager();
        $graphManager->saveCognitiveNetwork($engine);
        
        $visualization = $graphManager->visualizeActivation();
        
        $this->assertIsArray($visualization);
    }
}
```

### 8. Console Command for Testing

**File: `app/Console/Commands/TestCognitive.php`**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\CognitiveFramework\Chapter5\GiveEventuality;
use App\CognitiveFramework\Chapter5\RunEventuality;
use App\CognitiveFramework\Chapter5\GenerationRelationship;
use App\CognitiveFramework\SpreadingActivationEngine;
use App\CognitiveFramework\GraphManager;

class TestCognitive extends Command {
    protected $signature = 'cognitive:test-chapter5';
    protected $description = 'Test Chapter 5 cognitive framework implementation';
    
    public function handle() {
        $this->info('Testing Chapter 5 Cognitive Framework...');
        
        // Create test scenario
        $engine = new SpreadingActivationEngine();
        
        // Create giving event
        $give = new GiveEventuality('give_001');
        $give->setGiving('John', 'Book', 'Mary')->setExists(true);
        
        // Create running and generation
        $run = new RunEventuality('run_001');
        $run->setRunner('John')->setExists(true);
        $go = $run->generateGoing();
        
        $generation = new GenerationRelationship($run, $go);
        
        // Add to engine
        $engine->addNode($give);
        $engine->addNode($run);
        $engine->addNode($go);
        $engine->addRelationship($generation);
        
        // Set initial activation
        $engine->setInitialActivation(['give_001', 'run_001'], 0.8);
        
        $this->info('Initial state:');
        $this->displayActivations($engine);
        
        // Run spreading activation
        $history = $engine->spreadActivation();
        
        $this->info("\nAfter spreading activation:");
        $this->displayActivations($engine);
        
        $this->info("\nTop activated concepts:");
        $topActivated = $engine->getTopActivatedNodes(5);
        foreach ($topActivated as $node) {
            $this->line(sprintf(
                "- %s (%s): %.3f", 
                $node->getId(), 
                $node->getType(), 
                $node->getActivation()
            ));
        }
        
        // Save to Neo4j
        $this->info("\nSaving to Neo4j...");
        $graphManager = new GraphManager();
        $graphManager->saveCognitiveNetwork($engine);
        
        $this->info("Test completed successfully!");
    }
    
    private function displayActivations(SpreadingActivationEngine $engine) {
        foreach ($engine->getNodes() as $node) {
            if ($node->getActivation() > 0) {
                $this->line(sprintf(
                    "Node %s: %.3f", 
                    $node->getId(), 
                    $node->getActivation()
                ));
            }
        }
        
        foreach ($engine->getRelationships() as $rel) {
            if ($rel->getActivation() > 0) {
                $this->line(sprintf(
                    "Relationship %s: %.3f", 
                    $rel->getId(), 
                    $rel->getActivation()
                ));
            }
        }
    }
}
```

### Neo4j Queries for Verification

```cypher
// View all conceptual nodes
MATCH (n:ConceptualNode) RETURN n

// View inheritance structure
MATCH (e:ConceptualNode)-[:IS_A]->(p:PredicateType) RETURN e, p

// View generation relationships
MATCH (r:RelationshipNode)-[:CONNECTS_FROM]->(s)-[:CONNECTS_TO]->(t) 
WHERE r.type = 'Generation' 
RETURN s, r, t

// View activated concepts
MATCH (n) WHERE n.activation > 0 RETURN n ORDER BY n.activation DESC

// Visualize spreading activation paths
MATCH path = (start)-[*1..3]-(end) 
WHERE start.activation > 0.5 AND end.activation > 0.1
RETURN path
```

## Next Steps

1. **Validation**: Run all tests and verify axiom compliance
2. **Performance**: Benchmark spreading activation with larger networks
3. **Extension**: Add remaining predicates from Chapter 5
4. **Integration**: Connect with Laravel web interface for interactive exploration
5. **Scaling**: Test with full axiom set from other chapters

This implementation provides a solid foundation for expanding to the complete commonsense psychology framework while maintaining cognitive plausibility and computational efficiency.
