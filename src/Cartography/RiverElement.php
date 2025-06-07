<?php

namespace LsvEu\Rivers\Cartography;

use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\Uid\Ulid;

abstract class RiverElement implements Arrayable
{
    public string $id;

    public function __construct(array $attributes = [])
    {
        $this->id = $attributes['id'] ?? Ulid::generate();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }

    public function validate(RiverMap $map): ?array
    {
        return [
            'Bad Object',
        ];
    }
}
