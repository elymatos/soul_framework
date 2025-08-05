<?php

namespace App\Soul\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Soul\Frame;
use App\Soul\Contracts\FrameDefinitionRegistry;
use App\Soul\Exceptions\FrameNotFoundException;
use App\Soul\Exceptions\FrameAlreadyExistsException;

/**
 * FrameDefinitionRegistryService - Manages frame definitions in memory
 * 
 * This service provides fast in-memory access to frame definitions with
 * support for automatic loading of primitive frames and type-based queries.
 */
class FrameDefinitionRegistryService implements FrameDefinitionRegistry
{
    protected Collection $frameDefinitions;
    protected array $typeIndex;
    protected bool $initialized = false;

    public function __construct()
    {
        $this->frameDefinitions = new Collection();
        $this->typeIndex = [];
        
        // Initialize with basic frame types
        $this->initializeFrameTypes();
    }

    /**
     * Get all registered frame definitions
     */
    public function getAllFrameDefinitions(): Collection
    {
        $this->ensureInitialized();
        return $this->frameDefinitions->values();
    }

    /**
     * Register a frame definition
     */
    public function register(Frame $frame): void
    {
        $frameId = $frame->getId();

        if ($this->frameDefinitions->has($frameId)) {
            throw new FrameAlreadyExistsException("Frame already exists: {$frameId}");
        }

        // Register frame
        $this->frameDefinitions->put($frameId, $frame);

        // Update type index
        $frameType = $frame->getType();
        if (!isset($this->typeIndex[$frameType])) {
            $this->typeIndex[$frameType] = new Collection();
        }
        $this->typeIndex[$frameType]->put($frameId, $frame);

        Log::debug("SOUL Registry: Registered frame", [
            'frame_id' => $frameId,
            'type' => $frameType,
            'label' => $frame->getLabel()
        ]);
    }

    /**
     * Get frame definition by ID
     */
    public function get(string $frameId): ?Frame
    {
        $this->ensureInitialized();
        return $this->frameDefinitions->get($frameId);
    }

    /**
     * Check if frame exists
     */
    public function has(string $frameId): bool
    {
        $this->ensureInitialized();
        return $this->frameDefinitions->has($frameId);
    }

    /**
     * Get frames by type
     */
    public function getByType(string $type): Collection
    {
        $this->ensureInitialized();
        return $this->typeIndex[$type] ?? new Collection();
    }

    /**
     * Get all available frame types
     */
    public function getAvailableTypes(): array
    {
        $this->ensureInitialized();
        return array_keys($this->typeIndex);
    }

    /**
     * Get registry statistics
     */
    public function getStatistics(): array
    {
        $this->ensureInitialized();
        
        $stats = [
            'total_frames' => $this->frameDefinitions->count(),
            'types' => []
        ];

        foreach ($this->typeIndex as $type => $frames) {
            $stats['types'][$type] = $frames->count();
        }

        return $stats;
    }

    /**
     * Load frames from a specific type category
     */
    public function loadFramesByCategory(string $category): int
    {
        $loaded = 0;

        try {
            switch ($category) {
                case 'image_schemas':
                    $loaded = $this->loadImageSchemaFrames();
                    break;
                case 'csp_primitives':
                    $loaded = $this->loadCSPPrimitiveFrames();
                    break;
                case 'meta_schemas':
                    $loaded = $this->loadMetaSchemaFrames();
                    break;
                case 'relation_frames':
                    $loaded = $this->loadRelationFrames();
                    break;
                default:
                    Log::warning("SOUL Registry: Unknown frame category", ['category' => $category]);
            }

            if ($loaded > 0) {
                Log::info("SOUL Registry: Loaded frame category", [
                    'category' => $category,
                    'count' => $loaded
                ]);
            }

        } catch (\Exception $e) {
            Log::error("SOUL Registry: Failed to load frame category", [
                'category' => $category,
                'error' => $e->getMessage()
            ]);
        }

        return $loaded;
    }

    /**
     * Clear all frame definitions (for testing)
     */
    public function clear(): void
    {
        $this->frameDefinitions->clear();
        $this->typeIndex = [];
        $this->initialized = false;
        
        Log::debug("SOUL Registry: Cleared all frame definitions");
    }

    /**
     * Ensure registry is initialized with basic frames
     */
    protected function ensureInitialized(): void
    {
        if (!$this->initialized) {
            $this->loadBasicFrames();
            $this->initialized = true;
        }
    }

    /**
     * Initialize basic frame type categories
     */
    protected function initializeFrameTypes(): void
    {
        $basicTypes = [
            'primitive',
            'derived', 
            'image_schema',
            'csp',
            'meta_schema',
            'relation',
            'structural'
        ];

        foreach ($basicTypes as $type) {
            $this->typeIndex[$type] = new Collection();
        }
    }

    /**
     * Load basic frames needed for system operation
     */
    protected function loadBasicFrames(): void
    {
        try {
            // Load primitive frame categories
            $totalLoaded = 0;
            $totalLoaded += $this->loadRelationFrames();
            $totalLoaded += $this->loadImageSchemaFrames();
            $totalLoaded += $this->loadCSPPrimitiveFrames();
            $totalLoaded += $this->loadMetaSchemaFrames();

            Log::info("SOUL Registry: Initialization complete", [
                'total_frames_loaded' => $totalLoaded,
                'available_types' => count($this->typeIndex)
            ]);

        } catch (\Exception $e) {
            Log::error("SOUL Registry: Initialization failed", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Load relation frame definitions
     */
    protected function loadRelationFrames(): int
    {
        // These would be loaded from actual RelationFrame classes
        // For now, we'll create placeholders that can be replaced by actual implementations
        
        $relationFrames = [
            'IS_A' => 'Hierarchical classification relationship',
            'CAUSES' => 'Causal relationship between entities',
            'SHARED_SLOT' => 'Minsky shared slot relationship',
            'PART_OF' => 'Component-whole relationship',
            'ENABLES' => 'Enabling relationship',
            'INHIBITS' => 'Inhibiting relationship'
        ];

        $loaded = 0;
        foreach ($relationFrames as $frameId => $description) {
            try {
                // Create a basic relation frame placeholder
                // This will be replaced by actual RelationFrame subclasses
                $frame = new class($frameId, $frameId, 'relation') extends Frame {
                    public function match(array $input): float { return 0.0; }
                };

                if (!$this->has($frameId)) {
                    $this->register($frame);
                    $loaded++;
                }
            } catch (\Exception $e) {
                Log::warning("SOUL Registry: Failed to load relation frame", [
                    'frame_id' => $frameId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $loaded;
    }

    /**
     * Load Image Schema primitive frames
     */
    protected function loadImageSchemaFrames(): int
    {
        $imageSchemas = [
            'FORCE' => 'Dynamic force schema',
            'REGION' => 'Bounded spatial region',
            'OBJECT' => 'Discrete physical entity',
            'POINT' => 'Zero-dimensional location',
            'CURVE' => 'One-dimensional path',
            'AXIS' => 'Directional orientation',
            'MOVEMENT' => 'Change of location over time',
            'CONTAINER' => 'Enclosed spatial boundary',
            'PATH' => 'Route from source to goal',
            'SCALE' => 'Relative magnitude relationship'
        ];

        $loaded = 0;
        foreach ($imageSchemas as $frameId => $description) {
            try {
                // Create Image Schema frame placeholder
                $frame = new class($frameId, $description, 'image_schema') extends Frame {
                    public function match(array $input): float { return 0.0; }
                };

                if (!$this->has($frameId)) {
                    $this->register($frame);
                    $loaded++;
                }
            } catch (\Exception $e) {
                Log::warning("SOUL Registry: Failed to load image schema frame", [
                    'frame_id' => $frameId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $loaded;
    }

    /**
     * Load Common Sense Psychology primitive frames
     */
    protected function loadCSPPrimitiveFrames(): int
    {
        $cspPrimitives = [
            'EMOTION' => 'Affective state primitive',
            'STATE' => 'Stable condition primitive', 
            'CAUSE' => 'Causal relation primitive',
            'NUMBER' => 'Quantitative primitive',
            'SCALE' => 'Magnitude comparison primitive',
            'BELIEF' => 'Epistemic state primitive',
            'INTENTION' => 'Goal-directed state primitive',
            'DESIRE' => 'Motivational state primitive'
        ];

        $loaded = 0;
        foreach ($cspPrimitives as $frameId => $description) {
            try {
                // Create CSP primitive frame placeholder
                $frame = new class($frameId, $description, 'csp') extends Frame {
                    public function match(array $input): float { return 0.0; }
                };

                if (!$this->has($frameId)) {
                    $this->register($frame);
                    $loaded++;
                }
            } catch (\Exception $e) {
                Log::warning("SOUL Registry: Failed to load CSP primitive frame", [
                    'frame_id' => $frameId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $loaded;
    }

    /**
     * Load Meta-Schema frames
     */
    protected function loadMetaSchemaFrames(): int
    {
        $metaSchemas = [
            'ENTITY' => 'High-level entity meta-schema',
            'STATE' => 'High-level state meta-schema', 
            'PROCESS' => 'High-level process meta-schema',
            'CHANGE' => 'High-level change meta-schema'
        ];

        $loaded = 0;
        foreach ($metaSchemas as $frameId => $description) {
            try {
                // Create Meta-Schema frame placeholder
                $frame = new class($frameId, $description, 'meta_schema') extends Frame {
                    public function match(array $input): float { return 0.0; }
                };

                if (!$this->has($frameId)) {
                    $this->register($frame);
                    $loaded++;
                }
            } catch (\Exception $e) {
                Log::warning("SOUL Registry: Failed to load meta-schema frame", [
                    'frame_id' => $frameId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $loaded;
    }
}