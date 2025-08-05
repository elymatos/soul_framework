<?php

namespace App\Soul;

use Illuminate\Support\Collection;
use Soul\Services\MindService;
/**
 * Represents an instantiated frame - independent and connected only through FE relations
 */
class FrameInstance
{
    protected string $instanceId;
    protected string $frameId; // Reference to original frame definition
    protected string $label;
    protected string $type;
    protected Collection $frameElements;
    protected MindService $mindService;

    public function __construct(string $instanceId, string $frameId, string $label, string $type)
    {
        $this->instanceId = $instanceId;
        $this->frameId = $frameId;
        $this->label = $label;
        $this->type = $type;
        $this->frameElements = new Collection();
        $this->mindService = app(MindService::class);
    }

    public function addFrameElement(FrameElementInstance $fe): void
    {
        $this->frameElements->put($fe->getName(), $fe);
    }

    public function getFrameElement(string $name): ?FrameElementInstance
    {
        return $this->frameElements->get($name);
    }

    /**
     * Agent method - can be called by other agents
     * Agents communicate directly but use Mind service to find targets
     */
    protected function sendMessageToAgent(string $targetInstanceId, string $method, array $params = []): mixed
    {
        $targetInstance = $this->mindService->getFrameInstance($targetInstanceId);

        if ($targetInstance && method_exists($targetInstance, $method)) {
            return $targetInstance->$method(...$params);
        }

        throw new \Exception("Cannot communicate with agent: {$targetInstanceId}::{$method}");
    }

    /**
     * Request Mind service to instantiate a frame
     */
    protected function requestFrameInstantiation(string $frameId, array $context = []): FrameInstance
    {
        return $this->mindService->instantiateFrame($frameId, $context);
    }

    // Getters
    public function getInstanceId(): string { return $this->instanceId; }
    public function getFrameId(): string { return $this->frameId; }
    public function getLabel(): string { return $this->label; }
    public function getType(): string { return $this->type; }
    public function getFrameElements(): Collection { return $this->frameElements; }
}

