<?php

namespace Workbench\App\Foundation;

class Application extends \Illuminate\Foundation\Application
{
    /**
     * Get the application namespace.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        return 'Workbench\\App\\';
    }
}
