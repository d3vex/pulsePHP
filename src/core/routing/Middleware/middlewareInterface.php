<?php

interface MiddlewareInterface
{
    public function handle(RequestModel $request);
}
