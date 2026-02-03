<?php


class ContainerDefinitionDontExist extends Exception {
    public function __construct($className) {
        parent::__construct("Invalid class parameter in class '$className'.");
    }
}
