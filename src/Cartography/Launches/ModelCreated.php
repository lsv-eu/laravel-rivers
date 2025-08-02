<?php

namespace LsvEu\Rivers\Cartography\Launches;

use Illuminate\Database\Eloquent\Model;
use LsvEu\Rivers\Cartography\Launch;
use LsvEu\Rivers\Contracts\CreatesRaft;

class ModelCreated extends Launch
{
    /**
     * @var class-string<CreatesRaft>
     */
    public readonly string $class;

    public readonly ?string $eventId;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->class = $attributes['class'];

        $this->eventId = $attributes['eventId'] ?? null;
    }

    public function createRaft(?Model $record = null): Model
    {
        return $record;
    }

    public function getStartListener(): ?string
    {
        return $this->class::createRiverRunListener('created', $this->eventId);
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'class' => $this->class,
            'eventId' => $this->eventId,
        ];
    }
}
