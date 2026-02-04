<?php
declare(strict_types=1);

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';

require_admin_login();

$feedback = '';
if (isset($_GET['revoke'])) {
    $licenseId = (int) $_GET['revoke'];
    $stmt = $pdo->prepare('UPDATE licenses SET status = "revoked" WHERE id = :id');
    $stmt->execute(['id' => $licenseId]);
    $feedback = 'License revoked.';
}

$licenses = $pdo->query('SELECT l.*, u.email FROM licenses l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Licenses</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Licenses</h1>
            <p class="text-sm text-slate-600">View and revoke issued licenses.</p>
        </div>
        <div class="space-x-4 text-sm">
            <a class="text-indigo-600" href="index.php">Back to dashboard</a>
        </div>
    </div>

    <?php if ($feedback): ?>
        <div class="mb-4 p-3 rounded-lg bg-emerald-100 text-emerald-800 text-sm">
            <?= htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="pb-3">User</th>
                        <th class="pb-3">Module</th>
                        <th class="pb-3">License</th>
                        <th class="pb-3">Domain</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php foreach ($licenses as $license): ?>
                        <tr>
                            <td class="py-3 pr-4"><?= htmlspecialchars($license['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-3 pr-4"><?= htmlspecialchars($license['module_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-3 pr-4 font-mono text-xs"><?= htmlspecialchars($license['license_key'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-3 pr-4"><?= htmlspecialchars($license['domain'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-3 pr-4"><?= htmlspecialchars($license['status'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="py-3">
                                <?php if ($license['status'] === 'active'): ?>
                                    <a class="text-indigo-600" href="licenses.php?revoke=<?= (int) $license['id'] ?>">Revoke</a>
                                <?php else: ?>
                                    <span class="text-slate-400">Revoked</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($licenses)): ?>
                <p class="text-sm text-slate-500 mt-4">No licenses issued yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
