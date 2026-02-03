<?php


class Headers
{
    public string $HTTP_X_FORWARDED_FOR;
    public string $HTTP_X_FORWARDED_HOST;
    public string $HTTP_X_FORWARDED_PORT;
    public string $HTTP_X_FORWARDED_PROTO;
    public string $HTTP_X_FORWARDED_AWS_ELB;
    public string $HTTP_X_REAL_IP;
    public bool $HTTP_X_FORWARDED;

    public string $HTTP_CACHE_CONTROL;
    public string $HTTP_CACHE_CONTROL_STRICT;
    public string $HTTP_CACHE_CONTROL_ALLOW_ORIGINS;
    public string $HTTP_CACHE_CONTROL_ALLOW_CREDENTIALS;
    public string $HTTP_CACHE_CONTROL_ALLOW_HEADERS;
    public string $HTTP_CACHE_CONTROL_ALLOW_METHODS;

    public string $HTTP_CONNECTION;
    public string $HTTP_CONNECTION_WRITE;
    public string $HTTP_CONNECTION_EXECUTE;
    public string $HTTP_CONNECTION_BUFFER;
    public string $HTTP_CONNECTION_BUFFER_LENGTH;
    public string $HTTP_CONNECTION_BUFFER_LENGTH_SIGNED;
    public string $HTTP_CONNECTION_BUFFER_LENGTH_END;
    public string $HTTP_CONNECTION_BUFFER_LENGTH_END_SIGNED;
    public string $HTTP_USER_AGENT;

    public string $HTTP_DATE;
    public string $HTTP_PRAGMA;
    public string $HTTP_TRAILER;
    public string $HTTP_UPGRADE;
    public string $CONTENT_LENGTH;
    public string $CONTENT_TYPE;

    public string $HTTP_HOST;
    public string $HTTP_REFERER;
    public string $HTTP_ORIGIN;
    public string $HTTP_ACCEPT;
    public string $HTTP_UPGRADE_INSECURE_REQUESTS;
    public string $SSL_PROTOCOL;
    public string $SSL_CIPHER;

    public string $STATUS_CODE;

    public static function fromGlobals(): self
    {

        $headers = new Headers();
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }

        // Special non HTTP_ headers
        foreach (['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'] as $key) {
            if (isset($_SERVER[$key])) {
                $headers[str_replace('_', '-', $key)] = $_SERVER[$key];
            }
        }

        return $headers;

    }

}

interface IRequest
{
    public function getRequestMethod(): string;
    public function getRequestUri(): string;
    public function getProtocol(): string;

    public function getHost(): ?string;
    public function getPort(): ?int;

    public function getClientIp(): ?string;
    public function getServerIp(): ?string;

    public function getHeaders(): Headers;
    public function getHeader(string $name): ?string;

    public function getContentType(): ?string;
    public function getContentLength(): ?int;

    public function isSecure(): bool;
}


class RequestModel implements IRequest
{
    private string $method;
    private string $uri;
    private string $protocol;
    public array $Query;
    public array $Body;
    public string $remoteAddr;
    public string $remotePort;
    public string $remoteHost;
    public string $serverAddr;
    public string $host;
    public string $port;

    public Headers $headers;

    public static function fromGlobals(): RequestModel
    {
        $request = new RequestModel();
        $request->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $request->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $request->protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        $request->headers = Headers::fromGlobals();
        $request->remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        $request->remotePort = $_SERVER['REMOTE_PORT'] ?? '';
        $request->remoteHost = $_SERVER['REMOTE_HOST'] ?? '';
        $request->serverIp = $_SERVER['SERVER_ADDR'] ?? '';
        $request->host = $_SERVER['HTTP_HOST'] ?? null;
        $request->port = $_SERVER['HTTP_PORT'] ?? null;
        $request->Query = $_GET ?? [];
        $request->Body = $_POST ?? [];

        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_SERVER = [];
        $_REQUEST = [];
        $_COOKIE = [];

        return $request;
    }

    public function getRequestMethod(): string
    {
        return $this->method;
    }
    public function getRequestUri(): string
    {
        return $this->uri;
    }
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getHost(): ?string {
        return $this->host;
    }
    public function getPort(): ?int {
        return $this->port;
    }

    public function getClientIp(): ?string {
        return $this->remoteAddr;
    }
    public function getServerIp(): ?string {
        return $this->serverIp;
    }

    public function getHeaders(): Headers
    {
        return $this->headers;
    }
    public function getHeader(string $name): ?string {
        return $this->headers[$name] ?? null;
    }

    public function getContentType(): ?string {
        return $this->getHeader('Content-Type');
    }
    public function getContentLength(): ?int {
        $length = $this->getHeader('Content-Length');
        return $length !== null ? (int)$length : null;
    }

    public function isSecure(): bool {
        $proto = $this->getHeader('X-Forwarded-Proto');
        if ($proto !== null) {
            return strtolower($proto) === 'https';
        }
        return false;
    }

}