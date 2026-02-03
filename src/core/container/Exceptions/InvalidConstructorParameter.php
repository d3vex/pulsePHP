<?php


class InvalidConstructorParameterException extends Exception {
    public function __construct($parameterName, $className) {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'.");
    }
}

class InvalidNoConstructorException extends Exception {
    public function __construct($className) {
        parent::__construct("Class '$className' does not have a constructor.");
    }
}

class InvalidOptionalConstructorParameterException extends Exception {
    public function __construct($parameterName, $className) {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'. Optional parameters are not allowed.");
    }
}

class InvalidAllowNullConstructorParameterException extends Exception {
    public function __construct($parameterName, $className) {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'. Null parameters are not allowed. Remove the '?' before parameter type");
    }
}
class InvalidBuiltinTypeConstructorParameterException extends Exception {
    public function __construct($parameterName, $parameterType, $className) {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'. Builtin types are not allowed (type: $parameterType).");
    }
}
class InvalidClassTypeConstructorParameterException extends Exception {
    public function __construct($parameterName, $parameterType, $className) {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'. Class type '$parameterType' does not exist.");
    }
}

class InvalidAutoLoopConstructorParameterException extends Exception {
    public function __construct($parameterName, $className) {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'. Constructor cannot be type of class itself.");
    }
}

class InvalidLoopConstructorParameterException extends Exception {
    public function __construct($parameterName, $className) {
        parent::__construct("Invalid constructor parameter '$parameterName' in class '$className'. Loop dependency detected. Currently no way to resolve this using a kind of forwardRef");
    }
}