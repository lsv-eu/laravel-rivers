<?php

namespace LsvEu\Rivers\Cartography;

use LsvEu\Rivers\Contracts\Raft;

abstract class Source extends RiverElement
{
    public string $id;

    public bool $enabled;

    public bool $restartable = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->enabled = $attributes['enabled'] ?? false;

        $this->restartable = $attributes['restartable'] ?? false;
    }

    public function createRaft(): mixed
    {
        return [];
    }

    public function getInterruptListener(Raft $raft): ?string
    {
        return null;
    }

    public function getStartListener(): ?string
    {
        return null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'enabled' => $this->enabled,
            'restartable' => $this->restartable,
        ];
    }
}
