# CC-02: Implementation Plan - Cortical Column-Based Cognitive Framework

## Document Information
- **Document**: CC-02-Implementation-Plan.md
- **Date**: 2025-09-23
- **Status**: Revised Implementation Plan 
- **Related**: CC-01.md (Comprehensive Documentation)

---

## Current Project State Analysis

The current SOUL Framework contains:

### Available Infrastructure
- ✅ **Laravel 12** with Octane, Reverb, and modern stack
- ✅ **Custom Query Builder**: `app/Database/Criteria.php` for **relational database** operations (MariaDB)
- ✅ **UI Components**: Extensive Blade components in `app/UI/components/`
- ✅ **View Components**: PHP classes in `app/View/_Components/`
- ✅ **Database Migrations**: Basic Laravel migration system for relational data
- ✅ **Neo4j Support**: Configured for **graph database** operations (cortical networks)
- ✅ **User Model**: Basic authentication infrastructure

### Database Architecture Clarification
- **Relational Database (MariaDB)**: Accessed via `Criteria.php` for structured data (users, metadata, configurations)
- **Graph Database (Neo4j)**: Requires new "Criteria-like" implementation for network operations (cortical columns, connections, activations)

---

## Implementation Strategy ("Baby Steps")

### Development Philosophy
The implementation follows a **test-driven infrastructure approach** where each phase builds upon and validates the previous infrastructure through interactive UI components. This ensures:

- **Progressive Validation**: Each step tests the previous infrastructure before adding complexity
- **Visual Feedback**: UI components provide immediate feedback during development
- **Interactive Testing**: Manual operations validate automated systems before deployment
- **Incremental Complexity**: Start with simple CRUD operations, build to complex reasoning

### Phase 1: Foundation Infrastructure (Weeks 1-3)
**Goal**: Build core cognitive framework infrastructure from scratch

#### 1.1 Create Graph Database Query Builder
- **Task**: Build "Criteria-like" implementation for Neo4j operations
- **Architecture**: Similar interface to `Criteria.php` but for graph database
- **Files to Create**:
  - `app/Database/GraphCriteria.php` (Neo4j query builder)
  - `app/Services/Neo4j/ConnectionService.php`
  - `app/Services/Neo4j/QueryBuilderService.php`

#### 1.2 Create Core Service Layer
- **Task**: Build foundational services using new `GraphCriteria` for network operations
- **Architecture**: Clean service layer following Laravel 12 patterns
- **Files to Create**:
  - `app/Services/CorticalNetwork/NetworkService.php`
  - `app/Services/CorticalNetwork/ActivationService.php`
  - `app/Services/CorticalNetwork/DatabaseService.php`

#### 1.3 Implement Repository Pattern for Cognitive Framework
- **Task**: Create repository layer for cortical columns
- **Architecture**: Use `GraphCriteria` for network data, `Criteria` for metadata
- **Files to Create**:
  - `app/Repositories/CorticalColumnRepository.php`
  - `app/Repositories/ColumnConnectionRepository.php`
  - `app/Repositories/ActivationStateRepository.php`

#### 1.4 Database Schema Design
- **Task**: Define data storage strategy for cortical column structure
  - Layer 4: Input (receives signals from other columns)
  - Layers 2/3: Feature Processing (frame elements/slots)
  - Layer 5: Output (cardinal nodes for concept broadcasting)
- **Graph Database (Neo4j)**: Network structure, connections, activations
- **Relational Database (MariaDB)**: Metadata, configurations, user data
- **Files to Create**:
  - `database/neo4j/cortical_network_schema.cypher` (Neo4j schema)
  - `database/migrations/create_cortical_metadata_table.php` (relational metadata)
  - `database/migrations/create_network_configurations_table.php` (settings)

### Phase 2: Infrastructure Testing & UI Development (Weeks 4-6)
**Goal**: Test Phase 1 infrastructure through progressive UI development and validation

#### 2.1 Build UI Interface for Graph Database Operations
- **Task**: Create interface to manually create nodes and relations for testing
- **Purpose**: Essential for testing the `GraphCriteria` infrastructure built in Phase 1
- **Template Base**: Follow existing GraphEditor/GraphViewer patterns
- **Files to Create**:
  - `app/UI/views/CorticalNetwork/editor.blade.php` (based on existing GraphEditor)
  - `app/UI/views/CorticalNetwork/create-node.blade.php`
  - `app/UI/views/CorticalNetwork/create-connection.blade.php`
  - Controllers for handling CRUD operations via `GraphCriteria`
  - Forms for cortical column layer configuration (Layer 4, Layers 2/3, Layer 5)

#### 2.2 Basic Network Visualization
- **Task**: Adapt existing `app/UI/views/GraphViewer/viewer.blade.php` for cortical networks
- **Purpose**: Visualize the network structure created in 2.1
- **Technical**: Leverage existing vis.js visualization patterns
- **Files to Create/Modify**:
  - `app/UI/views/CorticalNetwork/viewer.blade.php` (adapted from GraphViewer template)
  - Cortical-specific visualization configurations (layer differentiation)
  - Updated legend and controls for cortical column types
  - Integration with `GraphCriteria` for data loading from Neo4j
  - Real-time graph updates for network changes

#### 2.3 Spread Activation Implementation with UI Testing
- **Task**: Implement core activation algorithms with visual testing interface
- **Purpose**: Test the core cognitive operations with immediate visual feedback
- **Integration**: Build activation controls into the network viewer
- **Files to Create**:
  - `app/Services/CorticalNetwork/SpreadActivationService.php`
  - `app/Services/CorticalNetwork/ThresholdService.php`
  - UI controls in viewer for triggering activation manually
  - Real-time visualization of activation propagation
  - Activation state display and monitoring tools

#### 2.4 Agent Architecture Foundation with Simple Test Agents
- **Task**: Create simple test agents for validating network functionality
- **Purpose**: Test agent coordination and network manipulation capabilities
- **Architecture**: Follow Minsky's "Society of Mind" principles with testable implementations
- **Files to Create**:
  - `app/Services/Agents/BaseAgent.php` (interface/abstract)
  - `app/Services/Agents/TestAgent.php` (simple test implementation)
  - `app/Services/Agents/NetworkAgent.php` (basic network operations)
  - `app/Services/Agents/CoordinationService.php`
  - UI interface for running and monitoring agent operations
  - Agent testing dashboard integrated with network viewer

### Phase 3: Frame Integration (Weeks 7-9)
**Goal**: Implement frame-based knowledge representation

#### 3.1 Frame Structure Implementation
- **Task**: Create frame system compatible with cortical columns
- **Architecture**: Map frames to three-layer column structure
- **Files to Create**:
  - `app/Repositories/FrameRepository.php` (new, from scratch)
  - `app/Repositories/FrameElementRepository.php` (new)
  - `app/Services/CorticalNetwork/FrameService.php`

#### 3.2 Frame-Column Integration
- **Task**: Connect frame representations to cortical network
- **Technical**: Bidirectional mapping between frames and columns
- **Files to Create**:
  - `app/Services/CorticalNetwork/FrameIntegrationService.php`
  - Database migrations for frame-column relationships

### Phase 4: Language Interface (Weeks 10-12)
**Goal**: Add LLM-based natural language processing

#### 4.1 LLM Integration Service
- **Task**: Build natural language interface from scratch
- **Features**: Parsing user inputs, generating network-compatible structures
- **Files to Create**:
  - `app/Services/Language/LLMInterfaceService.php`
  - `app/Services/Language/NetworkTranslationService.php`
  - `app/Services/Language/ValidationService.php`

#### 4.2 Interactive Network Building
- **Task**: LLM-guided concept creation and expansion
- **Architecture**: Conversation-based network development
- **Files to Create**:
  - `app/Services/Language/ConceptBuilderService.php`
  - `app/Services/Language/ConversationService.php`

### Phase 5: Advanced Cognitive Operations (Weeks 13-15)
**Goal**: Implement sophisticated reasoning capabilities

#### 5.1 Minsky's Accommodation Strategies
- **Task**: Implement cognitive accommodation mechanisms
- **Features**: Matching, excuse, advice, summary strategies
- **Files to Create**:
  - `app/Services/Reasoning/AccommodationService.php`
  - `app/Services/Agents/ReasoningAgent.php`

#### 5.2 Image Schema Foundation
- **Task**: Manual implementation of foundational spatial-semantic structures
- **Schemas**: FORCE, REGION, OBJECT, POINT, CURVE, AXIS, MOVEMENT
- **Files to Create**:
  - `app/Services/ImageSchemas/ImageSchemaService.php`
  - `app/Services/ImageSchemas/SpatialReasoningService.php`
  - `database/seeders/ImageSchemaSeeder.php`

---

## Technical Architecture

### Database Design
- **Hybrid Architecture**: Relational + Graph databases for optimal performance
- **Relational Database (MariaDB)**:
  - Metadata, configurations, user data
  - Accessed via `app/Database/Criteria.php`
- **Graph Database (Neo4j)**:
  - Cortical network structure, connections, activations
  - Accessed via new `app/Database/GraphCriteria.php`
- **No Eloquent Models**: Pure repository pattern with dual query builders

### Service Layer Architecture
```
app/Services/
├── CorticalNetwork/          # Core network operations
│   ├── NetworkService.php
│   ├── ActivationService.php
│   └── VisualizationService.php
├── Agents/                   # Cognitive agents
│   ├── BaseAgent.php
│   └── CoordinationService.php
├── Language/                 # LLM interface
│   └── LLMInterfaceService.php
└── Reasoning/                # Advanced reasoning
    └── AccommodationService.php
```

### Repository Layer
```
app/Repositories/
├── CorticalColumnRepository.php
├── FrameRepository.php       # New implementation
└── ColumnConnectionRepository.php
```

### UI Integration
- **Leverage Existing Templates**:
  - `app/UI/views/GraphViewer/viewer.blade.php` as base for cortical network viewer
  - `app/UI/views/GraphEditor/main.blade.php` as base for network editor
  - Existing vis.js configurations and styling patterns
- **Extend Components**: Add cognitive-specific UI elements for cortical columns
- **Visualization**: Adapt existing graph components for cortical network specifics
- **Pattern Consistency**: Follow established UI/UX patterns from existing graph tools

---

## Implementation Considerations

### Database Strategy
- **Hybrid Storage Strategy**:
  - **Relational (MariaDB)**: Metadata, configurations, user management
  - **Graph (Neo4j)**: Network topology, cortical columns, connections, activations
- **Query Optimization**:
  - `Criteria` builder for relational operations
  - `GraphCriteria` builder for graph operations
- **Schema Management**:
  - Laravel migrations for relational schema
  - Cypher scripts for Neo4j schema

### Performance Considerations
- **Activation Propagation**: Efficient algorithms for spread activation
- **Memory Management**: Careful handling of large network states
- **Caching Strategy**: Cache frequently accessed network patterns
- **Real-time Updates**: Laravel Reverb for network state broadcasting

### Integration with Existing Infrastructure
- **UI Components**: Reuse existing Blade components where possible
- **Authentication**: Leverage existing User model and middleware
- **Database**: Use configured database connections
- **Asset Pipeline**: Integrate with existing Vite build system

---

## Expected Deliverables by Phase

### Phase 1: Foundation
- Complete service layer architecture
- Repository pattern using `Criteria`
- Basic cortical column database schema
- Network service foundation

### Phase 2: Infrastructure Testing & UI
- UI interface for creating/editing cortical columns and connections
- Network visualization adapted from existing GraphViewer template
- Spread activation system with visual testing interface
- Basic agent architecture with simple test agents
- Complete testing framework for Phase 1 infrastructure

### Phase 3: Frame Integration
- Frame-based knowledge representation
- Integration with cortical columns
- Enhanced visualization capabilities
- Frame creation and manipulation

### Phase 4: Language Interface
- LLM-powered natural language processing
- Interactive network building
- Conversation-based system interaction
- Network expansion through language

### Phase 5: Advanced Operations
- Sophisticated reasoning capabilities
- Image schema implementations
- Complete cognitive framework
- Production-ready system

---

## Risk Mitigation

### Technical Risks
- **Query Performance**:
  - Optimization of `Criteria`-based queries for relational data
  - Optimization of `GraphCriteria`-based queries for network operations
- **Memory Usage**: Careful management of large network activation states
- **LLM Integration**: Robust error handling and fallback mechanisms
- **Database Coordination**: Ensuring consistency between relational and graph data

### Development Risks
- **Scope Creep**: Strict adherence to phase-by-phase development
- **Complexity Management**: Regular refactoring and code review
- **Testing Coverage**: Comprehensive testing at each phase

---

## Success Metrics

### Functional Metrics
- **Network Operations**: Successful spread activation across network
- **Reasoning Quality**: Accurate inference and accommodation
- **Language Interface**: Effective natural language understanding
- **System Integration**: Seamless operation with existing infrastructure

### Technical Metrics
- **Performance**: Sub-second response times for network operations
- **Scalability**: Support for networks with thousands of nodes
- **Reliability**: 99.9% uptime and error-free operation
- **Code Quality**: Comprehensive test coverage and clean architecture

---

## Next Steps

1. **Environment Verification**: Confirm Neo4j and database configuration
2. **Phase 1 Initiation**: Begin with NetworkService and repository creation
3. **Database Schema**: Create initial cortical column migrations
4. **Service Foundation**: Build core service layer using `Criteria`
5. **Testing Framework**: Establish comprehensive testing for each component

This revised plan reflects the current project state and provides a clear path forward for implementing the cortical column-based cognitive framework using the available infrastructure.
