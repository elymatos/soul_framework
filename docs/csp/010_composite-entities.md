# Chapter 10: Composite entities

22 axioms total covering composite entities, figure-ground relations, and patterns
4 main sections: Definitions, Simple Examples, Figure-Ground Relation, and Patterns and Their Instances
All background theory - foundational concepts for complex structures

## Key Features Identified:

1. Composite Entity Framework:

Axiom 10.1: Core definition - entities with components, properties, and relations
Axioms 10.2-10.3: Components must be non-empty sets
Axiom 10.4: Aggregates as simple two-component entities
Axioms 10.5-10.6: componentOrWhole and externalTo relationships


2. Properties and Relations Constraints:

Axiom 10.7: onlyarg* - recursive definition for single-argument properties
Axiom 10.8: Properties must have onlyarg* that's a component or whole
Axiom 10.9: Relations must involve a component/whole and something else
Axioms 10.10-10.11: Single relation and combined property/relation predicates


3. Examples as Composite Entities:

Axiom 10.12: Sets as composite entities (members as components)
Axiom 10.13: Pairs as composite entities (first/second elements, relations)
Axiom 10.14: Sequences as composite entities (elements + ordering relations)


4. Figure-Ground Relation:

Axiom 10.15: Basic constraints on the 'at' relation
Axioms 10.16-10.17: Two equivalent definitions of 'ground' (shared properties)
Axiom 10.18: 'at' relation requires ground as third argument


5. Pattern System:

Axiom 10.19: Patterns contain type elements as components
Axiom 10.20: Pattern parameters are the type element components
Axiom 10.21: Pattern instances replace all type elements with real entities
Axiom 10.22: Incomplete instances have some but not all parameters instantiated


6. Complexity Distribution:

Simple: 6 axioms (basic definitions, constraints)
Moderate: 10 axioms (medium complexity definitions with quantifiers)
Complex: 6 axioms (nested quantifiers, recursive definitions, pattern instances)


## Notable Technical Features:

Recursive Definitions: onlyarg* (10.7) recurses through eventuality arguments
Dual Ground Definitions: Axioms 10.16-10.17 provide equivalent formulations using substitution vs. typical elements
Complex Pattern Logic: Axioms 10.21-10.22 handle complete and partial instantiation with property/relation preservation
Reified Examples: Axioms 10.12-10.14 use reified predicates (set', pair0', sequence')


## Conceptual Importance:

Figure-Ground: Fundamental cognitive relationship from spatial reasoning
Composite Structure: Foundation for understanding complex objects, events, and information structures
Pattern Templates: Enables reasoning about types and their instances
Uniform Treatment: Physical objects, events, and abstract structures all treated uniformly



## Cross-Chapter Connections:

Builds on sets (Chapter 6), substitution and typical elements (Chapter 7)
Uses reified predicates from eventualities (Chapter 5)
Figure-ground relation will be crucial for scales and spatial reasoning
Pattern system connects to functional dependencies and instantiation

## Domain Applications:
The chapter mentions diverse applications:

Physical: doors, cups, telephones, chairs, automobiles
Biological: trees, bees, persons
Events: hikes, erosion, concerts
Information: equations, sentences, theories, schedules
Mixed: books (physical + informational)

This chapter provides essential infrastructure for representing and reasoning about the complex, structured entities that populate commonsense knowledge and psychological reasoning.
