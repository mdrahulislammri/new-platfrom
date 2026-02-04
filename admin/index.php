<?php
declare(strict_types=1);

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';

require_admin_login();

$action = $_POST['action'] ?? '';
$feedback = '';
$error = '';

if ($action === 'save_module') {
    $moduleId = (int) ($_POST['module_id'] ?? 0);
    $moduleName = sanitize_string($_POST['module_name'] ?? '');
    $displayName = sanitize_string($_POST['display_name'] ?? '');
    $description = trim((string) ($_POST['description'] ?? ''));
    $version = sanitize_string($_POST['version'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $status = ($_POST['status'] ?? 'active') === 'active' ? 'active' : 'disabled';

    if ($moduleName === '' || $displayName === '' || $version === '') {
        $error = 'Module name, display name, and version are required.';
    } else {
        $filePath = null;
        if (!empty($_FILES['module_file']['name'])) {
            $uploadName = basename($_FILES['module_file']['name']);
            $target = __DIR__ . '/../assets/' . $uploadName;
            if (move_uploaded_file($_FILES['module_file']['tmp_name'], $target)) {
                $filePath = $target;
            }
        }

        if ($moduleId > 0) {
            $sql = 'UPDATE modules SET module_name = :module_name, display_name = :display_name, description = :description, version = :version, price = :price, status = :status';
            $params = [
                'module_name' => $moduleName,
                'display_name' => $displayName,
                'description' => $description,
                'version' => $version,
                'price' => $price,
                'status' => $status,
                'id' => $moduleId,
            ];

            if ($filePath) {
                $sql .= ', file_path = :file_path';
                $params['file_path'] = $filePath;
            }

            $sql .= ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $feedback = 'Module updated successfully.';
        } else {
            if (!$filePath) {
                $error = 'Module file is required for new modules.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO modules (module_name, display_name, description, version, price, file_path, status, created_at) VALUES (:module_name, :display_name, :description, :version, :price, :file_path, :status, NOW())');
                $stmt->execute([
                    'module_name' => $moduleName,
                    'display_name' => $displayName,
                    'description' => $description,
                    'version' => $version,
                    'price' => $price,
                    'file_path' => $filePath,
                    'status' => $status,
                ]);
                $feedback = 'Module added successfully.';
            }
        }
    }
}

if (isset($_GET['toggle'])) {
    $moduleId = (int) $_GET['toggle'];
    $stmt = $pdo->prepare('UPDATE modules SET status = IF(status = "active", "disabled", "active") WHERE id = :id');
    $stmt->execute(['id' => $moduleId]);
    $feedback = 'Module status updated.';
}

$editModule = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM modules WHERE id = :id');
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editModule = $stmt->fetch();
}

$modules = $pdo->query('SELECT * FROM modules ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
            <p class="text-sm text-slate-600">Manage marketplace modules and availability.</p>
        </div>
        <div class="space-x-4 text-sm">
            <a class="text-indigo-600" href="licenses.php">Manage Licenses</a>
            <a class="text-indigo-600" href="logout.php">Logout</a>
        </div>
    </div>

    <?php if ($feedback): ?>
        <div class="mb-4 p-3 rounded-lg bg-emerald-100 text-emerald-800 text-sm">
            <?= htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mb-4 p-3 rounded-lg bg-rose-100 text-rose-700 text-sm">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white p-6 rounded-xl border border-slate-200">
            <h2 class="text-lg font-semibold mb-4"><?= $editModule ? 'Edit Module' : 'Add Module' ?></h2>
            <form method="post" enctype="multipart/form-data" class="space-y-3">
                <input type="hidden" name="action" value="save_module">
                <input type="hidden" name="module_id" value="<?= (int) ($editModule['id'] ?? 0) ?>">
                <div>
                    <label class="block text-sm font-medium mb-1" for="module_name">Module Name (slug)</label>
                    <input class="w-full border border-slate-300 rounded-lg px-3 py-2" type="text" name="module_name" id="module_name" value="<?= htmlspecialchars($editModule['module_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="display_name">Display Name</label>
                    <input class="w-full border border-slate-300 rounded-lg px-3 py-2" type="text" name="display_name" id="display_name" value="<?= htmlspecialchars($editModule['display_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="description">Description</label>
                    <textarea class="w-full border border-slate-300 rounded-lg px-3 py-2" name="description" id="description" rows="4"><?= htmlspecialchars($editModule['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="version">Version</label>
                    <input class="w-full border border-slate-300 rounded-lg px-3 py-2" type="text" name="version" id="version" value="<?= htmlspecialchars($editModule['version'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="price">Price</label>
                    <input class="w-full border border-slate-300 rounded-lg px-3 py-2" type="number" step="0.01" name="price" id="price" value="<?= htmlspecialchars((string) ($editModule['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="status">Status</label>
                    <select class="w-full border border-slate-300 rounded-lg px-3 py-2" name="status" id="status">
                        <option value="active" <?= ($editModule['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="disabled" <?= ($editModule['status'] ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="module_file">Module File</label>
                    <input class="w-full text-sm" type="file" name="module_file" id="module_file">
                </div>
                <button class="w-full bg-indigo-600 text-white py-2 rounded-lg" type="submit">Save Module</button>
            </form>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-xl border border-slate-200">
                <h2 class="text-lg font-semibold mb-4">Existing Modules</h2>
                <div class="space-y-4">
                    <?php foreach ($modules as $module): ?>
                        <div class="border border-slate-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h3 class="font-semibold"><?= htmlspecialchars($module['display_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="text-sm text-slate-500"><?= htmlspecialchars($module['module_name'], ENT_QUOTES, 'UTF-8') ?> · Version <?= htmlspecialchars($module['version'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="text-sm text-slate-600 mt-1">$<?= number_format((float) $module['price'], 2) ?> · <?= htmlspecialchars($module['status'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <a class="text-indigo-600" href="index.php?edit=<?= (int) $module['id'] ?>">Edit</a>
                                <a class="text-indigo-600" href="index.php?toggle=<?= (int) $module['id'] ?>">Toggle</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($modules)): ?>
                        <p class="text-sm text-slate-500">No modules created yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
