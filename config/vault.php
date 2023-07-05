<?php

return [
    'uri' => env('VAULT_URI'),
    'auth_strategy' => env('VAULT_AUTH_STRATEGY', 'token'),
    'token' => env('VAULT_TOKEN'),
    'use_namespace' => env('VAULT_USE_NAMESPACE'),
    'namespace' => env('VAULT_DEFAULT_NAMESPACE'),
    'auth_strategies' => [
        'token' => [
            'token' => env('VAULT_AUTH_TOKEN')
        ],
        'userpass' => [
            'username' => env('VAULT_AUTH_USERNAME'),
            'password' => env('VAULT_AUTH_PASS')
        ],
        'approle' => [
            'id' => env('VAULT_AUTH_APPROLE_ID'),
            'secret' => env('VAULT_AUTH_APPROLE_SECRET')
        ]
    ]
];