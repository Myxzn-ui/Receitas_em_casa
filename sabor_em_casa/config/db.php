<?php
/**
 * Configuração de Banco de Dados e Conexão PDO
 * Sabor em Casa
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sabor_em_casa');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Detecção dinâmica de Base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = rtrim($scriptName, '/');

// Se estiver no diretório sabor_em_casa
if (strpos($basePath, '/sabor_em_casa') === false && basename(dirname(__DIR__)) === 'sabor_em_casa') {
    $baseUrl = $protocol . $host . '/sabor_em_casa';
} else {
    $baseUrl = $protocol . $host . $basePath;
    // Corrige subdiretórios se chamado de dentro de includes ou api
    $baseUrl = preg_replace('/(\/includes|\/config|\/api)$/', '', $baseUrl);
}
define('BASE_URL', rtrim($baseUrl, '/'));

function getDb(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}
