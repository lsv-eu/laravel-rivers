<?php

namespace LsvEu\Rivers\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use LsvEu\Rivers\Models\RiverTimedBridge;

class CheckTimedBridges extends Command
{
    protected $signature = 'rivers:check_timed_bridges
        {--d|dry-run : Just output the RiverRuns to resume}
        {--e|exact : Only check for the current minute}
        {--t|timestamp : Override time to use with unix timestamp}';

    protected $description = 'Check Timed Bridges to see if any RiverRuns can be resumed';

    public function handle(): int
    {
        $time = Carbon::createFromTimestamp($this->option('timestamp') ?: now()->timestamp)
            ->seconds(0);
        if ($this->option('exact')) {
            $this->info('Checking for RiverRuns resuming at '.$time->format('Y-m-d H:i'));
        } else {
            $this->info('Checking for RiverRuns resuming at or before '.$time->format('Y-m-d H:i'));
        }

        $bridgeQuery = RiverTimedBridge::query()
            ->when(
                $this->option('exact'),
                fn (Builder $builder) => $builder->where('resume_at', '=', $time),
                fn (Builder $builder) => $builder->where('resume_at', '<=', $time),
            )
            ->whereHas('riverRun', function (Builder $query) {
                $query->whereRunning(true);
            });

        $count = $bridgeQuery->count();
        $this->info('Resuming '.$count.' RiverRuns');

        if ($count) {
            if ($this->option('dry-run')) {
                $this->table(['ID'], $bridgeQuery->pluck('id'));
            } else {
                $bridgeQuery->each(function (RiverTimedBridge $bridge) {
                    $bridge->resume();
                });
            }
        }

        return 0;
    }
}
