<?php


class Kernel {

    private Logger $logger;

    private Router $router;
    private Dispatcher $dispatcher;
    public function __construct(Router $router, Dispatcher $dispatcher) {
        $this->logger = new Logger(Kernel::class);
        $this->router = $router;
        $this->dispatcher = $dispatcher;
    }


    public function handle(RequestModel $request): ResponseModel {
        $response = new ResponseModel();
        $this->logger->info("Receiving new request with path: " . $request->getRequestUri());
        $route = $this->router->matchRoute($request->getRequestUri(), $request->getRequestMethod());
        if ($route === null) {
            $response->setStatusCode(404);
            return $response;
        }
        $response->setHeaders($this->router->getDefaultHeaders());

        try {
            $this->dispatcher->dispatch($route, $request, $response);
        }catch (HTTPExceptions $e) {
            $response->setStatusCode($e->getHttpCode());
            $response->setBody($e->__toString());
        }

        return $response;
    }

}