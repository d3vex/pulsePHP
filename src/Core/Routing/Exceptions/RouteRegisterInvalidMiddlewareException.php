<?php

namespace D3vex\Pulsephp\Core\Routing\Exceptions;

class RouteRegisterInvalidMiddlewareException extends \Exception
{
    public function __construct(string $path, string $method)
    {
        parent::__construct("Invalid route middleware definition for path '$path' with method '$method'. Middleware should be class name.");
    }
}
