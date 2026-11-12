  </main>

  <!-- Rodapé do Site -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        
        <div class="footer-brand">
          <div class="brand-logo" style="color: #fff; margin-bottom: 14px;">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Sabor em Casa" class="brand-img">
            <div class="brand-title">Sabor <span style="color: var(--secondary);">em Casa</span></div>
          </div>
          <p>O seu portal completo de gastronomia caseira. Receitas testadas e aprovadas com texto detalhado, medidas exatas e tutoriais em vídeo para você cozinhar com prazer.</p>
        </div>

        <div class="footer-col">
          <h4>Navegação</h4>
          <ul class="footer-links">
            <li><a href="<?= BASE_URL ?>/index.php">Início</a></li>
            <li><a href="<?= BASE_URL ?>/receitas.php">Todas as Receitas</a></li>
            <li><a href="<?= BASE_URL ?>/receitas.php?com_video=1">Receitas em Vídeo</a></li>
            <li><a href="<?= BASE_URL ?>/publicar.php">Publicar Receita</a></li>
            <li><a href="<?= BASE_URL ?>/politica-cookies.php">Política de Cookies</a></li>
            <li><a href="javascript:void(0)" class="js-open-cookie-preferences"><i class="fa-solid fa-cookie-bite" style="color: var(--secondary);"></i> Preferências de Cookies</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Categorias</h4>
          <ul class="footer-links">
            <li><a href="<?= BASE_URL ?>/receitas.php?categoria=almoco-jantar">Almoço & Jantar</a></li>
            <li><a href="<?= BASE_URL ?>/receitas.php?categoria=sobremesas-doces">Sobremesas & Doces</a></li>
            <li><a href="<?= BASE_URL ?>/receitas.php?categoria=massas-pizzas">Massas & Pizzas</a></li>
            <li><a href="<?= BASE_URL ?>/receitas.php?categoria=lanches-salgados">Lanches & Salgados</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Acesso</h4>
          <ul class="footer-links">
            <?php if (isLoggedIn()): ?>
              <li><a href="<?= BASE_URL ?>/minhas-receitas.php">Minhas Receitas</a></li>
              <li><a href="<?= BASE_URL ?>/publicar.php">Enviar Nova Receita</a></li>
              <li><a href="<?= BASE_URL ?>/logout.php">Sair da Conta</a></li>
            <?php else: ?>
              <li><a href="<?= BASE_URL ?>/login.php">Entrar com Login</a></li>
              <li><a href="<?= BASE_URL ?>/cadastro.php">Criar Nova Conta</a></li>
              <li><a href="javascript:void(0)" onclick="abrirModalGoogle()">Acesso Rápido com Google</a></li>
            <?php endif; ?>
          </ul>
        </div>

      </div>

      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Sabor em Casa. Todos os direitos reservados. Feito com amor pela culinária caseira.</p>
        <div class="footer-privacy-links" style="display:flex; align-items:center; gap:14px; font-size:0.86rem; flex-wrap:wrap;">
          <a href="<?= BASE_URL ?>/politica-cookies.php" style="color:#A0AEC0; text-decoration:underline;">Política de Cookies (LGPD)</a>
          <span style="color:#718096;">&bull;</span>
          <a href="javascript:void(0)" class="js-open-cookie-preferences" style="color:var(--secondary); display:inline-flex; align-items:center; gap:6px; font-weight:600;">
            <i class="fa-solid fa-cookie-bite"></i> Preferências de Cookies
          </a>
        </div>
        <p style="display:flex; align-items:center; gap:8px;">
          <span>Compatível com Celular, Tablet e Desktop</span>
          <i class="fa-solid fa-mobile-screen"></i>
          <i class="fa-solid fa-tablet-screen-button"></i>
          <i class="fa-solid fa-laptop"></i>
        </p>
      </div>
    </div>
  </footer>

  <!-- Componente de Consentimento de Cookies (LGPD) -->
  <?php require_once __DIR__ . '/cookie-banner.php'; ?>

  <!-- Modal Interativo: Entrar com o Google -->
  <div class="modal-overlay" id="modalGoogle">
    <div class="modal-content">
      <button class="modal-close" onclick="fecharModalGoogle()" aria-label="Fechar">&times;</button>
      <div style="text-align:center; margin-bottom: 20px;">
        <svg width="40" height="40" viewBox="0 0 24 24" style="margin-bottom:8px;">
          <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.15z"/>
          <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.26v3.15C3.25 21.36 7.33 24 12 24z"/>
          <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.26C.46 8.16 0 9.94 0 12s.46 3.84 1.26 5.42l4.02-3.15z"/>
          <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.25 2.64 1.26 6.58l4.02 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
        </svg>
        <h3 style="font-family:var(--font-heading); font-size:1.4rem; color:var(--dark);">Conectar com Google</h3>
        <p style="font-size:0.9rem; color:var(--text-muted); margin-top:4px;">Acesse ou crie sua conta em 1 clique usando sua conta do Google.</p>
      </div>

      <form action="<?= BASE_URL ?>/auth-google.php" method="POST">
        <div class="form-group">
          <label class="form-label">Seu Nome no Google</label>
          <input type="text" name="google_nome" class="form-control" value="Allan Silva" required>
        </div>
        <div class="form-group">
          <label class="form-label">Seu E-mail Google</label>
          <input type="email" name="google_email" class="form-control" value="allan.culinaria@gmail.com" required>
        </div>
        <div class="form-group">
          <label class="form-label">Data de Nascimento (para calcular idade)</label>
          <input type="date" name="google_nascimento" class="form-control" value="1998-05-20" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">
          <i class="fa-solid fa-arrow-right"></i> Continuar com o Google
        </button>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="<?= BASE_URL ?>/assets/js/cookie-consent.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
