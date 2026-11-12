<?php
/**
 * Exclusão Permanente de Conta de Usuário
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Exige que o usuário esteja autenticado
requireAuth();

$user = currentUser();
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmacao = $_POST['confirmacao_seguranca'] ?? '';
    $ciente = isset($_POST['ciente_exclusao']);

    if (!$ciente) {
        $erros[] = 'Você precisa marcar a caixa confirmando que está ciente de que a exclusão da conta é definitiva e irreversível.';
    } else {
        $excluiu = excluirContaUsuario($user['id'], $confirmacao, $erros);
        if ($excluiu) {
            logoutUser();
            setFlash('info', 'Sua conta e todos os seus dados foram permanentemente excluídos do banco de dados. Sentiremos sua falta!');
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }
}

$pageTitle = 'Excluir Minha Conta - Sabor em Casa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 40px; padding-bottom: 80px; max-width: 680px;">

  <!-- Botão de Voltar -->
  <div style="margin-bottom: 24px;">
    <a href="<?= BASE_URL ?>/configuracoes.php" class="btn btn-secondary btn-sm">
      <i class="fa-solid fa-arrow-left"></i> Voltar para Configurações
    </a>
  </div>

  <?php if (!empty($erros)): ?>
    <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start; margin-bottom: 24px;">
      <div style="font-weight: 700; margin-bottom: 4px;">Não foi possível prosseguir com a exclusão:</div>
      <ul style="padding-left: 20px; font-size: 0.9rem; margin: 0;">
        <?php foreach ($erros as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card-danger-zone" style="padding: 36px 32px;">
    <div style="display:flex; align-items:center; gap: 14px; margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid #fee2e2;">
      <div style="width: 50px; height: 50px; border-radius: 50%; background: #fee2e2; color: #dc2626; display:flex; align-items:center; justify-content:center; font-size: 1.5rem; flex-shrink: 0;">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div>
        <div style="display:flex; align-items:center; gap: 8px;">
          <h1 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: #991b1b; margin: 0;">
            Excluir Conta Definitivamente
          </h1>
          <span class="danger-zone-badge">Irreversível</span>
        </div>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px; margin-bottom: 0;">
          Você está prestes a remover permanentemente a conta de <strong><?= htmlspecialchars($user['nome']) ?></strong> (@<?= htmlspecialchars($user['usuario']) ?>).
        </p>
      </div>
    </div>

    <div class="alert alert-danger" style="margin-bottom: 24px; background: #fff5f5; border-color: #fca5a5; color: #7f1d1d; line-height: 1.5;">
      <i class="fa-solid fa-circle-exclamation" style="font-size: 1.5rem; color: #dc2626;"></i>
      <div>
        <strong>Atenção máxima:</strong> Esta operação apagará imediatamente todos os seus registros do banco de dados MySQL e do servidor. Não haverá como recuperar suas receitas, fotos ou dados posteriormente.
      </div>
    </div>

    <div style="margin-bottom: 24px;">
      <h3 style="font-size: 0.95rem; font-weight: 700; color: #991b1b; margin-bottom: 10px;">
        Registros que serão excluídos do banco de dados:
      </h3>
      <ul class="delete-impact-list">
        <li class="delete-impact-item">
          <i class="fa-solid fa-database"></i>
          <div>
            <strong>Perfil no Banco de Dados:</strong> O registro na tabela <code>usuarios</code> será excluído.
          </div>
        </li>
        <li class="delete-impact-item">
          <i class="fa-solid fa-utensils"></i>
          <div>
            <strong>Receitas Cadastradas:</strong> Todas as receitas publicadas por você e seus arquivos de fotos serão removidos.
          </div>
        </li>
        <li class="delete-impact-item">
          <i class="fa-solid fa-comments"></i>
          <div>
            <strong>Comentários e Avaliações:</strong> Todas as mensagens deixadas em pratos de outros usuários serão deletadas.
          </div>
        </li>
        <li class="delete-impact-item">
          <i class="fa-solid fa-heart"></i>
          <div>
            <strong>Curtidas e Interações:</strong> Curtidas dadas em receitas da comunidade serão desfeitas e os contadores atualizados.
          </div>
        </li>
      </ul>
    </div>

    <form action="<?= BASE_URL ?>/excluir-conta.php" method="POST">
      <?php if (!empty($user['google_id'])): ?>
        <div class="form-group" style="margin-bottom: 20px;">
          <label class="form-label" for="confirmacao_seguranca" style="color: var(--dark); font-weight: 600;">
            Como sua conta é vinculada ao Google, digite a palavra <span style="color:#dc2626; font-weight:700;">EXCLUIR</span> ou seu e-mail cadastrado (<strong><?= htmlspecialchars($user['email']) ?></strong>) para confirmar: *
          </label>
          <input 
            type="text" 
            name="confirmacao_seguranca" 
            id="confirmacao_seguranca" 
            class="form-control" 
            placeholder="Digite EXCLUIR" 
            required
            autocomplete="off"
            style="font-size: 1rem; padding: 12px 14px;"
          >
        </div>
      <?php else: ?>
        <div class="form-group" style="margin-bottom: 20px;">
          <label class="form-label" for="confirmacao_seguranca" style="color: var(--dark); font-weight: 600;">
            Para sua segurança, digite sua <strong>senha de acesso atual</strong>: *
          </label>
          <div style="position: relative;">
            <input 
              type="password" 
              name="confirmacao_seguranca" 
              id="confirmacao_seguranca" 
              class="form-control" 
              placeholder="Digite sua senha atual" 
              required
              autocomplete="current-password"
              style="padding-right: 44px; font-size: 1rem; padding-top: 12px; padding-bottom: 12px;"
            >
            <button 
              type="button" 
              onclick="alternarVisibilidadeSenha('confirmacao_seguranca', this)" 
              style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1rem; padding: 4px;"
              aria-label="Ver senha"
            >
              <i class="fa-regular fa-eye"></i>
            </button>
          </div>
        </div>
      <?php endif; ?>

      <div style="margin-bottom: 24px; padding: 14px 16px; background: #fef2f2; border-radius: var(--radius-sm); border: 1px dashed #fca5a5;">
        <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; font-size: 0.92rem; color: #7f1d1d; margin: 0;">
          <input type="checkbox" name="ciente_exclusao" id="ciente_exclusao" required style="margin-top: 3px; accent-color: #dc2626; width: 18px; height: 18px; flex-shrink: 0;">
          <span>Tenho total ciência de que a exclusão da minha conta é definitiva, não pode ser desfeita e removerá todos os meus registros do banco de dados do sistema.</span>
        </label>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap: 14px;">
        <a href="<?= BASE_URL ?>/configuracoes.php" class="btn btn-secondary">
          <i class="fa-solid fa-xmark"></i> Cancelar e Voltar em Segurança
        </a>
        <button type="submit" class="btn btn-danger" style="padding: 12px 24px;">
          <i class="fa-solid fa-trash-can"></i> Confirmar Exclusão Definitiva
        </button>
      </div>
    </form>
  </div>

</div>

<script>
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
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
