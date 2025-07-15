<?php

use LsvEu\Rivers\Cartography\Condition;
use LsvEu\Rivers\Cartography\Launch;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Models\RiverRun;
use Tests\Unit\Classes\EmptyLaunch;
use Tests\Unit\Classes\EmptyRaft;
use Tests\Unit\Classes\TestRaft;
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

it('should be invalid if not raft class is provided', function () {
    $map = new RiverMap([
        'raftClass' => EmptyRaft::class,
    ]);

    $launch = new EmptyLaunch;

    $errors = $launch->validate($map);
    expect($errors)->toBeArray()->toHaveKey('raftClass')
        ->and($errors['raftClass'])->toBe('A raft class must be provided.');
});

it('should be invalid if the launches do not match the raft type', function () {

    $map = new RiverMap([
        'raftClass' => TestRaft::class,
    ]);

    $launch = new EmptyLaunch([
        'raftClass' => EmptyRaft::class,
    ]);

    $errors = $launch->validate($map);
    expect($errors)->toBeArray()->toHaveKey('raftClass')
        ->and($errors['raftClass'])->toBe('The raft class provided does not match the map raft class.');
});
