<?php

namespace App\Soul\Bootstrap;

use Illuminate\Support\Facades\Log;
use App\Soul\Frame;
use App\Soul\FrameElement;
use App\Soul\Contracts\FrameDefinitionRegistry;
use App\Soul\Exceptions\FrameInstantiationException;

/**
 * PrimitiveFrameLoader - Loads primitive frame definitions for the SOUL Framework
 * 
 * This class provides a structured way to define and load primitive frames
 * including Image Schemas, CSP primitives, Meta-schemas, and Relation frames.
 */
class PrimitiveFrameLoader
{
    protected FrameDefinitionRegistry $registry;
    protected array $loadedCategories = [];

    public function __construct(FrameDefinitionRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Load all primitive frame categories
     */
    public function loadAllPrimitives(): array
    {
        $results = [
            'image_schemas' => $this->loadImageSchemaFrames(),
            'csp_primitives' => $this->loadCSPPrimitiveFrames(),
            'meta_schemas' => $this->loadMetaSchemaFrames(),
            'relation_frames' => $this->loadRelationFrames(),
            'structural_schemas' => $this->loadStructuralSchemaFrames()
        ];

        $totalLoaded = array_sum($results);
        
        Log::info("SOUL Bootstrap: Loaded all primitive frames", [
            'total_loaded' => $totalLoaded,
            'breakdown' => $results
        ]);

        return $results;
    }

    /**
     * Load Image Schema primitive frames
     */
    public function loadImageSchemaFrames(): int
    {
        if (in_array('image_schemas', $this->loadedCategories)) {
            return 0;
        }

        $loaded = 0;
        $imageSchemas = $this->getImageSchemaDefinitions();

        foreach ($imageSchemas as $frameId => $definition) {
            try {
                $frame = $this->createImageSchemaFrame($frameId, $definition);
                if (!$this->registry->has($frameId)) {
                    $this->registry->register($frame);
                    $loaded++;
                }
            } catch (\Exception $e) {
                Log::warning("SOUL Bootstrap: Failed to load image schema frame", [
                    'frame_id' => $frameId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->loadedCategories[] = 'image_schemas';
        return $loaded;
    }

    /**
     * Load Common Sense Psychology primitive frames
     */
    public function loadCSPPrimitiveFrames(): int
    {
        if (in_array('csp_primitives', $this->loadedCategories)) {
            return 0;
        }

        $loaded = 0;
        $cspPrimitives = $this->getCSPPrimitiveDefinitions();

        foreach ($cspPrimitives as $frameId => $definition) {
            try {
                $frame = $this->createCSPPrimitiveFrame($frameId, $definition);
                if (!$this->registry->has($frameId)) {
                    $this->registry->register($frame);
                    $loaded++;
                }
            } catch (\Exception $e) {
                Log::warning("SOUL Bootstrap: Failed to load CSP primitive frame", [
                    'frame_id' => $frameId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->loadedCategories[] = 'csp_primitives';
        return $loaded;
    }

    /**
     * Load Meta-Schema frames
     */
    public function loadMetaSchemaFrames(): int
    {
        if (in_array('meta_schemas', $this->loadedCategories)) {
            return 0;
        }

        $loaded = 0;
        $metaSchemas = $this->getMetaSchemaDefinitions();

        foreach ($metaSchemas as $frameId => $definition) {
            try {
                $frame = $this->createMetaSchemaFrame($frameId, $definition);
                if (!$this->registry->has($frameId)) {
                    $this->registry->register($frame);
                    $loaded++;
                }
            } catch (\Exception $e) {
                Log::warning("SOUL Bootstrap: Failed to load meta-schema frame", [
                    'frame_id' => $frameId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->loadedCategories[] = 'meta_schemas';
        return $loaded;
    }

    /**
     * Load Relation frames
     */
    public function loadRelationFrames(): int
    {
        if (in_array('relation_frames', $this->loadedCategories)) {
            return 0;
        }

        $loaded = 0;
        $relationFrames = $this->getRelationFrameDefinitions();

        foreach ($relationFrames as $frameId => $definition) {
            try {
                $frame = $this->createRelationFrame($frameId, $definition);
                if (!$this->registry->has($frameId)) {
                    $this->registry->register($frame);
                    $loaded++;
                }
            } catch (\Exception $e) {
                Log::warning("SOUL Bootstrap: Failed to load relation frame", [
                    'frame_id' => $frameId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->loadedCategories[] = 'relation_frames';
        return $loaded;
    }

    /**
     * Load Structural Schema frames
     */
    public function loadStructuralSchemaFrames(): int
    {
        if (in_array('structural_schemas', $this->loadedCategories)) {
            return 0;
        }

        $loaded = 0;
        $structuralSchemas = $this->getStructuralSchemaDefinitions();

        foreach ($structuralSchemas as $frameId => $definition) {
            try {
                $frame = $this->createStructuralSchemaFrame($frameId, $definition);
                if (!$this->registry->has($frameId)) {
                    $this->registry->register($frame);
                    $loaded++;
                }
            } catch (\Exception $e) {
                Log::warning("SOUL Bootstrap: Failed to load structural schema frame", [
                    'frame_id' => $frameId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->loadedCategories[] = 'structural_schemas';
        return $loaded;
    }

    /**
     * Get Image Schema frame definitions
     */
    protected function getImageSchemaDefinitions(): array
    {
        return [
            'FORCE' => [
                'label' => 'Force',
                'description' => 'Dynamic force schema representing energy transfer and causation',
                'frame_elements' => [
                    'source' => ['type' => 'Entity', 'required' => true, 'description' => 'Source of force'],
                    'target' => ['type' => 'Entity', 'required' => false, 'description' => 'Target of force'],
                    'magnitude' => ['type' => 'Scale', 'required' => false, 'description' => 'Force intensity'],
                    'direction' => ['type' => 'Axis', 'required' => false, 'description' => 'Force direction']
                ]
            ],
            'REGION' => [
                'label' => 'Region',
                'description' => 'Bounded spatial region with interior, boundary, and exterior',
                'frame_elements' => [
                    'interior' => ['type' => 'Space', 'required' => true, 'description' => 'Inside the region'],
                    'boundary' => ['type' => 'Curve', 'required' => true, 'description' => 'Region boundary'],
                    'exterior' => ['type' => 'Space', 'required' => false, 'description' => 'Outside the region']
                ]
            ],
            'OBJECT' => [
                'label' => 'Object',
                'description' => 'Discrete physical entity with boundaries',
                'frame_elements' => [
                    'entity' => ['type' => 'Entity', 'required' => true, 'description' => 'The object itself'],
                    'location' => ['type' => 'Point', 'required' => false, 'description' => 'Object location'],
                    'properties' => ['type' => 'State', 'required' => false, 'description' => 'Object properties']
                ]
            ],
            'CONTAINER' => [
                'label' => 'Container',
                'description' => 'Enclosed spatial boundary that can hold entities',
                'frame_elements' => [
                    'container' => ['type' => 'Region', 'required' => true, 'description' => 'The container region'],
                    'contents' => ['type' => 'Entity', 'required' => false, 'description' => 'What is contained'],
                    'capacity' => ['type' => 'Scale', 'required' => false, 'description' => 'Container capacity']
                ]
            ],
            'PATH' => [
                'label' => 'Path',
                'description' => 'Route from source to goal through space',
                'frame_elements' => [
                    'source' => ['type' => 'Point', 'required' => true, 'description' => 'Starting point'],
                    'goal' => ['type' => 'Point', 'required' => true, 'description' => 'Destination point'],
                    'trajectory' => ['type' => 'Curve', 'required' => false, 'description' => 'Path trajectory'],
                    'obstacles' => ['type' => 'Object', 'required' => false, 'description' => 'Path obstacles']
                ]
            ]
        ];
    }

    /**
     * Get CSP primitive frame definitions
     */
    protected function getCSPPrimitiveDefinitions(): array
    {
        return [
            'EMOTION' => [
                'label' => 'Emotion',
                'description' => 'Affective state primitive with valence and arousal',
                'frame_elements' => [
                    'experiencer' => ['type' => 'Entity', 'required' => true, 'description' => 'Who experiences the emotion'],
                    'valence' => ['type' => 'Scale', 'required' => false, 'description' => 'Positive/negative valence'],
                    'arousal' => ['type' => 'Scale', 'required' => false, 'description' => 'Activation level'],
                    'trigger' => ['type' => 'Entity', 'required' => false, 'description' => 'Emotion trigger']
                ]
            ],
            'STATE' => [
                'label' => 'State',
                'description' => 'Stable condition or configuration',
                'frame_elements' => [
                    'entity' => ['type' => 'Entity', 'required' => true, 'description' => 'Entity in state'],
                    'condition' => ['type' => 'Property', 'required' => true, 'description' => 'State condition'],
                    'duration' => ['type' => 'Time', 'required' => false, 'description' => 'State duration']
                ]
            ],
            'CAUSE' => [
                'label' => 'Cause',
                'description' => 'Causal relation between events or states',
                'frame_elements' => [
                    'cause' => ['type' => 'Event', 'required' => true, 'description' => 'Causing event'],
                    'effect' => ['type' => 'Event', 'required' => true, 'description' => 'Effect event'],
                    'mechanism' => ['type' => 'Process', 'required' => false, 'description' => 'Causal mechanism']
                ]
            ],
            'BELIEF' => [
                'label' => 'Belief',
                'description' => 'Epistemic state about propositions',
                'frame_elements' => [
                    'believer' => ['type' => 'Entity', 'required' => true, 'description' => 'Who believes'],
                    'proposition' => ['type' => 'Content', 'required' => true, 'description' => 'What is believed'],
                    'confidence' => ['type' => 'Scale', 'required' => false, 'description' => 'belief strength']
                ]
            ]
        ];
    }

    /**
     * Get Meta-Schema frame definitions
     */
    protected function getMetaSchemaDefinitions(): array
    {
        return [
            'ENTITY' => [
                'label' => 'Entity',
                'description' => 'High-level meta-schema for all entities',
                'frame_elements' => [
                    'identity' => ['type' => 'Identity', 'required' => true, 'description' => 'Entity identity'],
                    'properties' => ['type' => 'State', 'required' => false, 'description' => 'Entity properties'],
                    'relations' => ['type' => 'Relation', 'required' => false, 'description' => 'Entity relations']
                ]
            ],
            'PROCESS' => [
                'label' => 'Process',
                'description' => 'High-level meta-schema for processes',
                'frame_elements' => [
                    'agent' => ['type' => 'Entity', 'required' => false, 'description' => 'Process agent'],
                    'steps' => ['type' => 'Sequence', 'required' => true, 'description' => 'Process steps'],
                    'result' => ['type' => 'State', 'required' => false, 'description' => 'Process result']
                ]
            ],
            'CHANGE' => [
                'label' => 'Change',
                'description' => 'High-level meta-schema for change',
                'frame_elements' => [
                    'entity' => ['type' => 'Entity', 'required' => true, 'description' => 'Changing entity'],
                    'initial_state' => ['type' => 'State', 'required' => true, 'description' => 'Before change'],
                    'final_state' => ['type' => 'State', 'required' => true, 'description' => 'After change'],
                    'process' => ['type' => 'Process', 'required' => false, 'description' => 'Change process']
                ]
            ]
        ];
    }

    /**
     * Get Relation frame definitions
     */
    protected function getRelationFrameDefinitions(): array
    {
        return [
            'IS_A' => [
                'label' => 'Is-A Relation',
                'description' => 'Hierarchical classification relationship',
                'frame_elements' => [
                    'figure' => ['type' => 'Entity', 'required' => true, 'description' => 'Specific entity'],
                    'ground' => ['type' => 'Entity', 'required' => true, 'description' => 'General category']
                ]
            ],
            'CAUSES' => [
                'label' => 'Causes Relation',
                'description' => 'Causal relationship between entities',
                'frame_elements' => [
                    'figure' => ['type' => 'Entity', 'required' => true, 'description' => 'Causing entity'],
                    'ground' => ['type' => 'Entity', 'required' => true, 'description' => 'Effect entity'],
                    'mechanism' => ['type' => 'Process', 'required' => false, 'description' => 'Causal mechanism']
                ]
            ],
            'SHARED_SLOT' => [
                'label' => 'Shared Slot',
                'description' => 'Minsky shared slot relationship',
                'frame_elements' => [
                    'figure' => ['type' => 'FrameElement', 'required' => true, 'description' => 'First shared element'],
                    'ground' => ['type' => 'FrameElement', 'required' => true, 'description' => 'Second shared element']
                ]
            ]
        ];
    }

    /**
     * Get Structural Schema frame definitions
     */
    protected function getStructuralSchemaDefinitions(): array
    {
        return [
            'QUALIA' => [
                'label' => 'Qualia Structure',
                'description' => 'Generative lexicon qualia structure',
                'frame_elements' => [
                    'formal' => ['type' => 'Property', 'required' => false, 'description' => 'Formal role'],
                    'constitutive' => ['type' => 'Property', 'required' => false, 'description' => 'Constitutive role'],
                    'telic' => ['type' => 'Property', 'required' => false, 'description' => 'Telic role'],
                    'agentive' => ['type' => 'Property', 'required' => false, 'description' => 'Agentive role']
                ]
            ],
            'RADIAL' => [
                'label' => 'Radial Structure',
                'description' => 'Radial category with prototype and extensions',
                'frame_elements' => [
                    'prototype' => ['type' => 'Entity', 'required' => true, 'description' => 'Central prototype'],
                    'extensions' => ['type' => 'Entity', 'required' => false, 'description' => 'Category extensions'],
                    'similarity' => ['type' => 'Scale', 'required' => false, 'description' => 'Similarity to prototype']
                ]
            ]
        ];
    }

    /**
     * Create an Image Schema frame
     */
    protected function createImageSchemaFrame(string $frameId, array $definition): Frame
    {
        return new class($frameId, $definition['label'], 'image_schema') extends Frame {
            protected array $definition;

            public function __construct(string $id, string $label, string $type, array $definition = [])
            {
                parent::__construct($id, $label, $type);
                $this->definition = $definition;
                $this->addFrameElementsFromDefinition();
            }

            public function match(array $input): float
            {
                // Basic keyword matching for now
                $score = 0.0;
                $keywords = strtolower(implode(' ', $input));
                
                if (str_contains($keywords, strtolower($this->getId()))) {
                    $score += 0.8;
                }
                if (str_contains($keywords, strtolower($this->getLabel()))) {
                    $score += 0.6;
                }
                
                return min($score, 1.0);
            }

            protected function addFrameElementsFromDefinition(): void
            {
                if (isset($this->definition['frame_elements'])) {
                    foreach ($this->definition['frame_elements'] as $name => $feData) {
                        $fe = new FrameElement($name, $feData['type'], $feData['description'] ?? null);
                        if (isset($feData['required'])) {
                            $fe->setRequired($feData['required']);
                        }
                        $this->addFrameElement($fe);
                    }
                }
            }
        };
    }

    /**
     * Create a CSP primitive frame
     */
    protected function createCSPPrimitiveFrame(string $frameId, array $definition): Frame
    {
        return $this->createImageSchemaFrame($frameId, array_merge($definition, ['type' => 'csp']));
    }

    /**
     * Create a Meta-Schema frame
     */
    protected function createMetaSchemaFrame(string $frameId, array $definition): Frame
    {
        return $this->createImageSchemaFrame($frameId, array_merge($definition, ['type' => 'meta_schema']));
    }

    /**
     * Create a Relation frame
     */
    protected function createRelationFrame(string $frameId, array $definition): Frame
    {
        return $this->createImageSchemaFrame($frameId, array_merge($definition, ['type' => 'relation']));
    }

    /**
     * Create a Structural Schema frame
     */
    protected function createStructuralSchemaFrame(string $frameId, array $definition): Frame
    {
        return $this->createImageSchemaFrame($frameId, array_merge($definition, ['type' => 'structural']));
    }

    /**
     * Get loading statistics
     */
    public function getLoadingStatistics(): array
    {
        return [
            'loaded_categories' => $this->loadedCategories,
            'available_categories' => [
                'image_schemas',
                'csp_primitives', 
                'meta_schemas',
                'relation_frames',
                'structural_schemas'
            ]
        ];
    }
}