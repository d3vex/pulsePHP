<?php


interface IRequestDataBag
{
    public function get(string $key, $default = null);
    public function all(): array;
    public function has(string $key): bool;
    public function init(array $parameters): void;
}

class DataBag implements IRequestDataBag
{
    private array $parameters = [];
    private bool $isInstancied = false;

    public function get(string $key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->parameters;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    public function init(array $parameters): void
    {
        if ($this->isInstancied) {
            throw new RuntimeException("ParametersBag can only be initialized once.");
        }
        $this->parameters = $parameters;
        $this->isInstancied = true;
    }
}

class QueryDataBag extends DataBag
{
    public static function fromUri(?string $queryString): QueryDataBag
    {
        if (empty($queryString)) {
            return new QueryDataBag();
        }
        $queryBag = new QueryDataBag();
        parse_str($queryString, $queryParameters);
        $queryBag->init($queryParameters);
        return $queryBag;
    }
}

class HeadersDataBag extends DataBag
{
    public static function fromGlobals(): HeadersDataBag
    {
        $headersBag = new HeadersDataBag();
        $headers = getallheaders();
        if (count($headers) > 0) {
            $headersBag->init($headers);
            return $headersBag;
        }

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = substr($key, 5);
                $headers[$headerName] = $value;
            }
        }
        $headersBag->init($headers);
        return $headersBag;
    }
}

class BodyDataBag extends DataBag
{
}

class CookiesDataBag extends DataBag
{
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

    public function getHeaders(): array;
    public function getHeader(string $name): ?string;

    public function getCookies(): array;
    public function getCookie(string $name): ?string;

    public function hasCookie(string $name): bool;

    public function getBody(): BodyDataBag;

    public function getQueries(): array;
    public function getQuery(string $name): ?string;
    public function isSecure(): bool;

}


class RequestModel extends DataBag implements IRequest
{
    private array $data = [
        "method" => "GET",
        "uri" => "/",
        "protocol" => "HTTP/1.1",
        "host" => null,
        "port" => null,
        "remoteAddr" => null,
        "remotePort" => null,
        "remoteHost" => null,
        "serverIp" => null,
        "query" => null,
    ];

    private HeadersDataBag $headers;
    private CookiesDataBag $cookies;
    private QueryDataBag $query;
    private BodyDataBag $body;

    public static function fromGlobals(): RequestModel
    {
        $request = new RequestModel();
        $request->data["method"] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $request->data["uri"] = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $request->data["query"] = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_QUERY);
        $request->data["protocol"] = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        $request->headers = HeadersDataBag::fromGlobals();
        $request->data["remoteAddr"] = $_SERVER['REMOTE_ADDR'] ?? '';
        $request->data["remotePort"] = $_SERVER['REMOTE_PORT'] ?? '';
        $request->data["remoteHost"] = $_SERVER['REMOTE_HOST'] ?? '';
        $request->data["serverIp"] = $_SERVER['SERVER_ADDR'] ?? '';
        $request->data["host"] = $_SERVER['HTTP_HOST'] ?? null;
        $request->data["port"] = $_SERVER['HTTP_PORT'] ?? null;
        $request->query = QueryDataBag::fromUri($request->data["query"]);
        $request->body = new BodyDataBag();
        $request->body->init($_POST ?? []);
        $request->cookies = new CookiesDataBag();
        $request->cookies->init($_COOKIE ?? []);

        // Clear superglobals for security
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
        return $this->data["method"];
    }
    public function getRequestUri(): string
    {
        return $this->data["uri"];
    }
    public function getProtocol(): string
    {
        return $this->data["protocol"];
    }

    public function getHost(): ?string
    {
        return $this->data["host"];
    }
    public function getPort(): ?int
    {
        return $this->data["port"];
    }

    public function getClientIp(): ?string
    {
        return $this->data["remoteAddr"];
    }
    public function getServerIp(): ?string
    {
        return $this->data["serverIp"];
    }

    public function getHeaders(): array
    {
        return $this->headers->all();
    }
    public function getHeader(string $name): ?string
    {
        return $this->headers->get($name);
    }

    public function getCookies(): array
    {
        return $this->cookies->all();
    }
    public function getCookie(string $name): ?string
    {
        return $this->cookies->get($name);
    }
    public function hasCookie(string $name): bool
    {
        return $this->cookies->has($name);
    }

    public function getBody(): BodyDataBag
    {
        return $this->body;
    }

    public function getQueries(): array
    {
        return $this->query->all();
    }

    public function getQuery(string $name): ?string
    {
        return $this->query->get($name);
    }

    public function isSecure(): bool
    {
        $proto = $this->getHeader('X-Forwarded-Proto');
        if ($proto !== null) {
            return strtolower($proto) === 'https';
        }
        return false;
    }

}