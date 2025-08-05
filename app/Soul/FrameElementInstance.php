<?php

namespace App\Soul;

use Illuminate\Support\Collection;
use App\Soul\Services\MindService;
/**
 * Frame Element Instance - holds actual values and relations
 */
class FrameElementInstance
{
    protected string $name;
    protected string $feType;
    protected ?string $description;
    protected array $constraints;
    protected bool $required;
    protected mixed $value = null;
    protected Collection $relations; // Relations to other FE instances
    protected MindService $mindService;

    public function __construct(string $name, string $feType, ?string $description, array $constraints, bool $required)
    {
        $this->name = $name;
        $this->feType = $feType;
        $this->description = $description;
        $this->constraints = $constraints;
        $this->required = $required;
        $this->relations = new Collection();
        $this->mindService = app(MindService::class);
    }

    /**
     * Set value with constraint validation
     */
    public function setValue(mixed $value): bool
    {
        if ($this->validateConstraints($value)) {
            $this->value = $value;
            return true;
        }
        return false;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function hasValue(): bool
    {
        return $this->value !== null;
    }

    /**
     * Create relation to another FE instance through a relation frame
     */
    public function relateTo(FrameElementInstance $other, string $relationType): void
    {
        // Create relation frame instance
        $relationFrame = $this->mindService->instantiateFrame($relationType);

        // Set this FE as Figure and other as Ground
        $relationFrame->getFrameElement('figure')->setValue($this);
        $relationFrame->getFrameElement('ground')->setValue($other);

        // Store relation
        $this->relations->push($relationFrame);
    }

    /**
     * Get all relations of a specific type
     */
    public function getRelations(string $relationType = null): Collection
    {
        if ($relationType) {
            return $this->relations->filter(function($relation) use ($relationType) {
                return $relation->getFrameId() === $relationType;
            });
        }
        return $this->relations;
    }

    /**
     * Implement Minsky's "shared slots" concept
     */
    public function shareSlotWith(FrameElementInstance $other): void
    {
        $this->relateTo($other, 'SHARED_SLOT');
    }

    protected function validateConstraints(mixed $value): bool
    {
        // Implement Minsky's marker validation
        foreach ($this->constraints as $constraint) {
            // To be implemented based on specific constraint types
        }
        return true;
    }

    // Getters
    public function getName(): string { return $this->name; }
    public function getFeType(): string { return $this->feType; }
    public function getConstraints(): array { return $this->constraints; }
    public function isRequired(): bool { return $this->required; }
}
