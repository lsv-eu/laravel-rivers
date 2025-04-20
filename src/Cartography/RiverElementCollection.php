<?php

namespace LsvEu\Rivers\Cartography;

use Illuminate\Support\Collection;

class RiverElementCollection extends Collection
{
    public function toArray(): array
    {
        return $this
            ->map(fn ($value) => [
                get_class($value),
                $value instanceof RiverElement ?
                    $value->toArray() :
                    throw new \Exception('Class '.get_class($value).' must extend '.RiverElement::class),
            ])
            ->values()
            ->all();
    }

    public static function make($items = [], $class = RiverElement::class): static
    {
        return (new static($items))
            ->mapWithKeys(function ($item) use ($class) {
                if (is_object($item)) {
                    if (is_subclass_of($item, $class)) {
                        return [$item->id => $item];
                    } else {
                        throw new \InvalidArgumentException('Class '.get_class($item)." must extend $class");
                    }
                }

                if (array_keys($item) !== [0, 1]) {
                    // TODO: Finish error message
                    throw new \InvalidArgumentException('Invalid syntax ????');
                }

                if (! class_exists($item[0])) {
                    throw new \InvalidArgumentException("Class {$item[0]} not found");
                }

                if (! is_subclass_of($item[0], $class)) {
                    throw new \InvalidArgumentException("Class {$item[0]} must extend $class");
                }

                $newItem = new $item[0]($item[1]);

                return [$newItem->id => $newItem];
            });
    }
}
