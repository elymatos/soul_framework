# Chapter 31: Plans

- **53 axioms total** covering plans as mental entities, planning processes, strategies and tactics, executability, plan types, and helping
- **6 main sections**: Plans as Mental Entities, Planning Process, Strategies and Tactics, Executability and Complete Plans, Types of Plans, Helping  
- **Pure psychology** - comprehensive treatment of planning as central cognitive process connecting beliefs to actions

## Key Features Identified:

### 1. **Plans as Mental Constructs** (Axioms 31.1-31.9):
- **Axiom 31.1**: `connectedSubgoal*` - recursive transitive closure of subgoal relations ensuring plan connectivity
- **Axiom 31.2**: Plans as composite entities with components (subgoals) and relations (subgoal relations)
- **Axioms 31.3-31.4**: Convenience predicates for subgoal membership and plan component access
- **Axiom 31.5**: Subplans as recursive plan structures with subset component and relation requirements
- **Axiom 31.6**: Plan sequences capturing plan evolution through `change` relations between successive versions
- **Axiom 31.7**: Subgoal relations grounded in agent's causal beliefs about enablement/causation
- **Axioms 31.8-31.9**: Plans as envisioned causal systems, planning as variety of envisioning

### 2. **Belief System vs. Planning Module Architecture** (Axioms 31.10-31.22):
- **Axiom 31.10**: Desires as causal beliefs about what contributes to thriving (belief system)
- **Axioms 31.11-31.13**: `thePlan` as current plan for thriving, `thePlanseq` as temporal sequence of plans
- **Axiom 31.12**: Actions defeasibly caused by being subgoals in thePlan (using `etc` for non-monotonicity)
- **Axiom 31.14**: Intentions as action subgoals in thePlan that agent performs
- **Axioms 31.15-31.16**: `decideTo` as bridge from belief system to planning module via plan modification
- **Axiom 31.17**: `planTo` combining goal reasoning, plan sequence generation, and plan adoption
- **Axioms 31.18-31.22**: Will as mind-body interface with constraints, direct causation, and plan-based motivation

### 3. **Strategies and Tactics Framework** (Axioms 31.23-31.26):
- **Axioms 31.23-31.24**: Both strategies and tactics as parameterized plans (eventuality types with non-null parameter sets)
- **Axiom 31.25**: Hierarchical constraint - strategies not subplans of tactics
- **Axiom 31.26**: Defeasible rule that tactics serve strategic goals (subplans of strategies)
- **Distinction criteria**: Strategies longer-term/higher-level, tactics shorter-term/lower-level, tactical responses to immediate situations

### 4. **Executability Theory** (Axioms 31.27-31.30):
- **Axiom 31.27**: `executable1` extending basic executability to include physics and committed agents
- **Four executability sources**: Agent capability, natural occurrence, committed others, recursive subgoal executability
- **Axiom 31.28**: Commitment theory - defeasible guarantee that committed agents cause their assigned events
- **Axiom 31.29**: Complete plans defined by `executable1` top-level goals at current time
- **Axiom 31.30**: Partial plans as non-complete plans requiring further development

### 5. **Plan Type Taxonomy** (Axioms 31.31-31.49):

#### **Basic Plan Types**:
- **Axioms 31.31-31.32**: `planInstance` and `planInstancePlus` for type instantiation and elaboration
- **Axiom 31.33**: Normal plans as frequent/socially expected plan types

#### **Multi-Agent Plan Relations**:
- **Axiom 31.34**: Adversarial plans targeting negation of other agents' goals
- **Axiom 31.35**: Counterplans blocking adversarial plans through causal negation
- **Axiom 31.36-31.37**: Competitive goals/plans where mutual exclusivity creates side-effect opposition
- **Axiom 31.38**: Assistive plans adopting others' goals through causal goal transmission
- **Axiom 31.39**: Shared plans requiring group goal ownership, mutual belief in structure, and member commitment

#### **Cognitive Accessibility**:
- **Axiom 31.40**: Envisioned plans as causal systems modeled by other agents
- **Axiom 31.41**: Unknown plans not accessible to other agents
- **Axiom 31.42**: Nonconscious plans not accessible to planning agent themselves

#### **Execution Patterns**:
- **Axioms 31.43-31.44**: `do` for individual actions and entire plan execution
- **Axiom 31.45**: Reused plans through multiple distinct executed instances
- **Axioms 31.46-31.47**: Occasional and repeated plans as reused plan subtypes
- **Axiom 31.48**: Periodic plans with equal intervals between execution instances
- **Axiom 31.49**: Continuous plans executed over specified time intervals

### 6. **Helping Theory** (Axioms 31.50-31.53):
- **Axiom 31.50**: `help0` - basic causal involvement (unintentional helping)
- **Axiom 31.51**: `help1` - agent action in causal complex for another's goal (accidental helping)
- **Axiom 31.52**: `help2` - intentional action motivated by other's goal (deliberate helping)
- **Axiom 31.53**: `help3` - shared plan participation for other's goal (collaborative helping)

## Technical Sophistication:
- **Recursive Definitions**: `connectedSubgoal*` and `executable1` with proper termination conditions
- **Composite Entity Theory**: Plans as structured collections of subgoals and subgoal relations
- **Extensive Reification**: 19 reified predicates for mental processes and plan relationships
- **Defeasible Reasoning**: 6 axioms using `(etc)` for non-monotonic planning and action
- **Multi-Agent Coordination**: Sophisticated treatment of competitive, adversarial, assistive, and shared planning
- **Cognitive Architecture**: Clear separation between belief system (desires, causal beliefs) and planning module (intentions, decisions, actions)

## Complexity Distribution:
- **Simple**: 23 axioms (basic constraints, definitions, type relationships)
- **Moderate**: 21 axioms (standard planning definitions, multi-agent relations)
- **Complex**: 9 axioms (recursive definitions, plan sequences, shared plans, counterplans)

## Conceptual Importance:
This chapter provides the central framework for:
- **Action Generation**: Bridge from beliefs to actions through planning module
- **Mental Architecture**: Dual-system model separating knowledge from goal-directed behavior  
- **Multi-Agent Systems**: Comprehensive taxonomy of inter-agent plan relationships
- **Cognitive Psychology**: Formal treatment of intentions, decisions, and goal-directed behavior
- **AI Planning**: Executability theory, plan refinement, and social planning concepts
- **Philosophy of Action**: Will as mind-body interface, conscious vs. nonconscious planning

## Cross-Chapter Connections:
- **Chapter 5 (Eventualities)**: Uses eventuality framework and `Rexist` for plan component reality
- **Chapter 15 (Causality)**: Grounded in causal theory through `causallyInvolved` and `cause` relations
- **Chapter 21 (Belief)**: Belief system provides causal knowledge used by planning module
- **Chapter 24 (Envisioning)**: Plans as envisioned causal systems extending ECS theory
- **Chapter 28 (Goals)**: Goal theory provides foundation for plan target specification
- **Chapter 30 (Thriving)**: Ultimate goal of all planning, short-term vs. long-term considerations

## Applications Mentioned:
- **Strategic Planning**: Customer loyalty through coupons, military strategy vs. tactics
- **Action Control**: Trapeze artist coordination, sofa moving cooperation
- **Multi-Agent Scenarios**: McCain-Obama election dynamics, competitive sports
- **Helping Behaviors**: Elderly street crossing, Christmas present scenarios, key confiscation
- **Plan Types**: Periodic exercise routines, continuous monitoring, occasional celebrations

## Notable Design Decisions:
- **Composite Entity Framework**: Plans as structured objects rather than simple predicates
- **Dual Cognitive Architecture**: Clear separation between passive beliefs and active planning
- **Recursive Plan Structure**: Subplans allowing hierarchical plan organization
- **Social Plan Theory**: Extensive treatment of multi-agent planning relationships
- **Executability Integration**: Physics, agent capabilities, and social commitments in unified framework
- **Defeasible Planning**: Non-monotonic reasoning acknowledging plan modification and action variability
- **Will as Primitive**: Mind-body interface through willing rather than detailed neurophysiology

## Theoretical Significance:
Chapter 31 represents the culmination of the commonsense psychology framework by connecting abstract mental representations (beliefs, desires, goals) to concrete physical actions. The sophisticated treatment of multi-agent planning provides formal foundations for social psychology and organizational behavior.

The dual architecture of belief system vs. planning module offers a cognitively plausible model that avoids both the frame problem (passive belief systems) and the symbol grounding problem (active planning with environmental interaction). The extensive reification enables precise reasoning about planning processes themselves.

The integration of individual cognitive processes with multi-agent social dynamics through shared plans, commitments, and helping relationships demonstrates how formal logic can capture both personal decision-making and interpersonal coordination. This provides foundations for both AI systems design and human social interaction modeling.

The chapter's 53 axioms establish planning as the central process transforming mental representations into physical actions, making it essential for any complete theory of agent behavior in both individual and social contexts.
