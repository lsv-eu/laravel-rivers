<?php

namespace Tests\Feature\Classes;

use LsvEu\Rivers\Cartography\RiverMap;
use Workbench\App\Rivers\Rafts\UserRaft;

class BasicUserMap extends RiverMap
{
    public function __construct(array $attributes = [])
    {
        $attributes['raftClass'] ??= UserRaft::class;

        parent::__construct($attributes);
    }
}
