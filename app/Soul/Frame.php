<?php

namespace App\Soul;

use Illuminate\Support\Collection;
use Soul\Services\MindService;
/**
 * Abstract base class for all frames in the SOUL framework
 * Represents Minsky's frame concept with dynamic frame elements
 */
abstract class Frame
{
    protected string $id;
    protected string $label;
    protected string $type; // e.g., 'primitive', 'derived', 'image_schema', 'csp', 'linguistic'
    protected Collection $frameElements;
    protected array $defaultValues = []; // Minsky's default assignments
    protected MindService $mindService;

    public function __construct(string $id, string $label, string $type)
    {
        $this->id = $id;
        $this->label = $label;
        $this->type = $type;
        $this->frameElements = new Collection();
        $this->mindService = app(MindService::class);
    }

    /**
     * Creates an independent instance of this frame
     * Once instantiated, it operates independently through FE relations
     */
    public function instantiate(array $context = [], ?string $instanceId = null): FrameInstance
    {
        $instanceId = $instanceId ?? $this->generateInstanceId();

        $instance = new FrameInstance(
            $instanceId,
            $this->id,
            $this->label,
            $this->type
        );

        // Copy frame elements structure but create new instances
        foreach ($this->frameElements as $fe) {
            $feInstance = $fe->createInstance();
            $instance->addFrameElement($feInstance);
        }

        // Apply default values (Minsky's defaults)
        $this->applyDefaults($instance, $context);

        // Register instance with Mind service
        $this->mindService->registerFrameInstance($instance);

        return $instance;
    }

    /**
     * Minsky's matching process - attempts to match this frame to input
     */
    public function match(array $input): float
    {
        // To be implemented by specific frame types
        // Returns confidence score 0.0 - 1.0
        return 0.0;
    }

    /**
     * Add a frame element definition to this frame
     */
    public function addFrameElement(FrameElement $fe): void
    {
        $this->frameElements->put($fe->getName(), $fe);
    }

    /**
     * Get frame element by name
     */
    public function getFrameElement(string $name): ?FrameElement
    {
        return $this->frameElements->get($name);
    }

    /**
     * Set default value for a frame element
     */
    public function setDefault(string $feName, $value): void
    {
        $this->defaultValues[$feName] = $value;
    }

    protected function applyDefaults(FrameInstance $instance, array $context): void
    {
        foreach ($this->defaultValues as $feName => $defaultValue) {
            $fe = $instance->getFrameElement($feName);
            if ($fe && !$fe->hasValue()) {
                $fe->setValue($defaultValue);
            }
        }
    }

    protected function generateInstanceId(): string
    {
        return $this->id . '_' . uniqid();
    }

    // Getters
    public function getId(): string { return $this->id; }
    public function getLabel(): string { return $this->label; }
    public function getType(): string { return $this->type; }
}

