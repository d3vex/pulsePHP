<?php

namespace D3vex\Pulsephp\Core\Http\Interfaces;

interface IResponse
{
    public function setStatusCode(int $code): void;

    public function setHeader(string $name, string $value): void;

    public function setBody(array|string $content): void;

    public function send(): void;
}

