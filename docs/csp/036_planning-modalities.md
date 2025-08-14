# Chapter 36: Planning Modalities

- **11 axioms total** covering planning activity, multi-agent planning modalities, and counterfactual planning
- **3 main sections**: The Activity of Planning, Planning Activity and Other Agents, Counterfactual Planning
- **Pure psychology** - systematic treatment of how agents plan individually and in social contexts with various goal relationships

## Key Features Identified:

### 1. **Foundation of Planning Activity** (Axioms 36.1-36.3):

#### **Core Planning Definition**:
- **Axiom 36.1**: `planning` - **reified mental activity**
  - Planning as sequence of envisionments of causal systems (plans)
  - Agent constructs plan sequences to achieve goals
  - Links envisioning theory with goal pursuit through reified activities
- **Axiom 36.2**: **Planning as subevent of goal pursuit**
  - All goal pursuit contains planning activities as components
  - Planning is necessary part of intentional behavior
  - Goal pursuit as "top-level intentional behavior" - what we do "going about our business"

#### **Human Planning Capability**:
- **Axiom 36.3**: **Universal human planning ability**
  - All persons with goals have ability to plan
  - Covers plan construction, adaptation, modification
  - Includes reactive planning, plan adaptation, elaborate from-scratch planning
  - Planning as fundamental human cognitive capacity

#### **Planning as Causal Knowledge Construction**:
- **Key Insight**: Planning uses causal knowledge to construct larger-scale causal knowledge
- **Decision Process**: Choosing which causal knowledge becomes part of "The Plan" being executed
- **Intentional Behavior**: Source of all intentional action through plan selection and execution

### 2. **Multi-Agent Planning Modalities** (Axioms 36.4-36.8):

#### **Collaborative Planning** (Axiom 36.4):
- **Complex definition** with shared goals and collective agency
- Both agents plan for same goal g
- Set {a1, a2} has g as collective goal
- Joint plan sequence combines contributions from both agents
- True collaboration requires shared goal commitment

#### **Assistive Planning** (Axiom 36.5):
- **Weaker collaboration** - helper doesn't need full goal commitment
- Agent a1 assists a2 by achieving subgoal g1 of a2's goal g
- a1 satisfied with subgoal achievement, not full goal
- Asymmetric relationship with clear helper-helpee roles

#### **Competitive Planning** (Axiom 36.6):
- **Zero-sum goal competition** - agents work toward mutually exclusive goals
- Each agent plans to achieve goal instances that exclude the other
- Competitive plans designed to succeed at other's expense
- Symmetric competition with both agents having same ultimate goal type

#### **Adversarial Planning** (Axiom 36.7):
- **Stronger than competition** - active goal negation
- Agent a1 has explicit goal that a2 NOT achieve goal g
- Goes beyond competition to active opposition
- Plans specifically designed to prevent other's success

#### **Counterplanning** (Axiom 36.8):
- **Strongest opposition** - active plan interference
- Agent a1 tries to discover and block a2's plans
- Involves plan recognition followed by plan disruption
- Most sophisticated multi-agent planning requiring theory of mind

### 3. **Counterfactual Planning Framework** (Axioms 36.9-36.11):

#### **Counterfactual Subgoals**:
- **Axiom 36.9**: `counterfactualSubgoal` - **moderate complexity framework**
  - Subgoal that doesn't hold and agent doesn't plan to make it hold
  - Enables "what if" reasoning without commitment to making assumptions true
  - Example: Planning for world peace assuming you're president (when you're not)

#### **Counterfactual Planning Process**:
- **Axiom 36.10**: `counterfactualPlanning` - plans with counterfactual assumptions
  - Last plan in sequence contains counterfactual subgoal
  - Agent continues planning despite knowing assumption is false
  - Enables hypothetical reasoning and contingency planning

#### **Other-Person Planning**:
- **Axiom 36.11**: `otherPersonPlanning` - **perspective-taking planning**
  - Special case of counterfactual planning
  - Agent a1 plans as if having property g1 that a2 actually has
  - "What would I do if I were Barack Obama?" type reasoning
  - Uses substitution framework from Chapter 7 for property transfer

## Technical Sophistication:
- **Reified Planning Activities**: Systematic treatment of planning as mental events with temporal structure
- **Multi-Agent Taxonomy**: Comprehensive classification from collaboration through adversarial competition
- **Counterfactual Reasoning**: Sophisticated treatment of hypothetical planning under false assumptions
- **Complex Quantification**: Multiple nested quantifiers over agents, plans, and sequences
- **Social Cognition Integration**: Planning modalities require theory of mind and other-agent modeling
- **Plan Sequence Management**: Complex handling of plan sequences and their combination in multi-agent contexts

## Complexity Distribution:
- **Simple**: 3 axioms (basic planning definition, planning-goal relationship, human ability)
- **Moderate**: 3 axioms (counterfactual subgoals, counterfactual planning, other-person planning)
- **Complex**: 5 axioms (all multi-agent planning modalities with intricate agent and plan coordination)

## Conceptual Importance:
This chapter provides crucial infrastructure for:
- **Social Planning**: Understanding how agents coordinate, compete, and conflict in planning
- **Hypothetical Reasoning**: Counterfactual planning for scenario analysis and contingency preparation
- **Theory of Mind**: Other-person planning requires understanding others' perspectives and capabilities
- **Collaborative Systems**: Formal foundations for team planning and distributed problem solving
- **Competitive Analysis**: Modeling strategic planning in competitive and adversarial contexts
- **Perspective Taking**: Understanding how agents model others' planning processes

## Cross-Chapter Connections:
- **Chapter 28 (Goals)**: Goal theory underlying all planning modalities and agent goal relationships
- **Chapter 31 (Plans)**: Basic plan structure and subgoal relationships used throughout
- **Chapter 24 (Envisioning)**: Planning as specialized envisioning activity creating plan sequences
- **Chapter 7 (Substitution)**: Substitution framework for other-person planning property transfer
- **Chapter 5 (Eventualities)**: Rexist predicate for confirming reality of substituted properties
- **Chapter 21 (Belief)**: Belief systems underlying counterfactual assumption management

## Applications Mentioned:
- **Collaborative Planning**: Team projects, joint ventures, shared goal achievement
- **Assistive Planning**: Helper-helpee relationships, mentoring, support systems
- **Competitive Planning**: Business competition, sports, resource allocation conflicts
- **Adversarial Planning**: Military strategy, legal opposition, direct goal blocking
- **Counterplanning**: Intelligence analysis, strategic defense, plan disruption
- **Counterfactual Planning**: Pat imagining world peace if she were president
- **Other-Person Planning**: "What would I do if I were Barack Obama?" perspective taking

## Notable Design Decisions:
- **Non-Defeasible Framework**: All planning modalities defined as strict logical relationships
- **Reified Activities**: Planning as mental events with temporal structure and causal relationships
- **Hierarchical Complexity**: From simple individual planning through complex adversarial counterplanning
- **Symmetric/Asymmetric Distinctions**: Collaborative and competitive planning (symmetric) vs. assistive and adversarial (asymmetric)
- **Counterfactual Integration**: Hypothetical planning integrated with basic planning framework
- **Social Cognition Requirements**: Multi-agent planning requires understanding others' mental states

## Theoretical Significance:
Chapter 36 addresses the fundamental challenge of planning in social contexts where multiple agents have potentially conflicting goals and planning processes. The systematic taxonomy from collaboration through adversarial counterplanning provides a comprehensive framework for understanding social strategic reasoning.

The counterfactual planning framework enables sophisticated hypothetical reasoning, allowing agents to explore alternative scenarios without committing to making false assumptions true. This supports contingency planning, scenario analysis, and perspective-taking reasoning essential for social interaction.

The reification of planning activities as mental events with temporal structure provides foundations for understanding planning processes as cognitive phenomena rather than just abstract logical relationships. This enables modeling of planning effort, planning coordination, and planning interference.

The multi-agent framework acknowledges that real planning occurs in social contexts where agents must consider others' goals, plans, and planning processes. The hierarchy from collaborative through adversarial planning captures the full spectrum of social goal relationships and their planning implications.

The integration with counterfactual reasoning enables "what if" planning essential for strategic thinking, risk assessment, and creative problem solving. Other-person planning specifically supports perspective-taking and empathetic reasoning crucial for social cooperation and competition.

The chapter's 11 axioms establish planning modalities as a systematic framework for understanding how intelligent agents navigate social environments with complex goal relationships, enabling both cooperative and competitive strategic reasoning.

This represents one of the most comprehensive formal treatments of social planning in cognitive science, providing both psychological plausibility for human strategic reasoning and computational foundations for multi-agent planning systems.
