<?php


class Dispatcher {

    private Logger $logger;

    public function __construct() {
        $this->logger = new Logger(__CLASS__ );
    }

    public function dispatch( RouteDefinition $route, RequestModel $request, ResponseModel $response) {
    }


}