<?php
namespace D3vex\Pulsephp\Core\Http;

use D3vex\Pulsephp\Core\Logger\Logger;
use D3vex\Pulsephp\Core\Container\IOCContainer;
use D3vex\Pulsephp\Core\Routing\Dispatcher;
use D3vex\Pulsephp\Core\Routing\RouteDefinition;
use D3vex\Pulsephp\Core\Routing\RouteOptionsDefinition;
use D3vex\Pulsephp\Core\Routing\Router;
use D3vex\Pulsephp\Core\Config\AppConfig;

class Kernel {

    private Logger $logger;

    private Router $router;
    private IOCContainer $container;
    private AppConfig $config;
    private Dispatcher $dispatcher;

    private array $globalMiddleware = [];

    public function __construct(Router $router, Dispatcher $dispatcher, IOCContainer $container) {
        $this->logger = new Logger(Kernel::class);
        $this->router = $router;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
        $this->config = $container->get(AppConfig::class);
    }

    public function setGlobalMiddleware(array $globalMiddleware): Kernel {
        $this->globalMiddleware = $globalMiddleware;
        return $this;
    }

    public function addGlobalMiddleware(string $middleware): Kernel {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }
    public function getGlobalMiddleware(): array {
        return $this->globalMiddleware;
    }
    public function removeGlobalMiddleware(string $middleware): Kernel {
        $this->globalMiddleware = array_filter($this->globalMiddleware, function($m) use ($middleware) {
            return $m !== $middleware;
        });
        return $this;
    }


    public function handle(RequestModel $request): ResponseModel {
        $response = new ResponseModel();

        $method = $request->getRequestMethod();
        $isHead = $method === "HEAD";
        $isOptions = $method === "OPTIONS";
        if($isHead) $method = "GET";

        $route = $this->router->matchRoute($request->getRequestUri(), $method);
        if ($route === null) {
            $response->setStatusCode(404);
            return $response;
        }
        $response->setHeaders(headers: $this->config->getDefaultHeaders());
        $this->handleCors($route, $request, $response);
        if($isOptions) {
            $response->setStatusCode(204);
            return $response;
        }
        try {
            $this->dispatcher->dispatch($route, $request, $response);
        }catch (HTTPExceptions $e) {
            $response->setStatusCode($e->getHttpCode());
            $response->setBody($e->__toString());
        }
        if ($isHead) $response->setBody("");
        if($isHead && $response->getStatusCode() >= 299) $response->setStatusCode(204);

        return $response;
    }

    private function handleCors(RouteDefinition $route, RequestModel $request, ResponseModel $response)
    {
        $config = $this->container->get(AppConfig::class);
        foreach ($config->getCorsAsHeaders() as $key => $value) {
            if (is_array(value: $value)) {
                $value = implode(",", $value);
            }
            $response->setHeader($key, $value);
        }
        if($route instanceof RouteOptionsDefinition) {
            $response->setHeader("Allow", implode(",", $route->allowMethods));
        }else {
        $response->setHeader("Allow", implode(",", [$route->method]));
        }
    }

}