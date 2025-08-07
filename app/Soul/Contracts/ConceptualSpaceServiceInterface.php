<?php

namespace App\Soul\Contracts;

interface ConceptualSpaceServiceInterface extends AgentServiceInterface
{
    public function placeConceptInRegion(array $parameters): array;
    public function findClosestNeighbor(array $parameters): ?array;
    public function projectSpace(array $parameters): array;
    public function calculateSimilarity(array $parameters): float;
}