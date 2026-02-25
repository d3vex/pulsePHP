<?php
namespace D3vex\Pulsephp\Core\Routing\Exceptions;
use D3vex\Pulsephp\Core\Http\HTTPExceptions;


class InvalidHandlerDispatcherException extends HTTPExceptions {
    public function __construct($path, $method, $code = HTTPCodes::HTTP_INTERNAL_SERVER_ERROR) {
        parent::__construct("The handler for endpoint '$method' '$path' should be in format: [Class name, Method name].", $code);
    }
}
