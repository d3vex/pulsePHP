# D3vex\Pulsephp\Core Documentation Index

Welcome to the D3vex\Pulsephp\Core documentation! This is a comprehensive guide to using the D3vex\Pulsephp\Core framework.

## 📖 Documentation Structure

### [README.md](../README.md)
**Start here!** Project overview, features, known limitations, and quick start guide.
- Project context and goals
- Feature list
- Known limitations (cookies, file uploads, etc.)
- Installation instructions
- Basic configuration examples

### [1. Architecture Overview](./1-architecture.md)
Learn how D3vex\Pulsephp\Core works under the hood.
- Framework structure and components
- Request lifecycle flow
- Attribute-based architecture
- Middleware chain execution
- Service registration
- Error handling

### [2. Routing Guide](./2-routing.md)
Define and manage your application routes.
- Route basics with `#[Route]` attribute
- HTTP methods (GET, POST, PUT, DELETE, PATCH)
- Path parameters extraction
- Query parameters handling
- HTTP headers access
- Request body parsing
- Parameter injection
- Complete routing examples
- Route matching and base URL configuration

### [3. Dependency Injection Container](./3-ioc-container.md)
Master the IoC container for dependency management.
- Shared services (singletons)
- Dedicated services (transient)
- Service registration
- Custom factory functions
- Constructor dependency injection
- Multi-level dependency resolution
- Circular dependency detection
- Service lifecycle management

### [4. Middleware System](./4-middleware.md)
Build powerful request processing middleware.
- Middleware interface and implementation
- Authentication middleware examples
- Logging and rate limiting
- CORS and validation middleware
- Registering middleware at class and method levels
- Multiple middleware chains
- Middleware execution order
- Error handling and exceptions

### [5. Examples & API Reference](./5-examples.md)
Practical code examples and complete API reference.
- Quick start examples
- REST API implementation
- Service architecture patterns
- Authentication flows
- Complete API reference for all classes
- Common patterns and best practices
- Testing examples with cURL and Postman
- Performance tips

## 🚀 Getting Started

### 1. First Time? Start Here
```
README.md → 1-architecture.md → 2-routing.md
```

### 2. Building Your First API
```
2-routing.md → 3-ioc-container.md → 5-examples.md (Example 2: REST API)
```

### 3. Adding Security
```
4-middleware.md → 5-examples.md (Example 4: Authentication)
```

### 4. Reference
```
5-examples.md (API Reference) when you need specific method signatures
```

## 📚 Reading Guide

### By Experience Level

**Beginner**
1. README.md - Understand what D3vex\Pulsephp\Core is
2. Quick Start section in README
3. 2-routing.md - Define your first routes
4. 5-examples.md - Example 1 (Hello World)

**Intermediate**
1. 1-architecture.md - Understand the request flow
2. 3-ioc-container.md - Manage dependencies
3. 5-examples.md - Example 2 (REST API)
4. 4-middleware.md - Add middleware

**Advanced**
1. Complete 1-architecture.md for deep understanding
2. 3-ioc-container.md - Advanced patterns
3. 4-middleware.md - Complex middleware chains
4. 5-examples.md - All examples and patterns

### By Task

**I want to...**

- **Create a route** → See [2-routing.md](./2-routing.md)
- **Set up authentication** → See [4-middleware.md](./4-middleware.md) + [5-examples.md](./5-examples.md) Example 4
- **Manage services** → See [3-ioc-container.md](./3-ioc-container.md)
- **Build a REST API** → See [5-examples.md](./5-examples.md) Example 2
- **Understand the framework** → See [1-architecture.md](./1-architecture.md)
- **Handle requests** → See [2-routing.md](./2-routing.md) - Parameter Injection section
- **Process requests** → See [4-middleware.md](./4-middleware.md)
- **Register services** → See [3-ioc-container.md](./3-ioc-container.md) - Service Registration
- **See code examples** → See [5-examples.md](./5-examples.md)
- **Find API reference** → See [5-examples.md](./5-examples.md) - API Reference section

## 🎯 Quick Reference

### Attributes

```php
#[Controller("/path")]              // Define controller with base path
#[Route("/path", "METHOD")]         // Define route with HTTP method
#[Middleware(MiddlewareClass::class)]  // Apply middleware
#[Params("name")]                   // Extract path parameter
#[Query("name")]                    // Extract query parameter
#[Header("name")]                   // Extract header value
#[Body()]                          // Access request body
```

### Common Code Patterns

```php
// Define a controller
#[Controller("/api/users")]
class UserController {
    public function __construct(private UserService $service) {}
    
    #[Route("", "GET")]
    public function list() { }
}

// Register services
$app->registerSharedService(DatabaseConnection::class);
$app->registerService(RequestHandler::class);
$app->registerController(UserController::class);

// Create middleware
class AuthMiddleware implements MiddlewareInterface {
    public function handle(RequestModel $request) {
        return !empty($request->getHeader("Authorization"));
    }
}

// Inject dependencies
class MyService {
    public function __construct(
        private Logger $logger,
        private Database $db
    ) {}
}
```

### HTTP Methods

- `GET` - Retrieve data
- `POST` - Create data
- `PUT` - Replace data
- `PATCH` - Partial update
- `DELETE` - Remove data
- `OPTIONS` - Get allowed methods

## 🔍 Finding Information

### By Keyword

- **Routes** → [2-routing.md](./2-routing.md)
- **Controllers** → [2-routing.md](./2-routing.md)
- **Dependency Injection** → [3-ioc-container.md](./3-ioc-container.md)
- **IoC Container** → [3-ioc-container.md](./3-ioc-container.md)
- **Middleware** → [4-middleware.md](./4-middleware.md)
- **Authentication** → [4-middleware.md](./4-middleware.md) + [5-examples.md](./5-examples.md)
- **Request Handling** → [2-routing.md](./2-routing.md)
- **Response** → [1-architecture.md](./1-architecture.md)
- **Routing** → [2-routing.md](./2-routing.md)
- **Services** → [3-ioc-container.md](./3-ioc-container.md)
- **Configuration** → [README.md](../README.md)
- **Examples** → [5-examples.md](./5-examples.md)

## 📋 Key Concepts Explained

### Routing
Routes map HTTP requests to controller methods using attributes. Each route has:
- **Path** - URL pattern with optional parameters
- **Method** - HTTP verb (GET, POST, etc.)
- **Handler** - Controller method to execute
- **Middleware** - Optional request processing

### Dependency Injection
Automatically inject dependencies into classes through constructors. The container:
- Resolves dependencies automatically
- Detects circular references
- Supports shared (singleton) and dedicated (transient) services

### Middleware
Processing layer that runs before route handlers. Can:
- Validate requests
- Authenticate users
- Log activities
- Rate limit

### Controllers
Classes that handle requests. Use:
- `#[Controller]` attribute to define base path
- `#[Route]` attribute to define routes
- Constructor injection for dependencies

## 🆘 Troubleshooting

### Common Issues

**404 Not Found**
- Check route path matches URL
- Verify HTTP method is correct
- Ensure controller is registered

**Middleware not executing**
- Verify middleware is registered as service
- Check middleware is applied to route
- Ensure middleware returns true to continue

**Service not found**
- Check service is registered
- Verify class name in registration matches usage
- Ensure all dependencies are registered

**Parameter not injecting**
- Check parameter has correct attribute
- Verify attribute name matches parameter name
- Ensure parameter type is correct

## 📞 Support

For issues or questions:
1. Check the relevant documentation file
2. See examples in [5-examples.md](./5-examples.md)
3. Review the test.php for working examples

## 📝 Project Files

```
/
├── README.md                    # Main documentation entry point
├── docs/
│   ├── index.md                # This file
│   ├── 1-architecture.md       # Framework architecture
│   ├── 2-routing.md            # Routing guide
│   ├── 3-ioc-container.md      # Dependency injection
│   ├── 4-middleware.md         # Middleware system
│   └── 5-examples.md           # Examples and API reference
├── public/
│   └── index.php               # Application entry point
├── src/
│   ├── app/
│   │   └── App.php             # Main application class
│   └── core/
│       ├── attributes/         # PHP attributes
│       ├── bootstrap/          # Bootstrap configuration
│       ├── container/          # Dependency injection
│       ├── http/               # HTTP request/response
│       ├── logger/             # Logging
│       └── routing/            # Routing system
└── test.php                    # Test file with examples
```

## 🎓 Learning Path

**Week 1: Basics**
- [ ] Read README.md
- [ ] Read 1-architecture.md
- [ ] Create your first controller (2-routing.md)
- [ ] Run Example 1 (5-examples.md)

**Week 2: Routing & Requests**
- [ ] Complete 2-routing.md
- [ ] Understand parameter injection
- [ ] Build simple CRUD routes
- [ ] Run Example 2 (5-examples.md)

**Week 3: Dependency Injection**
- [ ] Complete 3-ioc-container.md
- [ ] Create service classes
- [ ] Register dependencies
- [ ] Understand shared vs dedicated

**Week 4: Middleware & Security**
- [ ] Complete 4-middleware.md
- [ ] Create middleware classes
- [ ] Implement authentication
- [ ] Run Example 4 (5-examples.md)

**Week 5: Advanced Topics**
- [ ] Review all documentation
- [ ] Study code patterns in 5-examples.md
- [ ] Build complete REST API
- [ ] Deploy your application

---

**Happy coding with D3vex\Pulsephp\Core!** 🚀
