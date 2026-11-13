// MAPA 2D - planta do prédio desenhada em SVG (é o mapa principal da página).
// Este arquivo só sabe DESENHAR. Quem decide qual sala/andar está ativo é o js/mapa.js,
// que chama Mapa2D.iniciar(svg, api) e depois usa os métodos do objeto devolvido:
//   definirAndar(andar)  -> redesenha a planta do andar
//   mostrarSala(sala)    -> destaca a sala e desenha a rota animada
//   ativar() / pausar()  -> quando o 2D volta a aparecer / fica escondido atrás do 3D
//   aproximar(), afastar(), visaoGeral() -> botões de zoom

window.Mapa2D = {
    iniciar(svg, api) {
        'use strict';

        const { CW, X1, XC, STAIR_RUN, S0, S1, ZT, PORTAO_X, fimDaAla } = api.planta;
        const NS = 'http://www.w3.org/2000/svg';

        // cria um elemento SVG com atributos, já colocando dentro do "pai"
        const el = (nome, atributos = {}, pai) => {
            const n = document.createElementNS(NS, nome);
            Object.entries(atributos).forEach(([k, v]) => n.setAttribute(k, v));
            if (pai) pai.appendChild(n);
            return n;
        };
        // Planta -> tela: a entrada fica embaixo e o fundo do prédio em cima.
        // (x da planta vira -y da tela; z da planta vira x da tela)
        const P = (x, z) => [z, -x];
        const retangulo = (pai, x0, x1, z0, z1, atributos) => {
            const [ax, ay] = P(x1, Math.min(z0, z1));
            return el('rect', Object.assign({ x: ax, y: ay, width: Math.abs(z1 - z0), height: Math.abs(x1 - x0) }, atributos), pai);
        };

        const HOME = { x: -27, y: -23.5, w: 54, h: 47 }; // enquadramento da visão geral
        let vb = Object.assign({}, HOME);
        const aplicarVB = () => svg.setAttribute('viewBox', `${vb.x} ${vb.y} ${vb.w} ${vb.h}`);

        let andar = 'terreo';
        let camadaRota = null;
        let animacao = 0;
        let pausado = false;

        function quebrarTexto(texto, max) {
            const palavras = texto.split(' '), linhas = [];
            let atual = '';
            palavras.forEach((p) => {
                if (!atual) atual = p;
                else if ((atual + ' ' + p).length <= max) atual += ' ' + p;
                else { linhas.push(atual); atual = p; }
            });
            if (atual) linhas.push(atual);
            return linhas;
        }

        /* ---------------- planta do andar (só refaz quando o andar muda) ---------------- */
        function desenharPlanta() {
            svg.innerHTML = '';
            const defs = el('defs', {}, svg);
            const sombra = el('filter', { id: 'mapa-sombra', x: '-10%', y: '-10%', width: '120%', height: '125%' }, defs);
            el('feDropShadow', { dx: 0, dy: 0.35, stdDeviation: 0.45, 'flood-color': '#10202f', 'flood-opacity': 0.22 }, sombra);
            const listras = el('pattern', { id: 'mapa-listras', width: 1, height: 0.5, patternUnits: 'userSpaceOnUse' }, defs);
            el('rect', { width: 1, height: 0.5, fill: 'var(--mapa-corredor)' }, listras);
            el('line', { x1: 0, y1: 0, x2: 1, y2: 0, stroke: 'var(--mapa-traco)', 'stroke-width': 0.08, opacity: 0.5 }, listras);

            el('rect', { x: -200, y: -200, width: 400, height: 400, fill: 'var(--mapa-chao)' }, svg);
            const base = el('g', {}, svg);
            const camadaSalas = el('g', {}, svg);
            camadaRota = el('g', {}, svg);

            if (andar === 'terreo') {
                desenharEntorno(base);
            } else {
                desenharContorno(base, 'terreo', true); // térreo "fantasma" por baixo, para orientar
            }
            desenharContorno(base, andar, false);

            api.salas.filter((s) => s.andar === andar).forEach((s) => desenharSala(camadaSalas, s));
            desenharEscadaEEntrada(base);
            mostrarSala(api.estado.sala, { semCamera: true });
        }

        // árvores, caminho, portão e mastros (só aparecem no térreo)
        function desenharEntorno(g) {
            const arvores = [[-16, -6], [-14, 7], [-9, -7.5], [-9, 8], [-22, -14], [-22, 14], [18, -22.5], [-20, 22], [22, 17],
                [25, -4], [24, 6], [-24, -3], [-20, 6], [-13, -20], [-11, 20], [16, 24], [-19, -22]];
            arvores.forEach(([tx, ty]) => {
                el('circle', { cx: tx + 0.35, cy: ty + 0.4, r: 1.5, fill: '#10202f', opacity: 0.18 }, g);
                el('circle', { cx: tx, cy: ty, r: 1.5, fill: 'var(--mapa-arvore)' }, g);
                el('circle', { cx: tx - 0.35, cy: ty - 0.4, r: 0.8, fill: '#fff', opacity: 0.18 }, g);
            });
            const portaoY = -PORTAO_X;
            el('rect', { x: -1.1, y: 0, width: 2.2, height: portaoY, rx: 0.3, fill: 'var(--mapa-caminho)' }, g);
            el('rect', { x: -2.6, y: portaoY - 0.2, width: 5.2, height: 0.6, rx: 0.2, fill: 'var(--mapa-vermelho)' }, g);
            [-2.9, 2.3].forEach((px) => el('rect', { x: px, y: portaoY - 0.5, width: 0.6, height: 1.2, rx: 0.2, fill: 'var(--mapa-parede)', stroke: 'var(--mapa-traco)', 'stroke-width': 0.08 }, g));
            el('text', { x: 0, y: portaoY + 1.9, class: 'mapa-etiqueta', fill: 'var(--text)' }, g).textContent = 'Portão';
            desenharPecas(g);
            // bandeiras (verde, branca e vermelha) ao lado do caminho
            [[4.2, '#2f9e41'], [5.4, '#ffffff'], [6.6, '#cd191e']].forEach(([mx, cor]) => {
                el('rect', { x: mx, y: 7.2, width: 1, height: 0.65, rx: 0.1, fill: cor, stroke: 'var(--mapa-traco)', 'stroke-width': 0.06 }, g);
            });
        }

        // Peças do cenário cadastradas no admin (mesmas cores do editor e do 3D: PECAS_MAPA em js/util.js)
        function desenharPecas(g) {
            (api.pecas || []).forEach((peca) => {
                const info = (typeof PECAS_MAPA !== 'undefined' && PECAS_MAPA[peca.tipo]) || { cor: '#94a3b8' };
                const [px, py] = P(peca.x, peca.z);
                const grupo = el('g', { transform: `translate(${px} ${py}) rotate(${peca.rotacao || 0})`, 'aria-hidden': 'true' }, g);
                const w = peca.largura, d = peca.profundidade;
                if (info.redonda) {
                    el('ellipse', { rx: w / 2, ry: d / 2, fill: info.cor }, grupo);
                } else {
                    el('rect', { x: -w / 2, y: -d / 2, width: w, height: d, rx: 0.3, fill: info.cor, 'fill-opacity': 0.8, stroke: 'var(--mapa-traco)', 'stroke-width': 0.1 }, grupo);
                }
                if (peca.nome) {
                    el('text', { x: 0, y: 0.3, class: 'mapa-etiqueta', fill: 'var(--text)', style: `font-size:${Math.max(0.6, Math.min(1.2, w / 7))}px` }, grupo).textContent = peca.nome;
                }
            });
        }

        // laje e corredores de um andar
        function desenharContorno(g, qualAndar, fantasma) {
            const Z = fimDaAla(qualAndar), XB = X1 + CW + api.planta.RD;
            const grupo = el('g', { opacity: fantasma ? 0.22 : 1 }, g);
            retangulo(grupo, X1 - api.planta.RD, XB, -Z, Z, { fill: 'var(--mapa-laje)', stroke: 'var(--mapa-parede)', 'stroke-width': 0.3, rx: 0.4, filter: fantasma ? '' : 'url(#mapa-sombra)' });
            retangulo(grupo, 0, X1, -CW / 2, CW / 2, { fill: 'var(--mapa-laje)', stroke: 'var(--mapa-parede)', 'stroke-width': 0.3 });
            const ala = api.planta.ALA[qualAndar] || ZT;
            retangulo(grupo, 0, X1 + CW, -CW / 2, CW / 2, { fill: 'url(#mapa-listras)' });
            retangulo(grupo, X1, X1 + CW, -ala, ala, { fill: 'url(#mapa-listras)' });
            if (!fantasma) {
                // setinhas no corredor de entrada, apontando para dentro do prédio
                for (let x = 2; x < S0 - 1; x += 2.4) {
                    const [a, b] = P(x, 0);
                    el('path', { d: `M${a - 0.3} ${b + 0.25} L${a} ${b - 0.25} L${a + 0.3} ${b + 0.25}`, fill: 'none', stroke: 'var(--primary)', 'stroke-width': 0.12, opacity: 0.5, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, grupo);
                }
            }
        }

        // Cada sala é um <g> focável: dá para navegar com Tab e escolher com Enter/Espaço
        function desenharSala(pai, s) {
            const g = el('g', {
                class: 'mapa-sala ' + s.ala, 'data-id': s.id, tabindex: 0, role: 'button', 'aria-pressed': 'false',
                'aria-label': `${s.nome}, ${api.nomesAndar[s.andar] || ''}, ala ${s.ala}`,
            }, pai);
            retangulo(g, s.x0, s.x1, s.z0, s.z1, { class: 'mapa-sala-fundo', rx: 0.25, stroke: 'var(--mapa-parede)', 'stroke-width': 0.28, filter: 'url(#mapa-sombra)' });
            // porta (traço verde na parede do corredor)
            if (s.end) {
                const zz = s.sgn * (api.planta.ALA[s.andar] || ZT);
                retangulo(g, XC - 1.1, XC + 1.1, zz - 0.12, zz + 0.12, { fill: 'var(--primary)' });
            } else {
                const [sx, sy] = P(s.doorX, s.cz);
                el('rect', { x: sx - 0.8, y: sy - 0.13, width: 1.6, height: 0.26, rx: 0.1, fill: 'var(--primary)' }, g);
            }
            // nome, quebrado em linhas para caber na sala
            const largura = Math.abs(s.z1 - s.z0);
            const fs = Math.min(0.78, largura * 0.15), lh = fs * 1.2;
            const linhas = quebrarTexto(s.nomeCurto, Math.max(5, Math.floor(largura / (fs * 0.72))));
            const [tx, ty] = P(s.cx, s.cz);
            const texto = el('text', { x: tx, y: ty - (linhas.length - 1) * lh / 2 + fs * 0.3, style: `font-size:${fs}px`, 'aria-hidden': 'true' }, g);
            linhas.forEach((linha, i) => { el('tspan', { x: tx, dy: i ? lh : 0 }, texto).textContent = linha; });

            g.addEventListener('click', () => { if (!arrastou) api.selecionar(s); });
            g.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); api.selecionar(s); }
            });
        }

        function desenharEscadaEEntrada(g) {
            const escada = el('g', {}, g);
            retangulo(escada, S0, S1, -0.8, 0.8, { fill: 'var(--mapa-parede)', stroke: 'var(--mapa-traco)', 'stroke-width': 0.1, rx: 0.15 });
            for (let i = 1; i < 10; i++) {
                const xx = S0 + (STAIR_RUN / 10) * i;
                const [a, b] = P(xx, -0.8), [c] = P(xx, 0.8);
                el('line', { x1: a, y1: b, x2: c, y2: b, stroke: 'var(--mapa-traco)', 'stroke-width': 0.08 }, escada);
            }
            const [ax, ay] = P((S0 + S1) / 2, 0);
            const seta = el('text', { x: ax, y: ay + 0.25, class: 'mapa-etiqueta', fill: 'var(--primary)', style: 'font-size:1.1px;letter-spacing:0' }, escada);
            seta.textContent = andar === 'terreo' ? '↑' : '↓';
            const [lx, ly] = P(S0 + 3.5, 2.7);
            el('text', { x: lx + 0.1, y: ly + 0.2, class: 'mapa-etiqueta', fill: 'var(--muted)', 'text-anchor': 'start' }, escada).textContent = 'Escada';
            if (andar === 'terreo') {
                const [ex, ey] = P(0, 0);
                el('rect', { x: ex - 1.6, y: ey - 0.2, width: 3.2, height: 0.4, rx: 0.12, fill: 'var(--mapa-vermelho)' }, escada);
                el('text', { x: ex, y: ey + 1.25, class: 'mapa-etiqueta', fill: 'var(--text)' }, escada).textContent = 'Entrada';
            }
        }

        /* ---------------- rota ---------------- */
        // caminho com as curvas arredondadas (fica mais "amigável" que ângulos retos)
        function caminhoSvg(pontos, raio = 1.1) {
            // pontos repetidos em sequência dariam divisão por zero (NaN) no cálculo da curva
            const q = pontos.map(([x, z]) => P(x, z))
                .filter((p, i, todos) => i === 0 || p[0] !== todos[i - 1][0] || p[1] !== todos[i - 1][1]);
            let d = `M${q[0][0]} ${q[0][1]}`;
            for (let i = 1; i < q.length - 1; i++) {
                const a = q[i - 1], b = q[i], c = q[i + 1];
                const l1 = Math.hypot(b[0] - a[0], b[1] - a[1]), l2 = Math.hypot(c[0] - b[0], c[1] - b[1]);
                const r1 = Math.min(raio, l1 / 2), r2 = Math.min(raio, l2 / 2);
                const s = [b[0] - (b[0] - a[0]) / l1 * r1, b[1] - (b[1] - a[1]) / l1 * r1];
                const e = [b[0] + (c[0] - b[0]) / l2 * r2, b[1] + (c[1] - b[1]) / l2 * r2];
                d += ` L${s[0]} ${s[1]} Q${b[0]} ${b[1]} ${e[0]} ${e[1]}`;
            }
            return d + ` L${q[q.length - 1][0]} ${q[q.length - 1][1]}`;
        }

        function pararAnimacao() {
            cancelAnimationFrame(animacao);
            animacao = 0;
        }

        function desenharRota(sala) {
            pararAnimacao();
            if (!camadaRota) return;
            camadaRota.innerHTML = '';
            if (!sala) return;
            const pontos = api.rotaNaPlanta(sala)[andar];
            if (!pontos) return; // sala do térreo vista no 1º andar: não há trecho aqui

            const d = caminhoSvg(pontos);
            const linha = { d, fill: 'none', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' };
            el('path', Object.assign({ stroke: '#10202f', 'stroke-width': 0.75, opacity: 0.18, transform: 'translate(0 .22)' }, linha), camadaRota);
            el('path', Object.assign({ stroke: 'var(--mapa-tinta)', 'stroke-width': 0.62, opacity: 0.95 }, linha), camadaRota);
            const base = el('path', Object.assign({ stroke: 'var(--primary)', 'stroke-width': 0.38, opacity: 0.55 }, linha), camadaRota);
            const fluxo = el('path', Object.assign({ stroke: 'var(--mapa-tinta)', 'stroke-width': 0.2, 'stroke-dasharray': '.1 .9' }, linha), camadaRota);
            const inicio = P(...pontos[0]);
            el('circle', { cx: inicio[0], cy: inicio[1], r: 0.5, fill: 'var(--primary)', stroke: 'var(--mapa-tinta)', 'stroke-width': 0.18 }, camadaRota);
            // "Você está aqui": quando a rota sai de uma sala (botão Estou aqui ou QR da porta)
            const origem = api.estado.origem;
            if (origem && origem !== 'entrada' && origem.andar === andar) {
                el('text', { x: inicio[0], y: inicio[1] - 1, class: 'mapa-etiqueta mapa-voce-esta-aqui', fill: 'var(--primary)' }, camadaRota).textContent = 'Você está aqui';
            }

            const fim = P(...pontos[pontos.length - 1]);
            let anel = null;
            if (andar === sala.andar) {
                // destino: alfinete com um anel pulsando
                anel = el('circle', { cx: fim[0], cy: fim[1], r: 1.6, fill: 'none', stroke: 'var(--primary)', 'stroke-width': 0.18, opacity: 0.6 }, camadaRota);
                const pino = el('g', { transform: `translate(${fim[0]} ${fim[1]})` }, camadaRota);
                el('circle', { r: 0.62, fill: 'var(--mapa-tinta)', stroke: 'var(--primary)', 'stroke-width': 0.3 }, pino);
                el('circle', { r: 0.22, fill: 'var(--primary)' }, pino);
            } else {
                // destino em outro andar: este trecho termina na escada
                el('text', { x: fim[0] + 1.2, y: fim[1] + 0.25, class: 'mapa-etiqueta', fill: 'var(--primary)', 'text-anchor': 'start' }, camadaRota).textContent = andar === 'terreo' ? '↑ suba aqui' : '↓ desça aqui';
            }

            // Quem pediu "menos movimento" no sistema vê a rota parada
            if (api.reduzirMovimento() || pausado) return;
            const bolinha = el('circle', { r: 0.46, fill: 'var(--mapa-tinta)', stroke: 'var(--primary)', 'stroke-width': 0.2 }, camadaRota);
            const total = base.getTotalLength();
            const t0 = performance.now();
            const quadro = (agora) => {
                const t = (agora - t0) / 1000, k = ((t * 9) % (total + 6)) / total;
                const p = base.getPointAtLength(Math.min(1, k) * total);
                bolinha.setAttribute('cx', p.x);
                bolinha.setAttribute('cy', p.y);
                bolinha.setAttribute('opacity', k > 1 ? 0 : 1);
                fluxo.setAttribute('stroke-dashoffset', -t * 2.2);
                if (anel) {
                    const pulso = (t % 1.6) / 1.6;
                    anel.setAttribute('r', 1 + pulso * 2.6);
                    anel.setAttribute('opacity', 1 - pulso);
                }
                animacao = requestAnimationFrame(quadro);
            };
            animacao = requestAnimationFrame(quadro);
        }

        function mostrarSala(sala, opcoes = {}) {
            svg.querySelectorAll('.mapa-sala').forEach((g) => {
                const ativa = !!sala && g.dataset.id === sala.id;
                g.classList.toggle('ativa', ativa);
                g.setAttribute('aria-pressed', String(ativa));
            });
            desenharRota(sala);
            if (sala && !opcoes.semCamera) enquadrar(sala);
        }

        /* ---------------- zoom e arrastar ---------------- */
        let arrastou = false, arraste = null;
        const ponteiros = new Map();

        function fitHome() {
            const r = svg.getBoundingClientRect();
            if (!r.width || !r.height) return Object.assign({}, HOME);
            const a = r.width / r.height;
            let vw = HOME.w, vh = HOME.h;
            if (a > HOME.w / HOME.h) vw = HOME.h * a; else vh = HOME.w / a;
            let x = HOME.x + HOME.w / 2 - vw / 2;
            const y = HOME.y + HOME.h / 2 - vh / 2;
            if (!api.estreito() && a > 1.2) x -= (150 / r.width) * vw; // abre espaço para a lista à esquerda
            return { x, y, w: vw, h: vh };
        }

        function zoomEm(fator, cx, cy) {
            const r = svg.getBoundingClientRect();
            const escala = Math.min(r.width / vb.w, r.height / vb.h);
            const ox = (r.width - vb.w * escala) / 2, oy = (r.height - vb.h * escala) / 2;
            const mx = vb.x + (cx - r.left - ox) / escala, my = vb.y + (cy - r.top - oy) / escala;
            const nw = Math.max(10, Math.min(110, vb.w * fator)), k = nw / vb.w;
            vb.x = mx - (mx - vb.x) * k;
            vb.y = my - (my - vb.y) * k;
            vb.w = nw;
            vb.h *= k;
            aplicarVB();
        }
        const zoomNoCentro = (fator) => {
            const r = svg.getBoundingClientRect();
            zoomEm(fator, r.left + r.width / 2, r.top + r.height / 2);
        };

        let idAnimVB = 0;
        function animarVB(destino) {
            const id = ++idAnimVB;
            if (api.reduzirMovimento()) { vb = destino; aplicarVB(); return; }
            const de = Object.assign({}, vb), t0 = performance.now();
            const passo = (agora) => {
                if (id !== idAnimVB) return;
                const k = Math.min(1, (agora - t0) / 700);
                const e = k < 0.5 ? 4 * k * k * k : 1 - Math.pow(-2 * k + 2, 3) / 2;
                ['x', 'y', 'w', 'h'].forEach((p) => { vb[p] = de[p] + (destino[p] - de[p]) * e; });
                aplicarVB();
                if (k < 1) requestAnimationFrame(passo);
            };
            requestAnimationFrame(passo);
        }

        // aproxima a câmera da rota + sala, deixando espaço para os painéis
        function enquadrar(sala) {
            const trecho = api.rotaNaPlanta(sala)[sala.andar] || [];
            const pts = trecho.map(([x, z]) => P(x, z)).concat([P(sala.x0, sala.z0), P(sala.x1, sala.z1)]);
            const xs = pts.map((p) => p[0]), ys = pts.map((p) => p[1]);
            const minx = Math.min(...xs) - 5, maxx = Math.max(...xs) + 5, miny = Math.min(...ys) - 5, maxy = Math.max(...ys) + 5;
            const r = svg.getBoundingClientRect();
            if (!r.width || !r.height) return;
            const a = r.width / r.height, estreito = api.estreito();
            let vw = maxx - minx, vh = maxy - miny;
            if (vw / vh < a) vw = vh * a; else vh = vw / a;
            vw = Math.max(vw, estreito ? 46 : 62);
            vh = vw / a;
            let cx = (minx + maxx) / 2;
            const cy = (miny + maxy) / 2;
            if (!estreito) cx -= (180 / r.width) * vw;
            animarVB({ x: cx - vw / 2, y: cy - (estreito ? vh * 0.27 : vh / 2), w: vw, h: vh });
        }

        svg.addEventListener('wheel', (e) => {
            e.preventDefault();
            zoomEm(e.deltaY > 0 ? 1.12 : 1 / 1.12, e.clientX, e.clientY);
        }, { passive: false });

        const distanciaPinca = () => {
            if (ponteiros.size < 2) return 0;
            const [a, b] = [...ponteiros.values()];
            return Math.hypot(a[0] - b[0], a[1] - b[1]);
        };
        svg.addEventListener('pointerdown', (e) => {
            ponteiros.set(e.pointerId, [e.clientX, e.clientY]);
            arrastou = false;
            arraste = { x: e.clientX, y: e.clientY, vx: vb.x, vy: vb.y, d: distanciaPinca() };
        });
        svg.addEventListener('pointermove', (e) => {
            if (!ponteiros.has(e.pointerId)) return;
            ponteiros.set(e.pointerId, [e.clientX, e.clientY]);
            if (ponteiros.size === 2) {
                // dois dedos: pinça para dar zoom
                const d = distanciaPinca();
                const [a, b] = [...ponteiros.values()];
                if (arraste.d) zoomEm(arraste.d / d, (a[0] + b[0]) / 2, (a[1] + b[1]) / 2);
                arraste.d = d;
                arrastou = true;
                return;
            }
            const dx = e.clientX - arraste.x, dy = e.clientY - arraste.y;
            if (!arrastou && Math.hypot(dx, dy) > 5) {
                arrastou = true;
                svg.classList.add('arrastando');
                // só "prende" o ponteiro quando vira arraste, para o clique simples continuar indo para a sala
                try { svg.setPointerCapture(e.pointerId); } catch (erro) { /* ponteiro já foi solto */ }
            }
            if (arrastou) {
                const r = svg.getBoundingClientRect(), escala = Math.min(r.width / vb.w, r.height / vb.h);
                vb.x = arraste.vx - dx / escala;
                vb.y = arraste.vy - dy / escala;
                aplicarVB();
            }
        });
        const soltar = (e) => {
            ponteiros.delete(e.pointerId);
            svg.classList.remove('arrastando');
            setTimeout(() => { arrastou = false; }, 0); // depois do "click" que vem logo em seguida
        };
        svg.addEventListener('pointerup', soltar);
        svg.addEventListener('pointercancel', soltar);

        // se a largura da tela mudar (girar o celular, redimensionar a janela), reenquadra
        let larguraAnterior = 0;
        new ResizeObserver(() => {
            const largura = svg.clientWidth;
            if (!largura || largura === larguraAnterior) return;
            larguraAnterior = largura;
            if (api.estado.sala && api.estado.modo === '2d') enquadrar(api.estado.sala);
            else { vb = fitHome(); aplicarVB(); }
        }).observe(svg);

        // se a pessoa ligar/desligar "reduzir movimento" com a página aberta
        window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener?.('change', () => {
            if (!pausado) desenharRota(api.estado.sala);
        });

        vb = fitHome();
        aplicarVB();
        desenharPlanta();

        // Teclado (I2): com a planta focada, setas movem e + / - aproximam e afastam
        svg.addEventListener('keydown', (evento) => {
            if (evento.target !== svg) return; // dentro de uma sala, Enter escolhe a sala
            const passo = vb.w * 0.1;
            const movimentos = { ArrowUp: [0, -passo], ArrowDown: [0, passo], ArrowLeft: [-passo, 0], ArrowRight: [passo, 0] };
            if (movimentos[evento.key]) {
                evento.preventDefault();
                vb.x += movimentos[evento.key][0];
                vb.y += movimentos[evento.key][1];
                aplicarVB();
            } else if (evento.key === '+' || evento.key === '=' || evento.key === '-') {
                evento.preventDefault();
                const r = svg.getBoundingClientRect();
                zoomEm(evento.key === '-' ? 1.25 : 0.8, r.left + r.width / 2, r.top + r.height / 2);
            }
        });

        return {
            definirAndar(novo) {
                andar = novo === 'andar1' ? 'andar1' : 'terreo';
                desenharPlanta();
            },
            mostrarSala,
            ativar() {
                pausado = false;
                larguraAnterior = svg.clientWidth;
                vb = fitHome();
                aplicarVB();
            },
            pausar() {
                pausado = true;
                pararAnimacao();
            },
            aproximar: () => zoomNoCentro(1 / 1.3),
            afastar: () => zoomNoCentro(1.3),
            visaoGeral: () => animarVB(fitHome()),
        };
    },
};
