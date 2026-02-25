<?php

namespace D3vex\Pulsephp\Core\Container\Exceptions;

class InvalidAllowNullConstructorParameterException extends \Exception
{
    public function __construct(string $parameterName, string $className)
    {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'. Null parameters are not allowed. Remove the '?' before parameter type");
    }
}
