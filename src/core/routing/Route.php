<?php
class RouteDefinition {
    public string $name;
    public string $originalPath;
    public string $compiledPath;
    public array $parameters = [];
    public string $method;
    public array $handler;
    /**
     * Middleware store array of middleware classes
     * @var array<string>
     */
    public array $middleware = [];
}