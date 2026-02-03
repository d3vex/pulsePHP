<?php


interface IResponse
{
    public function setStatusCode(int $code): void;

    public function setHeader(string $name, string $value): void;

    public function setBody(string $content): void;

    public function send(): void;
}

class ResponseModel implements IResponse {

    private Headers $headers;
    private array $body;
    private bool $isSent = false;

    public function setStatusCode(int $code): void {
        $this->headers->STATUS_CODE = (string)$code;
    }
    public function setHeader(string $name, string $value): void {
        $this->headers[$name] = $value;
    }
    public function setBody(string $content): void {
        $this->body = $content;
    }

    public function send(): void {
        // Send headers
        $this->isSent = true;
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Send body
        if(count($this->body) > 0) {
            echo json_encode($this->body);
        }else {
            echo "";
        }
    }

    public function isSent(): bool {
        return $this->isSent;
    }

}