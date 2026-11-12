<?php
/**
 * Página de Política de Cookies - Sabor em Casa
 * Em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018 - LGPD)
 */

$pageTitle = 'Política de Cookies e Privacidade | Sabor em Casa';
$metaDescription = 'Conheça nossa Política de Cookies em total conformidade com a LGPD. Entenda quais dados coletamos, como gerenciamos seu consentimento e controle suas preferências a qualquer momento.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container" style="padding: 40px 0 70px 0; background: var(--bg-body);">
  <div class="container" style="max-width: 960px;">

    <!-- Breadcrumb e Cabeçalho -->
    <div style="margin-bottom: 30px;">
      <nav aria-label="Navegação" style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 12px;">
        <a href="<?= BASE_URL ?>/index.php" style="color: var(--primary); font-weight: 500;">Início</a>
        <span style="margin: 0 6px;">&bull;</span>
        <span>Política de Cookies</span>
      </nav>

      <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px;">
        <div>
          <h1 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: var(--dark); line-height: 1.2; margin-bottom: 8px;">
            Política de Cookies e Privacidade
          </h1>
          <p style="font-size: 1.05rem; color: var(--text-muted);">
            Transparência, controle e respeito absoluto aos seus dados pessoais conforme a LGPD.
          </p>
        </div>
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 8px 16px; border-radius: 30px; font-size: 0.85rem; font-weight: 600; color: var(--text-main); display:inline-flex; align-items:center; gap:8px;">
          <i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i>
          Versão 1.0 &bull; Em vigor: Nov/2026
        </div>
      </div>
    </div>

    <!-- Painel Interativo Dinâmico de Gestão de Consentimento -->
    <div style="background: #FFFFFF; border: 2px solid var(--secondary); border-radius: 18px; padding: 26px; box-shadow: var(--shadow-md); margin-bottom: 40px;">
      <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
        <div style="width:42px; height:42px; background:var(--primary-subtle); color:var(--primary); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">
          <i class="fa-solid fa-cookie-bite"></i>
        </div>
        <div>
          <h3 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 700; color: var(--dark); margin:0;">
            Central Interativa de Consentimento
          </h3>
          <p style="font-size: 0.88rem; color: var(--text-muted); margin:0;">
            Você pode verificar seu consentimento registrado ou alterá-lo diretamente agora mesmo.
          </p>
        </div>
      </div>

      <!-- Container preenchido dinamicamente pelo CookieConsent.js -->
      <div id="policyStatusTableContainer">
        <p style="color:var(--text-muted); font-size:0.9rem;">Carregando suas preferências de cookies...</p>
      </div>
    </div>

    <!-- Conteúdo Textual Explicativo da Política -->
    <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: 18px; padding: 36px; box-shadow: var(--shadow-sm); line-height: 1.75; color: var(--text-main);">

      <!-- Seção 1 -->
      <section style="margin-bottom: 36px;">
        <h2 style="font-family: var(--font-heading); font-size: 1.45rem; font-weight: 700; color: var(--dark); margin-bottom: 14px; display:flex; align-items:center; gap:10px;">
          <span style="color:var(--primary);">1.</span> O que são Cookies e por que os utilizamos?
        </h2>
        <p>
          Cookies são pequenos arquivos de texto gravados no seu dispositivo (computador, tablet ou smartphone) quando você navega em sites da internet. Eles funcionam como uma memória de curto ou médio prazo, permitindo reconhecer seu dispositivo, manter sua conta conectada de forma segura e guardar preferências de navegação.
        </p>
        <p style="margin-top: 10px;">
          No portal <strong>Sabor em Casa</strong>, utilizamos cookies com objetivos claros: assegurar o funcionamento técnico das receitas, comentários e área de membros, recordar as preferências que você escolheu (como a marcação de ingredientes durante o preparo culinário) e disponibilizar tutoriais práticos em vídeo através de plataformas como o YouTube.
        </p>
      </section>

      <hr style="border:0; border-top:1px solid var(--border-color); margin: 30px 0;">

      <!-- Seção 2 -->
      <section style="margin-bottom: 36px;">
        <h2 style="font-family: var(--font-heading); font-size: 1.45rem; font-weight: 700; color: var(--dark); margin-bottom: 14px; display:flex; align-items:center; gap:10px;">
          <span style="color:var(--primary);">2.</span> Conformidade com a LGPD (Lei nº 13.709/2018)
        </h2>
        <p>
          O <strong>Sabor em Casa</strong> cumpre integralmente os princípios da <strong>Lei Geral de Proteção de Dados Pessoais (LGPD)</strong>: finalidade, adequação, necessidade, livre acesso, transparência, segurança, prevenção e prestação de contas.
        </p>
        <div style="background: var(--bg-body); border-left: 4px solid #2A9D8F; padding: 16px 20px; border-radius: 0 10px 10px 0; margin: 18px 0; font-size: 0.94rem;">
          <strong>Bases Legais Utilizadas:</strong>
          <ul style="margin: 8px 0 0 20px;">
            <li><strong>Consentimento Livre e Informado (Art. 7º, I):</strong> Aplicado a todos os cookies opcionais (preferências, estatísticas e marketing). Nenhum cookie opcional é ativado antes de sua expressa autorização.</li>
            <li><strong>Legítimo Interesse e Execução Contratual (Art. 7º, IX e V):</strong> Aplicado estritamente aos cookies necessários para o funcionamento indispensável da plataforma e proteção contra acessos fraudulentos.</li>
            <li><strong>Ônus da Prova e Auditoria (Art. 8º, § 2º):</strong> Cada escolha é associada a um token anônimo de consentimento, data, horário e versão, permitindo auditar a legitimidade do consentimento a qualquer instante.</li>
          </ul>
        </div>
      </section>

      <hr style="border:0; border-top:1px solid var(--border-color); margin: 30px 0;">

      <!-- Seção 3 -->
      <section style="margin-bottom: 36px;">
        <h2 style="font-family: var(--font-heading); font-size: 1.45rem; font-weight: 700; color: var(--dark); margin-bottom: 18px; display:flex; align-items:center; gap:10px;">
          <span style="color:var(--primary);">3.</span> As 4 Categorias de Cookies Detalhadas
        </h2>

        <!-- Card Categoria 1 -->
        <div style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:12px; padding:20px; margin-bottom:16px;">
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
            <h3 style="font-family:var(--font-heading); font-size:1.15rem; color:var(--dark); margin:0;">
              <i class="fa-solid fa-lock" style="color:var(--primary); margin-right:8px;"></i> A. Cookies Estritamente Necessários
            </h3>
            <span style="font-size:0.75rem; font-weight:700; background:#E8F7F5; color:#2A9D8F; padding:3px 8px; border-radius:6px; text-transform:uppercase;">Sempre Ativos</span>
          </div>
          <p style="font-size:0.92rem; color:var(--text-main); margin-bottom:8px;">
            Essenciais para o funcionamento básico e segurança da plataforma. Permitem a autenticação de login de usuários, a segurança da sessão e o próprio registro das suas opções de consentimento. Sem estes cookies, o portal não pode operar.
          </p>
          <div style="font-size:0.85rem; color:var(--text-muted);">
            <strong>Exemplos:</strong> <code>PHPSESSID</code> (sessão), <code>sabor_cookie_consent</code> (comprovante de consentimento), <code>csrf_token</code> (segurança).
          </div>
        </div>

        <!-- Card Categoria 2 -->
        <div style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:12px; padding:20px; margin-bottom:16px;">
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
            <h3 style="font-family:var(--font-heading); font-size:1.15rem; color:var(--dark); margin:0;">
              <i class="fa-solid fa-sliders" style="color:var(--primary); margin-right:8px;"></i> B. Cookies de Preferência (Opcionais)
            </h3>
            <span style="font-size:0.75rem; font-weight:700; background:#FFF3CD; color:#856404; padding:3px 8px; border-radius:6px; text-transform:uppercase;">Opt-in Prévio</span>
          </div>
          <p style="font-size:0.92rem; color:var(--text-main); margin-bottom:8px;">
            Permitem que o site memorize as opções que você seleciona, como a marcação dos ingredientes já medidos na lista interativa de preparo da receita (checklist), além de modos de exibição preferidos.
          </p>
          <div style="font-size:0.85rem; color:var(--text-muted);">
            <strong>Exemplos:</strong> <code>recipe_check_*</code> (checklist de ingredientes), <code>sabor_ui_pref</code> (layout preferido).
          </div>
        </div>

        <!-- Card Categoria 3 -->
        <div style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:12px; padding:20px; margin-bottom:16px;">
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
            <h3 style="font-family:var(--font-heading); font-size:1.15rem; color:var(--dark); margin:0;">
              <i class="fa-solid fa-chart-line" style="color:var(--primary); margin-right:8px;"></i> C. Cookies Estatísticos / Analíticos (Opcionais)
            </h3>
            <span style="font-size:0.75rem; font-weight:700; background:#FFF3CD; color:#856404; padding:3px 8px; border-radius:6px; text-transform:uppercase;">Opt-in Prévio</span>
          </div>
          <p style="font-size:0.92rem; color:var(--text-main); margin-bottom:8px;">
            Coletam dados anônimos de navegação agregada, informando quais receitas são mais visualizadas, páginas com carregamento lento ou eventuais erros de digitação. Seu endereço IP é devidamente anonimizado e os dados nunca são cruzados para identificação individual.
          </p>
          <div style="font-size:0.85rem; color:var(--text-muted);">
            <strong>Exemplos:</strong> <code>_ga, _gid</code> (Google Analytics com IP mascarado), <code>sabor_analytics</code>.
          </div>
        </div>

        <!-- Card Categoria 4 -->
        <div style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:12px; padding:20px;">
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
            <h3 style="font-family:var(--font-heading); font-size:1.15rem; color:var(--dark); margin:0;">
              <i class="fa-solid fa-play" style="color:var(--primary); margin-right:8px;"></i> D. Cookies de Marketing & Mídia (Opcionais)
            </h3>
            <span style="font-size:0.75rem; font-weight:700; background:#FFF3CD; color:#856404; padding:3px 8px; border-radius:6px; text-transform:uppercase;">Opt-in Prévio</span>
          </div>
          <p style="font-size:0.92rem; color:var(--text-main); margin-bottom:8px;">
            Utilizados para viabilizar a incorporação de players de vídeo de terceiros (como YouTube) diretamente nas receitas do site, permitindo acompanhar o passo a passo com imagem e som, bem como recursos interativos de compartilhamento social.
          </p>
          <div style="font-size:0.85rem; color:var(--text-muted);">
            <strong>Exemplos:</strong> <code>VISITOR_INFO1_LIVE, YSC</code> (player do YouTube), <code>sabor_mkt</code>.
          </div>
        </div>
      </section>

      <hr style="border:0; border-top:1px solid var(--border-color); margin: 30px 0;">

      <!-- Seção 4: Tabela Completa -->
      <section style="margin-bottom: 36px;">
        <h2 style="font-family: var(--font-heading); font-size: 1.45rem; font-weight: 700; color: var(--dark); margin-bottom: 14px; display:flex; align-items:center; gap:10px;">
          <span style="color:var(--primary);">4.</span> Inventário Completo de Cookies
        </h2>
        <p style="font-size:0.95rem; margin-bottom:16px;">
          Abaixo disponibilizamos a relação detalhada dos cookies que podem ser gerados durante sua visita:
        </p>

        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; font-size:0.88rem; border:1px solid var(--border-color); border-radius:10px; overflow:hidden;">
            <thead>
              <tr style="background:var(--bg-alt); color:var(--dark); text-align:left;">
                <th style="padding:12px 14px; border-bottom:2px solid var(--border-color);">Nome do Cookie</th>
                <th style="padding:12px 14px; border-bottom:2px solid var(--border-color);">Categoria</th>
                <th style="padding:12px 14px; border-bottom:2px solid var(--border-color);">Provedor</th>
                <th style="padding:12px 14px; border-bottom:2px solid var(--border-color);">Finalidade Técnica</th>
                <th style="padding:12px 14px; border-bottom:2px solid var(--border-color);">Validade</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);"><code>PHPSESSID</code></td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);"><span style="color:#2A9D8F; font-weight:600;">Necessário</span></td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Próprio</td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Identifica a sessão de navegação e login do usuário autenticado.</td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Sessão</td>
              </tr>
              <tr>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);"><code>sabor_cookie_consent</code></td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);"><span style="color:#2A9D8F; font-weight:600;">Necessário</span></td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Próprio</td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Salva as categorias autorizadas e token de prova perante a LGPD.</td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">180 dias</td>
              </tr>
              <tr>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);"><code>recipe_check_*</code></td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);"><span style="color:var(--primary); font-weight:600;">Preferência</span></td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Próprio</td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Salva a lista de ingredientes já marcados pelo cozinheiro na receita.</td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">30 dias</td>
              </tr>
              <tr>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);"><code>_ga, _gid</code></td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);"><span style="color:#4285F4; font-weight:600;">Analítico</span></td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Google Analytics</td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Estatísticas anônimas de visualizações e tempos de resposta de receitas.</td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Até 2 anos</td>
              </tr>
              <tr>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);"><code>VISITOR_INFO1_LIVE</code></td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);"><span style="color:#E63946; font-weight:600;">Marketing/Mídia</span></td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">YouTube</td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">Permite a reprodução do tutorial em vídeo incorporado da receita.</td>
                <td style="padding:10px 14px; border-bottom:1px solid var(--border-color);">6 meses</td>
              </tr>
              <tr>
                <td style="padding:10px 14px;"><code>YSC</code></td>
                <td style="padding:10px 14px;"><span style="color:#E63946; font-weight:600;">Marketing/Mídia</span></td>
                <td style="padding:10px 14px;">YouTube</td>
                <td style="padding:10px 14px;">Registra estatísticas de visualização do vídeo incorporado.</td>
                <td style="padding:10px 14px;">Sessão</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <hr style="border:0; border-top:1px solid var(--border-color); margin: 30px 0;">

      <!-- Seção 5: Como Gerenciar e Revogar -->
      <section style="margin-bottom: 36px;">
        <h2 style="font-family: var(--font-heading); font-size: 1.45rem; font-weight: 700; color: var(--dark); margin-bottom: 14px; display:flex; align-items:center; gap:10px;">
          <span style="color:var(--primary);">5.</span> Como Alterar ou Revogar seu Consentimento
        </h2>
        <p>
          A qualquer momento, sem qualquer custo ou burocracia, você pode modificar suas permissões ou revogar totalmente os cookies opcionais concedidos anteriormente. Para isso, você pode:
        </p>
        <ul style="margin: 10px 0 16px 24px;">
          <li>Clicar no botão flutuante com ícone de cookie 🍪 localizado no canto inferior esquerdo de qualquer página;</li>
          <li>Clicar no link <strong>"Preferências de Cookies"</strong> presente no rodapé do site;</li>
          <li>Clicar diretamente no botão abaixo:</li>
        </ul>
        <div style="margin: 16px 0;">
          <button type="button" class="btn btn-primary" onclick="CookieConsent.openPreferences()">
            <i class="fa-solid fa-sliders"></i> Abrir Minhas Preferências de Cookies
          </button>
        </div>
      </section>

      <hr style="border:0; border-top:1px solid var(--border-color); margin: 30px 0;">

      <!-- Seção 6: Desativar no Navegador -->
      <section style="margin-bottom: 36px;">
        <h2 style="font-family: var(--font-heading); font-size: 1.45rem; font-weight: 700; color: var(--dark); margin-bottom: 14px; display:flex; align-items:center; gap:10px;">
          <span style="color:var(--primary);">6.</span> Gerenciamento de Cookies pelo Próprio Navegador
        </h2>
        <p>
          Caso prefira, você também pode bloquear, recusar ou apagar cookies diretamente nas configurações do seu navegador de internet:
        </p>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-top:14px;">
          <a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener" style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:10px; padding:12px; display:flex; align-items:center; gap:10px;">
            <i class="fa-brands fa-chrome" style="font-size:1.4rem; color:#4285F4;"></i>
            <span style="font-size:0.9rem; font-weight:600;">Google Chrome</span>
          </a>
          <a href="https://support.mozilla.org/pt-BR/kb/gerencie-configuracoes-de-armazenamento-local-do-site" target="_blank" rel="noopener" style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:10px; padding:12px; display:flex; align-items:center; gap:10px;">
            <i class="fa-brands fa-firefox-browser" style="font-size:1.4rem; color:#FF7139;"></i>
            <span style="font-size:0.9rem; font-weight:600;">Mozilla Firefox</span>
          </a>
          <a href="https://support.apple.com/pt-br/guide/safari/sfri11471/mac" target="_blank" rel="noopener" style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:10px; padding:12px; display:flex; align-items:center; gap:10px;">
            <i class="fa-brands fa-safari" style="font-size:1.4rem; color:#006CFF;"></i>
            <span style="font-size:0.9rem; font-weight:600;">Apple Safari</span>
          </a>
          <a href="https://support.microsoft.com/pt-br/windows/gerenciar-cookies-no-microsoft-edge-exibir-permitir-bloquear-excluir-e-usar-168dab11-0753-043d-7c16-ede5947fc64d" target="_blank" rel="noopener" style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:10px; padding:12px; display:flex; align-items:center; gap:10px;">
            <i class="fa-brands fa-edge" style="font-size:1.4rem; color:#0078D7;"></i>
            <span style="font-size:0.9rem; font-weight:600;">Microsoft Edge</span>
          </a>
        </div>
      </section>

      <hr style="border:0; border-top:1px solid var(--border-color); margin: 30px 0;">

      <!-- Seção 7: Seus Direitos LGPD -->
      <section style="margin-bottom: 36px;">
        <h2 style="font-family: var(--font-heading); font-size: 1.45rem; font-weight: 700; color: var(--dark); margin-bottom: 14px; display:flex; align-items:center; gap:10px;">
          <span style="color:var(--primary);">7.</span> Seus Direitos como Titular de Dados (Art. 18 da LGPD)
        </h2>
        <p>
          Como titular de dados pessoais no Brasil, a LGPD confere a você direitos fundamentais, exercíveis a qualquer momento mediante requisição:
        </p>
        <ul style="margin: 12px 0 0 24px;">
          <li>Confirmação da existência de tratamento dos seus dados pessoais;</li>
          <li>Acesso facilitado aos seus dados armazenados em nossos registros;</li>
          <li>Correção de dados incompletos, inexatos ou desatualizados;</li>
          <li>Anonimização, bloqueio ou eliminação de dados desnecessários ou tratados em desconformidade;</li>
          <li>Eliminação dos dados pessoais tratados mediante consentimento (exceto nos casos autorizados por lei);</li>
          <li>Revogação do consentimento a qualquer instante por procedimento gratuito e facilitado.</li>
        </ul>
      </section>

      <hr style="border:0; border-top:1px solid var(--border-color); margin: 30px 0;">

      <!-- Seção 8: Contato do DPO / Encarregado -->
      <section>
        <h2 style="font-family: var(--font-heading); font-size: 1.45rem; font-weight: 700; color: var(--dark); margin-bottom: 14px; display:flex; align-items:center; gap:10px;">
          <span style="color:var(--primary);">8.</span> Encarregado de Proteção de Dados (DPO) e Contato
        </h2>
        <p>
          Para exercer qualquer direito previsto na LGPD, tirar dúvidas sobre o uso de cookies ou solicitar a exclusão definitiva dos seus dados de navegação, entre em contato direto com o nosso Encarregado de Proteção de Dados:
        </p>
        <div style="background:var(--bg-body); border:1px dashed var(--secondary); border-radius:12px; padding:18px 22px; margin-top:14px;">
          <div style="font-weight:700; color:var(--dark); margin-bottom:4px;">
            <i class="fa-solid fa-envelope" style="color:var(--primary);"></i> Canal Oficial de Privacidade & DPO
          </div>
          <div style="color:var(--text-main); font-size:0.95rem;">
            E-mail: <a href="mailto:privacidade@saboremcasa.com.br" style="color:var(--primary); font-weight:600; text-decoration:underline;">privacidade@saboremcasa.com.br</a>
          </div>
          <div style="color:var(--text-muted); font-size:0.85rem; margin-top:4px;">
            Tempo médio de resposta: até 48 horas úteis.
          </div>
        </div>
      </section>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

