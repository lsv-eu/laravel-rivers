<?php

namespace LsvEu\Rivers\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use LsvEu\Rivers\Actions\ProcessRiverElement;
use LsvEu\Rivers\Cartography\Fork;
use LsvEu\Rivers\Models\RiverInterrupt;
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

        if ($run->river->isPaused()) {
            $this->delete();
            $run->update(['status' => 'paused']);

            return;
        }

        $run->update(['status' => 'running']);

        // Check if there is a new interrupt
        if ($this->handleInterrupt($run)) {
            static::dispatch($run->id);

            return;
        }

        $connection = $run->river->map->connections->firstWhere('startId', $run->location);

        if (! $connection) {
            $this->completeJob($run);
            $this->delete();

            return;
        }

        $next = $run->river->map->bridges->get($connection->endId)
            ?? $run->river->map->forks->get($connection->endId)
            ?? $run->river->map->rapids->get($connection->endId);

        if ($next instanceof Fork) {
            $nextConnectionConditionId = $next->getNext($run);
            $nextConnection = $run->river->map->connections
                ->where('startId', $next->id)
                ->where('startConditionId', $next->id != $nextConnectionConditionId ? $nextConnectionConditionId : null)
                ->first();

            if (! $nextConnection) {
                $this->completeJob($run);
                $this->delete();

                return;
            }

            $next = $run->river->map->rapids->get($nextConnection->endId);
        }

        ProcessRiverElement::run($run, $next);

        $run->location = $next->id;
        $run->save();

        $run->river->refresh();
        if ($this->handleInterrupt($run)) {
            return;
        } elseif ($run->river->isPaused()) {
            $run->update(['status' => 'paused']);
        } else {
            if (! $run->status == 'bridge') {
                static::dispatch($run->id);
            }
        }
    }

    protected function handleInterrupt(RiverRun $run): bool
    {
        $interrupted = false;

        $run->interrupts()->oldest()->each(function (RiverInterrupt $interrupt) use (&$interrupted, $run) {
            $launch = $run->river->map->getLaunchByInterruptListener($run, $interrupt->event, $interrupt->details);
            if ($launch) {
                $interrupted = true;
                $run->update(['location' => $launch->id]);
            }

            $interrupt->delete();
        });

        return $interrupted;
    }

    protected function completeJob(RiverRun $run): void
    {
        $run->completed_at = now();
        $run->location = null;
        $run->status = 'completed';
        $run->save();
    }
}
