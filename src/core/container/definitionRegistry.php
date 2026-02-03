<?php


class DefinitionRegistry {

    private $definitions = [];

    public function __construct() {
    }

    public function addDefintion($name, ContainerDefinition $defintion): void {
        $this->definitions[$name] = $defintion;
    }

    public function getDefintion($name): ContainerDefinition|null {
        return $this->definitions[$name] ?? null;
    }

    public function hasDefintion($name): bool {
        return isset($this->definitions[$name]);
    }

    /**
     * @return ContainerDefinition[]
     */
    public function all(): array {
        return $this->definitions;
    }  
}