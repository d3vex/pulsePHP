# 5. Examples & API Reference

## Quick Start Examples

### Example 1: Simple Hello World

```php
<?php
require_once __DIR__ . "/../src/app/App.php";

#[Controller("")]
class HomeController {
    public function __construct() {}
    
    #[Route("/", "GET")]
    public function index() {
        return ["message" => "Hello World!"];
    }
}

$app = App::start();
$app->getRouter()->setBaseUrl("");
$app->registerController(HomeController::class);
$app->run();
```

**Request:**
```
GET http://localhost:8000/
```

**Response:**
```json
{"message": "Hello World!"}
```

---

### Example 2: REST API for Posts

```php
<?php

#[Controller("/posts")]
class PostController {
    
    public function __construct(private PostService $service) {}
    
    // List all posts
    #[Route("", "GET")]
    public function list(#[Query("page")] $page, #[Query("limit")] $limit) {
        return $this->service->paginate(
            page: $page ?? 1,
            limit: $limit ?? 10
        );
    }
    
    // Get single post
    #[Route("/{id}", "GET")]
    public function show(#[Params("id")] $id) {
        $post = $this->service->findById($id);
        if (!$post) {
            throw new HTTPException("Post not found", 404);
        }
        return $post;
    }
    
    // Create post (protected)
    #[Route("", "POST")]
    #[Middleware(AuthMiddleware::class)]
    public function create(#[Body()] $data) {
        if (!isset($data['title']) || !isset($data['content'])) {
            throw new HTTPException("Missing required fields", 400);
        }
        return $this->service->create($data);
    }
    
    // Update post
    #[Route("/{id}", "PUT")]
    #[Middleware(AuthMiddleware::class)]
    public function update(#[Params("id")] $id, #[Body()] $data) {
        return $this->service->update($id, $data);
    }
    
    // Delete post
    #[Route("/{id}", "DELETE")]
    #[Middleware(AuthMiddleware::class)]
    #[Middleware(AdminMiddleware::class)]
    public function delete(#[Params("id")] $id) {
        $this->service->delete($id);
        return ["deleted" => true];
    }
}
```

**Usage:**

```
GET /api/posts                          # List posts
GET /api/posts?page=2&limit=20          # Paginated list
GET /api/posts/42                       # Get post 42
POST /api/posts                         # Create post (requires auth)
PUT /api/posts/42                       # Update post 42
DELETE /api/posts/42                    # Delete post 42 (requires admin)
```

---

### Example 3: Services with Dependency Injection

```php
<?php

// Logger service
class Logger {
    public function __construct() {}
    
    public function info(string $message) {
        echo "[INFO] $message\n";
    }
    
    public function error(string $message) {
        echo "[ERROR] $message\n";
    }
}

// Database service
class Database {
    public function __construct(private Logger $logger) {}
    
    public function query(string $sql): array {
        $this->logger->info("Executing: $sql");
        return [];
    }
}

// Repository
class UserRepository {
    public function __construct(
        private Database $db,
        private Logger $logger
    ) {}
    
    public function findAll(): array {
        $this->logger->info("Finding all users");
        return $this->db->query("SELECT * FROM users");
    }
    
    public function findById($id): ?array {
        $this->logger->info("Finding user $id");
        return $this->db->query("SELECT * FROM users WHERE id = $id")[0] ?? null;
    }
}

// Service
class UserService {
    public function __construct(private UserRepository $repository) {}
    
    public function list(): array {
        return $this->repository->findAll();
    }
    
    public function get($id): ?array {
        return $this->repository->findById($id);
    }
}

// Controller
#[Controller("/users")]
class UserController {
    public function __construct(private UserService $service) {}
    
    #[Route("", "GET")]
    public function list() {
        return $this->service->list();
    }
    
    #[Route("/{id}", "GET")]
    public function show(#[Params("id")] $id) {
        return $this->service->get($id);
    }
}

// Application setup
$app = App::start();
$app->getRouter()->setBaseUrl("/api");

$app->registerSharedService(Logger::class);
$app->registerSharedService(Database::class);
$app->registerSharedService(UserRepository::class);
$app->registerSharedService(UserService::class);
$app->registerController(UserController::class);

$app->run();
```

---

### Example 4: Authentication with Middleware

```php
<?php

// Token service
class TokenService {
    private array $tokens = [
        "valid-token-123" => ["user_id" => 1, "role" => "admin"],
        "valid-token-456" => ["user_id" => 2, "role" => "user"]
    ];
    
    public function validate(string $token): bool {
        return isset($this->tokens[$token]);
    }
    
    public function getUser(string $token): ?array {
        return $this->tokens[$token] ?? null;
    }
}

// Auth middleware
class AuthMiddleware implements MiddlewareInterface {
    public function __construct(private TokenService $tokenService) {}
    
    public function handle(RequestModel $request) {
        $auth = $request->getHeader("Authorization");
        
        if (!$auth || !str_starts_with($auth, "Bearer ")) {
            return false;
        }
        
        $token = str_replace("Bearer ", "", $auth);
        return $this->tokenService->validate($token);
    }
}

// Admin middleware
class AdminMiddleware implements MiddlewareInterface {
    public function __construct(private TokenService $tokenService) {}
    
    public function handle(RequestModel $request) {
        $auth = $request->getHeader("Authorization");
        $token = str_replace("Bearer ", "", $auth);
        
        $user = $this->tokenService->getUser($token);
        return $user && $user['role'] === 'admin';
    }
}

// Protected controller
#[Controller("/api")]
class ProtectedController {
    
    #[Route("/public", "GET")]
    public function publicRoute() {
        return ["type" => "public"];
    }
    
    #[Route("/protected", "GET")]
    #[Middleware(AuthMiddleware::class)]
    public function protectedRoute() {
        return ["type" => "protected"];
    }
    
    #[Route("/admin", "GET")]
    #[Middleware(AuthMiddleware::class)]
    #[Middleware(AdminMiddleware::class)]
    public function adminRoute() {
        return ["type" => "admin"];
    }
}
```

**Usage:**

```bash
# Public route - no auth needed
curl http://localhost:8000/api/public

# Protected route - requires valid token
curl -H "Authorization: Bearer valid-token-123" http://localhost:8000/api/protected

# Admin route - requires admin token
curl -H "Authorization: Bearer valid-token-123" http://localhost:8000/api/admin
```

---

## API Reference

### App Class

```php
class App {
    // Start the application
    public static function start(): App
    
    // Register services
    public function registerService(string $serviceClass, ?Closure $constructor = null): void
    public function registerSharedService(string $serviceClass, ?Closure $constructor = null): void
    public function registerController(string $controllerClass): void
    
    // Configuration
    public function getRouter(): Router
    
    // Run the application
    public function run(): void
}
```

### Router Class

```php
class Router {
    // Configure
    public function setBaseUrl(string $baseUrl): void
    public function setDefaultHeader(string $name, string $value): void
    public function getDefaultHeaders(): array
    
    // Register routes
    public function registerController(string $controller): void
    public function addRoute(string $path, string $method, ...$controllers): void
    
    // Convenience methods
    public function get(string $path, ...$controllers): void
    public function post(string $path, ...$controllers): void
    public function put(string $path, ...$controllers): void
    public function patch(string $path, ...$controllers): void
    public function delete(string $path, ...$controllers): void
    public function options(string $path, ...$controllers): void
    
    // Matching
    public function matchRoute(string $path, string $method): RouteDefinition|null
}
```

### RequestModel Class

```php
class RequestModel {
    // HTTP info
    public function getRequestMethod(): string
    public function getRequestUri(): string
    public function getProtocol(): string
    
    // Network info
    public function getHost(): ?string
    public function getPort(): ?int
    public function getClientIp(): ?string
    public function getServerIp(): ?string
    
    // Data access
    public function getQuery(string $key, $default = null)
    public function getQueries(): array
    public function getHeader(string $name): ?string
    public function getHeaders(): array
    public function getCookie(string $name): ?string
    public function getCookies(): array
    public function getBody(): array|string
    
    // Create from globals
    public static function fromGlobals(): RequestModel
}
```

### ResponseModel Class

```php
class ResponseModel {
    // Configuration
    public function setStatusCode(int $code): void
    public function setHeader(string $name, string $value): void
    public function setHeaders(array $headers): void
    public function setBody(array|string $content): void
    
    // Send response
    public function send(): void
    public function isSent(): bool
}
```

### IOCContainer Class

```php
class IOCContainer {
    // Registration
    public function registerShared(string $class, ?Closure $factory = null): void
    public function registerDedicated(string $class, ?Closure $factory = null): void
    
    // Resolution
    public function get(string $class): object
    public function has(string $class): bool
}
```

### Attributes

#### Controller Attribute

```php
#[Attribute(Attribute::TARGET_CLASS)]
class Controller {
    public function __construct(public string $baseUrl = '') {}
}

// Usage
#[Controller("/api/users")]
class UserController { }
```

#### Route Attribute

```php
#[Attribute(Attribute::TARGET_METHOD)]
class Route {
    public function __construct(
        public string $path,
        public string $method = 'GET'
    ) {}
}

// Usage
#[Route("/list", "GET")]
public function list() { }
```

#### Middleware Attribute

```php
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Middleware {
    public function __construct(public string $middlewareClass) {}
}

// Usage
#[Middleware(AuthMiddleware::class)]
public function admin() { }
```

#### Parameter Attributes

```php
// Path parameters
#[Params("id")]
public function show(#[Params("id")] $id) { }

// Query parameters
#[Query("search")]
public function search(#[Query("search")] $term) { }

// Headers
#[Header("Authorization")]
public function protected(#[Header("Authorization")] $auth) { }

// Request body
#[Body()]
public function create(#[Body()] $data) { }

// Full request
#[Request()]
public function full(#[Request()] RequestModel $req) { }
```

### Exception Classes

#### HTTPException

```php
class HTTPException extends Exception {
    public function __construct(string $message, int $code) {}
    public function getHttpCode(): int
}
```

#### Middleware Exceptions

```php
class InvalidMiddlewareExceptions extends Exception {}
class MiddlewareReturnFalseExceptions extends Exception {}
```

#### Routing Exceptions

```php
class RouteRegisterInvalidHandlerException extends Exception {}
class RouteRegisterInvalidMiddlewareException extends Exception {}
```

#### Container Exceptions

```php
class ContainerDefinitionDontExist extends Exception {}
class InvalidConstructorParameterException extends Exception {}
class InvalidBuiltinTypeConstructorParameterException extends Exception {}
class InvalidClassTypeConstructorParameterException extends Exception {}
class InvalidOptionalConstructorParameterException extends Exception {}
class InvalidAllowNullConstructorParameterException extends Exception {}
class InvalidLoopConstructorParameterException extends Exception {}
```

---

## Common Patterns

### Pattern 1: Controller Inheritance

```php
abstract class BaseController {
    protected function jsonResponse(array $data, int $status = 200): array {
        return ["data" => $data, "status" => $status];
    }
}

#[Controller("/users")]
class UserController extends BaseController {
    #[Route("/{id}", "GET")]
    public function show(#[Params("id")] $id) {
        return $this->jsonResponse(["id" => $id]);
    }
}
```

### Pattern 2: Service Locator (not recommended but possible)

```php
#[Controller("/api")]
class MyController {
    public function __construct(private IOCContainer $container) {}
    
    #[Route("/users", "GET")]
    public function list() {
        $service = $this->container->get(UserService::class);
        return $service->list();
    }
}
```

### Pattern 3: Request Validation

```php
class ValidationMiddleware implements MiddlewareInterface {
    public function handle(RequestModel $request) {
        if ($request->getRequestMethod() !== "POST") {
            return true;
        }
        
        $body = $request->getBody();
        
        if (!is_array($body) || empty($body)) {
            return false;
        }
        
        return true;
    }
}
```

### Pattern 4: Rate Limiting

```php
class RateLimitMiddleware implements MiddlewareInterface {
    private static array $requests = [];
    private const LIMIT_PER_MINUTE = 60;
    
    public function handle(RequestModel $request) {
        $ip = $request->getClientIp();
        $now = time();
        
        if (!isset(self::$requests[$ip])) {
            self::$requests[$ip] = [];
        }
        
        // Clean old requests
        self::$requests[$ip] = array_filter(
            self::$requests[$ip],
            fn($time) => $now - $time < 60
        );
        
        if (count(self::$requests[$ip]) >= self::LIMIT_PER_MINUTE) {
            return false;
        }
        
        self::$requests[$ip][] = $now;
        return true;
    }
}
```

---

## Testing Your API

### Using cURL

```bash
# GET request
curl http://localhost:8000/api/users

# GET with query parameters
curl "http://localhost:8000/api/users?page=2&limit=10"

# GET with headers
curl -H "Authorization: Bearer token123" http://localhost:8000/api/protected

# POST with JSON body
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com"}'

# PUT request
curl -X PUT http://localhost:8000/api/users/42 \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane"}'

# DELETE request
curl -X DELETE http://localhost:8000/api/users/42
```

### Using Postman

1. Create new request
2. Set method (GET, POST, etc.)
3. Enter URL with base path
4. Add headers in Headers tab
5. Add JSON body in Body tab
6. Send request

---

## Performance Tips

1. **Use shared services for expensive operations**
   ```php
   $app->registerSharedService(DatabaseConnection::class);
   ```

2. **Cache frequently used data**
   ```php
   $app->registerSharedService(Cache::class);
   ```

3. **Minimize middleware overhead**
   ```php
   // Only add middleware where needed
   #[Middleware(AuthMiddleware::class)]
   public function protectedRoute() { }
   ```

4. **Use connection pooling**
   ```php
   $app->registerSharedService(ConnectionPool::class);
   ```

5. **Profile your code**
   ```php
   class TimingMiddleware implements MiddlewareInterface {
       public function handle(RequestModel $request) {
           // Log request timing
           return true;
       }
   }
   ```
