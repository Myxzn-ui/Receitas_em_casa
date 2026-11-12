<?php
/**
 * Publicar Nova Receita
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Exige autenticação
requireAuth();

$user = currentUser();
$db = getDb();

// Busca categorias ativas
$stmtCat = $db->query("SELECT * FROM categorias ORDER BY nome ASC");
$categorias = $stmtCat->fetchAll();

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $categoriaId = (int)($_POST['categoria_id'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $tempoPreparo = trim($_POST['tempo_preparo'] ?? '');
    $rendimento = trim($_POST['rendimento'] ?? '');
    $dificuldade = trim($_POST['dificuldade'] ?? 'Médio');
    $videoUrl = trim($_POST['video_url'] ?? '');
    $imagemUrl = trim($_POST['imagem_url'] ?? '');
    $ingredientes = trim($_POST['ingredientes'] ?? '');
    $modoPreparo = trim($_POST['modo_preparo'] ?? '');

    // Validações
    if (empty($titulo)) {
        $erros[] = 'Por favor, informe o título da receita.';
    }

    if ($categoriaId <= 0) {
        $erros[] = 'Selecione uma categoria válida.';
    }

    if (empty($tempoPreparo)) {
        $erros[] = 'Informe o tempo estimado de preparo (ex: 45 min).';
    }

    if (empty($rendimento)) {
        $erros[] = 'Informe o rendimento (ex: 4 porções).';
    }

    if (empty($ingredientes)) {
        $erros[] = 'Informe a lista de ingredientes.';
    }

    if (empty($modoPreparo)) {
        $erros[] = 'Descreva o modo de preparo passo a passo.';
    }

    // Processamento da imagem
    $imagemFinal = 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=800&auto=format&fit=crop&q=80';

    // Se enviou arquivo de imagem
    if (isset($_FILES['imagem_arquivo']) && $_FILES['imagem_arquivo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagem_arquivo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        
        if (in_array($file['type'], $allowedTypes)) {
            $uploadsDir = __DIR__ . '/uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'receita_' . uniqid() . '.' . $ext;
            $destPath = $uploadsDir . '/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $imagemFinal = BASE_URL . '/uploads/' . $fileName;
            }
        }
    } elseif (!empty($imagemUrl)) {
        $imagemFinal = $imagemUrl;
    }

    if (empty($erros)) {
        $slug = slugify($titulo) . '-' . rand(100, 999);

        $stmtInsert = $db->prepare("
            INSERT INTO receitas (
                usuario_id, categoria_id, titulo, slug, descricao, tempo_preparo, 
                rendimento, dificuldade, ingredientes, modo_preparo, video_url, imagem
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmtInsert->execute([
            $user['id'],
            $categoriaId,
            $titulo,
            $slug,
            $descricao ?: 'Deliciosa receita caseira enviada por ' . $user['nome'],
            $tempoPreparo,
            $rendimento,
            $dificuldade,
            $ingredientes,
            $modoPreparo,
            $videoUrl ?: null,
            $imagemFinal
        ]);

        $novoId = $db->lastInsertId();
        setFlash('success', 'Parabéns! Sua receita foi publicada e já está disponível para toda a comunidade!');
        header("Location: " . BASE_URL . "/receita.php?id=" . $novoId);
        exit;
    }
}

$pageTitle = 'Publicar Nova Receita - Sabor em Casa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container publish-wrapper">
  
  <div style="margin-bottom: 24px;">
    <h1 class="section-title">
      <i class="fa-solid fa-cloud-arrow-up" style="color:var(--primary);"></i> Compartilhe sua Receita
    </h1>
    <p class="section-subtitle">
      Preencha os detalhes abaixo para que outros cozinheiros e famílias possam preparar seu prato com perfeição.
    </p>
  </div>

  <?php if (!empty($erros)): ?>
    <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start;">
      <div style="font-weight: 700; margin-bottom: 4px;">Por favor, corrija os seguintes itens:</div>
      <ul style="padding-left: 20px; font-size: 0.9rem;">
        <?php foreach ($erros as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="publish-card">
    <form action="<?= BASE_URL ?>/publicar.php" method="POST" enctype="multipart/form-data">
      
      <!-- Informações Principais -->
      <div class="form-group">
        <label for="titulo" class="form-label">Título da Receita *</label>
        <input type="text" id="titulo" name="titulo" class="form-control" placeholder="ex: Bolo de Laranja Fofinho da Vovó" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>" required>
      </div>

      <div class="publish-grid-3">
        <div class="form-group">
          <label for="categoria_id" class="form-label">Categoria *</label>
          <select id="categoria_id" name="categoria_id" class="form-control" required>
            <option value="">Selecione uma Categoria</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="tempo_preparo" class="form-label">Tempo de Preparo *</label>
          <input type="text" id="tempo_preparo" name="tempo_preparo" class="form-control" placeholder="ex: 45 min" value="<?= htmlspecialchars($_POST['tempo_preparo'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label for="rendimento" class="form-label">Rendimento *</label>
          <input type="text" id="rendimento" name="rendimento" class="form-control" placeholder="ex: 6 porções" value="<?= htmlspecialchars($_POST['rendimento'] ?? '') ?>" required>
        </div>
      </div>

      <div class="publish-grid-2">
        <div class="form-group">
          <label for="dificuldade" class="form-label">Nível de Dificuldade</label>
          <select id="dificuldade" name="dificuldade" class="form-control">
            <option value="Fácil" <?= (isset($_POST['dificuldade']) && $_POST['dificuldade'] == 'Fácil') ? 'selected' : '' ?>>Fácil</option>
            <option value="Médio" <?= (!isset($_POST['dificuldade']) || $_POST['dificuldade'] == 'Médio') ? 'selected' : '' ?>>Médio</option>
            <option value="Difícil" <?= (isset($_POST['dificuldade']) && $_POST['dificuldade'] == 'Difícil') ? 'selected' : '' ?>>Difícil</option>
          </select>
        </div>

        <div class="form-group">
          <label for="descricao" class="form-label">Breve Descrição / História do Prato</label>
          <input type="text" id="descricao" name="descricao" class="form-control" placeholder="Uma breve apresentação apaixonante do seu prato..." value="<?= htmlspecialchars($_POST['descricao'] ?? '') ?>">
        </div>
      </div>

      <!-- Mídia: Vídeo e Foto -->
      <div style="background: #FAF8F5; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 22px; margin: 24px 0;">
        <h3 style="font-family:var(--font-heading); font-size:1.15rem; color:var(--dark); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
          <i class="fa-solid fa-video" style="color:#e63946;"></i> Receita em Vídeo & Foto de Capa
        </h3>

        <div class="publish-grid-2">
          <div class="form-group">
            <label for="video_url" class="form-label">Link do Vídeo (YouTube ou Vimeo)</label>
            <input type="url" id="video_url" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>">
            <span class="form-hint">O vídeo será exibido em player de alta definição na página da receita.</span>
            
            <div id="previewVideoBox" style="margin-top:12px; display:none;"></div>
          </div>

          <div class="form-group">
            <label for="imagem_url" class="form-label">Foto do Prato (URL ou Upload)</label>
            <input type="url" id="imagem_url" name="imagem_url" class="form-control" placeholder="Link de imagem na internet (ex: https://...)" value="<?= htmlspecialchars($_POST['imagem_url'] ?? '') ?>" style="margin-bottom:8px;">
            <input type="file" id="imagem_arquivo" name="imagem_arquivo" accept="image/*" class="form-control">
            <span class="form-hint">Dê preferência para fotos na horizontal bem iluminadas.</span>

            <div id="previewImagemBox" class="media-preview-box">
              <span style="color:#A0AEC0">Pré-visualização da imagem aparecerá aqui</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Ingredientes e Modo de Preparo -->
      <div class="publish-grid-2">
        <div class="form-group">
          <label for="ingredientes" class="form-label">Ingredientes (um por linha) *</label>
          <textarea id="ingredientes" name="ingredientes" class="form-control" style="min-height: 220px;" placeholder="- 2 xícaras de farinha de trigo&#10;- 3 ovos&#10;- 1 xícara de leite&#10;- 1 colher (sopa) de fermento" required><?= htmlspecialchars($_POST['ingredientes'] ?? '') ?></textarea>
          <span class="form-hint">Dica: Você pode criar seções escrevendo em maiúsculas (ex: PARA A COBERTURA:)</span>
        </div>

        <div class="form-group">
          <label for="modo_preparo" class="form-label">Modo de Preparo Passo a Passo *</label>
          <textarea id="modo_preparo" name="modo_preparo" class="form-control" style="min-height: 220px;" placeholder="1. Em uma tigela grande, bata os ovos...&#10;2. Adicione a farinha aos poucos...&#10;3. Leve ao forno pré-aquecido a 180°C..." required><?= htmlspecialchars($_POST['modo_preparo'] ?? '') ?></textarea>
          <span class="form-hint">Descreva cada etapa separando por linhas para gerar os passos numerados automáticos.</span>
        </div>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:14px; margin-top:28px; padding-top:20px; border-top:1px solid var(--border-color);">
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="fa-solid fa-check"></i> Publicar Receita Agora
        </button>
      </div>

    </form>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
