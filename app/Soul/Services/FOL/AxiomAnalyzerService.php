<?php

namespace App\Soul\Services\FOL;

use Illuminate\Support\Facades\Log;

/**
 * AxiomAnalyzerService - Analyze FOL axioms for complexity, patterns, and characteristics
 *
 * This service analyzes parsed axiom data to determine complexity levels,
 * identify patterns, and extract characteristics needed for cognitive processing.
 */
class AxiomAnalyzerService
{
    /**
     * Analyze an axiom to determine its characteristics
     */
    public function analyzeAxiom(array $axiomData, array $predicates): array
    {
        $fol = $axiomData['fol'] ?? '';
        $english = $axiomData['english'] ?? '';
        
        return [
            'complexity' => $this->analyzeComplexity($fol, $predicates, $axiomData),
            'pattern' => $this->identifyPattern($fol, $predicates, $english),
            'defeasible' => $this->isDefeasible($fol, $english, $axiomData),
            'reified' => $this->isReified($fol, $predicates),
            'domain' => $this->inferDomain($predicates, $axiomData, $english)
        ];
    }
    
    /**
     * Analyze complexity of axiom
     */
    protected function analyzeComplexity(string $fol, array $predicates, array $axiomData): string
    {
        $complexityScore = 0;
        
        // Count quantifiers
        $quantifierCount = preg_match_all('/(?:forall|exists|∀|∃)/', $fol, $matches);
        $complexityScore += $quantifierCount * 2;
        
        // Count predicates
        $predicateCount = count($predicates);
        $complexityScore += $predicateCount;
        
        // Count variables (approximation)
        $variableCount = preg_match_all('/\b[a-z]\d*\b/', $fol, $matches);
        $complexityScore += $variableCount;
        
        // Count logical operators
        $operatorCount = preg_match_all('/(?:&|∧|\||∨|→|->|¬|~|↔|<->)/', $fol, $matches);
        $complexityScore += $operatorCount;
        
        // Count nested parentheses levels
        $maxNesting = $this->calculateMaxNesting($fol);
        $complexityScore += $maxNesting * 2;
        
        // Check for temporal operators
        if (preg_match('/(?:before|after|during|until|since)/', $fol . ' ' . ($axiomData['english'] ?? ''))) {
            $complexityScore += 3;
        }
        
        // Check for modal operators
        if (preg_match('/(?:believe|know|want|possible|necessary)/', $fol . ' ' . ($axiomData['english'] ?? ''))) {
            $complexityScore += 2;
        }
        
        // Classify based on score
        if ($complexityScore <= 5) {
            return 'simple';
        } elseif ($complexityScore <= 12) {
            return 'moderate';
        } else {
            return 'complex';
        }
    }
    
    /**
     * Calculate maximum nesting level of parentheses
     */
    protected function calculateMaxNesting(string $fol): int
    {
        $maxLevel = 0;
        $currentLevel = 0;
        
        for ($i = 0; $i < strlen($fol); $i++) {
            if ($fol[$i] === '(') {
                $currentLevel++;
                $maxLevel = max($maxLevel, $currentLevel);
            } elseif ($fol[$i] === ')') {
                $currentLevel--;
            }
        }
        
        return $maxLevel;
    }
    
    /**
     * Identify axiom patterns for agent generation
     */
    protected function identifyPattern(string $fol, array $predicates, string $english): string
    {
        $lowerFol = strtolower($fol);
        $lowerEnglish = strtolower($english);
        $combinedText = $lowerFol . ' ' . $lowerEnglish;
        
        // Goal causation patterns
        if ($this->containsAny($predicates, ['goal', 'cause', 'believe']) &&
            preg_match('/goal.*cause|cause.*goal/', $combinedText)) {
            return 'goal_causation';
        }
        
        // Belief logic patterns
        if ($this->containsAny($predicates, ['believe', 'know', 'perceive']) &&
            preg_match('/believe|perception|knowledge/', $combinedText)) {
            return 'belief_logic';
        }
        
        // Emotion causation patterns
        if ($this->containsAny($predicates, ['happy', 'sad', 'angry', 'afraid', 'emotion']) &&
            preg_match('/emotion|feeling|mood/', $combinedText)) {
            return 'emotion_causation';
        }
        
        // Simple inheritance patterns
        if (preg_match('/^forall\s+\w+\s*\(.*\)\s*->.*$/', $lowerFol) && 
            count($predicates) <= 2) {
            return 'simple_inheritance';
        }
        
        // Defeasible rule patterns
        if ($this->isDefeasible($fol, $english, []) && 
            preg_match('/unless|except|normally|typically/', $combinedText)) {
            return 'defeasible_rule';
        }
        
        // Causal chain patterns
        if ($this->containsAny($predicates, ['cause', 'effect', 'result']) &&
            preg_match('/cause.*cause|chain|sequence/', $combinedText)) {
            return 'causal_chain';
        }
        
        // Temporal sequence patterns
        if ($this->containsAny($predicates, ['before', 'after', 'during']) ||
            preg_match('/before|after|during|sequence|timeline/', $combinedText)) {
            return 'temporal_sequence';
        }
        
        // Social interaction patterns
        if ($this->containsAny($predicates, ['give', 'tell', 'show', 'interact']) &&
            preg_match('/social|interaction|communicate/', $combinedText)) {
            return 'social_interaction';
        }
        
        // Mental state change patterns
        if ($this->containsAny($predicates, ['become', 'change', 'state']) &&
            preg_match('/mental.*state|state.*change/', $combinedText)) {
            return 'mental_state_change';
        }
        
        // Perception-action patterns
        if ($this->containsAny($predicates, ['perceive', 'see', 'hear']) &&
            $this->containsAny($predicates, ['do', 'action', 'move'])) {
            return 'perception_action';
        }
        
        // Default pattern
        return 'general_logic';
    }
    
    /**
     * Check if axiom is defeasible (has exceptions)
     */
    protected function isDefeasible(string $fol, string $english, array $axiomData): bool
    {
        $combinedText = strtolower($fol . ' ' . $english);
        
        // Look for explicit defeasibility markers
        $defeasibleMarkers = [
            'unless', 'except', 'normally', 'typically', 'usually', 'generally',
            'by default', 'tends to', 'likely', 'probably', 'often',
            'etc', 'et cetera', 'other things being equal'
        ];
        
        foreach ($defeasibleMarkers as $marker) {
            if (str_contains($combinedText, $marker)) {
                return true;
            }
        }
        
        // Check for probability or uncertainty expressions
        if (preg_match('/\b(?:\d+%|probability|likely|probable|chance)\b/', $combinedText)) {
            return true;
        }
        
        // Check axiom data for defeasibility flag
        if (isset($axiomData['defeasible']) && $axiomData['defeasible']) {
            return true;
        }
        
        // Psychology domain rules are often defeasible
        if (preg_match('/psychology|emotion|belief|social/', $combinedText)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if axiom uses reification
     */
    protected function isReified(string $fol, array $predicates): bool
    {
        // Look for event/state reification patterns
        $reificationMarkers = ['event', 'state', 'situation', 'fact'];
        
        foreach ($reificationMarkers as $marker) {
            if ($this->containsAny($predicates, [$marker])) {
                return true;
            }
        }
        
        // Look for reification patterns in FOL
        if (preg_match('/exists\s+\w+\s*\(.*event\(.*\)|.*state\(.*\).*\)/', strtolower($fol))) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Infer the domain of the axiom
     */
    protected function inferDomain(array $predicates, array $axiomData, string $english): string
    {
        $combinedText = strtolower(implode(' ', $predicates) . ' ' . $english);
        
        // Psychology domain
        $psychologyTerms = [
            'believe', 'know', 'think', 'want', 'goal', 'emotion', 'happy', 'sad',
            'angry', 'afraid', 'memory', 'remember', 'perceive', 'feel', 'mood'
        ];
        if ($this->containsAnyString($combinedText, $psychologyTerms)) {
            return 'psychology';
        }
        
        // Physics domain
        $physicsTerms = [
            'move', 'motion', 'force', 'mass', 'velocity', 'acceleration',
            'position', 'location', 'distance', 'time', 'space'
        ];
        if ($this->containsAnyString($combinedText, $physicsTerms)) {
            return 'physics';
        }
        
        // Mathematics domain
        $mathTerms = [
            'number', 'equal', 'greater', 'less', 'plus', 'minus',
            'multiply', 'divide', 'sum', 'count', 'measure'
        ];
        if ($this->containsAnyString($combinedText, $mathTerms)) {
            return 'mathematics';
        }
        
        // Social domain
        $socialTerms = [
            'person', 'people', 'society', 'group', 'interaction', 'communication',
            'relationship', 'friend', 'family', 'community', 'social'
        ];
        if ($this->containsAnyString($combinedText, $socialTerms)) {
            return 'social';
        }
        
        // Temporal domain
        $temporalTerms = [
            'time', 'before', 'after', 'during', 'when', 'while',
            'until', 'since', 'temporal', 'sequence', 'order'
        ];
        if ($this->containsAnyString($combinedText, $temporalTerms)) {
            return 'temporal';
        }
        
        // Spatial domain
        $spatialTerms = [
            'space', 'location', 'position', 'near', 'far', 'above', 'below',
            'left', 'right', 'inside', 'outside', 'between', 'spatial'
        ];
        if ($this->containsAnyString($combinedText, $spatialTerms)) {
            return 'spatial';
        }
        
        // Linguistics domain
        $linguisticTerms = [
            'language', 'word', 'sentence', 'meaning', 'semantic',
            'syntax', 'grammar', 'linguistic', 'communication'
        ];
        if ($this->containsAnyString($combinedText, $linguisticTerms)) {
            return 'linguistics';
        }
        
        // Check chapter information
        if (isset($axiomData['chapter_title'])) {
            $chapterTitle = strtolower($axiomData['chapter_title']);
            if (str_contains($chapterTitle, 'psychology') || str_contains($chapterTitle, 'mental')) {
                return 'psychology';
            }
            if (str_contains($chapterTitle, 'physics') || str_contains($chapterTitle, 'physical')) {
                return 'physics';
            }
            if (str_contains($chapterTitle, 'social') || str_contains($chapterTitle, 'society')) {
                return 'social';
            }
        }
        
        // Default domain
        return 'logic';
    }
    
    /**
     * Check if any of the given items exist in array
     */
    protected function containsAny(array $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (in_array(strtolower($needle), array_map('strtolower', $haystack))) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if text contains any of the given strings
     */
    protected function containsAnyString(string $text, array $strings): bool
    {
        foreach ($strings as $string) {
            if (str_contains($text, strtolower($string))) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get analysis statistics
     */
    public function getAnalysisStatistics(): array
    {
        return [
            'analyzer_version' => '1.0',
            'supported_patterns' => [
                'goal_causation', 'belief_logic', 'emotion_causation', 
                'simple_inheritance', 'defeasible_rule', 'causal_chain',
                'temporal_sequence', 'social_interaction', 'mental_state_change',
                'perception_action', 'general_logic'
            ],
            'supported_domains' => [
                'psychology', 'physics', 'mathematics', 'social', 
                'temporal', 'spatial', 'linguistics', 'logic'
            ],
            'complexity_levels' => ['simple', 'moderate', 'complex']
        ];
    }
}