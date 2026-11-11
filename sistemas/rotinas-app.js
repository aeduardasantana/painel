/* Painel Mestre · PWA e preferências de alertas.
 * Web Push, quando autorizado, recebe eventos do servidor com o app fechado.
 * Sem Web Push, usa lembretes locais apenas enquanto o painel estiver aberto.
 */
(() => {
  'use strict';
  const enable = document.getElementById('notify-enable');
  const disable = document.getElementById('notify-disable');
  const install = document.getElementById('install-app');
  const status = document.getElementById('notify-status');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  if (!enable || !disable || !install || !status) return;

  const PREF = 'geb-rotinas-alertas-v1';
  let registration = null;
  let mode = 'off';
  let installEvent = null;
  const canNotify = ('Notification' in window) && ('serviceWorker' in navigator);
  const canPush = canNotify && ('PushManager' in window);
  function message(text) { status.textContent = text; }
  function render() {
    const active = localStorage.getItem(PREF) === 'on' && Notification.permission === 'granted';
    enable.hidden = active;
    disable.hidden = !active;
  }
  function brazilDateAndMinute() {
    const parts = new Intl.DateTimeFormat('en-US', {
      timeZone: 'America/Sao_Paulo', year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', hour12: false
    }).formatToParts(new Date());
    const v = Object.fromEntries(parts.map(p => [p.type, p.value]));
    return {date: v.year+'-'+v.month+'-'+v.day, minute: (Number(v.hour)%24)*60+Number(v.minute)};
  }
  function decodeKey(input) {
    const base64 = input.replace(/-/g,'+').replace(/_/g,'/');
    const binary = atob(base64 + '='.repeat((4-base64.length%4)%4));
    return Uint8Array.from(binary, char => char.charCodeAt(0));
  }
  async function serviceWorker() {
    if (!registration && ('serviceWorker' in navigator)) {
      registration = await navigator.serviceWorker.register('/sistemas/rotinas-sw.js', {scope:'/sistemas/'});
    }
    return registration;
  }
  async function api(url, options = {}) {
    const response = await fetch(url, {credentials:'same-origin',cache:'no-store',...options});
    if (!response.ok) {
      if (response.status === 401) throw new Error('Sessão expirada. Faça login novamente.');
      throw new Error('Serviço de alertas indisponível ('+response.status+').');
    }
    return response.json();
  }
  async function updateSubscription(action, subscription) {
    return api('/sistemas/rotinas-push.php', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},
      body:JSON.stringify({action,subscription:subscription.toJSON()})
    });
  }
  async function enableAlerts() {
    if (!canNotify) { message('Este navegador não oferece notificações para este aplicativo.'); return; }
    try {
      // Solicitação diretamente vinculada ao clique, exigida por alguns navegadores.
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') { message('Autorize as notificações nas configurações do navegador.'); return; }
      await serviceWorker();
      localStorage.setItem(PREF,'on');
      if (canPush) {
        try {
          const config = await api('/sistemas/rotinas-push.php');
          let subscription = await registration.pushManager.getSubscription();
          if (!subscription) {
            subscription = await registration.pushManager.subscribe({
              userVisibleOnly:true, applicationServerKey:decodeKey(config.publicKey)
            });
          }
          await updateSubscription('subscribe',subscription);
          mode = 'push';
          message('Alertas ativados neste dispositivo. Lembretes em segundo plano dependem do agendador do servidor.');
        } catch (pushError) {
          mode = 'local';
          message('Alertas locais ativados enquanto o painel estiver aberto. Push em segundo plano indisponível neste dispositivo ou servidor.');
        }
      } else {
        mode = 'local';
        message('Alertas ativados enquanto o painel estiver aberto. Este navegador não oferece Web Push.');
      }
      render();
      if (mode==='local') checkLocal();
    } catch (error) {
      message(error.message || 'Não foi possível ativar notificações.');
    }
  }
  async function disableAlerts() {
    try {
      const reg = await serviceWorker();
      const sub = await reg?.pushManager?.getSubscription?.();
      if (sub) {
        await updateSubscription('unsubscribe',sub);
        await sub.unsubscribe();
      }
      localStorage.removeItem(PREF);
      mode = 'off';
      message('Alertas desativados neste dispositivo.');
      render();
    } catch (error) {
      message('Falha ao remover a inscrição do servidor. Tente novamente antes de considerar os alertas desativados.');
    }
  }
  async function checkLocal() {
    if (mode!=='local' || Notification.permission!=='granted') return;
    const now = brazilDateAndMinute();
    let tasks;
    try {
      const r=await api('/sistemas/rotinas-status.php');
      if (r.date!==now.date) return;
      tasks=r.tasks||{};
    } catch (_) { return; }
    for (const [id,task] of Object.entries(tasks)) {
      if (task.status==='feito') continue;
      const [hour,minute]=(task.hour||'').split(':').map(Number);
      if (!Number.isFinite(hour) || !Number.isFinite(minute)) continue;
      const diff=now.minute-(hour*60+minute);
      // Evitar avisos durante a madrugada. Programação de 08:00 a 20:59.
      if (diff<0 || now.minute>20*60+59) continue;
      const slot=Math.floor(diff/60);
      const marker='geb-rotinas-alertado-'+now.date+'-'+id+'-'+slot;
      if (localStorage.getItem(marker)) continue;
      try {
        await registration.showNotification('Painel Mestre · '+task.hour, {
          body:task.title+(slot>0?' — ainda não concluído':''),
          tag:'rotina-'+now.date+'-'+id,
          icon:'/sistemas/rotinas-icon.svg',
          data:{url:'/sistemas/rotinas.php?data='+now.date}
        });
        localStorage.setItem(marker,'1');
      } catch (_) {
        message('O dispositivo não conseguiu apresentar o alerta. Verifique as permissões.');
      }
    }
  }
  enable.addEventListener('click',enableAlerts);
  disable.addEventListener('click',disableAlerts);
  window.addEventListener('beforeinstallprompt',event => {
    event.preventDefault();
    installEvent=event;
    install.hidden=false;
  });
  install.addEventListener('click',async () => {
    if (!installEvent) return;
    installEvent.prompt();
    await installEvent.userChoice;
    install.hidden=true;
    installEvent=null;
  });
  window.addEventListener('appinstalled',() => {install.hidden=true;installEvent=null;});
  (async () => {
    if (!canNotify) {
      enable.hidden=true;
      message('Instalação disponível em navegadores compatíveis. Notificações não são compatíveis com este navegador.');
      return;
    }
    try {
      await serviceWorker();
      if (localStorage.getItem(PREF)==='on' && Notification.permission==='granted') {
        if (canPush) {
          const sub=await registration.pushManager.getSubscription();
          if (sub) {
            try { await updateSubscription('subscribe',sub); mode='push'; }
            catch (_) { mode='local'; }
          } else {
            mode='local';
          }
        } else mode='local';
        message(mode==='push'
          ? 'Inscrição de notificações ativa. Entrega em segundo plano sujeita ao agendador e ao sistema operacional.'
          : 'Alertas locais ativos enquanto esta página estiver aberta.');
      } else {
        mode='off';
        message('Ative os alertas para receber lembretes. No iPhone, instale primeiro pela opção Adicionar à Tela de Início.');
      }
      render();
      if (mode==='local') checkLocal();
    } catch (_) {
      message('Não foi possível inicializar o aplicativo neste navegador.');
    }
  })();
  setInterval(checkLocal, 60*1000);
})();
