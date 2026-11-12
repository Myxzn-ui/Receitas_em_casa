<?php
/**
 * Excluir Receita
 * Sabor em Casa
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

$id = (int)($_GET['id'] ?? 0);
$user = currentUser();

if ($id > 0) {
    $db = getDb();
    
    // Verifica propriedade da receita
    $stmt = $db->prepare("SELECT id, titulo FROM receitas WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $user['id']]);
    $receita = $stmt->fetch();

    if ($receita) {
        $stmtDelete = $db->prepare("DELETE FROM receitas WHERE id = ?");
        $stmtDelete->execute([$id]);
        setFlash('success', 'A receita "' . htmlspecialchars($receita['titulo']) . '" foi excluída com sucesso.');
    } else {
        setFlash('danger', 'Você não tem permissão para excluir esta receita ou ela não existe.');
    }
}

header('Location: ' . BASE_URL . '/minhas-receitas.php');
exit;
