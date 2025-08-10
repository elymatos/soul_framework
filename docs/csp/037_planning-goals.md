# Chapter 37: Planning Goals

- **31 axioms total** covering planning constraints, preferences, and goal optimization including value minimization/maximization, enabling/blocking events, and plan instantiation
- **6 main sections**: Constraints and Preferences, Including and Avoiding, Enabling and Blocking, Minimizing and Maximizing, Locating Instances for Plans, Maintaining Plan Progress
- **Pure psychology** - comprehensive treatment of how agents optimize plans through preferences, constraints, and goal management

## Key Features Identified:

### 1. **Fundamental Preference Framework** (Axioms 37.1-37.12):

#### **Plan Goal Addition and Constraints**:
- **Axiom 37.1**: `addGoalToPlan` - **moderate complexity goal integration**
  - Agent adds new goal to existing plan creating extended plan
  - Original plan becomes subplan of new plan through goal conjunction
  - Foundation for all plan optimization and constraint satisfaction
- **Axiom 37.2**: `planningConstraint` - **simple constraint definition**
  - Hard constraints as goals that must be added to plans
  - Constraints distinguished from soft preferences by necessity

#### **Preference Theory Foundation**:
- **Axiom 37.3**: **Preference type constraints** - agents, eventualities, and plans must be valid
- **Axiom 37.4**: **Defeasible incompatibility** - preferred options should be mutually exclusive
- **Axioms 37.5-37.7**: **Complex preference adoption rules**
  - **Axiom 37.5**: Preferred option adopted when possible
  - **Axiom 37.6**: Dispreferred option adopted when preferred impossible  
  - **Axiom 37.7**: Forced choice with "bad for agent" negation handling
  - All three axioms use complex world understanding and possibility checking

#### **Preference Ordering Properties**:
- **Axiom 37.8**: **Antisymmetry** - if prefer a to b, then not prefer b to a
- **Axiom 37.9**: **Transitivity** - if prefer a to b and b to c, then prefer a to c
- **Axiom 37.10**: `prefer0` - **simple preference over absence** 
- **Axioms 37.11-37.12**: **Value and cost-based preferences** - defeasible rules linking value/cost to preferences

### 2. **Action Inclusion and Avoidance** (Axiom 37.13):

#### **Action Selection Preferences**:
- **Axiom 37.13**: `preferAvoidAction` - avoiding action by preferring its negation
- Links to English phrases like "try to avoid" and "only as a last resort"
- Foundation for risk-averse planning and constraint-based action selection

### 3. **Event Enabling and Blocking Framework** (Axioms 37.14-37.23):

#### **Basic Event Preferences**:
- **Axiom 37.14**: `preferEnableEvent` - preferring actions that enable desired events
- **Axiom 37.15**: `preferBlockEvent` - preferring actions that prevent undesired events
- Foundation for proactive and protective planning strategies

#### **Threat Management**:
- **Axiom 37.16**: `preferEnableThreat` - malicious planning to "set one up to fail"
- **Axiom 37.17**: `preferBlockThreat` - defensive planning with "preemptive action"
- Integration with threat theory for risk assessment and management

#### **Physical Transfer Control**:
- **Axiom 37.18**: `preferEnableTransfer` - facilitating movement ("clear the path")
- **Axiom 37.19**: `preferBlockTransfer` - preventing movement ("obstruct")
- Uses reified movement predicates for spatial reasoning

#### **Multi-Agent Goal Management**:
- **Axiom 37.20**: `preferEnableAgency` - enabling other agents' actions
- **Axiom 37.21**: `preferBlockAgency` - blocking other agents' actions  
- **Axiom 37.22**: `preferEnableOtherAgentGoalSatisfaction` - helping others succeed
- **Axiom 37.23**: `preferBlockOtherAgentGoalSatisfaction` - preventing others' success
- Comprehensive framework for cooperative and competitive multi-agent planning

### 4. **Value Optimization Framework** (Axioms 37.24-37.27):

#### **Scale-Based Value Preferences**:
- **Axiom 37.24**: `preferMinimizeValue` - **moderate complexity minimization**
  - Systematic preference for lower scale positions across all value comparisons
  - Captures "less is more," "be conservative," "frugal" strategies
- **Axiom 37.25**: `preferMaximizeValue` - **moderate complexity maximization**
  - Systematic preference for higher scale positions across all value comparisons
  - Captures "bigger is better," "as much as possible," "be liberal with" strategies

#### **Duration-Specific Optimization**:
- **Axiom 37.26**: `preferMinimizeDuration` - time efficiency ("time is of the essence")
- **Axiom 37.27**: `preferMaximizeDuration` - time extension ("prolong," "make it last")
- Special cases of value optimization applied to temporal planning

### 5. **Plan Instantiation Framework** (Axioms 37.28-37.30):

#### **Entity Location and Identification**:
- **Axiom 37.28**: `locateThing` - **moderate complexity instantiation**
  - Adding identifying properties to unspecified plan entities
  - Uses `wh'` predicate for context-dependent identification
  - Captures "locate," "search for," "get one's hands on" activities
- **Axiom 37.29**: `locateAgent` - finding people for plan roles ("fill the job")
- **Axiom 37.30**: `locateLocation` - finding places for plan activities
- Systematic treatment of plan completion through parameter instantiation

### 6. **Plan Progress Maintenance** (Axiom 37.31):

#### **Progress Protection**:
- **Axiom 37.31**: `preferMaintainPlanProgress` - **moderate complexity progress preservation**
  - Systematic preference against undoing achieved subgoals
  - Captures "avoid backpedaling" and "keep moving forward" strategies
  - Acknowledges that progress protection can be overridden when necessary

## Technical Sophistication:
- **Comprehensive Preference Theory**: Complete treatment from basic preferences through complex optimization strategies
- **Defeasible Reasoning**: 6 axioms with `(etc)` for non-monotonic preference adoption and value reasoning
- **Scale Integration**: Heavy use of scale theory for value optimization and comparison
- **Multi-Agent Framework**: Systematic treatment of preferences involving other agents' goals and actions
- **Reified Mental States**: Extensive use of primed predicates for mental states and temporal relations
- **Plan Optimization**: Complete framework for transforming basic plans into optimized plans through preference satisfaction

## Complexity Distribution:
- **Simple**: 21 axioms (basic definitions, type constraints, simple preference relationships)
- **Moderate**: 7 axioms (goal addition, value optimization, instantiation, progress maintenance)
- **Complex**: 3 axioms (sophisticated preference adoption with world understanding and possibility checking)

## Conceptual Importance:
This chapter provides crucial infrastructure for:
- **Plan Optimization**: Moving beyond basic goal achievement to sophisticated plan refinement
- **Preference Satisfaction**: Systematic treatment of soft constraints and optimization objectives
- **Multi-Agent Planning**: Comprehensive framework for cooperative and competitive preference interactions
- **Resource Management**: Value-based optimization for time, cost, and other scarce resources
- **Risk Management**: Threat-aware planning with proactive and defensive strategies
- **Plan Completion**: Systematic approach to instantiating underspecified plan parameters

## Cross-Chapter Connections:
- **Chapter 31 (Plans)**: Uses basic plan structure for goal addition and subplan relationships
- **Chapter 28 (Goals)**: Goal theory underlying all preference and constraint relationships  
- **Chapter 12 (Scales)**: Scale theory for value optimization and preference comparison
- **Chapter 21 (Belief)**: World understanding for preference adoption possibility checking
- **Chapter 15 (Causality)**: Enable/prevent relationships for event control preferences
- **Chapter 5 (Eventualities)**: Basic eventuality framework for preference objects

## Applications Mentioned:
- **Planning Examples**: Sandwich buying (minimize time), driving (avoid traffic), hiking (flexible lunch location)
- **Preference Examples**: Aisle vs. window seat, apple vs. orange eating preferences
- **Forced Choice Examples**: Leg amputation vs. death from gangrene (bad option handling)
- **Value Examples**: "Less is more," "bigger is better," "time is of the essence," "make it last"
- **Multi-Agent Examples**: "Make someone happy," "thwart," "paralyze," "set one up to fail"
- **Instantiation Examples**: "Locate," "search for," "find someone to," "find a place where"
- **Progress Examples**: Block building (don't put things on cleared tops), "avoid backpedaling"

## Notable Design Decisions:
- **Hard vs. Soft Constraints**: Clear distinction between must-satisfy constraints and optional preferences
- **Preference as Goals-in-Waiting**: Preferences adopted as goals when possible but not required
- **Bad Option Negation**: When preferred option is bad, adopt negation of dispreferred option instead
- **Partial Ordering**: Preferences form antisymmetric, transitive partial order (not complete ordering)
- **Defeasible Adoption**: Preference adoption is non-monotonic with exceptions possible
- **Scale-Based Optimization**: Value preferences defined through systematic scale position comparison
- **Progress Protection**: Default preference for maintaining achieved subgoals with override capability

## Theoretical Significance:
Chapter 37 addresses the fundamental challenge of plan optimization under preferences and constraints. The distinction between hard constraints (must be satisfied) and soft preferences (adopted when possible) provides a principled approach to plan refinement and goal management.

The sophisticated preference adoption framework with world understanding and possibility checking enables realistic planning under uncertainty and resource constraints. The "goals-in-waiting" concept captures how preferences influence planning without overriding essential goals.

The comprehensive multi-agent preference framework enables modeling of cooperative, competitive, and adversarial planning relationships. Agents can prefer to help or hinder others, enable or block events, and optimize various aspects of their plans relative to other agents' activities.

The scale-based value optimization provides systematic foundations for resource management, time optimization, and cost-benefit analysis in planning. The framework handles both minimization and maximization preferences with consistent logical structure.

The plan instantiation framework addresses the practical challenge of completing underspecified plans by locating appropriate entities, agents, and locations. This bridges the gap between abstract planning and concrete execution.

The progress maintenance framework acknowledges that agents naturally prefer to protect achieved progress while allowing for necessary backtracking when circumstances require it.

The chapter's 31 axioms establish planning goal management as a sophisticated cognitive process involving preference satisfaction, constraint management, value optimization, and strategic interaction with other agents.

This represents one of the most comprehensive formal treatments of plan optimization in cognitive science, providing both psychological plausibility for human planning behavior and computational foundations for automated planning systems under preferences and constraints.
