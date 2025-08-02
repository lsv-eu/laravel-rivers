<?php

namespace LsvEu\Rivers\Cartography\Traits;

use Illuminate\Database\Eloquent\Model;

trait SerializesData
{
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        return new static(json_decode($attributes[$key], true));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        if (! $value instanceof static) {
            throw new \InvalidArgumentException('The given value is not valid.');
        }

        return [$key => json_encode($value->toArray())];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
