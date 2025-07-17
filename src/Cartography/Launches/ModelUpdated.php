<?php

namespace LsvEu\Rivers\Cartography\Launches;

class ModelUpdated extends ModelCreated
{
    public function getInterruptListener(): ?string
    {
        return $this->class::createRiverRunListener('updated');
    }

    public function getStartListener(): ?string
    {
        return null;
    }
}
