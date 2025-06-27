<?php

namespace Tests\Feature\Classes;

use LsvEu\Rivers\Cartography\Condition;
use Workbench\App\Rivers\Rafts\UserRaft;

class NameCondition extends Condition
{
    public ?string $name;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->name = $attributes['name'] ?? '';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'name' => $this->name,
        ];
    }

    public function evaluate(?UserRaft $user = null): bool
    {
        return $user->name == $this->name;
    }
}
