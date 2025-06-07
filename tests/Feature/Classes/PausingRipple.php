<?php

namespace Tests\Feature\Classes;

use LsvEu\Rivers\Cartography\Ripple;
use LsvEu\Rivers\Contracts\Raft;
use LsvEu\Rivers\Models\River;

class PausingRipple extends Ripple
{
    public function process(Raft $raft): void
    {
        River::whereStatus('active')->update(['status' => 'paused']);
    }
}
