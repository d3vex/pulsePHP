<?php

namespace D3vex\Pulsephp\Core\Routing\Middleware;

use D3vex\Pulsephp\Core\Http\RequestModel;

interface MiddlewareInterface
{
    public function handle(RequestModel $request);
}
