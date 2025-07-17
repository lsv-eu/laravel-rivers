<?php

namespace LsvEu\Rivers\Contracts;

interface CreatesRaft
{
    public function createRaft(): ?Raft;

    public static function createRiverRunListener(string $event, ?string $id = null): string;
}
