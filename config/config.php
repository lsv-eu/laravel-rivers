<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'database' => [
        'prefix' => 'river_',
    ],

    'queue' => env('RIVERS_QUEUE', 'default'),

    'job_class' => \LsvEu\Rivers\Jobs\ProcessRiverRun::class,

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
