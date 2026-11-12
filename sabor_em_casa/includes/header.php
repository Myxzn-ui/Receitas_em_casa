<?php
/**
 * Header Global Responsivo
 * Sabor em Casa
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$user = currentUser();
$flash = getFlash();

$pageTitle = $pageTitle ?? 'Sabor em Casa - Receitas Completas em Texto e Vídeo';
$metaDescription = $metaDescription ?? 'Descubra receitas deliciosas, fáceis e afetivas com vídeos e passo a passo completo. Compartilhe suas próprias criações culinárias.';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logo.png">
  
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Folha de Estilos Principal -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  
  <!-- Sistema de Consentimento de Cookies (LGPD) -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/cookie-consent.css?v=<?= filemtime(__DIR__ . '/../assets/css/cookie-consent.css') ?>">

  <script>
    window.BASE_URL = '<?= BASE_URL ?>';
  </script>

  <!-- Scripts com Bloqueio Prévio Conforme LGPD (só rodam após consentimento) -->
  <script type="text/plain" data-cookie-category="estatisticas">
    console.log('[LGPD Consent] Categoria ESTATÍSTICAS autorizada: Sistema analítico anônimo carregado.');
  </script>
  <script type="text/plain" data-cookie-category="marketing">
    console.log('[LGPD Consent] Categoria MARKETING autorizada: Mídias de terceiros e YouTube carregados.');
  </script>
</head>
<body data-recipe-id="<?= $currentRecipeId ?? '' ?>">

  <!-- Header Principal -->
  <header class="site-header">
    <div class="container header-container">
      
      <!-- Logotipo -->
      <a href="<?= BASE_URL ?>/index.php" class="brand-logo" aria-label="Sabor em Casa - Página Inicial">
        <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Sabor em Casa" class="brand-img">
        <div class="brand-title">Sabor <span>em Casa</span></div>
      </a>

      <!-- Menu de Navegação -->
      <nav class="nav-menu" id="navMenu">
        <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">
          <i class="fa-solid fa-house"></i> Início
        </a>
        <a href="<?= BASE_URL ?>/receitas.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'receitas.php' && empty($_GET['com_video'])) ? 'active' : '' ?>">
          <i class="fa-solid fa-book-open"></i> Todas as Receitas
        </a>
        <a href="<?= BASE_URL ?>/receitas.php?com_video=1" class="nav-link <?= (!empty($_GET['com_video'])) ? 'active' : '' ?>">
          <i class="fa-solid fa-circle-play" style="color: #e63946;"></i> Receitas em Vídeo
        </a>
        
        <?php if ($user): ?>
          <a href="<?= BASE_URL ?>/configuracoes.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'configuracoes.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-gear"></i> Configurações
          </a>
          <a href="<?= BASE_URL ?>/minhas-receitas.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'minhas-receitas.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-user-circle"></i> Meu Perfil
          </a>
          <a href="<?= BASE_URL ?>/logout.php" class="nav-link" style="color: #B93E22;">
            <i class="fa-solid fa-right-from-bracket"></i> Sair
          </a>
        <?php endif; ?>
      </nav>

      <!-- Ações do Header à Direita -->
      <div class="header-actions">
        <?php if ($user): ?>
          <a href="<?= BASE_URL ?>/publicar.php" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-circle-plus"></i> Publicar Receita
          </a>

          <!-- Menu Dropdown do Usuário Logado -->
          <div class="user-dropdown">
            <button class="user-btn" id="userDropdownBtn" aria-label="Menu do Usuário">
              <img src="<?= htmlspecialchars($user['avatar'] ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80') ?>" alt="Avatar" class="user-avatar">
              <span class="user-name"><?= htmlspecialchars(explode(' ', $user['nome'])[0]) ?></span>
              <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: var(--text-muted);"></i>
            </button>
            <div class="dropdown-menu" id="userDropdownMenu">
              <div class="dropdown-header">
                <strong><?= htmlspecialchars($user['nome']) ?></strong>
                <p>@<?= htmlspecialchars($user['usuario']) ?> &bull; <?= $user['idade'] ?> anos</p>
              </div>
              <a href="<?= BASE_URL ?>/minhas-receitas.php" class="dropdown-item">
                <i class="fa-solid fa-utensils"></i> Minhas Receitas
              </a>
              <a href="<?= BASE_URL ?>/configuracoes.php" class="dropdown-item">
                <i class="fa-solid fa-gear"></i> Configurações
              </a>
              <a href="<?= BASE_URL ?>/publicar.php" class="dropdown-item">
                <i class="fa-solid fa-plus-circle"></i> Nova Receita
              </a>
              <div class="dropdown-divider"></div>
              <a href="<?= BASE_URL ?>/excluir-conta.php" class="dropdown-item" style="color: #dc2626;">
                <i class="fa-solid fa-trash-can"></i> Excluir Conta
              </a>
              <a href="<?= BASE_URL ?>/logout.php" class="dropdown-item" style="color: #e63946;">
                <i class="fa-solid fa-right-from-bracket"></i> Sair da Conta
              </a>
            </div>
          </div>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/login.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-right-to-bracket"></i> Entrar
          </a>
          <a href="<?= BASE_URL ?>/cadastro.php" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-user-plus"></i> Cadastre-se
          </a>
        <?php endif; ?>

        <!-- Botão Hambúrguer Mobile -->
        <button class="mobile-toggle" id="mobileToggle" aria-label="Abrir Menu">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>

    </div>
  </header>

  <!-- Container de Alertas e Notificações Flash -->
  <?php if ($flash): ?>
    <div class="container flash-container">
      <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
        <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : ($flash['type'] === 'danger' ? 'fa-circle-exclamation' : 'fa-circle-info') ?>"></i>
        <span><?= htmlspecialchars($flash['message']) ?></span>
      </div>
    </div>
  <?php endif; ?>

  <main>
