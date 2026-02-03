<?php

require_once "src/core/bootstrap/bootstrap.php";

class App {

    private IOCContainer $container;
    private Router $router;
    private Kernel $kernel;

    
    public function __construct() {
    }

    public static function start() {
        $app = new App();
        
        $app->container = ContainerFactory::buildContainer();

        $app->router = $app->container->get(Router::class);

        $app->kernel = $app->container->get(Kernel::class);

        return $app;
    }

    public function getRouter(): Router {
        return $this->router;
    }

    public function registerController(string $controllerClass): void {
        $this->registerService($controllerClass);
        $this->router->registerController($controllerClass);

    }
    public function registerSharedService(string $serviceClass): void {
        $this->container->registerShared($serviceClass);
    }
    public function registerService(string $serviceClass): void {
        $this->container->registerDedicated($serviceClass);
    }

}




?>