<?php
/**
 * Funções de Autenticação e Sessão
 * Sabor em Casa
 */

require_once __DIR__ . '/../config/db.php';

function isLoggedIn(): bool {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    
    static $user = null;
    if ($user === null) {
        $db = getDb();
        $stmt = $db->prepare("SELECT id, nome, usuario, email, data_nascimento, idade, google_id, avatar, biografia, criado_em FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $user = $stmt->fetch() ?: null;
        if (!$user) {
            // Se usuário foi deletado, limpa a sessão
            unset($_SESSION['usuario_id']);
        }
    }
    return $user;
}

function requireAuth(string $redirect = null): void {
    if (!isLoggedIn()) {
        $dest = $redirect ?: $_SERVER['REQUEST_URI'] ?? (BASE_URL . '/index.php');
        $_SESSION['redirect_after_login'] = $dest;
        setFlash('warning', 'Você precisa estar conectado para acessar esta página ou publicar uma receita.');
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}

function loginUser(array $user): void {
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario_nome'] = $user['nome'];
    $_SESSION['usuario_user'] = $user['usuario'];
    $_SESSION['usuario_avatar'] = $user['avatar'];
}

function logoutUser(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Calcula a idade a partir da data de nascimento
 */
function calcularIdade(string $dataNascimento): int {
    try {
        $nascimento = new DateTime($dataNascimento);
        $hoje = new DateTime();
        if ($nascimento > $hoje) {
            return 0;
        }
        return (int)$hoje->diff($nascimento)->y;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Flash messages para avisos e notificações
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash_message'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Exclui a conta do usuário e todos os registros vinculados no banco de dados e arquivos locais.
 *
 * @param int $userId ID do usuário
 * @param string $confirmacao Senha atual ou termo de confirmação (para contas Google)
 * @param array &$erros Coletor de erros por referência
 * @param bool $ignorarValidacaoSenha Usado apenas para operações administrativas se necessário
 * @return bool
 */
function excluirContaUsuario(int $userId, string $confirmacao = '', array &$erros = [], bool $ignorarValidacaoSenha = false): bool {
    $db = getDb();

    // 1. Localiza usuário no banco
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        $erros[] = "Usuário não encontrado no banco de dados.";
        return false;
    }

    // 2. Validação de segurança
    if (!$ignorarValidacaoSenha) {
        $isGoogleAccount = !empty($user['google_id']);

        if (!$isGoogleAccount && !empty($user['senha'])) {
            // Conta com senha padrão
            if (empty($confirmacao) || !password_verify($confirmacao, $user['senha'])) {
                $erros[] = "Senha incorreta. Por motivos de segurança, digite sua senha atual para confirmar a exclusão da conta.";
                return false;
            }
        } else {
            // Conta Google ou sem senha convencional
            $confirmacaoTrim = trim($confirmacao);
            $emailMatch = (strcasecmp($confirmacaoTrim, $user['email']) === 0);
            $palavraMatch = (strtoupper($confirmacaoTrim) === 'EXCLUIR');

            if (!$emailMatch && !$palavraMatch) {
                $erros[] = "Confirmação inválida. Digite a palavra EXCLUIR ou seu e-mail cadastrado (" . htmlspecialchars($user['email']) . ") para autorizar a remoção definitiva.";
                return false;
            }
        }
    }

    try {
        $db->beginTransaction();

        $uploadsDir = realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads');
        $arquivosParaRemover = [];

        // 3. Buscar receitas para apagar imagens locais do disco
        $stmtRec = $db->prepare("SELECT id, imagem FROM receitas WHERE usuario_id = ?");
        $stmtRec->execute([$userId]);
        $receitas = $stmtRec->fetchAll();

        foreach ($receitas as $rec) {
            if (!empty($rec['imagem']) && strpos($rec['imagem'], 'uploads/') !== false) {
                $fileName = basename(parse_url($rec['imagem'], PHP_URL_PATH));
                $caminhoArquivo = $uploadsDir . DIRECTORY_SEPARATOR . $fileName;
                if (file_exists($caminhoArquivo) && is_file($caminhoArquivo)) {
                    $arquivosParaRemover[] = $caminhoArquivo;
                }
            }
        }

        // Se o avatar for arquivo local em uploads, remove também
        if (!empty($user['avatar']) && strpos($user['avatar'], 'uploads/') !== false) {
            $avatarName = basename(parse_url($user['avatar'], PHP_URL_PATH));
            $avatarPath = $uploadsDir . DIRECTORY_SEPARATOR . $avatarName;
            if (file_exists($avatarPath) && is_file($avatarPath)) {
                $arquivosParaRemover[] = $avatarPath;
            }
        }

        // 4. Tratar curtidas dadas pelo usuário em receitas de outros autores
        $stmtLikes = $db->prepare("SELECT receita_id FROM receitas_likes WHERE usuario_id = ?");
        $stmtLikes->execute([$userId]);
        $likedRecipeIds = $stmtLikes->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($likedRecipeIds)) {
            $stmtDecr = $db->prepare("UPDATE receitas SET likes = GREATEST(0, likes - 1) WHERE id = ? AND usuario_id != ?");
            foreach ($likedRecipeIds as $recId) {
                $stmtDecr->execute([$recId, $userId]);
            }
            $db->prepare("DELETE FROM receitas_likes WHERE usuario_id = ?")->execute([$userId]);
        }

        // 5. Apagar todos os comentários feitos pelo usuário em quaisquer receitas
        $db->prepare("DELETE FROM comentarios WHERE usuario_id = ?")->execute([$userId]);

        // 6. Apagar todas as receitas criadas pelo usuário
        // (O ON DELETE CASCADE nas chaves estrangeiras também limpa likes e comentários destas receitas)
        $db->prepare("DELETE FROM receitas WHERE usuario_id = ?")->execute([$userId]);

        // 7. Apagar o registro do usuário na tabela usuarios do banco de dados
        $db->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$userId]);

        // Confirma todas as exclusões no banco de dados
        $db->commit();

        // 8. Remove os arquivos locais apenas após o commit com sucesso
        foreach ($arquivosParaRemover as $filePath) {
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        return true;

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $erros[] = "Erro ao excluir conta do banco de dados: " . $e->getMessage();
        return false;
    }
}
