<?php

namespace Tests\Unit\Classes;

use LsvEu\Rivers\Contracts\AttributeRaft;

class TestRaft extends AttributeRaft
{
    protected array $properties = [
        'name' => 'string',
        'upper_name' => 'string',
    ];

    protected function propertyUpperName(): string
    {
        return str($this->getRawProperty('name'))->upper();
    }

    protected function propertyLowerName(): string
    {
        return str($this->getRawProperty('name'))->lower();
    }
}
