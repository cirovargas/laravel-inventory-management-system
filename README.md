# Inventory Management System API

A production-ready REST API built with Laravel 12 for managing inventory and sales control in a multi-tenant ERP system.

## Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Performance Optimizations](#performance-optimizations)
- [Scheduled Tasks](#scheduled-tasks)
- [Architecture Decisions](#architecture-decisions)

## Features

- **Multi-tenancy Support**: Company-based data isolation
- **Inventory Management**: Track product stock with entry/exit movements
- **Fully Async Sales Processing**: Sales creation and inventory updates processed asynchronously via queued jobs
- **Sales Reports**: Generate detailed sales reports with metrics
- **Job-Based Architecture**: Async processing using Laravel queues for improved scalability
- **Caching Layer**: Redis-based caching for improved performance
- **Scheduled Tasks**: Automated stale inventory detection
- **Comprehensive Testing**: Unit and integration tests with Pest

## Technology Stack

- **Framework**: Laravel 12.x
- **PHP**: 8.4
- **Database**: PostgreSQL 17.0
- **Cache**: Redis
- **Queue**: Database driver (configurable to Redis/SQS)
- **Testing**: Pest 4.x
- **Code Quality**: Laravel Pint

## Architecture

This application follows **Clean Architecture** principles with **CQRS pattern** and **Event-Driven Design**.

### Directory Structure

```
app/
├── Domain/                    # Pure business logic
│   ├── Inventory/
│   │   ├── DTO/              # Data Transfer Objects
│   │   ├── Repository/       # Repository interfaces
│   │   ├── Enum/             # Model enums
│   │   └── Service/          # Business logic services
│   └── Sales/
│       ├── DTO/
│       ├── Repository/
│       ├── Enum/
│       └── Service/
├── Http/
│   ├── Controllers/Api/      # API controllers
│   ├── Requests/             # Form request validation
│   └── Resources/            # API response resources
├── Repository/                # Concrete repository implementations
├── Models/                    # Eloquent models
├── Events/                    # Domain events
├── Listeners/                 # Event listeners
├── Jobs/                      # Queued jobs
└── Console/Commands/          # Artisan commands
```

### Key Design Patterns

1. **Repository Pattern**: Abstracts data access logic
2. **Service Layer**: Encapsulates business logic
3. **DTO Pattern**: Immutable data transfer objects
4. **Event-Driven**: Domain events trigger async operations

## Installation

### Prerequisites

- Docker and Docker Compose
- Git

### Setup Steps

1. **Clone the repository**
```bash
git clone <repository-url>
cd inventory-management-api
```

2. **Start Docker containers**
```bash
docker-compose up -d
```


## Database Setup

### Seed Database

The seeder creates:
- 2 companies
- 200 products per company (600 total)
- Initial inventory entries
- 1,000,000 sales records with items

```bash
docker compose exec php php artisan db:seed
```

**Note**: Seeding 1M sales may take 5-10 minutes depending on your system.

## API Documentation

Base URL: `http://localhost:3000/api`

### Inventory Endpoints

#### Get Inventory Status
```http
GET /api/inventory?company_id=1
```

**Response:**
```json
{
  "data": [
    {
      "product_id": 1,
      "sku": "PROD-001",
      "name": "Product Name",
      "current_stock": 150,
      "cost_price": 100.00,
      "sale_price": 150.00,
      "total_value": 15000.00,
      "projected_profit": 7500.00
    }
  ]
}
```

#### Create Inventory Entry
```http
POST /api/inventory
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 100,
  "company_id": 1,
  "unit_cost": 50.00,
  "notes": "Initial stock"
}
```

**Response:** `201 Created`


### Sales Endpoints

#### Create Sale
```http
POST /api/sales
Content-Type: application/json

{
  "company_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 2,
      "quantity": 1
    }
  ],
  "notes": "Customer order #123"
}
```

**Response:** `202 Accepted`
```json
{
  "message": "Sale is being processed",
  "tracking_id": "9d8e7f6a-5b4c-3d2e-1f0a-9b8c7d6e5f4a"
}
```

**Note:** The sale is processed asynchronously. Use the `tracking_id` to track the sale status or query the sale by ID once processing is complete.

#### Get Sale by ID
```http
GET /api/sales/{id}
```

### Reports Endpoints

#### Get Sales Report
```http
GET /api/reports/sales?start_date=2024-01-01&end_date=2024-12-31&sku=PROD-001&per_page=20&cursor=...&company_id=1
```

**Query Parameters:**
- `company_id` (required): Company ID
- `cursor` (optional): Cursor for pagination
- `start_date` (required): Start date in Y-m-d format
- `end_date` (required): End date in Y-m-d format
- `sku` (optional): Filter by product SKU
- `per_page` (optional): Results per page (default: 15, max: 100)

**Response:**
```json
{
  "data": [...],
  "metrics": {
    "total_sales": 150,
    "total_amount": 75000.00,
    "total_profit": 25000.00,
    "total_quantity": 500
  },
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8
  }
}
```

## Testing

### Run All Tests
```bash
docker compose exec php php artisan test
```

### Run Specific Test File
```bash
docker compose exec php php artisan test tests/Feature/Api/SaleApiTest.php
```

### Run Tests with Filter
```bash
docker compose exec php php artisan test --filter=creates_a_sale
```

### Code Formatting
```bash
docker compose exec php vendor/bin/pint
```

## Performance Optimizations

### 1. Database Indexes
- Composite indexes on frequently queried columns
- Indexes on foreign keys and status fields
- Covering indexes for report queries
- Partitioning on sales table by sale_date
- Partitioning on sale_items table by sale_date
- Sharding on all tables by company_id

### 2. Query Optimization
- Eager loading relationships to prevent N+1 queries
- Batch inserts for seeders (1000 records per batch)
- Optimized aggregation queries for metrics

### 3. Caching Strategy
- Inventory status cached for 5 minutes
- Cache invalidation on inventory updates
- Redis-based caching for production

### 4. Async Processing
- **Fully asynchronous sale creation**: Sales are created and processed entirely via queued jobs
- **ProcessSaleJob**: Handles both sale creation and inventory updates in a single transaction
- **Immediate response**: API returns 202 Accepted with a tracking ID without waiting for sale creation
- **Job retry mechanism**: 3 attempts with 120-second timeout per attempt
- **Comprehensive error handling**: Failed jobs are logged with tracking ID for debugging

## Scheduled Tasks

### Archive Stale Inventory
Runs daily at 2:00 AM to identify products with no inventory updates in 90+ days.

```bash
# Manual execution
docker compose exec php php artisan inventory:archive-stale

# With custom days threshold
docker compose exec php php artisan inventory:archive-stale --days=60
```

### Start Scheduler
```bash
docker compose exec php php artisan schedule:work
```

## Architecture Decisions

### Why Clean Architecture?
- **Separation of Concerns**: Business logic is independent of frameworks and infrastructure
- **Testability**: Pure business logic can be tested without database or HTTP dependencies
- **Maintainability**: Changes to infrastructure don't affect business rules

### Why Repository Pattern?
- **Abstraction**: Controllers don't depend on Eloquent directly
- **Flexibility**: Easy to swap data sources (e.g., API, cache, different database)
- **Testing**: Easy to mock repositories in unit tests

### Why Event-Driven Architecture?
- **Decoupling**: Sale creation doesn't directly handle inventory updates
- **Scalability**: Async processing via queues handles high load
- **Reliability**: Failed jobs can be retried automatically

### Why DTOs?
- **Immutability**: Readonly DTOs prevent accidental data modification
- **Type Safety**: Strong typing catches errors at compile time
- **Validation**: Data is validated before creating DTOs

### Database Design Decisions

#### Multi-tenancy via company_id
- Simple and effective for moderate scale
- Row-level security via foreign keys
- Easy to query and maintain

#### Inventory Entry/Exit Pattern
- Audit trail of all stock movements
- Supports complex inventory scenarios
- Easy to calculate current stock

#### Denormalized Totals in Sales
- Faster queries for reports
- Avoids complex joins
- Trade-off: slight data redundancy for performance

## Queue Configuration

For production, configure Redis or SQS for better performance:

```env
QUEUE_CONNECTION=redis
```

Start queue workers:
```bash
docker-compose exec php php artisan queue:work --tries=3
```

## License

This project is under the Apache 2.0 license
