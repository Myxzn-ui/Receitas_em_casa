<?php
/**
 * API - Comentários e Chat da Receita
 * Sabor em Casa
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$db = getDb();
$user = currentUser();

// 1. DELETE / EXCLUSÃO DE COMENTÁRIO
// Verificado primeiro para capturar POST com action=delete ou _method=DELETE
$isDelete = ($_SERVER['REQUEST_METHOD'] === 'DELETE')
    || (isset($_POST['_method']) && strtoupper($_POST['_method']) === 'DELETE')
    || (isset($_POST['action']) && $_POST['action'] === 'delete')
    || (isset($_GET['action']) && $_GET['action'] === 'delete');

if ($isDelete) {
    $comentarioId = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
    if ($comentarioId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do comentário inválido']);
        exit;
    }

    // Busca comentário e dados da receita associada
    $stmtCheck = $db->prepare("
        SELECT c.*, r.usuario_id as receita_autor_id 
        FROM comentarios c
        JOIN receitas r ON c.receita_id = r.id
        WHERE c.id = ?
    ");
    $stmtCheck->execute([$comentarioId]);
    $comm = $stmtCheck->fetch();

    if (!$comm) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Comentário não encontrado']);
        exit;
    }

    $canDelete = false;
    if ($user) {
        $myId = (int)$user['id'];
        $commentAuthorId = (int)($comm['usuario_id'] ?? 0);
        $recipeAuthorId = (int)($comm['receita_autor_id'] ?? 0);

        // Permissão: autor do comentário, autor da receita, ou administrador (id=1)
        if ($commentAuthorId === $myId || $recipeAuthorId === $myId || $myId === 1) {
            $canDelete = true;
        }
    } else {
        // Se for comentário sem usuário vinculado (visitante anônimo), permite exclusão
        if (empty($comm['usuario_id'])) {
            $canDelete = true;
        }
    }

    if (!$canDelete) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Você não tem permissão para excluir este comentário.']);
        exit;
    }

    $db->prepare("DELETE FROM comentarios WHERE id = ?")->execute([$comentarioId]);

    $stmtTotal = $db->prepare("SELECT COUNT(*) FROM comentarios WHERE receita_id = ?");
    $stmtTotal->execute([$comm['receita_id']]);
    $totalCount = (int)$stmtTotal->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => 'Comentário excluído com sucesso!',
        'total' => $totalCount
    ]);
    exit;
}

// 2. GET: Retorna lista de comentários da receita
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $receitaId = (int)($_GET['receita_id'] ?? 0);
    if ($receitaId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Receita inválida']);
        exit;
    }

    $stmt = $db->prepare("
        SELECT c.*, u.usuario, u.nome as user_nome, u.avatar as user_avatar
        FROM comentarios c
        LEFT JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.receita_id = ?
        ORDER BY c.criado_em ASC
    ");
    $stmt->execute([$receitaId]);
    $lista = $stmt->fetchAll();

    $comentarios = [];
    foreach ($lista as $item) {
        $nome = !empty($item['user_nome']) ? $item['user_nome'] : $item['nome_autor'];
        $avatar = !empty($item['user_avatar']) ? $item['user_avatar'] : $item['avatar_autor'];
        if (empty($avatar)) {
            $avatar = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80';
        }

        $isMe = ($user && (int)$user['id'] === (int)$item['usuario_id']);

        $comentarios[] = [
            'id' => (int)$item['id'],
            'usuario_id' => (int)($item['usuario_id'] ?? 0),
            'nome_autor' => $nome,
            'avatar_autor' => $avatar,
            'comentario' => htmlspecialchars($item['comentario']),
            'criado_em' => $item['criado_em'],
            'tempo_relativo' => tempoRelativo($item['criado_em']),
            'is_me' => $isMe
        ];
    }

    echo json_encode([
        'success' => true,
        'total' => count($comentarios),
        'comentarios' => $comentarios
    ]);
    exit;
}

// 3. POST: Adiciona novo comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receitaId = (int)($_POST['receita_id'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');
    $nomeAutor = trim($_POST['nome_autor'] ?? '');

    if ($receitaId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Receita inválida']);
        exit;
    }

    if (empty($comentario)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Por favor, escreva uma mensagem no comentário.']);
        exit;
    }

    if (mb_strlen($comentario) > 1000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'O comentário deve ter no máximo 1000 caracteres.']);
        exit;
    }

    // Verifica se receita existe
    $stmtRec = $db->prepare("SELECT id FROM receitas WHERE id = ?");
    $stmtRec->execute([$receitaId]);
    if (!$stmtRec->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Receita não encontrada']);
        exit;
    }

    if ($user) {
        $userId = (int)$user['id'];
        $nome = $user['nome'];
        $avatar = $user['avatar'] ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80';
    } else {
        $userId = null;
        $nome = !empty($nomeAutor) ? htmlspecialchars(strip_tags($nomeAutor)) : 'Cozinheiro(a) Anônimo';
        $avatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop&q=80';
    }

    $stmtInsert = $db->prepare("
        INSERT INTO comentarios (receita_id, usuario_id, nome_autor, avatar_autor, comentario)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([$receitaId, $userId, $nome, $avatar, $comentario]);
    $novoId = (int)$db->lastInsertId();

    // Contagem total
    $stmtTotal = $db->prepare("SELECT COUNT(*) FROM comentarios WHERE receita_id = ?");
    $stmtTotal->execute([$receitaId]);
    $totalCount = (int)$stmtTotal->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => 'Comentário enviado com sucesso!',
        'total' => $totalCount,
        'comentario' => [
            'id' => $novoId,
            'usuario_id' => $userId,
            'nome_autor' => $nome,
            'avatar_autor' => $avatar,
            'comentario' => htmlspecialchars($comentario),
            'tempo_relativo' => 'Agora mesmo',
            'is_me' => true
        ]
    ]);
    exit;
}
