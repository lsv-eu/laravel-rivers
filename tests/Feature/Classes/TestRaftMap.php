<?php

namespace Tests\Feature\Classes;

use LsvEu\Rivers\Cartography\RiverMap;
use Tests\Unit\Classes\TestRaft;

class TestRaftMap extends RiverMap
{
    public function __construct(array $attributes = [])
    {
        $attributes['raftClass'] ??= TestRaft::class;

        parent::__construct($attributes);
    }
}
