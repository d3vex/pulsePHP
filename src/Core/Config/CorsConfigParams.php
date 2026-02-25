<?php
namespace D3vex\Pulsephp\Core\Config;

class CorsConfigParams
{
    public array|string $allowedOrigins = "*";
    public array|string $allowedMethods = "OPTIONS,HEAD";
    public array|string $allowedHeaders;
    public array|string $exposedHeaders;
    public bool $allowCredentials;

    public function __construct(
    ) {}
}