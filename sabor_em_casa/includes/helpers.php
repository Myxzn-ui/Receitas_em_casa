<?php
/**
 * Funções Auxiliares e Utilitários
 * Sabor em Casa
 */

function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    // Remove acentos
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
    // Remove caracteres especiais
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    // Remove múltiplos traços
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Extrai o ID do vídeo do YouTube de qualquer formato comum de link
 */
function extrairYoutubeId(?string $url): ?string {
    if (empty($url)) {
        return null;
    }

    $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/|youtube\.com/shorts/)([^"&?/\s]{11})%i';
    if (preg_match($pattern, $url, $match)) {
        return $match[1];
    }
    return null;
}

/**
 * Retorna URL de embed do YouTube ou player compatível
 */
function obterEmbedVideoUrl(?string $url): ?string {
    $youtubeId = extrairYoutubeId($url);
    if ($youtubeId) {
        return "https://www.youtube.com/embed/{$youtubeId}?rel=0&modestbranding=1";
    }
    
    // Suporte para Vimeo
    if (!empty($url) && preg_match('/vimeo\.com\/(\d+)/', $url, $vimeoMatch)) {
        return "https://player.vimeo.com/video/" . $vimeoMatch[1];
    }

    return null;
}

/**
 * Converte o texto de ingredientes em array de itens limpos
 */
function separarIngredientes(string $texto): array {
    $linhas = explode("\n", str_replace(["\r\n", "\r"], "\n", $texto));
    $ingredientes = [];
    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha !== '') {
            // Remove marcadores comuns como -, *, •
            $linhaLimpa = preg_replace('/^[\-\*\•\d\.\)]\s*/u', '', $linha);
            $ingredientes[] = [
                'texto' => $linhaLimpa,
                'is_secao' => (strpos($linha, 'PARA ') === 0 || strpos($linha, 'COBERTURA') !== false || strpos($linha, 'MOLHO') !== false || strpos($linha, 'MASSA') !== false || strpos($linha, 'RECHEIO') !== false || substr($linha, -1) === ':')
            ];
        }
    }
    return $ingredientes;
}

/**
 * Converte o texto do modo de preparo em passos ordenados
 */
function separarModoPreparo(string $texto): array {
    $linhas = explode("\n", str_replace(["\r\n", "\r"], "\n", $texto));
    $passos = [];
    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha !== '') {
            $linhaLimpa = preg_replace('/^\d+[\.\)\-]?\s*/', '', $linha);
            $passos[] = $linhaLimpa;
        }
    }
    return $passos;
}

/**
 * Limita quantidade de texto com reticências
 */
function resumirTexto(string $texto, int $limite = 110): string {
    $texto = strip_tags($texto);
    if (mb_strlen($texto) <= $limite) {
        return $texto;
    }
    return mb_substr($texto, 0, $limite) . '...';
}

/**
 * Formata data no formato brasileiro
 */
function dataFormatada(string $data): string {
    $dt = new DateTime($data);
    return $dt->format('d/m/Y');
}

/**
 * Retorna tempo relativo amigável (Ex: 'Agora mesmo', 'há 5 min', 'há 2 horas')
 */
function tempoRelativo(string $data): string {
    $timestamp = strtotime($data);
    $diferenca = time() - $timestamp;

    if ($diferenca < 60) {
        return 'Agora mesmo';
    } elseif ($diferenca < 3600) {
        $min = (int)floor($diferenca / 60);
        return "há {$min} min";
    } elseif ($diferenca < 86400) {
        $horas = (int)floor($diferenca / 3600);
        return "há {$horas} " . ($horas == 1 ? 'hora' : 'horas');
    } elseif ($diferenca < 172800) {
        return 'Ontem às ' . date('H:i', $timestamp);
    } else {
        return date('d/m/Y \à\s H:i', $timestamp);
    }
}
