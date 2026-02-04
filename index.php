<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';

start_session();

$modules = $pdo->query('SELECT * FROM modules WHERE status = "active" ORDER BY created_at DESC')->fetchAll();
$userEmail = $_SESSION['user_email'] ?? null;
$message = flash_message('success');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechX License Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
<header class="bg-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 py-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">TechX License Marketplace</h1>
        <nav class="space-x-4 text-sm">
            <?php if ($userEmail): ?>
                <span class="text-slate-600">Signed in as <?= htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') ?></span>
                <a class="text-indigo-600 font-medium" href="logout.php">Logout</a>
            <?php else: ?>
                <a class="text-indigo-600 font-medium" href="login.php">Login</a>
                <a class="text-indigo-600 font-medium" href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="max-w-6xl mx-auto px-4 py-10">
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg bg-emerald-100 text-emerald-800">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($modules as $module): ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col">
                <h2 class="text-xl font-semibold mb-2">
                    <?= htmlspecialchars($module['display_name'], ENT_QUOTES, 'UTF-8') ?>
                </h2>
                <p class="text-sm text-slate-600 flex-1">
                    <?= nl2br(htmlspecialchars($module['description'], ENT_QUOTES, 'UTF-8')) ?>
                </p>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-lg font-semibold">$<?= number_format((float) $module['price'], 2) ?></span>
                    <a class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm" href="purchase.php?module_id=<?= (int) $module['id'] ?>">Buy</a>
                </div>
                <div class="mt-3 text-xs text-slate-500">Version <?= htmlspecialchars($module['version'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endforeach; ?>
    </section>
    <?php if (empty($modules)): ?>
        <div class="mt-8 p-6 bg-white rounded-xl border border-slate-200 text-center text-slate-500">
            No active modules available yet. Check back soon.
        </div>
    <?php endif; ?>
</main>
</body>
</html>
