<?php

namespace D3vex\Pulsephp\Core\Http\Interfaces;

use D3vex\Pulsephp\Core\Http\DataBag\BodyDataBag;

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
