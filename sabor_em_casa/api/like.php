<?php
/**
 * API - Alternar Curtida (Like) de Receita
 * Sabor em Casa
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$receitaId = (int)($_POST['receita_id'] ?? 0);
if ($receitaId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da receita inválido']);
    exit;
}

$db = getDb();

// Verifica se a receita existe
$stmt = $db->prepare("SELECT id, likes FROM receitas WHERE id = ?");
$stmt->execute([$receitaId]);
$receita = $stmt->fetch();

if (!$receita) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Receita não encontrada']);
    exit;
}

$user = currentUser();
$userId = $user ? (int)$user['id'] : null;

// Garante token de sessão para visitantes
if (empty($_SESSION['guest_like_token'])) {
    $_SESSION['guest_like_token'] = bin2hex(random_bytes(16));
}
$sessaoToken = $_SESSION['guest_like_token'];

// Verifica se já curtiu
if ($userId) {
    $stmtLike = $db->prepare("SELECT id FROM receitas_likes WHERE receita_id = ? AND (usuario_id = ? OR sessao_token = ?)");
    $stmtLike->execute([$receitaId, $userId, $sessaoToken]);
} else {
    $stmtLike = $db->prepare("SELECT id FROM receitas_likes WHERE receita_id = ? AND sessao_token = ?");
    $stmtLike->execute([$receitaId, $sessaoToken]);
}
$existingLike = $stmtLike->fetch();

if ($existingLike) {
    // Remover curtida (Descurtir)
    $stmtDel = $db->prepare("DELETE FROM receitas_likes WHERE id = ?");
    $stmtDel->execute([$existingLike['id']]);

    $db->prepare("UPDATE receitas SET likes = GREATEST(0, likes - 1) WHERE id = ?")->execute([$receitaId]);
    $liked = false;
    $msg = 'Curtida removida.';
} else {
    // Adicionar curtida
    $stmtIns = $db->prepare("INSERT INTO receitas_likes (receita_id, usuario_id, sessao_token) VALUES (?, ?, ?)");
    $stmtIns->execute([$receitaId, $userId, $sessaoToken]);

    $db->prepare("UPDATE receitas SET likes = likes + 1 WHERE id = ?")->execute([$receitaId]);
    $liked = true;
    $msg = 'Receita favoritada com sucesso!';
}

// Busca contagem atualizada
$stmtCount = $db->prepare("SELECT likes FROM receitas WHERE id = ?");
$stmtCount->execute([$receitaId]);
$totalLikes = (int)$stmtCount->fetchColumn();

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'total_likes' => $totalLikes,
    'total_formatado' => number_format($totalLikes, 0, ',', '.') . ' curtidas',
    'message' => $msg
]);
exit;
