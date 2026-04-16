<?php

namespace Tests\Feature\Classes;

use LsvEu\Rivers\Contracts\Raft;

class NameRaft extends Raft
{
    protected array $properties = [
        'name' => 'string',
    ];

    protected string $name;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->name = $data['name'];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }

    protected function getRawProperty($key): mixed
    {
        return $this->$key;
    }

    protected function createRaftId(array $data): string
    {
        return md5($data['name']);
    }
}
