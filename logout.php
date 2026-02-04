<?php
declare(strict_types=1);

require __DIR__ . '/includes/functions.php';

start_session();
session_destroy();

header('Location: index.php');
exit;
