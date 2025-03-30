<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Excluded Paths
    |--------------------------------------------------------------------------
    |
    | List of paths that should be excluded from transaction enforcement.
    | Wildcards (*) are supported.
    |
    */
    'excluded_paths' => [
        'api/health-check',
        '_debugbar/*',
        'telescope/*',
        'horizon/*',
    ],
];
