<?php

namespace D3vex\Pulsephp\Core\Container\Exceptions;

class InvalidNoConstructorException extends \Exception
{
    public function __construct(string $className)
    {
        parent::__construct("Class '$className' does not have a constructor.");
    }
}
