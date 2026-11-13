/**
 * EDITOR DO MAPA (admin/mapa.php)
 * 1) Salas: arrastar entre as listas (mouse) ou setas ↑ ↓ (toque e teclado).
 *    "Salvar ordem" manda a arrumação nova pro servidor.
 * 2) Cenário: peças num desenho SVG. Toque/clique escolhe, arrastar move,
 *    o quadradinho do canto muda o tamanho. "Salvar cenário" manda tudo.
 * Usa enviarAdmin() e mostrarToastAdmin() do admin.js, e PECAS_MAPA do ../js/util.js.
 *
 * Coordenadas iguais às do mapa do site (js/mapa.js):
 *   x = da entrada para o fundo do prédio (0 = porta), z = para os lados (negativo = esquerda).
 *   Na tela: z vira o eixo horizontal e x o vertical (a entrada fica embaixo).
 */
(() => {
    'use strict';

    /* =========================== SALAS =========================== */
    const listas = Array.from(document.querySelectorAll('[data-grupo-salas]'));
    const botaoOrdem = document.getElementById('editor-salvar-ordem');
    const statusOrdem = document.getElementById('editor-ordem-status');
    let arrastando = null;

    const marcarMudou = () => {
        if (!botaoOrdem) return;
        botaoOrdem.disabled = false;
        statusOrdem.textContent = 'Ordem alterada (ainda não salva).';
    };

    listas.forEach((lista) => {
        lista.addEventListener('dragstart', (evento) => {
            arrastando = evento.target.closest('.editor-sala');
            if (!arrastando) return;
            arrastando.classList.add('arrastando');
            evento.dataTransfer.effectAllowed = 'move';
            evento.dataTransfer.setData('text/plain', arrastando.dataset.id);
        });
        lista.addEventListener('dragend', () => {
            arrastando?.classList.remove('arrastando');
            arrastando = null;
        });
        lista.addEventListener('dragover', (evento) => {
            if (!arrastando) return;
            evento.preventDefault();
            // coloca antes do item que está embaixo do ponteiro (ou no fim da lista)
            const depois = Array.from(lista.querySelectorAll('.editor-sala:not(.arrastando)'))
                .find((item) => evento.clientY < item.getBoundingClientRect().top + item.offsetHeight / 2);
            if (depois) lista.insertBefore(arrastando, depois);
            else lista.appendChild(arrastando);
        });
        lista.addEventListener('drop', (evento) => {
            evento.preventDefault();
            marcarMudou();
        });
    });

    document.addEventListener('click', (evento) => {
        const botao = evento.target.closest('[data-mover-sala]');
        if (!botao) return;
        const item = botao.closest('.editor-sala');
        if (botao.dataset.moverSala === 'cima' && item.previousElementSibling) {
            item.parentElement.insertBefore(item, item.previousElementSibling);
        } else if (botao.dataset.moverSala === 'baixo' && item.nextElementSibling) {
            item.parentElement.insertBefore(item.nextElementSibling, item);
        } else {
            return;
        }
        botao.focus();
        marcarMudou();
    });

    botaoOrdem?.addEventListener('click', async () => {
        const posicoes = [];
        listas.forEach((lista) => {
            const [andar, ala] = lista.dataset.grupoSalas.split('|');
            lista.querySelectorAll('.editor-sala').forEach((item, indice) => {
                posicoes.push({ id: item.dataset.id, andar_id: andar, ala, ordem: indice + 1 });
            });
        });
        botaoOrdem.disabled = true;
        try {
            const resposta = await enviarAdmin('mapa.php', { acao: 'salvar_ordem', dados: JSON.stringify(posicoes) });
            statusOrdem.textContent = 'Ordem salva.';
            mostrarToastAdmin(resposta.mensagem || 'Ordem salva.');
        } catch (erro) {
            botaoOrdem.disabled = false;
            statusOrdem.textContent = '';
            mostrarToastAdmin(erro.message, 'erro');
        }
    });

    /* =========================== CENÁRIO =========================== */
    const svg = document.getElementById('editor-cenario');
    if (!svg) return;

    const NS = 'http://www.w3.org/2000/svg';
    const el = (nome, atributos = {}, pai) => {
        const n = document.createElementNS(NS, nome);
        Object.entries(atributos).forEach(([k, v]) => n.setAttribute(k, v));
        if (pai) pai.appendChild(n);
        return n;
    };
    const ler = (id) => {
        try { return JSON.parse(document.getElementById(id)?.textContent || 'null'); } catch (erro) { return null; }
    };

    // Medidas do prédio (mesmas do js/mapa.js) só pra desenhar a referência.
    const X1 = 12, CW = 3.2, RD = 5.4, ALA_TERREO = 16, PONTA = 8, PORTAO_X = -18.5;
    let pecas = (ler('editor-pecas-dados') || []).map((peca, i) => Object.assign({ chave: i + 1 }, peca));
    let proximaChave = pecas.length + 1;
    let escolhida = null;
    let mudou = false;

    svg.setAttribute('viewBox', '-60 -80 120 118');
    const camadaPecas = el('g');

    function desenharReferencia() {
        el('rect', { x: -60, y: -80, width: 120, height: 118, fill: 'var(--background)' }, svg);
        // grade de 5 em 5 metros
        const grade = el('g', { stroke: 'var(--border)', 'stroke-width': 0.08 }, svg);
        for (let v = -60; v <= 60; v += 5) el('line', { x1: v, y1: -80, x2: v, y2: 38 }, grade);
        for (let v = -80; v <= 38; v += 5) el('line', { x1: -60, y1: v, x2: 60, y2: v }, grade);
        // prédio (térreo) + corredor de entrada + caminho e portão
        const zFim = ALA_TERREO + PONTA;
        el('rect', { x: -zFim, y: -(X1 + CW + RD), width: zFim * 2, height: 2 * RD + CW, fill: '#cbd5e1', stroke: '#64748b', 'stroke-width': 0.3 }, svg);
        el('rect', { x: -CW / 2, y: -X1, width: CW, height: X1, fill: '#cbd5e1' }, svg); // corredor da porta até o prédio
        el('rect', { x: -1.1, y: 0, width: 2.2, height: -PORTAO_X, fill: '#e2e8f0' }, svg);
        el('rect', { x: -2.6, y: -PORTAO_X - 0.3, width: 5.2, height: 0.6, fill: '#dc2626' }, svg);
        el('text', { x: 0, y: -(X1 + 1), 'text-anchor': 'middle', 'font-size': 2, fill: '#334155' }, svg).textContent = 'Prédio';
        el('text', { x: 0, y: -PORTAO_X + 2.6, 'text-anchor': 'middle', 'font-size': 1.6, fill: '#334155' }, svg).textContent = 'Portão';
        svg.appendChild(camadaPecas);
    }

    function desenharPecas() {
        camadaPecas.innerHTML = '';
        pecas.forEach((peca) => {
            const info = PECAS_MAPA[peca.tipo] || PECAS_MAPA.bloco;
            const g = el('g', {
                class: 'editor-peca' + (escolhida === peca ? ' escolhida' : ''),
                transform: `translate(${peca.z} ${-peca.x}) rotate(${peca.rotacao || 0})`,
                tabindex: 0, role: 'button', 'aria-label': `${info.rotulo}${peca.nome ? ': ' + peca.nome : ''}`,
            }, camadaPecas);
            g.dataset.chave = peca.chave;
            const w = peca.largura, d = peca.profundidade;
            if (info.redonda) {
                el('ellipse', { cx: 0, cy: 0, rx: w / 2, ry: d / 2, fill: info.cor, 'fill-opacity': 0.85, stroke: '#0f172a', 'stroke-width': 0.15 }, g);
            } else {
                el('rect', { x: -w / 2, y: -d / 2, width: w, height: d, rx: 0.3, fill: info.cor, 'fill-opacity': 0.75, stroke: '#0f172a', 'stroke-width': 0.15 }, g);
            }
            if (peca.nome) {
                el('text', { x: 0, y: 0.5, 'text-anchor': 'middle', 'font-size': Math.max(0.8, Math.min(1.8, w / 6)), fill: '#0f172a', 'pointer-events': 'none' }, g).textContent = peca.nome;
            }
            if (escolhida === peca) {
                el('rect', { class: 'editor-alca-tamanho', x: w / 2 - 0.8, y: d / 2 - 0.8, width: 1.6, height: 1.6, fill: '#fff', stroke: '#0f172a', 'stroke-width': 0.2 }, g);
            }
        });
    }

    /* ---- formulário da peça escolhida ---- */
    const form = document.getElementById('editor-peca-form');
    const campos = Array.from(form.querySelectorAll('[data-campo-peca]'));

    function preencherFormulario() {
        form.hidden = !escolhida;
        if (!escolhida) return;
        campos.forEach((campo) => { campo.value = escolhida[campo.dataset.campoPeca] ?? ''; });
    }

    campos.forEach((campo) => {
        campo.addEventListener('input', () => {
            if (!escolhida) return;
            const chave = campo.dataset.campoPeca;
            if (['x', 'z', 'largura', 'profundidade', 'rotacao'].includes(chave)) {
                const numero = parseFloat(campo.value);
                if (Number.isNaN(numero)) return;
                escolhida[chave] = (chave === 'largura' || chave === 'profundidade') ? Math.max(0.5, numero) : numero;
            } else {
                escolhida[chave] = campo.value;
            }
            mudou = true;
            desenharPecas();
        });
    });

    function escolher(peca) {
        escolhida = peca;
        desenharPecas();
        preencherFormulario();
    }

    document.querySelectorAll('[data-nova-peca]').forEach((botao) => {
        botao.addEventListener('click', () => {
            const tipo = botao.dataset.novaPeca;
            const redonda = PECAS_MAPA[tipo]?.redonda;
            // nasce na frente do prédio, à direita do caminho, pra ficar fácil de ver
            const peca = { chave: proximaChave++, tipo, nome: '', x: -8, z: 10, largura: redonda ? 3 : 8, profundidade: redonda ? 3 : 6, rotacao: 0, inicio: '', fim: '' };
            pecas.push(peca);
            mudou = true;
            escolher(peca);
            document.getElementById('peca-nome').focus();
        });
    });

    document.getElementById('editor-excluir-peca').addEventListener('click', () => {
        if (!escolhida) return;
        pecas = pecas.filter((peca) => peca !== escolhida);
        mudou = true;
        escolher(null);
    });

    /* ---- arrastar e redimensionar (mouse e toque, pelos pointer events) ---- */
    const pontoNoSvg = (evento) => {
        const ponto = svg.createSVGPoint();
        ponto.x = evento.clientX;
        ponto.y = evento.clientY;
        return ponto.matrixTransform(svg.getScreenCTM().inverse());
    };
    const meioMetro = (valor) => Math.round(valor * 2) / 2;
    let gesto = null;

    svg.addEventListener('pointerdown', (evento) => {
        const grupo = evento.target.closest('.editor-peca');
        if (!grupo) {
            escolher(null);
            return;
        }
        const peca = pecas.find((item) => String(item.chave) === grupo.dataset.chave);
        const redimensionar = evento.target.classList.contains('editor-alca-tamanho');
        if (peca !== escolhida) escolher(peca);
        const inicio = pontoNoSvg(evento);
        gesto = { peca, redimensionar, inicio, x: peca.x, z: peca.z };
        svg.setPointerCapture(evento.pointerId);
        evento.preventDefault();
    });

    svg.addEventListener('pointermove', (evento) => {
        if (!gesto) return;
        const atual = pontoNoSvg(evento);
        const peca = gesto.peca;
        if (gesto.redimensionar) {
            // distância do centro até o ponteiro, "desgirada" pro sistema da peça
            const angulo = -(peca.rotacao || 0) * Math.PI / 180;
            const dx = atual.x - peca.z, dy = atual.y + peca.x;
            const localX = dx * Math.cos(angulo) - dy * Math.sin(angulo);
            const localY = dx * Math.sin(angulo) + dy * Math.cos(angulo);
            peca.largura = Math.max(0.5, meioMetro(Math.abs(localX) * 2));
            peca.profundidade = Math.max(0.5, meioMetro(Math.abs(localY) * 2));
        } else {
            peca.z = meioMetro(gesto.z + (atual.x - gesto.inicio.x));
            peca.x = meioMetro(gesto.x - (atual.y - gesto.inicio.y));
        }
        mudou = true;
        desenharPecas();
        preencherFormulario();
    });

    const terminarGesto = () => { gesto = null; };
    svg.addEventListener('pointerup', terminarGesto);
    svg.addEventListener('pointercancel', terminarGesto);

    /* ---- teclado: Enter escolhe a peça; setas movem (Shift + setas mudam o tamanho) ---- */
    svg.addEventListener('keydown', (evento) => {
        const grupo = evento.target.closest('.editor-peca');
        if (!grupo) return;
        const peca = pecas.find((item) => String(item.chave) === grupo.dataset.chave);
        if (evento.key === 'Enter' || evento.key === ' ') {
            evento.preventDefault();
            escolher(peca);
            svg.querySelector(`.editor-peca[data-chave="${peca.chave}"]`)?.focus();
            return;
        }
        const passos = { ArrowUp: [0.5, 0], ArrowDown: [-0.5, 0], ArrowLeft: [0, -0.5], ArrowRight: [0, 0.5] };
        if (!passos[evento.key] || peca !== escolhida) return;
        evento.preventDefault();
        const [dx, dz] = passos[evento.key];
        if (evento.shiftKey) {
            peca.profundidade = Math.max(0.5, peca.profundidade + dx);
            peca.largura = Math.max(0.5, peca.largura + dz);
        } else {
            peca.x += dx;
            peca.z += dz;
        }
        mudou = true;
        desenharPecas();
        preencherFormulario();
        svg.querySelector(`.editor-peca[data-chave="${peca.chave}"]`)?.focus();
    });

    /* ---- salvar ---- */
    const statusCenario = document.getElementById('editor-cenario-status');
    document.getElementById('editor-salvar-cenario').addEventListener('click', async (evento) => {
        const botao = evento.currentTarget;
        botao.disabled = true;
        const dados = pecas.map(({ tipo, nome, x, z, largura, profundidade, rotacao, inicio, fim }) => ({ tipo, nome, x, z, largura, profundidade, rotacao: rotacao || 0, inicio, fim }));
        try {
            const resposta = await enviarAdmin('mapa.php', { acao: 'salvar_pecas', dados: JSON.stringify(dados) });
            mudou = false;
            statusCenario.textContent = `${pecas.length} peça(s) salvas.`;
            mostrarToastAdmin(resposta.mensagem || 'Cenário salvo.');
        } catch (erro) {
            mostrarToastAdmin(erro.message, 'erro');
        } finally {
            botao.disabled = false;
        }
    });

    // Avisa antes de sair da página com mudança não salva.
    window.addEventListener('beforeunload', (evento) => {
        if (mudou || (botaoOrdem && !botaoOrdem.disabled)) {
            evento.preventDefault();
            evento.returnValue = '';
        }
    });

    desenharReferencia();
    desenharPecas();
})();
