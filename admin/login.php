<?php
declare(strict_types=1);

require __DIR__ . '/../includes/functions.php';

$config = require __DIR__ . '/../includes/config.php';

start_session();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === $config['admin']['username'] && password_verify($password, $config['admin']['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    }

    $error = 'Invalid admin credentials.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200">
        <h1 class="text-2xl font-semibold mb-2">Admin Login</h1>
        <p class="text-sm text-slate-600 mb-6">Secure access for marketplace administrators.</p>

        <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-lg bg-rose-100 text-rose-700 text-sm">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1" for="username">Username</label>
                <input class="w-full border border-slate-300 rounded-lg px-3 py-2" type="text" name="username" id="username" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="password">Password</label>
                <input class="w-full border border-slate-300 rounded-lg px-3 py-2" type="password" name="password" id="password" required>
            </div>
            <button class="w-full bg-indigo-600 text-white py-2 rounded-lg" type="submit">Sign in</button>
        </form>
    </div>
</div>
</body>
</html>
