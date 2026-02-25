<?php

namespace D3vex\Pulsephp\Core\Container\Exceptions;

class InvalidConstructorParameterException extends \Exception
{
    public function __construct(string $parameterName, string $className)
    {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'.");
    }
}
