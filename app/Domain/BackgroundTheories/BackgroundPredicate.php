<?php

namespace App\Domain\BackgroundTheories;

use DateTime;
use JsonSerializable;

/**
 * Abstract base class for all Background Theory predicates
 * 
 * Represents FOL relationships from Gordon & Hobbs' Background Theories
 * including Rexist, member, union, equal, and, not, imply, etc.
 */
abstract class BackgroundPredicate implements JsonSerializable
{
    protected string $id;
    protected string $name;
    protected array $arguments;
    protected int $arity;
    protected bool $reallyExists;
    protected DateTime $createdAt;
    protected ?DateTime $updatedAt = null;
    protected array $metadata;

    /**
     * Constructor for all Background Theory predicates
     */
    public function __construct(string $name, array $arguments)
    {
        $this->id = uniqid($name . '_');
        $this->name = $name;
        $this->arguments = $arguments;
        $this->arity = count($arguments);
        $this->reallyExists = false;
        $this->createdAt = new DateTime();
        $this->metadata = [];
    }

    // Standard getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgument(int $index): mixed
    {
        return $this->arguments[$index] ?? null;
    }

    public function getArity(): int
    {
        return $this->arity;
    }

    public function getReallyExists(): bool
    {
        return $this->reallyExists;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    // Standard setters
    public function setReallyExists(bool $exists): void
    {
        $this->reallyExists = $exists;
        $this->updatedAt = new DateTime();
    }

    public function realize(): void
    {
        $this->setReallyExists(true);
    }

    public function unrealize(): void
    {
        $this->setReallyExists(false);
    }

    public function setMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
        $this->updatedAt = new DateTime();
    }

    // Database serialization methods
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'arguments' => json_encode($this->serializeArguments()),
            'arity' => $this->arity,
            'really_exists' => $this->reallyExists,
            'metadata' => json_encode($this->metadata),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function fromDatabaseArray(array $data): static
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->arguments = $this->deserializeArguments(json_decode($data['arguments'], true) ?? []);
        $this->arity = $data['arity'];
        $this->reallyExists = (bool) $data['really_exists'];
        $this->metadata = json_decode($data['metadata'], true) ?? [];
        $this->createdAt = new DateTime($data['created_at']);
        $this->updatedAt = $data['updated_at'] ? new DateTime($data['updated_at']) : null;
        
        return $this;
    }

    // JSON serialization
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'arguments' => $this->serializeArguments(),
            'arity' => $this->arity,
            'really_exists' => $this->reallyExists,
            'metadata' => $this->metadata,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt?->format('c'),
        ];
    }

    // Argument serialization helpers
    protected function serializeArguments(): array
    {
        return array_map(function ($arg) {
            if ($arg instanceof BackgroundEntity) {
                return ['type' => 'entity', 'id' => $arg->getId(), 'entity_type' => $arg->getType()];
            } elseif ($arg instanceof BackgroundPredicate) {
                return ['type' => 'predicate', 'id' => $arg->getId(), 'name' => $arg->getName()];
            } else {
                return ['type' => 'primitive', 'value' => $arg];
            }
        }, $this->arguments);
    }

    protected function deserializeArguments(array $serialized): array
    {
        // This would need access to repository to resolve entity/predicate references
        // For now, return the serialized data as placeholders
        return $serialized;
    }

    // Abstract methods for subclasses to implement

    /**
     * Evaluate this predicate in the given reasoning context
     */
    abstract public function evaluate(BackgroundReasoningContext $context): bool;

    /**
     * Get FOL representation of this predicate
     */
    abstract public function toFOL(): string;

    /**
     * Defeasible reasoning - "everything else being normal"
     * Default implementation returns true (no exceptions)
     */
    public function etc(): bool
    {
        return true;
    }

    /**
     * Get a human-readable description of this predicate
     */
    public function describe(): string
    {
        $argDescriptions = array_map(function ($arg, $index) {
            if ($arg instanceof BackgroundEntity) {
                return "arg{$index}:{$arg->getType()}({$arg->getId()})";
            } elseif ($arg instanceof BackgroundPredicate) {
                return "arg{$index}:{$arg->getName()}({$arg->getId()})";
            } else {
                return "arg{$index}:" . (is_string($arg) ? $arg : json_encode($arg));
            }
        }, $this->arguments, array_keys($this->arguments));

        return "{$this->name}(" . implode(', ', $argDescriptions) . ")";
    }

    /**
     * Check if two predicates are equivalent
     */
    public function equals(BackgroundPredicate $other): bool
    {
        if ($this->name !== $other->name || $this->arity !== $other->arity) {
            return false;
        }

        for ($i = 0; $i < $this->arity; $i++) {
            $thisArg = $this->getArgument($i);
            $otherArg = $other->getArgument($i);

            if ($thisArg instanceof BackgroundEntity && $otherArg instanceof BackgroundEntity) {
                if (!$thisArg->equals($otherArg)) {
                    return false;
                }
            } elseif ($thisArg instanceof BackgroundPredicate && $otherArg instanceof BackgroundPredicate) {
                if (!$thisArg->equals($otherArg)) {
                    return false;
                }
            } elseif ($thisArg !== $otherArg) {
                return false;
            }
        }

        return true;
    }
}