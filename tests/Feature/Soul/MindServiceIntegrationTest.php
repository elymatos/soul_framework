<?php

namespace Tests\Feature\Soul;

use Tests\TestCase;
use App\Soul\Services\MindService;
use App\Soul\Services\FrameDefinitionRegistryService;
use App\Soul\Services\Neo4jFrameService;
use App\Soul\Bootstrap\PrimitiveFrameLoader;
use App\Soul\Contracts\FrameDefinitionRegistry;
use App\Soul\Contracts\Neo4jService;
use Mockery;
use Laudis\Neo4j\Contracts\ClientInterface;

class MindServiceIntegrationTest extends TestCase
{
    protected $mockNeo4j;
    protected FrameDefinitionRegistryService $registry;
    protected Neo4jFrameService $neo4jService;
    protected MindService $mindService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Neo4j client to avoid actual database calls in tests
        $this->mockNeo4j = Mockery::mock(ClientInterface::class);
        
        // Create real service instances
        $this->registry = new FrameDefinitionRegistryService();
        $this->neo4jService = new Neo4jFrameService($this->mockNeo4j);
        
        // Load primitive frames
        $loader = new PrimitiveFrameLoader($this->registry);
        $loader->loadAllPrimitives();
        
        // Create MindService with real implementations
        $this->mindService = new MindService($this->neo4jService, $this->registry);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_mindservice_can_start_processing_session()
    {
        $input = ['text' => 'John pushes the box with force'];
        
        $sessionId = $this->mindService->startProcessingSession($input);
        
        $this->assertIsString($sessionId);
        $this->assertStringContains('session_', $sessionId);
        
        // Check that session is tracked
        $sessions = $this->mindService->getProcessingSessions();
        $this->assertTrue($sessions->has($sessionId));
        
        $session = $sessions->get($sessionId);
        $this->assertEquals('active', $session['status']);
        $this->assertEquals($input, $session['input']);
    }

    public function test_mindservice_has_access_to_primitive_frames()
    {
        // Test that MindService can access frame definitions from registry
        $this->assertTrue($this->mindService->hasFrameDefinition('FORCE'));
        $this->assertTrue($this->mindService->hasFrameDefinition('EMOTION'));
        $this->assertTrue($this->mindService->hasFrameDefinition('IS_A'));
        $this->assertTrue($this->mindService->hasFrameDefinition('CONTAINER'));
        
        // Test getting frame definitions by type
        $imageSchemaFrames = $this->registry->getByType('image_schema');
        $cspFrames = $this->registry->getByType('csp');
        $relationFrames = $this->registry->getByType('relation');
        
        $this->assertGreaterThan(0, $imageSchemaFrames->count());
        $this->assertGreaterThan(0, $cspFrames->count());
        $this->assertGreaterThan(0, $relationFrames->count());
    }

    public function test_mindservice_can_instantiate_frames()
    {
        // Mock Neo4j operations for frame instance creation
        $this->mockNeo4jForFrameCreation();
        
        $sessionId = $this->mindService->startProcessingSession(['text' => 'test']);
        
        // Instantiate a primitive frame
        $forceInstance = $this->mindService->instantiateFrame('FORCE', [], $sessionId);
        
        $this->assertNotNull($forceInstance);
        $this->assertEquals('FORCE', $forceInstance->getFrameId());
        $this->assertEquals('image_schema', $forceInstance->getType());
        
        // Check that instance is registered with MindService
        $this->assertTrue($this->mindService->hasFrameInstance($forceInstance->getInstanceId()));
        $this->assertEquals($forceInstance, $this->mindService->getFrameInstance($forceInstance->getInstanceId()));
    }

    public function test_mindservice_cognitive_processing_pipeline()
    {
        // Mock Neo4j operations
        $this->mockNeo4jForFullPipeline();
        
        $input = ['text' => 'The person feels strong emotion'];
        $sessionId = $this->mindService->startProcessingSession($input);
        
        // Process the input through the cognitive pipeline
        $response = $this->mindService->processInput($input, $sessionId);
        
        $this->assertIsArray($response);
        $this->assertArrayHasKey('session_id', $response);
        $this->assertArrayHasKey('instances_count', $response);
        $this->assertArrayHasKey('processing_time', $response);
        $this->assertArrayHasKey('result', $response);
        
        $this->assertEquals($sessionId, $response['session_id']);
        $this->assertEquals('processed', $response['result']);
    }

    public function test_mindservice_can_end_processing_session()
    {
        // Mock Neo4j operations for session cleanup
        $this->mockNeo4jForSessionCleanup();
        
        $sessionId = $this->mindService->startProcessingSession(['text' => 'test']);
        
        // Create some instances
        $this->mindService->instantiateFrame('EMOTION', [], $sessionId);
        $this->mindService->instantiateFrame('STATE', [], $sessionId);
        
        // End the session
        $result = $this->mindService->endProcessingSession($sessionId);
        
        $this->assertIsArray($result);
        $this->assertEquals($sessionId, $result['id']);
        $this->assertEquals('completed', $result['status']);
        $this->assertArrayHasKey('ended_at', $result);
        
        // Session should be removed from active sessions
        $sessions = $this->mindService->getProcessingSessions();
        $this->assertFalse($sessions->has($sessionId));
    }

    public function test_mindservice_statistics()
    {
        $stats = $this->mindService->getStatistics();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('active_instances', $stats);
        $this->assertArrayHasKey('active_sessions', $stats);
        $this->assertArrayHasKey('registered_frames', $stats);
        $this->assertArrayHasKey('instances_created', $stats);
        $this->assertArrayHasKey('sessions_started', $stats);
        
        // Should have loaded primitive frames
        $this->assertGreaterThan(0, $stats['registered_frames']);
    }

    public function test_primitive_frame_loader_integration()
    {
        $loader = new PrimitiveFrameLoader($this->registry);
        $stats = $loader->getLoadingStatistics();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('loaded_categories', $stats);
        $this->assertArrayHasKey('available_categories', $stats);
        
        // Should have loaded all categories
        $expectedCategories = [
            'image_schemas',
            'csp_primitives',
            'meta_schemas',
            'relation_frames',
            'structural_schemas'
        ];
        
        foreach ($expectedCategories as $category) {
            $this->assertContains($category, $stats['loaded_categories']);
        }
    }

    protected function mockNeo4jForFrameCreation(): void
    {
        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('count')->andReturn(1);
        $mockResult->shouldReceive('first->get')->andReturn('created_instance_id');
        
        $this->mockNeo4j->shouldReceive('run')
            ->andReturn($mockResult);
    }

    protected function mockNeo4jForFullPipeline(): void
    {
        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('count')->andReturn(1);
        $mockResult->shouldReceive('first->get')->andReturn('result_value');
        
        $this->mockNeo4j->shouldReceive('run')
            ->andReturn($mockResult);
    }

    protected function mockNeo4jForSessionCleanup(): void
    {
        // Mock for instance creation
        $createResult = Mockery::mock();
        $createResult->shouldReceive('count')->andReturn(1);
        $createResult->shouldReceive('first->get')->andReturn('created_id');
        
        // Mock for instance deletion
        $deleteResult = Mockery::mock();
        $deleteResult->shouldReceive('first->get')->with('deleted_count')->andReturn(1);
        
        // Mock for session archival
        $archiveResult = Mockery::mock();
        $archiveResult->shouldReceive('count')->andReturn(1);
        
        $this->mockNeo4j->shouldReceive('run')
            ->andReturn($createResult, $deleteResult, $archiveResult);
    }
}