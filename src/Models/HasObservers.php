<?php

namespace LsvEu\Rivers\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

trait HasObservers
{
    protected array $observers = [];

    public function hasObserverEvent(string $class, string $event): bool
    {
        return in_array($event, $this->observers[$class]['events'] ?? []);
    }

    public function getObserverEvents(string $class): array
    {
        return $this->observers[$class]['events'] ?? [];
    }

    public function registerObserver(string $class, string|array $events, ?string $name = null): void
    {
        if (empty($events)) {
            unset($this->observers[$class]);
        } else {
            $this->observers[$class] = [
                'events' => Arr::wrap($events),
                'name' => $name ?? class_basename($class),
            ];
        }
    }

    protected function loadObservers(): void
    {
        $this->observers = Config::get('rivers.observers');
    }
}
