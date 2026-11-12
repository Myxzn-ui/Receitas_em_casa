<?php
/**
 * Página de Login
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Se já estiver logado, redireciona
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($login) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $db = getDb();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? OR usuario = ?");
        $stmt->execute([$login, $login]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            loginUser($usuario);
            setFlash('success', 'Bem-vindo(a) de volta, ' . htmlspecialchars($usuario['nome']) . '!');
            
            $redirect = $_SESSION['redirect_after_login'] ?? (BASE_URL . '/index.php');
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $erro = 'E-mail/usuário ou senha incorretos.';
        }
    }
}

$pageTitle = 'Entrar na sua Conta - Sabor em Casa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <div class="brand-icon" style="margin: 0 auto 16px; width:52px; height:52px; font-size:1.5rem;">
        <i class="fa-solid fa-utensils"></i>
      </div>
      <h1 class="auth-title">Acesse sua Conta</h1>
      <p class="auth-subtitle">Entre para salvar seus pratos favoritos e publicar suas receitas</p>
    </div>

    <?php if (!empty($erro)): ?>
      <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($erro) ?></span>
      </div>
    <?php endif; ?>

    <!-- Botão Google -->
    <button type="button" class="btn btn-google" style="width: 100%;" onclick="abrirModalGoogle()">
      <svg width="18" height="18" viewBox="0 0 24 24">
        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.15z"/>
        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.26v3.15C3.25 21.36 7.33 24 12 24z"/>
        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.26C.46 8.16 0 9.94 0 12s.46 3.84 1.26 5.42l4.02-3.15z"/>
        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.25 2.64 1.26 6.58l4.02 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
      </svg>
      Entrar com a conta do Google
    </button>

    <div class="auth-divider">
      <span>ou com e-mail e senha</span>
    </div>

    <form action="<?= BASE_URL ?>/login.php" method="POST">
      <div class="form-group">
        <label for="login" class="form-label">E-mail ou Nome de Usuário</label>
        <input type="text" id="login" name="login" class="form-control" placeholder="ex: ana@email.com ou chefana" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required autofocus>
      </div>

      <div class="form-group">
        <label for="senha" class="form-label">Senha</label>
        <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha cadastrada" required>
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px;">
        <i class="fa-solid fa-arrow-right-to-bracket"></i> Entrar
      </button>
    </form>

    <div class="auth-footer">
      Ainda não tem conta? <a href="<?= BASE_URL ?>/cadastro.php">Cadastre-se gratuitamente</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
