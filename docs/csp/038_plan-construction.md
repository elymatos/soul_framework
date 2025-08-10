# Chapter 38: Plan Construction

- **26 axioms total** covering planning processes from instantiation to scratch planning, process control, subprocesses, obstacles, and candidate plan selection
- **5 main sections**: Planning by Instantiation vs From Scratch, Process Control, Subprocess Activity, Obstacles, Candidate Selection
- **Pure psychology** - comprehensive treatment of how agents construct, control, and select plans through systematic construction processes

## Key Features Identified:

### 1. **Planning Construction Methods** (Axioms 38.1-38.4):

#### **Planning by Instantiation vs From Scratch**:
- **Axiom 38.1**: `planningByInstantiating` - **moderate complexity habitual planning**
  - Only way between successive plans is instantiating types in earlier plan
  - Habitual action like restaurant ordering: taking general script and instantiating with specific waiter and menu
  - Near-automatic planning using well-established behavioral patterns
- **Axiom 38.2**: `planningFromScratch` - **complex constructive planning**
  - Mining causal knowledge to build plans rather than instantiating scripts
  - Constructing p2 from p1 by adding subgoal e2 based on causal belief about e2's involvement in e1
  - Complex nested quantification over plan elements, relations, and causal beliefs
  - Foundation for creative and adaptive planning when scripts don't exist

#### **Single vs Multiple Goal Planning**:
- **Axiom 38.3**: `singleGoalPlanning` - **simple goal focus**
  - Planning to achieve single non-conjunctive goals
  - Foundation for focused, undivided planning attention
- **Axiom 38.4**: `multipleGoalPlanning` - **simple goal multiplexing**
  - Planning to achieve conjunctive goals with multiple components
  - Enables complex goal achievement with multiple simultaneous objectives

### 2. **Planning Process Control** (Axioms 38.5-38.9):

#### **Basic Process Control Operations**:
- **Axiom 38.5**: `suspendPlanning` - **simple process suspension**
  - "Hold off on the details," "put aside the plan"
  - Temporal interruption of planning process with intention to resume
- **Axiom 38.6**: `resumePlanning` - **simple process continuation**  
  - "Finish the plan," "take up the plan again"
  - Reactivating suspended planning processes
- **Axiom 38.7**: `abortPlanning` - **simple process termination**
  - "Stop planning," "give up on the plan"
  - Permanent abandonment of planning process

#### **Planning Process Restart**:
- **Axiom 38.8**: `replan` - **simple process restart**
  - "Start from scratch," "back to the drawing board," "try a different approach"  
  - Restarting planning process for same goal with fresh approach
- **Axiom 38.9**: `replanSubplan` - **moderate complexity partial restart**
  - "Patch the plan," "fix part of the plan," "rework a step"
  - Restarting subplan for subgoal while maintaining overall plan structure

### 3. **Plan Construction Operations** (Axioms 38.10-38.15):

#### **Plan Development Operations**:
- **Axiom 38.10**: `specifyPlan` - **simple plan elaboration**
  - "Flesh out the details," "be specific about the plan"
  - McDonald's example: general eating plan becomes specific register choice
  - Adding detail to plans through `planInstancePlus` relationships
- **Axiom 38.11**: `addSubplan` - **complex subplan integration**
  - "Add a step," "addendum to the plan," "put a part in the plan"
  - Complex set operations to add subplan p3 achieving subgoal g1 of g
  - Sophisticated plan structure modification with constraint satisfaction
- **Axiom 38.12**: `removeSubplan` - **complex subplan elimination**
  - "Remove a step," "simplify the plan"
  - Mirror of addSubplan with reversed set difference operations
  - Systematic plan simplification while maintaining goal achievement

#### **Plan Structure Operations**:
- **Axiom 38.13**: `identifyPrecondition` - **moderate complexity precondition discovery**
  - "Realize we first need to," "see a necessary step"
  - Discovering that goals have preconditions requiring additional subgoals
  - Uses `enable` relation to identify necessary prerequisites
- **Axiom 38.14**: `selectSubplan` - **moderate complexity alternative selection**
  - "Choose a step," "select an action"
  - Replacing disjunction with one of its disjuncts
  - Converting alternative possibilities into specific choices
- **Axiom 38.15**: `orderSubplans` - **moderate complexity temporal ordering**
  - "First things first," "figure out the order"
  - Adding temporal constraints through `before` relations between subgoals
  - Temporal planning and sequencing operations

### 4. **Obstacle Management Framework** (Axioms 38.16-38.19):

#### **Obstacle Theory**:
- **Axiom 38.16**: `obstacle` - **moderate complexity impediment definition**
  - Something that causes goal not to occur
  - "Barrier," "impediment," "impasse"
  - Entity x or its property e1 can cause goal negation
- **Axiom 38.17**: `overcomeObstacle` - **simple obstacle elimination**
  - Causing obstacle not to occur in way consistent with goal achievement
  - Obstacle elimination as precondition satisfaction
  - Agent causes obstacle not to exist as obstacle

#### **Planning Problems and Options**:
- **Axiom 38.18**: `planningProblem` - **simple problem identification**
  - Obstacles as problems requiring overcoming in plans
  - "Challenge," "dilemma," "sticky situation," "something must be done"
  - Integration of obstacle theory with plan construction
- **Axiom 38.19**: **Planning option from obstacle overcoming** - simple option generation
  - Overcoming obstacles becomes planning options
  - Systematic generation of plan alternatives from problem analysis

### 5. **Candidate Plan Selection** (Axioms 38.20-38.26):

#### **Candidate Plan Framework**:
- **Axiom 38.20**: `candidatePlan` - **moderate complexity plan qualification**
  - Plan in focus that is executable now with respect to constraints
  - Agents construct multiple possible plans then select among candidates
  - Integration of attention, executability, and temporal constraints
- **Axiom 38.21**: `successfulPlanning` - **simple planning success**
  - Planning activity that produces candidate plans
  - Successful completion of plan construction process
- **Axiom 38.22**: `planningFailure` - **simple planning failure**
  - Planning without producing viable candidate plans
  - Recognition of planning process breakdown

#### **Plan Assessment and Selection**:
- **Axiom 38.23**: `assessPlan` - **simple plan evaluation**
  - Coming to belief about plan's likelihood of achieving goal
  - Graded belief in plan success using degree d
  - Foundation for rational plan selection
- **Axiom 38.24**: `selectCandidatePlan` - **complex rational selection**
  - Selecting plan assessed as most likely to succeed
  - Complex optimization over all candidate plans with assessment comparison
  - Rational decision making through comparative evaluation
- **Axiom 38.25**: **Selected plan scheduling** - simple scheduling integration
  - Selected plans are assigned execution times
  - Bridge from planning to scheduling and execution
- **Axiom 38.26**: **Non-selected candidate exclusion** - simple exclusion principle
  - Candidates not selected will not be executed
  - Ensures single plan selection for execution

## Technical Sophistication:
- **Planning Process Control**: Comprehensive framework for suspend/resume/abort/restart operations with temporal anchoring
- **Plan Construction Operations**: Systematic treatment of plan development from specification through structural modification
- **Obstacle Integration**: Complete obstacle framework integrated with planning problem identification and resolution
- **Rational Selection**: Sophisticated candidate assessment and selection with optimization principles
- **Reified Planning Activities**: Extensive use of primed predicates for planning processes and causal relations
- **Process Integration**: Integration with general process control operations and forward references to scheduling

## Complexity Distribution:
- **Simple**: 15 axioms (basic definitions, process control, problem identification)
- **Moderate**: 8 axioms (planning methods, precondition discovery, candidate management)
- **Complex**: 3 axioms (from-scratch planning, subplan operations, rational selection)

## Conceptual Importance:
This chapter provides crucial infrastructure for:
- **Planning Process Management**: Complete framework for controlling planning activities through their lifecycle
- **Plan Development**: Systematic approach to constructing and refining plans through multiple operations
- **Problem Solving**: Integration of obstacle identification and resolution with plan construction
- **Rational Planning**: Assessment-based selection among alternative candidate plans
- **Bridge to Execution**: Connection from plan construction through scheduling to execution systems
- **Creative vs Routine Planning**: Distinction between script instantiation and from-scratch construction

## Cross-Chapter Connections:
- **Chapter 31 (Plans)**: Uses basic plan structure, subgoals, and plan relationships throughout
- **Chapter 28 (Goals)**: Goal theory underlying all planning construction and selection processes
- **Chapter 36 (Planning Modalities)**: Basic planning activity extended with construction-specific operations
- **Chapter 37 (Planning Goals)**: Preference and constraint satisfaction integrated with construction processes
- **Chapter 15 (Causality)**: Causal reasoning fundamental to from-scratch planning and obstacle management
- **Chapter 21 (Belief)**: Belief systems underlying plan assessment and causal reasoning
- **Chapter 42 (Plan Scheduling)**: Forward reference to scheduling operations for selected plans

## Applications Mentioned:
- **Planning by Instantiation**: Restaurant ordering (general script → specific waiter and menu)
- **Planning From Scratch**: Creative problem solving when no scripts available
- **Process Control**: "Hold off on details," "back to drawing board," "patch the plan"
- **Plan Operations**: McDonald's planning (general eating → specific register choice)
- **Obstacle Examples**: "Challenge," "dilemma," "sticky situation," "something must be done"
- **Selection Examples**: Multiple candidate plan construction and assessment for goal achievement

## Notable Design Decisions:
- **Script vs Creative Distinction**: Clear separation between habitual instantiation and creative from-scratch planning
- **Complete Process Control**: Comprehensive suspend/resume/abort/restart framework with temporal anchoring
- **Modular Construction Operations**: Systematic treatment of individual plan modification operations
- **Obstacle as Planning Problem**: Integration of impediment theory with planning problem framework
- **Rational Selection Process**: Assessment-based optimization for candidate plan selection
- **No Defeasible Rules**: All construction processes defined as strict logical relationships
- **Reified Activities**: Planning processes as mental events with causal relationships and temporal structure

## Theoretical Significance:
Chapter 38 addresses the fundamental challenge of how agents actually construct plans from initial goals through final candidate selection. The distinction between planning by instantiation (using familiar scripts) and planning from scratch (creative construction using causal knowledge) captures essential differences in human planning behavior.

The comprehensive process control framework acknowledges that planning is not a linear process but involves suspension, resumption, restart, and abortion as circumstances change. The modular construction operations provide systematic foundations for understanding how plans develop through specification, subplan addition/removal, precondition identification, alternative selection, and temporal ordering.

The obstacle management framework integrates impediment theory with planning problem identification, enabling systematic approach to problem-solving during plan construction. The rational candidate selection process provides foundations for understanding how agents choose among multiple possible plans through assessment and optimization.

The integration of planning construction with process control, obstacle management, and rational selection creates a comprehensive framework for understanding planning as a complex cognitive process involving multiple interacting components rather than simple goal-to-action transformation.

The chapter's 26 axioms establish plan construction as sophisticated cognitive architecture involving script instantiation, creative construction, process management, problem solving, and rational decision making under uncertainty.

This represents one of the most comprehensive formal treatments of planning construction processes in cognitive science, providing both psychological plausibility for human planning behavior and computational foundations for automated planning systems that must construct, control, and select plans under realistic constraints.
