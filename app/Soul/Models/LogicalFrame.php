<?php

namespace App\Soul\Models;

use App\Soul\Models\BaseGraphModel;
use Illuminate\Support\Collection;

/**
 * LogicalFrame - Model representing frame structures derived from FOL axioms
 *
 * This model represents complex frame structures built from FOL axioms,
 * implementing Fillmore's Frame Semantics within the Society of Mind architecture.
 */
class LogicalFrame extends BaseGraphModel
{
    protected string $label = 'LogicalFrame';
    
    protected array $fillable = [
        'name',
        'frame_type',
        'based_on_axiom',
        'description',
        'domain',
        'complexity',
        'defeasible',
        'elements',
        'constraints',
        'activation_conditions',
        'output_patterns',
        'confidence',
        'usage_count',
        'success_rate',
        'frame_elements',
        'semantic_roles',
        'conceptual_dependencies',
        'processing_requirements',
        'examples',
        'created_at',
        'updated_at'
    ];
    
    protected array $casts = [
        'elements' => 'array',
        'constraints' => 'array',
        'activation_conditions' => 'array',
        'output_patterns' => 'array',
        'defeasible' => 'boolean',
        'confidence' => 'float',
        'usage_count' => 'integer',
        'success_rate' => 'float',
        'frame_elements' => 'array',
        'semantic_roles' => 'array',
        'conceptual_dependencies' => 'array',
        'processing_requirements' => 'array',
        'examples' => 'array'
    ];
    
    // Frame type constants
    public const TYPE_GOAL_CAUSATION = 'goal_causation';
    public const TYPE_EMOTION_CAUSATION = 'emotion_causation';
    public const TYPE_BELIEF_FORMATION = 'belief_formation';
    public const TYPE_PERCEPTION_ACTION = 'perception_action';
    public const TYPE_CAUSAL_CHAIN = 'causal_chain';
    public const TYPE_TEMPORAL_SEQUENCE = 'temporal_sequence';
    public const TYPE_SOCIAL_INTERACTION = 'social_interaction';
    public const TYPE_MENTAL_STATE_CHANGE = 'mental_state_change';
    
    /**
     * Get frame type
     */
    public function getFrameType(): string
    {
        return $this->frame_type ?? self::TYPE_MENTAL_STATE_CHANGE;
    }
    
    /**
     * Get frame elements list
     */
    public function getElements(): array
    {
        return $this->elements ?? [];
    }
    
    /**
     * Get frame constraints
     */
    public function getConstraints(): array
    {
        return $this->constraints ?? [];
    }
    
    /**
     * Get activation conditions
     */
    public function getActivationConditions(): array
    {
        return $this->activation_conditions ?? [];
    }
    
    /**
     * Get output patterns
     */
    public function getOutputPatterns(): array
    {
        return $this->output_patterns ?? [];
    }
    
    /**
     * Get frame elements with their roles
     */
    public function getFrameElements(): array
    {
        return $this->frame_elements ?? [];
    }
    
    /**
     * Get semantic roles mapping
     */
    public function getSemanticRoles(): array
    {
        return $this->semantic_roles ?? [];
    }
    
    /**
     * Check if frame is defeasible (can have exceptions)
     */
    public function isDefeasible(): bool
    {
        return $this->defeasible === true;
    }
    
    /**
     * Get frame confidence level
     */
    public function getConfidence(): float
    {
        return $this->confidence ?? 0.7;
    }
    
    /**
     * Get usage statistics
     */
    public function getUsageCount(): int
    {
        return $this->usage_count ?? 0;
    }
    
    /**
     * Get success rate
     */
    public function getSuccessRate(): float
    {
        return $this->success_rate ?? 0.0;
    }
    
    /**
     * Check if frame can be activated with given input
     */
    public function canActivate(array $input, array $context = []): bool
    {
        $conditions = $this->getActivationConditions();
        
        if (empty($conditions)) {
            return true; // No specific conditions
        }
        
        foreach ($conditions as $condition) {
            if (!$this->checkCondition($condition, $input, $context)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check a specific activation condition
     */
    protected function checkCondition(array $condition, array $input, array $context): bool
    {
        $type = $condition['type'] ?? 'presence';
        
        switch ($type) {
            case 'presence':
                return $this->checkPresenceCondition($condition, $input);
                
            case 'value_match':
                return $this->checkValueMatchCondition($condition, $input);
                
            case 'context_match':
                return $this->checkContextMatchCondition($condition, $context);
                
            case 'semantic_role':
                return $this->checkSemanticRoleCondition($condition, $input);
                
            default:
                return true;
        }
    }
    
    /**
     * Check presence condition
     */
    protected function checkPresenceCondition(array $condition, array $input): bool
    {
        $required = $condition['required'] ?? [];
        
        foreach ($required as $key) {
            if (!isset($input[$key])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check value match condition
     */
    protected function checkValueMatchCondition(array $condition, array $input): bool
    {
        $key = $condition['key'] ?? null;
        $expected = $condition['value'] ?? null;
        
        if (!$key || !isset($input[$key])) {
            return false;
        }
        
        return $input[$key] === $expected;
    }
    
    /**
     * Check context match condition
     */
    protected function checkContextMatchCondition(array $condition, array $context): bool
    {
        $key = $condition['key'] ?? null;
        $expected = $condition['value'] ?? null;
        
        if (!$key || !isset($context[$key])) {
            return false;
        }
        
        return $context[$key] === $expected;
    }
    
    /**
     * Check semantic role condition
     */
    protected function checkSemanticRoleCondition(array $condition, array $input): bool
    {
        $role = $condition['role'] ?? null;
        $entity = $condition['entity'] ?? null;
        
        if (!$role || !$entity) {
            return false;
        }
        
        $roles = $this->getSemanticRoles();
        
        return isset($roles[$role]) && isset($input[$entity]);
    }
    
    /**
     * Generate frame instance bindings from input
     */
    public function generateBindings(array $input, array $context = []): array
    {
        $bindings = [];
        $elements = $this->getFrameElements();
        
        foreach ($elements as $elementName => $elementInfo) {
            $binding = $this->generateElementBinding($elementName, $elementInfo, $input, $context);
            if ($binding !== null) {
                $bindings[$elementName] = $binding;
            }
        }
        
        return $bindings;
    }
    
    /**
     * Generate binding for a specific frame element
     */
    protected function generateElementBinding(string $elementName, array $elementInfo, array $input, array $context)
    {
        // Try direct mapping first
        if (isset($input[$elementName])) {
            return $input[$elementName];
        }
        
        // Try semantic role mapping
        $semanticRoles = $this->getSemanticRoles();
        if (isset($semanticRoles[$elementName])) {
            $sourceKey = $semanticRoles[$elementName];
            if (isset($input[$sourceKey])) {
                return $input[$sourceKey];
            }
        }
        
        // Try context mapping
        if (isset($context[$elementName])) {
            return $context[$elementName];
        }
        
        // Try inference based on element type
        $elementType = $elementInfo['type'] ?? 'unknown';
        return $this->inferElementValue($elementName, $elementType, $input, $context);
    }
    
    /**
     * Infer element value based on type and available data
     */
    protected function inferElementValue(string $elementName, string $elementType, array $input, array $context)
    {
        switch ($elementType) {
            case 'agent':
                return $input['agent'] ?? $input['person'] ?? $context['default_agent'] ?? null;
                
            case 'goal':
                return $input['goal'] ?? $input['objective'] ?? $input['intention'] ?? null;
                
            case 'belief':
                return $input['belief'] ?? $input['belief_state'] ?? null;
                
            case 'emotion':
                return $input['emotion'] ?? $input['emotional_state'] ?? null;
                
            case 'action':
                return $input['action'] ?? $input['activity'] ?? null;
                
            case 'object':
                return $input['object'] ?? $input['entity'] ?? null;
                
            default:
                return null;
        }
    }
    
    /**
     * Validate frame bindings against constraints
     */
    public function validateBindings(array $bindings): array
    {
        $errors = [];
        $constraints = $this->getConstraints();
        
        foreach ($constraints as $constraint) {
            $error = $this->checkConstraint($constraint, $bindings);
            if ($error) {
                $errors[] = $error;
            }
        }
        
        return $errors;
    }
    
    /**
     * Check a specific constraint
     */
    protected function checkConstraint(array $constraint, array $bindings): ?string
    {
        $type = $constraint['type'] ?? 'unknown';
        
        switch ($type) {
            case 'required_element':
                return $this->checkRequiredElement($constraint, $bindings);
                
            case 'mutual_exclusion':
                return $this->checkMutualExclusion($constraint, $bindings);
                
            case 'dependency':
                return $this->checkDependency($constraint, $bindings);
                
            case 'type_constraint':
                return $this->checkTypeConstraint($constraint, $bindings);
                
            default:
                return null;
        }
    }
    
    /**
     * Check required element constraint
     */
    protected function checkRequiredElement(array $constraint, array $bindings): ?string
    {
        $element = $constraint['element'] ?? null;
        
        if ($element && !isset($bindings[$element])) {
            return "Required element '{$element}' is missing";
        }
        
        return null;
    }
    
    /**
     * Check mutual exclusion constraint
     */
    protected function checkMutualExclusion(array $constraint, array $bindings): ?string
    {
        $elements = $constraint['elements'] ?? [];
        $present = [];
        
        foreach ($elements as $element) {
            if (isset($bindings[$element])) {
                $present[] = $element;
            }
        }
        
        if (count($present) > 1) {
            return "Mutually exclusive elements present: " . implode(', ', $present);
        }
        
        return null;
    }
    
    /**
     * Check dependency constraint
     */
    protected function checkDependency(array $constraint, array $bindings): ?string
    {
        $dependent = $constraint['dependent'] ?? null;
        $requires = $constraint['requires'] ?? null;
        
        if ($dependent && $requires && isset($bindings[$dependent]) && !isset($bindings[$requires])) {
            return "Element '{$dependent}' requires '{$requires}' to be present";
        }
        
        return null;
    }
    
    /**
     * Check type constraint
     */
    protected function checkTypeConstraint(array $constraint, array $bindings): ?string
    {
        $element = $constraint['element'] ?? null;
        $expectedType = $constraint['expected_type'] ?? null;
        
        if ($element && $expectedType && isset($bindings[$element])) {
            $value = $bindings[$element];
            if (!$this->matchesType($value, $expectedType)) {
                return "Element '{$element}' does not match expected type '{$expectedType}'";
            }
        }
        
        return null;
    }
    
    /**
     * Check if value matches expected type
     */
    protected function matchesType($value, string $expectedType): bool
    {
        switch ($expectedType) {
            case 'string':
                return is_string($value);
            case 'number':
                return is_numeric($value);
            case 'boolean':
                return is_bool($value);
            case 'array':
                return is_array($value);
            case 'person':
                return is_string($value) && !empty($value);
            case 'emotion':
                return is_string($value) && in_array(strtolower($value), 
                    ['happy', 'sad', 'angry', 'afraid', 'surprised', 'disgusted']);
            default:
                return true; // Unknown types pass by default
        }
    }
    
    /**
     * Record successful frame usage
     */
    public function recordSuccess(): void
    {
        $this->usage_count = $this->getUsageCount() + 1;
        $currentSuccesses = $this->getSuccessRate() * ($this->getUsageCount() - 1);
        $this->success_rate = ($currentSuccesses + 1) / $this->getUsageCount();
    }
    
    /**
     * Record failed frame usage
     */
    public function recordFailure(): void
    {
        $this->usage_count = $this->getUsageCount() + 1;
        $currentSuccesses = $this->getSuccessRate() * ($this->getUsageCount() - 1);
        $this->success_rate = $currentSuccesses / $this->getUsageCount();
    }
    
    /**
     * Get frame complexity score
     */
    public function getComplexityScore(): int
    {
        $elements = count($this->getElements());
        $constraints = count($this->getConstraints());
        $conditions = count($this->getActivationConditions());
        
        return match($this->complexity) {
            'simple' => 1,
            'moderate' => 2,
            'complex' => 3,
            default => min(3, max(1, ($elements + $constraints + $conditions) / 3))
        };
    }
    
    /**
     * Get processing requirements
     */
    public function getProcessingRequirements(): array
    {
        return $this->processing_requirements ?? [
            'memory' => 'normal',
            'processing_time' => 'normal',
            'agent_services' => [],
            'dependencies' => []
        ];
    }
    
    /**
     * Generate frame summary
     */
    public function getSummary(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->getFrameType(),
            'domain' => $this->domain,
            'complexity' => $this->complexity,
            'elements_count' => count($this->getElements()),
            'constraints_count' => count($this->getConstraints()),
            'defeasible' => $this->isDefeasible(),
            'confidence' => $this->getConfidence(),
            'usage_count' => $this->getUsageCount(),
            'success_rate' => $this->getSuccessRate(),
            'based_on_axiom' => $this->based_on_axiom
        ];
    }
    
    /**
     * Validate frame structure
     */
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->name)) {
            $errors[] = 'name is required';
        }
        
        if (empty($this->frame_type)) {
            $errors[] = 'frame_type is required';
        }
        
        if ($this->confidence !== null && ($this->confidence < 0.0 || $this->confidence > 1.0)) {
            $errors[] = 'confidence must be between 0.0 and 1.0';
        }
        
        if ($this->success_rate !== null && ($this->success_rate < 0.0 || $this->success_rate > 1.0)) {
            $errors[] = 'success_rate must be between 0.0 and 1.0';
        }
        
        if ($this->usage_count !== null && $this->usage_count < 0) {
            $errors[] = 'usage_count cannot be negative';
        }
        
        return $errors;
    }
    
    /**
     * Check if frame is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }
}