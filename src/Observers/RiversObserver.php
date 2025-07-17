<?php

namespace LsvEu\Rivers\Observers;

use Illuminate\Database\Eloquent\Model;
use LsvEu\Rivers\Contracts\CreatesRaft;
use LsvEu\Rivers\Contracts\ProvidesRiverEventId;
use LsvEu\Rivers\Facades\Rivers;

class RiversObserver
{
    public function created(CreatesRaft|Model $model): void
    {
        if ($raft = $model->createRaft()) {
            Rivers::trigger($this->createListener($model, 'created'), $raft);
        }
    }

    public function deleted(CreatesRaft|Model $model): void
    {
        if ($raft = $model->createRaft()) {
            Rivers::trigger($this->createListener($model, 'created'), $raft);
        }
    }

    public function updated(CreatesRaft|Model $model): void
    {
        if ($raft = $model->createRaft()) {
            Rivers::trigger(
                $this->createListener($model, 'updated'),
                $raft,
                $model->getChanges(),
            );
        }
    }

    protected function createListener(CreatesRaft $model, string $event): string
    {
        return $model::createRiverRunListener(
            event: $event,
            id: $model instanceof ProvidesRiverEventId ? $model->getRiverEventId() : null,
        );
    }
}
