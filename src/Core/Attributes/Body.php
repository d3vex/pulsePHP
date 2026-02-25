<?php

namespace D3vex\Pulsephp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Body
{
    public function __construct(
        public ?string $dto = null
    ) {}
}
