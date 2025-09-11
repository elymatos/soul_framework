<?php

namespace App\Domain\BackgroundTheories\Entities;

use App\Domain\BackgroundTheories\BackgroundEntity;

/**
 * EventualityEntity - eventuality entity from Background Theories Chapter 5
 * 
 * Represents reified events, states, and situations from Gordon & Hobbs'
 * formal theory. Eventualities are the fundamental units of the temporal
 * and causal reasoning system.
 */
class EventualityEntity extends BackgroundEntity
{
    public function __construct(array $attributes = [])
    {
        parent::__construct('eventuality', $attributes);
    }

    public function validate(): bool
    {
        // Eventualities should have a predicate name and arguments
        $predicateName = $this->getAttribute('predicate_name');
        if (!$predicateName) {
            return false;
        }

        $arguments = $this->getAttribute('arguments', []);
        if (!is_array($arguments)) {
            return false;
        }

        return true;
    }

    public function describe(): string
    {
        $predicateName = $this->getAttribute('predicate_name', 'unknown');
        $arguments = $this->getAttribute('arguments', []);
        $reallyExists = $this->reallyExists() ? ' (REAL)' : ' (potential)';
        
        $argStr = implode(', ', array_map(function($arg) {
            return is_string($arg) ? $arg : json_encode($arg);
        }, $arguments));

        return "eventuality({$predicateName}({$argStr})){$reallyExists}";
    }

    /**
     * Get the predicate name for this eventuality
     */
    public function getPredicateName(): ?string
    {
        return $this->getAttribute('predicate_name');
    }

    /**
     * Set the predicate name for this eventuality
     */
    public function setPredicateName(string $predicateName): void
    {
        $this->setAttribute('predicate_name', $predicateName);
    }

    /**
     * Get the arguments for this eventuality
     */
    public function getArguments(): array
    {
        return $this->getAttribute('arguments', []);
    }

    /**
     * Set the arguments for this eventuality
     */
    public function setArguments(array $arguments): void
    {
        $this->setAttribute('arguments', $arguments);
    }

    /**
     * Get the nth argument (1-indexed as per FOL convention)
     */
    public function getArg(int $n): mixed
    {
        $arguments = $this->getArguments();
        return $arguments[$n - 1] ?? null;
    }

    /**
     * Set the nth argument (1-indexed)
     */
    public function setArg(int $n, mixed $value): void
    {
        $arguments = $this->getArguments();
        $arguments[$n - 1] = $value;
        $this->setArguments($arguments);
    }

    /**
     * Get the arity (number of arguments) of this eventuality
     */
    public function getArity(): int
    {
        return count($this->getArguments());
    }

    /**
     * Check if this eventuality is complete (has all required structure)
     */
    public function isComplete(): bool
    {
        return $this->getAttribute('complete', false);
    }

    /**
     * Mark this eventuality as complete
     */
    public function setComplete(bool $complete = true): void
    {
        $this->setAttribute('complete', $complete);
    }
}