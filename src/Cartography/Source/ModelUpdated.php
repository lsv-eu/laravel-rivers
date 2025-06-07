<?php

namespace LsvEu\Rivers\Cartography\Source;

use LsvEu\Rivers\Contracts\Raft;

class ModelUpdated extends ModelCreated
{
    public function getInterruptListener(Raft $raft): ?string
    {
        return "model.updated.$this->class.".$raft->toArray()['modelId'];
    }

    public function getStartListener(): ?string
    {
        return null;
    }
}
