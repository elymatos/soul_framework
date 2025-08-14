<?php

namespace App\Domain\BackgroundTheories;

use Illuminate\Support\Collection;

/**
 * Abstract base class for converting FOL axioms to executable procedures
 * 
 * Each axiom from Gordon & Hobbs' Background Theories gets its own executor
 * that implements the logical rules as imperative PHP code.
 */
abstract class BackgroundAxiomExecutor
{
    protected string $axiomId;
    protected string $description;
    protected array $predicatesUsed;
    protected string $folFormula;
    protected string $complexity;
    
    /**
     * Constructor for axiom executors
     */
    public function __construct(
        string $axiomId,
        string $description,
        array $predicatesUsed = [],
        string $folFormula = '',
        string $complexity = 'unknown'
    ) {
        $this->axiomId = $axiomId;
        $this->description = $description;
        $this->predicatesUsed = $predicatesUsed;
        $this->folFormula = $folFormula;
        $this->complexity = $complexity;
    }

    // Getters
    public function getAxiomId(): string
    {
        return $this->axiomId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPredicatesUsed(): array
    {
        return $this->predicatesUsed;
    }

    public function getFolFormula(): string
    {
        return $this->folFormula;
    }

    public function getComplexity(): string
    {
        return $this->complexity;
    }

    /**
     * Main execution method - converts FOL axiom to imperative code
     * 
     * @param BackgroundReasoningContext $context The reasoning context
     * @return Collection Collection of new predicates/entities created
     */
    abstract public function execute(BackgroundReasoningContext $context): Collection;

    /**
     * Check if this axiom is applicable given the current context
     * 
     * @param BackgroundReasoningContext $context The reasoning context
     * @return bool True if the axiom should be executed
     */
    public function isApplicable(BackgroundReasoningContext $context): bool
    {
        // Default implementation: check if required predicates exist
        foreach ($this->predicatesUsed as $predicateName) {
            if ($context->hasPredicatesByName($predicateName)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get execution priority (lower number = higher priority)
     * 
     * @return int Priority level (1-10, where 1 is highest priority)
     */
    public function getPriority(): int
    {
        // Default priority based on complexity
        return match ($this->complexity) {
            'simple' => 1,
            'moderate' => 2,
            'complex' => 3,
            default => 5
        };
    }

    /**
     * Pre-execution validation
     * 
     * @param BackgroundReasoningContext $context The reasoning context
     * @return array Array of validation errors (empty if valid)
     */
    public function validate(BackgroundReasoningContext $context): array
    {
        $errors = [];

        // Check if context has the required capabilities
        if (!$context->canCreateEntities()) {
            $errors[] = "Context cannot create entities";
        }

        if (!$context->canCreatePredicates()) {
            $errors[] = "Context cannot create predicates";
        }

        return $errors;
    }

    /**
     * Post-execution cleanup and reporting
     * 
     * @param Collection $results Results from execution
     * @param BackgroundReasoningContext $context The reasoning context
     * @return array Execution report
     */
    public function postExecution(Collection $results, BackgroundReasoningContext $context): array
    {
        return [
            'axiom_id' => $this->axiomId,
            'description' => $this->description,
            'predicates_created' => $results->whereInstanceOf(BackgroundPredicate::class)->count(),
            'entities_created' => $results->whereInstanceOf(BackgroundEntity::class)->count(),
            'total_results' => $results->count(),
            'execution_timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Helper method to create execution trace
     * 
     * @param string $step Description of the step
     * @param mixed $data Associated data
     * @return array Trace entry
     */
    protected function trace(string $step, mixed $data = null): array
    {
        return [
            'axiom_id' => $this->axiomId,
            'step' => $step,
            'data' => $data,
            'timestamp' => microtime(true),
        ];
    }

    /**
     * Helper method to create success response
     * 
     * @param Collection $results The results collection
     * @param array $trace Execution trace
     * @return array Success response
     */
    protected function createSuccessResponse(Collection $results, array $trace = []): array
    {
        return [
            'success' => true,
            'results' => $results,
            'trace' => $trace,
            'axiom_id' => $this->axiomId,
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Helper method to create error response
     * 
     * @param string $error Error message
     * @param array $trace Execution trace
     * @return array Error response
     */
    protected function createErrorResponse(string $error, array $trace = []): array
    {
        return [
            'success' => false,
            'error' => $error,
            'trace' => $trace,
            'axiom_id' => $this->axiomId,
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Get human-readable string representation
     */
    public function __toString(): string
    {
        return "Axiom {$this->axiomId}: {$this->description}";
    }
}