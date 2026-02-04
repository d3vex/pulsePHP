# 3. Dependency Injection Container

## Overview

The IoC (Inversion of Control) Container is a lightweight dependency injection system that manages object creation and dependency resolution. It supports both **shared** (singleton) and **dedicated** (transient) service registration.

## Key Concepts

### Shared vs Dedicated Services

#### Shared Services (Singletons)

A shared service is instantiated once and reused throughout the application lifecycle:

```php
// Register as shared
$app->registerSharedService(DatabaseConnection::class);

// Every request gets the same instance
$service1 = $container->get(DatabaseConnection::class);
$service2 = $container->get(DatabaseConnection::class);
// $service1 === $service2  (true)
```

**Use shared services for:**
- Database connections
- Configuration objects
- Logger instances
- Cache managers
- API clients

#### Dedicated Services (Transient)

A dedicated service creates a new instance each time it's requested:

```php
// Register as dedicated
$app->registerService(RequestHandler::class);

// Each request gets a new instance
$handler1 = $container->get(RequestHandler::class);
$handler2 = $container->get(RequestHandler::class);
// $handler1 === $handler2  (false)
```

**Use dedicated services for:**
- Request handlers
- Command objects
- Temporary processors
- Context-specific objects

## Service Registration

### Basic Registration

In your `App` class, override `registerUserServices()`:

```php
public function registerUserServices(): void {
    // Shared service
    $app->registerSharedService(DatabaseConnection::class);
    
    // Dedicated service
    $app->registerService(UserRepository::class);
}
```

### Custom Factory Functions

Register services with custom initialization:

```php
public function registerUserSharedService(): void {
    $this->registerSharedService(
        Configuration::class,
        function($container) {
            $config = new Configuration();
            $config->load('/etc/config.php');
            return $config;
        }
    );
}
```

The factory function receives the container as a parameter, allowing you to resolve other dependencies:

```php
$this->registerSharedService(
    UserService::class,
    function($container) {
        $db = $container->get(DatabaseConnection::class);
        $logger = $container->get(Logger::class);
        return new UserService($db, $logger);
    }
);
```

### Automatic Registration

When using PHP constructor type hints, the container automatically resolves dependencies:

```php
class UserService {
    public function __construct(
        private DatabaseConnection $db,
        private Logger $logger
    ) {}
}

// Register service - dependencies resolved automatically
$app->registerSharedService(UserService::class);

// The container will:
// 1. Detect DatabaseConnection dependency
// 2. Detect Logger dependency
// 3. Resolve both automatically
// 4. Create UserService with resolved dependencies
```

## Constructor Dependency Injection

The container uses PHP Reflection to analyze constructor parameters and automatically inject dependencies.

### Requirements

Constructor parameters must meet these conditions:

1. **Have type hints** - No support for built-in types
```php
// ✓ Valid
public function __construct(private DatabaseConnection $db) {}

// ✗ Invalid - no type hint
public function __construct(private $db) {}

// ✗ Invalid - built-in type
public function __construct(private string $name) {}
```

2. **Cannot be optional**
```php
// ✓ Valid
public function __construct(private Logger $logger) {}

// ✗ Invalid - optional parameter
public function __construct(private Logger $logger = null) {}
```

3. **Cannot be nullable**
```php
// ✓ Valid
public function __construct(private Logger $logger) {}

// ✗ Invalid - nullable parameter
public function __construct(private ?Logger $logger) {}
```

4. **Must reference existing classes**
```php
// ✓ Valid
public function __construct(private MyClass $obj) {}

// ✗ Invalid - class doesn't exist
public function __construct(private NonExistentClass $obj) {}
```

## Multi-Level Dependency Resolution

The container recursively resolves nested dependencies:

```php
class Logger {
    public function __construct() {}
}

class DatabaseConnection {
    public function __construct(private Logger $logger) {}
}

class UserService {
    public function __construct(
        private DatabaseConnection $db,
        private Logger $logger
    ) {}
}

// Register services
$app->registerSharedService(Logger::class);
$app->registerSharedService(DatabaseConnection::class);
$app->registerSharedService(UserService::class);

// Dependency chain:
// UserService depends on:
//   - DatabaseConnection depends on Logger
//   - Logger
```

## Circular Dependency Detection

The container detects and prevents circular dependencies:

```php
class ServiceA {
    public function __construct(private ServiceB $b) {}
}

class ServiceB {
    public function __construct(private ServiceA $a) {}
}

// This throws InvalidLoopConstructorParameterException
$app->registerSharedService(ServiceA::class);
$app->registerSharedService(ServiceB::class);
```

## Checking Service Registration

```php
$container = $app->container;

// Check if service is registered
if ($container->has(UserService::class)) {
    $service = $container->get(UserService::class);
}
```

## Complete Example

```php
<?php

// Define services
class Logger {
    public function __construct() {}
    
    public function log($message) {
        echo "[LOG] $message\n";
    }
}

class DatabaseConnection {
    public function __construct(private Logger $logger) {
        $this->logger->log("Database connected");
    }
    
    public function query($sql) {
        return [];
    }
}

class UserRepository {
    public function __construct(
        private DatabaseConnection $db,
        private Logger $logger
    ) {}
    
    public function findAll() {
        $this->logger->log("Finding all users");
        return $this->db->query("SELECT * FROM users");
    }
}

class UserService {
    public function __construct(private UserRepository $repository) {}
    
    public function list() {
        return $this->repository->findAll();
    }
}

class UserController {
    public function __construct(private UserService $service) {}
    
    #[Route("/users", "GET")]
    public function list() {
        return $this->service->list();
    }
}

// Application setup
$app = App::start();

// Register services
$app->registerSharedService(Logger::class);
$app->registerSharedService(DatabaseConnection::class);
$app->registerSharedService(UserRepository::class);
$app->registerSharedService(UserService::class);
$app->registerController(UserController::class);

$app->run();
```

### Dependency Resolution Flow

```
1. Request: GET /api/users
2. Router matches UserController::list route
3. Container needs to instantiate UserController
4. Container detects UserService dependency
5. Container needs to instantiate UserService
6. Container detects UserRepository dependency
7. Container needs to instantiate UserRepository
8. Container detects DatabaseConnection and Logger dependencies
9. Container instantiates Logger (no dependencies)
10. Container instantiates DatabaseConnection (Logger injected)
11. Container instantiates UserRepository (DB and Logger injected)
12. Container instantiates UserService (Repository injected)
13. Container instantiates UserController (Service injected)
14. UserController::list() executes
```

## Service Lifecycle

### Shared Service Lifecycle

```
App Start
   ↓
First request to service
   ↓
Container creates instance
   ↓
Instance stored in container
   ↓
All subsequent requests return same instance
   ↓
App End
```

### Dedicated Service Lifecycle

```
Request for service
   ↓
Container creates new instance
   ↓
Instance returned
   ↓
Instance eligible for garbage collection
   ↓
Next request for service
   ↓
Container creates new instance
```

## Error Handling

### Missing Constructor Type Hints

```
InvalidConstructorParameterException
Message: "Parameter 'param' of class 'ClassName' has no type hint"
```

### Built-in Type Parameters

```
InvalidBuiltinTypeConstructorParameterException
Message: "Parameter 'param' of class 'ClassName' uses built-in type 'string'"
```

### Non-existent Class Type

```
InvalidClassTypeConstructorParameterException
Message: "Parameter 'param' of class 'ClassName' references non-existent class"
```

### Optional Parameters

```
InvalidOptionalConstructorParameterException
Message: "Parameter 'param' of class 'ClassName' cannot be optional"
```

### Null-able Parameters

```
InvalidAllowNullConstructorParameterException
Message: "Parameter 'param' of class 'ClassName' cannot be nullable"
```

### Undefined Service

```
ContainerDefinitionDontExist
Message: "Service 'ClassName' is not registered in container"
```

## Best Practices

1. **Register at startup** - Register all services in `registerUserServices()`
2. **Use shared for singletons** - Database, cache, config
3. **Use dedicated for transient** - Request-specific objects
4. **Keep constructors clean** - Only inject required dependencies
5. **Avoid optional parameters** - Make all dependencies required
6. **Type hint properly** - Always provide class type hints
7. **Use factories for complex setup** - Custom initialization logic
8. **Check service exists** - Use `$container->has()` before access

## Accessing the Container

In your controller or service:

```php
class MyController {
    public function __construct(private IOCContainer $container) {}
    
    #[Route("/example", "GET")]
    public function example() {
        $service = $this->container->get(SomeService::class);
        return [];
    }
}
```

Or access through Bootstrap:

```php
// In App class
public function someMethod() {
    $service = $this->container->get(SomeService::class);
}
```
