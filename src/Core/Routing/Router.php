<?php

namespace D3vex\Pulsephp\Core\Routing;

use D3vex\Pulsephp\Core\Logger\Logger;

use D3vex\Pulsephp\Core\Attributes\Controller;
use D3vex\Pulsephp\Core\Attributes\Middleware;
use D3vex\Pulsephp\Core\Attributes\Route;

use D3vex\Pulsephp\Core\Routing\Exceptions;

class Router
{

    private array $routes = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "DELETE" => [],
        "PATCH" => [],
        "OPTIONS" => []
    ];

    private string $baseUrl = "/api";
    private Logger $logger;
    public function __construct()
    {
        $this->logger = new Logger(__CLASS__);
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $this->sanitizePath($baseUrl);
        if (str_ends_with($this->baseUrl, "/")) {
            $this->baseUrl = substr($this->baseUrl, 0, -1);
        }
    }

    public function matchRoute(string $path, string $method): RouteDefinition|null
    {
        if (!isset($this->routes[$method])) {
            return null;
        }
        $routes = $this->routes[$method];
        if (!is_array($routes)) {
            return null;
        }
        foreach ($routes as $route) {
            if ($this->match($path, $method, $route)) {
                return $route;
            }
        }
        return null;
    }

    public function registerController(string $controller): void
    {
        $class = new \ReflectionClass($controller);
        $baseUrl = $this->baseUrl;
        $baseMiddleware = [];
        foreach ($class->getAttributes() as $attribute) {
            if ($attribute->getName() == Controller::class) {
                $baseUrl .= $this->sanitizePath($attribute->getArguments()[0]);
            }
            $baseMiddleware = array_merge($baseMiddleware, $this->parseMiddlewareAttribute($attribute));
        }

        foreach ($class->getMethods() as $method) {
            $this->registerRouteFromController($method, $baseUrl, $controller, $baseMiddleware);
        }

    }

    /**
     * This method allow you to add a single route, wich the method you prefer
     * @param string $path
     * @param string $method
     * @param array $controllers
     * @return void
     */
    public function addRoute(string $path, string $method, ...$controllers): void
    {
        $controller = array_pop($controllers);
        $middlewares = $controllers; 
        if(is_array($controller) && count($controller) != 2) {
            throw new Exceptions\RouteRegisterInvalidHandlerException($path, $method);
        }
        if(is_array($controller) && !is_string($controller[0])) {
            throw new Exceptions\RouteRegisterInvalidHandlerException($path, $method);
        }
        $path = $this->baseUrl . $this->sanitizePath($path);
        $validMiddlewares = [];
        foreach($middlewares as $middleware) {
            if(is_string($middleware) && class_exists($middleware)) {
                $validMiddlewares[] = $middleware;
            } else {
                throw new Exceptions\RouteRegisterInvalidMiddlewareException($path, $method);
            }
        }
        $this->createRoute($path, $method, $controller, $validMiddlewares);
    }

    /**
     * This method wrap addRoute method, and specify a GET method
     * @param string $path
     * @param array $controllers
     * @return void
     */
    public function get(string $path, ...$controllers): void
    {
        $this->addRoute($path, "GET", ...$controllers);
    }

    /**
     * This method wrap addRoute method, and specify a POST method
     * @param string $path
     * @param array $controllers
     * @return void
     */
    public function post(string $path, ...$controllers): void
    {
        $this->addRoute($path, "POST", ...$controllers);
    }

    /**
     * This method wrap addRoute method, and specify a PUT method
     * @param string $path
     * @param array $controllers
     * @return void
     */
    public function put(string $path, ...$controllers): void
    {
        $this->addRoute($path, "PUT", ...$controllers);
    }

    /**
     * This method wrap addRoute method, and specify a PATCH method
     * @param string $path
     * @param array $controllers
     * @return void
     */
    public function patch(string $path, ...$controllers): void
    {
        $this->addRoute($path, "PATCH", ...$controllers);
    }

    /**
     * This method wrap addRoute method, and specify a DELETE method
     * @param string $path
     * @param array $controllers
     * @return void
     */
    public function delete(string $path, ...$controllers): void
    {
        $this->addRoute($path, "DELETE", ...$controllers);
    }

    /**
     * This method wrap addRoute method, and specify a OPTIONS method
     * @param string $path
     * @param array $controllers
     * @return void
     */
    public function options(string $path, ...$controllers): void
    {
        $this->addRoute($path, "OPTIONS", ...$controllers);
    }

    private function registerRouteFromController(\ReflectionMethod $method, string $baseUrl, string $controller, array $middleware): void
    {
        if ($method->isConstructor() || $method->isDestructor())
            return;
        if ($method->isStatic() || $method->isAbstract()) {
            $this->logger->warning("Skipping static or abstract method " . $method->getName() . " in controller " . $controller);
        }
        $isARoute = false;
        $httpMethod = "GET";
        foreach ($method->getAttributes() as $attribute) {
            if ($attribute->getName() == Route::class) {
                $isARoute = true;
                $baseUrl .= $this->sanitizePath($attribute->getArguments()[0]);
                $httpMethod = $attribute->getArguments()[1];
            }
            $middleware = array_merge($middleware, $this->parseMiddlewareAttribute($attribute));
        }
        if ($isARoute) {
            $this->createRoute($baseUrl, $httpMethod, [$controller, $method->getName()], $middleware);
        } else {
            $this->logger->warning("Method " . $method->getName() . " in controller " . $controller . " is not a route, skipping.");
        }
    }

    private function parseMiddlewareAttribute(\ReflectionAttribute $attribute): array
    {
        if ($attribute->getName() != Middleware::class) {
            return [];
        }
        $middleware = [];
        $args = $attribute->getArguments()[0];
        if (is_array($args)) {
            foreach ($args as $mw) {
                $middleware[] = $mw;
            }
        } else {
            $middleware[] = $args;
        }
        return $middleware;
    }

    private function sanitizePath(string $path): string
    {
        if (!str_starts_with($path, "/")) {
            $path = "/" . $path;
        }
        return $path;
    }

    private function createRoute(string $path, string $method, array $controller, array $middleware)
    {
        $route = new RouteDefinition();
        $route->originalPath = $path;
        $route->method = $method;
        $route->name = $path;
        $parsedPath = $this->parseRoutePathToRegex($path, $method);
        $route->compiledPath = $parsedPath["regex"];
        $route->parameters = $parsedPath["params"];
        $route->middleware = $middleware;
        $route->handler = $controller;
        $this->routes[$method][] = $route;
        $this->handleOptionsRoute($route);

        return $route;
    }

    private function handleOptionsRoute(RouteDefinition $route): void
    {
        $existingRoute = $this->routes["OPTIONS"][$route->name] ?? null;
        if(isset($existingRoute)) {
            if($existingRoute->definedByUser) return;
            $existingRoute->allowMethod[] = $route->method;
            return;
        }
        $optionsRoute = new RouteOptionsDefinition();
        $optionsRoute->originalPath = $route->originalPath;
        $optionsRoute->compiledPath = $route->compiledPath;
        $optionsRoute->parameters = $route->parameters;
        $optionsRoute->middleware = $route->middleware;
        $optionsRoute->method = "OPTIONS";
        $optionsRoute->name = $route->name . "_options";
        $optionsRoute->allowMethods = [$route->method];
        $optionsRoute->handler = [];
        $this->routes["OPTIONS"][] = $optionsRoute;
        return;
    }

    private function parseRoutePathToRegex(string $path, string $method): array
    {
        $regex = "";
        $params = [];
        $lastIndex = 0;
        while (str_contains($path, "{")) {
            $index = strpos($path, "{");
            if ($index !== false) {
                $regex .= preg_quote(substr($path, 0, $index), '/');
                $endIndex = strpos($path, "}", $index);
                if ($endIndex !== false) {
                    $params[] = substr($path, $index + 1, $endIndex - $index - 1);
                    $regex .= '([^\/]*)';
                    $path = substr($path, $endIndex + 1);
                } else {
                    throw new Exceptions\RouteRegisterParameterPathWithNoEndException($path, $method);
                }
            }
        }
        $regex .= preg_quote($path, '/');
        $regex  = preg_replace('/\{[a-zA-Z0-9_]*\}/', '([^\/]*)', $regex);
        $regex = "/^" . $regex . "$/";
        return ['regex' => $regex, 'params' => $params];
    }


    private function match(string $path, string $method, RouteDefinition $route): bool
    {
        if ($route->method !== $method) {
            return false;
        }
        $pattern = $route->compiledPath;
        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches);
            preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $route->originalPath, $paramNames);
            $paramNames = $paramNames[1];
            $route->parameters = [];
            foreach ($paramNames as $index => $name) {
                $route->parameters[$name] = $matches[$index];
            }
            return true;
        }
        return false;
    }


    /**
     * This allow to print route and test the registration
     * @return void
     */
    public function logRoutes(): void
    {
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route) {
                $this->logger->info($this->routeToString($route));
            }
        }
    }

    private function routeToString(RouteDefinition $route): string
    {
        $str = "[$route->method]" . " " . $route->originalPath . ":\n";
        $str .= "  Controller: " . (is_array($route->handler) ? implode("::", $route->handler) : $route->handler) . "\n";
        $str .= "  Middleware: " . implode(", ", $route->middleware) . "\n";
        $str .= "  Parameters: " . implode(", ", $route->parameters) . "\n";
        $str .= "  Compiled Path: " . $route->compiledPath . "\n";
        return $str;
    }

}
