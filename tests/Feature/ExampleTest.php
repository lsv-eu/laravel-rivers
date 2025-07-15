<?php

use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Models\River;
use Symfony\Component\Uid\Ulid;
use Tests\Traits\UsesConfig;
use Workbench\App\Models\Tag;
use Workbench\App\Models\User;

uses(UsesConfig::class);

test('example', function () {
    expect(true)->toBeTrue();
});

test('create_users', function () {
    // $user = \Orchestra\Testbench\Factories\UserFactory::new()->createOne(['name' => 'John']);
    $user = User::factory()->createOne(['name' => 'John']);

    expect($user->name)->toBe('John');
});

test('create_first_river', function () {
    $map = new RiverMap([
        'launches' => [
            new \LsvEu\Rivers\Cartography\Launches\ModelCreated([
                'id' => Ulid::generate(),
                'class' => \Workbench\App\Models\Taggable::class,
            ]),
        ],
    ]);
    $river = River::create([
        'title' => 'First River',
        'status' => 'active',
        'map' => $map,
    ]);

    $tag = Tag::create(['name' => 'Test',  'type' => 'user']);
    $user = User::factory()->createOne(['name' => 'John']);
    $user->tags()->attach($tag);
    expect($river->riverRuns)->toHaveCount(1);
    expect($river->riverRuns->first()->raft->name)->toBe('John');
});

test('mock app tag events', function () {});
