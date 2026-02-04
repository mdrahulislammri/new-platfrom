<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';

start_session();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = (string) ($_POST['password'] ?? '');

    if (!$email || $password === '') {
        $error = 'Please provide your email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid credentials provided.';
        } else {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_email'] = $email;
            flash_message('success', 'Welcome back!');
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
<div class="max-w-lg mx-auto px-4 py-12">
    <a class="text-sm text-indigo-600" href="index.php">&larr; Back to marketplace</a>
    <div class="bg-white mt-6 p-8 rounded-xl shadow-sm border border-slate-200">
        <h1 class="text-2xl font-semibold mb-2">Sign in to TechX</h1>
        <p class="text-sm text-slate-600 mb-6">Access your licenses and purchases.</p>

        <?php if ($error): ?>
            <div class="mb-4 p-3 rounded-lg bg-rose-100 text-rose-700 text-sm">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1" for="email">Email</label>
                <input class="w-full border border-slate-300 rounded-lg px-3 py-2" type="email" name="email" id="email" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1" for="password">Password</label>
                <input class="w-full border border-slate-300 rounded-lg px-3 py-2" type="password" name="password" id="password" required>
            </div>
            <button class="w-full bg-indigo-600 text-white py-2 rounded-lg" type="submit">Sign in</button>
        </form>
        <p class="mt-4 text-sm text-slate-600">New here? <a class="text-indigo-600" href="register.php">Create an account</a></p>
    </div>
</div>
</body>
</html>
