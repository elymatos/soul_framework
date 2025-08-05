<?php

namespace App\Soul\Contracts;

use Illuminate\Support\Collection;
use App\Soul\Frame;
use App\Soul\FrameInstance;

/**
 * Contract for frame definition registry
 */
interface FrameDefinitionRegistry
{
    /**
     * Get all registered frame definitions
     */
    public function getAllFrameDefinitions(): Collection;

    /**
     * Register a frame definition
     */
    public function register(Frame $frame): void;

    /**
     * Get frame definition by ID
     */
    public function get(string $frameId): ?Frame;

    /**
     * Check if frame exists
     */
    public function has(string $frameId): bool;

    /**
     * Get frames by type
     */
    public function getByType(string $type): Collection;
}

