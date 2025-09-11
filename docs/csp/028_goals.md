# Chapter 28: Goals

## Overview
- **82 axioms total** covering goals, subgoals, plans, trying/succeeding/failing, functionality, value/cost/importance, and multi-agent goal interactions
- **7 main sections**: Goals/Subgoals/Plans, Content of Goals, Goals and Multiple Agents, Trying/Succeeding/Failing, Functionality, Good and Bad, Value/Cost/Importance
- **Pure psychology domain** - all axioms deal with intentional agency and goal-directed behavior

## Key Features Identified:

### 1. **Foundational Goal Theory**:
- **Goal Definition (28.1)**: Type constraint - goals are eventualities that agents have
- **Causal Knowledge (28.2-28.3)**: Agents know facts about what causes or enables what in the world
- **Goal Adoption (28.4-28.8)**: If agent has goal e2 and believes e1 causes/enables e2, agent adopts e1 as subgoal
- **Defeasible Planning**: Only axiom 28.8 uses defeasible reasoning with (etc) for goal adoption

### 2. **Subgoal Structure and Planning**:
- **Causation vs. Enablement**: Separate treatment for causal and enabling conditions in planning
- **Subgoal Definition (28.13)**: Subgoals are members of causal complexes for supergoals that agents believe in
- **Transitivity (28.14)**: Subgoal relation is transitive, allowing hierarchical goal structures
- **Goal Reversal**: Predicate goal reverses causality - wanting light on causes wanting to flip switch

### 3. **Temporal Goal Categories**:
- **Knowledge Goals (28.16)**: Goals of knowing something - prerequisite for most actions
- **Preservation Goals (28.17-28.18)**: Maintaining states over time intervals, with violation conditions
- **Persistent Goals (28.19)**: Goals that remain even after achievement (wealth, shell collecting)
- **Future Goals (28.20)**: Envisioned goals for future times (retirement planning)
- **Achievement States (28.21-28.23)**: Achieved, unachieved, and never-achieved goal classifications

### 4. **Goal Interactions and Conflicts**:
- **Conflicting Goals (28.24)**: Goals that cannot both be achieved simultaneously
- **Auxiliary Goals (28.25)**: Goals abandoned when conflicts arise with primary goals
- **Goal Hierarchy**: Sophisticated priority system for resolving goal conflicts

### 5. **Multi-Agent Goal Theory**:
- **Shared Goals (28.26-28.27)**: Collective goals with mutual belief among group members
- **Competitive Goals (28.28)**: Multiple agents wanting same property for themselves (races, competitions)
- **Adversarial Goals (28.29)**: Direct opposition where one agent wants negation of other's goal
- **Goal Attribution**: Framework for agents to understand and predict others' goals

### 6. **Action Theory - Trying, Succeeding, Failing**:
- **Trying Definition (28.30)**: Executing subgoals that are actions, caused by having those subgoals
- **Causal Involvement (28.31)**: Trying implies believing actions are causally involved in goal achievement
- **Success (28.32)**: Trying that causes the goal to actually occur
- **Failure (28.33)**: Trying without goal occurrence - allows for "lucking out" vs. genuine success

### 7. **Functionality Theory**:
- **Absolute Functionality (28.34)**: Composite entities associated with hypothetical agent goals
- **Relative Functionality (28.35)**: Component behaviors serving whole-system functionality
- **Intactness (28.36)**: Components able to fulfill functionality without impediments
- **Goal Talk Extension**: Framework applies to artifacts, organizations, and natural systems

### 8. **Value, Cost, and Importance Framework**:
- **Good/Bad Definitions (28.37-28.38)**: Events good/bad based on causal contribution to goals
- **Partial Orderings (28.39-28.44)**: Transitive comparison relations for value, cost, importance
- **Supergoal Hierarchy (28.45-28.52)**: Upper bound and least upper bound supergoals determine value rankings
- **Goal Relevance (28.53-28.58)**: Positive, negative, and general goal relevance for events and consequences

### 9. **Scale Theory Integration**:
- **Value/Cost/Importance Scales (28.74-28.82)**: Formal scale definitions using partial orderings
- **Hi Region Classification**: Valuable, costly, important as Hi region membership
- **Scale Positions**: Value, cost, importance as positions within respective scales
- **Property-Based Transfer**: Entity value/cost/importance derived from goal-relevant properties

## Technical Sophistication:

### **Extensive Reification**: 
- 21 different primed predicates for goal processes: `goal'`, `cause'`, `enable'`, `believe'`, `subgoal'`, `try'`, `succeed'`, `moreValuable'`, etc.
- Enables sophisticated temporal and causal reasoning about intentional processes

### **Scale Theory Integration**: 
- Deep integration with Chapter 12 scale theory through `scaleDefinedBy`, `Hi`, `inScale` predicates
- Formal partial orderings for value, cost, and importance comparisons
- Systematic transfer principles from goals to entities via properties

### **Minimal Defeasible Reasoning**: 
- Only 2 axioms use `(etc)` conditions (28.8, 28.15) - focus on precise goal relationships
- Most goal reasoning follows deterministic patterns once beliefs are established

### **Multi-Agent Sophistication**: 
- Complex treatment of shared, competitive, and adversarial goals
- Mutual belief integration for collective agency
- Goal attribution and other-agent reasoning foundations

### **Temporal Complexity**: 
- Sophisticated temporal logic with `atTime`, `atTime'`, `before`, `during`, `during'`
- Time-anchored goal states, achievement conditions, and preservation requirements
- Future-directed planning and expectation integration

## Complexity Distribution:
- **Simple: 20 axioms** (type constraints, basic goal reasoning, transitivity properties)
- **Moderate: 48 axioms** (standard goal definitions, multi-agent interactions, scale operations)
- **Complex: 14 axioms** (sophisticated temporal reasoning, functionality theory, upper bound supergoals)

## Conceptual Importance:

### **Cognitive Architecture**:
Provides comprehensive formal foundation for intentional agency. Shows how goals drive planning, action selection, and value judgments through systematic causal reasoning and belief integration.

### **AI Planning Systems**:
Offers implementable framework for goal-directed reasoning with hierarchical planning, conflict resolution, and multi-agent coordination. Integrates trying/succeeding/failing for robust plan execution.

### **Philosophy of Mind**:
Addresses fundamental questions about intentionality, agency, and value. Formalizes relationships between goals, actions, and evaluative judgments while maintaining computational tractability.

### **Multi-Agent Systems**:
Sophisticated framework for shared goals, competition, and adversarial interactions. Provides foundation for understanding cooperation, conflict, and coordination in multi-agent environments.

### **Value Theory**:
Formal account of how goals ground evaluative judgments about value, cost, and importance. Links subjective preferences to objective causal structures through goal-relevant properties.

## Cross-Chapter Connections:
- **Chapter 15 (Causality)**: Uses `cause`, `enable`, `causalComplex` as foundation for goal reasoning
- **Chapter 21 (Belief Management)**: Uses `believe` for goal adoption and plan formation
- **Chapter 24 (Envisioning)**: Uses `causallyInvolved` for action planning and trying
- **Chapter 12 (Scales)**: Deep integration for value, cost, and importance measurements
- **Chapter 19 (Persons)**: Uses `agent` and `person` predicates for intentional subjects
- **Temporal Framework**: Integrates with temporal logic from earlier chapters

## Applications Mentioned:
- **Hierarchical Planning**: Subgoal decomposition for complex task achievement
- **Multi-Agent Coordination**: Shared goals in organizations (General Motors selling cars)
- **Competitive Scenarios**: Racing, elections, market competition with goal conflicts
- **Artifact Design**: Functionality analysis of cars, steering wheels, complex systems
- **Natural Systems**: Functional analysis of trees, volcanos through hypothetical agency
- **Value Judgments**: Economic decisions, importance ranking, cost-benefit analysis

## Notable Design Decisions:

### **Goal-Belief Integration**: 
Goals adopted through belief in causal/enabling relations rather than direct causal facts. Enables planning with false beliefs and accommodates individual differences in causal understanding.

### **Defeasible Minimalism**: 
Limited use of defeasible reasoning reflects focus on structural relationships rather than probabilistic inference. Goal adoption is defeasible, but goal definitions are precise.

### **Functionality Generalization**: 
Extension of goal framework to artifacts and natural systems through hypothetical agents. Enables unified treatment of intentional design and functional analysis.

### **Scale-Based Evaluation**: 
Value, cost, and importance grounded in formal scale theory rather than utility functions. Maintains qualitative reasoning while enabling comparative judgments.

### **Multi-Agent Integration**: 
Sophisticated treatment of collective agency, competition, and conflict. Mutual belief integration for shared goals while maintaining individual goal autonomy.

### **Temporal Anchoring**: 
Goals, achievements, and preservation anchored to specific times enabling precise temporal reasoning and plan monitoring.

### **Action-Goal Connection**: 
Trying defined through subgoal execution with causal requirements. Distinguishes genuine success from accidental achievement through causal role of intentions.

## Theoretical Significance:

Chapter 28 represents the most comprehensive formal treatment of goal theory in cognitive science, integrating planning, action, evaluation, and multi-agent coordination into a unified framework.

The chapter's strength lies in its systematic integration across multiple levels: from individual goal adoption through belief and causation, to hierarchical planning with subgoal structures, to multi-agent scenarios with shared and conflicting goals, to evaluative frameworks grounding value judgments in goal-relevant properties.

The minimal use of defeasible reasoning (only 2/82 axioms) reflects the focus on structural relationships in goal systems rather than probabilistic planning. Once beliefs about causation and enabling are established, goal adoption and planning follow deterministic patterns, though the beliefs themselves may be defeasible.

The functionality theory provides an elegant extension of intentional concepts to artifacts and natural systems by associating them with hypothetical agents. This enables functional analysis while maintaining the goal-theoretic foundation and avoiding problematic teleological commitments.

The scale theory integration (28.74-28.82) grounds evaluative concepts in formal comparative structures rather than utility functions. This maintains the qualitative character of commonsense evaluation while enabling systematic reasoning about value, cost, and importance relationships.

The multi-agent framework addresses fundamental issues in collective agency, competition, and cooperation. The treatment of shared goals through mutual belief provides foundation for understanding organizational behavior, while competitive and adversarial goal frameworks address conflict and coordination challenges.

The temporal sophistication enables precise reasoning about goal achievement, preservation, and planning over time. The distinction between achieved goals (causally successful), unachieved goals (attempted but failed), and never-achieved goals (never attempted) provides fine-grained analysis of success and failure patterns.

The trying/succeeding/failing framework addresses classic issues in action theory about the relationship between intentions and outcomes. The requirement that trying involve executing subgoals that are caused by having those subgoals ensures that genuine agency involves appropriate causal connections between goals and actions.

This chapter establishes the formal foundation for intentional agency that underlies rational choice, planning systems, multi-agent coordination, and evaluative judgment. It demonstrates how sophisticated intentional phenomena can emerge from systematic application of causal reasoning, belief integration, and hierarchical goal structures.

## Pattern Analysis:
- **50 Definitions** - Comprehensive formalization of goal theory concepts
- **23 Goal Reasoning** - Systematic inference patterns for goal adoption and evaluation
- **4 Type Constraints** - Structural requirements for goal predicates
- **3 Argument Structure** - Transitivity and structural properties
- **2 Defeasible Rules** - Limited non-monotonic reasoning for goal adoption
- **2 Axiom Schema** - General patterns for causal knowledge

The chapter represents the culmination of the commonsense psychology framework, showing how abstract theories of causation, belief, time, and scales support sophisticated intentional agency suitable for both theoretical understanding and computational implementation.
