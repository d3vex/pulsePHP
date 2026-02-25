<?php
namespace D3vex\Pulsephp\Core\Container\Exceptions;


class ContainerDefinitionDontExist extends \Exception {
    public function __construct($className) {
        parent::__construct("Invalid class parameter in class '$className'.");
    }
}
