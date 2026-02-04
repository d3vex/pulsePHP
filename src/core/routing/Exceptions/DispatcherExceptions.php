<?php


class DispatcherException extends HTTPExceptions {
    public function __construct($path, $method, $code = HTTPCodes::HTTP_INTERNAL_SERVER_ERROR) {
        parent::__construct("Errors occurs while dispatching request: '$method' '$path'.", $code);
    }
}


class InvalidHandlerDispatcherException extends HTTPExceptions {
    public function __construct($path, $method, $code = HTTPCodes::HTTP_INTERNAL_SERVER_ERROR) {
        parent::__construct("The handler for endpoint '$method' '$path' should be in format: [Class name, Method name].", $code);
    }
}
