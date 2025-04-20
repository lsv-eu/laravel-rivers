<?php

namespace LsvEu\Rivers\Contracts;

interface CreatesRaft
{
    public function createRaft(): ?Raft;
}
