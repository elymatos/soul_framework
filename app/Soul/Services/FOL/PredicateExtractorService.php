<?php

namespace App\Soul\Services\FOL;

use Illuminate\Support\Facades\Log;

/**
 * PredicateExtractorService - Extract predicates, variables, and quantifiers from FOL expressions
 *
 * This service parses First-Order Logic expressions to extract predicates,
 * variables, quantifiers, and other logical components for analysis.
 */
class PredicateExtractorService
{
    /**
     * Extract predicates from FOL expression
     */
    public function extractPredicatesFromFOL(string $fol): array
    {
        $predicates = [];
        
        // Clean up the FOL expression
        $cleanFol = $this->cleanFOLExpression($fol);
        
        // Pattern to match predicates: predicate_name(args)
        $predicatePattern = '/\b([A-Za-z][A-Za-z0-9_]*)\s*\(/';
        
        if (preg_match_all($predicatePattern, $cleanFol, $matches)) {
            $predicates = array_unique($matches[1]);
        }
        
        // Also look for predicates without parentheses (e.g., unary predicates)
        $unaryPattern = '/\b([A-Z][A-Za-z0-9_]*)\s*(?![(\s]*[a-z])/';
        if (preg_match_all($unaryPattern, $cleanFol, $matches)) {
            $predicates = array_unique(array_merge($predicates, $matches[1]));
        }
        
        // Filter out logical operators and quantifiers
        $logicalOperators = [
            'forall', 'exists', 'and', 'or', 'not', 'implies', 'iff',
            'true', 'false', 'ALL', 'EXISTS', 'AND', 'OR', 'NOT',
            'IMPLIES', 'IFF', 'TRUE', 'FALSE'
        ];
        
        $predicates = array_filter($predicates, function($predicate) use ($logicalOperators) {
            return !in_array(strtolower($predicate), $logicalOperators);
        });
        
        // Sort and return as indexed array
        sort($predicates);
        
        Log::debug("FOL PredicateExtractor: Extracted predicates", [
            'fol' => $fol,
            'predicates' => $predicates,
            'count' => count($predicates)
        ]);
        
        return array_values($predicates);
    }
    
    /**
     * Extract variables from FOL expression
     */
    public function extractVariablesFromFOL(string $fol): array
    {
        $variables = [];
        
        // Clean up the FOL expression
        $cleanFol = $this->cleanFOLExpression($fol);
        
        // Pattern to match variables (lowercase letters, optionally followed by digits)
        $variablePattern = '/\b([a-z][a-z0-9]*)\b/';
        
        if (preg_match_all($variablePattern, $cleanFol, $matches)) {
            $variables = array_unique($matches[1]);
        }
        
        // Filter out logical operators and common words
        $filterOut = [
            'and', 'or', 'not', 'if', 'then', 'else', 'for', 'all', 'exists',
            'true', 'false', 'in', 'is', 'of', 'to', 'the', 'a', 'an'
        ];
        
        $variables = array_filter($variables, function($var) use ($filterOut) {
            return !in_array(strtolower($var), $filterOut);
        });
        
        // Sort and return as indexed array
        sort($variables);
        
        return array_values($variables);
    }
    
    /**
     * Extract quantifiers from FOL expression
     */
    public function extractQuantifiersFromFOL(string $fol): array
    {
        $quantifiers = [];
        
        // Clean up the FOL expression
        $cleanFol = $this->cleanFOLExpression($fol);
        
        // Patterns for different quantifier notations
        $patterns = [
            // Universal quantifiers
            'universal' => [
                '/\b(forall|∀|ALL)\s+([a-z][a-z0-9]*)/i',
                '/\b(for\s+all)\s+([a-z][a-z0-9]*)/i'
            ],
            // Existential quantifiers
            'existential' => [
                '/\b(exists|∃|EXISTS)\s+([a-z][a-z0-9]*)/i',
                '/\b(there\s+exists?)\s+([a-z][a-z0-9]*)/i'
            ]
        ];
        
        foreach ($patterns as $type => $patternList) {
            foreach ($patternList as $pattern) {
                if (preg_match_all($pattern, $cleanFol, $matches)) {
                    for ($i = 0; $i < count($matches[1]); $i++) {
                        $quantifiers[] = [
                            'type' => $type,
                            'quantifier' => trim($matches[1][$i]),
                            'variable' => trim($matches[2][$i]),
                            'position' => strpos($cleanFol, $matches[0][$i])
                        ];
                    }
                }
            }
        }
        
        // Sort by position in the expression
        usort($quantifiers, function($a, $b) {
            return $a['position'] <=> $b['position'];
        });
        
        // Remove position information for clean output
        return array_map(function($q) {
            unset($q['position']);
            return $q;
        }, $quantifiers);
    }
    
    /**
     * Extract logical operators from FOL expression
     */
    public function extractLogicalOperators(string $fol): array
    {
        $operators = [];
        
        // Clean up the FOL expression
        $cleanFol = $this->cleanFOLExpression($fol);
        
        // Patterns for different operators
        $operatorPatterns = [
            'conjunction' => ['/\b(and|∧|&)\b/i', '/\s+&\s+/'],
            'disjunction' => ['/\b(or|∨|\|)\b/i', '/\s+\|\s+/'],
            'negation' => ['/\b(not|¬|~)\b/i', '/¬/', '/~/'],
            'implication' => ['/\b(implies?|→|->)\b/i', '/→/', '/->/'],
            'biconditional' => ['/\b(iff|↔|<->)\b/i', '/↔/', '/<->/'],
            'equality' => ['/\b(equals?|=)\b/i', '/=/']
        ];
        
        foreach ($operatorPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $cleanFol, $matches)) {
                    foreach ($matches[0] as $match) {
                        $operators[] = [
                            'type' => $type,
                            'operator' => trim($match)
                        ];
                    }
                }
            }
        }
        
        return array_unique($operators, SORT_REGULAR);
    }
    
    /**
     * Extract function symbols from FOL expression
     */
    public function extractFunctionSymbols(string $fol): array
    {
        $functions = [];
        
        // Clean up the FOL expression
        $cleanFol = $this->cleanFOLExpression($fol);
        
        // Pattern to match function symbols: function_name(args) where args contains variables
        $functionPattern = '/\b([a-zA-Z][a-zA-Z0-9_]*)\s*\([^)]*[a-z][^)]*\)/';
        
        if (preg_match_all($functionPattern, $cleanFol, $matches)) {
            foreach ($matches[1] as $function) {
                // Determine if this is likely a function vs a predicate
                // Functions typically return values, predicates return truth values
                if ($this->isFunctionSymbol($function, $cleanFol)) {
                    $functions[] = $function;
                }
            }
        }
        
        return array_unique($functions);
    }
    
    /**
     * Extract constants from FOL expression
     */
    public function extractConstants(string $fol): array
    {
        $constants = [];
        
        // Clean up the FOL expression
        $cleanFol = $this->cleanFOLExpression($fol);
        
        // Pattern to match constants (uppercase identifiers or quoted strings)
        $constantPatterns = [
            '/\b([A-Z][A-Z0-9_]*)\b/',  // UPPERCASE_CONSTANTS
            '/"([^"]+)"/',               // "quoted strings"
            "/\'([^']+)\'/",             // 'quoted strings'
            '/\b(\d+(?:\.\d+)?)\b/'      // numeric constants
        ];
        
        foreach ($constantPatterns as $pattern) {
            if (preg_match_all($pattern, $cleanFol, $matches)) {
                $constants = array_merge($constants, $matches[1]);
            }
        }
        
        // Filter out predicates and logical operators
        $predicates = $this->extractPredicatesFromFOL($fol);
        $logicalOperators = ['TRUE', 'FALSE', 'AND', 'OR', 'NOT', 'IMPLIES', 'IFF'];
        
        $constants = array_filter($constants, function($const) use ($predicates, $logicalOperators) {
            return !in_array($const, $predicates) && !in_array($const, $logicalOperators);
        });
        
        return array_unique($constants);
    }
    
    /**
     * Get complete structural analysis of FOL expression
     */
    public function analyzeStructure(string $fol): array
    {
        return [
            'predicates' => $this->extractPredicatesFromFOL($fol),
            'variables' => $this->extractVariablesFromFOL($fol),
            'quantifiers' => $this->extractQuantifiersFromFOL($fol),
            'operators' => $this->extractLogicalOperators($fol),
            'functions' => $this->extractFunctionSymbols($fol),
            'constants' => $this->extractConstants($fol),
            'complexity_metrics' => $this->calculateComplexityMetrics($fol)
        ];
    }
    
    /**
     * Calculate complexity metrics for FOL expression
     */
    protected function calculateComplexityMetrics(string $fol): array
    {
        $cleanFol = $this->cleanFOLExpression($fol);
        
        return [
            'length' => strlen($cleanFol),
            'quantifier_depth' => $this->calculateQuantifierDepth($cleanFol),
            'logical_depth' => $this->calculateLogicalDepth($cleanFol),
            'predicate_count' => count($this->extractPredicatesFromFOL($fol)),
            'variable_count' => count($this->extractVariablesFromFOL($fol)),
            'operator_count' => count($this->extractLogicalOperators($fol))
        ];
    }
    
    /**
     * Clean FOL expression for parsing
     */
    protected function cleanFOLExpression(string $fol): string
    {
        // Remove extra whitespace
        $clean = preg_replace('/\s+/', ' ', trim($fol));
        
        // Normalize Unicode symbols to ASCII equivalents
        $replacements = [
            '∀' => 'forall',
            '∃' => 'exists',
            '∧' => 'and',
            '∨' => 'or',
            '¬' => 'not',
            '→' => 'implies',
            '↔' => 'iff',
            '⊃' => 'implies',
            '≡' => 'iff'
        ];
        
        foreach ($replacements as $unicode => $ascii) {
            $clean = str_replace($unicode, $ascii, $clean);
        }
        
        return $clean;
    }
    
    /**
     * Determine if a symbol is likely a function vs predicate
     */
    protected function isFunctionSymbol(string $symbol, string $fol): bool
    {
        // Heuristics to distinguish functions from predicates
        // Functions are typically used as arguments to other predicates
        
        // Check if symbol appears as argument to other predicates
        $asArgPattern = '/\b[A-Za-z][A-Za-z0-9_]*\s*\([^)]*\b' . preg_quote($symbol, '/') . '\s*\([^)]*\)[^)]*\)/';
        if (preg_match($asArgPattern, $fol)) {
            return true;
        }
        
        // Common function names
        $functionNames = ['successor', 'plus', 'times', 'father', 'mother', 'age', 'height', 'weight'];
        if (in_array(strtolower($symbol), $functionNames)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Calculate quantifier nesting depth
     */
    protected function calculateQuantifierDepth(string $fol): int
    {
        $depth = 0;
        $maxDepth = 0;
        
        // Simple approximation: count nested quantifier patterns
        $quantifierPattern = '/\b(?:forall|exists|∀|∃)\b/i';
        $tokens = preg_split('/(\s+|\(|\))/', $fol, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        foreach ($tokens as $token) {
            if (preg_match($quantifierPattern, $token)) {
                $depth++;
                $maxDepth = max($maxDepth, $depth);
            }
            // This is a simplified approach; real implementation would need proper parsing
        }
        
        return $maxDepth;
    }
    
    /**
     * Calculate logical operator nesting depth
     */
    protected function calculateLogicalDepth(string $fol): int
    {
        $depth = 0;
        $maxDepth = 0;
        
        for ($i = 0; $i < strlen($fol); $i++) {
            if ($fol[$i] === '(') {
                $depth++;
                $maxDepth = max($maxDepth, $depth);
            } elseif ($fol[$i] === ')') {
                $depth--;
            }
        }
        
        return $maxDepth;
    }
    
    /**
     * Get extractor statistics
     */
    public function getExtractorStatistics(): array
    {
        return [
            'extractor_version' => '1.0',
            'supported_notations' => ['ASCII', 'Unicode'],
            'supported_quantifiers' => ['universal', 'existential'],
            'supported_operators' => [
                'conjunction', 'disjunction', 'negation', 
                'implication', 'biconditional', 'equality'
            ]
        ];
    }
}