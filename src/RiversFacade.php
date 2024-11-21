<?php

namespace LsvEu\Rivers;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LsvEu\Rivers\Skeleton\SkeletonClass
 */
class RiversFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'rivers';
    }
}
