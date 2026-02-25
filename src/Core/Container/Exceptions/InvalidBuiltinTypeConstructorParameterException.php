<?php

namespace D3vex\Pulsephp\Core\Container\Exceptions;

class InvalidBuiltinTypeConstructorParameterException extends \Exception
{
    public function __construct(string $parameterName, string $parameterType, string $className)
    {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'. Builtin types are not allowed (type: $parameterType).");
    }
}
