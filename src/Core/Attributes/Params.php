<?php

namespace D3vex\Pulsephp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Params
{
    public function __construct(
        public string $name = ''
    ) {}
}
