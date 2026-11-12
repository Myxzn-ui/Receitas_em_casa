<?php
/**
 * Visualização Completa da Receita
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . '/receitas.php');
    exit;
}

$db = getDb();

// Incrementa visualização
$db->prepare("UPDATE receitas SET visualizacoes = visualizacoes + 1 WHERE id = ?")->execute([$id]);

// Busca dados da receita + autor
$stmt = $db->prepare("
    SELECT r.*, c.nome AS categoria_nome, c.slug AS categoria_slug, 
           u.nome AS autor_nome, u.usuario AS autor_usuario, u.avatar AS autor_avatar, 
           u.idade AS autor_idade, u.biografia AS autor_bio
    FROM receitas r
    JOIN categorias c ON r.categoria_id = c.id
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$receita = $stmt->fetch();

if (!$receita) {
    setFlash('danger', 'Receita não encontrada.');
    header('Location: ' . BASE_URL . '/receitas.php');
    exit;
}

$ingredientes = separarIngredientes($receita['ingredientes']);
$passos = separarModoPreparo($receita['modo_preparo']);
$embedUrl = obterEmbedVideoUrl($receita['video_url']);

// Receitas relacionadas da mesma categoria
$stmtRel = $db->prepare("
    SELECT r.*, c.nome AS categoria_nome 
    FROM receitas r
    JOIN categorias c ON r.categoria_id = c.id
    WHERE r.categoria_id = ? AND r.id != ?
    LIMIT 3
");
$stmtRel->execute([$receita['categoria_id'], $id]);
$relacionadas = $stmtRel->fetchAll();

// Verifica se o usuário atual curtiu a receita
$user = currentUser();
$userId = $user ? (int)$user['id'] : null;
if (empty($_SESSION['guest_like_token'])) {
    $_SESSION['guest_like_token'] = bin2hex(random_bytes(16));
}
$sessaoToken = $_SESSION['guest_like_token'];

if ($userId) {
    $stmtLikeCheck = $db->prepare("SELECT id FROM receitas_likes WHERE receita_id = ? AND (usuario_id = ? OR sessao_token = ?)");
    $stmtLikeCheck->execute([$id, $userId, $sessaoToken]);
} else {
    $stmtLikeCheck = $db->prepare("SELECT id FROM receitas_likes WHERE receita_id = ? AND sessao_token = ?");
    $stmtLikeCheck->execute([$id, $sessaoToken]);
}
$usuarioCurtiu = (bool)$stmtLikeCheck->fetch();
$totalLikes = (int)($receita['likes'] ?? 0);

// Busca comentários da receita
$stmtComentarios = $db->prepare("
    SELECT c.*, u.usuario, u.nome as user_nome, u.avatar as user_avatar
    FROM comentarios c
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.receita_id = ?
    ORDER BY c.criado_em ASC
");
$stmtComentarios->execute([$id]);
$comentarios = $stmtComentarios->fetchAll();
$totalComentarios = count($comentarios);

$currentRecipeId = $receita['id'];
$pageTitle = htmlspecialchars($receita['titulo']) . ' - Sabor em Casa';
$metaDescription = htmlspecialchars(resumirTexto($receita['descricao'], 150));
require_once __DIR__ . '/includes/header.php';
?>

<div class="container recipe-header-view">
  
  <!-- Breadcrumb -->
  <nav style="display:flex; align-items:center; gap:8px; font-size:0.88rem; color:var(--text-muted); margin-bottom:18px;">
    <a href="<?= BASE_URL ?>/index.php">Início</a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.65rem;"></i>
    <a href="<?= BASE_URL ?>/receitas.php?categoria=<?= urlencode($receita['categoria_slug']) ?>"><?= htmlspecialchars($receita['categoria_nome']) ?></a>
    <i class="fa-solid fa-chevron-right" style="font-size:0.65rem;"></i>
    <span style="color:var(--text-main); font-weight:600;"><?= resumirTexto($receita['titulo'], 40) ?></span>
  </nav>

  <!-- Cabeçalho do Prato -->
  <div class="recipe-hero-details">
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
      <span class="badge-category" style="position:static; background:var(--primary); font-size:0.85rem;">
        <?= htmlspecialchars($receita['categoria_nome']) ?>
      </span>
      <?php if (!empty($receita['video_url'])): ?>
        <span class="badge-video" style="position:static; font-size:0.85rem;">
          <i class="fa-solid fa-circle-play"></i> Vídeo Incluso
        </span>
      <?php endif; ?>
    </div>

    <h1 class="recipe-title-lg"><?= htmlspecialchars($receita['titulo']) ?></h1>
    <p style="font-size:1.1rem; color:var(--text-muted); max-width:800px;"><?= nl2br(htmlspecialchars($receita['descricao'])) ?></p>
  </div>

  <!-- Barra do Autor e Ações -->
  <div class="recipe-author-bar">
    <div class="author-profile-link">
      <img src="<?= htmlspecialchars($receita['autor_avatar']) ?>" alt="<?= htmlspecialchars($receita['autor_nome']) ?>" class="author-lg-avatar">
      <div class="author-lg-info">
        <h4><?= htmlspecialchars($receita['autor_nome']) ?> (@<?= htmlspecialchars($receita['autor_usuario']) ?>)</h4>
        <span>Cozinheiro(a) &bull; <?= $receita['autor_idade'] ?> anos &bull; Publicado em <?= dataFormatada($receita['criado_em']) ?></span>
      </div>
    </div>

    <div class="recipe-actions-bar">
      <button class="btn btn-secondary btn-sm" onclick="window.print()" title="Imprimir Receita">
        <i class="fa-solid fa-print"></i> Imprimir
      </button>
      <button class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(window.location.href); alert('Link da receita copiado com sucesso!');" title="Compartilhar Link">
        <i class="fa-solid fa-share-nodes"></i> Compartilhar
      </button>
    </div>
  </div>

  <!-- Barra de Estatísticas da Receita -->
  <div class="stats-banner" style="margin-top: 24px;">
    <div class="stat-box">
      <div class="stat-icon">
        <i class="fa-regular fa-clock"></i>
      </div>
      <div>
        <span class="stat-label">Tempo de Preparo</span>
        <span class="stat-value"><?= htmlspecialchars($receita['tempo_preparo']) ?></span>
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-icon">
        <i class="fa-solid fa-chart-pie"></i>
      </div>
      <div>
        <span class="stat-label">Rendimento</span>
        <span class="stat-value"><?= htmlspecialchars($receita['rendimento']) ?></span>
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-icon">
        <i class="fa-solid fa-gauge-high"></i>
      </div>
      <div>
        <span class="stat-label">Dificuldade</span>
        <span class="stat-value"><?= htmlspecialchars($receita['dificuldade']) ?></span>
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-icon">
        <i class="fa-solid fa-eye"></i>
      </div>
      <div>
        <span class="stat-label">Visualizações</span>
        <span class="stat-value"><?= number_format($receita['visualizacoes'], 0, ',', '.') ?> acessos</span>
      </div>
    </div>

    <!-- Sistema Interativo de Likes ao Lado de Visualizações -->
    <div class="stat-box stat-box-like <?= $usuarioCurtiu ? 'liked' : '' ?>" id="statBoxLike" role="button" tabindex="0" title="Clique para curtir ou descurtir esta receita">
      <div class="stat-icon stat-icon-like">
        <i class="<?= $usuarioCurtiu ? 'fa-solid' : 'fa-regular' ?> fa-heart heart-icon" id="likeHeartIcon"></i>
      </div>
      <div>
        <span class="stat-label">Curtidas</span>
        <span class="stat-value" id="likeCountDisplay"><?= number_format($totalLikes, 0, ',', '.') ?> <?= $totalLikes == 1 ? 'curtida' : 'curtidas' ?></span>
      </div>
    </div>
  </div>

  <!-- Seção de Vídeo (se houver) -->
  <?php if (!empty($embedUrl)): ?>
    <?php $ytId = extrairYoutubeId($receita['video_url']); ?>
    <section class="video-player-section">
      <div style="margin-bottom: 12px; display:flex; align-items:center; gap:8px;">
        <i class="fa-solid fa-film" style="color:var(--primary); font-size:1.2rem;"></i>
        <h2 style="font-family:var(--font-heading); font-size:1.35rem; color:var(--dark);">Passo a Passo em Vídeo</h2>
      </div>
      <div class="video-container" data-cookie-protected-video data-youtube-id="<?= htmlspecialchars($ytId ?? '') ?>">
        <iframe src="<?= htmlspecialchars($embedUrl) ?>" title="<?= htmlspecialchars($receita['titulo']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      </div>
      <div class="video-notice">
        <i class="fa-solid fa-circle-info"></i>
        <span>Você pode assistir ao vídeo completo acima ou acompanhar a lista detalhada de ingredientes e modo de preparo em texto abaixo.</span>
      </div>
    </section>
  <?php else: ?>
    <!-- Imagem em destaque caso não haja vídeo -->
    <div style="margin-bottom: 36px; border-radius: var(--radius-lg); overflow: hidden; max-height: 480px; box-shadow: var(--shadow-lg);">
      <img src="<?= htmlspecialchars($receita['imagem']) ?>" alt="<?= htmlspecialchars($receita['titulo']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
  <?php endif; ?>

  <!-- Conteúdo Principal: Ingredientes & Modo de Preparo -->
  <div class="recipe-content-grid">
    
    <!-- Coluna de Ingredientes (com checklist) -->
    <aside class="recipe-sidebar">
      <div class="sidebar-box">
        <h2 class="box-title">
          <i class="fa-solid fa-basket-shopping"></i> Ingredientes
        </h2>
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:16px;">
          Toque ou clique nos itens conforme for separando na sua cozinha:
        </p>

        <ul class="ingredients-list">
          <?php foreach ($ingredientes as $item): ?>
            <?php if ($item['is_secao']): ?>
              <li class="ingredient-section-title"><?= htmlspecialchars($item['texto']) ?></li>
            <?php else: ?>
              <li class="ingredient-item">
                <input type="checkbox" class="ingredient-checkbox">
                <span class="ingredient-text"><?= htmlspecialchars($item['texto']) ?></span>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Card do Autor -->
      <div class="sidebar-box" style="background:#FAF8F5;">
        <h3 style="font-family:var(--font-heading); font-size:1.15rem; color:var(--dark); margin-bottom:12px;">Sobre quem preparou</h3>
        <div style="display:flex; gap:14px; align-items:center; margin-bottom:12px;">
          <img src="<?= htmlspecialchars($receita['autor_avatar']) ?>" alt="<?= htmlspecialchars($receita['autor_nome']) ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
          <div>
            <strong style="color:var(--dark); font-size:0.95rem;"><?= htmlspecialchars($receita['autor_nome']) ?></strong>
            <div style="font-size:0.82rem; color:var(--text-muted);"><?= $receita['autor_idade'] ?> anos &bull; Membro da comunidade</div>
          </div>
        </div>
        <p style="font-size:0.88rem; color:var(--text-muted); line-height:1.5;"><?= htmlspecialchars($receita['autor_bio'] ?: 'Amante da boa culinária caseira e receitas práticas para o dia a dia.') ?></p>
      </div>

      <style>
      /* Estilos Aprimorados de Espaçamento e Perfil do Chat */
      .recipe-chat-card {
        background: #FFFFFF !important;
        border: 1px solid #E6E0D8 !important;
        border-radius: 16px !important;
        padding: 24px 20px !important;
        margin-top: 26px !important;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04) !important;
      }
      .recipe-chat-card .chat-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        margin-bottom: 6px !important;
        padding-bottom: 14px !important;
        border-bottom: 1px solid #EEE8E0 !important;
      }
      .recipe-chat-card .chat-title-group {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
      }
      .recipe-chat-card .chat-title {
        font-family: var(--font-heading) !important;
        font-size: 1.22rem !important;
        color: var(--dark) !important;
        margin: 0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
      }
      .recipe-chat-card .chat-badge {
        background: #FFE8E2 !important;
        color: var(--primary) !important;
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        padding: 3px 10px !important;
        border-radius: 20px !important;
      }
      .recipe-chat-card .chat-status-pulse {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        color: #1B7A6D !important;
        background: #E8F7F4 !important;
        padding: 4px 10px !important;
        border-radius: 20px !important;
        border: 1px solid #D0EFE9 !important;
      }
      .recipe-chat-card .pulse-dot {
        width: 8px !important;
        height: 8px !important;
        background: #2A9D8F !important;
        border-radius: 50% !important;
        animation: pulseGreen 1.8s infinite !important;
      }
      .recipe-chat-card .chat-description {
        font-size: 0.88rem !important;
        color: var(--text-muted) !important;
        margin-bottom: 18px !important;
        line-height: 1.45 !important;
      }
      /* Espaçamento entre cada comentário */
      .recipe-chat-card .chat-messages-container {
        max-height: 520px !important;
        overflow-y: auto !important;
        padding: 4px 2px !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 16px !important;
        margin-bottom: 22px !important;
        scroll-behavior: smooth !important;
      }
      .recipe-chat-card .chat-messages-container::-webkit-scrollbar {
        width: 5px !important;
      }
      .recipe-chat-card .chat-messages-container::-webkit-scrollbar-thumb {
        background: #D8D0C5 !important;
        border-radius: 10px !important;
      }
      /* Card espaçado de comentário */
      .chat-comment-card {
        background: #FAFAF8 !important;
        border: 1px solid #E8E2D8 !important;
        border-radius: 14px !important;
        padding: 16px 18px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03) !important;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s !important;
      }
      .chat-comment-card:hover {
        background: #FFFFFF !important;
        border-color: #D4C9BC !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06) !important;
        transform: translateY(-2px) !important;
      }
      .chat-comment-card.comment-mine {
        background: #FFFBF9 !important;
        border-color: #F8D9CF !important;
      }
      .chat-comment-card.comment-from-chef {
        background: #FFFAF5 !important;
        border-color: #FED7AA !important;
      }
      .comment-card-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        margin-bottom: 10px !important;
      }
      .comment-user-info {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
      }
      /* Perfil do Usuário (Ícone elegante) */
      .comment-profile-icon {
        width: 42px !important;
        height: 42px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.25rem !important;
        flex-shrink: 0 !important;
      }
      .comment-profile-icon.icon-user {
        background: linear-gradient(135deg, #6C757D, #495057) !important;
        color: #FFFFFF !important;
        box-shadow: 0 3px 8px rgba(108, 117, 125, 0.25) !important;
      }
      .comment-profile-icon.icon-chef {
        background: linear-gradient(135deg, #E76F51, #D94824) !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 12px rgba(231, 111, 81, 0.35) !important;
      }
      .comment-profile-icon.icon-me {
        background: linear-gradient(135deg, #2A9D8F, #21867A) !important;
        color: #FFFFFF !important;
        box-shadow: 0 3px 8px rgba(42, 157, 143, 0.3) !important;
      }
      .comment-user-details {
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
      }
      .comment-user-name {
        font-size: 0.96rem !important;
        font-weight: 700 !important;
        color: var(--dark) !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
      }
      .badge-tag-chef {
        font-size: 0.72rem !important;
        background: #FEF3C7 !important;
        color: #92400E !important;
        padding: 2px 8px !important;
        border-radius: 6px !important;
        font-weight: 700 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        border: 1px solid #FDE68A !important;
      }
      .badge-tag-me {
        font-size: 0.72rem !important;
        background: #E6F7F4 !important;
        color: #1B7A6D !important;
        padding: 2px 8px !important;
        border-radius: 6px !important;
        font-weight: 700 !important;
        border: 1px solid #C8EBE4 !important;
      }
      .comment-user-time {
        font-size: 0.78rem !important;
        color: var(--text-muted) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
      }
      .comment-card-delete {
        background: none !important;
        border: none !important;
        color: #B5ACA1 !important;
        cursor: pointer !important;
        font-size: 0.85rem !important;
        padding: 5px 8px !important;
        border-radius: 6px !important;
        transition: all 0.2s !important;
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
      }
      .comment-card-delete:hover {
        color: #E63946 !important;
        background: #FEE2E2 !important;
      }
      .comment-card-body {
        padding-top: 10px !important;
        border-top: 1px solid #EFEAE2 !important;
      }
      .comment-card-text {
        font-size: 0.94rem !important;
        line-height: 1.6 !important;
        color: var(--text-main) !important;
        margin: 0 !important;
        word-break: break-word !important;
      }
      /* Formulário de Envio */
      .chat-form-box {
        background: #FAFAF8 !important;
        border: 1px solid #E8E2D8 !important;
        border-radius: 14px !important;
        padding: 18px !important;
      }
      .chat-form-title {
        font-size: 0.94rem !important;
        font-weight: 700 !important;
        color: var(--dark) !important;
        margin-bottom: 12px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
      }
      .chat-anon-fields {
        margin-bottom: 10px !important;
      }
      .input-with-icon-sm {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
      }
      .input-with-icon-sm i {
        position: absolute !important;
        left: 12px !important;
        font-size: 0.85rem !important;
        color: var(--text-muted) !important;
      }
      .chat-input-name {
        width: 100% !important;
        padding: 8px 12px 8px 36px !important;
        font-size: 0.88rem !important;
        border: 1px solid #D6CCC0 !important;
        border-radius: 8px !important;
        outline: none !important;
        background: #FFFFFF !important;
      }
      .chat-input-name:focus {
        border-color: var(--primary) !important;
      }
      .chat-form-main-row {
        display: flex !important;
        align-items: flex-start !important;
        gap: 12px !important;
      }
      .chat-form-textarea {
        flex: 1 !important;
        width: 100% !important;
        border: 1px solid #D6CCC0 !important;
        border-radius: 8px !important;
        padding: 10px 14px !important;
        font-size: 0.92rem !important;
        font-family: inherit !important;
        resize: vertical !important;
        min-height: 72px !important;
        outline: none !important;
        background: #FFFFFF !important;
        line-height: 1.5 !important;
      }
      .chat-form-textarea:focus {
        border-color: var(--primary) !important;
      }
      .chat-form-footer-bar {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        margin-top: 10px !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
      }
      .chat-btn-submit {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 9px 18px !important;
        font-size: 0.9rem !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
        cursor: pointer !important;
      }
      </style>

      <!-- Chat de Comentários Interativo (Abaixo de Sobre quem preparou) -->
      <div class="sidebar-box recipe-chat-card" id="recipeChatCard">
        <div class="chat-header">
          <div class="chat-title-group">
            <h3 class="chat-title">
              <i class="fa-solid fa-comments"></i> Chat da Receita
            </h3>
            <span class="chat-badge" id="chatCountBadge"><?= $totalComentarios ?></span>
          </div>
          <span class="chat-status-pulse" title="Conversa da comunidade ao vivo">
            <span class="pulse-dot"></span> Ao vivo
          </span>
        </div>

        <p class="chat-description">
          Dúvidas, sugestões ou elogios? Compartilhe sua experiência no preparo!
        </p>

        <!-- Feed de Mensagens do Chat com Espaçamento Amplo -->
        <div class="chat-messages-container" id="chatMessagesList">
          <?php if (empty($comentarios)): ?>
            <div class="chat-empty-state" id="chatEmptyState">
              <i class="fa-regular fa-comment-dots"></i>
              <p>Nenhum comentário ainda. Seja a primeira pessoa a enviar um comentário!</p>
            </div>
          <?php else: ?>
            <?php foreach ($comentarios as $comm): 
              $cNome = !empty($comm['user_nome']) ? $comm['user_nome'] : $comm['nome_autor'];
              $isAuthor = ((int)$comm['usuario_id'] === (int)$receita['usuario_id']);
              $isMe = ($user && (int)$comm['usuario_id'] === (int)$user['id']);
              $isRecipeOwner = ($user && (int)$user['id'] === (int)$receita['usuario_id']);
              $canDeleteThis = ($isMe || $isRecipeOwner || ($user && (int)$user['id'] === 1));
              $iconClass = $isAuthor ? 'icon-chef' : ($isMe ? 'icon-me' : 'icon-user');
            ?>
              <div class="chat-comment-card <?= $isMe ? 'comment-mine' : '' ?> <?= $isAuthor ? 'comment-from-chef' : '' ?>" id="comment-<?= $comm['id'] ?>">
                <div class="comment-card-header">
                  <div class="comment-user-info">
                    <!-- Perfil do Usuário em Ícone ao invés da foto -->
                    <div class="comment-profile-icon <?= $iconClass ?>" title="<?= $isAuthor ? 'Chef da Receita' : ($isMe ? 'Seu perfil' : 'Perfil do usuário') ?>">
                      <i class="fa-solid <?= $isAuthor ? 'fa-crown' : 'fa-circle-user' ?>"></i>
                    </div>
                    <div class="comment-user-details">
                      <span class="comment-user-name">
                        <?= htmlspecialchars($cNome) ?>
                        <?php if ($isAuthor): ?>
                          <span class="badge-tag-chef"><i class="fa-solid fa-crown"></i> Chef da Receita</span>
                        <?php elseif ($isMe): ?>
                          <span class="badge-tag-me"><i class="fa-solid fa-check"></i> Você</span>
                        <?php endif; ?>
                      </span>
                      <span class="comment-user-time">
                        <i class="fa-regular fa-clock"></i> <?= tempoRelativo($comm['criado_em']) ?>
                      </span>
                    </div>
                  </div>
                  <?php if ($canDeleteThis): ?>
                    <button type="button" class="comment-card-delete" onclick="excluirComentario(<?= $comm['id'] ?>)" title="Excluir este comentário">
                      <i class="fa-regular fa-trash-can"></i> Excluir
                    </button>
                  <?php endif; ?>
                </div>
                <div class="comment-card-body">
                  <p class="comment-card-text"><?= nl2br(htmlspecialchars($comm['comentario'])) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Formulário de Envio do Chat -->
        <div class="chat-form-box">
          <div class="chat-form-title">
            <i class="fa-solid fa-pen-to-square"></i> Deixar um comentário
          </div>
          <form id="formChatComentario" onsubmit="enviarComentario(event)">
            <input type="hidden" name="receita_id" value="<?= $receita['id'] ?>">
            
            <?php if (!$user): ?>
              <div class="chat-anon-fields">
                <div class="input-with-icon-sm">
                  <i class="fa-solid fa-user"></i>
                  <input type="text" name="nome_autor" id="chatNomeAutor" placeholder="Seu nome (ex: João Silva)" class="chat-input-name" maxlength="50">
                </div>
              </div>
            <?php endif; ?>

            <div class="chat-form-main-row">
              <!-- Perfil de quem está comentando -->
              <div class="comment-profile-icon <?= $user ? 'icon-me' : 'icon-user' ?>" style="width:38px !important; height:38px !important; font-size:1.15rem !important;" title="<?= $user ? htmlspecialchars($user['nome']) : 'Visitante' ?>">
                <i class="fa-solid fa-circle-user"></i>
              </div>
              <textarea name="comentario" id="chatComentarioTexto" placeholder="Escreva seu comentário, dúvida ou elogio sobre a receita..." rows="3" class="chat-form-textarea" required maxlength="1000"></textarea>
            </div>
            
            <div class="chat-form-footer-bar">
              <span style="font-size:0.76rem; color:var(--text-muted);">
                <span id="chatCharCount">0</span>/1000 caracteres
                <?php if (!$user): ?>
                  &bull; <a href="<?= BASE_URL ?>/login.php" style="color:var(--primary); font-weight:600;">Entrar na conta</a>
                <?php endif; ?>
              </span>
              <button type="submit" id="btnEnviarComentario" class="btn btn-primary chat-btn-submit">
                <i class="fa-solid fa-paper-plane"></i> Publicar
              </button>
            </div>
          </form>
        </div>

      </div>
    </aside>

    <!-- Coluna do Modo de Preparo (Passo a Passo) -->
    <section class="recipe-instructions">
      <div class="sidebar-box" style="padding: 32px;">
        <h2 class="box-title" style="margin-bottom: 24px;">
          <i class="fa-solid fa-kitchen-set"></i> Modo de Preparo Passo a Passo
        </h2>

        <ol class="prep-steps-list">
          <?php foreach ($passos as $index => $passo): ?>
            <li class="prep-step-item">
              <div class="step-number"><?= $index + 1 ?></div>
              <div class="step-text"><?= nl2br(htmlspecialchars($passo)) ?></div>
            </li>
          <?php endforeach; ?>
        </ol>

        <!-- Dica do Chef -->
        <div style="margin-top: 36px; padding: 20px; background: #FFF9E6; border-left: 4px solid var(--accent-gold); border-radius: var(--radius-sm);">
          <div style="display:flex; align-items:center; gap:8px; font-weight:700; color:#856404; margin-bottom:6px;">
            <i class="fa-solid fa-lightbulb"></i> Dica de Ouro Sabor em Casa
          </div>
          <p style="font-size:0.92rem; color:#856404; margin:0;">
            Para garantir o melhor resultado, separe e meça todos os ingredientes antes de acender o fogo ou ligar o forno (mise en place). Bom apetite!
          </p>
        </div>
      </div>
    </section>

  </div>

  <!-- Outras Receitas Relacionadas -->
  <?php if (!empty($relacionadas)): ?>
    <section style="margin-top: 30px; margin-bottom: 60px;">
      <h3 style="font-family:var(--font-heading); font-size:1.6rem; color:var(--dark); margin-bottom:20px;">
        Você também pode gostar:
      </h3>
      <div class="recipes-grid">
        <?php foreach ($relacionadas as $rel): ?>
          <article class="recipe-card">
            <div class="recipe-thumb">
              <img src="<?= htmlspecialchars($rel['imagem']) ?>" alt="<?= htmlspecialchars($rel['titulo']) ?>" loading="lazy">
              <span class="badge-category"><?= htmlspecialchars($rel['categoria_nome']) ?></span>
              <?php if (!empty($rel['video_url'])): ?>
                <span class="badge-video"><i class="fa-solid fa-play"></i> Vídeo</span>
              <?php endif; ?>
            </div>
            <div class="recipe-body">
              <h4 class="recipe-title">
                <a href="<?= BASE_URL ?>/receita.php?id=<?= $rel['id'] ?>">
                  <?= htmlspecialchars($rel['titulo']) ?>
                </a>
              </h4>
              <p class="recipe-desc"><?= resumirTexto($rel['descricao'], 90) ?></p>
              <div class="recipe-meta">
                <span class="meta-item"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($rel['tempo_preparo']) ?></span>
                <span class="meta-item"><i class="fa-solid fa-chart-pie"></i> <?= htmlspecialchars($rel['rendimento']) ?></span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</div>

<!-- Scripts Interativos: Curtidas e Chat de Comentários -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const baseUrl = '<?= BASE_URL ?>';
  const receitaId = <?= (int)$receita['id'] ?>;

  /* ==========================================================================
     SISTEMA INTERATIVO DE LIKES (CURTIDAS)
     ========================================================================== */
  const statBoxLike = document.getElementById('statBoxLike');
  const likeHeartIcon = document.getElementById('likeHeartIcon');
  const likeCountDisplay = document.getElementById('likeCountDisplay');
  let isLikeProcessing = false;

  function toggleLikeReceita() {
    if (isLikeProcessing) return;
    isLikeProcessing = true;

    const currentlyLiked = statBoxLike.classList.contains('liked');

    // Feedback visual instantâneo (otimista)
    statBoxLike.classList.toggle('liked');
    if (currentlyLiked) {
      likeHeartIcon.classList.remove('fa-solid');
      likeHeartIcon.classList.add('fa-regular');
    } else {
      likeHeartIcon.classList.remove('fa-regular');
      likeHeartIcon.classList.add('fa-solid');
      likeHeartIcon.classList.add('heart-pulse-anim');
      setTimeout(() => likeHeartIcon.classList.remove('heart-pulse-anim'), 480);
    }

    const formData = new FormData();
    formData.append('receita_id', receitaId);

    fetch(baseUrl + '/api/like.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      isLikeProcessing = false;
      if (data.success) {
        if (data.liked) {
          statBoxLike.classList.add('liked');
          likeHeartIcon.classList.remove('fa-regular');
          likeHeartIcon.classList.add('fa-solid');
        } else {
          statBoxLike.classList.remove('liked');
          likeHeartIcon.classList.remove('fa-solid');
          likeHeartIcon.classList.add('fa-regular');
        }
        likeCountDisplay.textContent = data.total_formatado;
      } else {
        // Reverte em caso de erro
        statBoxLike.classList.toggle('liked', currentlyLiked);
        alert(data.message || 'Erro ao registrar curtida.');
      }
    })
    .catch(err => {
      isLikeProcessing = false;
      console.error('Erro na requisição de like:', err);
      statBoxLike.classList.toggle('liked', currentlyLiked);
    });
  }

  if (statBoxLike) {
    statBoxLike.addEventListener('click', toggleLikeReceita);
    statBoxLike.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleLikeReceita();
      }
    });
  }

  /* ==========================================================================
     CHAT DE COMENTÁRIOS INTERATIVO
     ========================================================================== */
  const formChat = document.getElementById('formChatComentario');
  const chatMessagesList = document.getElementById('chatMessagesList');
  const chatEmptyState = document.getElementById('chatEmptyState');
  const chatCountBadge = document.getElementById('chatCountBadge');
  const chatTextarea = document.getElementById('chatComentarioTexto');
  const chatCharCount = document.getElementById('chatCharCount');
  const btnSendComment = document.getElementById('btnEnviarComentario');

  // Contador de caracteres do chat
  if (chatTextarea && chatCharCount) {
    chatTextarea.addEventListener('input', function() {
      chatCharCount.textContent = this.value.length;
    });
  }

  // Scroll inicial para a mensagem mais recente
  function scrollToBottom() {
    if (chatMessagesList) {
      chatMessagesList.scrollTop = chatMessagesList.scrollHeight;
    }
  }
  scrollToBottom();

  // Enviar comentário
  window.enviarComentario = function(e) {
    e.preventDefault();
    const texto = chatTextarea.value.trim();
    if (!texto) return;

    btnSendComment.disabled = true;
    btnSendComment.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    const formData = new FormData(formChat);

    fetch(baseUrl + '/api/comentarios.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      btnSendComment.disabled = false;
      btnSendComment.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';

      if (data.success) {
        if (chatEmptyState) {
          chatEmptyState.remove();
        }

        const c = data.comentario;
        const card = document.createElement('div');
        card.className = 'chat-comment-card comment-mine message-new-anim';
        card.id = 'comment-' + c.id;
        card.innerHTML = `
          <div class="comment-card-header">
            <div class="comment-user-info">
              <div class="comment-profile-icon icon-me" title="Seu perfil">
                <i class="fa-solid fa-circle-user"></i>
              </div>
              <div class="comment-user-details">
                <span class="comment-user-name">
                  ${escapeHtml(c.nome_autor)}
                  <span class="badge-tag-me"><i class="fa-solid fa-check"></i> Você</span>
                </span>
                <span class="comment-user-time">
                  <i class="fa-regular fa-clock"></i> ${escapeHtml(c.tempo_relativo)}
                </span>
              </div>
            </div>
            <button type="button" class="comment-card-delete" onclick="excluirComentario(${c.id})" title="Excluir meu comentário">
              <i class="fa-regular fa-trash-can"></i> Excluir
            </button>
          </div>
          <div class="comment-card-body">
            <p class="comment-card-text">${escapeHtml(c.comentario).replace(/\\n/g, '<br>')}</p>
          </div>
        `;

        chatMessagesList.appendChild(card);
        scrollToBottom();

        // Limpa campos
        chatTextarea.value = '';
        if (chatCharCount) chatCharCount.textContent = '0';
        if (chatCountBadge) chatCountBadge.textContent = data.total;
      } else {
        alert(data.message || 'Erro ao enviar comentário.');
      }
    })
    .catch(err => {
      btnSendComment.disabled = false;
      btnSendComment.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
      console.error('Erro ao enviar mensagem:', err);
      alert('Não foi possível conectar ao servidor. Tente novamente.');
    });
  };

  // Excluir comentário
  window.excluirComentario = function(id) {
    if (!confirm('Deseja realmente excluir este comentário?')) return;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', 'delete');
    formData.append('_method', 'DELETE');

    fetch(baseUrl + '/api/comentarios.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const card = document.getElementById('comment-' + id);
        if (card) {
          card.style.transition = 'all 0.3s ease';
          card.style.opacity = '0';
          card.style.transform = 'translateY(-10px) scale(0.96)';
          setTimeout(() => {
            card.remove();
            if (chatCountBadge) chatCountBadge.textContent = data.total;
            if (chatMessagesList && chatMessagesList.querySelectorAll('.chat-comment-card').length === 0) {
              chatMessagesList.innerHTML = `
                <div class="chat-empty-state" id="chatEmptyState">
                  <i class="fa-regular fa-comment-dots"></i>
                  <p>Nenhum comentário ainda. Seja a primeira pessoa a enviar um comentário!</p>
                </div>
              `;
            }
          }, 300);
        }
      } else {
        alert(data.message || 'Erro ao excluir comentário.');
      }
    })
    .catch(err => {
      console.error('Erro ao excluir:', err);
      alert('Erro de conexão ao excluir comentário.');
    });
  };

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
