<?php

require_once "src/core/container/index.php";
require_once "src/core/routing/index.php";


class ContainerFactory {
    public static function buildContainer(): IOCContainer {
        $IOC = new IOCContainer();
        $IOC->registerShared(Router::class);
        return $IOC;
    }
}

/**
 * DON'T EDIT THIS FILE
 * 
 * You should define IN YOUR CODE all your bindings and singletons for the application
 * 
 * Use $IOC->registerDedicated(ClassName::class, function() { return new ClassName(); }) to register a binding
 * Use $IOC->registerShared(ClassName::class, function() { return new ClassName(); }) to register a singleton
 * 
 * The second attributes is optional, if not provided the container will try to instantiate the class automatically
 * No built-in type is supported for now (string, int, array, etc.)
 */