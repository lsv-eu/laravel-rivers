<?php

namespace LsvEu\Rivers\Exceptions;

class PropertyNotDefinedException extends \Exception
{
    public function __construct(string $property)
    {
        parent::__construct("Property \"$property\" is not defined.");
    }
}
