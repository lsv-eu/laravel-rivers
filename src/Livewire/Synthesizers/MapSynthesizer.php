<?php

namespace LsvEu\Rivers\Livewire\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use LsvEu\Rivers\Cartography\RiverMap;

class MapSynthesizer extends Synth
{
    public static $key = 'riverMap';

    public static function match($target): bool
    {
        return $target instanceof RiverMap;
    }

    public function dehydrate(RiverMap $target): array
    {
        return [$target, []];
    }

    public function hydrate($target): RiverMap
    {
        return new RiverMap($target);
    }
}
