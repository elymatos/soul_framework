# Chapter 11: Defeasibility
- **14 axioms total** covering defeasible reasoning and nonmonotonic logic
- **Single main section** but introduces crucial concepts for the entire book
- **13 examples, 1 psychology** - mostly illustrative examples with one psychological axiom

## Key Features Identified:

1. **Classical vs. Defeasible Reasoning**:
    - Axiom 11.1: Classical "birds fly" rule that leads to contradictions
    - Axiom 11.2: Defeasible version using etc1 predicate
    - Shows the problem with monotonic reasoning in commonsense knowledge

2. **Extended Bird Example**:
    - Axioms 11.3-11.4: Emus are birds but don't fly (the exception case)
    - Axioms 11.7-11.8: Additional defeasible properties (two legs, feathers)
    - Shows how multiple defeasible rules can coexist

3. **Alternative Approaches**:
    - Axioms 11.5-11.6: Circumscriptive logic using "abnormal" (ab) predicates
    - Different way to encode the same defeasible reasoning

4. **Etc Predicate System**:
    - Different etc predicates for different axioms (etc1, etc2, etc3, etc4)
    - Axiom 11.9: Biconditional relationship between etc3 and feathered
    - Axiom 11.10: Relationships between etc predicates
    - Axiom 11.12: Psychology example - mothers love children

5. **Notation Development**:
    - Axiom 11.13: Full form with indexed etc predicate (etc.11.14)
    - Axiom 11.14: Abbreviated form using simple "etc"
    - The abbreviation is for readability - must be expanded for automated reasoning

6. **Technical Insights**:
    - **Nonmonotonic Logic**: New information can defeat previous conclusions
    - **Weighted Abduction**: Framework for handling costs of assumptions
    - **Indexing**: Each axiom gets its own etc predicate to avoid interference
    - **Variable Scope**: etc predicates must include all universally quantified variables

7. **Complexity Distribution**:
    - Simple: 11 axioms (most are straightforward defeasible rules)
    - Moderate: 3 axioms (biconditional, general patterns)
    - Complex: 0 axioms (conceptually sophisticated but formally simple)

## Conceptual Importance:
This chapter is foundational for the entire book because:
- Most commonsense knowledge is defeasible, not absolute
- The "etc" notation appears throughout the psychology theories
- Enables realistic modeling of human reasoning under uncertainty
- Bridges formal logic and practical knowledge representation

## Cross-References:
- The "etc" predicate will appear extensively in Chapters 21-49 (psychology theories)
- Connected to weighted abduction framework (Hobbs et al., 1993)
- Links to circumscriptive logic (McCarthy, 1980)

