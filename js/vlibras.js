// VLibras (tradutor de Libras do governo federal, vlibras.gov.br).
// Antes o script deles era carregado travando toda página. Agora:
// - só começa a baixar depois que a página terminou de carregar (evento load);
// - espera o navegador ficar livre (requestIdleCallback) quando dá;
// - se o site do governo estiver fora do ar, o botão some e nada quebra.
(function () {
    const URL_VLIBRAS = 'https://vlibras.gov.br/app/vlibras-plugin.js';

    function esconderBotao() {
        const caixa = document.querySelector('[vw]');
        if (caixa) caixa.hidden = true;
    }

    function iniciarVLibras() {
        const script = document.createElement('script');
        script.src = URL_VLIBRAS;
        script.async = true;
        script.onload = () => {
            try {
                new window.VLibras.Widget({ rootPath: 'https://vlibras.gov.br/app', position: 'L' });
                // O widget do VLibras se monta no evento load da janela, que já passou.
                // Disparar de novo faz ele aparecer sem recarregar a página.
                window.dispatchEvent(new Event('load'));
            } catch (erro) {
                esconderBotao();
            }
        };
        script.onerror = esconderBotao;
        document.body.appendChild(script);
    }

    function quandoLivre() {
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(iniciarVLibras, { timeout: 3000 });
        } else {
            window.setTimeout(iniciarVLibras, 1000);
        }
    }

    if (document.readyState === 'complete') {
        quandoLivre();
    } else {
        window.addEventListener('load', quandoLivre, { once: true });
    }
})();
