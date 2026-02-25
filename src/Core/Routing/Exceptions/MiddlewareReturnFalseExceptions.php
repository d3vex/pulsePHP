<?php

namespace D3vex\Pulsephp\Core\Routing\Exceptions;

use D3vex\Pulsephp\Core\Http\HTTPExceptions;
use D3vex\Pulsephp\Core\Http\HTTPCodes;

class MiddlewareReturnFalseExceptions extends HTTPExceptions
{
    public function __construct(string $className, int $code = HTTPCodes::HTTP_INTERNAL_SERVER_ERROR)
    {
        parent::__construct("Exceptions occurs with middleware '$className'. Return false.", $code);
    }
}
