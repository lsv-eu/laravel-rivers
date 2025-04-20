<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Config;

use function Orchestra\Testbench\workbench_path;

trait UsesConfig
{
    public function setUpUsesConfig(): void
    {
        Config::set('rivers', array_merge(Config::get('rivers', []), include (workbench_path('config/rivers.php'))));
    }
}
