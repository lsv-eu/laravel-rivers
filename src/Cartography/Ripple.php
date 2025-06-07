<?php

namespace LsvEu\Rivers\Cartography;

use LsvEu\Rivers\Contracts\Raft;

abstract class Ripple extends RiverElement
{
    public ?string $description = null;

    public ?string $name = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->description = $attributes['description'] ?? $this->description ?: $this->description;
        $this->name = $attributes['name'] ?? $this->name ?: $this->name;
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'description' => $this->description,
            'name' => $this->name,
        ];
    }

    abstract public function process(Raft $raft): void;
}
