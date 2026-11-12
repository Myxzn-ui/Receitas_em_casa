<?php
/**
 * Página Inicial (Home)
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$db = getDb();

// Busca categorias com contagem de receitas
$stmtCat = $db->query("
    SELECT c.*, COUNT(r.id) AS total_receitas 
    FROM categorias c 
    LEFT JOIN receitas r ON c.id = r.categoria_id 
    GROUP BY c.id 
    ORDER BY c.id ASC
");
$categorias = $stmtCat->fetchAll();

// Busca receitas em destaque / com vídeo
$stmtVideos = $db->query("
    SELECT r.*, c.nome AS categoria_nome, c.slug AS categoria_slug, u.nome AS autor_nome, u.usuario AS autor_usuario, u.avatar AS autor_avatar
    FROM receitas r
    JOIN categorias c ON r.categoria_id = c.id
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.video_url IS NOT NULL AND r.video_url != ''
    ORDER BY r.destaque DESC, r.id DESC
    LIMIT 3
");
$receitasVideo = $stmtVideos->fetchAll();

// Busca receitas recentes
$stmtRecentes = $db->query("
    SELECT r.*, c.nome AS categoria_nome, c.slug AS categoria_slug, u.nome AS autor_nome, u.usuario AS autor_usuario, u.avatar AS autor_avatar
    FROM receitas r
    JOIN categorias c ON r.categoria_id = c.id
    JOIN usuarios u ON r.usuario_id = u.id
    ORDER BY r.id DESC
    LIMIT 6
");
$receitasRecentes = $stmtRecentes->fetchAll();

$pageTitle = 'Sabor em Casa - Receitas Caseiras em Texto e Vídeo';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="hero-grid">
      
      <div class="hero-content">
        <div class="hero-badge">
          <i class="fa-solid fa-fire"></i> O seu cantinho gastronômico na web
        </div>
        <h1 class="hero-title">
          Cozinhar em casa é <span>puro afeto</span> e sabor.
        </h1>
        <p class="hero-subtitle">
          Descubra receitas completas em texto e assista a vídeos passo a passo explicativos. Do celular ao computador, o guia perfeito para o seu almoço, jantar ou sobremesa!
        </p>

        <!-- Formulário de Pesquisa Rápida -->
        <form action="<?= BASE_URL ?>/receitas.php" method="GET" class="search-form">
          <div class="search-input-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="busca" class="search-input" placeholder="O que você quer cozinhar hoje? (ex: bolo, lasanha...)" required>
          </div>
          <button type="submit" class="btn btn-primary search-btn">
            <span>Buscar</span> <i class="fa-solid fa-arrow-right"></i>
          </button>
        </form>
      </div>

      <div class="hero-visual">
        <div class="hero-image-card">
          <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&auto=format&fit=crop&q=80" alt="Prato saboroso Sabor em Casa">
        </div>
        <div class="hero-floating-badge">
          <div class="floating-icon">
            <i class="fa-solid fa-circle-play"></i>
          </div>
          <div>
            <strong style="display:block; font-size: 0.95rem; color:var(--dark);">Passo a passo em Vídeo</strong>
            <span style="font-size: 0.8rem; color: var(--text-muted);">Aulas e tutoriais práticos</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Categorias -->
<section class="categories-section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Explore por Categorias</h2>
        <p class="section-subtitle">Escolha o estilo de prato ideal para qualquer momento do seu dia</p>
      </div>
      <a href="<?= BASE_URL ?>/receitas.php" class="btn btn-secondary btn-sm" style="display: inline-flex;">
        Ver todas <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
      </a>
    </div>

    <div class="categories-grid">
      <?php foreach ($categorias as $cat): ?>
        <a href="<?= BASE_URL ?>/receitas.php?categoria=<?= urlencode($cat['slug']) ?>" class="category-card">
          <div class="category-icon">
            <i class="fa-solid <?= htmlspecialchars($cat['icone']) ?>"></i>
          </div>
          <div class="category-name"><?= htmlspecialchars($cat['nome']) ?></div>
          <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 500;">
            <?= $cat['total_receitas'] ?> receita<?= $cat['total_receitas'] == 1 ? '' : 's' ?>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Receitas em Vídeo -->
<?php if (!empty($receitasVideo)): ?>
<section class="recipes-section" style="background-color: #FAF6EE; padding-top: 50px;">
  <div class="container">
    <div class="section-header">
      <div>
        <div style="display:inline-flex; align-items:center; gap:6px; color:#e63946; font-weight:700; font-size:0.85rem; text-transform:uppercase; margin-bottom:4px;">
          <i class="fa-solid fa-circle-play"></i> Tutoriais Visuais
        </div>
        <h2 class="section-title">Receitas em Vídeo</h2>
        <p class="section-subtitle">Assista e aprenda cada detalhe do preparo com nossos vídeos culinários</p>
      </div>
      <a href="<?= BASE_URL ?>/receitas.php?com_video=1" class="btn btn-secondary btn-sm">
        Ver todos os vídeos <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
      </a>
    </div>

    <div class="recipes-grid">
      <?php foreach ($receitasVideo as $receita): ?>
        <article class="recipe-card">
          <div class="recipe-thumb">
            <img src="<?= htmlspecialchars($receita['imagem']) ?>" alt="<?= htmlspecialchars($receita['titulo']) ?>" loading="lazy">
            <span class="badge-category"><?= htmlspecialchars($receita['categoria_nome']) ?></span>
            <span class="badge-video">
              <i class="fa-solid fa-play"></i> Vídeo
            </span>
            <a href="<?= BASE_URL ?>/receita.php?id=<?= $receita['id'] ?>" class="play-overlay" aria-label="Assistir receita em vídeo">
              <div class="play-circle">
                <i class="fa-solid fa-play"></i>
              </div>
            </a>
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
              <span class="meta-item" style="color: var(--accent-green);"><i class="fa-solid fa-gauge-high"></i> <?= htmlspecialchars($receita['dificuldade']) ?></span>
            </div>

            <div class="recipe-author">
              <img src="<?= htmlspecialchars($receita['autor_avatar']) ?>" alt="<?= htmlspecialchars($receita['autor_nome']) ?>" class="author-mini-avatar">
              <span class="author-mini-name">Por <?= htmlspecialchars($receita['autor_nome']) ?></span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Todas as Receitas Recentes -->
<section class="recipes-section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Últimas Receitas Publicadas</h2>
        <p class="section-subtitle">As novidades preparadas e compartilhadas pela nossa comunidade</p>
      </div>
      <a href="<?= BASE_URL ?>/receitas.php" class="btn btn-secondary btn-sm">
        Ver catálogo completo <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
      </a>
    </div>

    <div class="recipes-grid">
      <?php foreach ($receitasRecentes as $receita): ?>
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

    <!-- Banner CTA para Publicar -->
    <div style="margin-top: 60px;">
      <div class="cta-banner">
        <div class="cta-content">
          <h2 class="cta-title">Você também tem uma receita especial de família?</h2>
          <p class="cta-desc">Cadastre-se no Sabor em Casa e compartilhe suas criações gastronômicas com fotos, ingredientes, modo de fazer e links de vídeo com milhares de pessoas.</p>
          <div style="display:flex; gap:14px; flex-wrap:wrap;">
            <?php if (isLoggedIn()): ?>
              <a href="<?= BASE_URL ?>/publicar.php" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-plus-circle"></i> Publicar Minha Receita
              </a>
            <?php else: ?>
              <a href="<?= BASE_URL ?>/cadastro.php" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-user-plus"></i> Criar Conta Gratuita
              </a>
              <button type="button" class="btn btn-secondary btn-lg" onclick="abrirModalGoogle()">
                Entrar com Google
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
