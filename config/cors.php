<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    // For production: Add your staff and customer domains here
    // Example: 'https://staff.example.com', 'https://customer.example.com'
    'allowed_origins' => [
        // Development
        'http://127.0.0.1:8000',
        'http://localhost:8000',
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        'http://127.0.0.1',
        'http://localhost',
        // Production - Update these with your actual domains
        // 'https://staff.yourdomain.com',
        // 'https://customer.yourdomain.com',
        // 'https://api.yourdomain.com',
    ],

    'allowed_origins_patterns' => [
        // Optional: Allow any subdomain in development
        // '#^https?://localhost(:\d+)?$#',
        // For production, keep specific origins above for security
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    // Critical for JWT cross-domain: allows credentials in requests
    'supports_credentials' => true,
];
