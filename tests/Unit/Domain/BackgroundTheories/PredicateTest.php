<?php

namespace Tests\Unit\Domain\BackgroundTheories;

use Tests\TestCase;
use App\Domain\BackgroundTheories\BackgroundReasoningContext;
use App\Domain\BackgroundTheories\BackgroundRepository;
use App\Domain\BackgroundTheories\Entities\EventualityEntity;
use App\Domain\BackgroundTheories\Entities\SetEntity;
use App\Domain\BackgroundTheories\Predicates\RexistPredicate;
use App\Domain\BackgroundTheories\Predicates\MemberPredicate;

/**
 * Test suite for Background Theory predicates
 */
class PredicateTest extends TestCase
{
    private BackgroundReasoningContext $context;

    protected function setUp(): void
    {
        parent::setUp();
        $repository = new BackgroundRepository();
        $this->context = new BackgroundReasoningContext($repository);
    }

    /** @test */
    public function rexist_predicate_handles_basic_operations(): void
    {
        $eventuality = new EventualityEntity([
            'predicate_name' => 'test',
            'arguments' => ['arg1']
        ]);

        $rexistPredicate = new RexistPredicate($eventuality);

        $this->assertEquals('Rexist', $rexistPredicate->getName());
        $this->assertEquals(1, $rexistPredicate->getArity());
        $this->assertEquals($eventuality, $rexistPredicate->getEntity());
        $this->assertFalse($rexistPredicate->getReallyExists());
    }

    /** @test */
    public function rexist_predicate_evaluates_correctly(): void
    {
        $eventuality = new EventualityEntity(['predicate_name' => 'exist_test']);
        $rexistPredicate = new RexistPredicate($eventuality);

        // Initially false
        $this->assertFalse($rexistPredicate->evaluate($this->context));

        // Make eventuality really exist
        $eventuality->realize();
        
        // Now should evaluate to true
        $this->assertTrue($rexistPredicate->evaluate($this->context));
    }

    /** @test */
    public function rexist_predicate_handles_reality_changes(): void
    {
        $eventuality = new EventualityEntity(['predicate_name' => 'reality_test']);
        $rexistPredicate = new RexistPredicate($eventuality);

        $rexistPredicate->makeReallyExist();
        $this->assertTrue($eventuality->reallyExists());
        $this->assertTrue($rexistPredicate->getReallyExists());

        $rexistPredicate->makeNotReallyExist();
        $this->assertFalse($eventuality->reallyExists());
        $this->assertFalse($rexistPredicate->getReallyExists());
    }

    /** @test */
    public function member_predicate_handles_basic_operations(): void
    {
        $set = new SetEntity(['elements' => ['a', 'b', 'c']]);
        $memberPredicate = new MemberPredicate('a', $set);

        $this->assertEquals('member', $memberPredicate->getName());
        $this->assertEquals(2, $memberPredicate->getArity());
        $this->assertEquals('a', $memberPredicate->getElement());
        $this->assertEquals($set, $memberPredicate->getSet());
    }

    /** @test */
    public function member_predicate_evaluates_correctly(): void
    {
        $set = new SetEntity(['elements' => ['x', 'y', 'z']]);
        
        $memberPredicate = new MemberPredicate('x', $set);
        $this->assertTrue($memberPredicate->evaluate($this->context));
        
        $nonMemberPredicate = new MemberPredicate('w', $set);
        $this->assertFalse($nonMemberPredicate->evaluate($this->context));
    }

    /** @test */
    public function member_predicate_handles_membership_changes(): void
    {
        $set = new SetEntity(['elements' => ['existing']]);
        $memberPredicate = new MemberPredicate('new_element', $set);

        // Initially not a member
        $this->assertFalse($memberPredicate->evaluate($this->context));

        // Make it a member
        $memberPredicate->makeMember();
        $this->assertTrue($memberPredicate->evaluate($this->context));
        $this->assertTrue($set->contains('new_element'));

        // Remove membership
        $memberPredicate->removeMember();
        $this->assertFalse($memberPredicate->evaluate($this->context));
        $this->assertFalse($set->contains('new_element'));
    }

    /** @test */
    public function predicates_generate_correct_fol_representations(): void
    {
        $eventuality = new EventualityEntity(['predicate_name' => 'fol_test']);
        $set = new SetEntity(['elements' => ['item1']]);
        
        $rexistPredicate = new RexistPredicate($eventuality);
        $memberPredicate = new MemberPredicate('item1', $set);

        $rexistFOL = $rexistPredicate->toFOL();
        $memberFOL = $memberPredicate->toFOL();

        $this->assertStringContains('Rexist', $rexistFOL);
        $this->assertStringContains($eventuality->getId(), $rexistFOL);
        
        $this->assertStringContains('member', $memberFOL);
        $this->assertStringContains('item1', $memberFOL);
        $this->assertStringContains($set->getId(), $memberFOL);
    }

    /** @test */
    public function predicates_handle_defeasible_reasoning(): void
    {
        $eventuality = new EventualityEntity(['predicate_name' => 'defeasible']);
        $set = new SetEntity(['elements' => []]);

        $rexistPredicate = new RexistPredicate($eventuality);
        $memberPredicate = new MemberPredicate('item', $set);

        // Test etc() method (defeasible reasoning)
        $this->assertTrue($rexistPredicate->etc());
        $this->assertTrue($memberPredicate->etc());
    }

    /** @test */
    public function predicates_handle_serialization(): void
    {
        $eventuality = new EventualityEntity(['predicate_name' => 'serialize']);
        $rexistPredicate = new RexistPredicate($eventuality);
        $rexistPredicate->realize();

        $json = $rexistPredicate->jsonSerialize();
        
        $this->assertIsArray($json);
        $this->assertEquals('Rexist', $json['name']);
        $this->assertEquals(1, $json['arity']);
        $this->assertTrue($json['really_exists']);
    }

    /** @test */
    public function predicates_handle_database_operations(): void
    {
        $eventuality = new EventualityEntity(['predicate_name' => 'db_test']);
        $rexistPredicate = new RexistPredicate($eventuality);
        $rexistPredicate->setMetadata('test_key', 'test_value');

        $dbArray = $rexistPredicate->toDatabaseArray();
        
        $this->assertIsArray($dbArray);
        $this->assertEquals('Rexist', $dbArray['name']);
        $this->assertEquals(1, $dbArray['arity']);
        $this->assertJson($dbArray['arguments']);
        $this->assertJson($dbArray['metadata']);
    }

    /** @test */
    public function predicates_handle_equality_comparison(): void
    {
        $eventuality1 = new EventualityEntity(['predicate_name' => 'equal1']);
        $eventuality2 = new EventualityEntity(['predicate_name' => 'equal2']);
        
        $predicate1a = new RexistPredicate($eventuality1);
        $predicate1b = new RexistPredicate($eventuality1);
        $predicate2 = new RexistPredicate($eventuality2);

        // Note: This tests structural equality, not object identity
        $this->assertTrue($predicate1a->getName() === $predicate1b->getName());
        $this->assertFalse($predicate1a->equals($predicate2));
    }

    /** @test */
    public function predicates_provide_meaningful_descriptions(): void
    {
        $eventuality = new EventualityEntity([
            'predicate_name' => 'walk',
            'arguments' => ['john', 'park']
        ]);
        
        $set = new SetEntity(['elements' => ['apple', 'banana']]);
        
        $rexistPredicate = new RexistPredicate($eventuality);
        $memberPredicate = new MemberPredicate('apple', $set);

        $rexistDesc = $rexistPredicate->describe();
        $memberDesc = $memberPredicate->describe();

        $this->assertStringContains('Rexist', $rexistDesc);
        $this->assertStringContains('eventuality', $rexistDesc);
        
        $this->assertStringContains('member', $memberDesc);
        $this->assertStringContains('apple', $memberDesc);
    }

    /** @test */
    public function predicates_handle_metadata_operations(): void
    {
        $eventuality = new EventualityEntity(['predicate_name' => 'metadata_test']);
        $predicate = new RexistPredicate($eventuality);

        $predicate->setMetadata('source', 'axiom_5_1');
        $predicate->setMetadata('confidence', 0.95);

        $metadata = $predicate->getMetadata();
        
        $this->assertEquals('axiom_5_1', $metadata['source']);
        $this->assertEquals(0.95, $metadata['confidence']);
    }

    /** @test */
    public function predicates_handle_realization_status(): void
    {
        $eventuality = new EventualityEntity(['predicate_name' => 'realization']);
        $predicate = new RexistPredicate($eventuality);

        $this->assertFalse($predicate->getReallyExists());

        $predicate->realize();
        $this->assertTrue($predicate->getReallyExists());

        $predicate->unrealize();
        $this->assertFalse($predicate->getReallyExists());
    }
}