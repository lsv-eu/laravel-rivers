<?php

namespace LsvEu\Rivers\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use LsvEu\Rivers\Jobs\ProcessTimeBridgesCheck;

class CheckTimedBridges extends Command
{
    protected $signature = 'rivers:check-timed-bridges
        {--d|dry-run : Just output the RiverRuns to resume}
        {--dispatch : Dispatch a job to process the check}
        {--e|exact : Only check for the current minute}
        {--t|timestamp : Override time to use with unix timestamp}';

    protected $description = 'Check Timed Bridges to see if any RiverRuns can be resumed';

    public function handle(): int
    {
        $time = Carbon::createFromTimestamp($this->option('timestamp') ?: now()->timestamp);
        if (! (int) config('rivers.timed_bridges.seconds')) {
            $time->seconds(0);
        }
        if ($this->option('exact')) {
            $this->info('Checking for RiverRuns resuming at '.$time->format('Y-m-d H:i:s'));
        } else {
            $this->info('Checking for RiverRuns resuming at or before '.$time->format('Y-m-d H:i:s'));
        }

        if ($this->option('dispatch')) {
            Queue::push(new ProcessTimeBridgesCheck($time));
            $this->info('Dispatched job');

            return 0;
        }

        $results = (new ProcessTimeBridgesCheck($time))->handle($this->option('exact'), $this->option('dry-run'));

        $count = $results['count'];
        $this->info('Resuming '.$count.' RiverRuns');

        if (isset($results['table'])) {
            $this->table(['ID'], $results['table']);
        }

        return 0;
    }
}
