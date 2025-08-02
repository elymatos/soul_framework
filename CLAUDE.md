# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

FNBr Webtool 4.0 is a web annotation and database management application developed by the FrameNet Brasil Project. It's built with Laravel 12 and focuses on multilingual framenets and constructicons annotation.

## Development Commands

### Environment Setup
```bash
# Build and start Docker containers
docker compose build
docker compose up

# Access the application at http://localhost:8001
# Default credentials: user=webtool, password=test
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

### Key Directories

#### Application Layer (`app/`)
- **Controllers**: Organized by domain (Annotation, Frame, Construction, etc.)
  - Extends base `Controller` class with HTMX support
  - Uses annotation-based routing via `laravelcollective-annotations`
- **Services**: Business logic layer with specialized annotation services
- **Data**: Data transfer objects and validation classes
- **Repositories**: Data access layer following repository pattern

#### Frontend Assets (`resources/` & `public/`)
- **Vite**: Asset bundling with Laravel Vite plugin
- **AlpineJS**: Frontend JavaScript framework
- **Fomantic UI**: Primary CSS framework
- **JointJS**: Graph visualization library
- **Tailwind CSS**: Utility CSS framework

#### Custom Authentication (`app/Auth/`)
- Custom Laravel session guards and user providers
- Session-based authentication with Auth0 integration support
- Role-based access control system

### Authentication & Authorization
- Configurable auth handlers (internal/Auth0)
- Role-based access control (ADMIN, MASTER, MANAGER levels)
- Laravel-based session authentication with custom guards and providers

### Database Architecture
- Custom `Criteria` class extending Laravel's Query Builder with specialized operators
- Repository pattern implementation for data access layer
- Multi-language support built into data models
- MariaDB with custom sequence management

### Frontend Architecture
- HTMX-driven interactions with server-side rendering
- Blade templating with component-based UI
- Custom JavaScript components for complex interactions
- Graph visualization using JointJS for frame relationships

## Configuration Files

- `config/webtool.php`: Application-specific configuration including menus and relations
- `composer.json`: PHP dependencies and autoloading
- `package.json`: Node.js dependencies
- `vite.config.js`: Asset compilation configuration

## Key Features

### Annotation Tools
- Static/Dynamic annotation modes
- Frame Element (FE) annotation
- Full text annotation
- Deixis annotation
- BBox annotation for multimodal content

### Data Management
- Frame and Construction management
- Lexical Unit (LU) management
- Corpus and document handling
- Multimodal data (video/image) support

### Reporting & Visualization
- Frame relationship graphs
- Construction reports
- Semantic type reports
- Network structure visualization

## Development Notes

- Controllers use HTMX headers for client-side interactions
- Custom annotation routing system via PHP attributes
- Multi-language support throughout the application
- Docker-based development environment
- Integration with Google Cloud services (Speech, Storage)
- Fully migrated from custom Orkester framework to Laravel 12
- Runs on PHP 8.4 for enhanced performance and latest language features
- Uses custom database abstraction layer for domain-specific query operations

# Claude rules

1. First think through the problem, read the codebase for relevant files, and write a plan to tasks/todo.md.
2. The plan should have a list of todo items that you can check off as you complete them
3. Before you begin working, check in with me and I will verify the plan.
4. Then, begin working on the todo items, marking them as complete as you go.
5. Please every step of the way just give me a high level explanation of what changes you made
6. Make every task and code change you do as simple as possible. We want to avoid making any massive or complex changes. Every change should impact as little code as possible. Everything is about simplicity.
7. Finally, add a review section to the [todo.md](http://todo.md/) file with a summary of the changes you made and any other relevant information.
