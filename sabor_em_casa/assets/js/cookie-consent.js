/**
 * Sabor em Casa - Sistema de Gestão de Consentimento de Cookies (LGPD)
 * Lei Geral de Proteção de Dados (Lei nº 13.709/2018)
 * 
 * Funcionalidades:
 * - Bloqueio prévio estrito (nenhum cookie opcional roda antes do opt-in)
 * - Aceitar todos, Rejeitar opcionais e Personalizar
 * - Execução dinâmica de scripts autorizados
 * - Limpeza de cookies revogados
 * - Registro com token único, data/hora, versão da política e auditoria
 * - Revogação e alteração a qualquer momento
 */

(function () {
  'use strict';

  const COOKIE_NAME = 'sabor_cookie_consent';
  const STORAGE_KEY = 'sabor_cookie_consent_data';
  const POLICY_VERSION = '1.0';
  const EXPIRATION_DAYS = 180; // 6 meses de validade
  const API_ENDPOINT = (window.BASE_URL || '') + '/api/cookie-consent.php';

  const CookieConsent = {
    state: {
      hasResponded: false,
      token: null,
      timestamp: null,
      dataHora: null,
      version: POLICY_VERSION,
      categories: {
        necessarios: true,
        preferencias: false,
        estatisticas: false,
        marketing: false
      }
    },

    listeners: [],

    init: function () {
      this.loadStoredConsent();
      this.bindDOM();

      if (!this.state.hasResponded) {
        // Primeira visita ou versão desatualizada: exibe banner
        this.showBanner();
      } else {
        // Já consentiu: aplica autorizações salvas
        this.applyConsent();
      }

      this.updateAuditDisplays();
      this.notifyListeners('init', this.state.categories);
    },

    // Lê cookie HTTP ou localStorage
    loadStoredConsent: function () {
      let data = null;

      // 1. Tenta ler cookie
      const cookieMatch = document.cookie.match(new RegExp('(^|;\\s*)' + COOKIE_NAME + '=([^;]+)'));
      if (cookieMatch) {
        try {
          data = JSON.parse(decodeURIComponent(cookieMatch[2]));
        } catch (e) {
          console.warn('Erro ao decodificar cookie de consentimento:', e);
        }
      }

      // 2. Fallback para localStorage
      if (!data) {
        try {
          const raw = localStorage.getItem(STORAGE_KEY);
          if (raw) data = JSON.parse(raw);
        } catch (e) {}
      }

      // Se encontrou dados válidos da mesma versão da política
      if (data && data.versao_politica === POLICY_VERSION && data.categorias) {
        this.state.hasResponded = true;
        this.state.token = data.consent_token || this.generateToken();
        this.state.timestamp = data.timestamp || Date.now();
        this.state.dataHora = data.data_hora || new Date().toLocaleString('pt-BR');
        this.state.version = data.versao_politica;
        this.state.categories = {
          necessarios: true,
          preferencias: Boolean(data.categorias.preferencias),
          estatisticas: Boolean(data.categorias.estatisticas),
          marketing: Boolean(data.categorias.marketing)
        };
      } else {
        this.state.hasResponded = false;
        this.state.token = this.generateToken();
      }
    },

    // Gera token único para auditoria LGPD (Art. 8º § 2º)
    generateToken: function () {
      const rand = Math.random().toString(36).substring(2, 10) + Math.random().toString(36).substring(2, 10);
      return 'cst_' + Date.now().toString(36) + '_' + rand;
    },

    // Salva consentimento localmente e envia registro ao servidor
    saveConsent: function (action, categories) {
      const now = new Date();
      const dataFormatada = now.toLocaleDateString('pt-BR') + ' ' + now.toLocaleTimeString('pt-BR');
      const timestamp = Math.floor(now.getTime() / 1000);

      this.state.hasResponded = true;
      this.state.timestamp = timestamp;
      this.state.dataHora = dataFormatada;
      this.state.version = POLICY_VERSION;
      this.state.categories = {
        necessarios: true,
        preferencias: Boolean(categories.preferencias),
        estatisticas: Boolean(categories.estatisticas),
        marketing: Boolean(categories.marketing)
      };

      const payload = {
        consent_token: this.state.token,
        timestamp: timestamp,
        data_hora: dataFormatada,
        versao_politica: POLICY_VERSION,
        acao: action,
        categorias: this.state.categories
      };

      // 1. Salva em cookie HTTP com SameSite
      const d = new Date();
      d.setTime(d.getTime() + (EXPIRATION_DAYS * 24 * 60 * 60 * 1000));
      const expires = 'expires=' + d.toUTCString();
      const cookieStr = COOKIE_NAME + '=' + encodeURIComponent(JSON.stringify(payload)) + ';' + expires + ';path=/;SameSite=Lax';
      document.cookie = cookieStr;

      // 2. Salva em localStorage para persistência redundante
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
      } catch (e) {}

      // 3. Envia para a API de registro / auditoria no servidor
      this.sendAuditLog(action, payload);

      // 4. Executa scripts aprovados e limpa não aprovados
      this.applyConsent();

      // 5. Interface
      this.hideBanner();
      this.closePreferences();
      this.updateAuditDisplays();
      this.showToast('Preferências de cookies salvas com sucesso!');

      // 6. Notifica ouvintes
      this.notifyListeners(action, this.state.categories);
      window.dispatchEvent(new CustomEvent('sabor:cookie-consent-updated', { detail: payload }));
    },

    // Envia registro assíncrono para a trilha de auditoria
    sendAuditLog: function (action, payload) {
      const dataToSend = {
        consent_token: payload.consent_token,
        acao: action,
        versao_politica: payload.versao_politica,
        categorias: payload.categorias
      };

      try {
        if (navigator.sendBeacon) {
          const blob = new Blob([JSON.stringify(dataToSend)], { type: 'application/json' });
          navigator.sendBeacon(API_ENDPOINT, blob);
        } else {
          fetch(API_ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dataToSend),
            keepalive: true
          }).catch(() => {});
        }
      } catch (err) {
        console.warn('Não foi possível enviar registro de auditoria ao servidor:', err);
      }
    },

    // Executa scripts de categorias aprovadas e limpa cookies de categorias revogadas
    applyConsent: function () {
      const cats = this.state.categories;

      // 1. Ativa scripts bloqueados conforme a categoria
      if (cats.estatisticas) {
        this.unblockScripts('estatisticas');
      } else {
        this.cleanCookies(['_ga', '_gid', '_gat', '_pk_', '_cl', 'sabor_analytics']);
      }

      if (cats.marketing) {
        this.unblockScripts('marketing');
        this.unlockMediaPlaceholders();
      } else {
        this.cleanCookies(['_fbp', 'VISITOR_INFO1_LIVE', 'YSC', 'sabor_mkt']);
        this.lockMediaPlaceholders();
      }

      if (cats.preferencias) {
        this.unblockScripts('preferencias');
      } else {
        // Se revogou preferências, remove dados de customização se desejado
      }
    },

    // Desbloqueia tags <script type="text/plain" data-cookie-category="...">
    unblockScripts: function (category) {
      const scripts = document.querySelectorAll(`script[type="text/plain"][data-cookie-category="${category}"]`);
      scripts.forEach(oldScript => {
        const newScript = document.createElement('script');
        // Copia atributos
        Array.from(oldScript.attributes).forEach(attr => {
          if (attr.name !== 'type' && attr.name !== 'data-cookie-category') {
            newScript.setAttribute(attr.name, attr.value);
          }
        });
        newScript.type = 'text/javascript';
        newScript.text = oldScript.text;
        
        // Substitui no DOM para executar
        oldScript.parentNode.replaceChild(newScript, oldScript);
      });
    },

    // Remove cookies pelo prefixo ou nome
    cleanCookies: function (cookieNames) {
      const domains = [window.location.hostname, '.' + window.location.hostname, ''];
      const paths = ['/', window.location.pathname];

      cookieNames.forEach(name => {
        domains.forEach(domain => {
          paths.forEach(path => {
            document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=${path}; ${domain ? 'domain=' + domain + ';' : ''}`;
          });
        });
      });
    },

    // Manipulação dos Placeholders de Vídeo (YouTube) na página de Receita
    lockMediaPlaceholders: function () {
      const videoWrappers = document.querySelectorAll('[data-cookie-protected-video]');
      videoWrappers.forEach(wrap => {
        const videoId = wrap.getAttribute('data-youtube-id');
        if (!videoId) return;

        if (!wrap.querySelector('.cookie-video-placeholder')) {
          wrap.setAttribute('data-original-html', wrap.innerHTML);
          wrap.innerHTML = `
            <div class="cookie-video-placeholder">
              <div class="placeholder-icon"><i class="fa-solid fa-film"></i></div>
              <h4>Conteúdo de Vídeo Bloqueado</h4>
              <p>Este tutorial em vídeo do YouTube requer autorização de cookies da categoria <strong>Marketing & Mídia</strong> para ser exibido, conforme a LGPD.</p>
              <button type="button" class="btn-unlock-media" onclick="CookieConsent.quickAcceptCategory('marketing')">
                <i class="fa-solid fa-circle-check"></i> Autorizar Vídeos e Assistir
              </button>
            </div>
          `;
        }
      });
    },

    unlockMediaPlaceholders: function () {
      const videoWrappers = document.querySelectorAll('[data-cookie-protected-video]');
      videoWrappers.forEach(wrap => {
        const videoId = wrap.getAttribute('data-youtube-id');
        if (videoId) {
          wrap.innerHTML = `
            <div class="video-container" style="position:relative; width:100%; padding-bottom:56.25%; height:0; overflow:hidden; border-radius:14px;">
              <iframe 
                src="https://www.youtube-nocookie.com/embed/${videoId}?autoplay=1" 
                title="Tutorial em Vídeo da Receita" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen
                style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;">
              </iframe>
            </div>
          `;
        }
      });
    },

    quickAcceptCategory: function (category) {
      const cats = Object.assign({}, this.state.categories);
      cats[category] = true;
      this.saveConsent('personalizado', cats);
      this.syncModalInputs();
    },

    // ==========================================
    // Métodos Públicos e Ações Principais (LGPD)
    // ==========================================

    acceptAll: function () {
      this.saveConsent('aceitar_todos', {
        necessarios: true,
        preferencias: true,
        estatisticas: true,
        marketing: true
      });
    },

    rejectOptional: function () {
      this.saveConsent('rejeitar_opcionais', {
        necessarios: true,
        preferencias: false,
        estatisticas: false,
        marketing: false
      });
    },

    saveCustomPreferences: function () {
      const switchPref = document.getElementById('cookieSwitchPreferences');
      const switchStats = document.getElementById('cookieSwitchAnalytics');
      const switchMkt = document.getElementById('cookieSwitchMarketing');

      const cats = {
        necessarios: true,
        preferencias: switchPref ? switchPref.checked : false,
        estatisticas: switchStats ? switchStats.checked : false,
        marketing: switchMkt ? switchMkt.checked : false
      };

      this.saveConsent('personalizado', cats);
    },

    revokeConsent: function () {
      if (confirm('Deseja realmente revogar todos os cookies opcionais e reabrir o aviso de privacidade?')) {
        this.saveConsent('revogado', {
          necessarios: true,
          preferencias: false,
          estatisticas: false,
          marketing: false
        });
        this.showBanner();
        this.openPreferences();
      }
    },

    hasConsent: function (category) {
      if (category === 'necessarios') return true;
      return Boolean(this.state.categories[category]);
    },

    getConsentData: function () {
      return Object.assign({}, this.state);
    },

    onConsentChange: function (callback) {
      if (typeof callback === 'function') {
        this.listeners.push(callback);
      }
    },

    notifyListeners: function (action, categories) {
      this.listeners.forEach(cb => {
        try {
          cb(action, categories);
        } catch (e) {
          console.error('Erro no listener de consentimento:', e);
        }
      });
    },

    // ==========================================
    // UI / DOM Binding
    // ==========================================

    bindDOM: function () {
      const self = this;

      // Botões do Banner
      const btnAccept = document.getElementById('btnCookieAcceptAll');
      const btnReject = document.getElementById('btnCookieRejectOptional');
      const btnConfig = document.getElementById('btnCookieCustomize');

      if (btnAccept) btnAccept.addEventListener('click', () => self.acceptAll());
      if (btnReject) btnReject.addEventListener('click', () => self.rejectOptional());
      if (btnConfig) btnConfig.addEventListener('click', () => self.openPreferences());

      // Botões do Modal
      const btnModalSave = document.getElementById('btnCookieSaveCustom');
      const btnModalAccept = document.getElementById('btnCookieModalAcceptAll');
      const btnModalReject = document.getElementById('btnCookieModalRejectOptional');
      const btnModalRevoke = document.getElementById('btnCookieRevokeConsent');
      const btnModalClose = document.getElementById('btnCookieModalClose');
      const modalOverlay = document.getElementById('cookiePreferencesModal');

      if (btnModalSave) btnModalSave.addEventListener('click', () => self.saveCustomPreferences());
      if (btnModalAccept) btnModalAccept.addEventListener('click', () => self.acceptAll());
      if (btnModalReject) btnModalReject.addEventListener('click', () => self.rejectOptional());
      if (btnModalRevoke) btnModalRevoke.addEventListener('click', () => self.revokeConsent());
      if (btnModalClose) btnModalClose.addEventListener('click', () => self.closePreferences());

      // Fechar modal ao clicar fora
      if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
          if (e.target === modalOverlay) self.closePreferences();
        });
      }

      // Tecla ESC fecha modal
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') self.closePreferences();
      });

      // Accordion de detalhes nas categorias do modal
      const catCards = document.querySelectorAll('.cookie-category-card');
      catCards.forEach(card => {
        const header = card.querySelector('.cookie-category-header');
        if (header) {
          header.addEventListener('click', (e) => {
            // Não expande se clicou no switch
            if (e.target.closest('.cookie-switch')) return;
            card.classList.toggle('expanded');
          });
        }
      });

      // Botão Flutuante Permanente
      const floatingBtn = document.getElementById('cookieFloatingButton');
      if (floatingBtn) {
        floatingBtn.addEventListener('click', () => self.openPreferences());
      }

      // Links do rodapé
      const footerTriggers = document.querySelectorAll('.js-open-cookie-preferences');
      footerTriggers.forEach(el => {
        el.addEventListener('click', (e) => {
          e.preventDefault();
          self.openPreferences();
        });
      });
    },

    showBanner: function () {
      const banner = document.getElementById('cookieConsentBanner');
      if (banner) {
        banner.classList.add('show');
      }
    },

    hideBanner: function () {
      const banner = document.getElementById('cookieConsentBanner');
      if (banner) {
        banner.classList.remove('show');
      }
    },

    openPreferences: function () {
      this.syncModalInputs();
      this.updateAuditDisplays();
      const modal = document.getElementById('cookiePreferencesModal');
      if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
      }
    },

    closePreferences: function () {
      const modal = document.getElementById('cookiePreferencesModal');
      if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
      }
    },

    syncModalInputs: function () {
      const switchPref = document.getElementById('cookieSwitchPreferences');
      const switchStats = document.getElementById('cookieSwitchAnalytics');
      const switchMkt = document.getElementById('cookieSwitchMarketing');

      if (switchPref) switchPref.checked = Boolean(this.state.categories.preferencias);
      if (switchStats) switchStats.checked = Boolean(this.state.categories.estatisticas);
      if (switchMkt) switchMkt.checked = Boolean(this.state.categories.marketing);
    },

    updateAuditDisplays: function () {
      const tokenEl = document.getElementById('cookieConsentTokenText');
      const dateEl = document.getElementById('cookieConsentDateText');
      const statusBadge = document.getElementById('cookieConsentStatusBadge');

      if (tokenEl) {
        tokenEl.textContent = this.state.hasResponded ? this.state.token : 'Ainda não registrado';
      }
      if (dateEl) {
        dateEl.textContent = this.state.hasResponded ? this.state.dataHora : 'Pendente de consentimento';
      }
      if (statusBadge) {
        if (this.state.hasResponded) {
          statusBadge.innerHTML = '<span class="badge-always-active" style="background:#E8F7F5; color:#2A9D8F;"><i class="fa-solid fa-circle-check"></i> Consentimento Ativo</span>';
        } else {
          statusBadge.innerHTML = '<span class="badge-always-active" style="background:#FFF3CD; color:#856404;"><i class="fa-solid fa-clock"></i> Pendente</span>';
        }
      }

      // Se houver painel interativo na página de Política de Cookies
      const policyStatusTable = document.getElementById('policyStatusTableContainer');
      if (policyStatusTable) {
        this.renderPolicyPageStatus();
      }
    },

    renderPolicyPageStatus: function () {
      const container = document.getElementById('policyStatusTableContainer');
      if (!container) return;

      const c = this.state.categories;
      const resp = this.state.hasResponded;

      container.innerHTML = `
        <div style="background:#FFFFFF; border:1.5px solid var(--border-color); border-radius:14px; padding:20px; margin:20px 0;">
          <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
            <h4 style="margin:0; font-family:var(--font-heading); color:var(--dark);">
              <i class="fa-solid fa-fingerprint" style="color:var(--primary);"></i> Seu Estado Atual de Consentimento
            </h4>
            <span style="font-size:0.85rem; font-weight:600; padding:4px 10px; border-radius:8px; ${resp ? 'background:#E8F7F5; color:#2A9D8F;' : 'background:#FFF3CD; color:#856404;'}">
              ${resp ? '✓ Consentimento Registrado' : '⏳ Aguardando Decisão'}
            </span>
          </div>

          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:18px;">
            <div style="background:var(--bg-body); padding:12px; border-radius:10px; border:1px solid #EAE3D9;">
              <div style="font-size:0.8rem; color:var(--text-muted);">Necessários</div>
              <strong style="color:#2A9D8F;">Sempre Ativo</strong>
            </div>
            <div style="background:var(--bg-body); padding:12px; border-radius:10px; border:1px solid #EAE3D9;">
              <div style="font-size:0.8rem; color:var(--text-muted);">Preferências</div>
              <strong style="color:${c.preferencias ? '#2A9D8F' : '#E76F51'};">${c.preferencias ? 'Autorizado' : 'Não autorizado'}</strong>
            </div>
            <div style="background:var(--bg-body); padding:12px; border-radius:10px; border:1px solid #EAE3D9;">
              <div style="font-size:0.8rem; color:var(--text-muted);">Analíticos</div>
              <strong style="color:${c.estatisticas ? '#2A9D8F' : '#E76F51'};">${c.estatisticas ? 'Autorizado' : 'Não autorizado'}</strong>
            </div>
            <div style="background:var(--bg-body); padding:12px; border-radius:10px; border:1px solid #EAE3D9;">
              <div style="font-size:0.8rem; color:var(--text-muted);">Marketing</div>
              <strong style="color:${c.marketing ? '#2A9D8F' : '#E76F51'};">${c.marketing ? 'Autorizado' : 'Não autorizado'}</strong>
            </div>
          </div>

          <div style="font-size:0.82rem; color:var(--text-muted); line-height:1.6; margin-bottom:16px;">
            <div><strong>Token de Auditoria LGPD:</strong> <code>${this.state.token || 'Pendente'}</code></div>
            <div><strong>Última Alteração:</strong> ${this.state.dataHora || 'Pendente'}</div>
            <div><strong>Versão da Política:</strong> ${this.state.version}</div>
          </div>

          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button type="button" class="btn btn-primary btn-sm" onclick="CookieConsent.openPreferences()">
              <i class="fa-solid fa-sliders"></i> Alterar Minhas Escolhas Agora
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="CookieConsent.revokeConsent()" style="color:#B93E22;">
              <i class="fa-solid fa-ban"></i> Revogar Todos os Opcionais
            </button>
          </div>
        </div>
      `;
    },

    showToast: function (msg) {
      let toast = document.getElementById('cookieToastNotification');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cookieToastNotification';
        toast.className = 'cookie-toast';
        document.body.appendChild(toast);
      }

      toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> <span>${msg}</span>`;
      toast.classList.add('show');

      setTimeout(() => {
        toast.classList.remove('show');
      }, 3500);
    }
  };

  // Expõe no escopo global
  window.CookieConsent = CookieConsent;

  // Inicializa quando o DOM estiver pronto
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => CookieConsent.init());
  } else {
    CookieConsent.init();
  }
})();

