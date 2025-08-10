# Chapter 7: Substitution, Typical elements and Instances
- **19 axioms total** covering substitution, typical elements, functional dependencies, and instantiation
- **5 main sections**: Substitution, Typical Elements, Handling Thorny Issues, Functional Dependencies, and Instances
- **All background theory** - advanced foundational concepts

## Key Features Identified:

1. **Substitution Framework**:
    - Axiom 7.1 is extremely complex, defining recursive substitution between eventualities
    - Axiom 7.2 defines composite substitutions (subst2)

2. **Typical Elements**:
    - Reification of universally quantified variables as "typical elements"
    - Property inheritance from typical elements to real set members (7.4)
    - Defined sets (dset) with their typical elements (7.5-7.6)

3. **Technical Safeguards**:
    - Axioms 7.7-7.8 prevent problematic applications involving set membership and typical elements
    - These "blocking" predicates solve Russell paradox-type issues

4. **Functional Dependencies**:
    - Captures existentially quantified variables through functional dependencies (fd)
    - Skolem functions (skfct) and their ranges (rangeFd)
    - Dependency inheritance for partial instantiations (7.13)

5. **Instantiation Framework**:
    - Parameters of abstract eventualities (7.16)
    - Partial and complete instantiation (7.17-7.18)
    - Type instantiation with holdsFor (7.19)

6. **Complexity Distribution**:
    - Simple: 3 axioms (basic definitions)
    - Moderate: 13 axioms (most of the functional machinery)
    - Complex: 3 axioms (recursive substitution, dependency inheritance, partial instantiation)

## Notable Technical Features:
- Axiom 7.1 is one of the most complex in the book, with nested quantifiers and recursive calls
- The chapter solves deep logical problems around reified variables and set theory
- Introduces the foundation for handling general vs. specific knowledge

