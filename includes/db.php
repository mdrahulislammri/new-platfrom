<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        $config['db']['dsn'],
        $config['db']['user'],
        $config['db']['pass'],
        $config['db']['options']
    );
} catch (PDOException $exception) {
    http_response_code(500);
    echo 'Database connection failed.';
    exit;
}
