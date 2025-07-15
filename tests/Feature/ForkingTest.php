<?php

namespace Tests\Feature;

use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Cartography\Launches\ModelCreated;
use LsvEu\Rivers\Cartography\Rapid;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Models\River;
use Tests\Feature\Classes\NameCondition;
use Tests\Feature\Classes\PausingRipple;
use Workbench\App\Models\User;

it('should choose the first connection', function () {
    $river = getRiver();
    User::factory()->create(['name' => 'John']);
    expect($river->riverRuns()->latest()->first()?->location)->toEqual('rapid-john');
});

it('should complete if the first condition is not connected', function () {
    $river = getRiver(includeFirst: false);
    User::factory()->create(['name' => 'John']);
    expect($river->riverRuns()->latest()->first()?->location)->toBeNull()
        ->and($river->riverRuns()->latest()->first()?->completed_at)->toBeObject();
});

it('should choose the second connection', function () {
    $river = getRiver();
    User::factory()->create(['name' => 'Mary']);
    expect($river->riverRuns()->latest()->first()?->location)->toEqual('rapid-mary');
});

it('should choose the else connection', function () {
    $river = getRiver();
    User::factory()->create(['name' => 'Frank']);
    expect($river->riverRuns()->latest()->first()?->location)->toEqual('rapid-other');
});

it('should complete if the else is not connected', function () {
    $river = getRiver(false);
    User::factory()->create(['name' => 'Frank']);
    expect($river->riverRuns()->latest()->first()?->location)->toBeNull()
        ->and($river->riverRuns()->latest()->first()?->completed_at)->toBeObject();
});

function getRiver(bool $includeElse = true, bool $includeFirst = true): River
{
    $connections = [
        Connection::make('launch-one', null, 'fork-on-name'),
        Connection::make('fork-on-name', 'condition-mary', 'rapid-mary'),
    ];

    if ($includeFirst) {
        $connections[] = Connection::make('fork-on-name', 'condition-john', 'rapid-john');
    }

    if ($includeElse) {
        $connections[] = Connection::make('fork-on-name', null, 'rapid-other');
    }

    return River::create([
        'title' => 'Forking Test',
        'status' => 'active',
        'map' => new RiverMap([
            'connections' => $connections,
            'forks' => [
                new Fork([
                    'id' => 'fork-on-name',
                    'conditions' => [
                        new NameCondition(['id' => 'condition-john', 'name' => 'John']),
                        new NameCondition(['id' => 'condition-mary', 'name' => 'Mary']),
                    ],
                ]),
            ],
            'rapids' => [
                new Rapid(['id' => 'rapid-john', 'ripples' => [new PausingRipple]]),
                new Rapid(['id' => 'rapid-mary', 'ripples' => [new PausingRipple]]),
                new Rapid(['id' => 'rapid-other', 'ripples' => [new PausingRipple]]),
            ],
            'launches' => [
                new ModelCreated([
                    'id' => 'launch-one',
                    'class' => User::class,
                ]),
            ],
        ]),
    ]);
}
