<?php

namespace App\Soul\Contracts;

interface ImageSchemaServiceInterface extends AgentServiceInterface
{
    public function createPath(array $parameters): array;
    public function calculateDistance(array $parameters): float;
    public function checkContainment(array $parameters): bool;
    public function applyForce(array $parameters): array;
    public function defineRegion(array $parameters): array;
}