<?php

namespace LsvEu\Rivers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LsvEu\Rivers\Rivers
 */
class Rivers extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rivers';
    }
}
