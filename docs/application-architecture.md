# Application Architecture Documentation

## Table of Contents
- [Architecture Overview](#architecture-overview)
- [Folder Structure](#folder-structure)
- [Architectural Layers](#architectural-layers)
- [Architecture Pattern](#architecture-pattern)
- [Design Patterns](#design-patterns)
- [Data Flow](#data-flow)

## Architecture Overview

This Laravel inventory management system implements a **Domain-Driven Design (DDD)** architecture with **CQRS-inspired patterns** and **Event-Driven** components. The architecture emphasizes:

- **Separation of Concerns**: Clear boundaries between business logic, application orchestration, and infrastructure
- **Testability**: Isolated components that can be tested independently
- **Scalability**: Async processing, caching, and database sharding for horizontal scaling
- **Maintainability**: Well-organized code structure following SOLID principles

### Key Architectural Characteristics

1. **Multi-Layered Architecture**: Domain, Application, Infrastructure, and Presentation layers
2. **Repository Pattern**: Abstraction of data access logic
3. **Service Layer**: Encapsulation of business logic
4. **Event-Driven**: Asynchronous processing via events and queued jobs
5. **DTO Pattern**: Immutable data transfer objects for type safety
6. **API-First Design**: RESTful API with versioned resources

## Folder Structure

### Complete Directory Organization

```
app/
├── Console/
│   └── Commands/              # Artisan commands (auto-registered)
│       └── ArchiveStaleInventoryCommand.php
│
├── Domain/                    # Pure business logic (framework-agnostic)
│   ├── Inventory/
│   │   ├── DTO/              # Data Transfer Objects
│   │   │   └── InventoryEntryData.php
│   │   ├── Enum/             # Domain enumerations
│   │   │   └── InventoryType.php
│   │   ├── Repository/       # Repository interfaces
│   │   │   ├── InventoryRepositoryInterface.php
│   │   │   └── ProductRepositoryInterface.php
│   │   └── Service/          # Business logic services
│   │       └── InventoryService.php
│   │
│   └── Sales/
│       ├── DTO/
│       │   ├── CreateSaleData.php
│       │   └── SaleItemData.php
│       ├── Enum/
│       │   └── SaleStatus.php
│       ├── Repository/
│       │   └── SaleRepositoryInterface.php
│       └── Service/
│           └── SaleService.php
│
├── Events/                    # Domain events
│   └── SaleCompleted.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php    # Base controller
│   │   └── Api/              # API controllers
│   │       ├── InventoryController.php
│   │       ├── ReportController.php
│   │       └── SaleController.php
│   │
│   ├── Requests/             # Form request validation
│   │   ├── SalesReportRequest.php
│   │   ├── StoreInventoryEntryRequest.php
│   │   └── StoreSaleRequest.php
│   │
│   └── Resources/            # API response resources
│       ├── InventoryEntryResource.php
│       ├── InventoryStatusResource.php
│       ├── SaleItemResource.php
│       └── SaleResource.php
│
├── Jobs/                      # Queued jobs for async processing
│   ├── ProcessSaleJob.php
│   └── UpdateInventoryJob.php
│
├── Listeners/                 # Event listeners
│   └── UpdateInventoryAfterSale.php
│
├── Models/                    # Eloquent ORM models
│   ├── Company.php
│   ├── InventoryEntry.php
│   ├── Product.php
│   ├── Sale.php
│   ├── SaleItem.php
│   └── User.php
│
├── Providers/                 # Service providers
│   ├── AppServiceProvider.php
│   └── RepositoryServiceProvider.php
│
└── Repository/                # Concrete repository implementations
    ├── InventoryRepository.php
    ├── ProductRepository.php
    └── SaleRepository.php

bootstrap/
├── app.php                    # Application bootstrap
├── cache/                     # Bootstrap cache
└── providers.php              # Service provider registration

config/                        # Configuration files
├── app.php
├── database.php
├── cache.php
├── queue.php
├── octane.php
└── ...

database/
├── factories/                 # Model factories for testing
├── migrations/                # Database migrations
└── seeders/                   # Database seeders

routes/
├── api.php                    # API routes
├── console.php                # Console routes
└── web.php                    # Web routes

tests/
├── Feature/                   # Feature tests
├── Unit/                      # Unit tests
├── Pest.php                   # Pest configuration
└── TestCase.php               # Base test case
```

### Folder Organization Justification

#### 1. **Domain Layer** (`app/Domain/`)
- **Purpose**: Contains pure business logic independent of Laravel framework
- **Benefits**:
  - Framework-agnostic business rules
  - Easy to test without framework dependencies
  - Clear separation of business concerns
  - Reusable across different contexts

#### 2. **Repository Pattern** (`app/Repository/` + `app/Domain/*/Repository/`)
- **Purpose**: Abstracts data access logic
- **Benefits**:
  - Decouples business logic from data persistence
  - Enables easy swapping of data sources
  - Facilitates testing with mock repositories
  - Centralizes query logic

#### 3. **Service Layer** (`app/Domain/*/Service/`)
- **Purpose**: Orchestrates business operations
- **Benefits**:
  - Keeps controllers thin
  - Encapsulates complex business logic
  - Promotes code reuse
  - Single Responsibility Principle

#### 4. **DTOs** (`app/Domain/*/DTO/`)
- **Purpose**: Immutable data containers for transferring data between layers
- **Benefits**:
  - Type safety
  - Validation at boundaries
  - Clear data contracts
  - Prevents unintended mutations

#### 5. **Events & Listeners** (`app/Events/`, `app/Listeners/`)
- **Purpose**: Decoupled event-driven architecture
- **Benefits**:
  - Loose coupling between components
  - Easy to add new functionality without modifying existing code
  - Supports async processing

## Architectural Layers

### 1. Presentation Layer (HTTP)

**Location**: `app/Http/Controllers/`, `app/Http/Resources/`, `app/Http/Requests/`

**Responsibilities**:
- Handle HTTP requests and responses
- Validate incoming data
- Transform data for API responses
- Route requests to appropriate services

**Components**:
- **Controllers**: Thin controllers that delegate to services
- **Form Requests**: Validation logic for incoming requests
- **API Resources**: Transform models into JSON responses

**Example Flow**:
```
HTTP Request → Controller → Form Request (Validation) → Service → API Resource → HTTP Response
```

### 2. Application Layer (Services)

**Location**: `app/Domain/*/Service/`

**Responsibilities**:
- Orchestrate business operations
- Coordinate between repositories
- Implement business logic
- Manage transactions
- Cache management

**Key Services**:
- **InventoryService**: Manages inventory entries, stock calculations, caching
- **SaleService**: Handles sale creation, reporting, metrics

**Example**:
```php
// InventoryService orchestrates business logic
public function registerEntry(InventoryEntryData $data): InventoryEntry
{
    return DB::transaction(function () use ($data) {
        // Validate product exists and belongs to company
        $product = $this->productRepository->findById($data->productId);
        
        // Create inventory entry
        $entry = $this->inventoryRepository->createEntry($data->toArray());
        
        // Invalidate cache
        Cache::forget("inventory_status_{$data->companyId}");
        
        return $entry;
    });
}
```

### 3. Domain Layer (Business Logic)

**Location**: `app/Domain/`

**Responsibilities**:
- Define business entities and value objects
- Specify repository contracts
- Define domain events
- Contain business rules and validations

**Components**:
- **DTOs**: Immutable data structures
- **Enums**: Type-safe enumerations (InventoryType, SaleStatus)
- **Repository Interfaces**: Contracts for data access
- **Services**: Business logic implementation

### 4. Infrastructure Layer (Data Access)

**Location**: `app/Repository/`, `app/Models/`

**Responsibilities**:
- Implement repository interfaces
- Interact with database via Eloquent ORM
- Handle data persistence
- Execute queries

**Components**:
- **Eloquent Models**: ORM representations of database tables
- **Repository Implementations**: Concrete implementations of repository interfaces

**Example**:
```php
// Repository implements interface from Domain layer
final class ProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::query()->find($id);
    }
}
```

### 5. Job/Queue Layer (Async Processing)

**Location**: `app/Jobs/`, `app/Listeners/`

**Responsibilities**:
- Handle asynchronous operations
- Process long-running tasks
- Decouple time-consuming operations from HTTP requests
- Retry failed operations

**Components**:
- **ProcessSaleJob**: Creates sales and updates inventory asynchronously
- **UpdateInventoryJob**: Updates inventory after sale completion
- **Event Listeners**: Dispatch jobs in response to events

## Architecture Pattern

### Primary Pattern: Domain-Driven Design (DDD) with CQRS Inspiration

#### Why DDD for Inventory Management?

1. **Complex Business Logic**
   - Inventory tracking requires sophisticated business rules
   - Multi-tenant data isolation
   - Financial calculations and profit tracking
   - Stock availability validation

2. **Clear Domain Boundaries**
   - **Inventory Domain**: Stock management, entries/exits
   - **Sales Domain**: Transaction processing, reporting
   - Each domain has its own models, services, and repositories

3. **Ubiquitous Language**
   - Domain terms: Product, SKU, InventoryEntry, Sale, SaleItem
   - Status enums: PENDING, PROCESSING, COMPLETED, FAILED
   - Types: ENTRY, EXIT

4. **Scalability Requirements**
   - Async processing for sales
   - Caching for inventory status
   - Database sharding for multi-tenancy

#### CQRS-Inspired Elements

While not full CQRS, the architecture separates:
- **Commands**: Write operations (CreateSale, RegisterInventoryEntry)
- **Queries**: Read operations (GetInventoryStatus, GetSalesReport)

This separation allows:
- Different optimization strategies for reads vs writes
- Caching for read-heavy operations
- Async processing for write operations

### Trade-offs

**Advantages**:
- ✅ Clear separation of concerns
- ✅ Highly testable
- ✅ Scalable architecture
- ✅ Easy to extend with new features
- ✅ Framework-agnostic business logic

**Disadvantages**:
- ❌ More complex than simple MVC
- ❌ More files and folders to navigate
- ❌ Steeper learning curve for new developers
- ❌ Potential over-engineering for simple CRUD operations

**Why These Trade-offs Are Acceptable**:
- Inventory management is inherently complex
- Multi-tenancy requires strict data isolation
- Async processing is essential for scalability
- Long-term maintainability outweighs initial complexity

## Design Patterns

### 1. Repository Pattern

**Location**: `app/Domain/*/Repository/` (interfaces), `app/Repository/` (implementations)

**Purpose**: Abstracts data access logic from business logic

**Implementation**:
```php
// Interface in Domain layer
interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function findBySku(int $companyId, string $sku): ?Product;
}

// Implementation in Infrastructure layer
final class ProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::query()->find($id);
    }
}
```

**Why Used**:
- Decouples business logic from Eloquent ORM
- Enables easy testing with mock repositories
- Centralizes query logic
- Allows swapping data sources without changing business logic

**Files**:
- `app/Domain/Inventory/Repository/ProductRepositoryInterface.php`
- `app/Domain/Inventory/Repository/InventoryRepositoryInterface.php`
- `app/Domain/Sales/Repository/SaleRepositoryInterface.php`
- `app/Repository/ProductRepository.php`
- `app/Repository/InventoryRepository.php`
- `app/Repository/SaleRepository.php`

### 2. Service Layer Pattern

**Location**: `app/Domain/*/Service/`

**Purpose**: Encapsulates business logic and orchestrates operations

**Implementation**:
```php
final class InventoryService
{
    public function __construct(
        private readonly InventoryRepositoryInterface $inventoryRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function registerEntry(InventoryEntryData $data): InventoryEntry
    {
        return DB::transaction(function () use ($data) {
            // Business logic here
        });
    }
}
```

**Why Used**:
- Keeps controllers thin
- Centralizes business logic
- Manages transactions
- Coordinates between multiple repositories

**Files**:
- `app/Domain/Inventory/Service/InventoryService.php`
- `app/Domain/Sales/Service/SaleService.php`

### 3. Data Transfer Object (DTO) Pattern

**Location**: `app/Domain/*/DTO/`

**Purpose**: Immutable objects for transferring data between layers

**Implementation**:
```php
final readonly class InventoryEntryData
{
    public function __construct(
        public int $companyId,
        public int $productId,
        public int $quantity,
        public float $unitCost,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return [
            'company_id' => $this->companyId,
            'product_id' => $this->productId,
            'type' => InventoryType::ENTRY,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unitCost,
            'notes' => $this->notes,
            'entry_date' => now(),
        ];
    }
}
```

**Why Used**:
- Type safety with readonly properties
- Clear data contracts between layers
- Prevents unintended mutations
- Validation at boundaries

**Files**:
- `app/Domain/Inventory/DTO/InventoryEntryData.php`
- `app/Domain/Sales/DTO/CreateSaleData.php`
- `app/Domain/Sales/DTO/SaleItemData.php`

### 4. Observer Pattern (Event-Driven)

**Location**: `app/Events/`, `app/Listeners/`

**Purpose**: Decouple components through event-driven architecture

**Implementation**:
```php
// Event
final class SaleCompleted
{
    public function __construct(
        public readonly Sale $sale,
    ) {}
}

// Listener
final class UpdateInventoryAfterSale implements ShouldQueue
{
    public function handle(SaleCompleted $event): void
    {
        UpdateInventoryJob::dispatch($event->sale);
    }
}

// Registration in AppServiceProvider
Event::listen(
    SaleCompleted::class,
    UpdateInventoryAfterSale::class,
);
```

**Why Used**:
- Loose coupling between sale creation and inventory updates
- Easy to add new side effects without modifying existing code
- Supports async processing via queued listeners

**Files**:
- `app/Events/SaleCompleted.php`
- `app/Listeners/UpdateInventoryAfterSale.php`
- `app/Providers/AppServiceProvider.php`

### 5. Dependency Injection Pattern

**Location**: Throughout application via Laravel's Service Container

**Purpose**: Inject dependencies rather than creating them

**Implementation**:
```php
// Service Provider binds interfaces to implementations
final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
    }
}

// Controllers receive dependencies via constructor
final class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {}
}
```

**Why Used**:
- Loose coupling
- Easy testing with mock dependencies
- Centralized dependency configuration
- Follows Dependency Inversion Principle

**Files**:
- `app/Providers/RepositoryServiceProvider.php`
- All controllers, services, and repositories use constructor injection

### 6. Factory Pattern

**Location**: `database/factories/`

**Purpose**: Create test data and seed database

**Implementation**:
```php
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'sku' => 'PROD-'.fake()->unique()->numerify('######'),
            'name' => fake()->words(3, true),
            'cost_price' => fake()->randomFloat(2, 10, 500),
            'sale_price' => fake()->randomFloat(2, 20, 1000),
        ];
    }
}
```

**Why Used**:
- Consistent test data generation
- Easy database seeding
- Supports different states (e.g., completed sales)

**Files**:
- `database/factories/CompanyFactory.php`
- `database/factories/ProductFactory.php`
- `database/factories/SaleFactory.php`
- `database/factories/SaleItemFactory.php`
- `database/factories/InventoryEntryFactory.php`

## Data Flow

### Read Operation Flow (Get Inventory Status)

```
HTTP GET /api/inventory?company_id=1
    ↓
InventoryController::index()
    ↓
InventoryService::getInventoryStatus()
    ↓
Cache::remember() → InventoryRepository::getInventoryStatus()
    ↓
Eloquent Query → PostgreSQL
    ↓
Collection of inventory data
    ↓
InventoryStatusResource::collection()
    ↓
JSON Response
```

### Write Operation Flow (Create Sale - Async)

```
HTTP POST /api/sales
    ↓
StoreSaleRequest (Validation)
    ↓
SaleController::store()
    ↓
CreateSaleData::fromArray()
    ↓
ProcessSaleJob::dispatch() → Queue
    ↓
HTTP 202 Accepted (tracking_id)

[Async Processing]
ProcessSaleJob::handle()
    ↓
DB::transaction {
    SaleService::createSale()
        ↓
    Create Sale + SaleItems
        ↓
    InventoryService::createInventoryExit() (for each item)
        ↓
    Update Sale status to COMPLETED
}
    ↓
Cache::forget() (invalidate inventory cache)
```
