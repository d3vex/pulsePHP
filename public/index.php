<?php

require_once __DIR__ . "/../vendor/autoload.php";

use D3vex\Pulsephp\App\App;
use D3vex\Pulsephp\Core\Routing\Middleware\MiddlewareInterface;
use D3vex\Pulsephp\Core\Attributes\Controller;
use D3vex\Pulsephp\Core\Attributes\Middleware;
use D3vex\Pulsephp\Core\Attributes\Params;
use D3vex\Pulsephp\Core\Attributes\Query;
use D3vex\Pulsephp\Core\Attributes\Route;
use D3vex\Pulsephp\Core\Http\HTTPCodes;
use D3vex\Pulsephp\Core\Http\HTTPExceptions;
use D3vex\Pulsephp\Core\Http\RequestModel;

class BasicMiddleware implements MiddlewareInterface {
    public function __construct(){}
    public function handle(RequestModel $request): bool {
        if($request->getQuery("aa") == "2") throw new HTTPExceptions("BasicMiddleware refuse connection due to Query 'aa' being 2", HTTPCodes::HTTP_FORBIDDEN);
        return true;
    }
}
$app = App::start();
$app->getRouter()->setBaseUrl("");
$app->registerSharedService(BasicMiddleware::class);
$app->registerController(PublicController::class);
$app->run();



#[Controller("/api")]
class PublicController {

    public function __construct() {}

    #[Route("index.php/{id}/e", "GET")]
    #[Middleware(BasicMiddleware::class)]
    public function index(#[Params("id")] $id, #[Query("token")] $token, RequestModel $req) {
        return [
            "message" => "Hello from PublicController index method!",
            "id" => $id,
            "token" => $token,
            "allQueries" => $req->getQueries(),
            "allHeaders" => $req->getHeaders(),
            "cookies" => $req->getCookies(),
            "hint" => "Test middleware using query aa with value 2 to trigger forbidden response"
        ];
    }

}