<?php
namespace D3vex\Pulsephp\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    public function __construct(
        public string $baseUrl = ''
    ) {}
}
