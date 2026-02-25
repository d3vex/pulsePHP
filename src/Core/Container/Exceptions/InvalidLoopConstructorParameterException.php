<?php

namespace D3vex\Pulsephp\Core\Container\Exceptions;

class InvalidLoopConstructorParameterException extends \Exception
{
    public function __construct(string $parameterName, string $className)
    {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'. Loop dependency detected. Currently no way to resolve this using a kind of forwardRef");
    }
}
