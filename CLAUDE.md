# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SOUL Framework 0.1 is a research prototype for computational implementation of "Project SOUL: A Conceptual Framework for Meaning Representation". This is a Laravel-based application that aims to develop a layered, concept-based framework for representing meaning, grounded in Cognitive Linguistics principles including Image Schemas and Conceptual Blending theory.

**Current Status**: The project is built on existing FrameNet Brasil infrastructure but is configured as SOUL Framework 0.1. The theoretical framework is documented, and SOUL computational features have been implemented including:
- SOUL ResourceController with comprehensive CRUD operations
- Spreading activation algorithms
- Conceptual blending operations  
- Image Schema, CSP, and Meta-schema primitives
- Neo4j constraints and indexes for optimal performance
- Graph visualization and data export/import capabilities

## Development Commands

### Environment Setup
```bash
# Development environment with Neo4j
docker compose -f docker-compose-dev.yml build
docker compose -f docker-compose-dev.yml up

# Production environment  
docker compose build
docker compose up

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

### Testing
```bash
# Run PHPUnit tests
php artisan test
# or
vendor/bin/phpunit
```

### Code Quality
```bash
# Laravel Pint for code formatting
vendor/bin/pint
```

## Architecture Overview

### Core Framework Structure
- **Laravel 12**: Primary framework with custom routing via annotations
- **HTMX**: Frontend reactivity via HX-* headers in controllers
- **Custom Database Layer**: Custom `Criteria` class extending Laravel's Query Builder
- **Graph-based Conceptual Network**: Intended architecture for SOUL concepts and relationships

### Key Directories

#### Application Layer (`app/`)
- **Controllers**: Organized by domain (Annotation, Frame, Construction, Concept, etc.)
  - Extends base `Controller` class with HTMX support
  - Uses annotation-based routing via `laravelcollective-annotations`
- **Services**: Business logic layer with specialized services
  - `GraphService.php`: Core graph operations for frame relationships and visualization
  - `RelationService.php`: Manages relationships between entities
  - Various annotation and reporting services
- **Repositories**: Data access layer following repository pattern
  - `Concept.php`: Core concept management with hierarchical operations
  - Graph-related repositories for frames, relations, semantic types
- **Database**: Custom abstraction layer
  - `Criteria.php`: Extended Query Builder with domain-specific operators

#### Frontend Assets (`resources/` & `public/`)
- **Vite**: Asset bundling with Laravel Vite plugin
- **AlpineJS**: Frontend JavaScript framework  
- **Fomantic UI**: Primary CSS framework
- **JointJS**: Graph visualization library (available for SOUL graph implementation)
- **Chart.js**: Data visualization
- **Additional Libraries**: svg-pan-zoom, Split.js, ky HTTP client

#### Custom Authentication (`app/Auth/`)
- Custom Laravel session guards and user providers
- Session-based authentication with Auth0 integration support
- Role-based access control system

### SOUL Framework Specific Architecture

#### Conceptual Framework (Theoretical)
Based on README.md documentation:
- **Primitives**: Image Schemas (FORCE, REGION, OBJECT, POINT, CURVE, AXIS, MOVEMENT) + CSP primitives (EMOTION, NUMBER, STATE, CAUSE, SCALE)
- **Meta-Schemas**: ENTITY, STATE, PROCESS, CHANGE
- **Structural Schemas**: CLASS & HIERARCHY, AXIS & SCALE, RADIAL, QUALIA

#### Current Implementation Status
**Implemented**:
- Basic concept management (`app/Repositories/Concept.php`)
- Graph visualization infrastructure (JointJS, GraphService)
- Relationship management system
- Database views: `view_concept`, `view_relation`, `view_semantictype`

**Not Yet Implemented**:
- SOUL-specific primitives and meta-schemas
- Spreading activation algorithms
- Conceptual blending operations
- Image schema definitions
- Structural schema implementations

### Database Architecture
- **Dual Database Setup**: 
  - **MySQL**: Legacy FrameNet data and traditional relational operations
  - **Neo4j Community Edition**: Graph database for SOUL conceptual networks
- Custom `Criteria` class extending Laravel's Query Builder with specialized operators
- Repository pattern implementation for data access layer
- Multi-language support built into data models
- Views for conceptual data: `view_concept`, `view_relation`, `view_semantictype`, `view_relationtype`

#### Neo4j Integration
- **Container**: `neo4j:5.15-community` with APOC plugins
- **Access**: Web interface at `http://localhost:7474`, Bolt protocol on port `7687`
- **Network**: All services connected via `soul-network` Docker bridge
- **Volumes**: Persistent storage for data, logs, imports, and plugins
- **Memory**: Configured with 2GB heap, 1GB page cache for optimal performance
- **Laravel Integration**: 
  - `Neo4jServiceProvider` registered in `config/app.php`
  - Neo4j driver singleton available via dependency injection
  - Environment-based configuration for flexible deployment

### Frontend Architecture
- HTMX-driven interactions with server-side rendering
- Blade templating with component-based UI
- Custom JavaScript components for complex interactions
- Graph visualization using JointJS for conceptual relationships
- AlpineJS for reactive components

## Configuration Files

- `config/webtool.php`: Application-specific configuration including menus and concept relations
- `config/app.php`: Laravel application configuration with Neo4j service provider registration
- `composer.json`: PHP dependencies including Laudis Neo4j PHP client and graph/ML libraries
- `package.json`: Node.js dependencies including visualization libraries
- `vite.config.js`: Asset compilation configuration
- `.env`: Environment configuration including Neo4j connection settings

## Key Features

### Concept Management
- Hierarchical concept navigation and relationships
- Type-based concept organization
- Multi-language concept support
- Concept tree structures and children listing

### Graph Operations
- Frame relationship graphs via `GraphService`
- Domain-based graph generation
- Node and link management for visualization
- Support for various relationship types

### Data Management
- Frame and Construction management (inherited from FNBr)
- Semantic type management
- Relationship and relation type handling
- Multi-language support throughout

### Visualization
- JointJS integration for graph visualization
- Chart.js for data visualization
- SVG pan/zoom capabilities
- Responsive graph layouts

## SOUL Framework Implementation Roadmap

### Phase 1: Core Infrastructure (Current)
- ✅ Basic concept repository and management
- ✅ Graph service foundation
- ✅ Visualization libraries integrated
- 🔄 Database schema for SOUL concepts

### Phase 2: SOUL Primitives
- ⏳ Image Schema primitive definitions
- ⏳ CSP primitive implementations
- ⏳ Meta-schema structure (ENTITY, STATE, PROCESS, CHANGE)
- ⏳ Structural schema relationships

### Phase 3: Dynamic Operations
- ⏳ Spreading activation algorithm implementation
- ⏳ Conceptual blending operations
- ⏳ Construal operations
- ⏳ Graph traversal and inference

### Phase 4: Advanced Features
- ⏳ Agent-based reasoning
- ⏳ JSON/YAML export capabilities
- ⏳ Advanced graph visualization
- ⏳ Pattern matching and similarity queries

## Development Notes

- Controllers use HTMX headers for client-side interactions
- Custom annotation routing system via PHP attributes
- Multi-language support throughout the application
- Docker-based development environment
- Built on existing FrameNet Brasil codebase but configured for SOUL Framework
- Runs on PHP 8.4 for enhanced performance
- Uses custom database abstraction layer for domain-specific query operations

## Key Interfaces for SOUL Implementation

### Planned Abstraction Interfaces
```php
// Future interfaces for SOUL implementation
interface GraphRepositoryInterface
interface GraphTraversalInterface  
interface ActivationInterface
interface ConceptualBlendingInterface
```

### Current Key Classes
- `app/Repositories/Concept.php`: Core concept operations
- `app/Services/GraphService.php`: Graph visualization and relationships
- `app/Database/Criteria.php`: Extended query capabilities
- `app/Providers/Neo4jServiceProvider.php`: Neo4j driver registration
- `config/webtool.php`: Relationship type definitions and UI configuration

## Neo4j Usage

### Basic Usage in Controllers/Services
```php
use Illuminate\Http\Request;
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

    public function findRelatedConcepts(string $conceptName)
    {
        $result = $this->neo4j->run(
            'MATCH (c:Concept {name: $name})-[:RELATES_TO]-(related:Concept)
             RETURN related.name as name, related.type as type',
            ['name' => $conceptName]
        );

        $concepts = [];
        foreach ($result as $record) {
            $concepts[] = [
                'name' => $record->get('name'),
                'type' => $record->get('type')
            ];
        }

        return response()->json($concepts);
    }
}
```

### SOUL Framework Controller Usage
The SOUL ResourceController provides comprehensive REST API endpoints for all SOUL Framework operations:

```php
// API Endpoints Available at /soul/*
GET    /soul/concepts                    # List/search concepts with filtering
POST   /soul/concepts                    # Create new concept
GET    /soul/concepts/{name}             # Get concept with relationships
PUT    /soul/concepts/{name}             # Update concept
DELETE /soul/concepts/{name}             # Delete concept

POST   /soul/relationships               # Create relationship between concepts
POST   /soul/initialize                  # Initialize SOUL primitives
GET    /soul/spreading-activation/{name} # Perform spreading activation
GET    /soul/graph/{name}                # Get graph visualization data
GET    /soul/statistics                  # Get graph statistics
GET    /soul/export                      # Export graph data
POST   /soul/import                      # Import graph data
POST   /soul/search                      # Advanced concept search

GET    /soul/relationship-types          # Get available relationship types
GET    /soul/concept-types               # Get available concept types

POST   /soul/database/constraints        # Create Neo4j constraints/indexes
GET    /soul/database/status             # Get database status
DELETE /soul/database/constraints        # Drop constraints/indexes
```

### Using via app() helper
```php
// Alternative way to access Neo4j client
$neo4j = app('neo4j');
$result = $neo4j->run('MATCH (n:Concept) RETURN count(n) as total');
$totalConcepts = $result->first()->get('total');
```

### Environment Variables
```bash
NEO4J_HOST=neo4j           # Docker service name
NEO4J_PORT=7687            # Bolt protocol port
NEO4J_USER=neo4j           # Default username
NEO4J_PASSWORD=secret      # Set in .env file
NEO4J_DATABASE=neo4j       # Default database name
```

### Laudis Neo4j Client Features
- **Modern PHP 8+ Support**: Built for PHP 8.1+ with full type safety
- **Laravel 12 Compatible**: No Guzzle version conflicts - uses PSR standards
- **Multiple Protocol Support**: HTTP and Bolt protocols supported
- **Elegant API**: Clean, fluent interface for Neo4j operations
- **High Performance**: Optimized for modern PHP with connection pooling
- **Active Maintenance**: Regularly updated and well-documented

## Authentication & Authorization
- Configurable auth handlers (internal/Auth0)
- Role-based access control (ADMIN, MASTER, MANAGER levels)
- Laravel-based session authentication with custom guards and providers

# Development Guidelines

1. **Theoretical Alignment**: Ensure all SOUL-specific implementations align with the theoretical framework documented in README.md
2. **Graph-First Approach**: Design new features with graph database concepts in mind for future migration
3. **Extensibility**: Build on existing FNBr infrastructure while adding SOUL-specific functionality
4. **Performance**: Consider spreading activation performance requirements for large conceptual networks
5. **Visualization**: Leverage JointJS and existing graph infrastructure for SOUL concept visualization
6. **Multi-language**: Maintain existing multi-language support for international research collaboration

# Important Notes

- The project is currently a research prototype focused on computational linguistics
- SOUL-specific features are planned but not yet implemented
- The codebase provides solid foundation for graph-based conceptual networks  
- Existing FrameNet infrastructure can be extended for SOUL requirements
- Graph visualization capabilities are already integrated and ready for SOUL concepts