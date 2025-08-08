# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Overview

This is an academic research collection containing PDF files from Gordon & Hobbs (2017) on commonsense psychology and commonsense reasoning. The repository contains 50+ PDF chapters covering topics from basic commonsense psychology theory to practical applications in AI systems.

## Content Organization

The PDFs are numbered sequentially (001-049) and cover the following major themes:

**Part I: Foundations (001-004)**
- Commonsense Psychology and Psychology theory
- Commonsense Psychology and Computers  
- Formalizing Commonsense Psychology
- Commonsense Psychology and Language

**Part II: Core Concepts (005-020)**  
- Eventualities and their structure
- Logic, set theory, and mathematical foundations
- Functions, sequences, and composite entities
- Defeasibility, scales, arithmetic
- Change of state, causality, time
- Event structure, space, persons, modality

**Part III: Knowledge Management (021-048)**
- Memory, envisioning, explanation
- Goal management and planning
- Threat detection and plan construction
- Decision-making and execution
- Mind-body interaction and emotions

**Appendices**
- First-order logic reference (ape_a_firstorder-logic.pdf)
- References (references.pdf)

## Usage Notes

This is a research collection for studying commonsense reasoning in AI systems. The PDFs contain formal logical representations and theoretical frameworks that are referenced in AI research on commonsense knowledge representation.

When working with this collection:
- PDFs are read-only academic materials
- No build/test/lint commands are applicable
- Content is for research reference and citation
- Files follow academic naming conventions with sequential numbering

## Instructions to processing

# Complete Guide to Converting FOL Axioms to JSON

## Overview
This guide explains how to convert First-Order Logic (FOL) axioms from "A Formal Theory of Commonsense Psychology: How People Think People Think" by Gordon and Hobbs (2017) into structured JSON format for systematic analysis and processing.

## JSON Structure Template

### File-Level Structure
```json
{
  "metadata": {
    "title": "A Formal Theory of Commonsense Psychology: How People Think People Think",
    "authors": "Andrew S. Gordon and Jerry R. Hobbs", 
    "publisher": "Cambridge University Press",
    "year": 2017,
    "chapter": [NUMBER],
    "chapter_title": "[TITLE]",
    "extraction_date": "2025-01-08",
    "axiom_count": [COUNT],
    "description": "Chapter [N] axioms covering [BRIEF DESCRIPTION]",
    "notation": "First-order logic with [SPECIFIC FEATURES]"
  },
  "axioms": [...],
  "pattern_distribution": {...},
  "complexity_distribution": {...},
  "domain_distribution": {...},
  "predicate_frequency": {...},
  "conversion_notes": {...},
  "file_format_notes": {...}
}
```

### Individual Axiom Structure
```json
{
  "id": "[CHAPTER].[AXIOM_NUMBER]",
  "chapter": [NUMBER],
  "chapter_title": "[TITLE]",
  "section": "[SECTION]",
  "page": [PAGE_NUMBER],
  "axiom_number": "([ORIGINAL_NUMBER])",
  "title": "[DESCRIPTIVE_TITLE]",
  "fol": "[EXACT_FOL_EXPRESSION]",
  "english": "[NATURAL_LANGUAGE_EXPLANATION]",
  "complexity": "simple|moderate|complex",
  "pattern": "[PATTERN_CATEGORY]",
  "predicates": ["list", "of", "predicates"],
  "variables": ["list", "of", "variables"],
  "quantifiers": ["forall", "exists"],
  "defeasible": true|false,
  "reified": true|false,
  "domain": "background_theory|psychology|example"
}
```

## Field-by-Field Instructions

### Required Fields

**id**: Format as "CHAPTER.AXIOM_NUMBER" (e.g., "5.3", "11.12")

**fol**: Extract the exact FOL expression from the PDF
- Include all parentheses, operators, and spacing exactly as written
- Use proper logical operators: `forall`, `exists`, `if`, `iff`, `and`, `or`, `not`
- Preserve predicate names exactly (including apostrophes for reified predicates)

**english**: Extract or summarize the natural language explanation
- Use the book's explanation when provided
- Create clear summaries for complex axioms
- Focus on the intuitive meaning

**complexity**:
- **simple**: Basic constraints, single conditions, straightforward implications
- **moderate**: Multiple conditions, existential quantifications, moderate nesting
- **complex**: Deep nesting, multiple quantifiers, recursive definitions, complex logical structure

**pattern**: Categorize the type of axiom (see Pattern Categories below)

**predicates**: List all predicate names used (without arguments)

**variables**: List all variable names used

**quantifiers**: List quantifier types present ("forall", "exists")

**defeasible**: `true` if axiom contains `(etc)` predicate, `false` otherwise

**reified**: `true` if uses primed predicates (e.g., `give'`, `believe'`), `false` otherwise

**domain**:
- **background_theory**: Mathematical/logical foundations (Chapters 5-19)
- **psychology**: Commonsense psychology theories (Chapters 21-49)  
- **example**: Illustrative examples

## Pattern Categories

### Background Theory Patterns
- `type_constraint`: Argument type requirements
- `definition`: Definitional equivalences using `iff`
- `argument_structure`: Predicate argument definitions
- `set_operations`: Set theory operations
- `scale_operations`: Scale theory operations
- `logical_conjunction/negation/disjunction`: Reified logic operators
- `ordering_definition`: Less than, greater than relations
- `function_definition`: Mathematical function definitions
- `recursive_definition`: Self-referential definitions

### Psychology Patterns
- `defeasible_rule`: Rules with `(etc)` conditions
- `belief_logic`: Belief formation and reasoning
- `goal_reasoning`: Goal formation and pursuit
- `emotion_causation`: Emotional cause and effect
- `action_planning`: Planning and execution

### Special Patterns
- `axiom_schema`: Template axioms for multiple predicates
- `existence_claim`: Assertions of entity existence
- `inheritance`: Type subsumption relationships

## Complexity Guidelines

### Simple (6-15 lines typical)
- Single quantifier level
- Direct implications: `(forall (x) (if (P x) (Q x)))`
- Basic type constraints
- Simple definitions

### Moderate (15-25 lines typical)
- Multiple quantifiers
- Existential quantification in consequent
- Multiple conjunctive/disjunctive conditions
- Medium nesting depth

### Complex (25+ lines typical)
- Deeply nested quantifiers
- Recursive definitions
- Multiple existential quantifications
- Complex logical structure with 3+ levels

## Statistical Summaries

Calculate these distributions after processing all axioms:

### pattern_distribution
Count occurrences of each pattern category

### complexity_distribution  
Count simple/moderate/complex axioms

### domain_distribution
Count background_theory/psychology/example axioms

### predicate_frequency
Count how many times each predicate appears

## Conversion Notes

Document important aspects in `conversion_notes`:
- **reified_predicates**: Note use of primed predicates
- **defeasible_reasoning**: Note use of `(etc)` conditions
- **recursive_definitions**: Note self-referential axioms
- **cross_references**: Note dependencies on other chapters
- **technical_complexity**: Note sophisticated logical machinery

## Quality Assurance

### Validation Checklist
- [ ] All FOL expressions syntactically correct
- [ ] Proper parentheses matching
- [ ] Consistent predicate naming
- [ ] Accurate complexity classification
- [ ] Complete predicate and variable lists
- [ ] Appropriate pattern categorization
- [ ] Accurate defeasible/reified flags

### Common Errors to Avoid
- Mismatched parentheses in FOL expressions
- Missing quantifiers in lists
- Incorrect complexity classification
- Inconsistent predicate naming (especially with/without apostrophes)
- Missing `etc` detection for defeasible axioms

## File Naming Convention
Use format: `chapter_[NUMBER]_[CHAPTER_TITLE_LOWERCASE_UNDERSCORES].json`

Examples:
- `chapter_5_eventualities_and_their_structure.json`
- `chapter_21_knowledge_management.json`

## Example Chapters Completed
Chapters 5-14 have been completed and provide good reference examples for:
- **Chapter 5**: Eventualities and reified predicates
- **Chapter 6**: Set theory operations  
- **Chapter 7**: Complex substitution framework
- **Chapter 8**: Reified logical operators
- **Chapter 11**: Defeasible reasoning with `(etc)`
- **Chapter 12**: Scale theory and qualitative reasoning
- **Chapter 13**: Mathematical foundations

The goal is a complete, systematic corpus enabling automated analysis of commonsense psychology formalization.

## Example of a summary for each chapter

## Chapter 21 Summary
- **112 axioms total** covering belief, knowledge, inference, justification, graded belief, assumptions, and mutual belief
- **13 main sections**: Objects of Belief, Belief, Belief Revision, Degrees of Belief, Assuming, Mind and Focus, Inference, Justification, Knowledge, Intelligence, Sentences/Domains, Expertise, Mutual Belief
- **All psychology** - first chapter in Part III focusing specifically on cognitive psychological phenomena

## Key Features Identified:

1. **Foundational Belief Theory**:
    - Axioms 21.1-21.3: Concept-eventuality distinction and belief as relation to concepts
    - Axioms 21.4-21.8: Logic within belief contexts (conjunction, modus ponens, universal instantiation)
    - Axiom 21.9: Perception causes belief (defeasibly)
    - Axiom 21.10: Beliefs influence action through willing

2. **Belief Revision and Management**:
    - Axioms 21.19-21.27: Adding/deleting beliefs, recognizing inconsistencies, restoring consistency
    - Integration with AI belief revision literature and AGM postulates
    - Preference for minimal changes to belief sets when resolving contradictions
    - Agent abilities to manage their own knowledge

3. **Graded Belief Theory**:
    - Axioms 21.28-21.42: Degrees of belief using likelihood scales from Chapter 20
    - Graded belief operations for conjunction, disjunction, implication
    - Thresholds of belief determining bias toward belief/disbelief
    - Suspect, increaseBelief, and threshold-based absolute belief conversion

4. **Assumption Framework**:
    - Axioms 21.43-21.51: Assumptions as reasoning tool (hypothesis testing, accommodation)
    - Logic within assumption contexts (similar to belief but different causation)
    - Making/retracting assumptions as agent abilities
    - Assumptions leading to belief through consequence verification

5. **Mind Structure and Focus**:
    - Axioms 21.52-21.59: Mind with memory and focus of attention components
    - inm relation for mental containment, inFocus for attentional focus
    - thinkThat as conscious belief (belief in focus)
    - Foundation for attention-based cognitive processing

6. **Inference Theory**:
    - Axioms 21.60-21.72: Three modes of inference (deduction, abduction, induction)
    - Inference as causal relation from belief in premises to conscious belief in conclusion
    - Inference management (checking, suppressing, ignoring, contradictions, reaffirmation)
    - Confusion from inconsistent inferences

7. **Justification Taxonomy**:
    - Axioms 21.73-21.81: Multiple justification types (sound, partial, circular, poor, missing)
    - Justification as inference causing belief
    - Sound justification requires minimal proof and belief in all premises
    - Partial justification for fallible inference modes (abduction, induction)

8. **Knowledge as Justified True Belief**:
    - Axioms 21.84-21.94: Knowledge as true belief plus sound true justification
    - Learning, realizing, false positives/negatives
    - Intelligence scale based on inference abilities
    - Gettier problem addressed through justification truth requirement

9. **Sentences and Knowledge Domains**:
    - Axioms 21.95-21.105: Sentences with propositional content and claims
    - Knowledge domains characterized by predicate sets
    - Expertise scales and expert classification
    - Truth/falsity conditions for sentences vs. propositions

10. **Mutual Belief Framework**:
    - Axioms 21.106-21.113: Shared knowledge in communities
    - Mutual belief reflection property (believing that we mutually believe)
    - Copresence heuristic for establishing mutual belief
    - Extension to sentences and knowledge domains

## Technical Sophistication:
- **Extensive Defeasibility**: 22 axioms use (etc) - more than any previous chapter
- **Concept-Eventuality Distinction**: Systematic treatment of mental representations vs. world objects
- **Modal Integration**: Integration with likelihood scales and possibility theory from Chapter 20
- **Reified Cognitive States**: Extensive use of primed predicates for mental processes
- **Scale-Based Psychology**: Graded belief, intelligence, and expertise as scale positions

## Complexity Distribution:
- Simple: 45 axioms (basic constraints, simple implications, defeasible rules)
- Moderate: 52 axioms (standard cognitive definitions, inference rules)
- Complex: 15 axioms (sophisticated epistemic definitions, multi-level cognitive processes)

## Conceptual Importance:
This chapter provides crucial infrastructure for:
- **Cognitive Psychology**: Foundational concepts for all mental phenomena
- **Artificial Intelligence**: Belief revision, inference, knowledge representation
- **Philosophy of Mind**: Epistemic concepts, justification, knowledge conditions
- **Social Cognition**: Mutual belief, shared knowledge, expertise attribution
- **Natural Language Understanding**: Belief contexts, assumptions, communication

## Cross-Chapter Connections:
- **Chapter 5 (Eventualities)**: Fundamental eventuality framework and Rexist
- **Chapter 7 (Substitution)**: Uses partialInstance for universal instantiation
- **Chapter 8 (Logic Reified)**: Uses and', not', imply' for belief operations
- **Chapter 12 (Scales)**: Graded belief, intelligence, and expertise scales
- **Chapter 15 (Causality)**: Causal relations in inference and belief formation
- **Chapter 19 (Persons)**: Uses perceive predicate and mind structure
- **Chapter 20 (Modality)**: Likelihood scales for graded belief theory

## Applications Mentioned:
- **Belief Revision**: Discovering whales aren't fish, white dogs contradicting "all dogs are brown"
- **Inference Types**: Deductive logic, abductive explanation, inductive generalization
- **Graded Belief**: Mugger in next block scenario, threshold-based decisions
- **Assumptions**: Hypothesis testing, accommodation, social conventions (money)
- **Mutual Belief**: Copresence creating shared knowledge, cultural common ground

## Notable Design Decisions:
- **Defeasible Logic**: Heavy emphasis on non-monotonic reasoning with (etc) conditions
- **Concept Mediation**: Belief relates agents to concepts, not directly to world eventualities
- **Anti-Logical Omniscience**: Explicit rejection of knowing all logical consequences
- **Threshold-Based Belief**: Graded belief converts to absolute belief via thresholds
- **Assumption-Belief Distinction**: Different causal patterns for assumptions vs. beliefs
- **Mind Structure**: Memory/focus distinction as foundation for attention
- **Justification Requirements**: Knowledge requires true justification, not just true belief

## Theoretical Significance:
Chapter 21 represents the transition from abstract background theories to specifically psychological phenomena, establishing belief as the central cognitive relation. The extensive use of defeasible reasoning (22 axioms with etc) reflects the inherently non-monotonic nature of human cognition, where general rules have exceptions.

The concept-eventuality distinction provides a sophisticated solution to problems in philosophy of mind about mental representation, while the graded belief theory offers a qualitative alternative to precise probability assignments. The mutual belief framework enables modeling of shared knowledge and cultural common ground essential for communication and cooperation.

The integration with modal logic from Chapter 20 and scale theory from Chapter 12 demonstrates how abstract mathematical frameworks support concrete psychological modeling. The chapter's 112 axioms establish a comprehensive foundation for reasoning about human cognitive processes, setting the stage for more specific psychological theories in subsequent chapters.

This represents one of the most substantial formalizations of folk psychology concepts in the AI and cognitive science literature, providing both philosophical precision and computational tractability for modeling human-like reasoning and belief management.

Now I'll create a comprehensive summary of Chapter 22 following the format shown in the example for Chapter 21:I have successfully converted Chapter 22 "Similarity Comparisons" from the FOL axioms to structured JSON format and created a comprehensive summary. Here's what I've accomplished:

## JSON Conversion (28 axioms):

**Key Features:**
- **Complete axiom extraction** with proper FOL formatting
- **Systematic complexity classification** (3 simple, 13 moderate, 12 complex)
- **Pattern categorization** covering 20 different axiom types
- **Proper predicate and variable tracking**
- **Reification detection** for cognitive processes
- **Cross-reference integration** with other chapters

## Chapter Summary:

**Major Contributions:**
1. **Recursive Similarity Theory** - sophisticated co-recursive definitions with loop prevention
2. **Binary vs. Graded Similarity** - both `similar0` and `similar1` frameworks
3. **Structural Similarity** - composite entity mappings and pattern recognition
4. **Analogy Framework** - structure mapping theory following Gentner (1983)
5. **Cognitive Integration** - reified comparison processes and scale-based judgments

**Technical Sophistication:**
- **Loop prevention mechanisms** using parameter `m` for matched pairs
- **Iterative similarity computation** through `iterArgs` and `iterProps`
- **Scale integration** with Hi/Lo regions for similarity thresholds
- **Structural mappings** for complex compositional entities

This chapter represents one of the most sophisticated formal treatments of similarity in cognitive science, bridging computational algorithms with psychological process models. The recursive framework handles everything from simple feature-based similarity to complex analogical reasoning while maintaining computational tractability.

The work follows the established conversion guidelines perfectly, maintaining consistency with the existing corpus structure and providing the detailed technical analysis needed for automated processing.

## Example of a generated JSON file

{
  "metadata": {
    "title": "A Formal Theory of Commonsense Psychology: How People Think People Think",
    "authors": "Andrew S. Gordon and Jerry R. Hobbs",
    "publisher": "Cambridge University Press", 
    "year": 2017,
    "chapter": 22,
    "chapter_title": "Similarity Comparisons",
    "extraction_date": "2025-01-08",
    "axiom_count": 28,
    "description": "Chapter 22 axioms covering similarity, difference, analogies, patterns, and cognitive comparison processes",
    "notation": "First-order logic with reified eventualities and recursive similarity definitions"
  },
  "axioms": [
    {
      "id": "22.1",
      "chapter": 22,
      "chapter_title": "Similarity Comparisons",
      "section": "22.1",
      "page": 256,
      "axiom_number": "(22.1)",
      "title": "Definition of similarInThat",
      "fol": "(forall(x y e1 e2) (iff(similarInThat x y e1 e2) (and(arg* x e1)(arg* y e2)(subst x e1 y e2)(Rexist e1)(Rexist e2))))",
      "english": "x and y are similar in that e1 holds for x and e2 holds for y, where e1 and e2 are substitutable properties that both really exist",
      "complexity": "moderate",
      "pattern": "definition",
      "predicates": ["similarInThat", "arg*", "subst", "Rexist"],
      "variables": ["x", "y", "e1", "e2"],
      "quantifiers": ["forall"],
      "defeasible": false,
      "reified": false,
      "domain": "psychology"
    },
    {
      "id": "22.2", 
      "chapter": 22,
      "chapter_title": "Similarity Comparisons",
      "section": "22.1",
      "page": 256,
      "axiom_number": "(22.2)",
      "title": "Definition of differentInThat",
      "fol": "(forall (x y e1 e2) (iff (differentInThat x y e1 e2) (and (arg* x e1)(arg* y e2)(subst x e1 y e2)(Rexist e1) (not (Rexist e2)))))",
      "english": "x and y are different in that e1 holds for x but the corresponding property e2 does not hold for y",
      "complexity": "moderate",
      "pattern": "definition",
      "predicates": ["differentInThat", "arg*", "subst", "Rexist"],
      "variables": ["x", "y", "e1", "e2"],
      "quantifiers": ["forall"],
      "defeasible": false,
      "reified": false,
      "domain": "psychology"
    },
    {
      "id": "22.3",
      "chapter": 22,
      "chapter_title": "Similarity Comparisons",
      "section": "22.1",
      "page": 259,
      "axiom_number": "(22.3)",
      "title": "Definition of similar0",
      "fol": "(forall (x1 x2) (iff (similar0 x1 x2) (exists (m p) (and (null m) (or (simPr0 x1 x2 m) (and (eventuality x1)(eventuality x2) (simStr0 x1 x2 m)))))))",
      "english": "Two entities are similar0 if they have similar properties or if they are eventualities with similar structure",
      "complexity": "moderate",
      "pattern": "recursive_definition",
      "predicates": ["similar0", "null", "simPr0", "eventuality", "simStr0"],
      "variables": ["x1", "x2", "m", "p"],
      "quantifiers": ["forall", "exists"],
      "defeasible": false,
      "reified": false,
      "domain": "psychology"
    },
    {
      "id": "22.4",
      "chapter": 22,
      "chapter_title": "Similarity Comparisons", 
      "section": "22.1",
      "page": 259,
      "axiom_number": "(22.4)",
      "title": "Definition of simStr0 - similar structure",
      "fol": "(forall (e1 e2 m) (iff (simStr0 e1 e2 m) (or (equal e1 e2) (exists (p d m1) (and (pair d e1 e2)(addElt m1 m d) (pred p e1)(pred p e2) (forall (n x1 x2 d1) (if (and (argn x1 n e1)(argn x2 n e2) (pair d1 x1 x2)(not (member d1 m))) (simPr0 x1 x2 m1))))))))",
      "english": "Two eventualities have similar structure if they are equal or have the same predicate and similar arguments",
      "complexity": "complex",
      "pattern": "recursive_definition",
      "predicates": ["simStr0", "equal", "pair", "addElt", "pred", "argn", "member", "simPr0"],
      "variables": ["e1", "e2", "m", "p", "d", "m1", "n", "x1", "x2", "d1"],
      "quantifiers": ["forall", "exists"],
      "defeasible": false,
      "reified": false,
      "domain": "psychology"
    },
    {
      "id": "22.5",
      "chapter": 22,
      "chapter_title": "Similarity Comparisons",
      "section": "22.1",
      "page": 260,
      "axiom_number": "(22.5)",
      "title": "Definition of simPr0 - similar properties",
      "fol": "(forall (x1 x2 m) (iff (simPr0 x1 x2 m) (exists (d m1) (and (pair d x1 x2)(addElt m1 m d) (or (equal x1 x2) (exists (e3 e4 p n d1) (and (pred p e3)(pred p e4) (argn x1 n e3)(argn x2 n e4) (pair d1 e3 e4)(not (member d1 m)) (simStr0 e3 e4 m1))))))))",
      "english": "Two entities have similar properties if they are equal or share a property with the same predicate and corresponding arguments",
      "complexity": "complex", 
      "pattern": "recursive_definition",
      "predicates": ["simPr0", "pair", "addElt", "equal", "pred", "argn", "member", "simStr0"],
      "variables": ["x1", "x2", "m", "d", "m1", "e3", "e4", "p", "n", "d1"],
      "quantifiers": ["forall", "exists"],
      "defeasible": false,
      "reified": false,
      "domain": "psychology"
    },
]}


