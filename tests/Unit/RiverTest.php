<?php

namespace Tests\Unit;

use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Exceptions\InvalidRiverMapException;
use LsvEu\Rivers\Models\River;
use Tests\Feature\Classes\TestRaftMap;

it('should accept a valid RiverMap', function () {
    River::create([
        'title' => 'test',
        'map' => new TestRaftMap,
    ]);
})->throwsNoExceptions();

it('should throw an exception for an invalid RiverMap', function () {
    River::create([
        'title' => 'test',
        'map' => new TestRaftMap([
            'connections' => [
                Connection::make('start', null, 'end'),
            ],
        ]),
    ]);
})->throws(InvalidRiverMapException::class);
