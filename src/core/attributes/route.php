<?php

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public string $path,
        public string $method = 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH'
    ) {}
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class Body
{
    public function __construct(
        public ?string $dto = null
    ) {}   
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class Query
{
    public function __construct(
        public string $name = ''
    ) {}   
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class Params
{
    public function __construct(
        public string $name = ''
    ) {}   
}