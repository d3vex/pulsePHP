<?php
namespace D3vex\Pulsephp\Core\Config;


class AppConfig
{
    private array $defaultHeaders = [
        "content-type" => "application/json"
    ];
    private CorsConfig $corsConfig;

    public function __construct()
    {
        $this->corsConfig = new CorsConfig();
    }
    
    public function setCorsConfig(CorsConfigParams $corsConfig): void
    {
        $this->corsConfig->set($corsConfig);
    }
    public function getCorsConfig(): CorsConfigParams
    {
        return $this->corsConfig->get();
    }
    public function getCorsAsHeaders(): array
    {
        return $this->corsConfig->getAsHeaders();
    }
    public function setDefaultHeaders(array $headers): void
    {
        $this->defaultHeaders = $headers;
    }
    public function setDefaultHeader(string $key, string $value): void
    {
        $this->defaultHeaders[$key] = $value;
    }
    public function getDefaultHeaders(): array
    {
        return $this->defaultHeaders; 
    }

}