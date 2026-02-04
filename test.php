<?php
require_once 'src/core/container/index.php';
require_once 'src/core/routing/index.php';


class TestMiddleware implements MiddlewareInterface {
    public function handle($request): void {
        echo "TestMiddleware executed\n";
    }

}



class TestMiddleware2 implements MiddlewareInterface {
    public function handle($request): void {
        echo "TestMiddleware executed\n";
    }
}

class TestMiddleware3 implements MiddlewareInterface {
    public function handle($request): void {
        echo json_encode($request);
        echo "TestMiddleware executed\n";
    }
}
#[Controller('/info')]
#[Middleware(TestMiddleware::class)]
class TestController {
    public function __construct() {}

    #[Middleware([TestMiddleware2::class, TestMiddleware3::class])]
    #[Route('/health', 'GET')]
    public function health(): int {
        return 42;
    }

    #[Middleware([TestMiddleware3::class])]
    #[Route('user/{id}/get/{info}/limit', 'GET')]
    public function get(): int {
        return 42;
    }

}


$ioc = new IOCContainer();

$ioc->registerShared(Router::class);

$router = $ioc->get(Router::class);

$router->setBaseUrl('/api');

$router->registerController(TestController::class);


$ioc->registerShared(IOCContainer::class, function() use($ioc) { return $ioc; });

$router->get("/test", null, TestMiddleware2::class, TestMiddleware3::class, [TestController::class, 'health']);

