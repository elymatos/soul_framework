# Chapter 22: Simility comparisons

## Overview
- **28 axioms total** covering similarity, difference, analogies, pattern recognition, and cognitive comparison processes
- **3 main sections**: Similarity, Similarity of Structured Entities, Cognizing Similarities
- **Pure psychology** - sophisticated treatment of similarity as foundational cognitive operation

## Key Features Identified:

### 1. **Basic Similarity Theory**:
- **Axioms 22.1-22.2**: `similarInThat` and `differentInThat` as fundamental relational concepts
- Properties held in common vs. properties that differ between entities
- Foundation for all higher-level similarity reasoning

### 2. **Binary Similarity Framework (similar0)**:
- **Axioms 22.3-22.5**: Recursive co-definition of `similar0`, `simStr0`, `simPr0`
- Entities similar if they share properties OR have similar structure as eventualities
- Loop prevention through matched pairs tracking (parameter `m`)
- Binary judgment: entities are either similar or not

### 3. **Graded Similarity Framework (similar1)**:
- **Axioms 22.6-22.11**: Enhanced similarity with explicit property set accumulation
- `similar1` tracks exactly which properties are shared (parameter `s`)
- More sophisticated than binary approach - enables quantitative similarity measurement
- **Axiom 22.11**: Equivalence theorem linking binary and graded approaches

### 4. **Iterative Similarity Computation**:
- **Axiom 22.8**: `iterArgs` - systematic traversal of eventuality arguments
- **Axiom 22.10**: `iterProps` - traversal of entity properties with inferential independence
- Complex bookkeeping to avoid infinite loops and ensure termination
- Builds up shared property sets incrementally

### 5. **Similarity Scale Theory**:
- **Axioms 22.12-22.17**: Integration with general scale framework from Chapter 12
- **Axiom 22.12**: Subset consistency - more shared properties = greater similarity
- **Axiom 22.14**: `similarityScale` based on `moreSimScale` partial ordering
- **Axioms 22.15-22.16**: `similar` and `different` as Hi/Lo regions on scale
- **Axiom 22.17**: Symmetry of similarity relation

### 6. **Structured Entity Similarity**:
- **Axiom 22.18**: `ceMapping` - composite entity mapping preserving structure
- **Axioms 22.19-22.20**: Pattern recognition through `exhibitPattern` and `commonPattern`
- Similarity based on shared structural organization, not just properties

### 7. **Analogy Through Structure Mapping**:
- **Axiom 22.21**: `cePredReplace` - systematic predicate transformation
- **Axiom 22.22**: `structureMapping` - composition of predicate replacement and entity mapping
- **Axiom 22.23**: `analogous` - entities with structure mappings between them
- Follows Gentner's (1983) structure mapping theory

### 8. **Cognitive Similarity Processes**:
- **Axiom 22.24**: `compare` - thinking about shared and differing properties
- **Axiom 22.25**: Comparison defeasibly causes similarity/difference judgments
- **Axiom 22.26**: Similarity scales as comparison metrics
- **Axioms 22.27-22.28**: `findPattern` and `drawAnalogy` as cognitive achievements

## Technical Sophistication:

### **Recursive Complexity**:
- Mutually recursive definitions with sophisticated loop prevention
- `simPr0`/`simStr0` and `simPr1`/`simStr1` co-define each other
- Parameter `m` tracks matched pairs to prevent infinite recursion

### **Graded vs. Binary Similarity**:
- `similar0`: Existential approach (at least one shared property)
- `similar1`: Accumulative approach (explicit set of all shared properties)
- Enables both qualitative and quantitative similarity reasoning

### **Structural Sophistication**:
- Beyond simple feature-based similarity to structural correspondence
- Handles complex compositional entities with components, properties, relations
- Systematic treatment of analogies through predicate mappings

### **Cognitive Integration**:
- Bridges computational similarity algorithms with psychological processes
- Reified cognitive operations: `compare'`, `findPattern'`, `drawAnalogy'`
- Integration with belief and thinking frameworks from Chapter 21

## Complexity Distribution:
- **Simple**: 3 axioms (basic definitions, symmetry)
- **Moderate**: 13 axioms (standard similarity definitions, cognitive processes)
- **Complex**: 12 axioms (recursive definitions, structural mappings, iterative procedures)

## Cross-Chapter Connections:
- **Chapter 5 (Eventualities)**: Uses `eventuality`, `argn`, `pred` predicates extensively
- **Chapter 6 (Set Theory)**: Heavy use of set operations (`member`, `union`, etc.)
- **Chapter 7 (Substitution)**: Uses `subst` for property substitution in similarity
- **Chapter 8 (Logic Reified)**: Uses `Rexist` for property existence
- **Chapter 10 (Composite Entities)**: Pattern framework for structured similarity
- **Chapter 12 (Scales)**: Similarity scales using general scale theory
- **Chapter 15 (Causality)**: Causal relations in cognitive similarity processes
- **Chapter 21 (Knowledge Management)**: Uses `thinkOf`, `thinkThat'` predicates

## Applications and Examples:

### **Feature-Based Similarity**:
- Block comparison: A(red, square, large) vs B(red, round, large) vs C(red, square, small)
- A more similar to C than B (2 vs 1 shared properties)

### **Quantitative Measurements**:
- Height similarity through shared threshold properties
- Pat(181cm) vs Chris(179cm) vs Kim(182cm) comparisons

### **Physics Problem Analysis**:
- Ladder and man both exert forces with similar structure
- Recursive similarity through force→weight→objects→positions→endpoints

### **Analogy Examples**:
- Planets orbiting Sun ↔ Students around professor
- Structure mapping preserves relational patterns while changing predicates

## Notable Design Decisions:

### **Loop Prevention**:
- Parameter `m` tracks already-matched pairs
- Essential for termination in recursive similarity checking
- Sophisticated bookkeeping throughout co-recursive definitions

### **Inferential Independence**:
- `iterProps` ensures properties are inferentially independent
- Avoids counting "man"/"human"/"mammal"/"animal" as separate similarities
- Maintains meaningful similarity metrics

### **Symmetry vs. Asymmetry**:
- Formal similarity relation is symmetric (Axiom 22.17)
- Acknowledges Tversky's asymmetry observations as discourse effects
- Distinguishes logical symmetry from pragmatic usage patterns

### **Graded Similarity**:
- Parameter `s` accumulates shared properties for quantitative comparison
- Enables subset consistency (Axiom 22.12)
- Supports both binary and graded similarity judgments

## Theoretical Significance:

Chapter 22 provides one of the most sophisticated formal treatments of similarity in cognitive science literature. The recursive, mutually-defined similarity predicates handle both simple feature-based similarity and complex structural correspondences.

The chapter's key innovation is the systematic treatment of similarity at multiple levels:
1. **Feature level**: Shared properties between entities
2. **Structural level**: Corresponding relationships and argument patterns
3. **Analogical level**: Systematic predicate mappings preserving structure
4. **Cognitive level**: Mental processes of comparison and pattern recognition

The graded similarity framework (`similar1`) advances beyond binary similarity judgments to enable quantitative similarity metrics while maintaining computational tractability. The integration with scale theory provides a principled foundation for similarity-based reasoning.

The treatment of analogies through structure mapping follows established cognitive science (Gentner 1983) while providing formal logical foundations. The systematic handling of composite entities enables similarity reasoning about complex structured objects.

The cognitive integration through reified comparison processes (`compare'`, `findPattern'`, `drawAnalogy'`) connects computational similarity algorithms with psychological theories of human similarity reasoning.

## Technical Contributions:

### **Loop Prevention in Recursive Similarity**:
- Novel formal treatment of the infinite regress problem in similarity
- Parameter `m` provides elegant solution to mutual recursion termination

### **Graded Similarity Accumulation**:
- Parameter `s` enables explicit tracking of shared properties
- Supports both existential and universal similarity quantifications

### **Structural Similarity Framework**:
- Systematic treatment of composite entity mappings
- Integration of pattern recognition with similarity theory

### **Scale-Based Similarity**:
- Principled integration with general scale theory
- Enables context-dependent similarity thresholds through Hi/Lo regions

This chapter establishes similarity as a foundational cognitive operation supporting higher-level reasoning about patterns, analogies, and relationships. The formal framework provides both computational algorithms and psychological process models for human-like similarity reasoning.
