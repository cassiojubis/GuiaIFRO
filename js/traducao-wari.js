/*
 * TRADUÇÃO EM ORO WARI (PAKAA NOVA)
 * Só carrega com o Oro Wari escolhido (ver view/parciais/rodape.php).
 * As traduções vêm do model/idiomas/wari.php, escritas na página em JSON (#traducao-wari-dados).
 *
 * Diferente do espanhol, aqui só trocamos a frase INTEIRA quando ela é igual ao texto do
 * documento — nunca um pedaço. O que não tem tradução continua em português.
 * Atributos data-* (usados pelos filtros e pelo mapa) e valores de campos não são mexidos.
 */
(() => {
    const elementoDados = document.getElementById('traducao-wari-dados');
    if (!elementoDados) return;

    let dados;
    try {
        dados = JSON.parse(elementoDados.textContent || '{}');
    } catch (erro) {
        return; // sem dicionário a página fica em português
    }

    const normalizar = (texto) => String(texto).replace(/\s+/gu, ' ').trim();
    const paraMapa = (textos) => new Map(Object.entries(textos || {})
        .filter(([, traducao]) => typeof traducao === 'string' && traducao.trim() !== '')
        .map(([original, traducao]) => [normalizar(original), traducao]));

    const pagina = document.body.dataset.paginaTraducao || '';
    const comum = paraMapa(dados.comum);
    const daPagina = paraMapa((dados.paginas || {})[pagina]);
    const escopos = (dados.escopos || [])
        .filter((escopo) => !escopo.pagina || escopo.pagina === pagina)
        .map((escopo) => ({ seletor: escopo.seletor, textos: paraMapa(escopo.textos) }));
    const padroes = (dados.padroes || []).map((padrao) => {
        try {
            return { expressao: new RegExp(padrao.expr, 'u'), troca: padrao.troca };
        } catch (erro) {
            return null;
        }
    }).filter(Boolean);

    // Ordem: pedaço da tela (escopo) > página > comum > frases com número
    function procurar(texto, elemento) {
        for (const escopo of escopos) {
            if (escopo.textos.has(texto) && elemento && elemento.closest(escopo.seletor)) return escopo.textos.get(texto);
        }
        if (daPagina.has(texto)) return daPagina.get(texto);
        if (comum.has(texto)) return comum.get(texto);
        for (const padrao of padroes) {
            if (padrao.expressao.test(texto)) return texto.replace(padrao.expressao, padrao.troca);
        }
        return null;
    }

    // Tradução da frase ou null. Aceita diferença só no ponto final ("Nada encontrado." x "Nada encontrado").
    function traduzir(valor, elemento) {
        const texto = normalizar(valor);
        if (!texto || !/\p{L}/u.test(texto)) return null;
        const direto = procurar(texto, elemento);
        if (direto !== null) return direto;

        const comPontuacao = texto.match(/^(.+?)([.:!?…]+)$/u);
        if (comPontuacao) {
            const semPonto = procurar(comPontuacao[1], elemento);
            if (semPonto !== null) return /[.:!?…]$/u.test(semPonto) ? semPonto : semPonto + comPontuacao[2];
        } else {
            const comPonto = procurar(texto + '.', elemento);
            if (comPonto !== null) return comPonto.replace(/\.$/u, '');
        }
        return null;
    }

    const ignorar = 'script, style, textarea, code, pre, [contenteditable], [translate="no"], [data-sem-traducao], #traducao-wari-dados';
    const atributos = ['placeholder', 'aria-label', 'title', 'alt'];
    // Elementos que podem ter a frase dividida em pedaços (ex.: "Bem-vindo ao <span>Guia IFRO</span>")
    const blocosDeTexto = new Set(['H1', 'H2', 'H3', 'H4', 'P', 'A', 'BUTTON', 'LABEL', 'LI', 'SMALL', 'STRONG', 'SPAN', 'EM', 'SUMMARY', 'TD', 'TH', 'LEGEND', 'FIGCAPTION']);

    function trocarTexto(no) {
        const pai = no.parentElement;
        if (!pai || pai.closest(ignorar)) return;
        const traducao = traduzir(no.nodeValue, pai);
        if (traducao === null) return;
        const espacoInicial = no.nodeValue.match(/^\s*/u)[0];
        const espacoFinal = no.nodeValue.match(/\s*$/u)[0];
        const novo = espacoInicial + traducao + espacoFinal;
        if (novo !== no.nodeValue) no.nodeValue = novo;
    }

    function trocarAtributos(elemento) {
        atributos.forEach((atributo) => {
            if (!elemento.hasAttribute(atributo)) return;
            const traducao = traduzir(elemento.getAttribute(atributo), elemento);
            if (traducao !== null && traducao !== elemento.getAttribute(atributo)) elemento.setAttribute(atributo, traducao);
        });
    }

    // Frase dividida entre texto e <span>/<strong>: troca o conteúdo inteiro, se não houver ícone, imagem ou campo dentro
    function trocarBloco(elemento) {
        if (!blocosDeTexto.has(elemento.tagName) || !elemento.firstElementChild) return;
        if (elemento.querySelector('svg, img, input, select, textarea, button, a, video')) return;
        const traducao = traduzir(elemento.textContent, elemento);
        if (traducao !== null) elemento.textContent = traducao;
    }

    function traduzirNo(no) {
        if (no.nodeType === Node.TEXT_NODE) {
            trocarTexto(no);
            return;
        }
        if (no.nodeType !== Node.ELEMENT_NODE || no.matches(ignorar)) return;
        trocarAtributos(no);
        trocarBloco(no);
        Array.from(no.childNodes).forEach(traduzirNo);
    }

    traduzirNo(document.body);

    // Título da aba: "Início | Guia IFRO" -> traduz só a primeira parte
    const [tituloPagina, ...restoTitulo] = document.title.split(' | ');
    const tituloTraduzido = traduzir(tituloPagina, document.body);
    if (tituloTraduzido !== null) document.title = [tituloTraduzido, ...restoTitulo].join(' | ');

    // Texto que o JavaScript coloca depois (busca ao vivo, mapa, contadores, tema...)
    new MutationObserver((mutacoes) => {
        mutacoes.forEach((mutacao) => {
            if (mutacao.type === 'characterData') {
                trocarTexto(mutacao.target);
            } else if (mutacao.type === 'attributes') {
                if (mutacao.target.nodeType === Node.ELEMENT_NODE && !mutacao.target.closest(ignorar)) trocarAtributos(mutacao.target);
            } else {
                mutacao.addedNodes.forEach(traduzirNo);
            }
        });
    }).observe(document.body, { subtree: true, childList: true, characterData: true, attributes: true, attributeFilter: atributos });
})();
