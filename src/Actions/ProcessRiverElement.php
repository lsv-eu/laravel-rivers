<?php

namespace LsvEu\Rivers\Actions;

use LsvEu\Rivers\Cartography\RiverElement;
use LsvEu\Rivers\Models\RiverRun;

class ProcessRiverElement
{
    public function __construct(protected RiverRun $run, protected array $additionalData = []) {}

    public static function run(RiverRun $run, RiverElement $element, string $method = 'process', array $additionalData = []): void
    {
        (new static($run, $additionalData))->handle($element, $method);
    }

    public function handle(RiverElement $element, string $method = 'process'): void
    {
        EvaluateRiverElement::run($this->run, $element, $method, $this->additionalData);
    }
}
