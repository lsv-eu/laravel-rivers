<?php

namespace LsvEu\Rivers\Concerns;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use LsvEu\Rivers\Observers\RiversObserver;

#[ObservedBy(RiversObserver::class)]
trait RiverRunnable
{
    public static function createRiverRunListener(string $event, ?string $id = null): string
    {
        return collect([
            'model',
            __CLASS__,
            $event,
            $id,
        ])
            ->filter()
            ->implode('.');
    }
}
