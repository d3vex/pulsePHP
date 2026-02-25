<?php
namespace D3vex\Pulsephp\Core\Http;


use D3vex\Pulsephp\Core\Http\DataBag\BodyDataBag;
use D3vex\Pulsephp\Core\Http\DataBag\CookiesDataBag;
use D3vex\Pulsephp\Core\Utils\DataBag;
use D3vex\Pulsephp\Core\Http\DataBag\HeadersDataBag;
use D3vex\Pulsephp\Core\Http\DataBag\QueryDataBag;




class RequestModel extends DataBag implements Interfaces\IRequest
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