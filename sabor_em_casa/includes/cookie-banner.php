<?php
/**
 * Componente Visual de Consentimento de Cookies (LGPD)
 * Sabor em Casa - Lei Geral de Proteção de Dados (Lei nº 13.709/2018)
 */
?>

<!-- =====================================================================
     1. Banner Principal de Cookies (Aviso em Primeiro Acesso)
     ===================================================================== -->
<div class="cookie-banner-wrapper" id="cookieConsentBanner" role="region" aria-label="Aviso de Privacidade e Consentimento de Cookies">
  <div class="cookie-banner-card">
    <div class="cookie-banner-content">
      <div class="cookie-banner-icon" aria-hidden="true">
        <i class="fa-solid fa-cookie-bite"></i>
      </div>
      <div class="cookie-banner-text">
        <h3>
          Sua privacidade é nosso ingrediente especial 🍪
          <span class="lgpd-tag"><i class="fa-solid fa-shield-halved"></i> LGPD</span>
        </h3>
        <p>
          Utilizamos cookies essenciais para manter você conectado(a) e cookies opcionais para lembrar suas receitas favoritas, aprimorar nossa plataforma e reproduzir tutoriais culinários em vídeo. Em conformidade com a <strong>LGPD (Lei nº 13.709/2018)</strong>, solicitamos seu consentimento para ativar os recursos opcionais. Você tem total controle para escolher ou alterar suas preferências a qualquer momento.
          <a href="<?= BASE_URL ?>/politica-cookies.php" class="cookie-policy-link">Leia nossa Política de Cookies</a>.
        </p>
      </div>
    </div>

    <!-- Botões de Ação do Banner -->
    <div class="cookie-banner-actions">
      <button type="button" class="btn-cookie-accept" id="btnCookieAcceptAll">
        <i class="fa-solid fa-check"></i> Aceitar Todos
      </button>
      <button type="button" class="btn-cookie-reject" id="btnCookieRejectOptional">
        <i class="fa-solid fa-ban"></i> Rejeitar Opcionais
      </button>
      <button type="button" class="btn-cookie-settings" id="btnCookieCustomize">
        <i class="fa-solid fa-sliders"></i> Personalizar Cookies
      </button>
    </div>
  </div>
</div>

<!-- =====================================================================
     2. Modal Completo de Personalização de Cookies (LGPD)
     ===================================================================== -->
<div class="cookie-modal-overlay" id="cookiePreferencesModal" role="dialog" aria-modal="true" aria-labelledby="cookieModalTitle">
  <div class="cookie-modal-dialog">
    
    <!-- Cabeçalho do Modal -->
    <div class="cookie-modal-header">
      <div class="cookie-modal-header-info">
        <div class="cookie-modal-header-icon">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
          <h3 class="cookie-modal-title" id="cookieModalTitle">Central de Privacidade e Cookies</h3>
          <div class="cookie-modal-subtitle">
            <span><i class="fa-solid fa-scale-balanced" style="color:var(--primary);"></i> Em conformidade com a Lei Federal nº 13.709/2018 (LGPD)</span>
          </div>
        </div>
      </div>
      <button type="button" class="cookie-modal-close" id="btnCookieModalClose" aria-label="Fechar">&times;</button>
    </div>

    <!-- Corpo do Modal com Categorias e Detalhes -->
    <div class="cookie-modal-body">
      
      <div class="cookie-modal-intro">
        <strong>Transparência e Consentimento Informado:</strong> Você pode habilitar ou desabilitar cada categoria de cookies abaixo. Cookies estritamente necessários não podem ser desativados pois sustentam o funcionamento e segurança básica do Sabor em Casa. Suas escolhas são registradas de forma segura e auditável.
      </div>

      <div class="cookie-categories-list">
        
        <!-- Categoria 1: Necessários -->
        <div class="cookie-category-card">
          <div class="cookie-category-header">
            <div class="cookie-category-title-group">
              <div class="cookie-category-icon">
                <i class="fa-solid fa-lock"></i>
              </div>
              <div>
                <div class="cookie-category-name">
                  Cookies Estritamente Necessários
                  <span class="badge-always-active">Sempre Ativo</span>
                </div>
                <div style="font-size:0.82rem; color:var(--text-muted);">Essenciais para navegação, login seguro e conformidade legal.</div>
              </div>
            </div>
            <div class="cookie-category-toggle-group">
              <label class="cookie-switch" title="Esta categoria é obrigatória para o funcionamento da plataforma">
                <input type="checkbox" checked disabled>
                <span class="cookie-slider"></span>
              </label>
              <i class="fa-solid fa-chevron-down cookie-chevron" title="Clique para ver os cookies desta categoria"></i>
            </div>
          </div>
          <div class="cookie-category-details">
            <p class="cookie-category-desc">
              Estes cookies são fundamentais para que o site funcione com segurança e eficiência. Eles viabilizam a manutenção da sua sessão de login, navegação entre páginas sem perda de dados temporários, prevenção a ataques CSRF e salvamento seguro do seu próprio termo de consentimento. Base legal: Legítimo Interesse e Execução Contratual (Art. 7º, IX e V da LGPD).
            </p>
            <table class="cookie-table-mini">
              <thead>
                <tr>
                  <th>Cookie / Recurso</th>
                  <th>Provedor</th>
                  <th>Finalidade</th>
                  <th>Duração</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><code>PHPSESSID</code></td>
                  <td>Sabor em Casa</td>
                  <td>Mantém a autenticação da sua conta e dados de sessão seguros.</td>
                  <td>Sessão</td>
                </tr>
                <tr>
                  <td><code>sabor_cookie_consent</code></td>
                  <td>Sabor em Casa</td>
                  <td>Armazena suas escolhas de privacidade e token de auditoria LGPD.</td>
                  <td>6 meses</td>
                </tr>
                <tr>
                  <td><code>csrf_token</code></td>
                  <td>Sabor em Casa</td>
                  <td>Protege seus envios de receitas e comentários contra fraudes.</td>
                  <td>Sessão</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Categoria 2: Preferências -->
        <div class="cookie-category-card">
          <div class="cookie-category-header">
            <div class="cookie-category-title-group">
              <div class="cookie-category-icon">
                <i class="fa-solid fa-sliders"></i>
              </div>
              <div>
                <div class="cookie-category-name">Cookies de Preferência</div>
                <div style="font-size:0.82rem; color:var(--text-muted);">Lembram sua personalização e o progresso das receitas.</div>
              </div>
            </div>
            <div class="cookie-category-toggle-group">
              <label class="cookie-switch" aria-label="Ativar cookies de preferência">
                <input type="checkbox" id="cookieSwitchPreferences">
                <span class="cookie-slider"></span>
              </label>
              <i class="fa-solid fa-chevron-down cookie-chevron" title="Clique para ver os cookies desta categoria"></i>
            </div>
          </div>
          <div class="cookie-category-details">
            <p class="cookie-category-desc">
              Permitem que o Sabor em Casa memorize suas preferências pessoais de uso, como os ingredientes que você já marcou na lista de checagem enquanto cozinha, tamanho de fonte preferido e filtros pré-selecionados de receitas. Base legal: Consentimento (Art. 7º, I da LGPD).
            </p>
            <table class="cookie-table-mini">
              <thead>
                <tr>
                  <th>Armazenamento</th>
                  <th>Provedor</th>
                  <th>Finalidade</th>
                  <th>Duração</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><code>recipe_check_*</code></td>
                  <td>Sabor em Casa</td>
                  <td>Memoriza os itens que você ticou na receita enquanto cozinha.</td>
                  <td>30 dias</td>
                </tr>
                <tr>
                  <td><code>sabor_ui_pref</code></td>
                  <td>Sabor em Casa</td>
                  <td>Lembra modos de exibição de lista/grade e filtros de busca.</td>
                  <td>1 ano</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Categoria 3: Analíticos / Estatísticas -->
        <div class="cookie-category-card">
          <div class="cookie-category-header">
            <div class="cookie-category-title-group">
              <div class="cookie-category-icon">
                <i class="fa-solid fa-chart-line"></i>
              </div>
              <div>
                <div class="cookie-category-name">Cookies Estatísticos / Analíticos</div>
                <div style="font-size:0.82rem; color:var(--text-muted);">Medem visitas e melhoram o desempenho de forma anônima.</div>
              </div>
            </div>
            <div class="cookie-category-toggle-group">
              <label class="cookie-switch" aria-label="Ativar cookies analíticos">
                <input type="checkbox" id="cookieSwitchAnalytics">
                <span class="cookie-slider"></span>
              </label>
              <i class="fa-solid fa-chevron-down cookie-chevron" title="Clique para ver os cookies desta categoria"></i>
            </div>
          </div>
          <div class="cookie-category-details">
            <p class="cookie-category-desc">
              Ajudam a entender quais receitas são mais apreciadas, o tempo médio de leitura dos passos culinários e possíveis erros de navegação. Os dados são agregados e anonimizados (com mascaramento de endereço IP), nunca utilizados para criar perfis individuais invasivos. Base legal: Consentimento (Art. 7º, I da LGPD).
            </p>
            <table class="cookie-table-mini">
              <thead>
                <tr>
                  <th>Cookie</th>
                  <th>Provedor</th>
                  <th>Finalidade</th>
                  <th>Duração</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><code>_ga, _gid</code></td>
                  <td>Google Analytics / Métricas</td>
                  <td>Contabiliza visitas e receitas mais visualizadas anonimamente.</td>
                  <td>Até 2 anos</td>
                </tr>
                <tr>
                  <td><code>sabor_analytics</code></td>
                  <td>Sabor em Casa</td>
                  <td>Métrica agregada de visualizações e tempos de resposta do site.</td>
                  <td>1 ano</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Categoria 4: Marketing & Mídia -->
        <div class="cookie-category-card">
          <div class="cookie-category-header">
            <div class="cookie-category-title-group">
              <div class="cookie-category-icon">
                <i class="fa-solid fa-play"></i>
              </div>
              <div>
                <div class="cookie-category-name">Cookies de Marketing & Mídia</div>
                <div style="font-size:0.82rem; color:var(--text-muted);">Habilitam players de vídeo (YouTube) e interações sociais.</div>
              </div>
            </div>
            <div class="cookie-category-toggle-group">
              <label class="cookie-switch" aria-label="Ativar cookies de marketing">
                <input type="checkbox" id="cookieSwitchMarketing">
                <span class="cookie-slider"></span>
              </label>
              <i class="fa-solid fa-chevron-down cookie-chevron" title="Clique para ver os cookies desta categoria"></i>
            </div>
          </div>
          <div class="cookie-category-details">
            <p class="cookie-category-desc">
              Utilizados para permitir a reprodução direta dos vídeos tutoriais do YouTube incorporados nas receitas do site, botões de compartilhamento em redes sociais e recomendações gastronômicas relevantes. Sem estes cookies, os players de vídeo incorporados ficarão protegidos até sua autorização expressa. Base legal: Consentimento (Art. 7º, I da LGPD).
            </p>
            <table class="cookie-table-mini">
              <thead>
                <tr>
                  <th>Cookie</th>
                  <th>Provedor</th>
                  <th>Finalidade</th>
                  <th>Duração</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><code>VISITOR_INFO1_LIVE, YSC</code></td>
                  <td>YouTube / Google</td>
                  <td>Permite carregar e reproduzir os vídeos passo a passo das receitas.</td>
                  <td>Até 6 meses</td>
                </tr>
                <tr>
                  <td><code>sabor_mkt</code></td>
                  <td>Sabor em Casa</td>
                  <td>Mede o engajamento com vídeos e receitas em destaque.</td>
                  <td>6 meses</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Registro de Auditoria e Comprovante LGPD (Art. 8º § 2º) -->
      <div class="cookie-audit-info">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
          <strong><i class="fa-solid fa-fingerprint"></i> Registro e Auditoria de Consentimento (LGPD)</strong>
          <span id="cookieConsentStatusBadge"></span>
        </div>
        <div><strong>ID do Consentimento:</strong> <code id="cookieConsentTokenText">Pendente</code></div>
        <div><strong>Data e Hora do Registro:</strong> <span id="cookieConsentDateText">Aguardando decisão</span></div>
        <div><strong>Versão da Política Vigente:</strong> <span>1.0 (Novembro/2026)</span></div>
        <div><strong>Encarregado de Dados (DPO):</strong> <a href="mailto:privacidade@saboremcasa.com.br" style="color:var(--primary); text-decoration:underline;">privacidade@saboremcasa.com.br</a></div>
      </div>

    </div>

    <!-- Rodapé com Botões de Ação do Modal -->
    <div class="cookie-modal-footer">
      <button type="button" class="btn-cookie-revoke" id="btnCookieRevokeConsent">
        <i class="fa-solid fa-rotate-left"></i> Revogar Todos os Opcionais
      </button>
      <div class="cookie-modal-footer-actions">
        <button type="button" class="btn-cookie-reject" id="btnCookieModalRejectOptional">
          Rejeitar Opcionais
        </button>
        <button type="button" class="btn-cookie-save" id="btnCookieSaveCustom">
          <i class="fa-solid fa-check"></i> Salvar Minhas Escolhas
        </button>
        <button type="button" class="btn-cookie-accept" id="btnCookieModalAcceptAll">
          Aceitar Todos
        </button>
      </div>
    </div>

  </div>
</div>

<!-- =====================================================================
     3. Botão Flutuante Permanente para Alteração a Qualquer Momento
     ===================================================================== -->
<button type="button" class="cookie-floating-trigger" id="cookieFloatingButton" aria-label="Gerenciar preferências de privacidade e cookies (LGPD)" title="Preferências de Cookies (LGPD)">
  <i class="fa-solid fa-cookie-bite"></i>
  <span>Preferências de Cookies</span>
</button>

