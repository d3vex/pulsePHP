<?php

namespace D3vex\Pulsephp\Core\Routing\Exceptions;

class RouteRegisterParameterPathWithNoEndException extends \Exception
{
    public function __construct(string $path, string $method)
    {
        parent::__construct("Invalid route definition for path '$path' with method '$method'. The parameter is not closed with a '}'.");
    }
}
