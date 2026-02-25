# 1. Architecture Overview

## Framework Structure

D3vex\Pulsephp\Core follows a clean architecture pattern with clear separation of concerns:

```
Request → Kernel → Router → Dispatcher → Middleware → Controller → Response
```

### Core Components

#### 1. **Kernel** (`src/core/http/kernel.php`)
- Entry point for all HTTP requests
- Receives requests and returns responses
- Delegates route matching to the Router
- Handles HTTP exceptions

**Request Lifecycle:**
1. Receives `RequestModel` from `public/index.php`
2. Matches route using `Router::matchRoute()`
3. Returns 404 if no route found
4. Sets default headers from router configuration
5. Delegates to `Dispatcher` for route handling
6. Catches `HTTPException` and sets appropriate status codes

#### 2. **Router** (`src/core/routing/Router.php`)
- Registers controllers and individual routes
- Manages route matching with pattern compilation
- Stores HTTP method definitions (GET, POST, PUT, DELETE, PATCH)
- Maintains default response headers and base URL configuration

**Key Responsibilities:**
- Parse PHP 8 attributes from controllers
- Build compiled regex patterns for URL matching
- Extract route parameters from URLs
- Combine controller-level and method-level middleware

#### 3. **Dispatcher** (`src/core/routing/dispatcher.php`)
- Executes middleware chain
- Resolves method parameters from request data
- Calls the handler (controller method)
- Returns response data to be sent

**Parameter Resolution:**
- `RequestModel` parameter injection
- `#[Query(...)]` - Query string parameters
- `#[Header(...)]` - HTTP headers
- `#[Params(...)]` - URL path parameters
- `#[Body(...)]` - Request body

#### 4. **IoC Container** (`src/core/container/container.php`)
- Manages dependency injection
- Registers services as either **shared** (singleton) or **dedicated** (transient)
- Automatically resolves constructor dependencies
- Detects and prevents circular dependencies

#### 5. **Request Model** (`src/core/http/request.php`)
- Represents incoming HTTP request
- Provides access to:
  - Query parameters
  - Headers
  - Cookies
  - Request method and URI
  - Request body

#### 6. **Response Model** (`src/core/http/response.php`)
- Represents outgoing HTTP response
- Manages:
  - Status codes
  - Response headers
  - Response body (arrays auto-encoded as JSON)

## Request Lifecycle

### Step-by-Step Flow

```
1. User makes HTTP request
   ↓
2. public/index.php receives request
   ↓
3. App::start() initializes framework
   ↓
4. App::run() creates RequestModel from $_SERVER globals
   ↓
5. Kernel::handle() receives request
   ↓
6. Router::matchRoute() finds matching route
   ↓
7. Dispatcher processes middleware chain
   ↓
8. Dispatcher resolves controller method parameters
   ↓
9. Controller method executes with injected dependencies
   ↓
10. Response body is set with returned data
   ↓
11. Response::send() outputs headers and body
```

## Attribute-Based Architecture

The framework leverages PHP 8 attributes extensively:

### Controller Attributes
- `#[Controller(path)]` - Defines controller base path and optional middleware
- `#[Middleware(...)]` - Applies middleware at controller level

### Route Attributes
- `#[Route(path, method)]` - Defines method route with HTTP method
- `#[Middleware(...)]` - Applies middleware at method level (combined with controller middleware)

### Parameter Attributes
- `#[Params(name)]` - Extracts URL path parameters
- `#[Query(name)]` - Extracts query string parameters
- `#[Header(name)]` - Extracts HTTP header values
- `#[Body(dto)]` - Accesses request body
- `#[Request()]` - Injects full RequestModel

## Middleware Chain

Middleware is applied in the following order:

1. **Class-level middleware** (from `#[Controller]`)
2. **Method-level middleware** (from `#[Route]`)

Each middleware must implement `MiddlewareInterface`:

```php
interface MiddlewareInterface {
    public function handle(RequestModel $request);
}
```

**Execution:**
- Middleware is executed sequentially
- If middleware returns `false`, request is rejected
- Middleware exception stops execution

## Dependency Injection

The framework uses constructor-based dependency injection:

```php
class UserService {
    public function __construct(private Logger $logger) {}
}

class UserController {
    public function __construct(private UserService $service) {}
}
```

**Container Rules:**
- All constructor parameters must have type hints (no builtin types)
- No optional or nullable parameters allowed
- Circular dependencies are detected and prevented
- Services must be registered before use

## Error Handling

### HTTP Exceptions

```php
class HTTPExceptions extends Exception {
    private int $httpCode;
    
    public function __construct(string $message, int $code) {
        $this->httpCode = $code;
    }
}
```

Thrown exceptions are caught by Kernel and converted to appropriate HTTP responses.

### 404 Handling

If no route matches the request, the kernel automatically returns 404.

## Service Registration

### In App Class

```php
public function registerUserServices(): void {
    // Dedicated service (transient - new instance each time)
    $this->registerService(MyService::class);
    
    // Shared service (singleton - same instance always)
    $this->registerSharedService(Config::class);
    
    // Register controller
    $this->registerController(UserController::class);
}
```

### Container Registration

```php
$app->registerSharedService(MyService::class, function($container) {
    return new MyService(/* dependencies */);
});
```

## URL Pattern Matching

The router converts attribute-defined routes into compiled regex patterns:

**Example:**
- Attribute: `#[Route("/users/{id}/posts/{postId}")]`
- Compiled regex: `/^\/users\/([^\/]+)\/posts\/([^\/]+)$/`
- Parameters extracted: `["id" => "123", "postId" => "456"]`

Special characters like `/` are used as delimiters, and parameters are captured as `([^\/]+)`.

## Base URL Configuration

```php
$app->getRouter()->setBaseUrl("/api");
```

All routes are prefixed with the base URL:
- Route: `#[Route("/users")]`
- Final path: `/api/users`

## Default Headers

```php
$app->getRouter()->setDefaultHeader("X-API-Version", "1.0");
```

These headers are automatically added to all responses.
