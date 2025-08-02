<?php

return [
    'observers' => [
        \Workbench\App\Models\User::class => [
            'name' => 'User',
            'events' => ['created', 'updated', 'saved', 'deleted'],
        ],
    ],
];
