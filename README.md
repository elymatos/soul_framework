# SOUL Framework 0.1 [Docker Container]
**Project SOUL: A Conceptual Framework for Meaning Representation**

#### **1. Core Conception and Objectives**

Projeto SOUL is an initiative to develop a layered, concept-based framework for representing meaning that is grounded in the principles of Cognitive Linguistics. The primary goal is to create a model that not only stores information but also supports the dynamic and creative processes of human thought, such as concept creation, metaphor, and common-sense reasoning. This framework will ultimately be applicable to natural language understanding and generation tasks, and its final form will align with the principles of Frame Semantics.

The project's three main objectives are:

1. **Theoretical Integration**: To form a coherent framework by integrating theories from Cognitive Linguistics, particularly Image Schemas and Conceptual Blending, with Common Sense Psychology.

2. **Systemic Conceptual Network**: To model concepts in a systemic network, or an ontology, that allows for logical and psychologically plausible inferences. This network will use image schema-based metaphors, event structures, and causal chains.

3. **Scenario Representation**: To use this conceptual network to represent "scenarios" for specific domains, which are composed of semantic frames. The framework also emphasizes how meaning can be projected between different domains.


#### **2. Theoretical Foundation and Key Decisions**

The framework is built on several key theoretical commitments that guide its structure:

- **Primitives vs. Derived Concepts**: The model distinguishes between **primitive** concepts, which are axiomatic and irreducible, and **derived** concepts, which are built from the primitives through combination and aggregation. This establishes a clear hierarchical foundation for the conceptual system.

- **Layered Hierarchy**: A crucial decision is to place Image Schemas as the most fundamental primitives, as they are rooted in direct bodily, sensory-motor experience. Certain abstract concepts from Commonsense Psychology (CSP), such as **EMOTION** and **STATE**, are also considered primitives because they are irreducible in their own right. The more complex concepts of CSP, like Beliefs and Intentions, are understood as derived concepts built upon this foundation.

- **Linguistic Alignment**: The model assumes that every concept, whether primitive or derived, corresponds to some linguistic form. The ultimate goal is to represent the conceptualization of a linguistic unit (LU) within the framework using a frame structure.


#### **3. Structure of the Conceptual Network**

The conceptual network is a dynamic and interconnected system of concepts, represented as frames. Its structure is defined by the following elements:

- **Primitive Concepts**:

    - **Image Schemas**: A set of foundational primitives including **FORCE**, **REGION**, **OBJECT**, **POINT**, **CURVE**, **AXIS**, and **MOVEMENT**.

    - **Commonsense Psychology Primitives**: A set of abstract primitives such as **EMOTION**, **NUMBER**, **STATE**, **CAUSE**, and **SCALE**.

- **High-Level Meta-Schemas**: The entire network is organized around four fundamental, high-level elements: **ENTITY**, **STATE**, **PROCESS**, and **CHANGE**. All primitives and derived concepts are systematically integrated into this structure.

    - An **ENTITY** is conceptualized via topological schemas like **POINT** or **OBJECT**.

    - A **STATE** is a primitive that describes a stable condition.

    - A **PROCESS** is driven by a primitive **FORCE** and structured by concepts like **PATH** and **CAUSE**.

    - A **CHANGE** is the result of a PROCESS altering a STATE.

- **Structural Schemas**: The relationships between concepts in the network are formalized by "structural schemas". These schemas define the links (or Frame Elements) that make the network functional:

    - **CLASS & HIERARCHY**: For representing `is-a` and `part-whole` relationships.

    - **AXIS & SCALE**: For representing ordered relationships, similarities, and comparisons.

    - **RADIAL**: For modeling prototype effects and polysemy.

    - **QUALIA**: For capturing the generative internal structure of a concept, defining its formal, constitutive, telic, and agentive roles.


#### **4. Proposed Computational Implementation**

To bring this theoretical framework to life, a prototype computational model is necessary. Based on our discussions, the ideal structure for this model is a **graph database** that represents the conceptual network as a labeled property graph.

This choice is motivated by the following requirements and capabilities:

- **Graph Structure**: Concepts are naturally represented as **nodes**, and the relationships between them (the structural schemas and Frame Elements) are represented as **labeled edges**.

- **Dynamic Operations**: The dynamic nature of the framework—including `construal operations` and conceptual blending—can be implemented as functions that traverse and manipulate the graph structure.

- **Non-Formal Logic Reasoning**: The system can perform `spreading activation`-based inference, which is a powerful technique for modeling common-sense reasoning and finding the "most plausible" explanations without relying on the rigid rules of formal logic.

- **Flexibility and Scalability**: A graph database provides a robust backend for storing the network's data, allowing for the use of an intuitive graphical interface for modeling and separate agents for performing specific reasoning tasks.

- **Data Portability**: The network's structure can be easily exported in formats like JSON or YAML, meeting the requirement for a textual representation.


This holistic approach integrates linguistic, psychological, and computational principles into a unified framework for representing and reasoning about meaning.
