<?php

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    public function __construct(
        public string $baseUrl = ''
    ) {}
}
