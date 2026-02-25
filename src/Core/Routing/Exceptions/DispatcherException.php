<?php

namespace D3vex\Pulsephp\Core\Routing\Exceptions;
use D3vex\Pulsephp\Core\Http\HTTPExceptions;

class DispatcherException extends HTTPExceptions {
    public function __construct($path, $method, $code = HTTPCodes::HTTP_INTERNAL_SERVER_ERROR) {
        parent::__construct("Errors occurs while dispatching request: '$method' '$path'.", $code);
    }
}
