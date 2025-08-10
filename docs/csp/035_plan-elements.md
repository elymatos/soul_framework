# Chapter 35: Plan Elements

- **12 axioms total** covering plan elements including conditional actions, repetitive structures, preconditions, subplans, and temporal plan structures
- **3 main sections**: Temporal Structure and Goals, Plan Structure Elements, Complex Plan Types
- **Pure psychology** - systematic analysis of plan components and their relationship to temporal event structures

## Key Features Identified:

### 1. **Goal-Directed Temporal Structures** (Axioms 35.1-35.3):

#### **Conditional Actions and Goals**:
- **Axiom 35.1**: **Complex defeasible rule** for conditional actions
  - Conditional action → condition enables goal OR negation of condition is goal
  - Two strategies: **exploiting** condition occurrence vs. **escaping** condition consequences
  - Condition must be relevant to agent's goals for action to make sense
  - Defeasible relationship acknowledging exceptions to goal-relevance

#### **Repetitive Structure Goals**:
- **Axiom 35.2**: `whileDo` - **defeasible goal negation**
  - Agent's goal is to negate the while-condition
  - Explains why agent keeps executing body until condition gone
  - Example: Wipe sweat while it drips into eyes → goal is no sweat in eyes
- **Axiom 35.3**: `repeatUntil` - **defeasible termination goal**
  - Until-condition is agent's goal
  - Each iteration brings agent closer to achieving termination condition
  - Example: Pound nails until flush → goal is nails flush with wood

#### **Inferring Causal from Temporal Structure**:
- **Key Insight**: Temporal patterns reveal underlying causal goal structure
- **Repetitive actions**: Usually have cumulative effect toward goal achievement
- **Conditional actions**: Usually exploit or counteract triggering conditions
- **Exception**: Individual pleasure actions (eating candy) may lack larger causal structure

### 2. **Plan Structure Components** (Axioms 35.4-35.8):

#### **Subplan Variations**:
- **Axiom 35.4**: `partialSubplan` - incomplete execution capability
  - Some terminal nodes not executable given constraints
  - Represents planning under resource/knowledge limitations
- **Axiom 35.5**: `subplanAgent` - **moderate complexity agent responsibility**
  - Single agent responsible for all actions in subplan
  - Enables clear accountability and coordination in complex plans

#### **Plan Termination and Causation**:
- **Axiom 35.6**: `planTermination` - **complex termination framework**
  - Final action that achieves goal through causal complex relationship
  - No other subgoals occur after termination action
  - Links plan structure to causal goal achievement
  - Most complex axiom in chapter with nested quantification

#### **Precondition Taxonomy**:
- **Axiom 35.7**: `knowledgePrecondition` - **simple knowledge enabling**
  - Agent knowing something enables action
  - Example: Knowing toolbox location enables finding tools
- **Axiom 35.8**: `resourcePrecondition` - **moderate resource framework**
  - Having resource + resource change + causal involvement
  - Distinguishes resources/tools from mere objects by change requirement
  - Example: Nails undergo location change during hammering

### 3. **Complex Plan Types** (Axioms 35.9-35.12):

#### **Conditional and Repetitive Plans**:
- **Axiom 35.9**: `conditionalPlan` - plans executed only when conditions occur
  - Links plan structure to conditional event structure
  - Reactive plans as special case responding to specific eventualities
- **Axiom 35.10**: `repetitivePlan` - plans with while/repeat structures
  - Captures systematic repetition patterns in goal achievement
  - Foundation for iterative problem-solving strategies

#### **Temporal and Logical Plan Structure**:
- **Axiom 35.11**: `periodicSubplan` - temporally regular execution patterns
  - Subplans with periodic temporal sequences (daily, weekly schedules)
  - Example: Workman's daily 9-5 job routine
- **Axiom 35.12**: `requiredPrecondition` - disjunctive enabling conditions
  - Precondition required regardless of which disjunct holds
  - Essential conditions that apply across alternative execution paths

## Technical Sophistication:
- **Temporal-Causal Integration**: Systematic connection between temporal event patterns and underlying causal goal structures
- **Defeasible Goal Inference**: 3 axioms with `(etc)` for non-monotonic reasoning about goal-action relationships
- **Plan Component Taxonomy**: Comprehensive classification of subplans, agents, termination, and preconditions
- **Reified Mental States**: 5 reified predicates for knowledge, resources, temporal relations, and logical operators
- **Complex Quantification**: Nested universal and existential quantifiers especially in plan termination definitions
- **Cross-Domain Integration**: Heavy use of temporal, causal, and goal theory from previous chapters

## Complexity Distribution:
- **Simple**: 7 axioms (basic plan types, simple preconditions, straightforward definitions)
- **Moderate**: 4 axioms (goal-action rules, subplan agents, resource preconditions)
- **Complex**: 1 axiom (plan termination with complex causal and temporal constraints)

## Conceptual Importance:
This chapter provides crucial infrastructure for:
- **Plan Recognition**: Inferring agent goals from observable temporal action patterns
- **Behavioral Explanation**: Understanding why agents engage in repetitive and conditional behaviors
- **Plan Decomposition**: Breaking complex plans into manageable subcomponents with clear responsibilities
- **Precondition Analysis**: Systematic treatment of knowledge and resource requirements for action
- **Temporal Planning**: Integration of periodic and conditional execution patterns
- **Multi-Agent Coordination**: Clear assignment of agent responsibilities within complex plans

## Cross-Chapter Connections:
- **Chapter 31 (Plans)**: Uses basic plan, subplan, and subgoal framework as foundation
- **Chapter 28 (Goals)**: Goal theory underlying all defeasible goal inference rules
- **Chapter 16-17 (Time/Events)**: Temporal sequence theory for repetitive and periodic plans
- **Chapter 15 (Causality)**: Causal complex theory for plan termination and preconditions
- **Chapter 21 (Belief)**: Knowledge preconditions linking to belief and knowing
- **Chapter 5 (Eventualities)**: Basic eventuality framework for all plan elements

## Applications Mentioned:
- **Workman Example**: Comprehensive illustration of plan elements in construction work
  - Conditional actions: Wiping sweat when it drips into eyes
  - Repetitive actions: Pounding nails until flush, daily work routine
  - Knowledge preconditions: Knowing where toolbox is located
  - Resource preconditions: Having hammer and nails available
  - Plan termination: Putting tools away at 5 o'clock
- **Candy Eating**: Counter-example showing actions without larger causal structure
- **Tool vs. Resource**: Nails as resources that change state during use

## Notable Design Decisions:
- **Defeasible Goal Inference**: Goal-action relationships are non-monotonic with exceptions
- **Causal Structure Priority**: Temporal patterns only meaningful when revealing underlying causal goal structures
- **Resource Change Requirement**: Resources/tools distinguished by undergoing state changes
- **Agent Responsibility**: Clear assignment of agents to subplan components
- **Precondition Taxonomy**: Systematic classification into knowledge vs. resource types
- **Plan-Event Integration**: Plan structures map directly onto temporal event structures

## Theoretical Significance:
Chapter 35 addresses the fundamental challenge of connecting observable temporal behavior patterns with underlying mental goal structures. The key insight that "we have not really interpreted actions until we understand the causal structure implicit in them" drives the systematic analysis of how temporal patterns reveal causal plan structures.

The defeasible goal inference rules provide a principled approach to plan recognition, enabling observers to infer agent goals from conditional and repetitive action patterns. This bridges the gap between external behavioral observation and internal mental state attribution.

The comprehensive precondition taxonomy acknowledges that successful action requires both knowledge (knowing where tools are) and resources (having tools available), with resources distinguished by undergoing changes that are causally involved in effects.

The plan component framework enables systematic decomposition of complex behaviors into manageable elements with clear agent responsibilities, temporal structures, and causal relationships. This supports both individual planning and multi-agent coordination.

The integration of temporal event structures (whileDo, repeatUntil, conditional) with plan structures provides a unified framework for understanding how complex goal-directed behaviors emerge from systematic manipulation of causal relationships.

The chapter's 12 axioms establish plan element analysis as a systematic cognitive process enabling behavioral interpretation, plan recognition, and goal attribution through temporal pattern analysis and causal structure inference.

This represents one of the most comprehensive formal treatments of plan structure analysis in cognitive science, providing both psychological plausibility for human plan recognition and computational tractability for automated behavior understanding systems.
