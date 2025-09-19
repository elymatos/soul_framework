<?php

namespace Tests\Unit\Domain\BackgroundTheories;

use App\Domain\BackgroundTheories\AxiomExecutors\Axiom5_1Executor;
use App\Domain\BackgroundTheories\AxiomExecutors\Axiom6_13Executor;
use App\Domain\BackgroundTheories\BackgroundReasoningContext;
use App\Domain\BackgroundTheories\BackgroundRepository;
use App\Domain\BackgroundTheories\BackgroundTheoriesService;
use App\Domain\BackgroundTheories\Entities\EventualityEntity;
use App\Domain\BackgroundTheories\Entities\SetEntity;
use App\Domain\BackgroundTheories\Predicates\MemberPredicate;
use App\Domain\BackgroundTheories\Predicates\RexistPredicate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive test suite for Background Theories system
 *
 * Tests the complete FOL-to-executable infrastructure including:
 * - Entity and predicate creation
 * - Axiom execution
 * - Cross-theory reasoning
 * - Database persistence
 */
class BackgroundTheoriesTest extends TestCase
{
    use RefreshDatabase;

    private BackgroundTheoriesService $service;

    private BackgroundRepository $repository;

    private BackgroundReasoningContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new BackgroundRepository;
        $this->context = new BackgroundReasoningContext($this->repository);
        $this->service = new BackgroundTheoriesService($this->context, $this->repository);
    }

    /** @test */
    public function it_can_create_and_persist_entities(): void
    {
        // Create an eventuality entity
        $eventuality = new EventualityEntity([
            'predicate_name' => 'happy',
            'arguments' => ['john'],
        ]);

        $this->service->addEntity($eventuality);

        // Verify entity was created
        $this->assertNotNull($eventuality->getId());
        $this->assertEquals('eventuality', $eventuality->getType());

        // Verify persistence
        $retrieved = $this->service->getEntity($eventuality->getId());
        $this->assertNotNull($retrieved);
        $this->assertEquals($eventuality->getId(), $retrieved->getId());
    }

    /** @test */
    public function it_can_create_and_evaluate_predicates(): void
    {
        // Create an eventuality
        $eventuality = new EventualityEntity([
            'predicate_name' => 'running',
            'arguments' => ['mary'],
        ]);

        // Create a Rexist predicate
        $rexistPredicate = new RexistPredicate($eventuality);

        // Initially, eventuality doesn't really exist
        $this->assertFalse($rexistPredicate->evaluate($this->context));

        // Make it really exist
        $eventuality->realize();

        // Now predicate should evaluate to true
        $this->assertTrue($rexistPredicate->evaluate($this->context));
    }

    /** @test */
    public function it_can_handle_set_operations(): void
    {
        // Create sets
        $set1 = new SetEntity(['elements' => ['a', 'b', 'c']]);
        $set2 = new SetEntity(['elements' => ['c', 'd', 'e']]);

        $this->service->addEntity($set1);
        $this->service->addEntity($set2);

        // Test set operations
        $union = $set1->union($set2);
        $this->assertEquals(5, $union->cardinality());
        $this->assertTrue($union->contains('a'));
        $this->assertTrue($union->contains('e'));

        $intersection = $set1->intersection($set2);
        $this->assertEquals(1, $intersection->cardinality());
        $this->assertTrue($intersection->contains('c'));
    }

    /** @test */
    public function it_can_create_and_evaluate_member_predicates(): void
    {
        $set = new SetEntity(['elements' => ['apple', 'banana', 'orange']]);
        $this->service->addEntity($set);

        // Create member predicate
        $memberPredicate = new MemberPredicate('apple', $set);

        // Should evaluate to true
        $this->assertTrue($memberPredicate->evaluate($this->context));

        // Test with non-member
        $nonMemberPredicate = new MemberPredicate('grape', $set);
        $this->assertFalse($nonMemberPredicate->evaluate($this->context));
    }

    /** @test */
    public function it_can_register_and_execute_axioms(): void
    {
        // Register axiom executors
        $axiom51 = new Axiom5_1Executor;
        $axiom613 = new Axiom6_13Executor;

        $this->service->registerAxiomExecutor('5.1', $axiom51);
        $this->service->registerAxiomExecutor('6.13', $axiom613);

        // Verify registration
        $executors = $this->service->getAxiomExecutors();
        $this->assertCount(2, $executors);
        $this->assertArrayHasKey('5.1', $executors);
        $this->assertArrayHasKey('6.13', $executors);
    }

    /** @test */
    public function it_can_execute_axiom_5_1(): void
    {
        // Create an eventuality that should trigger Axiom 5.1
        $eventuality = new EventualityEntity([
            'predicate_name' => 'walk',
            'arguments' => ['person1', 'location1'],
        ]);

        $this->service->addEntity($eventuality);

        // Register and execute Axiom 5.1
        $axiom51 = new Axiom5_1Executor;
        $this->service->registerAxiomExecutor('5.1', $axiom51);

        $results = $this->service->executeAxiom('5.1');

        // Should have created Rexist predicates
        $this->assertGreaterThan(0, $results->count());

        // Check if Rexist predicate was created
        $rexistPredicates = $results->filter(function ($item) {
            return $item instanceof RexistPredicate;
        });

        $this->assertGreaterThan(0, $rexistPredicates->count());
    }

    /** @test */
    public function it_can_execute_axiom_6_13(): void
    {
        // Create sets for union operation
        $set1 = new SetEntity(['elements' => [1, 2, 3]]);
        $set2 = new SetEntity(['elements' => [3, 4, 5]]);

        $this->service->addEntity($set1);
        $this->service->addEntity($set2);

        // Register and execute Axiom 6.13
        $axiom613 = new Axiom6_13Executor;
        $this->service->registerAxiomExecutor('6.13', $axiom613);

        $results = $this->service->executeAxiom('6.13');

        // Should have created union sets and member predicates
        $this->assertGreaterThan(0, $results->count());

        // Check if union set was created
        $unionSets = $results->filter(function ($item) {
            return $item instanceof SetEntity;
        });

        $this->assertGreaterThan(0, $unionSets->count());
    }

    /** @test */
    public function it_can_perform_cross_theory_reasoning(): void
    {
        // Create entities from different theories
        $eventuality = new EventualityEntity([
            'predicate_name' => 'belong_to',
            'arguments' => ['item1', 'collection1'],
        ]);

        $set = new SetEntity(['elements' => ['item1', 'item2', 'item3']]);

        // Perform cross-theory reasoning
        $results = $this->service->performCrossTheoryReasoning(
            [$eventuality, $set],
            []
        );

        $this->assertIsArray($results);
        $this->assertArrayHasKey('initial_entities', $results);
        $this->assertArrayHasKey('final_state', $results);
        $this->assertEquals(2, $results['initial_entities']);
    }

    /** @test */
    public function it_tracks_axiom_execution_history(): void
    {
        // Register an axiom
        $axiom51 = new Axiom5_1Executor;
        $this->service->registerAxiomExecutor('5.1', $axiom51);

        // Execute it
        $this->service->executeAxiom('5.1');

        // Check execution history
        $history = $this->service->getAxiomExecutionHistory();
        $this->assertGreaterThan(0, $history->count());

        $execution = $history->first();
        $this->assertEquals('5.1', $execution->axiom_id);
    }

    /** @test */
    public function it_can_get_system_statistics(): void
    {
        // Add some entities and predicates
        $eventuality = new EventualityEntity(['predicate_name' => 'test']);
        $set = new SetEntity(['elements' => [1, 2, 3]]);

        $this->service->addEntity($eventuality);
        $this->service->addEntity($set);

        $rexist = new RexistPredicate($eventuality);
        $member = new MemberPredicate(1, $set);

        $this->service->addPredicate($rexist);
        $this->service->addPredicate($member);

        // Get statistics
        $stats = $this->service->getStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('entities', $stats);
        $this->assertArrayHasKey('predicates', $stats);
        $this->assertGreaterThanOrEqual(2, $stats['entities']['total']);
        $this->assertGreaterThanOrEqual(2, $stats['predicates']['total']);
    }

    /** @test */
    public function it_can_validate_system_integrity(): void
    {
        // Add some valid data
        $eventuality = new EventualityEntity([
            'predicate_name' => 'valid_test',
            'arguments' => ['arg1'],
        ]);

        $this->service->addEntity($eventuality);

        // Validate system integrity
        $validation = $this->service->validateSystemIntegrity();

        $this->assertIsArray($validation);
        $this->assertArrayHasKey('is_valid', $validation);
        $this->assertArrayHasKey('issues', $validation);
        $this->assertTrue($validation['is_valid']);
        $this->assertEmpty($validation['issues']);
    }

    /** @test */
    public function it_can_export_and_import_system_state(): void
    {
        // Create some data
        $eventuality = new EventualityEntity([
            'predicate_name' => 'export_test',
            'arguments' => ['data'],
        ]);

        $set = new SetEntity(['elements' => ['export', 'import']]);

        $this->service->addEntity($eventuality);
        $this->service->addEntity($set);

        // Export system state
        $exported = $this->service->exportSystemState();

        $this->assertIsArray($exported);
        $this->assertArrayHasKey('entities', $exported);
        $this->assertArrayHasKey('predicates', $exported);

        // Clear system
        $this->service->clearAll();

        // Verify it's empty
        $state = $this->service->getSystemState();
        $this->assertEquals(0, $state['entities_count']);

        // Import the data back
        $importResults = $this->service->importSystemState($exported);

        $this->assertGreaterThan(0, $importResults['entities_imported']);
    }

    /** @test */
    public function it_handles_entity_validation(): void
    {
        // Create invalid eventuality (missing predicate name)
        $invalidEventuality = new EventualityEntity([
            'arguments' => ['test'],
            // Missing 'predicate_name'
        ]);

        $this->assertFalse($invalidEventuality->validate());

        // Create valid eventuality
        $validEventuality = new EventualityEntity([
            'predicate_name' => 'test_predicate',
            'arguments' => ['arg1', 'arg2'],
        ]);

        $this->assertTrue($validEventuality->validate());
    }

    /** @test */
    public function it_handles_defeasible_reasoning(): void
    {
        $eventuality = new EventualityEntity([
            'predicate_name' => 'defeasible_test',
        ]);

        $rexistPredicate = new RexistPredicate($eventuality);

        // Test defeasible reasoning (etc() method)
        $this->assertTrue($rexistPredicate->etc());
    }

    /** @test */
    public function it_generates_proper_fol_representations(): void
    {
        $eventuality = new EventualityEntity([
            'predicate_name' => 'walk',
            'arguments' => ['person'],
        ]);

        $set = new SetEntity(['elements' => ['a', 'b']]);

        $rexistPredicate = new RexistPredicate($eventuality);
        $memberPredicate = new MemberPredicate('a', $set);

        // Test FOL generation
        $rexistFOL = $rexistPredicate->toFOL();
        $memberFOL = $memberPredicate->toFOL();

        $this->assertStringContains('Rexist', $rexistFOL);
        $this->assertStringContains('member', $memberFOL);
    }

    /** @test */
    public function it_maintains_execution_trace(): void
    {
        // Execute some operations
        $eventuality = new EventualityEntity(['predicate_name' => 'trace_test']);
        $this->service->addEntity($eventuality);

        // Get execution trace
        $trace = $this->service->getExecutionTrace();

        $this->assertIsArray($trace);
        $this->assertGreaterThan(0, count($trace));

        // Clear trace
        $this->service->clearExecutionTrace();
        $clearedTrace = $this->service->getExecutionTrace();

        $this->assertEmpty($clearedTrace);
    }
}
