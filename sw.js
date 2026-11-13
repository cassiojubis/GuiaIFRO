/**
 * O modo offline (PWA) saiu do Guia IFRO (está em _futuro/sw.js).
 * Este arquivo fica só para quem chegou a instalar a versão antiga:
 * ele apaga o cache guardado e se desinstala sozinho.
 */
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (evento) => {
    evento.waitUntil((async () => {
        const nomes = await caches.keys();
        await Promise.all(nomes.map((nome) => caches.delete(nome)));
        await self.registration.unregister();
        const janelas = await self.clients.matchAll({ type: 'window' });
        janelas.forEach((janela) => janela.navigate(janela.url));
    })());
});
