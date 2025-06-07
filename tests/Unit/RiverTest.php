<?php

namespace Tests\Unit;

use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Exceptions\InvalidRiverMapException;
use LsvEu\Rivers\Models\River;

it('should accept a valid RiverMap', function () {
    River::create([
        'title' => 'test',
        'map' => new RiverMap([
            'connections' => [],
        ]),
    ]);
})->throwsNoExceptions();

it('should throw an exception for an invalid RiverMap', function () {
    River::create([
        'title' => 'test',
        'map' => new RiverMap([
            'connections' => [
                Connection::make('start', null, 'end'),
            ],
        ]),
    ]);
})->throws(InvalidRiverMapException::class);
