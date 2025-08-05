# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SOUL Framework 0.1 is a sophisticated cognitive AI system that implements a computational version of Marvin Minsky's Frame Theory and Charles Fillmore's Frame Semantics. Built on Laravel PHP with Neo4j graph database, it provides a layered, concept-based framework for representing meaning, grounded in Cognitive Linguistics principles including Image Schemas and Conceptual Blending theory.

**Current Status**: Production-ready cognitive framework with comprehensive implementation including:
- Complete Frame/FrameInstance/FrameElement architecture
- MindService implementing "Society of Mind" coordination 
- SOUL ResourceController with full CRUD operations
- Neo4j graph database integration
- Agent communication system
- Cognitive processing pipeline with spreading activation
- Graph visualization and data export/import capabilities

## Development Environment

### Docker Setup
```bash
# Development environment with Neo4j
docker compose -f docker-compose-dev.yml up --build

# Production environment  
docker compose up --build

# Access the application at http://localhost:8001
# Access Neo4j Browser at http://localhost:7474
# Default Laravel credentials: user=webtool, password=test
# Default Neo4j credentials: username=neo4j, password=secret
```

### Asset Compilation
```bash
# Development with hot reload
npm run dev

# Build for production  
npm run build
```

### Laravel Commands
```bash
# Run migrations
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Generate application key
php artisan key:generate

# Check application status
php artisan about
```

### Testing & Code Quality
```bash
# Run PHPUnit tests
php artisan test
# or
vendor/bin/phpunit

# Laravel Pint for code formatting
vendor/bin/pint
```

## SOUL Framework Architecture

### Theoretical Foundation

The SOUL (Structured Object-Oriented Understanding Language) framework implements:

- **Frames**: Knowledge structures representing stereotyped situations (Minsky's approach)
- **Frame Semantics**: Linguistic meaning through encyclopedic knowledge (Fillmore's approach)  
- **Society of Mind**: Intelligence emerges from interaction of simple agents
- **Dynamic Frame Elements**: Programmatically added attributes for flexible representation
- **Non-hierarchical Structure**: Frames connected through relations, not inheritance

### Key Design Principles

1. All concepts (primitive, derived, image-schematic, CSP, linguistic, entities, relations) are represented as Frames
2. Frame instances operate independently after instantiation
3. Connections between frames occur through Frame Element relations
4. Agents (methods) within frames can communicate directly via Mind service coordination
5. Relations themselves are frames with Figure/Ground structure

### Core Architecture

```
App\Soul\
├── Frame.php                  # Abstract base class for all frames
├── FrameInstance.php         # Independent cognitive units
├── FrameElement.php          # FE definition templates
├── FrameElementInstance.php  # FE instances with values/relations
├── RelationFrame.php         # Abstract relation frame
├── Relations/               # Specific relation implementations
│   ├── IsARelation.php
│   ├── CausesRelation.php
│   └── SharedSlotRelation.php
├── Services/
│   └── MindService.php       # Central coordinator ("Society of Mind")
└── Contracts/               # Interfaces
    ├── FrameDefinitionRegistry.php
    └── Neo4jService.php
```

## Framework Implementation

### 1. Frame System (`App\Soul\Framework`)

#### `Frame` (Abstract Base Class)
- **Purpose**: Template for all frame definitions
- **Key Methods**:
    - `instantiate()`: Creates independent FrameInstance
    - `match()`: Minsky's matching process (confidence scoring)
    - `addFrameElement()`: Dynamic FE addition
    - `setDefault()`: Minsky's default assignments
- **Features**: Default values, context application, Mind service integration

#### `FrameInstance`
- **Purpose**: Independent cognitive units that participate in processing
- **Key Methods**:
    - `sendMessageToAgent()`: Direct agent communication
    - `requestFrameInstantiation()`: Request new frame creation via Mind
    - Agent methods (defined in subclasses)
- **Features**: Isolated operation, agent communication, Mind service queries

#### `FrameElement` & `FrameElementInstance`
- **Purpose**: Templates and instances for frame element management
- **Features**: Constraint validation, relation management, shared slots
- **Methods**: `setValue()/getValue()`, `relateTo()`, `shareSlotWith()`

#### `RelationFrame` (Abstract)
- **Purpose**: Represents relationships between frame elements
- **Structure**: Always has Figure/Ground FE pattern
- **Subclasses**: `IsARelation`, `CausesRelation`, `SharedSlotRelation`

### 2. MindService - Central Coordinator

The `App\Soul\Services\MindService` implements the "Society of Mind" coordination principle.

#### External World Interface
- **`startProcessingSession()`**: Entry point for cognitive processing
- **`processInput()`**: Main cognitive pipeline execution
- **`endProcessingSession()`**: Cleanup and archival

#### Frame Management
- **`registerFrameDefinition()`**: Register frame templates
- **`instantiateFrame()`**: Create independent cognitive units
- **`getFrameInstance()`**: Lookup for agent communication

#### Cognitive Processing Pipeline
1. **Input Analysis**: Identify relevant frames from input
2. **Frame Instantiation**: Create initial cognitive units
3. **Initial Activation**: Trigger Minsky's matching process
4. **Spreading Activation**: Run cognitive processing rounds
5. **Response Generation**: Extract results from final state

#### Session Management
- Multiple concurrent processing sessions
- Session-scoped instance management
- Statistics and monitoring

### 3. SOUL ResourceController API

The `App\Http\Controllers\SOUL\ResourceController` provides comprehensive REST API endpoints:

```php
// Concept Management
GET    /soul/concepts                    # List/search concepts with filtering
POST   /soul/concepts                    # Create new concept
GET    /soul/concepts/{name}             # Get concept with relationships
PUT    /soul/concepts/{name}             # Update concept
DELETE /soul/concepts/{name}             # Delete concept

// Relationship Operations
POST   /soul/relationships               # Create relationship between concepts

// SOUL Framework Operations
POST   /soul/initialize                  # Initialize SOUL primitives
GET    /soul/spreading-activation/{name} # Perform spreading activation
GET    /soul/graph/{name}                # Get graph visualization data
GET    /soul/statistics                  # Get graph statistics

// Data Import/Export
GET    /soul/export                      # Export graph data
POST   /soul/import                      # Import graph data
POST   /soul/search                      # Advanced concept search

// Utility Endpoints
GET    /soul/relationship-types          # Get available relationship types
GET    /soul/concept-types               # Get available concept types

// Database Management
POST   /soul/database/constraints        # Create Neo4j constraints/indexes
GET    /soul/database/status             # Get database status
DELETE /soul/database/constraints        # Drop constraints/indexes
```

## Database Architecture

### Dual Database Setup
- **MySQL**: Legacy FrameNet data and traditional relational operations
- **Neo4j Community Edition**: Graph database for SOUL conceptual networks

### Neo4j Integration
- **Container**: `neo4j:5.15-community` with APOC plugins
- **Access**: Web interface at `http://localhost:7474`, Bolt protocol on port `7687`
- **Network**: All services connected via `soul-network` Docker bridge
- **Volumes**: Persistent storage for data, logs, imports, and plugins
- **Memory**: Configured with 2GB heap, 1GB page cache for optimal performance

### Laravel Integration
- `Neo4jServiceProvider` registered in `config/app.php`
- Neo4j driver singleton available via dependency injection
- Environment-based configuration for flexible deployment

### Usage Examples

```php
use Laudis\Neo4j\Contracts\ClientInterface;

class ConceptController extends Controller
{
    public function __construct(private ClientInterface $neo4j)
    {
        // Neo4j client is automatically injected via service provider
    }

    public function createConcept(Request $request)
    {
        $result = $this->neo4j->run(
            'CREATE (c:Concept {name: $name, type: $type}) RETURN c',
            ['name' => $request->name, 'type' => $request->type]
        );
        
        $concept = $result->first()->get('c')->toArray();
        return response()->json(['created' => true, 'concept' => $concept]);
    }
}
```

### Environment Variables
```bash
NEO4J_HOST=neo4j           # Docker service name
NEO4J_PORT=7687            # Bolt protocol port
NEO4J_USER=neo4j           # Default username
NEO4J_PASSWORD=secret      # Set in .env file
NEO4J_DATABASE=neo4j       # Default database name
```

## Frontend Architecture

### Technology Stack
- **Laravel 12**: Primary framework with custom routing via annotations
- **HTMX**: Frontend reactivity via HX-* headers in controllers
- **Vite**: Asset bundling with Laravel Vite plugin
- **AlpineJS**: Frontend JavaScript framework  
- **Fomantic UI**: Primary CSS framework
- **JointJS**: Graph visualization library for SOUL graph implementation
- **Chart.js**: Data visualization
- **Additional Libraries**: svg-pan-zoom, Split.js, ky HTTP client

### Key Features
- HTMX-driven interactions with server-side rendering
- Blade templating with component-based UI
- Custom JavaScript components for complex interactions
- Graph visualization using JointJS for conceptual relationships
- AlpineJS for reactive components

## Theoretical Implementation

### Primitive Concepts (To Be Implemented)
- **Image Schemas**: FORCE, REGION, OBJECT, POINT, CURVE, AXIS, MOVEMENT
- **CSP Primitives**: EMOTION, NUMBER, STATE, CAUSE, SCALE

### Meta-Schemas (To Be Implemented)
- **ENTITY**: Conceptualized via topological schemas
- **STATE**: Describes stable conditions  
- **PROCESS**: Driven by FORCE, structured by PATH and CAUSE
- **CHANGE**: Result of PROCESS altering STATE

### Structural Schemas (To Be Implemented)
- **CLASS & HIERARCHY**: For `is-a` and `part-whole` relationships
- **AXIS & SCALE**: For ordered relationships and comparisons
- **RADIAL**: For prototype effects and polysemy
- **QUALIA**: For generative internal structure of concepts

## Implementation Status

### ✅ Completed Features
- Core framework architecture and classes
- MindService coordination system
- Comprehensive exception system with rich context
- Contract interfaces definition
- SOUL ResourceController with full CRUD API
- Neo4j integration and service provider
- Docker development environment
- Graph visualization infrastructure

### 🔄 Current Development Phase
1. **Concrete Frame Implementations**: Create specific frame classes for Image Schemas and CSP primitives
2. **Contract Implementations**: Implement FrameDefinitionRegistry and Neo4jService interfaces
3. **Frame Definition Bootstrap**: System for loading primitive frames at startup
4. **Example Frame Definitions**: Create sample frames (PERSON, COMMERCIAL_TRANSACTION, etc.)
5. **Testing Framework**: Comprehensive unit tests for core functionality

### ⏳ Next Development Phases
- **Primitive Frame Classes**: Complete Image Schema and CSP primitive implementations
- **Structural Schema Implementation**: Implement QUALIA, RADIAL, AXIS & SCALE schemas
- **Advanced Cognitive Operations**: Conceptual blending, construal operations
- **Agent-based Reasoning**: Enhanced agent communication patterns
- **Performance Optimization**: Large-scale spreading activation optimization

## Usage Pattern

```php
// 1. Start cognitive processing
$mindService = app(MindService::class);
$sessionId = $mindService->startProcessingSession(['text' => 'John buys a book']);

// 2. Process input (automatic pipeline execution)
$response = $mindService->processInput(['text' => 'John buys a book'], $sessionId);

// 3. End session and cleanup
$mindService->endProcessingSession($sessionId);
```

## Development Guidelines

1. **Theoretical Alignment**: Ensure all SOUL-specific implementations align with Minsky's Frame Theory and Fillmore's Frame Semantics
2. **Graph-First Approach**: Design new features with Neo4j graph concepts in mind
3. **Independence Pattern**: Maintain FrameInstance independence after instantiation
4. **Agent Communication**: Use MindService for coordination, direct method calls for communication
5. **Performance**: Consider spreading activation performance requirements for large conceptual networks
6. **Multi-language**: Maintain existing multi-language support for international research collaboration

## Key Configuration Files

- `config/app.php`: Laravel application configuration with Neo4j service provider registration
- `composer.json`: PHP dependencies including Laudis Neo4j PHP client
- `package.json`: Node.js dependencies including visualization libraries
- `vite.config.js`: Asset compilation configuration
- `docker-compose-dev.yml`: Development environment with Neo4j
- `.env`: Environment configuration including Neo4j connection settings

## Authentication & Authorization
- Configurable auth handlers (internal/Auth0)
- Role-based access control (ADMIN, MASTER, MANAGER levels)
- Laravel-based session authentication with custom guards and providers

This framework provides a solid foundation for implementing cognitive AI systems that can perform common-sense reasoning, natural language understanding, and dynamic knowledge representation based on established cognitive science principles.