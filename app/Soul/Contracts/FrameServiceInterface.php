<?php

namespace App\Soul\Contracts;

interface FrameServiceInterface extends AgentServiceInterface
{
    public function instantiateFrame(array $parameters): array;
    public function addFrameElement(array $parameters): array;
    public function resolveInheritance(array $parameters): array;
    public function matchFrame(array $parameters): float;
    public function adaptFrame(array $parameters): array;
}