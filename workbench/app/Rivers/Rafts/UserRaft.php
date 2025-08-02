<?php

namespace Workbench\App\Rivers\Rafts;

use LsvEu\Rivers\Contracts\ModelRaft;
use Workbench\App\Models\User;

class UserRaft extends ModelRaft
{
    protected static string $modelClass = User::class;

    protected array $properties = [
        'name' => 'string',
        'email' => 'email',
    ];
}
