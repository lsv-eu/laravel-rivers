<?php

namespace Tests\Unit;

use LsvEu\Rivers\Cartography\Launches\ModelCreated;
use LsvEu\Rivers\Models\HasObservers;
use LsvEu\Rivers\Models\River;
use Tests\Feature\Classes\BasicUserMap;
use Tests\Traits\UsesConfig;
use Workbench\App\Models\Page;
use Workbench\App\Models\Tag;
use Workbench\App\Models\Taggable;
use Workbench\App\Models\User;
use Workbench\App\Rivers\Rafts\UserRaft;

uses(UsesConfig::class);

test('can load observers from config', function () {
    $testClass = new class
    {
        use HasObservers;

        public function __construct()
        {
            $this->loadObservers();
        }

        public function getObservers(): array
        {
            return $this->observers;
        }
    };

    expect($testClass->getObservers())->toEqual([
        'Workbench\App\Models\User' => [
            'name' => 'User',
            'events' => ['created', 'updated', 'saved', 'deleted'],
        ],
    ]);
});

test('can register observer', function () {
    $testClass = new class
    {
        use HasObservers;

        public function getObservers(): array
        {
            return $this->observers;
        }
    };

    expect($testClass->getObservers())->toEqual([]);
    $testClass->registerObserver('FakeClass', 'created', 'Faker');
    expect($testClass->getObservers())->toEqual([
        'FakeClass' => [
            'name' => 'Faker',
            'events' => ['created'],
        ],
    ]);
});

test('can return if observer event exists', function () {
    $testClass = new class
    {
        use HasObservers;
    };

    $testClass->registerObserver('FakeClass', 'created');
    expect($testClass->hasObserverEvent('FakeClass', 'created'))->toBeTrue()
        ->and($testClass->hasObserverEvent('FakeClass', 'deleted'))->toBeFalse();
});

it('will not trigger a run if there is no raft provided', function () {
    $user = User::factory()->create();
    $page = Page::factory()->create([
        'user_id' => $user->id,
    ]);
    $tag = Tag::create(['name' => 'Test']);
    $river = River::create([
        'title' => 'Test River',
        'status' => 'active',
        'map' => new BasicUserMap([
            'launches' => [
                new ModelCreated([
                    'id' => 'user-tagged',
                    'class' => Taggable::class,
                    'raftClass' => UserRaft::class,
                    'eventId' => $tag->id,
                ]),
            ],
        ]),
    ]);

    $user->tags()->attach($tag);
    expect($river->riverRuns()->count())->toBe(1);

    $page->tags()->attach($tag);
    expect($river->riverRuns()->count())->toBe(1);
});
