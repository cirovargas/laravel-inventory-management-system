# Laravel Inventory Management System - Documentation

Welcome to the comprehensive documentation for the Laravel Inventory Management System. This documentation covers all architectural decisions, implementation details, and design patterns used in the project.

## Table of Contents

### Core Documentation

1. **[Database Design](database-design.md)**
   - Database selection and justification (PostgreSQL + Citus)
   - Complete table structure and column definitions
   - Relationships and cardinality
   - Indexes and performance optimization
   - Constraints and business rules
   - Entity-Relationship diagrams

2. **[Application Architecture](application-architecture.md)**
   - Architecture overview and principles
   - Folder structure and organization
   - Architectural layers (Domain, Application, Infrastructure)
   - Architecture pattern (DDD with CQRS inspiration)
   - Design patterns implementation
   - Data flow diagrams

3. **[Technology Stack](technology-stack.md)**
   - Core technologies (PHP 8.4, Laravel 12)
   - Infrastructure (PostgreSQL, Redis, Docker)
   - Development tools (Composer, Pint, Pest)
   - Performance and scalability features
   - Testing framework

### Additional Resources

- **[Architectural Decision Records](architectural-decision-records/)**: ADRs documenting key decisions
- **[Architecture Haiku](architecture-haiku/)**: Poetic summaries of architectural concepts
- **[Diagrams](diagrams/)**: Visual representations of system architecture

## Quick Start

If you're new to this project, we recommend reading the documentation in this order:

1. Start with **[Application Architecture](application-architecture.md)** to understand the overall structure
2. Review **[Database Design](database-design.md)** to understand data modeling
3. Explore **[Technology Stack](technology-stack.md)** to learn about the technologies used

## Key Highlights

### Architecture

- **Pattern**: Domain-Driven Design (DDD) with CQRS-inspired elements
- **Layers**: Domain, Application, Infrastructure, Presentation
- **Principles**: SOLID, Separation of Concerns, Dependency Inversion

### Database

- **System**: PostgreSQL 18.0 with Citus extension
- **Sharding**: Company-based horizontal sharding
- **Partitioning**: Monthly partitions for time-series data
- **Tables**: 5 core tables (companies, products, sales, sale_items, inventory_entries)

### Technology

- **Framework**: Laravel 12 with Octane (Swoole)
- **Language**: PHP 8.4 with strict typing
- **Cache**: Redis for performance
- **Queue**: Async job processing with Supervisor
- **Testing**: Pest 4 with browser testing support

## Architecture at a Glance

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  (Controllers, Requests, Resources, Routes)                  │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                    Application Layer                         │
│  (Services, DTOs, Events, Jobs, Listeners)                   │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                      Domain Layer                            │
│  (Business Logic, Repository Interfaces, Enums)              │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                  Infrastructure Layer                        │
│  (Repository Implementations, Eloquent Models, Database)     │
└─────────────────────────────────────────────────────────────┘
```

## Design Patterns Used

1. **Repository Pattern**: Data access abstraction
2. **Service Layer Pattern**: Business logic encapsulation
3. **DTO Pattern**: Immutable data transfer
4. **Observer Pattern**: Event-driven architecture
5. **Dependency Injection**: Loose coupling
6. **Factory Pattern**: Test data generation

