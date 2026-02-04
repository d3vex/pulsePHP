<?php


class RouteRegisterException extends Exception {
    public function __construct($path, $method) {
        parent::__construct("Invalid route definition for path '$path' with method '$method'.");
    }
}

class RouteRegisterParameterPathWithNoEndException extends Exception {
    public function __construct($path, $method) {
        parent::__construct("Invalid route definition for path '$path' with method '$method'. The parameter is not closed with a '}'.");
    }

}
class RouteRegisterInvalidMiddlewareException extends Exception {
    public function __construct($path, $method) {
        parent::__construct("Invalid route middleware definition for path '$path' with method '$method'. Middleware should be class name.");
    }
}

class RouteRegisterInvalidHandlerException extends Exception {
    public function __construct($path, $method) {
        parent::__construct("Invalid route handler definition for path '$path' with method '$method'. Handler should be an array with [Class name, Method name].");
    }
}
