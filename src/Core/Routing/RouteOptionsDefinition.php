<?php
namespace D3vex\Pulsephp\Core\Routing;

class RouteOptionsDefinition extends RouteDefinition {
    public array $allowMethods = [];
    public bool $definedByUser = false;
}