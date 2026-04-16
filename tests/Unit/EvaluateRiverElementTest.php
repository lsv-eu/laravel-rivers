<?php

namespace Tests\Unit;

use LsvEu\Rivers\Actions\EvaluateRiverElement;
use LsvEu\Rivers\Cartography;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;
use Tests\Feature\Classes\BasicUserMap;
use Workbench\App\Models\User;
use Workbench\App\Rivers\Launches\UserCreated;
use Workbench\App\Rivers\Rafts\UserRaft;

test('evaluate should provide raft and run', function () {
    $river = River::create([
        'title' => 'Queue Test',
        'status' => 'active',
        'map' => new BasicUserMap([
            'launches' => [new UserCreated(['id' => 'user-created'])],
        ]),
    ]);

    $ripple = new class extends Cartography\Ripple
    {
        public int $userId;

        public function process(?UserRaft $raft = null, ?RiverRun $run = null): void
        {
            $this->userId = $raft->id;
        }
    };

    $user = User::factory()->createQuietly();
    $run = RiverRun::create([
        'river_id' => $river->id,
        'raft' => $user->createRaft(),
    ]);
    EvaluateRiverElement::run($run, $ripple, 'process');
    expect($ripple->userId)->toBe($user->id);
});

test('evaluate should run without a raft', function () {
    $river = River::create([
        'title' => 'Queue Test',
        'status' => 'active',
        'map' => new BasicUserMap,
    ]);

    $ripple = new class extends Cartography\Ripple
    {
        public string $output;

        public function process(?UserRaft $raft = null, ?\stdClass $other = null): void
        {
            $this->output = $other->message;
        }
    };

    EvaluateRiverElement::run($river, $ripple, 'process', ['other' => (object) ['message' => 'test message']]);
    expect($ripple->output)->toBe('test message');
});
