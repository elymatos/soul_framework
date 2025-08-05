<?php

namespace App\Soul;

/**
 * Frame Element definition - template for creating FE instances
 */
class FrameElement
{
    protected string $name;
    protected string $feType; // FE-type for cross-frame mapping
    protected ?string $description;
    protected array $constraints = []; // Minsky's conditions/markers
    protected bool $required = false;

    public function __construct(string $name, string $feType, ?string $description = null)
    {
        $this->name = $name;
        $this->feType = $feType;
        $this->description = $description;
    }

    /**
     * Create an instance of this frame element
     */
    public function createInstance(): FrameElementInstance
    {
        return new FrameElementInstance(
            $this->name,
            $this->feType,
            $this->description,
            $this->constraints,
            $this->required
        );
    }

    public function addConstraint(string $constraint): void
    {
        $this->constraints[] = $constraint;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    // Getters
    public function getName(): string { return $this->name; }
    public function getFeType(): string { return $this->feType; }
    public function getDescription(): ?string { return $this->description; }
    public function getConstraints(): array { return $this->constraints; }
    public function isRequired(): bool { return $this->required; }
}

