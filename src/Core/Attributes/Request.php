<?php

namespace D3vex\Pulsephp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Request
{
    public function __construct()
    {
    }
}
