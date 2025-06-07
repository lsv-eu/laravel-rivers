<?php

namespace LsvEu\Rivers\Observers;

use LsvEu\Rivers\Contracts\CreatesRaft;
use LsvEu\Rivers\Facades\Rivers;

class RiversObserver
{
    public function created(CreatesRaft $model): void
    {
        $this->handle($model, 'created', false);
    }

    public function deleted(CreatesRaft $model): void
    {
        $this->handle($model, 'deleted', true);
    }

    public function updated(CreatesRaft $model): void
    {
        $this->handle($model, 'updated', true);
    }

    protected function handle(CreatesRaft $model, string $event, bool $eventHasId): void
    {
        $listener = "model.$event.".get_class($model);
        if ($eventHasId) {
            $listener .= ".{$model->getKey()}";
        }

        Rivers::trigger($listener, $model, $eventHasId);
    }
}
