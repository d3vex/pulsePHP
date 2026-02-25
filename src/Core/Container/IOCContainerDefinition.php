<?php

namespace D3vex\Pulsephp\Core\Container;

class IOCContainerDefinition {

    public string $className;
    public bool $shared = true;
    public ?\Closure $factory = null;
    public array $dependencies = [];

}
