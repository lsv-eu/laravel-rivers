<?php

namespace LsvEu\Rivers\Cartography\Source;

class ModelUpdated extends ModelCreated
{
    public function getInterruptListener(array $raft): ?string
    {
        return "model.updated.$this->class.{$raft['modelId']}";
    }

    public function getStartListener(): ?string
    {
        return "model.updated.$this->class";
    }
}
