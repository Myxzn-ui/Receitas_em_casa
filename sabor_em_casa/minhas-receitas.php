<?php
/**
 * Painel do Usuário / Minhas Receitas
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

requireAuth();

$user = currentUser();
$db = getDb();

// Busca as receitas publicadas por este usuário
$stmt = $db->prepare("
    SELECT r.*, c.nome AS categoria_nome 
    FROM receitas r
    JOIN categorias c ON r.categoria_id = c.id
    WHERE r.usuario_id = ?
    ORDER BY r.id DESC
");
$stmt->execute([$user['id']]);
$minhasReceitas = $stmt->fetchAll();

// Estatísticas
$totalReceitas = count($minhasReceitas);
$totalVisualizacoes = array_sum(array_column($minhasReceitas, 'visualizacoes'));

$pageTitle = 'Minhas Receitas - Sabor em Casa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 36px; padding-bottom: 70px;">
  
  <!-- Cartão de Perfil do Usuário com Nome e Idade -->
  <div class="profile-header-card">
    <img src="<?= htmlspecialchars($user['avatar'] ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80') ?>" alt="<?= htmlspecialchars($user['nome']) ?>" class="profile-avatar-lg">
    
    <div class="profile-info" style="flex: 1;">
      <h2><?= htmlspecialchars($user['nome']) ?></h2>
      <p style="color:var(--text-muted); font-size:0.95rem; margin-top:2px;">
        @<?= htmlspecialchars($user['usuario']) ?> &bull; <?= htmlspecialchars($user['email']) ?>
      </p>
      
      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:8px;">
        <span class="profile-badge-age">
          <i class="fa-solid fa-cake-candles"></i> <?= $user['idade'] ?> anos (nascido(a) em <?= dataFormatada($user['data_nascimento']) ?>)
        </span>
        <span style="font-size:0.85rem; color:var(--text-muted);">
          Membro desde <?= dataFormatada($user['criado_em']) ?>
        </span>
      </div>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/configuracoes.php" class="btn btn-secondary">
        <i class="fa-solid fa-gear"></i> Configurações
      </a>
      <a href="<?= BASE_URL ?>/publicar.php" class="btn btn-primary">
        <i class="fa-solid fa-plus-circle"></i> Publicar Receita
      </a>
    </div>
  </div>

  <!-- Resumo de Estatísticas -->
  <div class="stats-banner" style="grid-template-columns: repeat(2, 1fr); max-width: 600px; margin-bottom: 36px;">
    <div class="stat-box">
      <div class="stat-icon">
        <i class="fa-solid fa-utensils"></i>
      </div>
      <div>
        <span class="stat-label">Receitas Publicadas</span>
        <span class="stat-value"><?= $totalReceitas ?> receita<?= $totalReceitas == 1 ? '' : 's' ?></span>
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-icon" style="background:#E8F7F5; color:var(--accent-green);">
        <i class="fa-solid fa-chart-line"></i>
      </div>
      <div>
        <span class="stat-label">Alcance Total</span>
        <span class="stat-value"><?= number_format($totalVisualizacoes, 0, ',', '.') ?> visualizações</span>
      </div>
    </div>
  </div>

  <!-- Listagem das Minhas Receitas -->
  <div class="section-header">
    <div>
      <h3 class="section-title">Minhas Receitas Cadastradas</h3>
      <p class="section-subtitle">Gerencie os pratos que você compartilhou com a comunidade</p>
    </div>
  </div>

  <?php if (empty($minhasReceitas)): ?>
    <div style="text-align:center; padding:50px 20px; background:#fff; border-radius:var(--radius-lg); border:1px dashed var(--border-color);">
      <div style="font-size:2.8rem; color:var(--text-light); margin-bottom:12px;">
        <i class="fa-solid fa-bowl-food"></i>
      </div>
      <h4 style="font-family:var(--font-heading); font-size:1.3rem; color:var(--dark); margin-bottom:6px;">Você ainda não publicou nenhuma receita</h4>
      <p style="color:var(--text-muted); margin-bottom:20px;">Que tal compartilhar aquele prato que você domina como ninguém?</p>
      <a href="<?= BASE_URL ?>/publicar.php" class="btn btn-primary">
        <i class="fa-solid fa-plus-circle"></i> Publicar Minha Primeira Receita
      </a>
    </div>
  <?php else: ?>
    <div class="recipes-grid">
      <?php foreach ($minhasReceitas as $receita): ?>
        <article class="recipe-card">
          <div class="recipe-thumb">
            <img src="<?= htmlspecialchars($receita['imagem']) ?>" alt="<?= htmlspecialchars($receita['titulo']) ?>">
            <span class="badge-category"><?= htmlspecialchars($receita['categoria_nome']) ?></span>
            <?php if (!empty($receita['video_url'])): ?>
              <span class="badge-video"><i class="fa-solid fa-play"></i> Vídeo</span>
            <?php endif; ?>
          </div>

          <div class="recipe-body">
            <h4 class="recipe-title">
              <a href="<?= BASE_URL ?>/receita.php?id=<?= $receita['id'] ?>">
                <?= htmlspecialchars($receita['titulo']) ?>
              </a>
            </h4>
            <p class="recipe-desc"><?= resumirTexto($receita['descricao'], 90) ?></p>

            <div class="recipe-meta" style="margin-bottom:14px;">
              <span class="meta-item"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($receita['tempo_preparo']) ?></span>
              <span class="meta-item"><i class="fa-solid fa-eye"></i> <?= $receita['visualizacoes'] ?> views</span>
            </div>

            <div style="display:flex; gap:10px; margin-top:auto;">
              <a href="<?= BASE_URL ?>/receita.php?id=<?= $receita['id'] ?>" class="btn btn-secondary btn-sm" style="flex:1;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver
              </a>
              <a href="<?= BASE_URL ?>/excluir-receita.php?id=<?= $receita['id'] ?>" class="btn btn-sm" style="background:#FDF1EE; color:#B93E22; border-color:#fad5cd;" onclick="return confirm('Tem certeza que deseja excluir esta receita permanentemente?')">
                <i class="fa-solid fa-trash"></i> Excluir
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
