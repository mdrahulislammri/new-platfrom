<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';

$license = strtoupper(trim((string) ($_REQUEST['license'] ?? '')));
$moduleName = sanitize_string($_REQUEST['module_name'] ?? '');
$domain = sanitize_string($_REQUEST['domain'] ?? '');

$logEntry = sprintf(
    "[%s] License: %s | Module: %s | Domain: %s | Result: %s\n",
    date('Y-m-d H:i:s'),
    $license ?: 'N/A',
    $moduleName ?: 'N/A',
    $domain ?: 'N/A',
    'INVALID'
);

$result = 'INVALID';

if ($license && $moduleName && $domain && is_valid_license_format($license)) {
    $stmt = $pdo->prepare('SELECT * FROM licenses WHERE license_key = :license AND module_name = :module_name');
    $stmt->execute(['license' => $license, 'module_name' => $moduleName]);
    $licenseRow = $stmt->fetch();

    if ($licenseRow && $licenseRow['status'] === 'active' && $licenseRow['domain'] === $domain) {
        $result = 'VALID';
    }
}

$logEntry = sprintf(
    "[%s] License: %s | Module: %s | Domain: %s | Result: %s\n",
    date('Y-m-d H:i:s'),
    $license ?: 'N/A',
    $moduleName ?: 'N/A',
    $domain ?: 'N/A',
    $result
);

file_put_contents(__DIR__ . '/log/verify.log', $logEntry, FILE_APPEND);

echo $result;
