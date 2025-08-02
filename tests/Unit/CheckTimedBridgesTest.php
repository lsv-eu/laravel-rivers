<?php

namespace Tests\Unit;

use Illuminate\Support\Carbon;

test('that help shows description and options', function () {
    $this->artisan('rivers:check_timed_bridges', ['--help'])
        ->expectsOutputToContain('Check Timed Bridges to see if any RiverRuns can be resumed')
        ->expectsOutputToContain('-d, --dry-run         Just output the RiverRuns to resume')
        ->expectsOutputToContain('-e, --exact           Only check for the current minute')
        ->expectsOutputToContain('-t, --timestamp       Override time to use with unix timestamp');
});

it('should check the correct time without timestamp and without exact', function () {
    $this->artisan('rivers:check_timed_bridges', [])
        ->expectsOutputToContain('Checking for RiverRuns resuming at or before '.now()->format('Y-m-d H:i'))
        ->expectsOutputToContain('Resuming 0 RiverRuns');
});

it('should check the correct time with timestamp and without exact', function () {
    $time = new Carbon('1981-06-27 03:06:28');
    $this->artisan('rivers:check_timed_bridges', ['--timestamp' => $time->timestamp])
        ->expectsOutputToContain('Checking for RiverRuns resuming at or before '.$time->format('Y-m-d H:i'))
        ->expectsOutputToContain('Resuming 0 RiverRuns');
});

it('should check the correct time without timestamp and with exact', function () {
    $this->artisan('rivers:check_timed_bridges', ['--exact' => true])
        ->expectsOutputToContain('Checking for RiverRuns resuming at '.now()->format('Y-m-d H:i'))
        ->expectsOutputToContain('Resuming 0 RiverRuns');
});

it('should check the correct time with timestamp and with exact', function () {
    $time = new Carbon('1981-06-27 03:06:28');
    $this->artisan('rivers:check_timed_bridges', ['--timestamp' => $time->timestamp, '--exact' => true])
        ->expectsOutputToContain('Checking for RiverRuns resuming at '.$time->format('Y-m-d H:i'))
        ->expectsOutputToContain('Resuming 0 RiverRuns');
});
