<?php

namespace LsvEu\Rivers\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StoreProperty
{
    public function __construct(
        public $default = null,
        public $setDefault = true,
    ) {}
}
