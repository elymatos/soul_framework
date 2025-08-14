<?php

namespace App\Domain\BackgroundTheories\Entities;

use App\Domain\BackgroundTheories\BackgroundEntity;

/**
 * SetEntity - set entity from Background Theories Chapter 6
 * 
 * Represents mathematical sets from traditional set theory as formalized
 * in Gordon & Hobbs' theory. Supports standard set operations.
 */
class SetEntity extends BackgroundEntity
{
    public function __construct(array $attributes = [])
    {
        // Initialize with empty elements if not provided
        if (!isset($attributes['elements'])) {
            $attributes['elements'] = [];
        }
        
        parent::__construct('set', $attributes);
    }

    public function validate(): bool
    {
        $elements = $this->getAttribute('elements');
        return is_array($elements);
    }

    public function describe(): string
    {
        $elements = $this->getElements();
        $count = count($elements);
        $reallyExists = $this->reallyExists() ? ' (REAL)' : ' (potential)';
        
        if ($count <= 3) {
            $elemStr = '{' . implode(', ', array_map(function($elem) {
                return is_string($elem) ? $elem : json_encode($elem);
            }, $elements)) . '}';
        } else {
            $sample = array_slice($elements, 0, 3);
            $elemStr = '{' . implode(', ', $sample) . '...}';
        }

        return "set({$elemStr}, |{$count}|){$reallyExists}";
    }

    /**
     * Get all elements in this set
     */
    public function getElements(): array
    {
        return $this->getAttribute('elements', []);
    }

    /**
     * Set all elements in this set
     */
    public function setElements(array $elements): void
    {
        // Remove duplicates to maintain set property
        $this->setAttribute('elements', array_values(array_unique($elements, SORT_REGULAR)));
    }

    /**
     * Add an element to this set
     */
    public function addElement(mixed $element): void
    {
        $elements = $this->getElements();
        
        if (!in_array($element, $elements, true)) {
            $elements[] = $element;
            $this->setElements($elements);
        }
    }

    /**
     * Remove an element from this set
     */
    public function removeElement(mixed $element): void
    {
        $elements = $this->getElements();
        $filtered = array_filter($elements, fn($e) => $e !== $element);
        $this->setElements($filtered);
    }

    /**
     * Check if this set contains an element
     */
    public function contains(mixed $element): bool
    {
        return in_array($element, $this->getElements(), true);
    }

    /**
     * Get the cardinality (size) of this set
     */
    public function cardinality(): int
    {
        return count($this->getElements());
    }

    /**
     * Check if this set is empty
     */
    public function isEmpty(): bool
    {
        return $this->cardinality() === 0;
    }

    /**
     * Create union with another set
     */
    public function union(SetEntity $other): SetEntity
    {
        $unionElements = array_unique(
            array_merge($this->getElements(), $other->getElements()),
            SORT_REGULAR
        );
        
        $union = new SetEntity(['elements' => $unionElements]);
        return $union;
    }

    /**
     * Create intersection with another set
     */
    public function intersection(SetEntity $other): SetEntity
    {
        $intersectionElements = array_intersect($this->getElements(), $other->getElements());
        
        $intersection = new SetEntity(['elements' => array_values($intersectionElements)]);
        return $intersection;
    }

    /**
     * Check if this is a subset of another set
     */
    public function isSubsetOf(SetEntity $other): bool
    {
        $myElements = $this->getElements();
        $otherElements = $other->getElements();
        
        foreach ($myElements as $element) {
            if (!in_array($element, $otherElements, true)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if sets are equal (contain same elements)
     */
    public function equals(SetEntity $other): bool
    {
        $myElements = $this->getElements();
        $otherElements = $other->getElements();
        
        sort($myElements);
        sort($otherElements);
        
        return $myElements === $otherElements;
    }
}