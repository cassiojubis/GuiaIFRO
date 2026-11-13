/**
 * SERVICE WORKER DO GUIA IFRO (PWA: instalar no celular e usar sem internet)
 *
 * Estratégias:
 *  - CSS, JS, imagens e fontes: cache primeiro (mudam pouco; o ?v= do PHP é ignorado na hora de achar).
 *  - Busca (busca.php): mostra o que tem guardado e atualiza por trás (stale-while-revalidate).
 *  - Páginas: tenta a internet primeiro (até 4 s) pra o "Aberto agora" ficar certo;
 *    sem internet, usa a cópia guardada; se não tiver, mostra offline.php.
 *  - Admin, formulários (POST) e contadores: nunca passam pelo cache.
 *
 * Mudou algo aqui ou na lista de arquivos? Aumente a VERSAO pra todo mundo baixar de novo.
 */
const VERSAO = 'guia-ifro-v2';
const CACHE_ARQUIVOS = `${VERSAO}-arquivos`;
const CACHE_PAGINAS = `${VERSAO}-paginas`;
const CACHE_DADOS = `${VERSAO}-dados`;

self.addEventListener('install', (evento) => {
    evento.waitUntil((async () => {
        const resposta = await fetch('offline-lista.php', { cache: 'no-store' });
        const lista = await resposta.json();
        const arquivos = await caches.open(CACHE_ARQUIVOS);
        const paginas = await caches.open(CACHE_PAGINAS);
        // O cabeçalho avisa o servidor que é o app guardando a página (não conta nas estatísticas).
        const guardar = async (cache, url) => {
            const resposta = await fetch(new Request(url, { headers: { 'X-Guia-Precache': '1' } }));
            if (resposta.ok) await cache.put(url, resposta);
        };
        // Um arquivo que falhar não pode impedir o resto de ser guardado.
        await Promise.all([
            ...lista.arquivos.map((url) => guardar(arquivos, url).catch(() => {})),
            ...lista.paginas.map((url) => guardar(paginas, url).catch(() => {})),
        ]);
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (evento) => {
    evento.waitUntil((async () => {
        const nomes = await caches.keys();
        await Promise.all(nomes.filter((nome) => !nome.startsWith(VERSAO)).map((nome) => caches.delete(nome)));
        await self.clients.claim();
    })());
});

function naoGuardar(url) {
    return url.pathname.includes('/admin/')
        || url.pathname.includes('/php/')
        || url.pathname.endsWith('offline-lista.php')
        || url.pathname.endsWith('qr.php')
        || url.searchParams.has('registrar');
}

function ehArquivoEstatico(url) {
    return /\.(css|js|png|jpe?g|webp|svg|gif|ico|woff2?|ttf|mp4)$/i.test(url.pathname);
}

async function cachePrimeiro(pedido) {
    const guardado = await caches.match(pedido, { ignoreSearch: true });
    if (guardado) return guardado;
    const resposta = await fetch(pedido);
    if (resposta.ok) (await caches.open(CACHE_ARQUIVOS)).put(pedido, resposta.clone());
    return resposta;
}

async function guardadoEAtualiza(pedido) {
    const cache = await caches.open(CACHE_DADOS);
    const guardado = await cache.match(pedido);
    const daRede = fetch(pedido).then((resposta) => {
        if (resposta.ok) cache.put(pedido, resposta.clone());
        return resposta;
    }).catch(() => guardado);
    return guardado || daRede;
}

async function redePrimeiro(pedido) {
    const cache = await caches.open(CACHE_PAGINAS);
    try {
        const resposta = await Promise.race([
            fetch(pedido),
            new Promise((_, rejeitar) => setTimeout(() => rejeitar(new Error('demorou')), 4000)),
        ]);
        if (resposta.ok) cache.put(pedido, resposta.clone());
        return resposta;
    } catch (erro) {
        return (await cache.match(pedido))
            || (await cache.match(pedido, { ignoreSearch: true }))
            || (await cache.match('offline.php'))
            || new Response('Sem internet.', { status: 503, headers: { 'Content-Type': 'text/plain; charset=utf-8' } });
    }
}

self.addEventListener('fetch', (evento) => {
    const pedido = evento.request;
    if (pedido.method !== 'GET') return;
    const url = new URL(pedido.url);
    if (url.origin !== self.location.origin || naoGuardar(url)) return;

    if (ehArquivoEstatico(url)) {
        evento.respondWith(cachePrimeiro(pedido));
    } else if (url.pathname.endsWith('busca.php')) {
        evento.respondWith(guardadoEAtualiza(pedido));
    } else if (pedido.mode === 'navigate' || (pedido.headers.get('accept') || '').includes('text/html')) {
        evento.respondWith(redePrimeiro(pedido));
    }
});
