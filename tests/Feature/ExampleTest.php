<?php

use LsvEu\Rivers\Cartography\Launches\ModelCreated;
use LsvEu\Rivers\Models\River;
use Symfony\Component\Uid\Ulid;
use Tests\Feature\Classes\BasicUserMap;
use Tests\Traits\UsesConfig;
use Workbench\App\Models\Tag;
use Workbench\App\Models\Taggable;
use Workbench\App\Models\User;
use Workbench\App\Rivers\Rafts\UserRaft;

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
    $tag = Tag::create(['name' => 'Test',  'type' => 'user']);

    $map = new BasicUserMap([
        'launches' => [
            new ModelCreated([
                'id' => Ulid::generate(),
                'class' => Taggable::class,
                'eventId' => $tag->id,
                'raftClass' => UserRaft::class,
            ]),
        ],
    ]);

    $river = River::create([
        'title' => 'First River',
        'status' => 'active',
        'map' => $map,
    ]);
    $user = User::factory()->createOne(['name' => 'John']);
    $user->tags()->attach($tag);
    expect($river->riverRuns)->toHaveCount(1)
        ->and($river->riverRuns->first()->raft->name)->toBe('John');
});

test('mock app tag events', function () {});
