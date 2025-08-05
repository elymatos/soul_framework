<?php

namespace App\Soul\Contracts;


use App\Soul\FrameInstance;
use Illuminate\Support\Collection;

/**
 * Contract for Neo4j database operations
 */
interface Neo4jService
{
    /**
     * Create a node for a frame instance
     */
    public function createFrameInstanceNode(FrameInstance $instance, string $sessionId): bool;

    /**
     * Delete a frame instance node
     */
    public function deleteFrameInstanceNode(string $instanceId): bool;

    /**
     * Create relationship between frame instances
     */
    public function createInstanceRelationship(
        string $fromInstanceId,
        string $toInstanceId,
        string $relationshipType,
        array $properties = []
    ): bool;

    /**
     * Archive a processing session
     */
    public function archiveProcessingSession(array $session): bool;

    /**
     * Query frame instances by criteria
     */
    public function queryFrameInstances(array $criteria): Collection;

    /**
     * Get instance relationships
     */
    public function getInstanceRelationships(string $instanceId): Collection;
}
