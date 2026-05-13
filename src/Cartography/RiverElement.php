<?php

namespace LsvEu\Rivers\Cartography;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use LsvEu\Rivers\Attributes\StoreProperty;
use LsvEu\Rivers\Exceptions\PropertyNotDefinedException;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Uid\Ulid;

abstract class RiverElement implements Arrayable
{
    use Macroable;

    public string $id;

    /**
     * @var ReflectionClass[]
     */
    protected array $_propertiesWithAttributes;

    /**
     * @var ReflectionProperty[]
     */
    protected array $_traits;

    /**
     * @throws PropertyNotDefinedException
     */
    public function __construct(array $attributes = [])
    {
        $reflector = new ReflectionClass($this);
        $this->_traits = $reflector->getTraits();
        $this->_propertiesWithAttributes = array_filter(
            $reflector->getProperties(),
            fn (ReflectionProperty $property) => $property->getAttributes(StoreProperty::class) !== [],
        );

        $this->id = $attributes['id'] ?? Ulid::generate();

        foreach ($this->_traits as $trait) {
            if ($trait->hasMethod("hydrate{$trait->getShortName()}")) {
                call_user_func([$this, "hydrate{$trait->getShortName()}"], $attributes);
            }
        }

        foreach ($this->_propertiesWithAttributes as $property) {
            $instance = $property->getAttributes(StoreProperty::class)[0]->newInstance();
            if ($instance->setDefault) {
                $property->setValue($this, $attributes[$property->getName()] ?? $instance->default);
            } else {
                $property->setValue($this, $attributes[$property->getName()] ?? throw new PropertyNotDefinedException($property->getName()));
            }
        }
    }

    public function getAllRiverElements(): array
    {
        return [$this];
    }

    public function toArray(): array
    {
        $traitsArray = array_reduce(
            $this->_traits,
            function (array $carry, ReflectionClass $trait) {
                if ($trait->hasMethod("toArray{$trait->getShortName()}")) {
                    return $carry + call_user_func([$this, "toArray{$trait->getShortName()}"]);
                }

                return $carry;
            },
            [],
        );

        $propertiesArray = array_reduce(
            $this->_propertiesWithAttributes,
            function (array $carry, ReflectionProperty $property) {
                return $carry + [$property->getName() => $property->getValue($this)];
            },
            []
        );

        return ['id' => $this->id, ...$traitsArray, ...$propertiesArray];
    }

    public function validate(RiverMap $map): ?array
    {
        return [
            'Bad Object',
        ];
    }
}
