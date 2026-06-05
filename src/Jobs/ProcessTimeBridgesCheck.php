<?php

namespace LsvEu\Rivers\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use LsvEu\Rivers\Models\RiverTimedBridge;

class ProcessTimeBridgesCheck implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Carbon $check,
    ) {
        $this->onQueue(Config::get('rivers.queue'));
    }

    public function handle(bool $exact = false, bool $dryRun = false): int|array
    {
        $time = $this->check;

        $bridgeQuery = RiverTimedBridge::query()
            ->when(
                $exact,
                fn (Builder $builder) => $builder->where('resume_at', '=', $time),
                fn (Builder $builder) => $builder->where('resume_at', '<=', $time),
            )
            ->whereHas('riverRun', function (Builder $query) {
                $query->whereStatus('bridge');
            });

        $count = $bridgeQuery->count();

        if ($count) {
            if ($dryRun) {
                return ['count' => $count, 'table' => $bridgeQuery->pluck('id')->all()];
            } else {
                $bridgeQuery->each(function (RiverTimedBridge $bridge) {
                    $bridge->resume();
                });
            }
        }

        return ['count' => $count];
    }

    public function tries(): int
    {
        return 1;
    }
}
