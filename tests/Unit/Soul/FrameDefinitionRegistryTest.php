<?php

namespace Tests\Unit\Soul;

use Tests\TestCase;
use App\Soul\Services\FrameDefinitionRegistryService;
use App\Soul\Frame;
use App\Soul\FrameElement;
use App\Soul\Exceptions\FrameAlreadyExistsException;

class FrameDefinitionRegistryTest extends TestCase
{
    protected FrameDefinitionRegistryService $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new FrameDefinitionRegistryService();
        $this->registry->clear(); // Start with clean registry
    }

    public function test_can_register_frame_definition()
    {
        $frame = $this->createTestFrame('TEST_FRAME', 'Test Frame', 'test');
        
        $this->registry->register($frame);
        
        $this->assertTrue($this->registry->has('TEST_FRAME'));
        $this->assertEquals($frame, $this->registry->get('TEST_FRAME'));
    }

    public function test_cannot_register_duplicate_frame()
    {
        $frame1 = $this->createTestFrame('TEST_FRAME', 'Test Frame 1', 'test');
        $frame2 = $this->createTestFrame('TEST_FRAME', 'Test Frame 2', 'test');
        
        $this->registry->register($frame1);
        
        $this->expectException(FrameAlreadyExistsException::class);
        $this->registry->register($frame2);
    }

    public function test_can_get_frames_by_type()
    {
        $frame1 = $this->createTestFrame('FRAME_1', 'Frame 1', 'type_a');
        $frame2 = $this->createTestFrame('FRAME_2', 'Frame 2', 'type_a');
        $frame3 = $this->createTestFrame('FRAME_3', 'Frame 3', 'type_b');
        
        $this->registry->register($frame1);
        $this->registry->register($frame2);
        $this->registry->register($frame3);
        
        $typeAFrames = $this->registry->getByType('type_a');
        $typeBFrames = $this->registry->getByType('type_b');
        
        $this->assertEquals(2, $typeAFrames->count());
        $this->assertEquals(1, $typeBFrames->count());
        $this->assertTrue($typeAFrames->has('FRAME_1'));
        $this->assertTrue($typeAFrames->has('FRAME_2'));
        $this->assertTrue($typeBFrames->has('FRAME_3'));
    }

    public function test_loads_primitive_frames_automatically()
    {
        // Trigger initialization by calling a method that needs it
        $allFrames = $this->registry->getAllFrameDefinitions();
        
        // Should have loaded some primitive frames
        $this->assertGreaterThan(0, $allFrames->count());
        
        // Check for some expected primitive frames
        $this->assertTrue($this->registry->has('FORCE'));
        $this->assertTrue($this->registry->has('EMOTION'));
        $this->assertTrue($this->registry->has('IS_A'));
    }

    public function test_can_load_frames_by_category()
    {
        $this->registry->clear();
        
        $imageSchemaCount = $this->registry->loadFramesByCategory('image_schemas');
        $cspCount = $this->registry->loadFramesByCategory('csp_primitives');
        
        $this->assertGreaterThan(0, $imageSchemaCount);
        $this->assertGreaterThan(0, $cspCount);
        
        // Verify specific frames were loaded
        $this->assertTrue($this->registry->has('FORCE'));
        $this->assertTrue($this->registry->has('CONTAINER'));
        $this->assertTrue($this->registry->has('EMOTION'));
        $this->assertTrue($this->registry->has('STATE'));
    }

    public function test_returns_statistics()
    {
        $stats = $this->registry->getStatistics();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_frames', $stats);
        $this->assertArrayHasKey('types', $stats);
        $this->assertIsArray($stats['types']);
        
        // Should have some frames loaded
        $this->assertGreaterThan(0, $stats['total_frames']);
    }

    public function test_returns_available_types()
    {
        $types = $this->registry->getAvailableTypes();
        
        $this->assertIsArray($types);
        $this->assertGreaterThan(0, count($types));
        
        // Should include basic frame types
        $this->assertContains('image_schema', $types);
        $this->assertContains('csp', $types);
        $this->assertContains('relation', $types);
    }

    protected function createTestFrame(string $id, string $label, string $type): Frame
    {
        return new class($id, $label, $type) extends Frame {
            public function match(array $input): float
            {
                return 0.5; // Basic test implementation
            }
        };
    }
}