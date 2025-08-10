# Chapter 14: Change of state
- **13 axioms total** covering change of state and derived concepts
- **2 main sections**: The change Predicate and Predicates Derived from change
- **All background theory** - foundational concepts for temporal and causal reasoning

## Key Features Identified:

1. **Basic Change Framework**:
    - Axiom 14.1: Change arguments must be eventualities
    - Axiom 14.2: Changes must involve a common entity (prevents unrelated state changes)
    - Axiom 14.3: Change is defeasibly transitive (with etc conditions)

2. **Inconsistency and Cyclical Change**:
    - Axiom 14.4: Complex axiom handling inconsistency - if states aren't inconsistent, change must go through an inconsistent intermediate state
    - Axiom 14.5: Defeasible inference that start and end states are inconsistent (since change isn't normally cyclic)

3. **Derived Change Predicates**:
    - Axiom 14.6: changeIn - change in properties of an entity
    - Axiom 14.7: changeFrom - change out of a state (ensures no same-type state exists after)
    - Axiom 14.8: changeTo - change into a state (ensures no same-type state existed before)

4. **Movement and Vertical Scales**:
    - Axiom 14.9: move - change from being at one location to another
    - Axioms 14.10-14.11: Vertical scales (numeric scales are vertical; vertical arguments must be scales)
    - Axioms 14.12-14.13: increase/decrease as movement up/down vertical scales

5. **Technical Sophistication**:
    - **Reified Predicates**: Heavy use of primed predicates (change', at', etc.)
    - **Substitution**: Uses substitution framework from Chapter 7 for type/token distinctions
    - **Generation**: Uses gen relation for eventuality relationships
    - **Inconsistency**: Sophisticated handling of when states conflict

6. **Complexity Distribution**:
    - Simple: 6 axioms (basic constraints, type requirements)
    - Moderate: 6 axioms (derived predicate definitions)
    - Complex: 1 axiom (14.4 - change inconsistency requirement)

## Conceptual Importance:
This chapter provides crucial infrastructure for:
- **Temporal Reasoning**: Foundation for understanding processes and events over time
- **Causal Reasoning**: Change is prerequisite for causality
- **Natural Language**: Verbs of motion and change ("move," "increase," "become")
- **Psychological Processes**: Learning, growth, adaptation all involve change
- **Physical Processes**: Motion, transformation, development

## Cross-Chapter Connections:
- **Chapter 7 (Substitution)**: Uses subst for type/token relationships
- **Chapter 8 (Logic Reified)**: Uses inconsistent and gen predicates
- **Chapter 10 (Composite Entities)**: Uses at relation for location
- **Chapter 12 (Scales)**: Uses lts and vertical scale concepts
- **Chapter 5 (Eventualities)**: Fundamental eventuality framework

## Applications Mentioned:
- **Physical**: Moving, growing, opening/closing doors
- **Cognitive**: Learning (not knowing → knowing)
- **Social**: Changing relationships, roles
- **Measurement**: Changes in quantities on scales
- **Spatial**: Movement from place to place

## Notable Design Decisions:
- **Undefinable**: Change is treated as too fundamental to define formally
- **Eventuality-based**: Changes relate eventualities, not just entities
- **Defeasible Properties**: Allows exceptions to general rules
- **Vertical Metaphor**: Numbers, probabilities viewed as "up/down"
