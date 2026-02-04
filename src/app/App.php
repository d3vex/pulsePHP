<?php

require_once __DIR__ . "/../core/bootstrap/Bootstrap.php";

class App
{

    protected IOCContainer $container;
    private Router $router;
    private Kernel $kernel;

    public static function start()
    {
        $app = new App();
        $app->boostrap();
        $app->registerUserScripts();
        return $app;
    }

    public function registerUserService(): void
    {
        // Define here all the service you have to define
        // Exemple: $this->registerService(User::class, new UserService());
    }
    public function registerUserSharedService(): void
    {
        // Define here all the service you have to define
        // Exemple: $this->registerSharedService(Configuration::class);
    }
    public function registerUserController(): void
    {
        // Define here all the service you have to define
        // Exemple: $this->registerController(BasicController::class);
    }
    

    public function getRouter(): Router
    {
        return $this->router;
    }


    public function registerUserScripts(): void {
        $this->registerUserController();
        $this->registerUserSharedService();
        $this->registerUserService();
    }

    public function registerController(string $controllerClass): void
    {
        $this->registerService($controllerClass);
        $this->router->registerController($controllerClass);

    }
    public function registerSharedService(string $serviceClass, ?Closure $constructor = null): void
    {
        $this->container->registerShared($serviceClass, $constructor);
    }
    public function registerService(string $serviceClass, ?Closure $constructor = null): void
    {
        $this->container->registerDedicated($serviceClass, $constructor);
    }

    public function boostrap()
    {
        $this->container = ContainerFactory::buildContainer();
        $this->kernel = $this->container->get(Kernel::class);
        $this->router = $this->container->get(Router::class);

    }

    public function run()
    {
        $request = RequestModel::fromGlobals();
        $response = $this->kernel->handle($request);
        $response->send();
    }
}




?>