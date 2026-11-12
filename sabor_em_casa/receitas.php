<?php
/**
 * Catálogo e Busca de Receitas
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$db = getDb();

// Filtros
$busca = trim($_GET['busca'] ?? '');
$categoriaSlug = trim($_GET['categoria'] ?? '');
$apenasComVideo = !empty($_GET['com_video']);
$ordem = $_GET['ordem'] ?? 'recentes';

// Categorias para o filtro
$stmtCat = $db->query("SELECT * FROM categorias ORDER BY nome ASC");
$categorias = $stmtCat->fetchAll();

// Monta query dinâmica
$sql = "
    SELECT r.*, c.nome AS categoria_nome, c.slug AS categoria_slug, u.nome AS autor_nome, u.usuario AS autor_usuario, u.avatar AS autor_avatar
    FROM receitas r
    JOIN categorias c ON r.categoria_id = c.id
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE 1=1
";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (r.titulo LIKE ? OR r.descricao LIKE ? OR r.ingredientes LIKE ?)";
    $termo = "%{$busca}%";
    $params[] = $termo;
    $params[] = $termo;
    $params[] = $termo;
}

if (!empty($categoriaSlug)) {
    $sql .= " AND c.slug = ?";
    $params[] = $categoriaSlug;
}

if ($apenasComVideo) {
    $sql .= " AND r.video_url IS NOT NULL AND r.video_url != ''";
}

if ($ordem === 'visualizacoes') {
    $sql .= " ORDER BY r.visualizacoes DESC, r.id DESC";
} else {
    $sql .= " ORDER BY r.id DESC";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$receitas = $stmt->fetchAll();

$pageTitle = !empty($busca) ? "Busca por '{$busca}' - Sabor em Casa" : 'Catálogo Completo de Receitas - Sabor em Casa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 36px; padding-bottom: 70px;">
  
  <div style="margin-bottom: 28px;">
    <h1 class="section-title">
      <?php if (!empty($busca)): ?>
        Resultados para: "<?= htmlspecialchars($busca) ?>"
      <?php elseif ($apenasComVideo): ?>
        Receitas em Vídeo
      <?php else: ?>
        Catálogo de Receitas
      <?php endif; ?>
    </h1>
    <p class="section-subtitle">
      Encontrada<?= count($receitas) == 1 ? '' : 's' ?> <?= count($receitas) ?> receita<?= count($receitas) == 1 ? '' : 's' ?> deliciosa<?= count($receitas) == 1 ? '' : 's' ?>
    </p>
  </div>

  <!-- Barra de Filtros Responsiva -->
  <div style="background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 32px; box-shadow: var(--shadow-sm);">
    <form action="<?= BASE_URL ?>/receitas.php" method="GET" style="display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
      
      <div style="flex: 2; min-width: 220px; position:relative;">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-light);"></i>
        <input type="text" name="busca" class="form-control" style="padding-left: 38px;" placeholder="Pesquisar por ingrediente, título..." value="<?= htmlspecialchars($busca) ?>">
      </div>

      <div style="flex: 1; min-width: 180px;">
        <select name="categoria" class="form-control">
          <option value="">Todas as Categorias</option>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $categoriaSlug === $cat['slug'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display:flex; align-items:center; gap:8px;">
        <input type="checkbox" id="check_video" name="com_video" value="1" <?= $apenasComVideo ? 'checked' : '' ?> style="accent-color:var(--primary); width:18px; height:18px;">
        <label for="check_video" style="font-size:0.92rem; font-weight:600; cursor:pointer;">
          <i class="fa-solid fa-circle-play" style="color:#e63946;"></i> Somente com Vídeo
        </label>
      </div>

      <div style="display:flex; gap:10px;">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-filter"></i> Filtrar
        </button>
        <?php if (!empty($busca) || !empty($categoriaSlug) || $apenasComVideo): ?>
          <a href="<?= BASE_URL ?>/receitas.php" class="btn btn-secondary btn-sm" title="Limpar Filtros">
            Limpar
          </a>
        <?php endif; ?>
      </div>

    </form>
  </div>

  <!-- Listagem de Resultados -->
  <?php if (empty($receitas)): ?>
    <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:var(--radius-lg); border:1px dashed var(--border-color);">
      <div style="font-size: 3rem; color: var(--text-light); margin-bottom: 16px;">
        <i class="fa-solid fa-cookie-bite"></i>
      </div>
      <h3 style="font-family:var(--font-heading); font-size:1.4rem; color:var(--dark); margin-bottom:8px;">Nenhuma receita encontrada</h3>
      <p style="color:var(--text-muted); max-width:440px; margin:0 auto 20px;">Tente pesquisar por outros ingredientes ou limpe os filtros para navegar por todas as receitas disponíveis.</p>
      <a href="<?= BASE_URL ?>/receitas.php" class="btn btn-secondary">Ver todas as receitas</a>
    </div>
  <?php else: ?>
    <div class="recipes-grid">
      <?php foreach ($receitas as $receita): ?>
        <article class="recipe-card">
          <div class="recipe-thumb">
            <img src="<?= htmlspecialchars($receita['imagem']) ?>" alt="<?= htmlspecialchars($receita['titulo']) ?>" loading="lazy">
            <span class="badge-category"><?= htmlspecialchars($receita['categoria_nome']) ?></span>
            <?php if (!empty($receita['video_url'])): ?>
              <span class="badge-video">
                <i class="fa-solid fa-play"></i> Vídeo
              </span>
            <?php endif; ?>
          </div>

          <div class="recipe-body">
            <h3 class="recipe-title">
              <a href="<?= BASE_URL ?>/receita.php?id=<?= $receita['id'] ?>">
                <?= htmlspecialchars($receita['titulo']) ?>
              </a>
            </h3>
            <p class="recipe-desc"><?= resumirTexto($receita['descricao'], 110) ?></p>

            <div class="recipe-meta">
              <span class="meta-item"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($receita['tempo_preparo']) ?></span>
              <span class="meta-item"><i class="fa-solid fa-chart-pie"></i> <?= htmlspecialchars($receita['rendimento']) ?></span>
              <span class="meta-item"><i class="fa-solid fa-gauge-high"></i> <?= htmlspecialchars($receita['dificuldade']) ?></span>
            </div>

            <div class="recipe-author">
              <img src="<?= htmlspecialchars($receita['autor_avatar']) ?>" alt="<?= htmlspecialchars($receita['autor_nome']) ?>" class="author-mini-avatar">
              <span class="author-mini-name">Por <?= htmlspecialchars($receita['autor_nome']) ?></span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
