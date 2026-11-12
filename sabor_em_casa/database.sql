-- Script de Criação do Banco de Dados - Sabor em Casa
CREATE DATABASE IF NOT EXISTS `sabor_em_casa` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sabor_em_casa`;

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(150) NOT NULL,
  `usuario` VARCHAR(60) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `data_nascimento` DATE NOT NULL,
  `idade` INT NOT NULL,
  `google_id` VARCHAR(100) NULL,
  `avatar` VARCHAR(255) DEFAULT 'assets/img/avatar-default.png',
  `biografia` VARCHAR(255) DEFAULT 'Apaixonado(a) por gastronomia caseira.',
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icone` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Receitas
CREATE TABLE IF NOT EXISTS `receitas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `categoria_id` INT NOT NULL,
  `titulo` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `descricao` TEXT NOT NULL,
  `tempo_preparo` VARCHAR(50) NOT NULL,
  `rendimento` VARCHAR(50) NOT NULL,
  `dificuldade` ENUM('Fácil', 'Médio', 'Difícil') DEFAULT 'Médio',
  `ingredientes` MEDIUMTEXT NOT NULL,
  `modo_preparo` MEDIUMTEXT NOT NULL,
  `video_url` VARCHAR(255) NULL,
  `imagem` VARCHAR(255) NOT NULL,
  `destaque` TINYINT(1) DEFAULT 0,
  `visualizacoes` INT DEFAULT 0,
  `likes` INT DEFAULT 0,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Likes das Receitas
CREATE TABLE IF NOT EXISTS `receitas_likes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receita_id` INT NOT NULL,
  `usuario_id` INT NULL,
  `sessao_token` VARCHAR(100) NOT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`receita_id`),
  FOREIGN KEY (`receita_id`) REFERENCES `receitas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Comentários / Chat da Receita
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receita_id` INT NOT NULL,
  `usuario_id` INT NULL,
  `nome_autor` VARCHAR(150) NOT NULL,
  `avatar_autor` VARCHAR(255) DEFAULT 'assets/img/avatar-default.png',
  `comentario` TEXT NOT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`receita_id`),
  FOREIGN KEY (`receita_id`) REFERENCES `receitas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserindo Categorias
INSERT INTO `categorias` (`id`, `nome`, `slug`, `icone`) VALUES
(1, 'Almoço & Jantar', 'almoco-jantar', 'fa-utensils'),
(2, 'Sobremesas & Doces', 'sobremesas-doces', 'fa-ice-cream'),
(3, 'Massas & Pizzas', 'massas-pizzas', 'fa-pizza-slice'),
(4, 'Lanches & Salgados', 'lanches-salgados', 'fa-hamburger'),
(5, 'Fitness & Leve', 'fitness-leve', 'fa-leaf'),
(6, 'Bebidas & Sucos', 'bebidas-sucos', 'fa-glass-martini-alt')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

-- Usuário Padrão Administrador/Chef (Senha: 123456)
-- Hash gerado com password_hash('123456', PASSWORD_DEFAULT)
INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `email`, `senha`, `data_nascimento`, `idade`, `avatar`, `biografia`) VALUES
(1, 'Chef Ana Carolina', 'chefanac', 'ana@saboremcasa.com.br', '$2y$10$wS9bXf8qHkWpT5LzEkm3.uUqHh6Gtz6c14m0uLpZ6nNl8yZJbQf9G', '1995-06-15', 29, 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80', 'Cozinheira apaixonada por receitas práticas e afetivas para a família.')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

-- Receitas Demonstrativas com Vídeos e Textos Completos
INSERT INTO `receitas` (`id`, `usuario_id`, `categoria_id`, `titulo`, `slug`, `descricao`, `tempo_preparo`, `rendimento`, `dificuldade`, `ingredientes`, `modo_preparo`, `video_url`, `imagem`, `destaque`, `visualizacoes`) VALUES
(1, 1, 2, 'Bolo de Cenoura Fofinho com Cobertura Crocante de Chocolate', 'bolo-de-cenoura-fofinho-com-chocolate', 
'A receita clássica brasileira que não tem erro: massa super fofinha, dourada e aquela calda de chocolate que forma uma casquinha irresistível.',
'50 min', '12 porções', 'Fácil',
'- 3 cenouras médias descascadas e picadas\n- 4 ovos inteiros\n- 1 xícara (chá) de óleo de milho ou girassol\n- 2 xícaras (chá) de açúcar\n- 2 e 1/2 xícaras (chá) de farinha de trigo\n- 1 colher (sopa) de fermento químico em pó\n- 1 pitada de sal\n\nPARA A COBERTURA:\n- 1 xícara (chá) de açúcar\n- 1 xícara (chá) de chocolate em pó ou cacau 50%\n- 2 colheres (sopa) de manteiga\n- 5 colheres (sopa) de leite',
'1. No liquidificador, adicione as cenouras picadas, os 4 ovos e o óleo. Bata muito bem por cerca de 3 a 5 minutos até obter um creme bem homogêneo e liso.\n2. Em uma tigela grande, peneire a farinha de trigo com o açúcar e o sal.\n3. Despeje a mistura do liquidificador sobre os ingredientes secos e misture delicadamente com um fouet até incorporar tudo perfeitamente.\n4. Por último, adicione o fermento em pó e mexa suavemente de baixo para cima.\n5. Despeje a massa em uma forma untada e enfarinhada (aproximadamente 22x32cm).\n6. Leve ao forno pré-aquecido a 180°C por cerca de 40 a 45 minutos (faça o teste do palito, ele deve sair limpo).\n7. Para a cobertura: junte todos os ingredientes em uma panela pequena e leve ao fogo médio, mexendo sempre, até levantar fervura e engrossar levemente (cerca de 3 a 4 minutos).\n8. Despeje a calda ainda quente sobre o bolo já assado e deixe esfriar para criar a casquinha crocante.',
'https://www.youtube.com/watch?v=J_O7E0YV930',
'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&auto=format&fit=crop&q=80',
1, 1420),

(2, 1, 1, 'Lasanha à Bolonhesa Tradicional com Queijo Gratinado', 'lasanha-a-bolonhesa-tradicional',
'Camadas perfeitas de massa caseira, molho bolonhesa aromático com carne de primeira, molho branco cremoso e muito queijo derretido.',
'1h 15 min', '8 porções', 'Médio',
'- 500g de massa para lasanha pré-cozida\n- 600g de carne bovina moída (patinho ou acém)\n- 1 cebola média picadinha\n- 3 dentes de alho amassados\n- 2 latas de tomate pelado ou 800g de molho de tomate caseiro\n- 2 colheres (sopa) de azeite de oliva\n- Sal, pimenta-do-reino e manjericão fresco a gosto\n\nPARA O MOLHO BRANCO (BÉCHAMEL):\n- 3 colheres (sopa) de manteiga\n- 3 colheres (sopa) de farinha de trigo\n- 800ml de leite integral morno\n- 1 pitada de noz-moscada ralada na hora\n- Sal e pimenta branca a gosto\n\nMONTAGEM:\n- 400g de queijo mussarela fatiado\n- 300g de presunto magro fatiado\n- 100g de queijo parmesão ralado para gratinar',
'1. Molho Bolonhesa: Aqueça o azeite em uma panela grande e doure a cebola e o alho. Adicione a carne moída e refogue até secar a água. Acrescente o molho de tomate, acerte o sal, a pimenta e deixe apurar em fogo baixo por 20 minutos. Finalize com manjericão.\n2. Molho Branco: Em outra panela, derreta a manteiga e junte a farinha de trigo, mexendo por 2 minutos para cozinhar. Adicione o leite morno aos poucos, mexendo vigorosamente com o batedor para não empelotar. Cozinhe até engrossar e tempere com sal e noz-moscada.\n3. Montagem: Em um refratário grande, espalhe uma concha de molho bolonhesa no fundo. Coloque uma camada de massa de lasanha.\n4. Cubra com molho branco, presunto e queijo mussarela.\n5. Repita as camadas finalizando com bastante queijo mussarela e polvilhe o parmesão ralado por cima.\n6. Leve ao forno pré-aquecido a 200°C por cerca de 30 minutos ou até o queijo ficar completamente dourado e borbulhante.\n7. Deixe descansar por 10 minutos antes de cortar e servir.',
'https://www.youtube.com/watch?v=0k6Z0q9K0e8',
'https://images.unsplash.com/photo-1574894709920-11b28e7367e3?w=800&auto=format&fit=crop&q=80',
1, 2310),

(3, 1, 1, 'Strogonoff de Frango Super Cremoso com Batata Palha', 'strogonoff-de-frango-super-cremoso',
'O queridinho do almoço de domingo! Frango macio e dourado, champignons frescos e molho aveludado servido com arroz branco e batata palha crocante.',
'35 min', '5 porções', 'Fácil',
'- 800g de peito de frango cortado em cubos médios\n- 2 colheres (sopa) de manteiga ou azeite\n- 1 cebola média picada\n- 2 dentes de alho picados\n- 150g de champignon fatiado\n- 3 colheres (sopa) de ketchup\n- 2 colheres (sopa) de mostarda dijon ou amarela\n- 1 colher (sopa) de molho inglês\n- 2 caixinhas (400g) de creme de leite\n- Sal, páprica doce e pimenta-do-reino a gosto\n- Cheiro-verde fresco picado a gosto',
'1. Tempere os cubos de frango com sal, pimenta-do-reino e uma pitada de páprica doce.\n2. Em uma panela larga e funda, aqueça a manteiga e sele os cubos de frango aos poucos em fogo alto até dourarem bem.\n3. Junte a cebola picadinha e o alho, refogando até ficarem macios e translúcidos.\n4. Adicione os cogumelos fatiados e refogue por 2 minutos.\n5. Acrescente o ketchup, a mostarda e o molho inglês, misturando tudo com a carne.\n6. Abaixe o fogo e despeje o creme de leite, mexendo continuamente até incorporar e ficar bem cremoso (não deixe ferver para não talhar).\n7. Acerte o sal se necessário e finalize com cheiro-verde picadinho.\n8. Sirva imediatamente acompanhado de arroz soltinho e batata palha.',
'https://www.youtube.com/watch?v=kRj_8Wc7k5c',
'https://images.unsplash.com/photo-1532550907401-a500c9a57435?w=800&auto=format&fit=crop&q=80',
1, 980),

(4, 1, 4, 'Pão de Queijo Mineiro Tradicional Crocante por Fora e Puxa por Dentro', 'pao-de-queijo-mineiro-tradicional',
'Autêntico pão de queijo mineiro feito com queijo Canastra meia cura e polvilho doce e azedo. Fica leve, inflado e delicioso.',
'40 min', '30 unidades', 'Fácil',
'- 500g de polvilho azedo (ou metade doce e metade azedo)\n- 1 xícara (chá) de leite integral\n- 1/2 xícara (chá) de óleo de girassol\n- 1/2 xícara (chá) de água\n- 1 colher (chá) de sal\n- 2 a 3 ovos grandes\n- 350g de queijo meia cura ou queijo minas padrão ralado fino\n- 50g de queijo parmesão ralado',
'1. Em uma panela pequena, coloque o leite, a água, o óleo e o sal. Leve ao fogo médio até começar a ferver.\n2. Em uma bacia espaçosa, coloque o polvilho. Despeje o líquido fervente sobre o polvilho aos poucos para escaldar, misturando com uma colher de pau até esfriar um pouco.\n3. Quando a massa estiver morna/fria ao toque, adicione os ovos um a um, amassando bem com as mãos.\n4. Junte o queijo ralado e continue sovando até a massa ficar lisa, uniforme e um pouco grudenta, porém moldável.\n5. Unte as mãos com um fio de óleo e molde bolinhas do tamanho desejado (cerca de 3 a 4 cm).\n6. Disponha os pães de queijo em uma assadeira mantendo um espaço de 2 cm entre eles.\n7. Asse em forno pré-aquecido a 200°C por 25 a 30 minutos, até dourarem por fora.\n8. Sirva quentinho acompanhado de um bom café passado na hora.',
'https://www.youtube.com/watch?v=lA47mXp9G5A',
'https://images.unsplash.com/photo-1598373182133-52452f7691ef?w=800&auto=format&fit=crop&q=80',
0, 840),

(5, 1, 2, 'Mousse de Maracujá Aerada com Ganache de Chocolate', 'mousse-de-maracuja-com-ganache',
'Sobremesa de restaurante feita em 10 minutos! O contraste perfeito entre o azedinho do maracujá e o doce aveludado do chocolate.',
'20 min (+ 3h geladeira)', '8 taças', 'Fácil',
'- 1 lata (395g) de leite condensado\n- 2 caixinhas (400g) de creme de leite\n- 1 xícara (240ml) de suco concentrado de maracujá puro\n- 1 sachê (12g) de gelatina incolor hidratada (opcional, para mais firmeza)\n\nPARA A GANACHE:\n- 180g de chocolate meio amargo picado\n- 1 caixinha (200g) de creme de leite\n- Polpa de 1 maracujá fresco com sementes para decorar',
'1. No liquidificador, bata o leite condensado, as 2 caixinhas de creme de leite e o suco concentrado de maracujá em velocidade alta por 4 minutos até criar uma consistência aerada e densa.\n2. Se usar gelatina incolor, hidrate conforme as instruções do pacote, derreta no micro-ondas por 15 segundos e adicione ao liquidificador batendo por mais 30 segundos.\n3. Despeje a mousse em taças individuais ou em uma travessa de vidro bonita, deixando espaço no topo para a cobertura.\n4. Para a ganache: derreta o chocolate no micro-ondas de 30 em 30 segundos, mexendo nos intervalos. Adicione o creme de leite e misture até virar um creme espelhado e brilhante.\n5. Espalhe a ganache delicadamente sobre a mousse e finalize com as sementinhas frescas de maracujá.\n6. Leve à geladeira por no mínimo 3 horas antes de servir bem gelado.',
'https://www.youtube.com/watch?v=vV95X6zBf4c',
'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800&auto=format&fit=crop&q=80',
1, 1670),

(6, 1, 3, 'Macarrão Carbonara Clássico Italiano Sem Creme de Leite', 'macarrao-carbonara-classico',
'A verdadeira receita romana: guanciale ou bacon dourado, gemas frescas, queijo pecorino/parmesão e bastante pimenta-do-reino preta.',
'25 min', '4 porções', 'Médio',
'- 400g de espaguete de boa qualidade\n- 200g de guanciale ou bacon em cubos pequenos\n- 4 gemas de ovo caipira + 1 ovo inteiro\n- 100g de queijo pecorino romano ou parmesão ralado na hora\n- Pimenta-do-reino preta moída na hora em abundância\n- Sal para a água do macarrão',
'1. Coloque uma panela grande com bastante água para ferver com um punhado generoso de sal grosso.\n2. Em uma frigideira fria, coloque os cubos de bacon/guanciale e ligue em fogo baixo para soltar a gordura e dourar lentamente até ficarem crocantes por fora e macios por dentro. Desligue o fogo e reserve.\n3. Em uma tigela, misture as 4 gemas, 1 ovo inteiro, a maior parte do queijo ralado e bastante pimenta-do-reino preta moída, formando uma pasta cremosa.\n4. Cozinhe o espaguete até ficar al dente. Retire 1 xícara da água do cozimento do macarrão antes de escorrer.\n5. Adicione a massa escorrida diretamente na frigideira com o bacon (fogo desligado).\n6. Despeje a mistura de ovos e queijo sobre a massa ainda quente, adicionando algumas colheradas da água do cozimento para emulsionar.\n7. Mexa vigorosamente fora do fogo até formar um molho aveludado e ultra brilhante (o calor residual da massa cozinha os ovos sem talhar).\n8. Sirva imediatamente em pratos aquecidos, finalizando com mais queijo e pimenta moída na hora.',
'https://www.youtube.com/watch?v=3AAdKl1UYZs',
'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=800&auto=format&fit=crop&q=80',
0, 1150)
ON DUPLICATE KEY UPDATE `titulo` = VALUES(`titulo`);


-- Tabela de Registro de Consentimento de Cookies (LGPD - Lei nº 13.709/2018)
CREATE TABLE IF NOT EXISTS `consentimento_cookies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `consent_token` VARCHAR(64) NOT NULL,
  `usuario_id` INT NULL,
  `ip_anonimizado` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NOT NULL,
  `categoria_necessarios` TINYINT(1) DEFAULT 1,
  `categoria_preferencias` TINYINT(1) DEFAULT 0,
  `categoria_estatisticas` TINYINT(1) DEFAULT 0,
  `categoria_marketing` TINYINT(1) DEFAULT 0,
  `acao` VARCHAR(30) NOT NULL,
  `versao_politica` VARCHAR(20) DEFAULT '1.0',
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`consent_token`),
  INDEX (`usuario_id`),
  INDEX (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
