/**
 * "OUVIR" (I5): lê a página ou o passo a passo em voz alta.
 *
 * Usa a voz do próprio navegador (speechSynthesis), sem serviço externo.
 * Se o conteúdo tiver um áudio gravado (data-audio-url no elemento — ex.: um
 * texto em Oro Wari gravado por falante, campo audio_url da tabela traducoes),
 * toca o áudio no lugar da voz sintética.
 *
 * Botões:
 *  - [data-ouvir="#seletor"] lê o elemento indicado (ou a página toda, se vazio);
 *  - a barra de controle (pausar, continuar, parar, velocidade) aparece enquanto lê.
 */
(() => {
    const temVoz = 'speechSynthesis' in window;
    const html = document.documentElement;
    let audioAtual = null;

    // Barra "Ouvir esta página", colocada no começo do conteúdo principal.
    const principal = document.querySelector('main');
    if (principal && (temVoz || principal.querySelector('[data-audio-url]'))) {
        const barra = document.createElement('div');
        barra.className = 'ouvir-barra container';
        barra.innerHTML = `
            <button type="button" class="ouvir-botao" data-ouvir=""><span aria-hidden="true">🔊</span> Ouvir esta página</button>
            <div class="ouvir-controles" hidden>
                <button type="button" data-ouvir-acao="pausar">Pausar</button>
                <button type="button" data-ouvir-acao="continuar" hidden>Continuar</button>
                <button type="button" data-ouvir-acao="parar">Parar</button>
                <label>Velocidade
                    <select data-ouvir-velocidade>
                        <option value="0.8">Devagar</option>
                        <option value="1" selected>Normal</option>
                        <option value="1.25">Rápido</option>
                        <option value="1.5">Mais rápido</option>
                    </select>
                </label>
            </div>`;
        // Vai dentro da primeira seção (depois do espaço reservado ao cabeçalho fixo),
        // senão fica escondida atrás do cabeçalho e ninguém consegue clicar.
        const primeiraSecao = principal.querySelector(':scope > section, :scope > header, :scope > div');
        const caixaInterna = primeiraSecao && !primeiraSecao.classList.contains('hero-home') ? primeiraSecao.querySelector(':scope > .container') : null;
        if (caixaInterna) {
            // Dentro do mesmo "container" do título: fica alinhado e sem espaço sobrando
            barra.classList.remove('container');
            barra.classList.add('ouvir-barra-interna');
            caixaInterna.prepend(barra);
        } else if (primeiraSecao) {
            primeiraSecao.prepend(barra);
            if (primeiraSecao.classList.contains('hero-home')) barra.classList.add('ouvir-barra-escura');
        } else {
            principal.prepend(barra);
        }
    }
    const controles = document.querySelector('.ouvir-controles');
    const seletorVelocidade = document.querySelector('[data-ouvir-velocidade]');
    try {
        const salva = localStorage.getItem('guiaifro-velocidade-voz');
        if (salva && seletorVelocidade) seletorVelocidade.value = salva;
    } catch (erro) { /* sem localStorage */ }

    function mostrarControles(lendo) {
        if (!controles) return;
        controles.hidden = !lendo;
        controles.querySelector('[data-ouvir-acao="pausar"]').hidden = false;
        controles.querySelector('[data-ouvir-acao="continuar"]').hidden = true;
    }

    /** Texto que vale a pena ler: tira menus, botões, código e o que pediu pra não ler. */
    function textoParaLer(elemento) {
        const copia = elemento.cloneNode(true);
        copia.querySelectorAll('script, style, nav, form, button, select, .ouvir-barra, [hidden], [aria-hidden="true"], [data-nao-ler], svg, .breadcrumb').forEach((n) => n.remove());
        return (copia.innerText || copia.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function parar() {
        if (temVoz) window.speechSynthesis.cancel();
        if (audioAtual) {
            audioAtual.pause();
            audioAtual = null;
        }
        mostrarControles(false);
    }

    function ler(elemento) {
        parar();
        // Áudio gravado tem prioridade sobre a voz do navegador.
        const comAudio = elemento.matches('[data-audio-url]') ? elemento : elemento.querySelector('[data-audio-url]');
        if (comAudio) {
            audioAtual = new Audio(comAudio.dataset.audioUrl);
            audioAtual.playbackRate = Number(seletorVelocidade?.value || 1);
            audioAtual.addEventListener('ended', () => mostrarControles(false));
            audioAtual.play().catch(() => mostrarControles(false));
            mostrarControles(true);
            return;
        }
        if (!temVoz) return;
        const texto = textoParaLer(elemento);
        if (!texto) return;
        // Frase por frase: textos longos de uma vez travam a voz em alguns celulares.
        const frases = texto.match(/[^.!?]+[.!?]*/g) || [texto];
        const idioma = html.lang && html.lang.startsWith('es') ? 'es-ES' : 'pt-BR';
        frases.forEach((frase, indice) => {
            const fala = new SpeechSynthesisUtterance(frase.trim());
            fala.lang = idioma;
            fala.rate = Number(seletorVelocidade?.value || 1);
            if (indice === frases.length - 1) fala.onend = () => mostrarControles(false);
            window.speechSynthesis.speak(fala);
        });
        mostrarControles(true);
    }

    document.addEventListener('click', (evento) => {
        const botao = evento.target.closest('[data-ouvir]');
        if (botao) {
            const alvo = botao.dataset.ouvir ? document.querySelector(botao.dataset.ouvir) : principal;
            if (alvo) ler(alvo);
            return;
        }
        const acao = evento.target.closest('[data-ouvir-acao]')?.dataset.ouvirAcao;
        if (!acao) return;
        if (acao === 'parar') parar();
        if (acao === 'pausar') {
            if (audioAtual) audioAtual.pause(); else window.speechSynthesis.pause();
            controles.querySelector('[data-ouvir-acao="pausar"]').hidden = true;
            controles.querySelector('[data-ouvir-acao="continuar"]').hidden = false;
        }
        if (acao === 'continuar') {
            if (audioAtual) audioAtual.play(); else window.speechSynthesis.resume();
            controles.querySelector('[data-ouvir-acao="pausar"]').hidden = false;
            controles.querySelector('[data-ouvir-acao="continuar"]').hidden = true;
        }
    });

    seletorVelocidade?.addEventListener('change', () => {
        try { localStorage.setItem('guiaifro-velocidade-voz', seletorVelocidade.value); } catch (erro) { /* sem localStorage */ }
    });

    // Saiu da página: para de falar.
    window.addEventListener('pagehide', parar);
})();
