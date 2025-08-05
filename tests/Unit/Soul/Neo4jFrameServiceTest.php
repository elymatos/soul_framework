<?php

namespace Tests\Unit\Soul;

use Tests\TestCase;
use Mockery;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use App\Soul\Services\Neo4jFrameService;
use App\Soul\FrameInstance;
use App\Soul\FrameElementInstance;
use Illuminate\Support\Collection;

class Neo4jFrameServiceTest extends TestCase
{
    protected $mockNeo4j;
    protected Neo4jFrameService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockNeo4j = Mockery::mock(ClientInterface::class);
        $this->service = new Neo4jFrameService($this->mockNeo4j);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_create_frame_instance_node()
    {
        $frameInstance = $this->createTestFrameInstance();
        $sessionId = 'test_session_123';

        // Mock the Neo4j result
        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('count')->andReturn(1);
        $mockResult->shouldReceive('first->get')->with('created_id')->andReturn('test_instance_1');

        $this->mockNeo4j->shouldReceive('run')
            ->once()
            ->with(
                Mockery::type('string'),
                Mockery::type('array')
            )
            ->andReturn($mockResult);

        $result = $this->service->createFrameInstanceNode($frameInstance, $sessionId);

        $this->assertTrue($result);
    }

    public function test_can_delete_frame_instance_node()
    {
        $instanceId = 'test_instance_1';

        // Mock the Neo4j result
        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('first->get')->with('deleted_count')->andReturn(1);

        $this->mockNeo4j->shouldReceive('run')
            ->once()
            ->with(
                Mockery::type('string'),
                ['instance_id' => $instanceId]
            )
            ->andReturn($mockResult);

        $result = $this->service->deleteFrameInstanceNode($instanceId);

        $this->assertTrue($result);
    }

    public function test_can_create_instance_relationship()
    {
        $fromId = 'instance_1';
        $toId = 'instance_2';
        $relationshipType = 'RELATES_TO';
        $properties = ['strength' => 0.8];

        // Mock the Neo4j result
        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('count')->andReturn(1);

        $this->mockNeo4j->shouldReceive('run')
            ->once()
            ->with(
                Mockery::type('string'),
                Mockery::type('array')
            )
            ->andReturn($mockResult);

        $result = $this->service->createInstanceRelationship($fromId, $toId, $relationshipType, $properties);

        $this->assertTrue($result);
    }

    public function test_can_archive_processing_session()
    {
        $session = [
            'id' => 'session_123',
            'started_at' => now(),
            'ended_at' => now(),
            'input' => ['text' => 'test input'],
            'status' => 'completed',
            'instances' => new Collection([
                $this->createTestFrameInstance(),
                $this->createTestFrameInstance()
            ])
        ];

        // Mock the Neo4j result
        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('count')->andReturn(1);

        $this->mockNeo4j->shouldReceive('run')
            ->once()
            ->with(
                Mockery::type('string'),
                Mockery::type('array')
            )
            ->andReturn($mockResult);

        $result = $this->service->archiveProcessingSession($session);

        $this->assertTrue($result);
    }

    public function test_can_query_frame_instances()
    {
        $criteria = [
            'type' => 'test_type',
            'session_id' => 'session_123'
        ];

        // Mock the Neo4j result
        $mockRecord1 = Mockery::mock();
        $mockRecord1->shouldReceive('get')->with('instance_id')->andReturn('instance_1');
        $mockRecord1->shouldReceive('get')->with('frame_id')->andReturn('TEST_FRAME');
        $mockRecord1->shouldReceive('get')->with('label')->andReturn('Test Frame');
        $mockRecord1->shouldReceive('get')->with('type')->andReturn('test_type');
        $mockRecord1->shouldReceive('get')->with('session_id')->andReturn('session_123');
        $mockRecord1->shouldReceive('get')->with('created_at')->andReturn('2023-01-01T00:00:00Z');
        $mockRecord1->shouldReceive('get')->with('status')->andReturn('active');

        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('getIterator')->andReturn(new \ArrayIterator([$mockRecord1]));

        $this->mockNeo4j->shouldReceive('run')
            ->once()
            ->with(
                Mockery::type('string'),
                $criteria
            )
            ->andReturn($mockResult);

        $result = $this->service->queryFrameInstances($criteria);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(1, $result->count());
        
        $instance = $result->first();
        $this->assertEquals('instance_1', $instance['instance_id']);
        $this->assertEquals('TEST_FRAME', $instance['frame_id']);
    }

    public function test_can_get_instance_relationships()
    {
        $instanceId = 'instance_1';

        // Mock the Neo4j result
        $mockRecord = Mockery::mock();
        $mockRecord->shouldReceive('get')->with('relationship_type')->andReturn('RELATES_TO');
        $mockRecord->shouldReceive('get')->with('relationship_properties')->andReturn(
            Mockery::mock()->shouldReceive('toArray')->andReturn(['strength' => 0.8])->getMock()
        );
        $mockRecord->shouldReceive('get')->with('other_instance_id')->andReturn('instance_2');
        $mockRecord->shouldReceive('get')->with('other_frame_id')->andReturn('OTHER_FRAME');
        $mockRecord->shouldReceive('get')->with('other_label')->andReturn('Other Frame');
        $mockRecord->shouldReceive('get')->with('other_type')->andReturn('other_type');
        $mockRecord->shouldReceive('get')->with('is_outgoing')->andReturn(true);

        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('getIterator')->andReturn(new \ArrayIterator([$mockRecord]));

        $this->mockNeo4j->shouldReceive('run')
            ->once()
            ->with(
                Mockery::type('string'),
                ['instance_id' => $instanceId]
            )
            ->andReturn($mockResult);

        $result = $this->service->getInstanceRelationships($instanceId);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(1, $result->count());
        
        $relationship = $result->first();
        $this->assertEquals('RELATES_TO', $relationship['relationship_type']);
        $this->assertEquals('instance_2', $relationship['other_instance']['instance_id']);
        $this->assertTrue($relationship['is_outgoing']);
    }

    public function test_can_get_database_statistics()
    {
        // Mock the Neo4j result
        $mockRecord = Mockery::mock();
        $mockRecord->shouldReceive('get')->with('frame_instances')->andReturn(10);
        $mockRecord->shouldReceive('get')->with('sessions')->andReturn(5);
        $mockRecord->shouldReceive('get')->with('relationships')->andReturn(25);

        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('count')->andReturn(1);
        $mockResult->shouldReceive('first')->andReturn($mockRecord);

        $this->mockNeo4j->shouldReceive('run')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturn($mockResult);

        $result = $this->service->getDatabaseStatistics();

        $this->assertIsArray($result);
        $this->assertEquals(10, $result['frame_instances']);
        $this->assertEquals(5, $result['processing_sessions']);
        $this->assertEquals(25, $result['relationships']);
    }

    protected function createTestFrameInstance(): FrameInstance
    {
        $instance = new FrameInstance('test_instance_1', 'TEST_FRAME', 'Test Frame', 'test_type');
        
        // Add a test frame element
        $feInstance = new FrameElementInstance('test_element', 'TestType', 'Test element', [], false);
        $instance->addFrameElement($feInstance);
        
        return $instance;
    }
}