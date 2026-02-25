# 2. Routing Guide

## Overview

D3vex\Pulsephp\Core uses PHP 8 attributes to define routes. Routes are defined directly on controller methods using the `#[Route]` attribute, eliminating the need for separate route configuration files.

## Basic Route Definition

### Controller Setup

Define a controller with a base path using the `#[Controller]` attribute:

```php
#[Controller("/users")]
class UserController {
    public function __construct() {}
    
    #[Route("/", "GET")]
    public function list() {
        return ["users" => []];
    }
}
```

When registered with base URL `/api`, this creates:
- **Path:** `/api/users/`
- **Method:** GET

### Accessing the Route

```
GET http://localhost:8000/api/users/
```

## HTTP Methods

The framework supports all standard HTTP methods:

```php
#[Route("/users", "GET")]
public function list() { }

#[Route("/users", "POST")]
public function create() { }

#[Route("/users/{id}", "PUT")]
public function update(#[Params("id")] $id) { }

#[Route("/users/{id}", "PATCH")]
public function patch(#[Params("id")] $id) { }

#[Route("/users/{id}", "DELETE")]
public function delete(#[Params("id")] $id) { }
```

### Convenience Methods

Use convenience methods on the router instead of `#[Route]`:

```php
$router->get("/path", [Controller::class, "method"]);
$router->post("/path", [Controller::class, "method"]);
$router->put("/path", [Controller::class, "method"]);
$router->patch("/path", [Controller::class, "method"]);
$router->delete("/path", [Controller::class, "method"]);
```

**With Middleware:**

```php
$router->get("/path", AuthMiddleware::class, [Controller::class, "method"]);
```

## Path Parameters

Extract parameters from URL path using curly braces `{}`:

```php
#[Controller("/users")]
class UserController {
    
    #[Route("/{id}", "GET")]
    public function show(#[Params("id")] $id) {
        return ["id" => $id];
    }
    
    #[Route("/{userId}/posts/{postId}", "GET")]
    public function getUserPost(
        #[Params("userId")] $userId,
        #[Params("postId")] $postId
    ) {
        return [
            "user" => $userId,
            "post" => $postId
        ];
    }
}
```

**Accessing:**
```
GET /api/users/123
GET /api/users/123/posts/456
```

### Path Parameter Extraction

Path parameters are automatically extracted from the URL:

```php
// Route: /api/products/{id}/details/{section}
// URL: /api/products/42/details/specs

#[Route("/{id}/details/{section}", "GET")]
public function getProductDetails(
    #[Params("id")] $id,
    #[Params("section")] $section
) {
    // $id = "42"
    // $section = "specs"
}
```

## Query Parameters

Access query string parameters using `#[Query]`:

```php
#[Route("/search", "GET")]
public function search(#[Query("q")] $query, #[Query("limit")] $limit) {
    return [
        "query" => $query,
        "limit" => $limit
    ];
}
```

**Accessing:**
```
GET /api/search?q=hello&limit=10

// $query = "hello"
// $limit = "10"
```

## Headers

Extract HTTP headers using `#[Header]`:

```php
#[Route("/protected", "GET")]
public function protected(#[Header("Authorization")] $auth) {
    return ["token" => $auth];
}
```

**Example Request:**
```
GET /api/protected
Authorization: Bearer xyz123

// $auth = "Bearer xyz123"
```

## Request Body

Access the full request body using `#[Body]`:

```php
#[Route("/create", "POST")]
public function create(#[Body()] $body) {
    return ["received" => $body];
}
```

For POST/PUT requests with JSON body, the body is automatically parsed.

## Full Request Object

Inject the entire `RequestModel` when you need full access to request data:

```php
#[Route("/full", "GET")]
public function full(RequestModel $request) {
    return [
        "method" => $request->getRequestMethod(),
        "uri" => $request->getRequestUri(),
        "headers" => $request->getHeaders(),
        "queries" => $request->getQueries(),
        "cookies" => $request->getCookies(),
        "ip" => $request->getClientIp()
    ];
}
```

### RequestModel Methods

```php
// HTTP information
$request->getRequestMethod()    // GET, POST, PUT, etc.
$request->getRequestUri()       // /api/users/123?page=1

// Query parameters
$request->getQuery("key")       // Get single query parameter
$request->getQueries()          // Get all query parameters

// Headers
$request->getHeader("Accept")   // Get single header
$request->getHeaders()          // Get all headers

// Cookies
$request->getCookie("session")  // Get single cookie
$request->getCookies()          // Get all cookies

// Body
$request->getBody()             // Get request body

// Network information
$request->getClientIp()         // Client IP address
$request->getServerIp()         // Server IP address
$request->getHost()             // Host name
$request->getPort()             // Port number
```

## Parameter Injection

You can mix different parameter types in a single method:

```php
#[Route("/users/{id}/posts", "GET")]
public function getUserPosts(
    #[Params("id")] $userId,
    #[Query("page")] $page,
    #[Header("Accept")] $contentType,
    RequestModel $request
) {
    return [
        "user" => $userId,
        "page" => $page ?? 1,
        "accept" => $contentType,
        "method" => $request->getRequestMethod()
    ];
}
```

## Middleware on Routes

Apply middleware to individual routes:

```php
#[Route("/admin", "GET")]
#[Middleware(AdminMiddleware::class)]
public function admin() {
    return ["admin" => true];
}
```

Or apply multiple middleware:

```php
#[Route("/admin/users", "POST")]
#[Middleware(AuthMiddleware::class)]
#[Middleware(AdminMiddleware::class)]
public function createUser() {
    return ["created" => true];
}
```

See [Middleware Guide](./4-middleware.md) for more details.

## Complete Example

```php
<?php

#[Controller("/api/products")]
class ProductController {
    
    public function __construct(private ProductService $service) {}
    
    // List all products with optional pagination
    #[Route("", "GET")]
    public function list(#[Query("page")] $page, #[Query("limit")] $limit) {
        return [
            "products" => $this->service->list(
                page: $page ?? 1,
                limit: $limit ?? 10
            )
        ];
    }
    
    // Get single product by ID
    #[Route("/{id}", "GET")]
    public function show(#[Params("id")] $id) {
        return $this->service->findById($id);
    }
    
    // Create new product
    #[Route("", "POST")]
    #[Middleware(AuthMiddleware::class)]
    public function create(#[Body()] $data) {
        return $this->service->create($data);
    }
    
    // Update product
    #[Route("/{id}", "PUT")]
    #[Middleware(AuthMiddleware::class)]
    public function update(#[Params("id")] $id, #[Body()] $data) {
        return $this->service->update($id, $data);
    }
    
    // Delete product
    #[Route("/{id}", "DELETE")]
    #[Middleware(AdminMiddleware::class)]
    public function delete(#[Params("id")] $id) {
        return $this->service->delete($id);
    }
}
```

## Registering Routes

In your `public/index.php`:

```php
$app = App::start();
$app->getRouter()->setBaseUrl("/api");

// Register all controllers with their attribute-based routes
$app->registerController(UserController::class);
$app->registerController(ProductController::class);

// Or register a single route dynamically
$app->getRouter()->get("/health", [HealthController::class, "check"]);

$app->run();
```

## Route Matching

The router matches routes with the following priority:

1. Exact method match (GET, POST, etc.)
2. Pattern matching (path parameters)
3. Return 404 if no match found

**Pattern Example:**
```
Route pattern: /users/{id}/posts/{postId}
Compiled regex: /^\/users\/([^\/]+)\/posts\/([^\/]+)$/
Matches: /users/123/posts/456
Extracts: id=123, postId=456
```

## Error Responses

### 404 Not Found

If no route matches:
```
HTTP 404
```

### Missing Required Parameters

The framework provides default values for missing query parameters:

```php
#[Query("page")] $page  // $page is null if not provided
```

You can set defaults:
```php
$page ?? 1  // Use 1 if not provided
```

### HTTP Exceptions

Throw HTTP exceptions from your routes:

```php
#[Route("/{id}", "GET")]
public function show(#[Params("id")] $id) {
    if (!$id) {
        throw new HTTPException("Product not found", 404);
    }
    return $this->service->find($id);
}
```

## Base URL Configuration

All routes are automatically prefixed with the base URL:

```php
$app->getRouter()->setBaseUrl("/api");
```

**Without base URL:**
```
GET /users
```

**With base URL `/api`:**
```
GET /api/users
```

**With base URL `/v1/api`:**
```
GET /v1/api/users
```

## Tips & Best Practices

1. **Use meaningful paths** - Keep routes RESTful and intuitive
2. **Group related routes** - Use controller base paths
3. **Limit path parameters** - Use query strings for filtering
4. **Validate inputs** - Always validate path and query parameters
5. **Document parameters** - Use PHPDoc comments for clarity
6. **Apply middleware early** - Add security middleware at controller level
