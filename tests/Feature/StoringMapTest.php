<?php

use LsvEu\Rivers\Cartography\Rapid;
use LsvEu\Rivers\Models\River;
use Tests\Feature\Classes\TestRaftMap;
use Tests\Traits\UsesConfig;

uses(UsesConfig::class);

test('create river with rapid', function () {
    $map = River::create([
        'title' => 'Test River',
        'map' => new TestRaftMap([
            'rapids' => [
                [Rapid::class, ['label' => 'First Rapid']],
            ],
        ]),
    ]);

    $cleanMap = River::find($map->id);
    expect($cleanMap->map->rapids->first()->label)->toBe('First Rapid');
});
