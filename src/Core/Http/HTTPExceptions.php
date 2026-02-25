<?php
namespace D3vex\Pulsephp\Core\Http;


class HTTPExceptions extends \Exception {
    protected int $HTTP_CODE = 500;
    public function __construct($message, $code = 0) {
        $this->HTTP_CODE = $code;
        parent::__construct($message);
    }

    public function getHttpCode(): int {
        return $this->HTTP_CODE;
    }

    public function __toString(): string {
        $result = [
            "error" => true,
            "message" => $this->message,
            "code"=> $this->HTTP_CODE,
        ];
        return json_encode($result); 
    }
}