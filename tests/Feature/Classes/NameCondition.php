<?php

namespace Tests\Feature\Classes;

use LsvEu\Rivers\Cartography\Fork\Condition;
use LsvEu\Rivers\Contracts\Raft;
use Workbench\App\Models\User;

class NameCondition extends Condition
{
    public ?string $name;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->name = $attributes['name'] ?? '';
    }

    public function check(Raft|User $raft): bool
    {
        return $raft->name == $this->name;
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'name' => $this->name,
        ];
    }
}
