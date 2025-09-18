# SOUL Framework Chapter Graphs

This directory contains individual graph representations for each chapter of the Formal Theory of Commonsense Psychology. Each graph is self-contained and shows the axioms, predicates, and variables used within that specific chapter.

## Available Chapters

| Chapter | Title | Axioms | Nodes | Links | File Size |
|---------|-------|--------|-------|-------|-----------|
| 5 | Eventualities and Their Structure | 21 | 49 | 145 | ~28KB |
| 6 | Traditional Set Theory | 22 | 50 | 194 | ~35KB |
| 7 | Substitution, Typical Elements, and Instances | 19 | 58 | 271 | ~45KB |
| 8 | Logic Reified | 13 | 36 | 113 | ~25KB |
| 9 | Functions and Sequences | 19 | 62 | 230 | ~42KB |
| 11 | Defeasibility | 14 | 33 | 81 | ~28KB |
| 12 | Scales | 38 | 107 | 516 | ~137KB |
| 13 | Arithmetic | 43 | 103 | 410 | ~116KB |
| 14 | Change of State | 13 | 43 | 149 | ~44KB |
| 17 | Event Structure | 16 | 55 | 211 | ~61KB |
| 18 | Space | 11 | 50 | 183 | ~52KB |
| 19 | Persons | 14 | 48 | 166 | ~48KB |
| 22 | Similarity Comparisons | 28 | 124 | 628 | ~98KB |
| 23 | Memory | 38 | 116 | 542 | ~85KB |
| 24 | Envisioning | 52 | 166 | 1057 | ~142KB |
| 25 | Explanation | 22 | 78 | 406 | ~71KB |
| 26 | Managing Expectations | 9 | 43 | 153 | ~38KB |
| 27 | Other-Agent Reasoning | 8 | 34 | 104 | ~29KB |
| 28 | Goals | 82 | 207 | 1063 | ~165KB |
| 29 | Goal Themes | 34 | 106 | 431 | ~78KB |
| 30 | Threats and Threat Detection | 23 | 98 | 389 | ~71KB |
| 31 | Plans | 53 | 170 | 813 | ~127KB |
| 32 | Goal Management | 20 | 88 | 370 | ~67KB |
| 33 | Execution Envisionment | 16 | 74 | 313 | ~57KB |
| 34 | Causes of Failure | 6 | 27 | 88 | ~22KB |
| 35 | Plan Elements | 12 | 50 | 171 | ~38KB |
| 36 | Planning Modalities | 11 | 54 | 220 | ~43KB |
| 37 | Planning Goals | 31 | 113 | 472 | ~83KB |
| 38 | Plan Construction | 26 | 110 | 538 | ~89KB |
| 39 | Plan Adaptation | 10 | 56 | 239 | ~48KB |
| 40 | Design | 13 | 64 | 281 | ~54KB |
| 41 | Decisions | 34 | 120 | 591 | ~97KB |
| 42 | Scheduling | 33 | 125 | 643 | ~102KB |
| 43 | Monitoring | 15 | 57 | 229 | ~45KB |
| 44 | Execution Modalities | 21 | 86 | 378 | ~65KB |
| 45 | Execution Control | 48 | 159 | 921 | ~123KB |
| 46 | Repetitive Execution | 17 | 62-74 | 328-374 | ~55-65KB |
| 47 | Mind–Body Interaction | 56 | 155 | 907 | ~121KB |
| 48 | Observation of Plan Executions | 30 | 112 | 543 | ~89KB |
| 49 | Emotions | 120 | 290 | 1677 | ~215KB |

## Graph Structure

Each chapter graph contains:

### Node Types
- **Axiom nodes**: Individual axioms with their complexity (simple/moderate/complex), pattern, and logical structure
- **Predicate nodes**: Predicates used in the chapter, classified by frequency (high/medium/low)
- **Variable nodes**: Frequently used variables (appearing in 2+ axioms within the chapter)

### Link Types
- **uses_predicate**: Axiom → Predicate (axiom uses this predicate)
- **has_variable**: Axiom → Variable (axiom contains this variable)
- **co_occurs**: Predicate ↔ Predicate (predicates appear together in axioms)

### Metadata
Each graph includes:
- Chapter information and statistics
- Complexity distribution
- Pattern analysis
- Top predicates by frequency
- Complete predicate frequency counts

## Usage

These graphs are designed for D3.js visualization. Each JSON file can be loaded individually to create focused, manageable visualizations of specific chapters rather than the overwhelming complete theory graph.

### Recommended Chapters for Starting Analysis

**Foundational Chapters:**
- Chapter 5: Eventualities (core event structure)
- Chapter 6: Set Theory (mathematical foundation)
- Chapter 8: Logic Reified (logical operators)

**Mid-complexity Examples:**
- Chapter 11: Defeasibility (manageable complexity with clear patterns)
- Chapter 19: Persons (cognitive agent concepts)
- Chapter 23: Memory (cognitive processes)

**Complex Examples:**
- Chapter 49: Emotions (largest chapter with 120 axioms)
- Chapter 28: Goals (82 axioms, complex goal structures)
- Chapter 24: Envisioning (52 axioms, complex reasoning)

Total: **1,128 axioms** across **41 processable chapters**