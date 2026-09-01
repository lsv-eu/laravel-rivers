<?php

namespace Tests\Unit\Classes;

use LsvEu\Rivers\Contracts\ProvidesInjections;
use LsvEu\Rivers\Contracts\Raft;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;

class AdditionalInjections implements ProvidesInjections
{
    /**
     * {@inheritDoc}
     */
    public static function getInjections(River|RiverRun $river, ?Raft $raft = null): array
    {
        return [
            'book' => fn () => 'book',
        ];
    }
}
