<?php
namespace D3vex\Pulsephp\Core\Utils\Interfaces;

interface IRequestDataBag
{
    public function get(string $key, $default = null);
    public function all(): array;
    public function has(string $key): bool;
    public function init(array $parameters): void;
}