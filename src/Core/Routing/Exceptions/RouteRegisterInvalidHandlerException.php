<?php

namespace D3vex\Pulsephp\Core\Routing\Exceptions;

class RouteRegisterInvalidHandlerException extends \Exception
{
    public function __construct(string $path, string $method)
    {
        parent::__construct("Invalid route handler definition for path '$path' with method '$method'. Handler should be an array with [Class name, Method name].");
    }
}
