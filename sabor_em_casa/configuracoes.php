<?php
/**
 * Configurações da Conta e Perfil
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Exige autenticação
requireAuth();

$user = currentUser();
$db = getDb();

$erros = [];
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? 'perfil';

    // 1. Atualização do Perfil
    if ($acao === 'perfil') {
        $nome = trim($_POST['nome'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dataNascimento = trim($_POST['data_nascimento'] ?? '');
        $biografia = trim($_POST['biografia'] ?? '');
        $avatarUrl = trim($_POST['avatar_url'] ?? '');

        // Validações
        if (empty($nome) || mb_strlen($nome) < 3) {
            $erros[] = 'Por favor, informe seu nome completo (mínimo 3 caracteres).';
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

        // Verifica duplicidade de usuário ou e-mail (excluindo o próprio ID)
        if (empty($erros)) {
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $erros[] = 'Este e-mail já está em uso por outro usuário.';
            }

            $stmt = $db->prepare("SELECT id FROM usuarios WHERE usuario = ? AND id != ?");
            $stmt->execute([$usuario, $user['id']]);
            if ($stmt->fetch()) {
                $erros[] = 'Este nome de usuário já está em uso por outro membro.';
            }
        }

        // Processamento do Avatar
        $avatarFinal = $user['avatar'];

        if (isset($_FILES['avatar_arquivo']) && $_FILES['avatar_arquivo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar_arquivo'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            
            if (in_array($file['type'], $allowedTypes)) {
                $uploadsDir = __DIR__ . '/uploads';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
                $destPath = $uploadsDir . '/' . $fileName;

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $avatarFinal = BASE_URL . '/uploads/' . $fileName;
                }
            } else {
                $erros[] = 'Formato de imagem inválido para o avatar. Use JPG, PNG ou WEBP.';
            }
        } elseif (!empty($avatarUrl)) {
            $avatarFinal = $avatarUrl;
        }

        // Salvar alterações se não houver erros
        if (empty($erros)) {
            $idade = calcularIdade($dataNascimento);
            $stmtUpdate = $db->prepare("
                UPDATE usuarios 
                SET nome = ?, usuario = ?, email = ?, data_nascimento = ?, idade = ?, avatar = ?, biografia = ?
                WHERE id = ?
            ");
            $stmtUpdate->execute([$nome, $usuario, $email, $dataNascimento, $idade, $avatarFinal, $biografia, $user['id']]);

            // Atualiza dados na sessão
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_user'] = $usuario;
            $_SESSION['usuario_avatar'] = $avatarFinal;

            setFlash('success', 'Configurações do perfil atualizadas com sucesso!');
            header('Location: ' . BASE_URL . '/configuracoes.php');
            exit;
        }
    }

    // 2. Alteração de Senha
    elseif ($acao === 'senha') {
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        if (empty($senhaAtual)) {
            $erros[] = 'Informe sua senha atual.';
        } else {
            // Busca a senha atual no banco
            $stmt = $db->prepare("SELECT senha FROM usuarios WHERE id = ?");
            $stmt->execute([$user['id']]);
            $userData = $stmt->fetch();

            if (!$userData || !password_verify($senhaAtual, $userData['senha'])) {
                $erros[] = 'Sua senha atual está incorreta.';
            }
        }

        if (empty($novaSenha) || strlen($novaSenha) < 6) {
            $erros[] = 'A nova senha deve ter no mínimo 6 caracteres.';
        } elseif ($novaSenha !== $confirmarSenha) {
            $erros[] = 'A confirmação de senha não confere com a nova senha.';
        }

        if (empty($erros)) {
            $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmtPass = $db->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmtPass->execute([$novaSenhaHash, $user['id']]);

            setFlash('success', 'Senha alterada com sucesso!');
            header('Location: ' . BASE_URL . '/configuracoes.php');
            exit;
        }
    }

    // 3. Exclusão Permanente de Conta
    elseif ($acao === 'excluir_conta') {
        $confirmacao = $_POST['confirmacao_seguranca'] ?? '';
        $ciente = isset($_POST['ciente_exclusao']);

        if (!$ciente) {
            $erros[] = 'Você precisa marcar a caixa confirmando que está ciente de que a exclusão da conta é definitiva e irreversível.';
        } else {
            $excluiu = excluirContaUsuario($user['id'], $confirmacao, $erros);
            if ($excluiu) {
                logoutUser();
                setFlash('info', 'Sua conta e todos os seus dados foram permanentemente excluídos do banco de dados. Obrigado por ter feito parte do Sabor em Casa.');
                header('Location: ' . BASE_URL . '/index.php');
                exit;
            }
        }
    }
}

$pageTitle = 'Configurações da Conta - Sabor em Casa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 36px; padding-bottom: 70px; max-width: 900px;">

  <!-- Cabeçalho da Página -->
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom: 28px;">
    <div>
      <h1 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: var(--dark); margin:0;">
        <i class="fa-solid fa-gear" style="color: var(--primary); margin-right: 10px;"></i>Configurações
      </h1>
      <p style="color: var(--text-muted); margin-top: 6px; font-size: 1rem;">
        Gerencie suas informações pessoais, avatar e segurança da conta.
      </p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="<?= BASE_URL ?>/minhas-receitas.php" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-user"></i> Ver Meu Perfil
      </a>
      <a href="<?= BASE_URL ?>/publicar.php" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus-circle"></i> Publicar Receita
      </a>
    </div>
  </div>

  <?php if (!empty($erros)): ?>
    <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start; margin-bottom: 24px;">
      <div style="font-weight: 700; margin-bottom: 4px;">Não foi possível salvar as alterações:</div>
      <ul style="padding-left: 20px; font-size: 0.9rem; margin: 0;">
        <?php foreach ($erros as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">

    <!-- Card 1: Informações do Perfil -->
    <div style="background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 32px;">
      <div style="display:flex; align-items:center; gap:12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
        <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--primary-subtle); color: var(--primary); display:flex; align-items:center; justify-content:center; font-size: 1.2rem;">
          <i class="fa-solid fa-user-pen"></i>
        </div>
        <div>
          <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0; color: var(--dark);">Dados Pessoais e Perfil</h2>
          <small style="color: var(--text-muted);">Altere seu nome, nome de usuário, e-mail e foto</small>
        </div>
      </div>

      <form action="<?= BASE_URL ?>/configuracoes.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="perfil">

        <!-- Pré-visualização e Edição de Avatar -->
        <div style="display:flex; align-items:center; gap: 20px; margin-bottom: 26px; padding: 16px; background: var(--bg-body); border-radius: var(--radius-md);">
          <img 
            id="avatarPreview"
            src="<?= htmlspecialchars($user['avatar'] ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80') ?>" 
            alt="<?= htmlspecialchars($user['nome']) ?>" 
            style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: var(--shadow-sm);"
          >
          <div style="flex: 1;">
            <label class="form-label" style="margin-bottom: 6px;">Foto de Perfil (Avatar)</label>
            <div style="display:flex; gap: 10px; flex-wrap: wrap;">
              <input 
                type="file" 
                name="avatar_arquivo" 
                id="avatarArquivo" 
                class="form-control" 
                accept="image/png, image/jpeg, image/webp"
                style="padding: 8px 12px; font-size: 0.9rem;"
                onchange="previewImg(event)"
              >
            </div>
            <small style="color: var(--text-muted); display:block; margin-top: 4px;">
              Formatos aceitos: JPG, PNG ou WEBP. Ou informe uma URL direta abaixo:
            </small>
            <input 
              type="url" 
              name="avatar_url" 
              class="form-control" 
              placeholder="https://exemplo.com/sua-foto.jpg" 
              style="margin-top: 8px; font-size: 0.9rem;"
              value="<?= (strpos($user['avatar'], 'http') === 0 && strpos($user['avatar'], BASE_URL) === false) ? htmlspecialchars($user['avatar']) : '' ?>"
            >
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
          <!-- Nome Completo -->
          <div class="form-group">
            <label class="form-label" for="nome">Nome Completo *</label>
            <input 
              type="text" 
              name="nome" 
              id="nome" 
              class="form-control" 
              required 
              value="<?= htmlspecialchars($user['nome']) ?>"
            >
          </div>

          <!-- Nome de Usuário -->
          <div class="form-group">
            <label class="form-label" for="usuario">Nome de Usuário (@usuario) *</label>
            <input 
              type="text" 
              name="usuario" 
              id="usuario" 
              class="form-control" 
              required 
              value="<?= htmlspecialchars($user['usuario']) ?>"
            >
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-top: 8px;">
          <!-- E-mail -->
          <div class="form-group">
            <label class="form-label" for="email">E-mail *</label>
            <input 
              type="email" 
              name="email" 
              id="email" 
              class="form-control" 
              required 
              value="<?= htmlspecialchars($user['email']) ?>"
            >
          </div>

          <!-- Data de Nascimento -->
          <div class="form-group">
            <label class="form-label" for="data_nascimento">Data de Nascimento *</label>
            <input 
              type="date" 
              name="data_nascimento" 
              id="data_nascimento" 
              class="form-control" 
              required 
              value="<?= htmlspecialchars($user['data_nascimento']) ?>"
            >
            <small style="color:var(--text-muted); font-size:0.8rem; display:block; margin-top:4px;">
              Idade atual: <strong><?= $user['idade'] ?> anos</strong> (recalculada automaticamente).
            </small>
          </div>
        </div>

        <!-- Biografia -->
        <div class="form-group" style="margin-top: 8px;">
          <label class="form-label" for="biografia">Biografia / Sobre você</label>
          <textarea 
            name="biografia" 
            id="biografia" 
            rows="3" 
            class="form-control" 
            placeholder="Conte um pouco sobre suas preferências culinárias, pratos favoritos..."
          ><?= htmlspecialchars($user['biografia'] ?? '') ?></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top: 20px;">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Salvar Informações do Perfil
          </button>
        </div>
      </form>
    </div>

    <!-- Card 2: Segurança e Alteração de Senha -->
    <div style="background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 32px;">
      <div style="display:flex; align-items:center; gap:12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
        <div style="width: 42px; height: 42px; border-radius: 50%; background: #fee2e2; color: #dc2626; display:flex; align-items:center; justify-content:center; font-size: 1.2rem;">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
          <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0; color: var(--dark);">Segurança e Senha</h2>
          <small style="color: var(--text-muted);">Atualize sua senha de acesso à conta</small>
        </div>
      </div>

      <form action="<?= BASE_URL ?>/configuracoes.php" method="POST">
        <input type="hidden" name="acao" value="senha">

        <div class="form-group">
          <label class="form-label" for="senha_atual">Senha Atual *</label>
          <input 
            type="password" 
            name="senha_atual" 
            id="senha_atual" 
            class="form-control" 
            placeholder="Digite sua senha atual" 
            required
          >
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-top: 8px;">
          <div class="form-group">
            <label class="form-label" for="nova_senha">Nova Senha *</label>
            <input 
              type="password" 
              name="nova_senha" 
              id="nova_senha" 
              class="form-control" 
              placeholder="No mínimo 6 caracteres" 
              required
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="confirmar_senha">Confirmar Nova Senha *</label>
            <input 
              type="password" 
              name="confirmar_senha" 
              id="confirmar_senha" 
              class="form-control" 
              placeholder="Repita a nova senha" 
              required
            >
          </div>
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top: 20px;">
          <button type="submit" class="btn btn-primary" style="background: #2b2d42; border-color: #2b2d42;">
            <i class="fa-solid fa-key"></i> Atualizar Senha
          </button>
        </div>
      </form>
    </div>

    <!-- Card 3: Zona de Perigo / Exclusão de Conta -->
    <div id="zona-de-perigo" class="card-danger-zone">
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #fee2e2;">
        <div style="display:flex; align-items:center; gap:12px;">
          <div style="width: 42px; height: 42px; border-radius: 50%; background: #fee2e2; color: #dc2626; display:flex; align-items:center; justify-content:center; font-size: 1.25rem;">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          <div>
            <div style="display:flex; align-items:center; gap:8px;">
              <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0; color: #991b1b;">Zona de Perigo</h2>
              <span class="danger-zone-badge"><i class="fa-solid fa-skull-crossbones"></i> Irreversível</span>
            </div>
            <small style="color: var(--text-muted);">Ações que afetam permanentemente sua conta no banco de dados</small>
          </div>
        </div>
      </div>

      <div class="alert alert-danger" style="margin-bottom: 22px; background: #fff5f5; border-color: #fca5a5; color: #7f1d1d;">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 1.4rem; color: #dc2626;"></i>
        <div>
          <strong>Atenção:</strong> Ao excluir sua conta, todos os seus dados serão <strong>permanentemente apagados do banco de dados</strong>. Suas receitas publicadas, fotos enviadas, comentários e curtidas serão completamente removidos.
        </div>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <div style="max-width: 580px;">
          <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--dark); margin-bottom: 4px;">Excluir minha conta do Sabor em Casa</h3>
          <p style="color: var(--text-muted); font-size: 0.9rem; margin:0;">
            Uma vez confirmada a exclusão, não será possível recuperar suas receitas, interações ou reativar esta conta.
          </p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <a href="<?= BASE_URL ?>/excluir-conta.php" class="btn btn-outline-danger btn-sm" style="display:inline-flex; align-items:center;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Página Detalhada
          </a>
          <button type="button" class="btn btn-danger" onclick="abrirModalExclusao()">
            <i class="fa-solid fa-trash-can"></i> Excluir Conta
          </button>
        </div>
      </div>
    </div>

  </div>

</div>

<!-- Modal de Confirmação de Exclusão de Conta -->
<div class="modal-overlay" id="modalExclusao" role="dialog" aria-modal="true" aria-labelledby="modalExclusaoTitulo">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-header-title" id="modalExclusaoTitulo">
        <i class="fa-solid fa-triangle-exclamation"></i> Confirmar Exclusão de Conta
      </h3>
      <button type="button" class="modal-close-btn" onclick="fecharModalExclusao()" aria-label="Fechar">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="<?= BASE_URL ?>/configuracoes.php" method="POST" id="formExcluirConta">
      <input type="hidden" name="acao" value="excluir_conta">

      <div class="modal-body">
        <p style="font-size: 0.95rem; color: var(--text-main); margin-bottom: 14px;">
          Você está prestes a excluir a conta de <strong><?= htmlspecialchars($user['nome']) ?></strong> (@<?= htmlspecialchars($user['usuario']) ?>).
        </p>

        <p style="font-size: 0.88rem; font-weight: 700; color: #991b1b; margin-bottom: 6px;">
          O que acontecerá ao confirmar:
        </p>
        <ul class="delete-impact-list">
          <li class="delete-impact-item">
            <i class="fa-solid fa-database"></i>
            <span><strong>Banco de Dados:</strong> Seu cadastro de usuário será excluído definitivamente da tabela do sistema.</span>
          </li>
          <li class="delete-impact-item">
            <i class="fa-solid fa-utensils"></i>
            <span><strong>Receitas e Fotos:</strong> Todas as suas receitas cadastradas e imagens enviadas serão apagadas do servidor.</span>
          </li>
          <li class="delete-impact-item">
            <i class="fa-solid fa-comments"></i>
            <span><strong>Comentários e Curtidas:</strong> Suas mensagens, avaliações e curtidas em receitas serão excluídas.</span>
          </li>
        </ul>

        <?php if (!empty($user['google_id'])): ?>
          <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label" for="confirmacao_seguranca" style="color: var(--dark); font-weight: 600;">
              Digite <span style="color:#dc2626; font-weight:700;">EXCLUIR</span> ou seu e-mail (<?= htmlspecialchars($user['email']) ?>) para confirmar: *
            </label>
            <input 
              type="text" 
              name="confirmacao_seguranca" 
              id="confirmacao_seguranca" 
              class="form-control" 
              placeholder="Digite EXCLUIR" 
              required
              autocomplete="off"
            >
          </div>
        <?php else: ?>
          <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label" for="confirmacao_seguranca" style="color: var(--dark); font-weight: 600;">
              Digite sua <strong>senha atual</strong> para confirmar sua identidade: *
            </label>
            <div style="position: relative;">
              <input 
                type="password" 
                name="confirmacao_seguranca" 
                id="confirmacao_seguranca" 
                class="form-control" 
                placeholder="Sua senha de acesso atual" 
                required
                autocomplete="current-password"
                style="padding-right: 42px;"
              >
              <button 
                type="button" 
                onclick="alternarVisibilidadeSenha('confirmacao_seguranca', this)" 
                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;"
                aria-label="Ver senha"
              >
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
          </div>
        <?php endif; ?>

        <div style="margin-top: 16px; padding: 12px 14px; background: #fef2f2; border-radius: var(--radius-sm); border: 1px dashed #fca5a5;">
          <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 0.88rem; color: #7f1d1d; margin: 0;">
            <input type="checkbox" name="ciente_exclusao" id="ciente_exclusao" required style="margin-top: 3px; accent-color: #dc2626; width: 16px; height: 16px;">
            <span>Estou ciente de que esta exclusão é permanente, irreversível e apagará todos os meus dados do banco de dados.</span>
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="fecharModalExclusao()">
          Cancelar
        </button>
        <button type="submit" class="btn btn-danger" id="btnConfirmarExclusao">
          <i class="fa-solid fa-trash-can"></i> Sim, Excluir Minha Conta
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function previewImg(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('avatarPreview').src = e.target.result;
    }
    reader.readAsDataURL(file);
  }
}

function abrirModalExclusao() {
  const modal = document.getElementById('modalExclusao');
  if (modal) {
    modal.classList.add('active');
    const input = document.getElementById('confirmacao_seguranca');
    if (input) {
      setTimeout(() => input.focus(), 100);
    }
  }
}

function fecharModalExclusao() {
  const modal = document.getElementById('modalExclusao');
  if (modal) {
    modal.classList.remove('active');
  }
}

// Fechar com ESC ou clicando fora do modal
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') fecharModalExclusao();
});

document.addEventListener('click', function(e) {
  const modal = document.getElementById('modalExclusao');
  if (modal && e.target === modal) fecharModalExclusao();
});

function alternarVisibilidadeSenha(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const icon = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    if (icon) {
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    }
  } else {
    input.type = 'password';
    if (icon) {
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  }
}

<?php if (isset($acao) && $acao === 'excluir_conta' && !empty($erros)): ?>
// Reabre modal caso tenha ocorrido erro de validação (ex: senha incorreta)
document.addEventListener('DOMContentLoaded', function() {
  abrirModalExclusao();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

