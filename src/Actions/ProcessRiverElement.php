<?php

namespace LsvEu\Rivers\Actions;

use LsvEu\Rivers\Contracts\CanBeProcessed;
use LsvEu\Rivers\Models\RiverRun;
use ReflectionMethod;

class ProcessRiverElement
{
    protected array $injections;

    public function __construct(protected RiverRun $run)
    {
        $this->injections = GetRiverRunInjections::run($run);
    }

    public static function run(RiverRun $run, string $id): void
    {
        (new static($run))->handle($id);
    }

    public function handle(string $id): void
    {
        $element = $this->run->river->map->getElementById($id);

        if ($element instanceof CanBeProcessed) {
            $dependencies = [];

            foreach ((new ReflectionMethod($element::class, 'process'))->getParameters() as $parameter) {
                $dependencies[] = $this->injections[$parameter->name]() ?? null;
            }

            $element->process(...$dependencies);
        }
    }
}
