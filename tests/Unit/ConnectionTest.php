<?php

namespace Test\Unit;

use LsvEu\Rivers\Cartography\Connection;
use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Cartography\Rapid;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Cartography\Source;

it('should give valid start options', function () {
    $map = getMap();
    expect(Connection::startOptions($map))->toEqualCanonicalizing(['f1', 'f2', 'r1', 'r2', 's1', 's2']);
});

it('should give valid end options if start is not a fork', function () {
    $map = getMap();
    expect(Connection::endOptions($map))->toEqualCanonicalizing(['f1', 'f2', 'r1', 'r2', 's1', 's2']);
});

it('should give valid end options if start is a fork', function () {
    $map = getMap();
    expect(Connection::endOptions($map, true))->toEqualCanonicalizing(['r1', 'r2', 's1', 's2']);
});

it('should give valid condition options if start is a fork', function () {
    $map = getMap();
    expect(Connection::startConditionOptions($map, 'f1'))->toEqualCanonicalizing(['f1-c1', 'f1-c2'])
        ->and(Connection::startConditionOptions($map, 'f2'))->toEqualCanonicalizing(['f2-c1', 'f2-c2']);
});

it('should give correct errors', function () {
    $map = getMap([
        $connection1 = new Connection(['startId' => 's1', 'endId' => 's1']),
        $connection2 = new Connection(['startId' => 'f1', 'endId' => 'f2']),
        $connection3 = new Connection(['startId' => 'f1', 'startConditionId' => 'f2-c1', 'endId' => 's2']),
        $connection4 = new Connection(['startId' => 's3', 'endId' => 'f3']),
    ]);

    expect($connection1->validate($map))->toEqual([
        'endId' => 'Connection end cannot be same as start.',
    ])
        ->and($connection2->validate($map))->toEqual([
            'endId' => 'Connection end cannot be a fork if the start is a fork.',
        ])
        ->and($connection3->validate($map))->toEqual([
            'startConditionId' => 'Connection start condition, f2-c1, does not exist on fork, f1.',
        ])
        ->and($connection4->validate($map))->toEqual([
            'startId' => 'Connection start, s3, does not exist.',
            'endId' => 'Connection end, f3, does not exist.',
        ]);
});

function getMap(?array $connections = null): RiverMap
{
    $connections ??= [];

    $fakeForkCondition = new class extends Fork\Condition
    {
        public function evaluate(): bool
        {
            return true;
        }
    };

    return new RiverMap([
        'connections' => $connections,
        'forks' => [
            new class(['id' => 'f1', 'conditions' => [new $fakeForkCondition(['id' => 'f1-c1']), new $fakeForkCondition(['id' => 'f1-c2'])]]) extends Fork {},
            new class(['id' => 'f2', 'conditions' => [new $fakeForkCondition(['id' => 'f2-c1']), new $fakeForkCondition(['id' => 'f2-c2'])]]) extends Fork {},
        ],
        'rapids' => [
            new class(['id' => 'r1']) extends Rapid {},
            new class(['id' => 'r2']) extends Rapid {},
        ],
        'sources' => [
            new class(['id' => 's1']) extends Source {},
            new class(['id' => 's2']) extends Source {},
        ],
    ]);
}
