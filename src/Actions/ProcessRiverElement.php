<?php

namespace LsvEu\Rivers\Actions;

use LsvEu\Rivers\Cartography\RiverElement;
use LsvEu\Rivers\Models\RiverRun;

class ProcessRiverElement
{
    public function __construct(protected RiverRun $run) {}

    public static function run(RiverRun $run, RiverElement $element, string $method = 'process'): void
    {
        (new static($run))->handle($element, $method);
    }

    public function handle(RiverElement $element, string $method = 'evaluate'): void
    {
        EvaluateRiverElement::run($this->run, $element, $method);
    }
}
