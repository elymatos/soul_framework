<?php

namespace App\Soul\Models;

use App\Soul\Models\BaseGraphModel;
use Illuminate\Support\Collection;

/**
 * PsychologicalPredicate - Model representing predicates used in FOL axioms
 *
 * This model represents predicates extracted from FOL axioms, categorized
 * by their psychological and logical roles in commonsense reasoning.
 */
class PsychologicalPredicate extends BaseGraphModel
{
    protected string $label = 'PsychologicalPredicate';
    
    protected array $fillable = [
        'name',
        'predicate_type',
        'arity',
        'description',
        'domain',
        'usage_count',
        'axiom_references',
        'activation_strength',
        'semantic_field',
        'argument_types',
        'conceptual_role',
        'examples',
        'created_at',
        'updated_at'
    ];
    
    protected array $casts = [
        'arity' => 'integer',
        'usage_count' => 'integer',
        'axiom_references' => 'array',
        'activation_strength' => 'float',
        'argument_types' => 'array',
        'examples' => 'array'
    ];
    
    // Predicate type constants
    public const TYPE_MENTAL_STATE = 'mental_state';
    public const TYPE_ACTION = 'action';
    public const TYPE_RELATION = 'relation';
    public const TYPE_EMOTION = 'emotion';
    public const TYPE_PERCEPTION = 'perception';
    public const TYPE_BELIEF = 'belief';
    public const TYPE_GOAL = 'goal';
    public const TYPE_CAUSAL = 'causal';
    public const TYPE_TEMPORAL = 'temporal';
    public const TYPE_ENTITY = 'entity';
    
    // Domain constants
    public const DOMAIN_PSYCHOLOGY = 'psychology';
    public const DOMAIN_LOGIC = 'logic';
    public const DOMAIN_PHYSICS = 'physics';
    public const DOMAIN_SOCIAL = 'social';
    public const DOMAIN_TEMPORAL = 'temporal';
    public const DOMAIN_SPATIAL = 'spatial';
    
    /**
     * Get the predicate type
     */
    public function getPredicateType(): string
    {
        return $this->predicate_type ?? self::TYPE_ENTITY;
    }
    
    /**
     * Check if this is a mental state predicate
     */
    public function isMentalStatePredicate(): bool
    {
        return $this->predicate_type === self::TYPE_MENTAL_STATE;
    }
    
    /**
     * Check if this is an action predicate
     */
    public function isActionPredicate(): bool
    {
        return $this->predicate_type === self::TYPE_ACTION;
    }
    
    /**
     * Check if this is an emotion predicate
     */
    public function isEmotionPredicate(): bool
    {
        return $this->predicate_type === self::TYPE_EMOTION;
    }
    
    /**
     * Check if this is a belief-related predicate
     */
    public function isBeliefPredicate(): bool
    {
        return $this->predicate_type === self::TYPE_BELIEF;
    }
    
    /**
     * Check if this is a goal-related predicate
     */
    public function isGoalPredicate(): bool
    {
        return $this->predicate_type === self::TYPE_GOAL;
    }
    
    /**
     * Check if this is a causal predicate
     */
    public function isCausalPredicate(): bool
    {
        return $this->predicate_type === self::TYPE_CAUSAL;
    }
    
    /**
     * Get the arity (number of arguments) of this predicate
     */
    public function getArity(): int
    {
        return $this->arity ?? 1;
    }
    
    /**
     * Get usage statistics
     */
    public function getUsageCount(): int
    {
        return $this->usage_count ?? 0;
    }
    
    /**
     * Get list of axioms that reference this predicate
     */
    public function getAxiomReferences(): array
    {
        return $this->axiom_references ?? [];
    }
    
    /**
     * Get argument types for this predicate
     */
    public function getArgumentTypes(): array
    {
        return $this->argument_types ?? [];
    }
    
    /**
     * Add an axiom reference
     */
    public function addAxiomReference(string $axiomId): void
    {
        $references = $this->getAxiomReferences();
        
        if (!in_array($axiomId, $references)) {
            $references[] = $axiomId;
            $this->axiom_references = $references;
            $this->usage_count = count($references);
        }
    }
    
    /**
     * Get activation strength for spreading activation
     */
    public function getActivationStrength(): float
    {
        if ($this->activation_strength !== null) {
            return $this->activation_strength;
        }
        
        // Calculate based on usage and type
        $baseStrength = 0.5;
        
        // Higher strength for frequently used predicates
        $usageBonus = min(0.3, $this->getUsageCount() * 0.05);
        $baseStrength += $usageBonus;
        
        // Type-based adjustments
        switch ($this->predicate_type) {
            case self::TYPE_MENTAL_STATE:
            case self::TYPE_BELIEF:
            case self::TYPE_GOAL:
                $baseStrength += 0.2; // High importance in psychology
                break;
            case self::TYPE_EMOTION:
            case self::TYPE_CAUSAL:
                $baseStrength += 0.15;
                break;
            case self::TYPE_ACTION:
            case self::TYPE_PERCEPTION:
                $baseStrength += 0.1;
                break;
        }
        
        return min(1.0, $baseStrength);
    }
    
    /**
     * Determine predicate type from name and context
     */
    public static function inferPredicateType(string $name, array $context = []): string
    {
        $lowerName = strtolower($name);
        
        // Mental state predicates
        $mentalStateTerms = ['believe', 'know', 'think', 'remember', 'understand', 'realize'];
        if (in_array($lowerName, $mentalStateTerms)) {
            return self::TYPE_MENTAL_STATE;
        }
        
        // Belief predicates
        $beliefTerms = ['belief', 'believe', 'trust', 'doubt', 'certain'];
        if (in_array($lowerName, $beliefTerms)) {
            return self::TYPE_BELIEF;
        }
        
        // Goal predicates
        $goalTerms = ['goal', 'want', 'desire', 'intend', 'aim', 'wish'];
        if (in_array($lowerName, $goalTerms)) {
            return self::TYPE_GOAL;
        }
        
        // Action predicates
        $actionTerms = ['give', 'take', 'move', 'go', 'come', 'do', 'make', 'create'];
        if (in_array($lowerName, $actionTerms)) {
            return self::TYPE_ACTION;
        }
        
        // Emotion predicates
        $emotionTerms = ['happy', 'sad', 'angry', 'afraid', 'love', 'hate', 'enjoy', 'fear'];
        if (in_array($lowerName, $emotionTerms)) {
            return self::TYPE_EMOTION;
        }
        
        // Perception predicates
        $perceptionTerms = ['see', 'hear', 'perceive', 'sense', 'feel', 'observe', 'notice'];
        if (in_array($lowerName, $perceptionTerms)) {
            return self::TYPE_PERCEPTION;
        }
        
        // Causal predicates
        $causalTerms = ['cause', 'effect', 'result', 'lead', 'produce', 'trigger'];
        if (in_array($lowerName, $causalTerms)) {
            return self::TYPE_CAUSAL;
        }
        
        // Relation predicates
        $relationTerms = ['equal', 'member', 'part', 'near', 'between', 'above', 'below'];
        if (in_array($lowerName, $relationTerms)) {
            return self::TYPE_RELATION;
        }
        
        // Temporal predicates
        $temporalTerms = ['before', 'after', 'during', 'when', 'until', 'since'];
        if (in_array($lowerName, $temporalTerms)) {
            return self::TYPE_TEMPORAL;
        }
        
        // Default to entity
        return self::TYPE_ENTITY;
    }
    
    /**
     * Infer arity from predicate name and context
     */
    public static function inferArity(string $name, array $context = []): int
    {
        $lowerName = strtolower($name);
        
        // Unary predicates (properties)
        $unaryTerms = ['person', 'agent', 'car', 'bird', 'happy', 'sad', 'tall', 'red'];
        if (in_array($lowerName, $unaryTerms)) {
            return 1;
        }
        
        // Binary predicates (relations)
        $binaryTerms = ['love', 'see', 'know', 'believe', 'want', 'have', 'own', 'near'];
        if (in_array($lowerName, $binaryTerms)) {
            return 2;
        }
        
        // Ternary predicates
        $ternaryTerms = ['give', 'between', 'transfer', 'tell', 'show'];
        if (in_array($lowerName, $ternaryTerms)) {
            return 3;
        }
        
        // Check context for variable count if available
        if (isset($context['variables']) && is_array($context['variables'])) {
            return min(count($context['variables']), 4);
        }
        
        // Default to binary (most common in psychology)
        return 2;
    }
    
    /**
     * Generate description for predicate
     */
    public function generateDescription(): string
    {
        if (!empty($this->description)) {
            return $this->description;
        }
        
        $type = $this->getPredicateType();
        $arity = $this->getArity();
        $domain = $this->domain ?? 'general';
        
        return "A {$type} predicate from the {$domain} domain, taking {$arity} argument(s). Used in {$this->getUsageCount()} axiom(s).";
    }
    
    /**
     * Get examples of predicate usage
     */
    public function getExamples(): array
    {
        return $this->examples ?? [];
    }
    
    /**
     * Add usage example
     */
    public function addExample(string $example): void
    {
        $examples = $this->getExamples();
        
        if (!in_array($example, $examples)) {
            $examples[] = $example;
            $this->examples = array_slice($examples, -5); // Keep last 5 examples
        }
    }
    
    /**
     * Get conceptual role in cognitive processing
     */
    public function getConceptualRole(): string
    {
        return $this->conceptual_role ?? match($this->predicate_type) {
            self::TYPE_MENTAL_STATE => 'Represents internal cognitive states',
            self::TYPE_ACTION => 'Represents physical or mental actions',
            self::TYPE_EMOTION => 'Represents emotional states and reactions',
            self::TYPE_BELIEF => 'Represents belief formation and maintenance',
            self::TYPE_GOAL => 'Represents goal formation and pursuit',
            self::TYPE_CAUSAL => 'Represents causal relationships',
            self::TYPE_PERCEPTION => 'Represents perceptual processes',
            self::TYPE_RELATION => 'Represents abstract relationships',
            self::TYPE_TEMPORAL => 'Represents temporal relationships',
            default => 'General predicate role'
        };
    }
    
    /**
     * Validate predicate data
     */
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->name)) {
            $errors[] = 'name is required';
        }
        
        if ($this->arity !== null && ($this->arity < 0 || $this->arity > 10)) {
            $errors[] = 'arity must be between 0 and 10';
        }
        
        if ($this->activation_strength !== null && 
            ($this->activation_strength < 0.0 || $this->activation_strength > 1.0)) {
            $errors[] = 'activation_strength must be between 0.0 and 1.0';
        }
        
        if ($this->usage_count !== null && $this->usage_count < 0) {
            $errors[] = 'usage_count cannot be negative';
        }
        
        return $errors;
    }
    
    /**
     * Check if predicate is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }
    
    /**
     * Get predicate summary
     */
    public function getSummary(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->getPredicateType(),
            'arity' => $this->getArity(),
            'domain' => $this->domain,
            'usage_count' => $this->getUsageCount(),
            'activation_strength' => $this->getActivationStrength(),
            'axiom_references_count' => count($this->getAxiomReferences())
        ];
    }
}