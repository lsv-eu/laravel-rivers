<?php

namespace LsvEu\Rivers\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LsvEu\Rivers\Cartography\RiverMap;
use LsvEu\Rivers\Exceptions\InvalidRiverMapException;

class AsRiverMap implements CastsAttributes
{
    public bool $withoutObjectCaching = true;

    /**
     * {@inheritDoc}
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?RiverMap
    {
        $decoded = json_decode($attributes[$key], true);

        return $decoded === null ? null : new RiverMap($decoded);
    }

    /**
     * {@inheritDoc}
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        if (! $value instanceof RiverMap) {
            throw new \InvalidArgumentException('The given value is not valid.');
        }

        if (! $value->isValid()) {
            throw new InvalidRiverMapException;
        }

        return [$key => json_encode($value->toArray())];
    }
}
