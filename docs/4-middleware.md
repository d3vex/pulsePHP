# 4. Middleware System

## Overview

Middleware is a powerful mechanism for processing requests before they reach your route handlers. PulsePHP middleware can perform authentication, logging, validation, rate limiting, and more.

## Understanding Middleware

Middleware is code that sits between the request and your route handler:

```
Request → Middleware 1 → Middleware 2 → Route Handler → Response
```

Each middleware can:
- Access the request data
- Allow/deny request processing (return true/false)
- Modify request state
- Throw exceptions

## Middleware Interface

All middleware must implement `MiddlewareInterface`:

```php
interface MiddlewareInterface {
    public function handle(RequestModel $request);
}
```

### Implementation

```php
class AuthMiddleware implements MiddlewareInterface {
    public function __construct() {}
    
    public function handle(RequestModel $request) {
        $token = $request->getHeader("Authorization");
        
        if (!$token) {
            return false;  // Reject request
        }
        
        return true;       // Allow request
    }
}
```

## Creating Middleware

### Authentication Middleware

```php
class AuthMiddleware implements MiddlewareInterface {
    private bool $isValid = false;
    
    public function __construct(private TokenValidator $validator) {}
    
    public function handle(RequestModel $request) {
        $authHeader = $request->getHeader("Authorization");
        
        if (!$authHeader) {
            return false;
        }
        
        // Extract token from "Bearer xyz123"
        $token = str_replace("Bearer ", "", $authHeader);
        
        if (!$this->validator->isValid($token)) {
            return false;
        }
        
        $this->isValid = true;
        return true;
    }
}
```

### Logging Middleware

```php
class LoggingMiddleware implements MiddlewareInterface {
    public function __construct(private Logger $logger) {}
    
    public function handle(RequestModel $request) {
        $this->logger->info(sprintf(
            "%s %s from %s",
            $request->getRequestMethod(),
            $request->getRequestUri(),
            $request->getClientIp()
        ));
        
        return true;  // Always allow
    }
}
```

### Rate Limiting Middleware

```php
class RateLimitMiddleware implements MiddlewareInterface {
    private static array $requests = [];
    private const LIMIT = 100;
    private const WINDOW = 60;  // seconds
    
    public function handle(RequestModel $request) {
        $ip = $request->getClientIp();
        $now = time();
        
        // Clean old requests
        if (!isset(self::$requests[$ip])) {
            self::$requests[$ip] = [];
        }
        
        self::$requests[$ip] = array_filter(
            self::$requests[$ip],
            fn($time) => $now - $time < self::WINDOW
        );
        
        // Check limit
        if (count(self::$requests[$ip]) >= self::LIMIT) {
            return false;
        }
        
        self::$requests[$ip][] = $now;
        return true;
    }
}
```

### CORS Middleware

```php
class CORSMiddleware implements MiddlewareInterface {
    public function handle(RequestModel $request) {
        // This middleware just validates, actual CORS headers
        // are set in response configuration
        $origin = $request->getHeader("Origin");
        
        if (!$origin) {
            return false;
        }
        
        if (!$this->isAllowedOrigin($origin)) {
            return false;
        }
        
        return true;
    }
    
    private function isAllowedOrigin(string $origin): bool {
        $allowed = [
            "http://localhost:3000",
            "https://example.com"
        ];
        
        return in_array($origin, $allowed);
    }
}
```

### Validation Middleware

```php
class ValidationMiddleware implements MiddlewareInterface {
    public function handle(RequestModel $request) {
        // Validate request body for POST/PUT
        if (!in_array($request->getRequestMethod(), ["POST", "PUT"])) {
            return true;
        }
        
        $body = $request->getBody();
        
        if (!is_array($body)) {
            return false;
        }
        
        if (empty($body)) {
            return false;
        }
        
        return true;
    }
}
```

## Registering Middleware

### As a Shared Service

Middleware must be registered as a service before use:

```php
public function registerUserSharedService(): void {
    $this->registerSharedService(AuthMiddleware::class);
    $this->registerSharedService(LoggingMiddleware::class);
}
```

### Class-Level Middleware

Apply middleware to all routes in a controller:

```php
#[Controller("/admin")]
#[Middleware(AuthMiddleware::class)]
class AdminController {
    // All routes in this controller require auth
    
    #[Route("/users", "GET")]
    public function listUsers() {
        return [];
    }
    
    #[Route("/users/{id}", "DELETE")]
    public function deleteUser(#[Params("id")] $id) {
        return [];
    }
}
```

### Method-Level Middleware

Apply middleware to specific routes:

```php
class UserController {
    
    #[Route("/users", "GET")]
    public function list() {
        return [];
    }
    
    #[Route("/users", "POST")]
    #[Middleware(AuthMiddleware::class)]
    public function create() {
        return [];
    }
    
    #[Route("/users/{id}", "DELETE")]
    #[Middleware(AuthMiddleware::class)]
    #[Middleware(AdminMiddleware::class)]
    public function delete(#[Params("id")] $id) {
        return [];
    }
}
```

### Multiple Middleware

Apply multiple middleware to a route - they execute in order:

```php
#[Route("/admin/users", "POST")]
#[Middleware(AuthMiddleware::class)]
#[Middleware(AdminMiddleware::class)]
#[Middleware(ValidationMiddleware::class)]
public function createUser() {
    // Executes: Auth → Admin → Validation → Handler
}
```

### Dynamic Registration

Register routes with middleware programmatically:

```php
$app->getRouter()->post(
    "/admin/users",
    AuthMiddleware::class,
    AdminMiddleware::class,
    [UserController::class, "create"]
);
```

## Middleware Execution Order

Middleware is executed in a predictable order:

1. **Class-level middleware** - Applied to entire controller
2. **Method-level middleware** - Applied to specific route

Multiple middleware at the same level execute in declaration order.

### Example

```php
#[Controller("/api")]
#[Middleware(LoggingMiddleware::class)]      // 1st
class ApiController {
    
    #[Route("/users", "POST")]
    #[Middleware(AuthMiddleware::class)]      // 2nd
    #[Middleware(ValidationMiddleware::class)] // 3rd
    public function create() {
        // Handler executes after all middleware
    }
}
```

**Execution order for POST /api/users:**
1. LoggingMiddleware::handle()
2. AuthMiddleware::handle()
3. ValidationMiddleware::handle()
4. ApiController::create()

## Middleware Exceptions

If middleware throws an exception or returns false, request processing stops:

```php
public function handle(RequestModel $request) {
    $token = $request->getHeader("Authorization");
    
    if (!$token) {
        // Stops execution here - returns 401
        throw new HTTPException("Unauthorized", 401);
    }
    
    if (!$this->isValid($token)) {
        // Also stops execution
        return false;
    }
    
    return true;  // Continues to next middleware
}
```

## Complete Example: Authentication Flow

```php
<?php

// 1. Define the middleware
class AuthMiddleware implements MiddlewareInterface {
    public function __construct(private TokenService $tokenService) {}
    
    public function handle(RequestModel $request) {
        $token = $request->getHeader("Authorization");
        
        if (!$token) {
            return false;
        }
        
        // Remove "Bearer " prefix
        $token = str_replace("Bearer ", "", $token);
        
        return $this->tokenService->validate($token);
    }
}

// 2. Define the controller
#[Controller("/api")]
#[Middleware(LoggingMiddleware::class)]
class UserController {
    
    public function __construct(private UserService $userService) {}
    
    // Public endpoint - no auth required
    #[Route("/users", "GET")]
    public function list() {
        return $this->userService->getAll();
    }
    
    // Protected endpoint - requires auth
    #[Route("/users", "POST")]
    #[Middleware(AuthMiddleware::class)]
    public function create(#[Body()] $data) {
        return $this->userService->create($data);
    }
    
    // Admin endpoint - requires auth + admin role
    #[Route("/users/{id}", "DELETE")]
    #[Middleware(AuthMiddleware::class)]
    #[Middleware(AdminMiddleware::class)]
    public function delete(#[Params("id")] $id) {
        return $this->userService->delete($id);
    }
}

// 3. Register middleware and controller
$app = App::start();
$app->registerSharedService(TokenService::class);
$app->registerSharedService(AuthMiddleware::class);
$app->registerSharedService(LoggingMiddleware::class);
$app->registerSharedService(AdminMiddleware::class);
$app->registerController(UserController::class);

$app->run();
```

## Request Flow with Middleware

```
HTTP Request: POST /api/users with Bearer token
   ↓
Router matches route → UserController::create
   ↓
Dispatcher loads middleware chain:
   - LoggingMiddleware (class-level)
   - AuthMiddleware (method-level)
   ↓
Dispatcher executes LoggingMiddleware
   - Logs: "POST /api/users from 192.168.1.1"
   - Returns true
   ↓
Dispatcher executes AuthMiddleware
   - Extracts "Bearer xyz123"
   - Validates token
   - Returns true
   ↓
Dispatcher resolves method parameters
   - Gets request body
   ↓
Controller method executes
   - $data contains parsed JSON
   - Returns new user data
   ↓
HTTP Response: 200 OK with user data
```

## Error Handling with Middleware

### Middleware Rejection

If middleware returns false:

```
HTTP Response: 403 Forbidden
Body: "Middleware rejected request"
```

### Middleware Exception

If middleware throws exception:

```
HTTP Response: Exception HTTP code
Body: Exception message
```

## Tips & Best Practices

1. **Keep middleware focused** - Single responsibility principle
2. **Register all middleware** - Register in `registerUserSharedService()`
3. **Document behavior** - Comment what middleware validates
4. **Log important events** - Use logging middleware for debugging
5. **Fail securely** - Return false rather than allowing uncertain requests
6. **Avoid side effects** - Don't modify request state unexpectedly
7. **Use exceptions** - Throw HTTPException for clear error messages
8. **Test middleware** - Middleware should have clear, testable logic

## Middleware Chain Debugging

To debug middleware execution:

```php
class DebugMiddleware implements MiddlewareInterface {
    public function __construct(private Logger $logger) {}
    
    public function handle(RequestModel $request) {
        $this->logger->info("DebugMiddleware executed for " . $request->getRequestUri());
        return true;
    }
}

// Add to your route
#[Route("/debug", "GET")]
#[Middleware(DebugMiddleware::class)]
public function debug() {
    return ["debug" => true];
}
```

Check logs to verify middleware execution order.
