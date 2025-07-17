<?php

namespace LsvEu\Rivers\Actions;

use LsvEu\Rivers\Cartography\RiverElement;
use LsvEu\Rivers\Exceptions\NotEvaluatableException;
use LsvEu\Rivers\Models\River;
use LsvEu\Rivers\Models\RiverRun;
use ReflectionMethod;

class EvaluateRiverElement
{
    protected array $injections;

    public function __construct(protected River|RiverRun $river, protected array $additionalData = [])
    {
        $raft = $this->river instanceof River ? $additionalData['raft'] ?? null : null;
        $this->injections = GetRiverRunInjections::run($river, raft: $raft);
    }

    public static function run(River|RiverRun $river, RiverElement $element, string $method = 'evaluate', array $additionalData = []): mixed
    {
        return (new static($river, $additionalData))->handle($element, $method);
    }

    public function handle(RiverElement $element, string $method = 'evaluate'): mixed
    {
        if (! method_exists($element, $method)) {
            throw new (NotEvaluatableException::class);
        }

        $dependencies = [];

        foreach ((new ReflectionMethod($element::class, $method))->getParameters() as $parameter) {
            $dependencies[] = $this->injections[$parameter->name]() ?? $this->additionalData[$parameter->name] ?? null;
        }

        return $element->$method(...$dependencies);
    }
}
