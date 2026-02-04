<?php
declare(strict_types=1);

require __DIR__ . '/../includes/functions.php';

start_session();

unset($_SESSION['admin_logged_in']);

header('Location: login.php');
exit;
