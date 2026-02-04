<?php

require_once __DIR__ . "/../src/app/App.php";

class BasicMiddleware implements MiddlewareInterface {
    public function __construct(){}
    public function handle(RequestModel $request): bool {
        if($request->getQuery("aa") == "2") return false;
        return true;
    }
}
$app = App::start();
$app->getRouter()->setBaseUrl("");
$app->registerSharedService(BasicMiddleware::class);
$app->registerController(PublicController::class);
$app->run();



#[Controller("/public")]
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
        ];
    }
}