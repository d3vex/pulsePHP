<?php
namespace D3vex\Pulsephp\Core\Bootstrap;

use D3vex\Pulsephp\Core\Container\IOCContainer;
use D3vex\Pulsephp\Core\Http\Kernel;
use D3vex\Pulsephp\Core\Routing\Dispatcher;
use D3vex\Pulsephp\Core\Routing\Router;

class ContainerFactory {
    public static function buildContainer(): IOCContainer {
        $IOC = new IOCContainer();
        $IOC->registerShared(IOCContainer::class, function() use ($IOC) {
            return $IOC;
        });
        $IOC->registerShared(Router::class);
        $IOC->registerShared(class: Kernel::class);
        $IOC->registerShared(Dispatcher::class);
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