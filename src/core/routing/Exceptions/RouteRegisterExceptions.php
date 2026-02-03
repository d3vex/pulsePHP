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
