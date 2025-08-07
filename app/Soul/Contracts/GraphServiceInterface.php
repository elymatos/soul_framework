<?php

namespace App\Soul\Contracts;

use Illuminate\Support\Collection;

interface GraphServiceInterface
{
    /**
     * Run spreading activation from initial concepts
     */
    public function runSpreadingActivation(array $initialConcepts, array $options = []): array;
    
    /**
     * Find procedural agent nodes by code reference
     */
    public function findProceduralAgent(string $codeReference): ?array;
    
    /**
     * Record successful activation path as K-line
     */
    public function recordKLine(array $activationPath, string $context): string;
    
    /**
     * Create or update concept node
     */
    public function createConcept(array $conceptData): string;
    
    /**
     * Create relationship between concepts
     */
    public function createRelationship(string $fromId, string $toId, string $type, array $properties = []): bool;
    
    /**
     * Create procedural agent node
     */
    public function createProceduralAgent(array $agentData): string;
    
    /**
     * Strengthen K-line based on usage
     */
    public function strengthenKLine(string $klineId): void;
}