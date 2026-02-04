# PulsePHP

A lightweight, zero-complexity PHP framework built from scratch for school projects and simple API development. PulsePHP provides a clean, attribute-based routing system with built-in IoC container and middleware support.

## 🎯 Project Context

PulsePHP was created as a response to overly complex frameworks and school project boilerplate. The goal was to build a simple, understandable framework that teaches routing, dependency injection, and middleware concepts without unnecessary complexity.

## ✨ Features

- **Attribute-Based Routing** - Define routes using PHP 8 attributes on controller methods
- **IoC Container** - Lightweight dependency injection container with shared/dedicated service management
- **Middleware Support** - Chain middleware at class and method levels for request processing
- **Request/Response Models** - Clean HTTP abstraction with access to query parameters, headers, and request body
- **Automatic Dependency Resolution** - Constructor-based dependency injection with loop detection
- **Default Headers** - Set application-wide default response headers
- **Base URL Configuration** - Easily configure API base path
- **Error Handling** - Built-in HTTP exception handling

## ⚠️ Known Limitations

The following features are **not currently implemented** but may be added in future versions:

- Response cookies (sending cookies in responses)
- File uploads and multipart form handling
- Built-in request validation
- Session management
- ORM/Query builder

These features are not critical for basic school projects but can be implemented as extensions if needed.

## 🚀 Quick Start

### Installation

1. Clone the repository
2. Ensure PHP 8.0+ is installed
3. No external dependencies required

### Basic Setup

Create your `public/index.php`:

```php
<?php
require_once __DIR__ . "/../src/app/App.php";

// Create and start the application
$app = App::start();

// Configure the API base URL
$app->getRouter()->setBaseUrl("/api");

// Register your services and controllers
$app->registerController(YourController::class);

// Run the application
$app->run();
```

### Your First Controller

```php
<?php

#[Controller("/users")]
class UserController {
    
    public function __construct() {}

    #[Route("/{id}", "GET")]
    public function getUser(#[Params("id")] $id, RequestModel $req) {
        return [
            "id" => $id,
            "message" => "User retrieved successfully"
        ];
    }
}
```

## 📚 Documentation

- [Architecture Overview](./docs/1-architecture.md) - Framework structure and request lifecycle
- [Routing Guide](./docs/2-routing.md) - Define routes and handle HTTP methods
- [Dependency Injection](./docs/3-ioc-container.md) - Manage dependencies with the IoC container
- [Middleware System](./docs/4-middleware.md) - Create and apply middleware
- [Examples & API Reference](./docs/5-examples.md) - Code examples and API reference

## 📂 Project Structure

```
src/
├── app/
│   └── App.php                 # Main application class
├── core/
│   ├── attributes/             # PHP attributes for routing and middleware
│   ├── bootstrap/              # Application bootstrap and configuration
│   ├── container/              # Dependency injection container
│   ├── http/                   # Request/Response models and kernel
│   ├── logger/                 # Logging utility
│   └── routing/                # Router, dispatcher, and middleware
public/
└── index.php                   # Application entry point
```

## 🔧 Configuration

### Setting Base URL

```php
$app->getRouter()->setBaseUrl("/api");
```

### Setting Default Headers

```php
$app->getRouter()->setDefaultHeader("X-Custom-Header", "value");
```

### Registering Services

```php
// Dedicated service (new instance each time)
$app->registerService(MyService::class);

// Shared service (singleton)
$app->registerSharedService(MyService::class);

// With custom factory
$app->registerSharedService(Config::class, function($container) {
    return new Config('/path/to/config');
});
```

## 📖 Examples

### Handling Query Parameters

```php
#[Route("/search", "GET")]
public function search(#[Query("q")] $query, #[Query("limit")] $limit) {
    return [
        "query" => $query,
        "limit" => $limit ?? 10
    ];
}
```

### Accessing Request Headers

```php
#[Route("/protected", "GET")]
public function protected(#[Header("Authorization")] $auth) {
    if (!$auth) {
        throw new HTTPException("Missing Authorization header", 401);
    }
    return ["authenticated" => true];
}
```

### Using Middleware

```php
#[Controller("/admin")]
#[Middleware(AuthMiddleware::class)]
class AdminController {
    
    #[Route("/dashboard", "GET")]
    #[Middleware(AdminMiddleware::class)]
    public function dashboard() {
        return ["content" => "Admin dashboard"];
    }
}
```

## 🧪 Testing

The project includes a `test.php` file demonstrating:
- Controller registration
- Route matching
- Middleware handling
- Service injection

Run the test:
```bash
php test.php
```

## 📝 License

This is a school project. Feel free to use and modify for educational purposes.

## 👨‍💻 Author

Created from scratch with zero AI assistance as a learning exercise in routing, dependency injection, and framework architecture.
