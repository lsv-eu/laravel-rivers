<?php

namespace LsvEu\Rivers\Cartography;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 *
 * @template-covariant TValue of RiverElement
 *
 * @implements \ArrayAccess<TKey, TValue>
 * @implements \Illuminate\Support\Enumerable<TKey, TValue>
 */
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

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @template T of RiverElement
     *
     * @param  Arrayable<TKey, T>|iterable<TKey, T>|null  $items
     * @param  class-string<T>  $class
     * @return static<TKey, T>
     */
    public static function make($items = [], string $class = RiverElement::class): static
    {
        return (new static($items))
            ->mapWithKeys(function ($item) use ($class) {
                if (is_object($item)) {
                    if (is_subclass_of($item, $class) || $item instanceof $class) {
                        return [$item->id => $item];
                    } else {
                        throw new \InvalidArgumentException('Class '.get_class($item)." must extend or be $class");
                    }
                }

                if (array_keys($item) !== [0, 1]) {
                    // TODO: Finish error message
                    throw new \InvalidArgumentException('Invalid syntax ????');
                }

                if (! class_exists($item[0])) {
                    throw new \InvalidArgumentException("Class {$item[0]} not found");
                }

                if (! is_subclass_of($item[0], $class) && $item[0] !== $class) {
                    throw new \InvalidArgumentException("Class {$item[0]} must extend or be $class");
                }

                $newItem = new $item[0]($item[1]);

                return [$newItem->id => $newItem];
            });
    }
}
