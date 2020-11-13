<?php

return [

    /*
    |--------------------------------------------------------------
    | Literally
    |--------------------------------------------------------------
    |
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------
    | History table name
    |--------------------------------------------------------------
    |
    |
    */
    'changes_table' => 'model_changes',

    /*
    |--------------------------------------------------------------
    | Events whitelist
    |--------------------------------------------------------------
    |
    | Events in this array will be recorded.
    | Available events are: created, updated, deleting, restored
    |
    */
    'events_whitelist' => [
        'created', 'updated', 'deleting', 'restored',
    ],

    /*
    |--------------------------------------------------------------
    | Attributes blacklist
    |--------------------------------------------------------------
    | 
    | Please add the whole class names. Example: \App\User:class
    | For each model, attributes in its respect array will NOT be recorded into meta when performing update operation.
    |
    */
    'attributes_blacklist' => [
        // \App\User::class => [
        //     'password'
        // ],
    ],

    /*
    |--------------------------------------------------------------
    | User blacklist
    |--------------------------------------------------------------
    |
    | Operations performed by users in this array will NOT be recorded.
    | Please add the whole class names. Example: \App\User:class
    | Use 'nobody' to bypass unauthenticated operations
    |
    */
    'user_blacklist' => [
        
    ],

    /*
    |--------------------------------------------------------------
    | Enabled when application running in console
    |--------------------------------------------------------------
    |
    | When application is running in console(include seeding)
    |
    */
    'console_enabled' => false,

    /*
    |--------------------------------------------------------------
    | Enabled when application running in unit tests
    |--------------------------------------------------------------
    |
    | When application is running unit tests
    |
    */
    'test_enabled' => false,

    /*
    |--------------------------------------------------------------
    | Enviroments blacklist
    |--------------------------------------------------------------
    |
    | When application's environment is in the list, tracker will be disabled
    |
    */
    'env_blacklist' => [
        
    ],

    /*
    |--------------------------------------------------------------
    | Record timestamps
    |--------------------------------------------------------------
    |
    | Ignore the created_at and updated_at timestamps.
    |
    */
    'record_timestamps' => false,
];