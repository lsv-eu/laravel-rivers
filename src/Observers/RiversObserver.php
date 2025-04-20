<?php

namespace LsvEu\Rivers\Observers;

use LsvEu\Rivers\Contracts\CreatesRaft;
use LsvEu\Rivers\Facades\Rivers;

class RiversObserver
{
    public function created(CreatesRaft $model): void
    {
        $this->handle($model, 'created');
    }

    public function deleted(CreatesRaft $model): void
    {
        $this->handle($model, 'deleted');
    }

    public function saved(CreatesRaft $model): void
    {
        $this->handle($model, 'saved');
    }

    public function updated(CreatesRaft $model): void
    {
        $this->handle($model, 'updated');
    }

    protected function handle(CreatesRaft $model, string $event): void
    {
        $class = get_class($model);
        Rivers::trigger("model.$event.$class.{$model->getKey()}", true, $model->createRaft());
    }
}
