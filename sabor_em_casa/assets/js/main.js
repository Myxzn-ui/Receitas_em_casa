/**
 * Sabor em Casa - Interatividades Frontend
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Menu Mobile Toggle
  const mobileToggle = document.getElementById('mobileToggle');
  const navMenu = document.getElementById('navMenu');

  if (mobileToggle && navMenu) {
    mobileToggle.addEventListener('click', () => {
      navMenu.classList.toggle('active');
      const icon = mobileToggle.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-times');
      }
    });
  }

  // 2. Dropdown do Usuário
  const userBtn = document.getElementById('userDropdownBtn');
  const userMenu = document.getElementById('userDropdownMenu');

  if (userBtn && userMenu) {
    userBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      userMenu.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
      if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) {
        userMenu.classList.remove('show');
      }
    });
  }

  // 3. Checklist de Ingredientes Interativa (Respeita Consentimento de Preferências LGPD)
  const ingredientItems = document.querySelectorAll('.ingredient-item');
  if (ingredientItems.length > 0) {
    const recipeId = document.body.dataset.recipeId || window.location.search;

    const restoreChecklist = () => {
      const hasPrefConsent = !window.CookieConsent || window.CookieConsent.hasConsent('preferencias');
      if (hasPrefConsent) {
        ingredientItems.forEach((item, index) => {
          const checkbox = item.querySelector('.ingredient-checkbox');
          const storageKey = `recipe_check_${recipeId}_${index}`;
          if (checkbox && localStorage.getItem(storageKey) === 'true') {
            checkbox.checked = true;
            item.classList.add('checked');
          }
        });
      }
    };

    restoreChecklist();

    ingredientItems.forEach((item, index) => {
      const checkbox = item.querySelector('.ingredient-checkbox');
      const storageKey = `recipe_check_${recipeId}_${index}`;

      if (checkbox) {
        checkbox.addEventListener('change', () => {
          const hasPrefConsent = !window.CookieConsent || window.CookieConsent.hasConsent('preferencias');
          if (checkbox.checked) {
            item.classList.add('checked');
            if (hasPrefConsent) {
              try { localStorage.setItem(storageKey, 'true'); } catch (e) {}
            }
          } else {
            item.classList.remove('checked');
            try { localStorage.removeItem(storageKey); } catch (e) {}
          }
        });
      }
    });

    // Se as preferências de cookies mudarem em tempo de execução
    window.addEventListener('sabor:cookie-consent-updated', (e) => {
      const categories = e.detail?.categorias || {};
      if (!categories.preferencias) {
        // Remove dados de preferências do localStorage se revogado
        Object.keys(localStorage).forEach(key => {
          if (key.startsWith('recipe_check_')) {
            localStorage.removeItem(key);
          }
        });
      } else {
        // Se autorizado agora, persiste os que estiverem marcados no momento
        ingredientItems.forEach((item, index) => {
          const checkbox = item.querySelector('.ingredient-checkbox');
          const storageKey = `recipe_check_${recipeId}_${index}`;
          if (checkbox && checkbox.checked) {
            try { localStorage.setItem(storageKey, 'true'); } catch (e) {}
          }
        });
      }
    });
  }

  // 4. Cálculo dinâmico de Idade no formulário de Cadastro
  const dataNascimentoInput = document.getElementById('data_nascimento');
  const idadePreview = document.getElementById('idade_preview');

  if (dataNascimentoInput && idadePreview) {
    const calcularIdadeFrontend = (dataStr) => {
      if (!dataStr) return null;
      const nasc = new Date(dataStr);
      const hoje = new Date();
      if (isNaN(nasc.getTime()) || nasc > hoje) return null;

      let idade = hoje.getFullYear() - nasc.getFullYear();
      const m = hoje.getMonth() - nasc.getMonth();
      if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) {
        idade--;
      }
      return idade;
    };

    dataNascimentoInput.addEventListener('change', () => {
      const idade = calcularIdadeFrontend(dataNascimentoInput.value);
      if (idade !== null && idade >= 0) {
        idadePreview.textContent = `Idade calculada: ${idade} ano${idade === 1 ? '' : 's'}`;
        idadePreview.style.display = 'block';
        idadePreview.style.color = '#2A9D8F';
      } else {
        idadePreview.textContent = 'Data inválida ou futura';
        idadePreview.style.display = 'block';
        idadePreview.style.color = '#E76F51';
      }
    });
  }

  // 5. Previews em tempo real no formulário de Publicar Receita
  const inputImagemUrl = document.getElementById('imagem_url');
  const previewImagemBox = document.getElementById('previewImagemBox');

  if (inputImagemUrl && previewImagemBox) {
    inputImagemUrl.addEventListener('input', () => {
      const url = inputImagemUrl.value.trim();
      if (url.length > 8) {
        previewImagemBox.innerHTML = `<img src="${url}" alt="Pré-visualização da receita" onerror="this.parentElement.innerHTML='<span style=\\'color:#E76F51\\'>Não foi possível carregar esta imagem</span>'">`;
      } else {
        previewImagemBox.innerHTML = '<span style="color:#A0AEC0">Pré-visualização da imagem aparecerá aqui</span>';
      }
    });
  }

  const inputVideoUrl = document.getElementById('video_url');
  const previewVideoBox = document.getElementById('previewVideoBox');

  if (inputVideoUrl && previewVideoBox) {
    const extrairYoutubeIdFrontend = (url) => {
      const reg = /(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/;
      const match = url.match(reg);
      return match ? match[1] : null;
    };

    inputVideoUrl.addEventListener('input', () => {
      const url = inputVideoUrl.value.trim();
      const ytId = extrairYoutubeIdFrontend(url);

      if (ytId) {
        previewVideoBox.innerHTML = `
          <div class="video-container" style="width:100%; border-radius:10px; overflow:hidden;">
            <iframe src="https://www.youtube.com/embed/${ytId}" frameborder="0" allowfullscreen></iframe>
          </div>
        `;
        previewVideoBox.style.display = 'block';
      } else if (url.length === 0) {
        previewVideoBox.innerHTML = '';
        previewVideoBox.style.display = 'none';
      } else {
        previewVideoBox.innerHTML = '<span style="font-size:0.85rem; color:#718096">Insira um link válido do YouTube para pré-visualizar o player aqui.</span>';
        previewVideoBox.style.display = 'block';
      }
    });
  }
});

// 6. Modal Interativo de Login / Cadastro com Google
function abrirModalGoogle() {
  const modal = document.getElementById('modalGoogle');
  if (modal) {
    modal.classList.add('show');
  }
}

function fecharModalGoogle() {
  const modal = document.getElementById('modalGoogle');
  if (modal) {
    modal.classList.remove('show');
  }
}
