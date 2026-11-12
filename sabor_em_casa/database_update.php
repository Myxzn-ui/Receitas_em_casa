<?php
require_once __DIR__ . '/config/db.php';

$db = getDb();

// 1. Adiciona coluna 'likes' na tabela receitas se não existir
try {
    $db->query("ALTER TABLE receitas ADD COLUMN likes INT DEFAULT 0 AFTER visualizacoes");
    echo "Coluna 'likes' adicionada com sucesso.\n";
} catch (Exception $e) {
    echo "Coluna 'likes' já existe ou erro: " . $e->getMessage() . "\n";
}

// Atualiza valores iniciais de likes para as receitas existentes se estiverem 0
$initialLikes = [
    1 => 142, // Bolo de Cenoura
    2 => 289, // Lasanha
    3 => 95,  // Strogonoff
    4 => 312, // Pão de Queijo
    5 => 178, // Mousse
    6 => 204  // Carbonara
];
foreach ($initialLikes as $recId => $cnt) {
    $db->prepare("UPDATE receitas SET likes = ? WHERE id = ? AND (likes IS NULL OR likes = 0)")->execute([$cnt, $recId]);
}
echo "Likes iniciais configurados.\n";

// 2. Cria tabela receitas_likes
$sqlLikes = "
CREATE TABLE IF NOT EXISTS `receitas_likes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receita_id` INT NOT NULL,
  `usuario_id` INT NULL,
  `sessao_token` VARCHAR(100) NOT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`receita_id`),
  INDEX (`usuario_id`),
  INDEX (`sessao_token`),
  FOREIGN KEY (`receita_id`) REFERENCES `receitas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
$db->exec($sqlLikes);
echo "Tabela receitas_likes criada/verificada com sucesso.\n";

// 3. Cria tabela comentarios
$sqlComentarios = "
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receita_id` INT NOT NULL,
  `usuario_id` INT NULL,
  `nome_autor` VARCHAR(150) NOT NULL,
  `avatar_autor` VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
  `comentario` TEXT NOT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`receita_id`),
  FOREIGN KEY (`receita_id`) REFERENCES `receitas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
$db->exec($sqlComentarios);
echo "Tabela comentarios criada/verificada com sucesso.\n";

// 4. Inserir alguns comentários de exemplo para a receita 1 se não houver comentários
$stmtCount = $db->query("SELECT COUNT(*) FROM comentarios WHERE receita_id = 1");
if ($stmtCount->fetchColumn() == 0) {
    $comentariosExemplo = [
        [
            'receita_id' => 1,
            'usuario_id' => null,
            'nome_autor' => 'Mariana Souza',
            'avatar_autor' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80',
            'comentario' => 'Fiz ontem à tarde para o café e ficou simplesmente perfeito! A massa cresceu muito e a casquinha ficou bem crocante.',
            'criado_em' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        [
            'receita_id' => 1,
            'usuario_id' => 1, // Chef Ana Carolina
            'nome_autor' => 'Chef Ana Carolina',
            'avatar_autor' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80',
            'comentario' => 'Que maravilha, Mariana! Fico muito feliz que tenha gostado. O segredo da casquinha é despejar a calda ainda bem quente!',
            'criado_em' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ],
        [
            'receita_id' => 1,
            'usuario_id' => null,
            'nome_autor' => 'Carlos Eduardo',
            'avatar_autor' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80',
            'comentario' => 'Posso substituir o óleo por manteiga derretida na mesma proporção?',
            'criado_em' => date('Y-m-d H:i:s', strtotime('-25 minutes'))
        ]
    ];

    $stmtInsert = $db->prepare("INSERT INTO comentarios (receita_id, usuario_id, nome_autor, avatar_autor, comentario, criado_em) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($comentariosExemplo as $c) {
        $stmtInsert->execute([$c['receita_id'], $c['usuario_id'], $c['nome_autor'], $c['avatar_autor'], $c['comentario'], $c['criado_em']]);
    }
    echo "Comentários de exemplo inseridos com sucesso.\n";
}

echo "Migração concluída com 100% de sucesso.\n";
