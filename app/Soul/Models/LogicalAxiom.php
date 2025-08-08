<?php

namespace App\Soul\Models;

use App\Soul\Models\BaseGraphModel;
use Illuminate\Support\Collection;

/**
 * LogicalAxiom - Model representing a First-Order Logic axiom
 *
 * This model represents axioms from Gordon & Hobbs' "A Formal Theory of 
 * Commonsense Psychology" integrated into the SOUL framework for 
 * cognitive reasoning and Society of Mind operations.
 */
class LogicalAxiom extends BaseGraphModel
{
    protected string $label = 'LogicalAxiom';
    
    protected array $fillable = [
        'name',
        'axiom_id',
        'chapter',
        'chapter_title',
        'section',
        'page',
        'axiom_number',
        'title',
        'fol',
        'english',
        'complexity',
        'pattern',
        'predicates',
        'variables',
        'quantifiers',
        'defeasible',
        'reified',
        'domain',
        'confidence',
        'implementation_status',
        'agent_references',
        'created_at',
        'updated_at'
    ];

    protected array $casts = [
        'predicates' => 'array',
        'variables' => 'array', 
        'quantifiers' => 'array',
        'defeasible' => 'boolean',
        'reified' => 'boolean',
        'confidence' => 'float',
        'agent_references' => 'array',
        'page' => 'integer',
        'axiom_number' => 'integer'
    ];
    
    /**
     * Get complexity score for processing prioritization
     */
    public function getComplexityScore(): int
    {
        return match($this->complexity) {
            'simple' => 1,
            'moderate' => 2, 
            'complex' => 3,
            default => 1
        };
    }
    
    /**
     * Check if axiom is defeasible (can have exceptions)
     */
    public function isDefeasible(): bool
    {
        return $this->defeasible === true;
    }
    
    /**
     * Get list of predicates used in this axiom
     */
    public function getPredicateList(): array
    {
        return $this->predicates ?? [];
    }
    
    /**
     * Get list of variables used in this axiom
     */
    public function getVariableList(): array
    {
        return $this->variables ?? [];
    }
    
    /**
     * Get quantifier information
     */
    public function getQuantifiers(): array
    {
        return $this->quantifiers ?? [];
    }
    
    /**
     * Check if axiom is suitable for agent generation
     */
    public function canGenerateAgent(): bool
    {
        // Psychology domain axioms and complex patterns are good candidates
        return $this->domain === 'psychology' || 
               $this->complexity === 'complex' ||
               in_array($this->pattern, [
                   'goal_causation',
                   'belief_logic', 
                   'emotion_causation',
                   'defeasible_rule'
               ]);
    }
    
    /**
     * Get recommended agent service class for this axiom
     */
    public function getRecommendedAgentService(): string
    {
        return match($this->pattern) {
            'goal_causation' => 'GoalReasoningService',
            'belief_logic' => 'BeliefReasoningService',
            'emotion_causation' => 'EmotionService',
            'defeasible_rule' => 'DefeasibleReasoningService',
            'simple_inheritance' => 'TaxonomyService',
            default => 'GeneralReasoningService'
        };
    }
    
    /**
     * Get recommended agent method for this axiom
     */
    public function getRecommendedAgentMethod(): string
    {
        return match($this->pattern) {
            'goal_causation' => 'processGoalCausation',
            'belief_logic' => 'processBeliefFormation',
            'emotion_causation' => 'processEmotionalCausation',
            'defeasible_rule' => 'applyDefeasibleRule',
            'simple_inheritance' => 'applyInheritanceRule',
            default => 'processAxiom'
        };
    }
    
    /**
     * Validate axiom data integrity
     */
    public function validate(): array
    {
        $errors = [];
        
        // Required fields validation
        if (empty($this->axiom_id)) {
            $errors[] = 'axiom_id is required';
        }
        
        if (empty($this->name)) {
            $errors[] = 'name is required';
        }
        
        if (empty($this->fol)) {
            $errors[] = 'fol (first-order logic) expression is required';
        }
        
        if (empty($this->english)) {
            $errors[] = 'english description is required';
        }
        
        // Complexity validation
        if (!in_array($this->complexity, ['simple', 'moderate', 'complex'])) {
            $errors[] = 'complexity must be simple, moderate, or complex';
        }
        
        // Confidence validation
        if ($this->confidence !== null && ($this->confidence < 0.0 || $this->confidence > 1.0)) {
            $errors[] = 'confidence must be between 0.0 and 1.0';
        }
        
        // Domain validation (optional but recommended)
        $validDomains = [
            'psychology', 'logic', 'physics', 'mathematics', 
            'linguistics', 'social', 'temporal', 'spatial'
        ];
        
        if ($this->domain && !in_array($this->domain, $validDomains)) {
            $errors[] = "domain '{$this->domain}' is not recognized";
        }
        
        // Predicates validation
        if (!is_array($this->predicates) && $this->predicates !== null) {
            $errors[] = 'predicates must be an array';
        }
        
        return $errors;
    }
    
    /**
     * Check if axiom is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }
    
    /**
     * Get axiom summary for logging/debugging
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->axiom_id,
            'name' => $this->name,
            'domain' => $this->domain,
            'complexity' => $this->complexity,
            'defeasible' => $this->defeasible,
            'predicates_count' => count($this->getPredicateList()),
            'confidence' => $this->confidence,
            'can_generate_agent' => $this->canGenerateAgent()
        ];
    }
    
    /**
     * Generate unique name if not provided
     */
    public function generateName(): string
    {
        if ($this->title && $this->axiom_id) {
            $cleanTitle = str_replace(' ', '_', strtoupper($this->title));
            return "{$cleanTitle}_{$this->axiom_id}";
        }
        
        if ($this->axiom_id) {
            return "AXIOM_{$this->axiom_id}";
        }
        
        return 'UNNAMED_AXIOM_' . uniqid();
    }
    
    /**
     * Check if axiom matches a pattern
     */
    public function matchesPattern(string $pattern): bool
    {
        if ($this->pattern === $pattern) {
            return true;
        }
        
        // Check for regex pattern matching
        if (preg_match("/{$pattern}/i", $this->pattern ?? '')) {
            return true;
        }
        
        // Check predicates for pattern matching
        foreach ($this->getPredicateList() as $predicate) {
            if (str_contains(strtolower($predicate), strtolower($pattern))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get processing priority based on axiom characteristics
     */
    public function getProcessingPriority(): int
    {
        $priority = 5; // Default medium priority
        
        // Higher priority for psychology domain
        if ($this->domain === 'psychology') {
            $priority -= 1;
        }
        
        // Higher priority for simpler axioms (easier to process)
        switch ($this->complexity) {
            case 'simple':
                $priority -= 1;
                break;
            case 'complex':
                $priority += 1;
                break;
        }
        
        // Lower priority for defeasible axioms (more complex handling)
        if ($this->defeasible) {
            $priority += 1;
        }
        
        return max(1, min(10, $priority));
    }
    
    /**
     * Convert axiom to YAML structure for export/backup
     */
    public function toYaml(): array
    {
        return [
            'name' => $this->name,
            'axiom_id' => $this->axiom_id,
            'title' => $this->title,
            'fol' => $this->fol,
            'english' => $this->english,
            'complexity' => $this->complexity,
            'pattern' => $this->pattern,
            'predicates' => $this->getPredicateList(),
            'variables' => $this->getVariableList(),
            'quantifiers' => $this->getQuantifiers(),
            'defeasible' => $this->defeasible,
            'domain' => $this->domain,
            'confidence' => $this->confidence,
            'properties' => [
                'chapter' => $this->chapter,
                'section' => $this->section,
                'page' => $this->page,
                'axiom_number' => $this->axiom_number,
                'reified' => $this->reified
            ]
        ];
    }
}