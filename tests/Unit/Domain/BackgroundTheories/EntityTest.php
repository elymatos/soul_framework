<?php

namespace Tests\Unit\Domain\BackgroundTheories;

use Tests\TestCase;
use App\Domain\BackgroundTheories\Entities\EventualityEntity;
use App\Domain\BackgroundTheories\Entities\SetEntity;

/**
 * Test suite for Background Theory entities
 */
class EntityTest extends TestCase
{
    /** @test */
    public function eventuality_entity_handles_basic_operations(): void
    {
        $eventuality = new EventualityEntity([
            'predicate_name' => 'run',
            'arguments' => ['john', 'park']
        ]);

        $this->assertEquals('eventuality', $eventuality->getType());
        $this->assertEquals('run', $eventuality->getPredicateName());
        $this->assertEquals(['john', 'park'], $eventuality->getArguments());
        $this->assertEquals(2, $eventuality->getArity());
        $this->assertEquals('john', $eventuality->getArg(1));
        $this->assertEquals('park', $eventuality->getArg(2));
    }

    /** @test */
    public function eventuality_entity_handles_reality_status(): void
    {
        $eventuality = new EventualityEntity(['predicate_name' => 'exist']);

        $this->assertFalse($eventuality->reallyExists());
        
        $eventuality->realize();
        $this->assertTrue($eventuality->reallyExists());
        
        $eventuality->unrealize();
        $this->assertFalse($eventuality->reallyExists());
    }

    /** @test */
    public function set_entity_handles_basic_operations(): void
    {
        $set = new SetEntity(['elements' => ['a', 'b', 'c']]);

        $this->assertEquals('set', $set->getType());
        $this->assertEquals(['a', 'b', 'c'], $set->getElements());
        $this->assertEquals(3, $set->cardinality());
        $this->assertFalse($set->isEmpty());
    }

    /** @test */
    public function set_entity_handles_membership(): void
    {
        $set = new SetEntity(['elements' => ['x', 'y', 'z']]);

        $this->assertTrue($set->contains('x'));
        $this->assertFalse($set->contains('w'));

        $set->addElement('w');
        $this->assertTrue($set->contains('w'));
        $this->assertEquals(4, $set->cardinality());

        $set->removeElement('x');
        $this->assertFalse($set->contains('x'));
        $this->assertEquals(3, $set->cardinality());
    }

    /** @test */
    public function set_entity_handles_duplicates(): void
    {
        $set = new SetEntity();
        
        $set->addElement('a');
        $set->addElement('a'); // Duplicate
        $set->addElement('b');
        
        $this->assertEquals(2, $set->cardinality());
        $this->assertEquals(['a', 'b'], $set->getElements());
    }

    /** @test */
    public function set_entity_handles_union_operation(): void
    {
        $set1 = new SetEntity(['elements' => [1, 2, 3]]);
        $set2 = new SetEntity(['elements' => [3, 4, 5]]);

        $union = $set1->union($set2);
        
        $this->assertEquals(5, $union->cardinality());
        $this->assertTrue($union->contains(1));
        $this->assertTrue($union->contains(5));
    }

    /** @test */
    public function set_entity_handles_intersection_operation(): void
    {
        $set1 = new SetEntity(['elements' => [1, 2, 3, 4]]);
        $set2 = new SetEntity(['elements' => [3, 4, 5, 6]]);

        $intersection = $set1->intersection($set2);
        
        $this->assertEquals(2, $intersection->cardinality());
        $this->assertTrue($intersection->contains(3));
        $this->assertTrue($intersection->contains(4));
        $this->assertFalse($intersection->contains(1));
        $this->assertFalse($intersection->contains(6));
    }

    /** @test */
    public function set_entity_handles_subset_relations(): void
    {
        $superset = new SetEntity(['elements' => [1, 2, 3, 4, 5]]);
        $subset = new SetEntity(['elements' => [2, 4]]);
        $nonSubset = new SetEntity(['elements' => [2, 6]]);

        $this->assertTrue($subset->isSubsetOf($superset));
        $this->assertFalse($nonSubset->isSubsetOf($superset));
        $this->assertTrue($superset->isSubsetOf($superset)); // Self is subset
    }

    /** @test */
    public function set_entity_handles_equality(): void
    {
        $set1 = new SetEntity(['elements' => ['a', 'b', 'c']]);
        $set2 = new SetEntity(['elements' => ['c', 'a', 'b']]); // Different order
        $set3 = new SetEntity(['elements' => ['a', 'b', 'd']]);

        $this->assertTrue($set1->equals($set2));
        $this->assertFalse($set1->equals($set3));
    }

    /** @test */
    public function entities_handle_json_serialization(): void
    {
        $eventuality = new EventualityEntity([
            'predicate_name' => 'serialize_test',
            'arguments' => ['arg1']
        ]);

        $json = $eventuality->jsonSerialize();
        
        $this->assertIsArray($json);
        $this->assertEquals('eventuality', $json['type']);
        $this->assertEquals('serialize_test', $json['attributes']['predicate_name']);
    }

    /** @test */
    public function entities_handle_database_serialization(): void
    {
        $set = new SetEntity(['elements' => ['db', 'test']]);
        
        $dbArray = $set->toDatabaseArray();
        
        $this->assertIsArray($dbArray);
        $this->assertEquals('set', $dbArray['type']);
        $this->assertJson($dbArray['attributes']);
        
        // Test deserialization
        $newSet = new SetEntity();
        $newSet->fromDatabaseArray($dbArray);
        
        $this->assertEquals($set->getId(), $newSet->getId());
        $this->assertEquals($set->getType(), $newSet->getType());
        $this->assertEquals($set->getElements(), $newSet->getElements());
    }

    /** @test */
    public function entities_handle_copying(): void
    {
        $original = new EventualityEntity([
            'predicate_name' => 'copy_test',
            'arguments' => ['original']
        ]);

        $copy = $original->copy();
        
        $this->assertNotEquals($original->getId(), $copy->getId());
        $this->assertEquals($original->getType(), $copy->getType());
        $this->assertEquals($original->getPredicateName(), $copy->getPredicateName());
        $this->assertEquals($original->getArguments(), $copy->getArguments());
    }

    /** @test */
    public function entities_handle_attribute_operations(): void
    {
        $entity = new EventualityEntity();
        
        $entity->setAttribute('test_key', 'test_value');
        $this->assertEquals('test_value', $entity->getAttribute('test_key'));
        
        $entity->setAttributes(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertEquals('value1', $entity->getAttribute('key1'));
        $this->assertEquals('value2', $entity->getAttribute('key2'));
        
        // Test default value
        $this->assertEquals('default', $entity->getAttribute('nonexistent', 'default'));
    }

    /** @test */
    public function entity_validation_works_correctly(): void
    {
        // Valid eventuality
        $validEventuality = new EventualityEntity([
            'predicate_name' => 'valid',
            'arguments' => ['arg1']
        ]);
        $this->assertTrue($validEventuality->validate());

        // Invalid eventuality (missing predicate name)
        $invalidEventuality = new EventualityEntity([
            'arguments' => ['arg1']
        ]);
        $this->assertFalse($invalidEventuality->validate());

        // Valid set
        $validSet = new SetEntity(['elements' => [1, 2, 3]]);
        $this->assertTrue($validSet->validate());

        // Invalid set (elements not an array)
        $invalidSet = new SetEntity(['elements' => 'not an array']);
        $this->assertFalse($invalidSet->validate());
    }
}