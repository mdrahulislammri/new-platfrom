<?php
declare(strict_types=1);

return [
    'site_name' => 'TechX License Marketplace',
    'base_url' => 'http://localhost',
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=techx_store;charset=utf8mb4',
        'user' => 'techx_user',
        'pass' => 'change_this_password',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],
    'license_secret' => 'change_this_license_secret',
    'admin' => [
        'username' => 'admin',
        'password_hash' => password_hash('change_this_admin_password', PASSWORD_DEFAULT),
    ],
    'email' => [
        'from' => 'no-reply@techx.top',
    ],
];
