<?php
/**
 * API de Registro e Gestão de Consentimento de Cookies (LGPD)
 * Sabor em Casa
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Função para anonimizar o endereço IP em conformidade com a LGPD
function anonimizarIp(string $ip): string {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $partes = explode('.', $ip);
        if (count($partes) === 4) {
            $partes[3] = 'xxx';
            return implode('.', $partes);
        }
    } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $partes = explode(':', $ip);
        if (count($partes) >= 4) {
            return $partes[0] . ':' . $partes[1] . ':' . $partes[2] . ':xxxx:xxxx:xxxx:xxxx:xxxx';
        }
    }
    return 'anonimizado';
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$currentUser = null;
$usuarioId = null;

try {
    $currentUser = currentUser();
    $usuarioId = $currentUser ? (int)$currentUser['id'] : null;
} catch (Throwable $e) {
    // Continua mesmo sem sessão de usuário
}

// Rota GET: Obter status ou histórico
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'status';

    if ($action === 'status') {
        $cookieValue = $_COOKIE['sabor_cookie_consent'] ?? null;
        if ($cookieValue) {
            $decoded = json_decode($cookieValue, true);
            if (is_array($decoded)) {
                echo json_encode([
                    'status' => 'sucesso',
                    'consentido' => true,
                    'dados' => $decoded
                ]);
                exit;
            }
        }

        echo json_encode([
            'status' => 'sucesso',
            'consentido' => false,
            'dados' => null
        ]);
        exit;
    }
}

// Rota POST: Salvar ou revogar consentimento
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        $data = $_POST;
    }

    $acao = filter_var($data['acao'] ?? 'personalizado', FILTER_SANITIZE_SPECIAL_CHARS);
    $versaoPolitica = filter_var($data['versao_politica'] ?? '1.0', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Token único de consentimento para auditoria LGPD
    $consentToken = !empty($data['consent_token']) 
        ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $data['consent_token'])
        : 'cst_' . bin2hex(random_bytes(16));

    // Categorias
    $categoriasInput = $data['categorias'] ?? [];
    $categoriaNecessarios = 1; // Sempre 1 por definição técnica
    $categoriaPreferencias = !empty($categoriasInput['preferencias']) ? 1 : 0;
    $categoriaEstatisticas = !empty($categoriasInput['estatisticas']) ? 1 : 0;
    $categoriaMarketing = !empty($categoriasInput['marketing']) ? 1 : 0;

    // Se a ação for aceitar_todos
    if ($acao === 'aceitar_todos') {
        $categoriaPreferencias = 1;
        $categoriaEstatisticas = 1;
        $categoriaMarketing = 1;
    } elseif ($acao === 'rejeitar_opcionais' || $acao === 'revogado') {
        $categoriaPreferencias = 0;
        $categoriaEstatisticas = 0;
        $categoriaMarketing = 0;
    }

    // IP e User Agent
    $rawIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $ipAnonimizado = anonimizarIp(trim(explode(',', $rawIp)[0]));
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido', 0, 500);

    $now = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    $dataFormatada = $now->format('d/m/Y H:i:s');
    $timestamp = $now->getTimestamp();

    // Salva no banco de dados para trilha de auditoria LGPD (Art. 8º § 2º)
    try {
        $db = getDb();
        $stmt = $db->prepare("
            INSERT INTO consentimento_cookies 
            (consent_token, usuario_id, ip_anonimizado, user_agent, categoria_necessarios, categoria_preferencias, categoria_estatisticas, categoria_marketing, acao, versao_politica, criado_em)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $consentToken,
            $usuarioId,
            $ipAnonimizado,
            $userAgent,
            $categoriaNecessarios,
            $categoriaPreferencias,
            $categoriaEstatisticas,
            $categoriaMarketing,
            $acao,
            $versaoPolitica
        ]);
    } catch (Throwable $e) {
        // Log de erro no servidor sem quebrar o fluxo do usuário
        error_log("Erro ao salvar consentimento de cookie no banco: " . $e->getMessage());
    }

    // Estrutura de dados para o cookie
    $payloadConsentimento = [
        'consent_token' => $consentToken,
        'timestamp' => $timestamp,
        'data_hora' => $dataFormatada,
        'versao_politica' => $versaoPolitica,
        'acao' => $acao,
        'categorias' => [
            'necessarios' => true,
            'preferencias' => (bool)$categoriaPreferencias,
            'estatisticas' => (bool)$categoriaEstatisticas,
            'marketing' => (bool)$categoriaMarketing
        ]
    ];

    // Validade: 180 dias (aprox. 6 meses, prazo recomendado para revalidação LGPD)
    $diasExpiracao = 180;
    $expiraEm = time() + ($diasExpiracao * 86400);

    // Grava cookie HTTP
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    setcookie(
        'sabor_cookie_consent',
        json_encode($payloadConsentimento, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        [
            'expires' => $expiraEm,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => false,
            'samesite' => 'Lax'
        ]
    );

    echo json_encode([
        'status' => 'sucesso',
        'mensagem' => 'Preferências de cookies salvas com sucesso.',
        'dados' => $payloadConsentimento
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'erro', 'mensagem' => 'Método não permitido']);
