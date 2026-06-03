<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'queue' => env('RIVERS_QUEUE', 'default'),

    'job_class' => LsvEu\Rivers\Jobs\ProcessRiverRun::class,

    'timed_bridges' => [
        'enabled' => env('RIVERS_TIMED_BRIDGES_ENABLED', true),

        'command' => env('RIVERS_TIMED_BRIDGES_COMMAND', 'rivers:check-timed-bridges'),

        /**
         * The cron expression for timed bridges.
         * If seconds is a positive integer, this will be ignored.
         * Set to null and seconds to 0 to disable scheduling so you can create your own.
         */
        'cron' => env('RIVERS_TIMED_BRIDGES_CRON', '* * * * *'),

        /**
         * The number of seconds between checks for timed bridges.
         * If set to 0, the cron will be used.
         * This number needs to evenly divide 60. (1, 2, 3, 4, 5, 6, 10, 12, 15, 20, 30)
         * Set to 0 and cron to null to disable scheduling so you can create your own.
         */
        'seconds' => env('RIVERS_TIMED_BRIDGES_SUBMINUTE', 0),
    ],

    /*
     * List of classes that are observed.
     */
    'observers' => [
        // \App\Models\User::class => [
        //
        //    /**
        //     * The display name of the model
        //     */
        //    'name' => 'User',
        //
        //    /**
        //     * The events available to the user
        //     */
        //    'events' => ['created', 'updated', 'saved', 'deleted'],
        //
        //    /**
        //     * The fields that can be selected for conditions
        //     *
        //     * A simple string will just be run through ucfirst()
        //     * Default field type: empty|string
        //     *
        //     * Field types:
        //     *   - empty: Adds options "empty" and "not empty"
        //     *   - string: Adds options with text field
        //     *      "equals", "doesn't equal"
        //     *      "contains", "doesn't contain"
        //     *      "starts with", "doesn't start with"
        //     *      "ends with", "doesn't end with"
        //     *   - date: Adds options with date field(s)
        //     *      "equals", "doesn't equal", "before", "after", "between"
        //     *   - datetime: Adds options with datetime field(s)
        //     *      "equals", "doesn't equal", "before", "after", "between"
        //     */
        //    'fields' => [
        //        'name',
        //        'email' => 'E-mail',
        //        'email_verified_at' => [
        //            'label' => 'Email verified at',
        //            'type' => 'empty|date|datetime'
        //        ],
        //    ],
        // ],
    ],
];
