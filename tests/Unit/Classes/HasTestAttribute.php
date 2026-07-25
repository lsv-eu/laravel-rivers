<?php

namespace Tests\Unit\Classes;

use LsvEu\Rivers\Attributes\StoreProperty;

trait HasTestAttribute
{
    #[StoreProperty]
    public bool $test;
}
