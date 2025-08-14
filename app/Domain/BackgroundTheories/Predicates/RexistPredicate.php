<?php

namespace App\Domain\BackgroundTheories\Predicates;

use App\Domain\BackgroundTheories\BackgroundPredicate;
use App\Domain\BackgroundTheories\BackgroundReasoningContext;
use App\Domain\BackgroundTheories\BackgroundEntity;

/**
 * RexistPredicate - "Really Exists" predicate from Background Theories
 * 
 * FOL: (Rexist e)
 * Meaning: Entity e really exists in the real world (not just potentially)
 * 
 * This is one of the most fundamental predicates in the system,
 * used throughout Chapters 5-20.
 */
class RexistPredicate extends BackgroundPredicate
{
    /**
     * Constructor for Rexist predicate
     * 
     * @param BackgroundEntity $entity The entity that may really exist
     */
    public function __construct(BackgroundEntity $entity)
    {
        parent::__construct('Rexist', [$entity]);
    }

    /**
     * Evaluate this Rexist predicate
     * 
     * @param BackgroundReasoningContext $context The reasoning context
     * @return bool True if the entity really exists
     */
    public function evaluate(BackgroundReasoningContext $context): bool
    {
        $entity = $this->getEntity();
        
        if (!$entity) {
            return false;
        }

        // Check if the entity has been marked as really existing
        return $entity->reallyExists();
    }

    /**
     * Get FOL representation
     */
    public function toFOL(): string
    {
        $entity = $this->getEntity();
        $entityRef = $entity ? $entity->getId() : 'unknown_entity';
        
        return "(Rexist {$entityRef})";
    }

    /**
     * Get the entity this predicate refers to
     */
    public function getEntity(): ?BackgroundEntity
    {
        $entity = $this->getArgument(0);
        return $entity instanceof BackgroundEntity ? $entity : null;
    }

    /**
     * Make the entity really exist
     * 
     * This is a convenience method that both marks the entity as really existing
     * and marks this predicate as really existing.
     */
    public function makeReallyExist(): void
    {
        $entity = $this->getEntity();
        if ($entity) {
            $entity->realize();
            $this->realize();
        }
    }

    /**
     * Make the entity not really exist
     */
    public function makeNotReallyExist(): void
    {
        $entity = $this->getEntity();
        if ($entity) {
            $entity->unrealize();
            $this->unrealize();
        }
    }

    /**
     * Defeasible reasoning - check for exceptions
     * 
     * For Rexist, we generally assume no exceptions unless specifically
     * contradicted by other information.
     */
    public function etc(): bool
    {
        // Default: no exceptions to reality
        return true;
    }

    /**
     * Description for debugging/logging
     */
    public function describe(): string
    {
        $entity = $this->getEntity();
        $entityDesc = $entity ? $entity->describe() : 'unknown entity';
        $reallyExists = $this->getReallyExists() ? ' [ASSERTED]' : ' [potential]';
        
        return "Rexist({$entityDesc}){$reallyExists}";
    }
}