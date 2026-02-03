<?php


class ContainerDefinition {

    public string $className;
    public bool $shared = true;
    public ?Closure $factory = null;
    public array $dependencies = [];

}
