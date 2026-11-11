/* Aviso de atualização do Painel Mestre.
 * Apenas verifica uma identificação pública de implantação; não lê nem envia registros.
 * Nunca recarrega automaticamente durante uma ação do usuário.
 */
(() => {
  'use strict';
  const loadedVersion = document.querySelector('meta[name="app-version"]')?.content || '';
  const banner = document.getElementById('app-update-banner');
  const checkButton = document.getElementById('app-update-check');
  const nowButton = document.getElementById('app-update-now');
  const laterButton = document.getElementById('app-update-later');
  const summary = document.getElementById('app-update-status');
  if (!banner || !checkButton || !nowButton || !laterButton || !summary) return;

  let checking = false;
  let availableVersion = '';
  const dismissedKey = 'geb-painel-adiar-versao';
  const endpoint = '/sistemas/rotinas-version.json';

  function setSummary(message) { summary.textContent = message; }
  function announce(version) {
    availableVersion = version;
    banner.hidden = false;
    setSummary('Atualização disponível.');
  }

  async function verifyVersion(manual = false) {
    if (checking) return;
    checking = true;
    if (manual) {
      checkButton.disabled = true;
      setSummary('Verificando versão...');
    }
    try {
      const response = await fetch(endpoint + '?t=' + Date.now(), {
        cache: 'no-store',
        credentials: 'same-origin',
        redirect: 'error'
      });
      if (!response.ok) throw new Error('Falha ao consultar a versão publicada.');
      const payload = await response.json();
      const latest = typeof payload.version === 'string' ? payload.version : '';
      if (!/^[a-f0-9]{40}$/.test(latest)) throw new Error('Versão do servidor inválida.');
      if (!loadedVersion) {
        setSummary('Verificação disponível após recarregar esta página uma vez.');
        return;
      }
      if (latest !== loadedVersion) {
        const dismissed = sessionStorage.getItem(dismissedKey);
        if (manual || dismissed !== latest) announce(latest);
        else setSummary('Nova versão adiada nesta sessão.');
      } else {
        availableVersion = '';
        banner.hidden = true;
        setSummary('Aplicativo atualizado · ' + latest.substring(0, 7));
      }
    } catch (error) {
      if (manual) setSummary('Não foi possível verificar a atualização. Confira sua conexão.');
    } finally {
      checking = false;
      checkButton.disabled = false;
    }
  }

  checkButton.addEventListener('click', () => verifyVersion(true));
  laterButton.addEventListener('click', () => {
    if (availableVersion) sessionStorage.setItem(dismissedKey, availableVersion);
    banner.hidden = true;
    setSummary('Atualização adiada. Você pode verificar novamente quando desejar.');
  });
  nowButton.addEventListener('click', async () => {
    // Só atualiza mediante ação explícita; não modifica registros ou estados.
    nowButton.disabled = true;
    setSummary('Reabrindo o aplicativo atualizado...');
    if (!navigator.onLine) {
      setSummary('Sem conexão. Conecte-se à internet antes de atualizar.');
      nowButton.disabled = false;
      return;
    }
    try {
      if ('serviceWorker' in navigator) {
        const registration = await navigator.serviceWorker.getRegistration('/sistemas/');
        if (registration) await registration.update();
      }
    } catch (_) {
      // O conteúdo da página não é armazenado no service worker; recarregar ainda é válido.
    }
    window.location.reload();
  });

  verifyVersion();
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) verifyVersion();
  });
  window.addEventListener('online', () => verifyVersion());
  window.setInterval(() => {
    if (!document.hidden) verifyVersion();
  }, 5 * 60 * 1000);
})();
