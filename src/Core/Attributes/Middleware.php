<?php
namespace D3vex\Pulsephp\Core\Attributes;

use Attribute;


#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
class Middleware
{
    public function __construct(
        public string $middlewareClass
    ) {}
}