<?php

namespace LsvEu\Rivers\Cartography;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\Uid\Ulid;

abstract class RiverElement implements Arrayable
{
    use Macroable;

    public string $id;

    /**
     * @var \ReflectionClass[]
     */
    protected array $_traits;

    public function __construct(array $attributes = [])
    {
        $this->_traits = (new \ReflectionClass($this))->getTraits();

        $this->id = $attributes['id'] ?? Ulid::generate();

        foreach ($this->_traits as $trait) {
            if ($trait->hasMethod("hydrate{$trait->getShortName()}")) {
                call_user_func([$this, "hydrate{$trait->getShortName()}"], $attributes);
            }
        }
    }

    public function getAllRiverElements(): array
    {
        return [$this];
    }

    public function toArray(): array
    {
        return array_reduce(
            $this->_traits,
            function (array $carry, \ReflectionClass $trait) {
                if ($trait->hasMethod("toArray{$trait->getShortName()}")) {
                    return $carry + call_user_func([$this, "toArray{$trait->getShortName()}"]);
                }

                return $carry;
            },
            [
                'id' => $this->id,
            ],
        );
    }

    public function validate(RiverMap $map): ?array
    {
        return [
            'Bad Object',
        ];
    }
}
