<?php


interface IResponse
{
    public function setStatusCode(int $code): void;

    public function setHeader(string $name, string $value): void;

    public function setBody(array|string $content): void;

    public function send(): void;
}

class ResponseModel implements IResponse
{

    private HeadersDataBag $headers;
    private array $headersData;
    private array|string $body;
    private bool $isSent = false;

    public function __construct()
    {
        $this->headers = new HeadersDataBag();
        $this->headersData = [];
        $this->body = [];
    }

    public function setStatusCode(int $code): void
    {
        http_response_code($code);
        $this->headersData["status_code"] = (string) $code;
    }
    public function setHeaders(array $headers): void
    {
        $this->headersData = $headers;
    }
    public function setHeader(string $name, string $value): void
    {
        $this->headersData[$name] = $value;
    }
    public function setBody(array|string $content): void
    {
        $this->body = $content;
    }

    public function send(): void
    {
        // Send headers
        $this->isSent = true;
        $this->headers->init($this->headersData);
        foreach ($this->headers->all() as $name => $value) {
            header("$name: $value");
        }

        // Send body
        if (is_array($this->body)) {
            echo json_encode($this->body);
        } else {
            echo $this->body;
        }

    }

    public function isSent(): bool
    {
        return $this->isSent;
    }

}