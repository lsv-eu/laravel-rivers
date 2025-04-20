<?php

use Tests\Traits\UsesConfig;

use function LsvEu\Rivers\rivers;

uses(UsesConfig::class);

test('reads rivers config', function () {
    expect(rivers());
});
