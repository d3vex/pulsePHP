<?php

namespace D3vex\Pulsephp\Core\Container;

class DefinitionRegistry {

    private $definitions = [];

    public function __construct() {
    }

    public function addDefintion($name, IOCContainerDefinition $defintion): void {
        $this->definitions[$name] = $defintion;
    }

    public function getDefintion($name): IOCContainerDefinition|null {
        return $this->definitions[$name] ?? null;
    }

    public function hasDefintion($name): bool {
        return isset($this->definitions[$name]);
    }

    /**
     * @return IOCContainerDefinition[]
     */
    public function all(): array {
        return $this->definitions;
    }  
}