<?php

namespace LsvEu\Rivers\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use LsvEu\Rivers\Models\RiverRun;

class ProcessRiverRun implements ShouldQueue
{
    use Queueable;

    public Carbon $createdAt;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $riverRunId,
    ) {
        $this->createdAt = now();
        $this->onQueue(Config::get('rivers.queue'));
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 5, 10, 30, 60];
    }

    /**
     * Determine the number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 5;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $run = RiverRun::findOrFail($this->riverRunId);
        } catch (RecordNotFoundException $e) {
            // Manually fail if the run has been deleted
            $this->fail($e);

            return;
        }

        if ($run->completed_at) {
            return;
        }

        // Check if there is a new interrupt
        if ($this->handleInterrupt($run)) {
            return;
        }

        if ($run->river->isPaused()) {
            return;
        }

        // TODO: Handle run step

        // Recheck in case interrupt was created while processing this current step
        $this->handleInterrupt($run);

        if (! $run->completed_at) {
            return;
        } else {
            // TODO: Set location to the current step and dispatch
        }
    }

    protected function handleInterrupt(RiverRun $run): bool
    {
        if ($run->completed_at && ! $run->river->map->repeatable) {
            $run->interrupts()->whereChecked(false)->latest()->update(['checked' => false]);

            return false;
        }
        $interrupts = $run->interrupts()->whereChecked(false)->latest()->get();
        if ($interrupts->isNotEmpty()) {
            foreach ($interrupts as $interrupt) {
                // If completed and interrupt is a source, start a new run (repeatable already checked) and break
                if (false) {
                    // TODO: Start new run
                    $run->interrupts()->whereChecked(false)->latest()->update(['checked' => false]);

                    return true;
                }
                // Elseif not completed, set location and break
            }

        }

        return false;
    }
}
