<?php

namespace App\Domain\BackgroundTheories\Predicates;

use App\Domain\BackgroundTheories\BackgroundPredicate;
use App\Domain\BackgroundTheories\BackgroundReasoningContext;
use App\Domain\BackgroundTheories\Entities\SetEntity;

/**
 * MemberPredicate - Set membership predicate from Background Theories Chapter 6
 * 
 * FOL: (member x s)
 * Meaning: x is a member of set s
 * 
 * Core predicate for traditional set theory operations.
 */
class MemberPredicate extends BackgroundPredicate
{
    /**
     * Constructor for member predicate
     * 
     * @param mixed $element The element that may be in the set
     * @param SetEntity $set The set that may contain the element
     */
    public function __construct(mixed $element, SetEntity $set)
    {
        parent::__construct('member', [$element, $set]);
    }

    /**
     * Evaluate this member predicate
     * 
     * @param BackgroundReasoningContext $context The reasoning context
     * @return bool True if the element is a member of the set
     */
    public function evaluate(BackgroundReasoningContext $context): bool
    {
        $element = $this->getElement();
        $set = $this->getSet();
        
        if (!$set) {
            return false;
        }

        return $set->contains($element);
    }

    /**
     * Get FOL representation
     */
    public function toFOL(): string
    {
        $element = $this->getElement();
        $set = $this->getSet();
        
        $elementRef = is_string($element) ? $element : 
                      (is_object($element) && method_exists($element, 'getId') ? $element->getId() : 'unknown');
        $setRef = $set ? $set->getId() : 'unknown_set';
        
        return "(member {$elementRef} {$setRef})";
    }

    /**
     * Get the element
     */
    public function getElement(): mixed
    {
        return $this->getArgument(0);
    }

    /**
     * Get the set
     */
    public function getSet(): ?SetEntity
    {
        $set = $this->getArgument(1);
        return $set instanceof SetEntity ? $set : null;
    }

    /**
     * Add the element to the set (make this predicate true)
     */
    public function makeMember(): void
    {
        $set = $this->getSet();
        $element = $this->getElement();
        
        if ($set && $element !== null) {
            $set->addElement($element);
            $this->realize();
        }
    }

    /**
     * Remove the element from the set (make this predicate false)
     */
    public function removeMember(): void
    {
        $set = $this->getSet();
        $element = $this->getElement();
        
        if ($set && $element !== null) {
            $set->removeElement($element);
            $this->unrealize();
        }
    }

    /**
     * Defeasible reasoning
     * 
     * For membership, we assume standard set theory unless contradicted.
     */
    public function etc(): bool
    {
        return true;
    }

    /**
     * Description for debugging/logging
     */
    public function describe(): string
    {
        $element = $this->getElement();
        $set = $this->getSet();
        
        $elementDesc = is_string($element) ? $element : 
                       (is_object($element) ? get_class($element) : json_encode($element));
        $setDesc = $set ? $set->describe() : 'unknown set';
        $reallyExists = $this->getReallyExists() ? ' [ASSERTED]' : ' [potential]';
        
        return "member({$elementDesc}, {$setDesc}){$reallyExists}";
    }
}