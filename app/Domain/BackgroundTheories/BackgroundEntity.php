<?php

namespace App\Domain\BackgroundTheories;

use DateTime;
use JsonSerializable;

/**
 * Abstract base class for all Background Theory entities
 * 
 * Represents domain objects from Gordon & Hobbs' Background Theories
 * including eventualities, sets, composites, functions, sequences, etc.
 */
abstract class BackgroundEntity implements JsonSerializable
{
    protected string $id;
    protected string $type;
    protected array $attributes;
    protected DateTime $createdAt;
    protected ?DateTime $updatedAt = null;

    /**
     * Constructor for all Background Theory entities
     */
    public function __construct(string $type, array $attributes = [])
    {
        $this->id = uniqid($type . '_');
        $this->type = $type;
        $this->attributes = $attributes;
        $this->createdAt = new DateTime();
    }

    // Standard getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    // Standard setters
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
        $this->updatedAt = new DateTime();
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        $this->updatedAt = new DateTime();
    }

    // Database serialization methods
    public function toDatabaseArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'attributes' => json_encode($this->attributes),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function fromDatabaseArray(array $data): static
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->attributes = json_decode($data['attributes'], true) ?? [];
        $this->createdAt = new DateTime($data['created_at']);
        $this->updatedAt = $data['updated_at'] ? new DateTime($data['updated_at']) : null;
        
        return $this;
    }

    // JSON serialization
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'attributes' => $this->attributes,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt?->format('c'),
        ];
    }

    // Abstract methods for subclasses to implement
    
    /**
     * Validate entity according to its type-specific rules
     */
    abstract public function validate(): bool;

    /**
     * Get a human-readable description of this entity
     */
    abstract public function describe(): string;

    /**
     * Check if this entity really exists (FOL: Rexist predicate)
     */
    public function reallyExists(): bool
    {
        return $this->getAttribute('really_exists', false);
    }

    /**
     * Mark this entity as really existing
     */
    public function realize(): void
    {
        $this->setAttribute('really_exists', true);
    }

    /**
     * Mark this entity as not really existing
     */
    public function unrealize(): void
    {
        $this->setAttribute('really_exists', false);
    }

    /**
     * Create a copy of this entity with new ID
     */
    public function copy(): static
    {
        $copy = new static($this->type, $this->attributes);
        return $copy;
    }

    /**
     * Compare two entities for equality
     */
    public function equals(BackgroundEntity $other): bool
    {
        return $this->type === $other->type &&
               $this->attributes === $other->attributes;
    }
}