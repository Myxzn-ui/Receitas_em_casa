<?php
/**
 * Logout
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

logoutUser();
setFlash('info', 'Você saiu da sua conta. Até breve!');
header('Location: ' . BASE_URL . '/index.php');
exit;
