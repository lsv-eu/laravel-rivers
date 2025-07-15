<?php

use LsvEu\Rivers\Cartography\Condition;
use LsvEu\Rivers\Cartography\Launch;
use LsvEu\Rivers\Models\RiverRun;
use Workbench\App\Models\User;
use Workbench\App\Rivers\Rafts\UserRaft;

test('launch without conditions tests true', function () {
    $launch = new class extends Launch {};
    $user = User::factory()->create();
    $run = new RiverRun(['raft' => $user->createRaft()]);

    expect($launch->check($run))->toBeTrue();
});

test('launch with a condition tests correctly', function () {
    $launchClass = new class extends Launch {};
    $condition = new class extends Condition
    {
        public function evaluate(?UserRaft $raft = null): bool
        {
            return $raft->name === 'Good';
        }
    };
    $launch = new $launchClass([
        'conditions' => [
            $condition,
        ],
    ]);
    $userBad = User::factory()->create(['name' => 'Bad']);
    $userGood = User::factory()->create(['name' => 'Good']);

    $runBad = new RiverRun(['raft' => $userBad->createRaft()]);
    $runGood = new RiverRun(['raft' => $userGood->createRaft()]);

    expect($launch->check($runBad))->toBeFalse()
        ->and($launch->check($runGood))->toBeTrue();
});

test('launch with multiple conditions tests correctly', function () {
    $launchClass = new class extends Launch {};
    $condition1 = new class extends Condition
    {
        public function evaluate(?UserRaft $raft = null): bool
        {
            return $raft->name === 'Good';
        }
    };
    $condition2 = new class extends Condition
    {
        public function evaluate(?UserRaft $raft = null): bool
        {
            return $raft->email === 'good@example.com';
        }
    };

    $launch = new $launchClass([
        'conditions' => [
            $condition1,
            $condition2,
        ],
    ]);

    $userBad = User::factory()->create(['name' => 'Good', 'email' => 'bad@example.com']);
    $userGood = User::factory()->create(['name' => 'Good', 'email' => 'good@example.com']);

    $runBad = new RiverRun(['raft' => $userBad->createRaft()]);
    $runGood = new RiverRun(['raft' => $userGood->createRaft()]);

    expect($launch->check($runBad))->toBeFalse()
        ->and($launch->check($runGood))->toBeTrue();
});
