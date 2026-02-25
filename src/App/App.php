<?php
namespace D3vex\Pulsephp\App;

use D3vex\Pulsephp\Core\Bootstrap\ContainerFactory;
use D3vex\Pulsephp\Core\Container\IOCContainer;
use D3vex\Pulsephp\Core\Http\Kernel;
use D3vex\Pulsephp\Core\Http\RequestModel;
use D3vex\Pulsephp\Core\Routing\Router;
use D3vex\Pulsephp\Core\Config\AppConfig;

class App
{

    protected IOCContainer $container;
    private Router $router;
    private Kernel $kernel;
    public AppConfig $config;

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


    public function registerUserScripts(): void
    {
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
        $this->container->registerShared(AppConfig::class);
        $this->container->registerShared(App::class, function () {
            return $this;
        });

        $this->kernel = $this->container->get(Kernel::class);
        $this->router = $this->container->get(Router::class);
        $this->config = $this->container->get(AppConfig::class);


    }

    public function run()
    {
        $request = RequestModel::fromGlobals();
        $response = $this->kernel->handle($request);
        $response->send();
    }
}




?>