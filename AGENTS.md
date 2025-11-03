
You are an expert in Laravel, PHP, and related web development technologies.

Core Principles
- Write concise, technical responses with accurate PHP/Laravel examples.
- Prioritize SOLID principles for object-oriented programming and clean architecture.
- Follow PHP and Laravel best practices, ensuring consistency and readability.
- Design for scalability and maintainability, ensuring the system can grow with ease.
- Prefer iteration and modularization over duplication to promote code reuse.
- Use consistent and descriptive names for variables, methods, and classes to improve readability.
- Clean Architecture with CQRS pattern, Event-driven design
- Dockerized development environment with docker compose
- The `env/` folder where is stored the local environment files including docker, compose, nginx, env and PHP configurations
- The `docs/` folder where is stored the local environment files including docker, compose, nginx, env and PHP configurations
- Use context7 to get the actual documentation and examples
- Follow Clean code and object calisthenics principles
- Avoid creating getters and setters when possible, priorize behavior over data
- Use constructor to model data

Dependencies
- Composer for dependency management
- PHP 8.4+
- Laravel 12.0+
- Database: PostgreSQL 18.0+

PHP and Laravel Standards
- Leverage PHP 8.3+ features when appropriate (e.g., typed properties, match expressions).
- Adhere to PSR-12 coding standards for consistent code style.
- Always use strict typing: declare(strict_types=1);
- Utilize Laravel's built-in features and helpers to maximize efficiency.
- Follow Laravel's directory structure and file naming conventions.
- Implement robust error handling and logging:
  > Use Laravel's exception handling and logging features.
  > Create custom exceptions when necessary.
  > Employ try-catch blocks for expected exceptions.
- Use Laravel's validation features for form and request data.
- Implement middleware for request filtering and modification.
- Utilize Laravel's Eloquent ORM for database interactions.
- Use Laravel's query builder for complex database operations.
- Create and maintain proper database migrations and seeders.

Directory Structure
```
app/
├── Domain/                    # Pure business logic
│   └── {EntityName}/
│       ├── Entity.php
│       ├── ValueObject/
│       ├── Repository/
│       ├── Service/
│       ├── Command/
│       ├── CommandHandler/
│       ├── Query/
│       ├── QueryHandler/
│       ├── Service/
│       └── Event/
├── Application/               # Use cases orchestration
│   ├── Shared/
│   │   └── Bus/
│   │       ├── CommandBus.php
│   │       ├── QueryBus.php
│   │       ├── SimpleCommandBus.php
│   │       └── SimpleQueryBus.php
├── Infrastructure/            # Technical implementations
│   └── Mappers/
```

Laravel Best Practices
- Use Eloquent ORM and Query Builder over raw SQL queries when possible
- Implement Repository and Service patterns for better code organization and reusability
- Utilize Laravel's built-in authentication and authorization features (Sanctum, Policies)
- Leverage Laravel's caching mechanisms (Redis, Memcached) for improved performance
- Use job queues and Laravel Horizon for handling long-running tasks and background processing
- Implement comprehensive testing using PHPUnit and Laravel Pest for unit, feature, and browser tests
- Use API resources and versioning for building robust and maintainable APIs
- Implement proper error handling and logging using Laravel's exception handler and logging facade
- Utilize Laravel's validation features, including Form Requests, for data integrity
- Implement database indexing and use Laravel's query optimization features for better performance
- Use Laravel Telescope for debugging and performance monitoring in development
- Leverage Laravel Nova or Filament for rapid admin panel development
- Implement proper security measures, including CSRF protection, XSS prevention, and input sanitization

Code Architecture
* Naming Conventions:
- Use consistent naming conventions for folders, classes, and files.
- Follow Laravel's conventions: singular for models, plural for controllers (e.g., User.php, UsersController.php).
- Use PascalCase for class names, camelCase for method names, and snake_case for database columns.
* Controller Design:
- Controllers should be final classes to prevent inheritance.
- Make controllers read-only (i.e., no property mutations).
- Avoid injecting dependencies directly into controllers. Instead, use method injection or service classes.
* Model Design:
- Models should be final classes to ensure data integrity and prevent unexpected behavior from inheritance.
* Services:
- Create a Services folder within the app directory.
- Organize services into model-specific services and other required services.
- Service classes should be final and read-only.
- Use services for complex business logic, keeping controllers thin.
* Routing:
- Maintain consistent and organized routes.
- Create separate route files for each major model or feature area.
- Group related routes together (e.g., all user-related routes in routes/user.php).
* Type Declarations:
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.
- Leverage PHP 8.3+ features like union types and nullable types when necessary.
* Data Type Consistency:
- Be consistent and explicit with data type declarations throughout the codebase.
- Use type hints for properties, method parameters, and return types.
- Leverage PHP's strict typing to catch type-related errors early.
* Error Handling:
- Use Laravel's exception handling and logging features to handle exceptions.
- Create custom exceptions when necessary.
- Use try-catch blocks for expected exceptions.
- Handle exceptions gracefully and return appropriate responses.

Domain-Driven Design Guidelines
- **Domain Models** (`core/Model/`): Pure business entities, no framework dependencies
- **Application Services** (`core/Application/`): Interfaces for infrastructure
- **Infrastructure** (`src/`): Symfony implementations of domain interfaces
- **Commands/Handlers**: CQRS pattern for write operations
- **Events**: Domain events for side effects

Key points
- Follow Laravel’s MVC architecture for clear separation of business logic, data, and presentation layers.
- Implement request validation using Form Requests to ensure secure and validated data inputs.
- Use Laravel’s built-in authentication system, including Laravel Sanctum for API token management.
- Ensure the REST API follows Laravel standards, using API Resources for structured and consistent responses.
- Leverage task scheduling and event listeners to automate recurring tasks and decouple logic.
- Implement database transactions using Laravel's database facade to ensure data consistency.
- Use Eloquent ORM for database interactions, enforcing relationships and optimizing queries.
- Implement API versioning for maintainability and backward compatibility.
- Optimize performance with caching mechanisms like Redis and Memcached.
- Ensure robust error handling and logging using Laravel’s exception handler and logging features.


Token Optimization Strategies

1. Efficient Code Retrieval
- Use specific search queries for codebase-retrieval
- Request only relevant code sections
- Focus on interfaces and contracts first
- Avoid retrieving large configuration files unless necessary

2. Targeted Information Gathering
```markdown
# Good: Specific request
"Show me the User entity, UserRepository interface, and CreateUserHandler implementation"

# Bad: Broad request  
"Show me all user-related code"
```

3. Incremental Development
- Start with interfaces and contracts
- Implement core business logic first
- Add infrastructure implementations last
- Test each layer independently

Speed and Quality Guidelines

1. Development Workflow
1. **Analyze Requirements**: Understand business rules first
2. **Design Interfaces**: Define contracts before implementation
3. **Implement Domain Logic**: Pure business logic 
4. **Add Infrastructure**: Framework-specific code 
5. **Write Tests**: Comprehensive test coverage
6. **Run Quality Gates**: Ensure code quality

2. Code Reuse Patterns
- Leverage existing abstractions
- Follow established naming conventions
- Use existing event patterns for side effects
- Reuse validation patterns and exception handling

Standard Workflow (plan → verification)
1) **PLAN:** user story, acceptance criteria, routes/DTOs, components, migrations, risks.
2) **Incremental implementation** .
3) **Tests:** create/update pest tests (back).
4) **Quality Gate (blocking):** linters/static/formatters/rector.
5) **Run E2E tests** using Playwright.
5) **Reports:** store under `./reports/**` (coverage, lint, Playwright).
6) **Delivery summary:** what changed, how to validate, risks & rollback.
7) **Documentation:** update README, API docs (adr, haiku and diagrams with mermaid), etc.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.14
- laravel/framework (LARAVEL) - v12
- laravel/octane (OCTANE) - v2
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== pest/core rules ===

## Pest

### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest <name>`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>


=== pest/v4 rules ===

## Pest 4

- Pest v4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in `tests/Browser/`.
- Use the `search-docs` tool for detailed guidance on utilizing these features.

### Browser Testing
- You can use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories within Pest v4 browser tests, as well as `RefreshDatabase` (when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests

<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);
});
</code-snippet>

<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
</laravel-boost-guidelines>
