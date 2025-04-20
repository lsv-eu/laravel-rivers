<?php

namespace Tests\Unit;

use LsvEu\Rivers\Models\HasObservers;
use Tests\Traits\UsesConfig;

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
