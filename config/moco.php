<?php

return [
    // Auth und Basis
    'subdomain' => env('MOCO_SUBDOMAIN', 'enodiasoftware'),
    'api_key'   => env('MOCO_API_TOKEN', '911c1d58-4b5a-4b5a-4b5a-4b5a4b5a4b5a'),
    'base_url'  => 'https://enodiasoftware.mocoapp.com/api/v1',

    // HTTP/Cache
    'timeout'   => (int) env('MOCO_TIMEOUT', 10),
    'cache_ttl' => (int) env('MOCO_CACHE_TTL', 120),
    'retry_attempts' => 3,

    // Endpunkte
    'endpoints' => [
        'session' => '/users/me',
        'projects' => '/projects',
        'users' => '/users',
        'activities' => '/activities',
        'companies' => '/companies',
        'contacts' => '/contacts',
        'deals' => '/deals',
        'invoices' => '/invoices',
        'offers' => '/offers',
        'planning_entries' => '/planning_entries',
        'profile' => '/users/me',
    ],

    // Sync/Listen
    'page_size' => 100,
    'sync_since_default_hours' => 24,
];
