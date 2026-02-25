<?php


namespace D3vex\Pulsephp\Core\Routing;

use D3vex\Pulsephp\Core\Attributes\Body;
use D3vex\Pulsephp\Core\Attributes\Header;
use D3vex\Pulsephp\Core\Attributes\Params;
use D3vex\Pulsephp\Core\Attributes\Query;
use D3vex\Pulsephp\Core\Attributes\Request;
use D3vex\Pulsephp\Core\Container\IOCContainer;
use D3vex\Pulsephp\Core\Http\RequestModel;
use D3vex\Pulsephp\Core\Http\ResponseModel;
use D3vex\Pulsephp\Core\Config\AppConfig;
use D3vex\Pulsephp\Core\Logger\Logger;

use D3vex\Pulsephp\Core\Routing\Exceptions;

class Dispatcher
{

    private Logger $logger;
    private IOCContainer $container;

    public function __construct(IOCContainer $container)
    {
        $this->logger = new Logger(__CLASS__);
        $this->container = $container;
    }

    public function dispatch(RouteDefinition $route, RequestModel $request, ResponseModel $response)
    {
        if (!$route) {
            $this->logger->error("No route definition provided for dispatching.");
            throw new \InvalidArgumentException("Route definition cannot be null.");
        }
        
        $this->ensureHandlerIsValid($route);
        $callableHandler = $this->getCallableHandler($route);

        $this->dispatchMiddleware($route, $request);
        $requestParams = $this->parseParamsFromUrl($request->getRequestUri(), $route->compiledPath, $route->parameters);
        $methodParams = $this->resolveControllerPrams($route, $request, $requestParams);
        $responseValue = call_user_func($callableHandler, ...$methodParams);
        $response->setBody($responseValue);
    }

    public function dispatchOptionsRequest(RouteOptionsDefinition $route, RequestModel $request, ResponseModel $response)
    {
        $config = $this->container->get(AppConfig::class);
        foreach ($config->getCorsAsHeaders() as $key => $value) {
            if (is_array(value: $value)) {
                $value = implode(",", $value);
            }
            $response->setHeader($key, $value);
        }
        $response->setHeader("Allow", implode(",", $route->allowMethods));
        $response->setStatusCode(204);
    }

    public function dispatchMiddleware(RouteDefinition $route, RequestModel $request)
    {
        if (!$route) {
            $this->logger->error("No route definition provided for dispatching middleware.");
            throw new \InvalidArgumentException("No route definition provided for dispatching middleware.");
        }
        foreach ($route->middleware as $middleware) {

            if(!$this->container->has($middleware)) {
                $this->logger->error("Middleware not registered into container: " . $middleware);
            }
            if (!is_string($middleware) || !class_exists($middleware)) {
                throw new Exceptions\InvalidMiddlewareExceptions($middleware);
            }
            $middleware = $this->container->get($middleware);
            $result = $middleware->handle($request);
            if ($result == false)
                throw new Exceptions\MiddlewareReturnFalseExceptions($middleware::class);
        }
    }

    private function parseParamsFromUrl(string $url, string $regex, array $params)
    {
        $matches = [];
        preg_match($regex, $url, $matches);
        $extractedParams = [];
        for($i = 0; $i < count($params); $i++) {
            $extractedParams[$params[$i]] = $matches[$i + 1];
        }
        return $extractedParams;
    }

    private function resolveControllerPrams(RouteDefinition $route, RequestModel $request, $requestParams)
    {
        $result = [];
        $handler = $route->handler;
        $method = new \ReflectionMethod($handler[0], $handler[1]);
        $params = $method->getParameters();
        foreach ($params as $param) {
            if ($param->getType() == RequestModel::class) {
                $result[] = $request;
                continue;
            }
            ;
            $attrs = $param->getAttributes();
            if (count($attrs) > 0) {
                $attr = $attrs[0];
                $attrName = $attr->getName();
                if ($attrName == Request::class) {
                    $result[] = $request;
                    continue;
                }
                if ($attrName == Query::class) {
                    $queryName = $attr->getArguments()[0];
                    $result[] = $request->getQuery($queryName);
                }
                if ($attrName == Header::class) {
                    $headerName = $attr->getArguments()[0];
                    $result[] = $request->getHeader($headerName);
                }
                if ($attrName == Body::class) {
                    $queryName = $attr->getArguments()[0];
                    $result[] = $request->getBody();
                }
                if ($attrName == Params::class) {
                    $paramName = $attr->getArguments()[0];
                    $result[] = $requestParams[$paramName] ?? null;
                }
            }
        }
        return $result;
    }

    private function getCallableHandler(RouteDefinition $route): array
    {
        return [$this->container->get($route->handler[0]), $route->handler[1]];
    }
    private function ensureHandlerIsValid(RouteDefinition $route)
    {
        if (!is_array($route->handler))
            throw new Exceptions\InvalidHandlerDispatcherException($route->originalPath, $route->method);
        if (count($route->handler) != 2)
            throw new Exceptions\InvalidHandlerDispatcherException($route->originalPath, $route->method);
        if (!is_string($route->handler[0]) || !class_exists($route->handler[0]))
            throw new Exceptions\InvalidHandlerDispatcherException($route->originalPath, $route->method);
    }



}