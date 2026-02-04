<?php


class MiddlewareExceptions extends HTTPExceptions {
    public function __construct($className, $code = HTTPCodes::HTTP_INTERNAL_SERVER_ERROR) {
        parent::__construct("Exceptions occurs with middleware '$className'.", $code);
    }
}


class MiddlewareReturnFalseExceptions extends HTTPExceptions {
    public function __construct($className, $code = HTTPCodes::HTTP_INTERNAL_SERVER_ERROR) {
        parent::__construct("Exceptions occurs with middleware '$className'. Return false.", $code);
    }
}



class InvalidMiddlewareExceptions extends HTTPExceptions {
    public function __construct($className, $code = HTTPCodes::HTTP_INTERNAL_SERVER_ERROR) {
        parent::__construct("Exceptions occurs with middleware '$className'. Middleware is not instance of MiddlewareInterface.", $code);
    }
}