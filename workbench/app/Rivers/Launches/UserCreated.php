<?php

namespace Workbench\App\Rivers\Launches;

use LsvEu\Rivers\Cartography\Launches\ModelCreated;
use Workbench\App\Models\User;
use Workbench\App\Rivers\Rafts\UserRaft;

class UserCreated extends ModelCreated
{
    public function __construct(array $attributes = [])
    {
        $attributes['class'] = User::class;
        $attributes['raftClass'] = UserRaft::class;

        parent::__construct($attributes);
    }
}
