/* Painel Mestre GEB: notificações por Web Push.
 * Não armazena em cache páginas autenticadas nem dados pessoais.
 */
'use strict';
const FALLBACK_URL = '/sistemas/rotinas.php';
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
self.addEventListener('push', event => {
  let data = {};
  try { if (event.data) data = event.data.json(); } catch (_) {}
  const title = typeof data.title === 'string' ? data.title.slice(0, 120) : 'Painel Mestre GEB';
  const body = typeof data.body === 'string' ? data.body.slice(0, 180) : 'Confira suas atividades no Painel Mestre.';
  const safeUrl = typeof data.url === 'string' && data.url.startsWith('/sistemas/rotinas.php')
    ? data.url : FALLBACK_URL;
  event.waitUntil(self.registration.showNotification(title, {
    body,
    icon: '/sistemas/rotinas-icon.svg',
    badge: '/sistemas/rotinas-icon.svg',
    tag: typeof data.tag === 'string' ? data.tag.slice(0, 140) : 'rotinas-geral',
    renotify: true,
    data: { url: safeUrl },
  }));
});
self.addEventListener('notificationclick', event => {
  event.notification.close();
  const url = new URL(event.notification?.data?.url || FALLBACK_URL, self.location.origin);
  event.waitUntil((async () => {
    const all = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    for (const client of all) {
      if (new URL(client.url).origin === url.origin) {
        try { await client.navigate(url.href); return client.focus(); } catch (_) {}
      }
    }
    return self.clients.openWindow(url.href);
  })());
});
