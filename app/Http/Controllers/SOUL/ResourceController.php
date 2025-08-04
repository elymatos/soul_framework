<?php

namespace App\Http\Controllers\SOUL;

use App\Http\Controllers\Controller;
use App\Data\SOUL\CreateConceptData;
use App\Data\SOUL\UpdateConceptData;
use App\Data\SOUL\CreateRelationshipData;
use App\Data\SOUL\SearchConceptData;
use App\Services\SOUL\ConceptService;
use App\Services\SOUL\GraphService;
use App\Repositories\Neo4jConceptRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravelcollective\Annotations\Routing\Annotations\Middleware;
use Laravelcollective\Annotations\Routing\Annotations\Route;

/**
 * SOUL Framework Resource Controller
 * 
 * Centralized controller for all CRUD operations related to the SOUL Framework,
 * including concept management, relationship creation, graph operations, and
 * spreading activation algorithms.
 * 
 * @Route("/soul")
 * @Middleware("auth")
 */
class ResourceController extends Controller
{
    public function __construct(
        private ConceptService $conceptService,
        private GraphService $graphService,
        private Neo4jConceptRepository $repository
    ) {}

    // =========================
    // CONCEPT CRUD OPERATIONS
    // =========================

    /**
     * List all concepts with optional search and filtering
     * 
     * @Route("/concepts", methods={"GET"})
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $searchData = SearchConceptData::from($request->query());
            $concepts = $this->conceptService->searchConcepts($searchData);

            return $this->success([
                'concepts' => $concepts,
                'total' => count($concepts),
                'search_criteria' => $searchData->toArray()
            ]);

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve concepts: ' . $e->getMessage());
        }
    }

    /**
     * Create a new concept
     * 
     * @Route("/concepts", methods={"POST"})
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = CreateConceptData::from($request->all());
            $concept = $this->conceptService->createConcept($data);

            return $this->success($concept, 'Concept created successfully', Response::HTTP_CREATED);

        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->error('Failed to create concept: ' . $e->getMessage());
        }
    }

    /**
     * Show a specific concept with its relationships
     * 
     * @Route("/concepts/{name}", methods={"GET"})
     */
    public function show(string $name): JsonResponse
    {
        try {
            $conceptData = $this->conceptService->getConceptWithRelationships($name);
            
            return $this->success($conceptData);

        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve concept: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing concept
     * 
     * @Route("/concepts/{name}", methods={"PUT", "PATCH"})
     */
    public function update(Request $request, string $name): JsonResponse
    {
        try {
            $data = UpdateConceptData::from($request->all());
            $concept = $this->conceptService->updateConcept($name, $data);

            return $this->success($concept, 'Concept updated successfully');

        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->error('Failed to update concept: ' . $e->getMessage());
        }
    }

    /**
     * Delete a concept
     * 
     * @Route("/concepts/{name}", methods={"DELETE"})
     */
    public function destroy(string $name): JsonResponse
    {
        try {
            $this->conceptService->deleteConcept($name);
            
            return $this->success(null, 'Concept deleted successfully');

        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->error('Failed to delete concept: ' . $e->getMessage());
        }
    }

    // ================================
    // RELATIONSHIP OPERATIONS
    // ================================

    /**
     * Create a relationship between concepts
     * 
     * @Route("/relationships", methods={"POST"})
     */
    public function createRelationship(Request $request): JsonResponse
    {
        try {
            $data = CreateRelationshipData::from($request->all());
            $success = $this->conceptService->createRelationship($data);

            if ($success) {
                return $this->success(null, 'Relationship created successfully', Response::HTTP_CREATED);
            }

            return $this->error('Failed to create relationship');

        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->error('Failed to create relationship: ' . $e->getMessage());
        }
    }

    // ================================
    // SOUL FRAMEWORK OPERATIONS
    // ================================

    /**
     * Initialize SOUL primitives (Image Schemas, CSP, Meta-schemas)
     * 
     * @Route("/initialize", methods={"POST"})
     */
    public function initializePrimitives(): JsonResponse
    {
        try {
            $initialized = $this->conceptService->initializeSoulPrimitives();

            return $this->success([
                'initialized_primitives' => $initialized,
                'count' => count($initialized)
            ], 'SOUL primitives initialized successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to initialize SOUL primitives: ' . $e->getMessage());
        }
    }

    /**
     * Perform spreading activation from a concept
     * 
     * @Route("/spreading-activation/{conceptName}", methods={"GET"})
     */
    public function spreadingActivation(
        string $conceptName, 
        Request $request
    ): JsonResponse {
        try {
            $threshold = (float) $request->query('threshold', 0.5);
            $maxDepth = (int) $request->query('max_depth', 3);
            $maxResults = (int) $request->query('max_results', 50);

            $result = $this->graphService->performSpreadingActivation(
                $conceptName, 
                $threshold, 
                $maxDepth, 
                $maxResults
            );

            return $this->success($result);

        } catch (\Exception $e) {
            return $this->error('Failed to perform spreading activation: ' . $e->getMessage());
        }
    }

    // ================================
    // GRAPH VISUALIZATION
    // ================================

    /**
     * Get graph visualization data for a concept
     * 
     * @Route("/graph/{conceptName}", methods={"GET"})
     */
    public function graphVisualization(string $conceptName, Request $request): JsonResponse
    {
        try {
            $depth = (int) $request->query('depth', 2);
            $graphData = $this->graphService->getConceptGraphVisualization($conceptName, $depth);

            return $this->success($graphData);

        } catch (\Exception $e) {
            return $this->error('Failed to generate graph visualization: ' . $e->getMessage());
        }
    }

    /**
     * Get graph statistics and metrics
     * 
     * @Route("/statistics", methods={"GET"})
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->graphService->getGraphStatistics();
            
            return $this->success($statistics);

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve statistics: ' . $e->getMessage());
        }
    }

    // ================================
    // DATA IMPORT/EXPORT
    // ================================

    /**
     * Export graph data
     * 
     * @Route("/export", methods={"GET"})
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $format = $request->query('format', 'json');
            $graphData = $this->graphService->exportGraph($format);

            return response()->json($graphData)
                ->header('Content-Disposition', 'attachment; filename=soul_graph_export.json');

        } catch (\Exception $e) {
            return $this->error('Failed to export graph: ' . $e->getMessage());
        }
    }

    /**
     * Import graph data
     * 
     * @Route("/import", methods={"POST"})
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $graphData = $request->json()->all();
            
            if (empty($graphData)) {
                return $this->error('No graph data provided', Response::HTTP_BAD_REQUEST);
            }

            $result = $this->graphService->importGraph($graphData);

            return $this->success($result, 'Graph data imported successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to import graph: ' . $e->getMessage());
        }
    }

    // ================================
    // UTILITY METHODS
    // ================================

    /**
     * Search concepts with advanced criteria
     * 
     * @Route("/search", methods={"POST"})
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $searchData = SearchConceptData::from($request->all());
            $concepts = $this->conceptService->searchConcepts($searchData);

            return $this->success([
                'concepts' => $concepts,
                'total' => count($concepts),
                'search_criteria' => $searchData->toArray()
            ]);

        } catch (\Exception $e) {
            return $this->error('Failed to search concepts: ' . $e->getMessage());
        }
    }

    /**
     * Get available relationship types
     * 
     * @Route("/relationship-types", methods={"GET"})
     */
    public function getRelationshipTypes(): JsonResponse
    {
        $relationshipTypes = [
            'IS_A' => 'Hierarchical classification',
            'PART_OF' => 'Component relationship',
            'RELATED_TO' => 'General association',
            'CAUSES' => 'Causal relationship',
            'ENABLES' => 'Enabling relationship',
            'INHIBITS' => 'Inhibiting relationship',
            'COMPONENT_OF' => 'Component-whole relationship',
            'SUBTYPE_OF' => 'Subtype classification',
            'INSTANCE_OF' => 'Instantiation relationship',
            'SIMILAR_TO' => 'Similarity relationship',
            'OPPOSITE_OF' => 'Opposition relationship',
            'BLENDS_WITH' => 'Conceptual blending relationship'
        ];

        return $this->success($relationshipTypes);
    }

    /**
     * Get available concept types
     * 
     * @Route("/concept-types", methods={"GET"})
     */
    public function getConceptTypes(): JsonResponse
    {
        $conceptTypes = [
            'primitive' => 'Basic primitive concept',
            'derived' => 'Derived from other concepts',
            'meta_schema' => 'Meta-level schema',
            'image_schema' => 'Image schema primitive',
            'csp' => 'Common Sense Psychology primitive'
        ];

        return $this->success($conceptTypes);
    }

    // ================================
    // DATABASE MANAGEMENT
    // ================================

    /**
     * Create Neo4j constraints and indexes
     * 
     * @Route("/database/constraints", methods={"POST"})
     */
    public function createConstraints(): JsonResponse
    {
        try {
            $results = $this->repository->createConstraintsAndIndexes();
            
            return $this->success($results, 'Database constraints and indexes created successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to create constraints: ' . $e->getMessage());
        }
    }

    /**
     * Get current constraints and indexes status
     * 
     * @Route("/database/status", methods={"GET"})
     */
    public function getDatabaseStatus(): JsonResponse
    {
        try {
            $status = $this->repository->getConstraintsAndIndexesStatus();
            
            return $this->success($status);

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve database status: ' . $e->getMessage());
        }
    }

    /**
     * Drop all constraints and indexes (maintenance)
     * 
     * @Route("/database/constraints", methods={"DELETE"})
     */
    public function dropConstraints(): JsonResponse
    {
        try {
            $results = $this->repository->dropConstraintsAndIndexes();
            
            return $this->success($results, 'Database constraints and indexes dropped successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to drop constraints: ' . $e->getMessage());
        }
    }

    // ================================
    // RESPONSE HELPERS
    // ================================

    /**
     * Return successful JSON response
     */
    private function success($data = null, string $message = 'Success', int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Return error JSON response
     */
    private function error(string $message, int $status = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $status);
    }
}