<?php
/**
 * Página de Cadastro de Usuário
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$erros = [];
$nome = '';
$usuario = '';
$email = '';
$dataNascimento = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dataNascimento = trim($_POST['data_nascimento'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    // Validações
    if (empty($nome) || mb_strlen($nome) < 3) {
        $erros[] = 'Por favor, informe seu nome completo (mínimo 3 letras).';
    }

    if (empty($usuario) || !preg_match('/^[a-zA-Z0-9_\.]{3,30}$/', $usuario)) {
        $erros[] = 'Nome de usuário inválido. Utilize de 3 a 30 caracteres (letras, números e sublinhado).';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Por favor, informe um endereço de e-mail válido.';
    }

    if (empty($dataNascimento)) {
        $erros[] = 'Informe sua data de nascimento.';
    } else {
        $idade = calcularIdade($dataNascimento);
        if ($idade <= 0 || $idade > 120) {
            $erros[] = 'Data de nascimento inválida.';
        }
    }

    if (empty($senha) || strlen($senha) < 6) {
        $erros[] = 'A senha deve conter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erros[] = 'As senhas digitadas não coincidem.';
    }

    // Se passou na validação básica, checa unicidade no banco de dados
    if (empty($erros)) {
        $db = getDb();

        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erros[] = 'Este e-mail já está cadastrado. Deseja fazer login?';
        }

        $stmt = $db->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        if ($stmt->fetch()) {
            $erros[] = 'Este nome de usuário já está em uso por outro membro. Escolha outro.';
        }
    }

    // Se tudo correto, insere no banco
    if (empty($erros)) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $idade = calcularIdade($dataNascimento);
        $avatarPadrao = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80';

        $stmt = $db->prepare("
            INSERT INTO usuarios (nome, usuario, email, senha, data_nascimento, idade, avatar)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nome, $usuario, $email, $senhaHash, $dataNascimento, $idade, $avatarPadrao]);
        $novoId = $db->lastInsertId();

        $novoUsuario = [
            'id' => $novoId,
            'nome' => $nome,
            'usuario' => $usuario,
            'avatar' => $avatarPadrao
        ];

        loginUser($novoUsuario);
        setFlash('success', 'Cadastro realizado com sucesso! Seja muito bem-vindo(a) ao Sabor em Casa.');

        $redirect = $_SESSION['redirect_after_login'] ?? (BASE_URL . '/index.php');
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    }
}

$pageTitle = 'Criar sua Conta - Sabor em Casa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper" style="max-width: 560px;">
  <div class="auth-card">
    <div class="auth-header">
      <div class="brand-icon" style="margin: 0 auto 16px; width:52px; height:52px; font-size:1.5rem;">
        <i class="fa-solid fa-user-plus"></i>
      </div>
      <h1 class="auth-title">Junte-se ao Sabor em Casa</h1>
      <p class="auth-subtitle">Cadastre-se para compartilhar suas receitas e fazer parte da nossa comunidade</p>
    </div>

    <?php if (!empty($erros)): ?>
      <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start;">
        <div style="font-weight: 700; margin-bottom: 4px;">Atenção aos seguintes campos:</div>
        <ul style="padding-left: 20px; font-size: 0.9rem;">
          <?php foreach ($erros as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
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
      Cadastrar utilizando a conta do Google
    </button>

    <div class="auth-divider">
      <span>ou preencha os dados abaixo</span>
    </div>

    <form action="<?= BASE_URL ?>/cadastro.php" method="POST">
      <div class="form-group">
        <label for="nome" class="form-label">Nome Completo</label>
        <input type="text" id="nome" name="nome" class="form-control" placeholder="ex: Carolina Silva" value="<?= htmlspecialchars($nome) ?>" required>
      </div>

      <div class="publish-grid-2">
        <div class="form-group">
          <label for="usuario" class="form-label">Nome de Usuário (Username)</label>
          <input type="text" id="usuario" name="usuario" class="form-control" placeholder="ex: carol.silva" value="<?= htmlspecialchars($usuario) ?>" required>
          <span class="form-hint">Sem espaços ou acentos.</span>
        </div>

        <div class="form-group">
          <label for="data_nascimento" class="form-label">Data de Nascimento</label>
          <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" value="<?= htmlspecialchars($dataNascimento) ?>" required max="<?= date('Y-m-d') ?>">
          <span id="idade_preview" class="form-hint" style="font-weight: 600; display: none;"></span>
        </div>
      </div>

      <div class="form-group">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="seuemail@exemplo.com" value="<?= htmlspecialchars($email) ?>" required>
      </div>

      <div class="publish-grid-2">
        <div class="form-group">
          <label for="senha" class="form-label">Crie uma Senha</label>
          <input type="password" id="senha" name="senha" class="form-control" placeholder="Mínimo 6 dígitos" required>
        </div>

        <div class="form-group">
          <label for="confirmar_senha" class="form-label">Confirme sua Senha</label>
          <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" placeholder="Repita a senha" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 14px;">
        <i class="fa-solid fa-user-check"></i> Concluir Cadastro
      </button>
    </form>

    <div class="auth-footer">
      Já tem uma conta cadastrada? <a href="<?= BASE_URL ?>/login.php">Fazer login</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
