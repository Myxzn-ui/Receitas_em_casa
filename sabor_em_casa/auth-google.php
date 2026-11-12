<?php
/**
 * Autenticação via Google
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['google_nome'] ?? '');
    $email = trim($_POST['google_email'] ?? '');
    $dataNasc = trim($_POST['google_nascimento'] ?? '2000-01-01');

    if (empty($nome) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('danger', 'Dados inválidos para autenticação com Google.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    $db = getDb();

    // Verifica se já existe um usuário com esse e-mail
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuarioExistente = $stmt->fetch();

    if ($usuarioExistente) {
        loginUser($usuarioExistente);
        setFlash('success', 'Olá, ' . htmlspecialchars($usuarioExistente['nome']) . '! Login via Google efetuado com sucesso.');
        
        $redirect = $_SESSION['redirect_after_login'] ?? (BASE_URL . '/index.php');
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    } else {
        // Criar novo usuário pelo Google
        $baseUser = explode('@', $email)[0];
        $username = slugify($baseUser);

        // Garante que o nome de usuário seja único
        $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmtCheck->execute([$username]);
        if ($stmtCheck->fetch()) {
            $username .= '_' . rand(100, 999);
        }

        $idade = calcularIdade($dataNasc);
        $senhaHash = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);
        $googleId = 'google_' . uniqid();
        $avatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop&q=80';

        $stmtInsert = $db->prepare("
            INSERT INTO usuarios (nome, usuario, email, senha, data_nascimento, idade, google_id, avatar)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtInsert->execute([$nome, $username, $email, $senhaHash, $dataNasc, $idade, $googleId, $avatar]);
        $novoId = $db->lastInsertId();

        $novoUsuario = [
            'id' => $novoId,
            'nome' => $nome,
            'usuario' => $username,
            'avatar' => $avatar
        ];

        loginUser($novoUsuario);
        setFlash('success', 'Conta criada com sucesso pelo Google! Bem-vindo(a) à comunidade Sabor em Casa.');
        
        $redirect = $_SESSION['redirect_after_login'] ?? (BASE_URL . '/index.php');
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    }
} else {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
