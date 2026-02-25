<?php

namespace D3vex\Pulsephp\Core\Utils;

use D3vex\Pulsephp\Core\Utils\Interfaces\IRequestDataBag;

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
            throw new \RuntimeException("ParametersBag can only be initialized once.");
        }
        $this->parameters = $parameters;
        $this->isInstancied = true;
    }
}