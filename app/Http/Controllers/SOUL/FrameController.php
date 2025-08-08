<?php

namespace App\Http\Controllers\SOUL;

use App\Http\Controllers\Controller;
use App\Soul\Services\FrameService;
use App\Soul\Services\MindService;
use App\Soul\Contracts\GraphServiceInterface;
use App\Soul\Contracts\FrameServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Laravelcollective\Annotations\Routing\Annotations\Middleware;
use Laravelcollective\Annotations\Routing\Annotations\Route;

/**
 * Frame Semantics Workbench Controller
 * 
 * Provides comprehensive frame-based cognitive processing tools including frame
 * library browsing, structure visualization, pattern matching, and specialized
 * commercial transaction analysis.
 * 
 * @Route("/soul/frames")
 * @Middleware("auth")
 */
class FrameController extends Controller
{
    public function __construct(
        private FrameServiceInterface $frameService,
        private GraphServiceInterface $graphService,
        private MindService $mindService
    ) {}

    // =========================
    // MAIN INTERFACE
    // =========================

    /**
     * Main frame workbench interface
     * 
     * @Route("/", methods={"GET"})
     */
    public function index(Request $request)
    {
        try {
            // Get initial frame statistics
            $frameStats = $this->getFrameStatistics();
            
            // Get sample frames for the interface
            $sampleFrames = $this->getSampleFrames();
            
            return view('SOUL.Frames.main', [
                'frameStats' => $frameStats,
                'sampleFrames' => $sampleFrames,
                'title' => 'Frame Semantics Workbench'
            ]);

        } catch (\Exception $e) {
            Log::error('FrameController: Failed to load main interface', [
                'error' => $e->getMessage()
            ]);

            return view('SOUL.Frames.main', [
                'frameStats' => [],
                'sampleFrames' => [],
                'error' => 'Failed to load frame data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Commercial transaction analyzer interface
     * 
     * @Route("/commercial", methods={"GET"})
     */
    public function commercial(Request $request)
    {
        try {
            // Get commercial frames
            $commercialFrames = $this->getCommercialFrames();
            
            // Get sample transactions for testing
            $sampleTransactions = $this->getSampleTransactions();
            
            return view('SOUL.Frames.commercial', [
                'commercialFrames' => $commercialFrames,
                'sampleTransactions' => $sampleTransactions,
                'title' => 'Commercial Transaction Analyzer'
            ]);

        } catch (\Exception $e) {
            Log::error('FrameController: Failed to load commercial interface', [
                'error' => $e->getMessage()
            ]);

            return view('SOUL.Frames.commercial', [
                'commercialFrames' => [],
                'sampleTransactions' => [],
                'error' => 'Failed to load commercial frame data: ' . $e->getMessage()
            ]);
        }
    }

    // =========================
    // FRAME LIBRARY OPERATIONS
    // =========================

    /**
     * Get all available frames
     * 
     * @Route("/api/frames", methods={"GET"})
     */
    public function getFrames(Request $request): JsonResponse
    {
        try {
            $frameType = $request->query('type');
            $search = $request->query('search');
            
            // Query Neo4j for frame concepts
            $query = 'MATCH (f:Concept) WHERE "Frame" IN labels(f)';
            $params = [];
            
            if ($frameType) {
                $query .= ' AND f.type = $type';
                $params['type'] = $frameType;
            }
            
            if ($search) {
                $query .= ' AND (f.name CONTAINS $search OR f.description CONTAINS $search)';
                $params['search'] = $search;
            }
            
            $query .= ' RETURN f ORDER BY f.name';
            
            $result = $this->graphService->executeQuery($query, $params);
            
            $frames = [];
            foreach ($result as $record) {
                $frame = $record->get('f');
                $frames[] = [
                    'name' => $frame->getProperty('name'),
                    'type' => $frame->getProperty('type', 'frame'),
                    'description' => $frame->getProperty('description', ''),
                    'domain' => $frame->getProperty('domain', ''),
                    'frame_elements' => json_decode($frame->getProperty('frame_elements', '{}'), true),
                    'created_at' => $frame->getProperty('created_at'),
                    'labels' => $frame->getLabels()
                ];
            }

            return $this->success([
                'frames' => $frames,
                'total' => count($frames),
                'filters' => [
                    'type' => $frameType,
                    'search' => $search
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('FrameController: Failed to get frames', [
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve frames: ' . $e->getMessage());
        }
    }

    /**
     * Get frame structure with elements and relationships
     * 
     * @Route("/api/frames/{frameName}/structure", methods={"GET"})
     */
    public function getFrameStructure(string $frameName): JsonResponse
    {
        try {
            // Get frame with its elements and relationships
            $query = '
                MATCH (f:Concept {name: $frameName}) 
                WHERE "Frame" IN labels(f)
                OPTIONAL MATCH (f)-[r:HAS_FRAME_ELEMENT]->(element:Concept)
                WHERE "FrameElement" IN labels(element)
                OPTIONAL MATCH (f)-[rel]->(related:Concept)
                WHERE type(rel) <> "HAS_FRAME_ELEMENT"
                RETURN f, 
                       collect(DISTINCT {element: element, relationship: r}) as elements,
                       collect(DISTINCT {related: related, relationship: rel}) as relationships
            ';
            
            $result = $this->graphService->executeQuery($query, ['frameName' => $frameName]);
            
            if (!$result->count()) {
                return $this->error('Frame not found', 404);
            }
            
            $record = $result->first();
            $frame = $record->get('f');
            $elements = $record->get('elements');
            $relationships = $record->get('relationships');
            
            $structure = [
                'frame' => [
                    'name' => $frame->getProperty('name'),
                    'type' => $frame->getProperty('type'),
                    'description' => $frame->getProperty('description', ''),
                    'domain' => $frame->getProperty('domain', ''),
                    'properties' => $frame->getProperties()
                ],
                'elements' => [],
                'relationships' => []
            ];
            
            // Process frame elements
            foreach ($elements as $elementData) {
                $element = $elementData['element'];
                $relationship = $elementData['relationship'];
                
                if ($element) {
                    $structure['elements'][] = [
                        'name' => $element->getProperty('element_name', $element->getProperty('name')),
                        'value' => json_decode($element->getProperty('element_value', '{}'), true),
                        'type' => $element->getProperty('element_type', 'core'),
                        'description' => $element->getProperty('description', ''),
                        'constraints' => json_decode($element->getProperty('constraints', '{}'), true)
                    ];
                }
            }
            
            // Process relationships
            foreach ($relationships as $relationshipData) {
                $related = $relationshipData['related'];
                $relationship = $relationshipData['relationship'];
                
                if ($related && $relationship) {
                    $structure['relationships'][] = [
                        'target' => $related->getProperty('name'),
                        'type' => $relationship->getType(),
                        'properties' => $relationship->getProperties(),
                        'target_labels' => $related->getLabels()
                    ];
                }
            }
            
            return $this->success($structure);

        } catch (\Exception $e) {
            Log::error('FrameController: Failed to get frame structure', [
                'frame_name' => $frameName,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve frame structure: ' . $e->getMessage());
        }
    }

    // =========================
    // FRAME MATCHING OPERATIONS
    // =========================

    /**
     * Test input text against frame patterns
     * 
     * @Route("/api/match", methods={"POST"})
     */
    public function matchFrame(Request $request): JsonResponse
    {
        try {
            $input = $request->input('input', []);
            $frameCandidates = $request->input('frame_candidates', []);
            $threshold = (float) $request->input('threshold', 0.5);
            $context = $request->input('context', []);
            
            // Use FrameService to perform matching
            $result = $this->frameService->executeAgent('matchFrame', [
                'input' => $input,
                'frame_candidates' => $frameCandidates,
                'threshold' => $threshold,
                'context' => $context
            ]);
            
            return $this->success($result['data']);

        } catch (\Exception $e) {
            Log::error('FrameController: Frame matching failed', [
                'error' => $e->getMessage()
            ]);

            return $this->error('Frame matching failed: ' . $e->getMessage());
        }
    }

    /**
     * Create frame instance with element bindings
     * 
     * @Route("/api/instantiate", methods={"POST"})
     */
    public function instantiateFrame(Request $request): JsonResponse
    {
        try {
            $frameType = $request->input('frame_type');
            $initialElements = $request->input('initial_elements', []);
            $context = $request->input('context', []);
            $sessionId = $request->input('session_id', 'frame_workbench_' . uniqid());
            
            // Use FrameService to instantiate frame
            $result = $this->frameService->executeAgent('instantiateFrame', [
                'frame_type' => $frameType,
                'initial_elements' => $initialElements,
                'context' => $context,
                'session_id' => $sessionId
            ]);
            
            return $this->success($result['data']);

        } catch (\Exception $e) {
            Log::error('FrameController: Frame instantiation failed', [
                'error' => $e->getMessage()
            ]);

            return $this->error('Frame instantiation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get active frame instances
     * 
     * @Route("/api/instances", methods={"GET"})
     */
    public function getFrameInstances(Request $request): JsonResponse
    {
        try {
            $sessionId = $request->query('session_id');
            $frameType = $request->query('frame_type');
            
            $query = 'MATCH (f:Concept) WHERE "Frame" IN labels(f)';
            $params = [];
            
            if ($sessionId) {
                $query .= ' AND f.session_id = $sessionId';
                $params['sessionId'] = $sessionId;
            }
            
            if ($frameType) {
                $query .= ' AND f.frame_type = $frameType';
                $params['frameType'] = $frameType;
            }
            
            $query .= ' AND f.instantiated_at IS NOT NULL';
            $query .= ' OPTIONAL MATCH (f)-[r:HAS_FRAME_ELEMENT]->(element:FrameElement)';
            $query .= ' RETURN f, collect({element: element, relationship: r}) as elements';
            $query .= ' ORDER BY f.instantiated_at DESC';
            
            $result = $this->graphService->executeQuery($query, $params);
            
            $instances = [];
            foreach ($result as $record) {
                $frame = $record->get('f');
                $elements = $record->get('elements');
                
                $instanceElements = [];
                foreach ($elements as $elementData) {
                    $element = $elementData['element'];
                    if ($element) {
                        $instanceElements[] = [
                            'name' => $element->getProperty('element_name'),
                            'value' => json_decode($element->getProperty('element_value', '{}'), true)
                        ];
                    }
                }
                
                $instances[] = [
                    'id' => $frame->getId(),
                    'name' => $frame->getProperty('name'),
                    'frame_type' => $frame->getProperty('frame_type'),
                    'session_id' => $frame->getProperty('session_id'),
                    'instantiated_at' => $frame->getProperty('instantiated_at'),
                    'context' => json_decode($frame->getProperty('context', '{}'), true),
                    'elements' => $instanceElements
                ];
            }
            
            return $this->success([
                'instances' => $instances,
                'total' => count($instances),
                'filters' => [
                    'session_id' => $sessionId,
                    'frame_type' => $frameType
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('FrameController: Failed to get frame instances', [
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve frame instances: ' . $e->getMessage());
        }
    }

    // =========================
    // COMMERCIAL FRAMES
    // =========================

    /**
     * Get commercial transaction frames
     * 
     * @Route("/api/commercial/frames", methods={"GET"})
     */
    public function getCommercialFrames(Request $request = null): JsonResponse|array
    {
        try {
            $query = '
                MATCH (f:Concept) 
                WHERE "Frame" IN labels(f) 
                AND (f.domain = "commercial" OR f.domain = "commerce" OR f.name CONTAINS "COMMERCIAL")
                RETURN f ORDER BY f.name
            ';
            
            $result = $this->graphService->executeQuery($query);
            
            $frames = [];
            foreach ($result as $record) {
                $frame = $record->get('f');
                $frames[] = [
                    'name' => $frame->getProperty('name'),
                    'type' => $frame->getProperty('type', 'frame'),
                    'description' => $frame->getProperty('description', ''),
                    'domain' => $frame->getProperty('domain', 'commerce'),
                    'frame_elements' => json_decode($frame->getProperty('frame_elements', '{}'), true)
                ];
            }
            
            // Add default commercial frames if none exist
            if (empty($frames)) {
                $frames = $this->getDefaultCommercialFrames();
            }
            
            $responseData = [
                'frames' => $frames,
                'total' => count($frames)
            ];
            
            return $request ? $this->success($responseData) : $responseData;

        } catch (\Exception $e) {
            Log::error('FrameController: Failed to get commercial frames', [
                'error' => $e->getMessage()
            ]);

            $errorData = [
                'frames' => [],
                'total' => 0,
                'error' => $e->getMessage()
            ];
            
            return $request ? $this->error('Failed to retrieve commercial frames: ' . $e->getMessage()) : $errorData;
        }
    }

    /**
     * Analyze commercial transaction text
     * 
     * @Route("/api/commercial/analyze", methods={"POST"})
     */
    public function analyzeCommercialTransaction(Request $request): JsonResponse
    {
        try {
            $text = $request->input('text');
            $context = $request->input('context', ['commercial', 'transaction']);
            
            if (!$text) {
                return $this->error('No text provided for analysis', 400);
            }
            
            // Get commercial frame candidates
            $commercialFrames = $this->getCommercialFrames();
            $frameCandidates = array_column($commercialFrames['frames'], 'name');
            
            // Perform frame matching
            $matchResult = $this->frameService->executeAgent('matchFrame', [
                'input' => ['text' => $text],
                'frame_candidates' => $frameCandidates,
                'threshold' => 0.3,
                'context' => $context
            ]);
            
            $analysis = [
                'text' => $text,
                'frame_matches' => $matchResult['data']['matches'] ?? [],
                'best_match' => $matchResult['data']['best_match'] ?? null,
                'entities' => $this->extractCommercialEntities($text),
                'relationships' => $this->identifyCommercialRelationships($text)
            ];
            
            return $this->success($analysis);

        } catch (\Exception $e) {
            Log::error('FrameController: Commercial transaction analysis failed', [
                'error' => $e->getMessage()
            ]);

            return $this->error('Commercial transaction analysis failed: ' . $e->getMessage());
        }
    }

    // =========================
    // HELPER METHODS
    // =========================

    private function getFrameStatistics(): array
    {
        try {
            $query = '
                MATCH (f:Concept) WHERE "Frame" IN labels(f)
                RETURN 
                    count(f) as total_frames,
                    count(CASE WHEN f.domain = "commercial" THEN 1 END) as commercial_frames,
                    count(CASE WHEN f.type = "image_schema" THEN 1 END) as image_schema_frames,
                    collect(DISTINCT f.domain) as domains,
                    collect(DISTINCT f.type) as types
            ';
            
            $result = $this->graphService->executeQuery($query);
            
            if ($result->count()) {
                $record = $result->first();
                return [
                    'total_frames' => $record->get('total_frames'),
                    'commercial_frames' => $record->get('commercial_frames'),
                    'image_schema_frames' => $record->get('image_schema_frames'),
                    'domains' => array_filter($record->get('domains')),
                    'types' => array_filter($record->get('types'))
                ];
            }
            
        } catch (\Exception $e) {
            Log::warning('Failed to get frame statistics', ['error' => $e->getMessage()]);
        }
        
        return [
            'total_frames' => 0,
            'commercial_frames' => 0,
            'image_schema_frames' => 0,
            'domains' => [],
            'types' => []
        ];
    }

    private function getSampleFrames(): array
    {
        return [
            [
                'name' => 'COMMERCIAL_TRANSACTION',
                'type' => 'frame',
                'description' => 'Frame for buying and selling interactions',
                'domain' => 'commerce',
                'elements' => ['buyer', 'seller', 'goods', 'money']
            ],
            [
                'name' => 'CONTAINER',
                'type' => 'image_schema',
                'description' => 'Bounded region with interior and exterior',
                'domain' => 'spatial',
                'elements' => ['container', 'contents', 'boundary']
            ],
            [
                'name' => 'MOTION',
                'type' => 'frame',
                'description' => 'Movement from one location to another',
                'domain' => 'spatial',
                'elements' => ['theme', 'source', 'goal', 'path']
            ]
        ];
    }

    private function getSampleTransactions(): array
    {
        return [
            'John buys a book from Mary for $20',
            'The customer purchased three items at the store',
            'Alice sold her car to Bob yesterday',
            'The company acquired new software licenses',
            'She paid the vendor for the catering services'
        ];
    }

    private function getDefaultCommercialFrames(): array
    {
        return [
            [
                'name' => 'COMMERCIAL_TRANSACTION',
                'type' => 'frame',
                'description' => 'Frame for buying and selling interactions',
                'domain' => 'commerce',
                'frame_elements' => [
                    'buyer' => 'Entity acquiring goods or services',
                    'seller' => 'Entity providing goods or services',
                    'goods' => 'Items or services being transacted',
                    'money' => 'Payment or consideration exchanged'
                ]
            ],
            [
                'name' => 'PURCHASE',
                'type' => 'frame',
                'description' => 'Act of acquiring something for payment',
                'domain' => 'commerce',
                'frame_elements' => [
                    'buyer' => 'Entity making the purchase',
                    'item' => 'Thing being purchased',
                    'cost' => 'Amount paid',
                    'vendor' => 'Entity selling the item'
                ]
            ]
        ];
    }

    private function extractCommercialEntities(string $text): array
    {
        // Simple entity extraction - in practice would be more sophisticated
        $entities = [];
        
        // Extract potential buyers/sellers (proper nouns)
        if (preg_match_all('/\b[A-Z][a-z]+\b/', $text, $matches)) {
            $entities['people'] = array_unique($matches[0]);
        }
        
        // Extract money amounts
        if (preg_match_all('/\$[\d,]+(\.\d{2})?/', $text, $matches)) {
            $entities['money'] = $matches[0];
        }
        
        // Extract commercial actions
        $commercialWords = ['buy', 'sell', 'purchase', 'acquire', 'pay', 'cost', 'price'];
        foreach ($commercialWords as $word) {
            if (stripos($text, $word) !== false) {
                $entities['actions'][] = $word;
            }
        }
        
        return $entities;
    }

    private function identifyCommercialRelationships(string $text): array
    {
        $relationships = [];
        
        // Simple pattern matching for commercial relationships
        if (preg_match('/(\w+) (buys?|purchases?) (.+) from (\w+)/i', $text, $matches)) {
            $relationships[] = [
                'type' => 'PURCHASE',
                'buyer' => $matches[1],
                'item' => $matches[3],
                'seller' => $matches[4]
            ];
        }
        
        if (preg_match('/(\w+) (sells?) (.+) to (\w+)/i', $text, $matches)) {
            $relationships[] = [
                'type' => 'SALE',
                'seller' => $matches[1],
                'item' => $matches[3],
                'buyer' => $matches[4]
            ];
        }
        
        return $relationships;
    }

    // =========================
    // RESPONSE HELPERS
    // =========================

    private function success($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    private function error(string $message, int $status = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $status);
    }
}