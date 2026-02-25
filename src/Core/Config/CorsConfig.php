<?php
namespace D3vex\Pulsephp\Core\Config;

class CorsConfig
{
    private array|string $allowedOrigins = "*";
    private array|string $allowedMethods = "OPTIONS,HEAD";
    private array|string $allowedHeaders;
    private array|string $exposedHeaders;
    private bool $allowCredentials;

    public function __construct(
    ) {}

    public function setAllowedOrigins(array|string $allowedOrigins): CorsConfig
    {
        $this->allowedOrigins = $allowedOrigins;
        return $this;
    }

    public function setAllowedMethods(array|string $allowedMethods): CorsConfig
    {
        $this->allowedMethods = $allowedMethods;
        return $this;
    }
    public function setAllowedHeaders(array|string $allowedHeaders): CorsConfig
    {
        $this->allowedHeaders = $allowedHeaders;
        return $this;
    }
    public function setAllowCredentials(bool $allowCredentials): CorsConfig
    {
        $this->allowCredentials = $allowCredentials;
        return $this;
    }
    public function setExposedHeaders(array|string $exposedHeaders): CorsConfig
    {
        $this->exposedHeaders = $exposedHeaders;
        return $this;
    }

    public function set(CorsConfigParams $params): CorsConfig
    {
        $this->allowedOrigins = $params->allowedOrigins;
        $this->allowedMethods = $params->allowedMethods;
        $this->allowedHeaders = $params->allowedHeaders;
        $this->exposedHeaders = $params->exposedHeaders;
        $this->allowCredentials = $params->allowCredentials;
        return $this;
    }
    public function get(): CorsConfigParams
    {
        $cors = new CorsConfigParams();
        $cors->allowedOrigins = $this->allowedOrigins;
        $cors->allowedMethods = $this->allowedMethods;
        $cors->allowedHeaders = $this->allowedHeaders;
        $cors->exposedHeaders = $this->exposedHeaders;
        $cors->allowCredentials = $this->allowCredentials;
        return $cors;
    }
    
    public function getAsHeaders(): array
    {
        $headers = [
            "Access-Control-Allow-Origin" => $this->allowedOrigins,
            "Access-Control-Allow-Methods" => $this->allowedMethods,
            "Access-Control-Allow-Headers" => $this->allowedHeaders,
            "Access-Control-Expose-Headers" => $this->exposedHeaders,
            "Access-Control-Allow-Credentials" => $this->allowCredentials ? "true" :"false",
            "Allow" => $this->allowedMethods,
        ];
        
        return $headers;
    }
}
