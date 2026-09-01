<?php

namespace LsvEu\Rivers\Contracts;

use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;

interface ProvidesInjections
{
    /**
     * @return array<string, callable>
     */
    public static function getInjections(River|RiverRun $river, ?Raft $raft = null): array;
}
