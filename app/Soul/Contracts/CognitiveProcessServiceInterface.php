<?php

namespace App\Soul\Contracts;

interface CognitiveProcessServiceInterface extends AgentServiceInterface
{
    public function runSpreadingActivation(array $parameters): array;
    public function performBlending(array $parameters): array;
    public function executeInference(array $parameters): array;
    public function manageAttention(array $parameters): array;
}