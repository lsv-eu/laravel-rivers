<?php

use Tests\Traits\UsesConfig;

uses(UsesConfig::class);

test('create river with rapid', function () {
    $map = \LsvEu\Rivers\Models\River::create([
        'title' => 'Test River',
        'map' => new \LsvEu\Rivers\Cartography\RiverMap([
            'rapids' => [
                [\LsvEu\Rivers\Cartography\Rapid::class, ['label' => 'First Rapid']],
            ],
        ]),
    ]);

    $cleanMap = \LsvEu\Rivers\Models\River::find($map->id);
    expect($cleanMap->map->rapids->first()->label)->toBe('First Rapid');
});
