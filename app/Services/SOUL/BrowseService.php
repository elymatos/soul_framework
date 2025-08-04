<?php

namespace App\Services\SOUL;

use App\Data\SOUL\BrowseConceptData;
use App\Data\SOUL\SearchConceptData;

/**
 * SOUL Browse Service
 *
 * Handles business logic for browsing and exploring SOUL concepts,
 * providing a user-friendly interface for concept discovery and navigation.
 */
class BrowseService
{
    public function __construct(
        private ConceptService $conceptService,
        private GraphService $graphService
    ) {}

    /**
     * Browse concepts with search and filtering
     */
    public function browseConcepts(BrowseConceptData $browseData): array
    {
        // Convert browse data to search data for the service layer
        $searchData = $browseData->toSearchConceptData();

        // Get concepts from the concept service
        $concepts = $this->conceptService->searchConcepts($searchData);

        // Enhance concepts with additional browse-specific data
        return array_map(function ($concept) {
            return $this->enhanceConceptForBrowse($concept);
        }, $concepts);
    }

    /**
     * Get detailed information for a specific concept
     */
    public function getConceptDetails(string $conceptName): array
    {
        try {
            // Get concept with relationships
            $conceptData = $this->conceptService->getConceptWithRelationships($conceptName);

            // Get related concepts through spreading activation
            $activationData = $this->graphService->performSpreadingActivation($conceptName, 0.5, 2, 10);

            // Get graph visualization data
            $graphData = $this->graphService->getConceptGraphVisualization($conceptName, 2);

            return [
                'concept' => $conceptData['concept'],
                'relationships' => $this->organizeRelationships($conceptData['relationships']),
                'activatedConcepts' => $activationData['activatedConcepts'],
                'graphData' => $graphData,
                'conceptName' => $conceptName,
            ];

        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Concept '{$conceptName}' not found or error retrieving data: ".$e->getMessage());
        }
    }

    /**
     * Get related concepts for a concept
     */
    public function getRelatedConcepts(string $conceptName, int $limit = 20): array
    {
        try {
            $conceptData = $this->conceptService->getConceptWithRelationships($conceptName);
            $relationships = $conceptData['relationships'];

            // Extract unique related concepts
            $relatedConcepts = [];
            foreach ($relationships as $rel) {
                if (! empty($rel['related_concept']) && $rel['related_concept'] !== $conceptName) {
                    $relatedConcepts[] = [
                        'name' => $rel['related_concept'],
                        'relationship' => $rel['relationship'],
                        'direction' => $rel['direction'],
                        'weight' => $rel['weight'] ?? 1.0,
                    ];
                }
            }

            // Remove duplicates and limit results
            $uniqueRelated = [];
            $seen = [];
            foreach ($relatedConcepts as $related) {
                $key = $related['name'];
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $uniqueRelated[] = $related;
                    if (count($uniqueRelated) >= $limit) {
                        break;
                    }
                }
            }

            return $uniqueRelated;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get spreading activation results
     */
    public function getSpreadingActivationResults(string $conceptName, float $threshold = 0.5, int $maxDepth = 3, int $maxResults = 20): array
    {
        try {
            return $this->graphService->performSpreadingActivation($conceptName, $threshold, $maxDepth, $maxResults);
        } catch (\Exception $e) {
            return [
                'startConcept' => $conceptName,
                'parameters' => [
                    'threshold' => $threshold,
                    'maxDepth' => $maxDepth,
                    'maxResults' => $maxResults,
                ],
                'activatedConcepts' => [],
                'totalActivated' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get concept statistics for the browse interface
     */
    public function getBrowseStatistics(): array
    {
        try {
            $stats = $this->graphService->getGraphStatistics();

            return [
                'totalConcepts' => $stats['totalConcepts'] ?? 0,
                'totalPrimitives' => $stats['totalPrimitives'] ?? 0,
                'totalRelationships' => $stats['totalRelationships'] ?? 0,
                'conceptsByType' => $stats['conceptsByType'] ?? [],
                'relationshipsByType' => $stats['relationshipsByType'] ?? [],
                'primitivesByCategory' => $stats['primitivesByCategory'] ?? [],
            ];
        } catch (\Exception $e) {
            return [
                'totalConcepts' => 0,
                'totalPrimitives' => 0,
                'totalRelationships' => 0,
                'conceptsByType' => [],
                'relationshipsByType' => [],
                'primitivesByCategory' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Enhance concept data for browse display
     */
    private function enhanceConceptForBrowse(array $concept): array
    {
        $concept['typeDisplay'] = $this->getTypeDisplayName($concept['type'] ?? '');
        $concept['categoryDisplay'] = $this->getCategoryDisplayName($concept['category'] ?? '');
        $concept['isPrimitive'] = $concept['is_primitive'] ?? false;
        $concept['typeClass'] = $this->getTypeClass($concept['type'] ?? '');
        $concept['shortDescription'] = $this->truncateDescription($concept['description'] ?? '', 150);

        return $concept;
    }

    /**
     * Organize relationships by type and direction
     */
    private function organizeRelationships(array $relationships): array
    {
        $organized = [
            'outgoing' => [],
            'incoming' => [],
            'by_type' => [],
        ];

        foreach ($relationships as $rel) {
            if (empty($rel['related_concept'])) {
                continue;
            }

            $direction = $rel['direction'] ?? 'outgoing';
            $relType = $rel['relationship'] ?? 'RELATED_TO';

            $organized[$direction][] = $rel;

            if (! isset($organized['by_type'][$relType])) {
                $organized['by_type'][$relType] = [];
            }
            $organized['by_type'][$relType][] = $rel;
        }

        return $organized;
    }

    /**
     * Get display name for concept type
     */
    private function getTypeDisplayName(string $type): string
    {
        return match ($type) {
            'primitive' => 'Primitive',
            'derived' => 'Derived',
            'meta_schema' => 'Meta-schema',
            'image_schema' => 'Image Schema',
            'csp' => 'CSP',
            default => ucfirst($type ?: 'Unknown')
        };
    }

    /**
     * Get display name for concept category
     */
    private function getCategoryDisplayName(string $category): string
    {
        return match ($category) {
            'concept' => 'Concept',
            'primitive' => 'Primitive',
            'meta_schema' => 'Meta-schema',
            default => ucfirst($category ?: 'Unknown')
        };
    }

    /**
     * Get CSS class for concept type
     */
    private function getTypeClass(string $type): string
    {
        return match ($type) {
            'image_schema' => 'soul-type-image-schema',
            'csp' => 'soul-type-csp',
            'meta_schema' => 'soul-type-meta-schema',
            'primitive' => 'soul-type-primitive',
            'derived' => 'soul-type-derived',
            default => 'soul-type-unknown'
        };
    }

    /**
     * Truncate description for card display
     */
    private function truncateDescription(string $description, int $length = 150): string
    {
        if (strlen($description) <= $length) {
            return $description;
        }

        return substr($description, 0, $length).'...';
    }
}
