<?php

namespace App\Domain\BackgroundTheories\AxiomExecutors;

use App\Domain\BackgroundTheories\BackgroundAxiomExecutor;
use App\Domain\BackgroundTheories\BackgroundReasoningContext;
use App\Domain\BackgroundTheories\Entities\SetEntity;
use App\Domain\BackgroundTheories\Predicates\MemberPredicate;
use Illuminate\Support\Collection;

/**
 * Axiom6_13Executor - Set Union Axiom executor
 * 
 * Description: s is the union of s1 and s2 if and only if for all x,
 * x is a member of s iff x is a member of s1 or x is a member of s2
 * 
 * FOL Formula: (forall (s s1 s2) (iff (union s s1 s2) 
 *   (forall (x) (iff (member x s) (or (member x s1) (member x s2))))))
 * 
 * This axiom defines set union in terms of membership.
 */
class Axiom6_13Executor extends BackgroundAxiomExecutor
{
    public function __construct()
    {
        parent::__construct(
            '6.13',
            's is the union of s1 and s2 if and only if for all x, x is a member of s iff x is a member of s1 or x is a member of s2',
            ['union', 'member', 'set'],
            '(forall (s s1 s2) (iff (union s s1 s2) (forall (x) (iff (member x s) (or (member x s1) (member x s2))))))',
            'moderate'
        );
    }

    public function execute(BackgroundReasoningContext $context): Collection
    {
        $results = collect();
        $trace = [];

        $trace[] = $this->trace('axiom_6_13_start', [
            'description' => 'Creating set unions and validating union relationships'
        ]);

        // Get all sets from the context
        $sets = $context->getEntitiesByType('set');
        
        if ($sets->count() < 2) {
            $trace[] = $this->trace('insufficient_sets', [
                'sets_count' => $sets->count(),
                'required' => 'at least 2'
            ]);
            return $results;
        }

        $setsArray = $sets->toArray();
        
        // For each pair of sets, create their union
        for ($i = 0; $i < count($setsArray); $i++) {
            for ($j = $i + 1; $j < count($setsArray); $j++) {
                $set1 = $setsArray[$i];
                $set2 = $setsArray[$j];
                
                if ($set1 instanceof SetEntity && $set2 instanceof SetEntity) {
                    $union = $this->createUnion($set1, $set2, $context, $results, $trace);
                    
                    if ($union) {
                        // Create member predicates for all elements in the union
                        $this->createMemberPredicates($union, $context, $results, $trace);
                    }
                }
            }
        }

        // Also validate existing union relationships
        $this->validateExistingUnions($context, $results, $trace);

        $trace[] = $this->trace('axiom_6_13_complete', [
            'results_count' => $results->count()
        ]);

        return $results;
    }

    /**
     * Create a union set from two sets
     */
    private function createUnion(
        SetEntity $set1, 
        SetEntity $set2, 
        BackgroundReasoningContext $context, 
        Collection $results, 
        array &$trace
    ): ?SetEntity {
        
        // Check if union already exists
        $existingUnion = $this->findExistingUnion($set1, $set2, $context);
        if ($existingUnion) {
            $trace[] = $this->trace('union_already_exists', [
                'set1_id' => $set1->getId(),
                'set2_id' => $set2->getId(),
                'union_id' => $existingUnion->getId()
            ]);
            return $existingUnion;
        }

        // Create the mathematical union
        $union = $set1->union($set2);
        
        // Set descriptive attributes
        $union->setAttribute('union_of', [$set1->getId(), $set2->getId()]);
        $union->setAttribute('operation', 'union');
        
        // Add to context and results
        $context->addEntity($union);
        $results->push($union);
        
        $trace[] = $this->trace('union_created', [
            'set1_id' => $set1->getId(),
            'set1_cardinality' => $set1->cardinality(),
            'set2_id' => $set2->getId(), 
            'set2_cardinality' => $set2->cardinality(),
            'union_id' => $union->getId(),
            'union_cardinality' => $union->cardinality()
        ]);

        return $union;
    }

    /**
     * Create member predicates for all elements in a union set
     */
    private function createMemberPredicates(
        SetEntity $union, 
        BackgroundReasoningContext $context, 
        Collection $results, 
        array &$trace
    ): void {
        
        $elements = $union->getElements();
        
        foreach ($elements as $element) {
            // Check if member predicate already exists
            if (!$this->memberPredicateExists($element, $union, $context)) {
                $memberPredicate = new MemberPredicate($element, $union);
                $memberPredicate->realize();
                
                $context->addPredicate($memberPredicate);
                $results->push($memberPredicate);
                
                $trace[] = $this->trace('member_predicate_created', [
                    'element' => is_string($element) ? $element : json_encode($element),
                    'set_id' => $union->getId()
                ]);
            }
        }
    }

    /**
     * Check if a member predicate exists for an element and set
     */
    private function memberPredicateExists(mixed $element, SetEntity $set, BackgroundReasoningContext $context): bool
    {
        $memberPredicates = $context->getPredicatesByName('member');
        
        foreach ($memberPredicates as $predicate) {
            if ($predicate instanceof MemberPredicate) {
                $predicateElement = $predicate->getElement();
                $predicateSet = $predicate->getSet();
                
                if ($predicateSet && $predicateSet->getId() === $set->getId() && $predicateElement === $element) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Find existing union of two sets
     */
    private function findExistingUnion(SetEntity $set1, SetEntity $set2, BackgroundReasoningContext $context): ?SetEntity
    {
        $sets = $context->getEntitiesByType('set');
        
        foreach ($sets as $set) {
            if ($set instanceof SetEntity) {
                $unionOf = $set->getAttribute('union_of', []);
                
                if (is_array($unionOf) && count($unionOf) === 2) {
                    $unionSet1Id = $unionOf[0];
                    $unionSet2Id = $unionOf[1];
                    
                    if (($unionSet1Id === $set1->getId() && $unionSet2Id === $set2->getId()) ||
                        ($unionSet1Id === $set2->getId() && $unionSet2Id === $set1->getId())) {
                        return $set;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Validate existing union relationships
     */
    private function validateExistingUnions(
        BackgroundReasoningContext $context, 
        Collection $results, 
        array &$trace
    ): void {
        
        $sets = $context->getEntitiesByType('set');
        
        foreach ($sets as $set) {
            if ($set instanceof SetEntity) {
                $unionOf = $set->getAttribute('union_of');
                
                if (is_array($unionOf) && count($unionOf) === 2) {
                    $this->validateUnionCorrectness($set, $unionOf, $context, $trace);
                }
            }
        }
    }

    /**
     * Validate that a union set correctly represents the union of its component sets
     */
    private function validateUnionCorrectness(
        SetEntity $unionSet, 
        array $componentSetIds, 
        BackgroundReasoningContext $context, 
        array &$trace
    ): void {
        
        $set1 = $context->getEntityById($componentSetIds[0]);
        $set2 = $context->getEntityById($componentSetIds[1]);
        
        if (!$set1 instanceof SetEntity || !$set2 instanceof SetEntity) {
            $trace[] = $this->trace('union_validation_failed', [
                'union_id' => $unionSet->getId(),
                'reason' => 'component sets not found'
            ]);
            return;
        }
        
        // Check if the union set contains all elements from both component sets
        $expectedUnion = $set1->union($set2);
        
        if (!$unionSet->equals($expectedUnion)) {
            $trace[] = $this->trace('union_inconsistency_detected', [
                'union_id' => $unionSet->getId(),
                'expected_cardinality' => $expectedUnion->cardinality(),
                'actual_cardinality' => $unionSet->cardinality()
            ]);
        } else {
            $trace[] = $this->trace('union_validated', [
                'union_id' => $unionSet->getId(),
                'component_sets' => $componentSetIds
            ]);
        }
    }

    /**
     * Check if this axiom is applicable
     */
    public function isApplicable(BackgroundReasoningContext $context): bool
    {
        // Applicable when there are at least 2 sets
        return $context->getEntitiesByType('set')->count() >= 2;
    }
}