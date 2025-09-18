# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the **SOUL Framework 0.1** - a Laravel-based web application that implements a conceptual framework for meaning representation grounded in Cognitive Linguistics. It's specifically the **FrameNet Brasil Web Annotation Tool** for linguistic annotation, frames, and constructions.

## Key Technologies

- **Backend**: Laravel 12.x (PHP 8.2+) with Octane and Reverb
- **Frontend**: Vite build system with Alpine.js, HTMX patterns
- **Database**: Neo4j graph database via `laudis/neo4j-php-client`
- **UI Libraries**: Shoelace, TailwindCSS, Fomantic UI, JointJS for graph visualization
- **Real-time**: Laravel Reverb for WebSocket connections
- **Testing**: Pest PHP testing framework

## Development Commands

### Laravel/PHP Commands
```bash
# Run development server
php artisan serve

# Laravel Octane (high-performance)
php artisan octane:start

# Run database migrations
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run tests
./vendor/bin/pest

# Code formatting (Laravel Pint)
./vendor/bin/pint

# Generate application key
php artisan key:generate
```

### Frontend Commands
```bash
# Run development server with hot reload
npm run dev

# Build for production
npm run build

# Install dependencies
npm install
```

### Docker Commands
```bash
# Development environment
docker-compose -f docker-compose-dev.yml up -d

# Production environment
docker-compose up -d
```

## Architecture Overview

### Backend Structure
- **MVC Pattern**: Standard Laravel MVC with additional architectural layers
- **Services Layer**: Business logic in `app/Services/` directory
- **Repositories**: Data access layer in `app/Repositories/`
- **Data Objects**: Spatie Laravel Data objects in `app/Data/`
- **UI Components**: Custom Blade components in `app/UI/` and `app/View/`

### Frontend Architecture
- **Component-based**: Reusable JavaScript components in `resources/js/components/`
- **Alpine.js**: For reactive UI interactions
- **Graph Visualization**: JointJS and vis-network for conceptual network display
- **Styling**: TailwindCSS with LESS preprocessing support

### Database Architecture
- **Graph Database**: Neo4j for storing conceptual networks and frame relationships
- **Migrations**: Laravel migrations for relational data in `database/migrations/`
- **Neo4j Data**: Graph data structure files in `database/neo4j/`

### Key Directories
- `app/Services/`: Core business logic and graph operations
- `app/UI/`: Custom UI components and views
- `app/Data/`: Data transfer objects and API resources
- `resources/js/components/`: Frontend JavaScript components
- `database/neo4j/`: Neo4j graph database files and queries

## Important Configuration

### Environment Setup
- Copy `.env.example` to `.env` for local development
- Configure Neo4j database connection
- Set up Auth0 integration for authentication
- Configure media server URLs for asset handling

### Key Features
- **Frame Annotation**: Linguistic frame annotation and semantic role labeling
- **Graph Visualization**: Interactive conceptual network visualization
- **Real-time Collaboration**: WebSocket-based collaborative annotation
- **Multi-format Export**: Export annotations in various formats

## Development Notes

- The project uses **Laravel Octane** for enhanced performance
- **Neo4j** is the primary database for graph-based conceptual relationships
- Frontend uses **Vite** with hot module replacement for development
- UI follows **component-based architecture** with reusable Blade components
- Real-time features powered by **Laravel Reverb** WebSocket server