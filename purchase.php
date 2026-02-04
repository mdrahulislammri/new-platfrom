<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/email.php';

$config = require __DIR__ . '/includes/config.php';

require_user_login();

$userId = (int) $_SESSION['user_id'];
$userEmail = (string) $_SESSION['user_email'];

$moduleId = (int) ($_GET['module_id'] ?? $_POST['module_id'] ?? 0);
$moduleStmt = $pdo->prepare('SELECT * FROM modules WHERE id = :id AND status = "active"');
$moduleStmt->execute(['id' => $moduleId]);
$module = $moduleStmt->fetch();

if (!$module) {
    flash_message('success', 'Module not found or unavailable.');
    header('Location: index.php');
    exit;
}

$error = '';
$licenseKey = '';

if (isset($_GET['download'], $_GET['license_id'])) {
    $licenseId = (int) $_GET['license_id'];
    $licenseStmt = $pdo->prepare('SELECT l.*, m.file_path, m.display_name FROM licenses l JOIN modules m ON l.module_name = m.module_name WHERE l.id = :id AND l.user_id = :user_id');
    $licenseStmt->execute(['id' => $licenseId, 'user_id' => $userId]);
    $license = $licenseStmt->fetch();

    if ($license && $license['status'] === 'active' && is_file($license['file_path'])) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($license['file_path']) . '"');
        readfile($license['file_path']);
        exit;
    }

    $error = 'Unable to download this module. Please contact support.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['download'])) {
    $domain = sanitize_string($_POST['domain'] ?? '');

    if ($domain === '') {
        $error = 'Please provide a valid domain.';
    } else {
        $licenseKey = generate_license($userEmail, $module['module_name'], $domain, $config['license_secret']);

        $insert = $pdo->prepare('INSERT INTO licenses (user_id, module_name, license_key, domain, status, created_at) VALUES (:user_id, :module_name, :license_key, :domain, "active", NOW())');
        $insert->execute([
            'user_id' => $userId,
            'module_name' => $module['module_name'],
            'license_key' => $licenseKey,
            'domain' => $domain,
        ]);

        send_license_email($userEmail, $module['display_name'], $licenseKey, $domain, $config);

        $licenseId = (int) $pdo->lastInsertId();
        header('Location: purchase.php?module_id=' . $moduleId . '&license_id=' . $licenseId);
        exit;
    }
}

$licenseId = (int) ($_GET['license_id'] ?? 0);
$existingLicense = null;
if ($licenseId > 0) {
    $licenseStmt = $pdo->prepare('SELECT * FROM licenses WHERE id = :id AND user_id = :user_id');
    $licenseStmt->execute(['id' => $licenseId, 'user_id' => $userId]);
    $existingLicense = $licenseStmt->fetch();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase <?= htmlspecialchars($module['display_name'], ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
<div class="max-w-2xl mx-auto px-4 py-12">
    <a class="text-sm text-indigo-600" href="index.php">&larr; Back to marketplace</a>

    <div class="bg-white mt-6 p-8 rounded-xl shadow-sm border border-slate-200">
        <h1 class="text-2xl font-semibold mb-2">Purchase <?= htmlspecialchars($module['display_name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-sm text-slate-600 mb-6">Complete your license purchase and access your download instantly.</p>

        <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-lg bg-rose-100 text-rose-700 text-sm">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($existingLicense): ?>
            <div class="mb-6 p-4 rounded-lg bg-emerald-100 text-emerald-800 text-sm">
                <p class="font-semibold">License generated successfully.</p>
                <p class="mt-2">License Key: <span class="font-mono"><?= htmlspecialchars($existingLicense['license_key'], ENT_QUOTES, 'UTF-8') ?></span></p>
                <p class="mt-1">Domain: <?= htmlspecialchars($existingLicense['domain'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm" href="purchase.php?module_id=<?= $moduleId ?>&download=1&license_id=<?= (int) $existingLicense['id'] ?>">Download module</a>
        <?php else: ?>
            <form method="post" class="space-y-4">
                <input type="hidden" name="module_id" value="<?= $moduleId ?>">
                <div>
                    <label class="block text-sm font-medium mb-1" for="domain">Licensed Domain</label>
                    <input class="w-full border border-slate-300 rounded-lg px-3 py-2" type="text" name="domain" id="domain" placeholder="example.com" required>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-lg font-semibold">$<?= number_format((float) $module['price'], 2) ?></span>
                    <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm" type="submit">Generate License</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
