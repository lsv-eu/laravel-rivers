<?php

namespace Workbench\App\Rivers\Rafts;

use LsvEu\Rivers\Contracts\ModelRaft;

class UserRaft extends ModelRaft
{
    protected array $properties = [
        'name' => 'string',
        'email' => 'email',
    ];
}
