// MAPA 3D - maquete do prédio feita com three.js (r128). É OPCIONAL.
// Este arquivo (e o three.js) só é baixado quando alguém clica em "Ver em 3D"
// (ver carregarScripts() no js/mapa.js). Ele só sabe DESENHAR; o estado
// (sala/andar) continua no js/mapa.js, que usa o objeto devolvido por iniciar():
//   definirAndar(andar, {semCamera})  -> 'todos', 'terreo' ou 'andar1'
//   mostrarSala(sala)                 -> destaca a sala, desenha a rota e leva a câmera
//   girar(), visaoGeral()             -> botões da direita
//   destruir()                        -> para a animação e libera a memória da placa de vídeo

window.Mapa3D = {
    iniciar(area, api) {
        'use strict';

        const THREE = window.THREE;
        const { CW, RD, WH, FY, X1, XC, STAIR_RUN, S0, S1, ALA, ED, PORTAO_X, fimDaAla } = api.planta;
        const ZT = ALA.terreo;

        /* ================= RENDERIZADOR, CÂMERA E LUZ ================= */
        area.innerHTML = '';
        const largura = () => area.clientWidth || 1;
        const altura = () => area.clientHeight || 1;

        // se não houver WebGL, a linha abaixo dá erro e o mapa.js volta para o 2D
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
        renderer.setSize(largura(), altura());
        renderer.outputEncoding = THREE.sRGBEncoding;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.05;
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        renderer.domElement.setAttribute('role', 'img');
        renderer.domElement.setAttribute('aria-label', 'Maquete 3D do prédio. Para escolher uma sala pelo teclado, use a lista de setores.');
        area.appendChild(renderer.domElement);

        // etiquetas em HTML que acompanham a maquete (nomes das salas, "Escada" etc.)
        const rotulosR = new THREE.CSS2DRenderer();
        rotulosR.setSize(largura(), altura());
        rotulosR.domElement.className = 'mapa3d-rotulos';
        area.appendChild(rotulosR.domElement);

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(36, largura() / altura(), 0.5, 400);
        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.07;
        controls.minDistance = 10;
        controls.maxDistance = 130;
        controls.maxPolarAngle = Math.PI * 0.47; // não deixa a câmera ir para baixo do chão
        controls.autoRotateSpeed = 0.8;

        const col = (cor) => new THREE.Color(cor).convertSRGBToLinear();
        const std = (cor, o = {}) => new THREE.MeshStandardMaterial(Object.assign({ color: col(cor), roughness: 0.85, metalness: 0 }, o));

        const hemi = new THREE.HemisphereLight(0xffffff, 0x8a96a3, 0.75);
        scene.add(hemi);
        const sol = new THREE.DirectionalLight(0xffffff, 0.95);
        sol.position.set(-18, 42, 30);
        sol.castShadow = true;
        sol.shadow.mapSize.set(2048, 2048);
        Object.assign(sol.shadow.camera, { left: -55, right: 55, top: 50, bottom: -50, near: 1, far: 130 });
        sol.shadow.bias = -0.0006;
        sol.shadow.normalBias = 0.03;
        sol.target.position.set(15, 0, 0);
        scene.add(sol, sol.target);

        const CENTRO = new THREE.Vector3(14, 2.5, 0);
        const CASA = new THREE.Vector3(-44, 56, 30); // posição da câmera na visão geral

        /* ================= CORES =================
           As cores principais vêm do CSS (css/mapa.css e --primary do site),
           assim o 3D acompanha o tema claro/escuro igual ao resto da página. */
        function paleta() {
            const css = (nome, padrao) => api.corCss(nome) || padrao;
            const comum = {
                bg: css('--mapa-fundo', '#dfe9e4'), accent: css('--primary', '#0f7a43'),
                esq: css('--mapa-esq', '#a98ae0'), dir: css('--mapa-dir', '#6aa8da'),
            };
            return Object.assign(api.temaEscuro() ? {
                ground: 0x1f3a2c, edge: 0x142a20, slab: 0x4a5663, wall: 0xaab4bf, corr: 0x39434f, green: 0x3fb85a, red: 0xe0454a,
                path: 0x6b6358, trunk: 0x5a4330, leaf: [0x2c6b46, 0x347a50, 0x255c3b], desk: 0x7c8794, hemi: 0.5, sun: 0.7,
            } : {
                ground: 0x9fcf93, edge: 0x6f9d66, slab: 0xe8e2d6, wall: 0xfbfaf6, corr: 0xd2cdc2, green: 0x2f9e41, red: 0xcd191e,
                path: 0xe3d3b0, trunk: 0x8b6a4a, leaf: [0x4fa85f, 0x5fb86a, 0x3f9553], desk: 0xc7a77d, hemi: 0.75, sun: 0.95,
            }, comum);
        }

        /* ================= CONSTRUÇÃO DA MAQUETE ================= */
        let world = null;
        let C = null;              // paleta em uso
        let modo = 'terreo';       // andar mostrado
        let selecionada = null;    // sala destacada
        let pickables = [];        // caixas invisíveis usadas para saber em qual sala clicaram
        const floorGroups = {};
        const tagEls = {};

        function box(g, w, h, d, x, y, z, mat, sombra = true) {
            const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat);
            m.position.set(x, y, z);
            if (sombra) { m.castShadow = true; m.receiveShadow = true; }
            g.add(m);
            return m;
        }
        function rotulo(g, texto, classe, x, y, z) {
            const div = document.createElement('div');
            div.className = 'mapa3d-rotulo ' + classe;
            div.textContent = texto; // textContent: o nome nunca vira HTML
            const o = new THREE.CSS2DObject(div);
            o.position.set(x, y, z);
            g.add(o);
            div._o = o;
            return div;
        }

        // placa "IFRO" desenhada num canvas e usada como textura
        function texturaPlaca(l1, l2) {
            const c = document.createElement('canvas');
            c.width = 1024; c.height = 256;
            const x = c.getContext('2d');
            x.fillStyle = '#ffffff'; x.fillRect(0, 0, 1024, 256);
            x.fillStyle = '#' + new THREE.Color(C.green).getHexString(); x.fillRect(0, 0, 1024, 18);
            x.fillStyle = '#' + new THREE.Color(C.red).getHexString(); x.fillRect(0, 238, 1024, 18);
            x.fillStyle = '#1b2733'; x.textAlign = 'center';
            x.font = '800 110px Inter, system-ui, Arial, sans-serif'; x.fillText(l1, 512, 142);
            x.font = '600 48px Inter, system-ui, Arial, sans-serif'; x.fillText(l2, 512, 205);
            const t = new THREE.CanvasTexture(c);
            t.encoding = THREE.sRGBEncoding;
            t.anisotropy = 4;
            return t;
        }
        function placa(g, x, y, z, w, h, l1, l2) {
            const frente = new THREE.MeshBasicMaterial({ map: texturaPlaca(l1, l2), toneMapped: false });
            const fundo = new THREE.MeshStandardMaterial({ color: col(0xffffff), roughness: 0.8 });
            const b = new THREE.Mesh(new THREE.BoxGeometry(0.15, h, w), fundo);
            b.position.set(x, y, z);
            b.castShadow = true;
            g.add(b);
            [-1, 1].forEach((k) => {
                const p = new THREE.Mesh(new THREE.PlaneGeometry(w, h), frente);
                p.rotation.y = k * Math.PI / 2;
                p.position.set(x + k * 0.09, y, z);
                g.add(p);
            });
        }

        function construirAndar(f) {
            const g = new THREE.Group();
            g.position.y = f === 'andar1' ? FY : 0;
            floorGroups[f] = g;
            world.add(g);

            const slabM = std(C.slab), corrM = std(C.corr, { roughness: 0.7 });
            const wallM = std(C.wall, { roughness: 0.9 }), capM = std(C.green, { roughness: 0.6 });
            const wd = 0.2, Z = ALA[f], buraco = 0.8;
            const faixa = (x0, x1, zA, zB, h, y, m, sh = true) => box(g, x1 - x0, h, zB - zA, (x0 + x1) / 2, y, (zA + zB) / 2, m, sh);
            const XB = X1 + CW + RD;      // fundo do bloco
            const ZB = fimDaAla(f);       // ponta das alas (com a sala de ponta, se houver)

            // lajes e pisos dos corredores (no 1º andar fica um buraco para a escada)
            if (f === 'terreo') {
                faixa(-0.6, X1, -CW / 2 - 0.6, CW / 2 + 0.6, 0.5, -0.25, slabM);
                faixa(X1 - RD - 0.6, XB + 0.6, -ZB - 0.6, ZB + 0.6, 0.5, -0.25, slabM);
                faixa(0, X1, -CW / 2, CW / 2, 0.06, 0.03, corrM, false).receiveShadow = true;
            } else {
                faixa(-0.6, S0, -CW / 2 - 0.6, CW / 2 + 0.6, 0.5, -0.25, slabM);
                faixa(S0, X1, buraco, CW / 2 + 0.6, 0.5, -0.25, slabM);
                faixa(S0, X1, -CW / 2 - 0.6, -buraco, 0.5, -0.25, slabM);
                faixa(X1 - RD - 0.6, X1, CW / 2, ZB + 0.6, 0.5, -0.25, slabM);
                faixa(X1 - RD - 0.6, X1, -ZB - 0.6, -CW / 2, 0.5, -0.25, slabM);
                faixa(X1, XB + 0.6, -ZB - 0.6, ZB + 0.6, 0.5, -0.25, slabM);
                faixa(0, S0, -CW / 2, CW / 2, 0.06, 0.03, corrM, false);
                faixa(S0, X1, buraco, CW / 2, 0.06, 0.03, corrM, false);
                faixa(S0, X1, -CW / 2, -buraco, 0.06, 0.03, corrM, false);
            }
            faixa(X1, X1 + CW, -Z, Z, 0.06, 0.03, corrM, false); // corredor transversal

            // paredes do corredor de entrada
            [-1, 1].forEach((k) => {
                box(g, X1 - RD, WH, wd, (X1 - RD) / 2, WH / 2, k * CW / 2, wallM);
                box(g, X1 - RD, 0.08, wd + 0.06, (X1 - RD) / 2, WH + 0.04, k * CW / 2, capM, false);
            });
            box(g, wd, WH, CW, X1 + CW + RD, WH / 2, 0, wallM); // fundo do corredor transversal

            api.salas.filter((s) => s.andar === f).forEach((s) => construirSala(g, s, wallM, capM));

            // setas no piso do corredor de entrada
            for (let x = 2; x < S0 - 1; x += 3) {
                const forma = new THREE.Shape();
                forma.moveTo(0, 0.28); forma.lineTo(0.5, 0); forma.lineTo(0, -0.28); forma.lineTo(0.14, 0); forma.closePath();
                const m = new THREE.Mesh(new THREE.ShapeGeometry(forma), new THREE.MeshBasicMaterial({ color: col(C.accent), transparent: true, opacity: 0.28 }));
                m.rotation.x = -Math.PI / 2;
                m.position.set(x, 0.075, 0);
                g.add(m);
            }

            // entrada (térreo) ou vidraça (1º andar)
            if (f === 'terreo') {
                const moldura = std(C.red, { roughness: 0.5 });
                box(g, 0.25, 2.8, 0.25, -0.1, 1.4, -1.6, moldura);
                box(g, 0.25, 2.8, 0.25, -0.1, 1.4, 1.6, moldura);
                box(g, 0.3, 0.3, CW + 0.35, -0.1, 2.85, 0, moldura);
                placa(g, -0.1, 3.75, 0, 4.2, 1.05, 'IFRO', 'Campus Guajará-Mirim');
                rotulo(g, 'Entrada', 'rotulo-lugar', -0.1, 4.9, 0);
            } else {
                box(g, wd, WH, CW + 0.2, 0, WH / 2, 0, wallM);
                box(g, 0.08, 1.0, CW - 0.5, -0.04, 1.5, 0, std(C.dir, { transparent: true, opacity: 0.45, roughness: 0.1 }), false);
            }
            const etiquetaAndar = rotulo(g, api.nomesAndar[f] || f, 'rotulo-andar', 2.5, 0.4, -4);
            etiquetaAndar.dataset.andar = f;
        }

        function construirSala(g, r, wallM, capM) {
            const cor = r.sgn < 0 ? C.esq : C.dir;
            const w = r.w, d = r.d, wd = 0.2, Z = ALA[r.andar];
            const piso = box(g, w - wd, 0.07, d - wd, r.cx, 0.035, r.cz, std(cor, { roughness: 0.6 }), false);
            piso.receiveShadow = true;
            const tampa = (bw, bd, x, z) => box(g, bw, 0.08, bd, x, WH + 0.04, z, capM, false);
            const larguraPorta = r.end ? 2.2 : 1.6;

            // parede paralela ao eixo z (na posição x); com porta, vira duas partes + verga
            const paredeX = (x, zc, comp, porta) => {
                if (!porta) { box(g, wd, WH, comp, x, WH / 2, zc, wallM); tampa(wd + 0.06, comp, x, zc); return; }
                const lado = (comp - larguraPorta) / 2;
                [-1, 1].forEach((k) => {
                    const pz = zc + k * (larguraPorta / 2 + lado / 2);
                    box(g, wd, WH, lado, x, WH / 2, pz, wallM);
                    tampa(wd + 0.06, lado, x, pz);
                });
                box(g, wd, WH - 1.9, larguraPorta, x, 1.9 + (WH - 1.9) / 2, zc, wallM);
                box(g, wd + 0.1, 0.1, larguraPorta + 0.15, x, 1.9, zc, std(cor, { roughness: 0.5 }), false);
            };
            // parede paralela ao eixo x (na posição z)
            const paredeZ = (z, xc, comp, porta) => {
                if (!porta) { box(g, comp, WH, wd, xc, WH / 2, z, wallM); tampa(comp, wd + 0.06, xc, z); return; }
                const lado = (comp - larguraPorta) / 2;
                [-1, 1].forEach((k) => {
                    const px = xc + k * (larguraPorta / 2 + lado / 2);
                    box(g, lado, WH, wd, px, WH / 2, z, wallM);
                    tampa(lado, wd + 0.06, px, z);
                });
                box(g, larguraPorta, WH - 1.9, wd, xc, 1.9 + (WH - 1.9) / 2, z, wallM);
                box(g, larguraPorta + 0.15, 0.1, wd + 0.1, xc, 1.9, z, std(cor, { roughness: 0.5 }), false);
            };
            const zLo = r.cz - d / 2, zHi = r.cz + d / 2;
            if (r.end) {
                paredeX(r.x0, r.cz, d, false);
                paredeX(r.x1, r.cz, d, false);
                const longe = r.sgn < 0 ? zLo : zHi, perto = r.sgn < 0 ? zHi : zLo;
                paredeZ(longe, r.cx, w, false);
                paredeZ(perto, r.cx, w, true);
            } else {
                paredeZ(zLo, r.cx, w, false);
                paredeZ(zHi, r.cx, w, false);
                paredeX(r.frente ? r.x0 : r.x1, r.cz, d, false);
                paredeX(r.doorX, r.cz, d, true);
            }

            // móveis simples, só para dar escala
            const movel = std(C.desk, { roughness: 0.6 });
            if (r.end && r.ala === 'esquerda') { // auditório: palco + fileiras de cadeiras
                box(g, w - 3, 0.35, 1.5, r.cx, 0.2, r.sgn * (Z + ED - 1.3), std(C.slab));
                for (let i = 0; i < 4; i++) {
                    for (let j = -2; j <= 2; j++) if (j) box(g, 1.1, 0.5, 0.5, r.cx + j * 1.6, 0.3, r.sgn * (Z + 1.5 + i * 1.1), movel);
                }
            } else if (r.end) { // biblioteca: estantes + balcão
                for (let j = -1; j <= 1; j++) box(g, 0.4, 1.3, 4.5, r.cx + j * 3.2, 0.7, r.sgn * (Z + 4.8), movel);
                box(g, 2.2, 0.5, 1.2, r.cx, 0.3, r.sgn * (Z + 1.8), movel);
            } else {
                const aoLongo = d > w ? 'z' : 'x';
                [-1, 1].forEach((k) => box(g, 1.3, 0.55, 0.7,
                    r.cx + (aoLongo === 'x' ? k * 1.1 : 0), 0.34, r.cz + (aoLongo === 'z' ? k * Math.min(1.1, d / 4) : 0), movel));
            }

            // caixa invisível do tamanho da sala: é nela que o clique "acerta"
            const alvo = new THREE.Mesh(new THREE.BoxGeometry(w - wd, WH, d - wd),
                new THREE.MeshBasicMaterial({ transparent: true, opacity: 0, depthWrite: false }));
            alvo.position.set(r.cx, WH / 2, r.cz);
            alvo.userData.sala = r;
            alvo.userData.hit = true;
            g.add(alvo);
            pickables.push(alvo);
            r._piso3d = piso;
            tagEls[r.id] = rotulo(g, r.nomeCurto, 'rotulo-sala', r.cx, WH + 0.5, r.cz);
        }

        function construirEscada() {
            const N = 14, degrau = STAIR_RUN / N, pedra = std(C.slab), corrimao = std(C.accent, { roughness: 0.5 });
            const sg = new THREE.Group();
            world.add(sg);
            for (let i = 0; i < N; i++) {
                const topo = (i + 1) * FY / N;
                box(sg, degrau, topo, 1.6, S0 + (i + 0.5) * degrau, topo / 2, 0, pedra);
            }
            const comp = Math.hypot(STAIR_RUN, FY), ang = Math.atan2(FY, STAIR_RUN);
            [-1, 1].forEach((k) => {
                const barra = new THREE.Mesh(new THREE.BoxGeometry(comp, 0.08, 0.08), corrimao);
                barra.position.set((S0 + S1) / 2, FY / 2 + 1.0, k * 0.85);
                barra.rotation.z = ang;
                barra.castShadow = true;
                sg.add(barra);
                [0.1, 0.35, 0.6, 0.85].forEach((t) => {
                    const poste = new THREE.Mesh(new THREE.BoxGeometry(0.06, 1.0, 0.06), corrimao);
                    poste.position.set(S0 + STAIR_RUN * t, FY * t + 0.5, k * 0.85);
                    sg.add(poste);
                });
            });
            rotulo(sg, 'Escada', 'rotulo-lugar', (S0 + S1) / 2, FY / 2 + 2.4, 0);
            floorGroups.escada = sg;
        }

        // gerador de números "aleatórios" sempre iguais: as árvores ficam no mesmo lugar toda vez
        function aleatorio(semente) {
            let s = semente;
            return () => (s = (s * 16807) % 2147483647) / 2147483647;
        }

        function construirTerreno() {
            const g = new THREE.Group();
            world.add(g);
            const disco = new THREE.Mesh(new THREE.CylinderGeometry(72, 72, 1.4, 96), std(C.ground, { roughness: 1 }));
            disco.position.set(14, -1.2, 0);
            disco.receiveShadow = true;
            g.add(disco);
            const borda = new THREE.Mesh(new THREE.CylinderGeometry(72.6, 73.4, 1.4, 96), std(C.edge, { roughness: 1 }));
            borda.position.set(14, -2.5, 0);
            g.add(borda);

            // caminho até a entrada + portão
            box(g, -PORTAO_X, 0.12, 2.6, PORTAO_X / 2, 0.06, 0, std(C.path), false).receiveShadow = true;
            const pilar = std(C.slab);
            box(g, 0.5, 2.6, 0.5, PORTAO_X, 1.3, -2.2, pilar);
            box(g, 0.5, 2.6, 0.5, PORTAO_X, 1.3, 2.2, pilar);
            box(g, 0.5, 0.4, 5.2, PORTAO_X, 2.7, 0, std(C.red, { roughness: 0.5 }));
            placa(g, PORTAO_X, 4.3, 0, 5.6, 1.4, 'IFRO', 'Campus Guajará-Mirim');
            [-1, 1].forEach((k) => box(g, 0.2, 1.4, 0.2, PORTAO_X, 3.4, k * 2.6, pilar));
            rotulo(g, 'Portão', 'rotulo-lugar', PORTAO_X, 3.7, 0);

            // mastros com as bandeiras
            [[-6, 4.2, 0x2f9e41], [-6, 5.6, 0xffffff], [-6, 7.0, 0xcd191e]].forEach(([x, z, c]) => {
                const mastro = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.08, 6, 8), std(0xcfd6dd));
                mastro.position.set(x, 3.2, z);
                mastro.castShadow = true;
                g.add(mastro);
                box(g, 0.05, 1.0, 1.7, x, 5.5, z + 0.95, std(c, { roughness: 0.7 }));
            });

            // árvores, sem nascer em cima do prédio nem no caminho
            const r = aleatorio(7);
            const XB0 = X1 + CW + RD, ZMAX = Math.max(fimDaAla('terreo'), fimDaAla('andar1'));
            const pontos = [];
            for (let i = 0; i < 70; i++) {
                const x = -28 + r() * 90, z = (r() < 0.5 ? -1 : 1) * (4 + r() * 44);
                if (x > -4 && x < XB0 + 6 && Math.abs(z) < ZMAX + 6) continue;
                if (x < -2 && Math.abs(z) < 5) continue;
                if (Math.hypot(x - 14, z) > 68) continue;
                pontos.push([x, z]);
            }
            pontos.push([-12, 5.5], [-12, -5.5], [-6, 7], [-6, -7]);
            const tronco = std(C.trunk);
            const folhas = C.leaf.map((c) => std(c, { roughness: 0.9 }));
            pontos.forEach(([x, z], i) => {
                const s = 0.6 + r() * 0.6;
                const t = new THREE.Mesh(new THREE.CylinderGeometry(0.15 * s, 0.22 * s, 1.2 * s, 7), tronco);
                t.position.set(x, 0.2 + 0.6 * s, z);
                t.castShadow = true;
                g.add(t);
                const f = folhas[i % 3];
                const c1 = new THREE.Mesh(new THREE.ConeGeometry(1.2 * s, 2.4 * s, 8), f);
                c1.position.set(x, 0.2 + 2.0 * s, z);
                const c2 = new THREE.Mesh(new THREE.ConeGeometry(0.9 * s, 2 * s, 8), f);
                c2.position.set(x, 0.2 + 3.2 * s, z);
                [c1, c2].forEach((c) => { c.castShadow = true; g.add(c); });
            });

            // Peças do cenário cadastradas no admin (mesmas do 2D; cores em PECAS_MAPA, js/util.js).
            // Na planta a largura vai para os lados (z) e a profundidade de frente pra trás (x).
            (api.pecas || []).forEach((peca) => {
                const info = (typeof PECAS_MAPA !== 'undefined' && PECAS_MAPA[peca.tipo]) || { cor: '#94a3b8', altura: 2 };
                const giro = -(peca.rotacao || 0) * Math.PI / 180; // mesmo giro do SVG do 2D
                if (info.redonda) {
                    const s = Math.max(0.5, Math.min(peca.largura, peca.profundidade) / 2.4);
                    const tronco3d = new THREE.Mesh(new THREE.CylinderGeometry(0.15 * s, 0.22 * s, 1.2 * s, 7), std(C.trunk));
                    tronco3d.position.set(peca.x, 0.2 + 0.6 * s, peca.z);
                    const copa = new THREE.Mesh(new THREE.ConeGeometry(1.2 * s, 2.4 * s, 8), std(info.cor, { roughness: 0.9 }));
                    copa.position.set(peca.x, 0.2 + 2.0 * s, peca.z);
                    [tronco3d, copa].forEach((m) => { m.castShadow = true; g.add(m); });
                } else {
                    const altura = info.altura || 1;
                    const bloco = box(g, peca.profundidade, altura, peca.largura, peca.x, altura / 2, peca.z, std(info.cor, { roughness: 0.8 }));
                    bloco.rotation.y = giro;
                }
                if (peca.nome) rotulo(g, peca.nome, 'rotulo-lugar', peca.x, (info.altura || 1) + 1.2, peca.z);
            });
        }

        // Libera da memória tudo que está dentro de um objeto (geometrias, materiais, texturas)
        // e tira da página as etiquetas HTML que estavam dentro dele (o three r128 não faz isso
        // sozinho quando quem sai da cena é o grupo "pai", e elas ficariam duplicadas na tela).
        function liberar(objeto) {
            objeto.traverse((o) => {
                if (o.element && o.element.parentNode) o.element.parentNode.removeChild(o.element);
                if (o.geometry) o.geometry.dispose();
                if (o.material) {
                    (Array.isArray(o.material) ? o.material : [o.material]).forEach((m) => {
                        if (m.map) m.map.dispose();
                        m.dispose();
                    });
                }
            });
        }

        // (Re)constrói tudo. Também é chamada quando o tema claro/escuro muda.
        function construirMundo() {
            limparRota();
            if (world) {
                scene.remove(world); // as etiquetas HTML saem junto (evento "removed" do CSS2DObject)
                liberar(world);
            }
            pickables = [];
            Object.keys(tagEls).forEach((k) => delete tagEls[k]);
            C = paleta();
            scene.background = col(C.bg);
            scene.fog = new THREE.Fog(col(C.bg), 90, 190);
            hemi.intensity = C.hemi;
            sol.intensity = C.sun;
            world = new THREE.Group();
            world.position.y = 0.4;
            scene.add(world);
            construirTerreno();
            construirAndar('terreo');
            construirAndar('andar1');
            construirEscada();
            aplicarAndar();
            destacar(selecionada);
        }

        /* ================= ANDARES ================= */
        // deixa um grupo "fantasma" (quase transparente) ou normal
        function fantasma(grupo, ligado) {
            grupo.traverse((o) => {
                if (!o.material || o.userData.hit) return;
                (Array.isArray(o.material) ? o.material : [o.material]).forEach((m) => {
                    if (m.userData.o === undefined) m.userData.o = m.opacity;
                    m.transparent = ligado || m.userData.o < 1;
                    m.opacity = ligado ? Math.min(m.userData.o, 0.16) : m.userData.o;
                    m.depthWrite = !ligado;
                });
                o.castShadow = !ligado && o.userData.cs !== false;
            });
        }
        const escondido = (andarDoItem) =>
            (modo === 'terreo' && andarDoItem === 'andar1') || (modo === 'andar1' && andarDoItem === 'terreo');

        function aplicarAndar() {
            floorGroups.andar1.visible = modo !== 'terreo';
            fantasma(floorGroups.terreo, modo === 'andar1');
            fantasma(floorGroups.andar1, false);
            fantasma(floorGroups.escada, modo === 'andar1');
            // em "Os dois", só as etiquetas do andar de cima aparecem (senão vira bagunça)
            api.salas.forEach((s) => {
                const el = tagEls[s.id];
                if (el) el._o.visible = !(escondido(s.andar) || (modo === 'todos' && s.andar === 'terreo'));
            });
            area.querySelectorAll('.rotulo-andar').forEach((el) => { el._o.visible = !escondido(el.dataset.andar); });
        }

        /* ================= ROTA ================= */
        let rota = null;
        const ROTA_Y = 0.32;

        // Mesma rota do 2D (api.trechosDaRota, que já sabe de onde a pessoa sai), só que em 3D:
        // cada trecho fica na altura do seu andar; a ligação entre os trechos é a escada.
        function pontosDaRota(r) {
            const pts = [];
            api.trechosDaRota(r).forEach((trecho) => {
                const y = trecho.andar === 'andar1' ? FY + ROTA_Y : ROTA_Y;
                trecho.pontos.forEach(([x, z]) => pts.push(new THREE.Vector3(x, y, z)));
            });
            return pts;
        }

        // arredonda as quinas da rota com pequenas curvas
        function arredondar(pts, raio, seg = 8) {
            const saida = [pts[0].clone()];
            for (let i = 1; i < pts.length - 1; i++) {
                const a = pts[i - 1], b = pts[i], c = pts[i + 1];
                const d1 = b.clone().sub(a), d2 = c.clone().sub(b);
                if (d1.clone().normalize().dot(d2.clone().normalize()) > 0.999) { saida.push(b.clone()); continue; }
                const r1 = Math.min(raio, d1.length() / 2), r2 = Math.min(raio, d2.length() / 2);
                const ini = b.clone().sub(d1.clone().normalize().multiplyScalar(r1));
                const fim = b.clone().add(d2.clone().normalize().multiplyScalar(r2));
                new THREE.QuadraticBezierCurve3(ini, b, fim).getPoints(seg).forEach((q) => saida.push(q));
            }
            saida.push(pts[pts.length - 1].clone());
            return saida;
        }

        function texturaListra() {
            const c = document.createElement('canvas');
            c.width = 128; c.height = 16;
            const x = c.getContext('2d');
            const gr = x.createLinearGradient(0, 0, 128, 0);
            gr.addColorStop(0, 'rgba(255,255,255,0)');
            gr.addColorStop(0.55, 'rgba(255,255,255,1)');
            gr.addColorStop(1, 'rgba(255,255,255,0.15)');
            x.fillStyle = gr;
            x.fillRect(0, 0, 80, 16);
            const t = new THREE.CanvasTexture(c);
            t.wrapS = t.wrapT = THREE.RepeatWrapping;
            t.encoding = THREE.sRGBEncoding;
            return t;
        }

        function limparRota() {
            if (!rota) return;
            world.remove(rota.grupo);
            liberar(rota.grupo);
            rota = null;
        }

        function desenharRota(r) {
            limparRota();
            if (!r) return;
            const brutos = pontosDaRota(r);
            const curva = new THREE.CatmullRomCurve3(arredondar(brutos, 1.2), false, 'centripetal');
            const comp = curva.getLength();
            const grupo = new THREE.Group();
            const destaque = col(C.accent);
            const parado = api.reduzirMovimento();

            // trilho contínuo + listras andando por cima (depthTest false: aparece através das paredes)
            const base = new THREE.Mesh(new THREE.TubeGeometry(curva, Math.ceil(comp * 6), 0.1, 8, false),
                new THREE.MeshBasicMaterial({ color: destaque, transparent: true, opacity: 0.55, depthTest: false }));
            base.renderOrder = 10;
            grupo.add(base);
            const tex = texturaListra();
            tex.repeat.set(comp / 1.8, 1);
            const fluxo = new THREE.Mesh(new THREE.TubeGeometry(curva, Math.ceil(comp * 6), 0.16, 10, false),
                new THREE.MeshBasicMaterial({ map: tex, color: 0xffffff, transparent: true, depthTest: false, blending: THREE.AdditiveBlending }));
            fluxo.renderOrder = 11;
            fluxo.visible = !parado;
            grupo.add(fluxo);

            const bola = (raio, cor, extra = {}) => new THREE.Mesh(new THREE.SphereGeometry(raio, 20, 16),
                new THREE.MeshBasicMaterial(Object.assign({ color: cor, depthTest: false }, extra)));
            const origem = bola(0.32, destaque);
            origem.position.copy(brutos[0]);
            origem.renderOrder = 12;
            grupo.add(origem);

            // "andarilho": bolinha que percorre a rota
            const andarilho = bola(0.28, 0xffffff);
            andarilho.renderOrder = 13;
            const halo = bola(0.5, destaque, { transparent: true, opacity: 0.35 });
            halo.renderOrder = 12;
            andarilho.visible = halo.visible = !parado;
            grupo.add(andarilho, halo);

            // destino: pino + anel pulsando
            const fim = brutos[brutos.length - 1];
            const pino = new THREE.Mesh(new THREE.ConeGeometry(0.42, 1.1, 20), new THREE.MeshBasicMaterial({ color: destaque, depthTest: false }));
            pino.rotation.x = Math.PI;
            pino.position.set(fim.x, fim.y + 1.6, fim.z);
            pino.renderOrder = 12;
            grupo.add(pino);
            const anel = new THREE.Mesh(new THREE.RingGeometry(0.5, 0.75, 40),
                new THREE.MeshBasicMaterial({ color: destaque, transparent: true, opacity: 0.7, side: THREE.DoubleSide, depthTest: false }));
            anel.rotation.x = -Math.PI / 2;
            anel.position.set(fim.x, fim.y + 0.02, fim.z);
            anel.renderOrder = 12;
            grupo.add(anel);

            rotulo(grupo, r.nome, 'rotulo-destino', fim.x, fim.y + 2.6, fim.z);

            world.add(grupo);
            rota = { grupo, tex, curva, andarilho, halo, pino, anel, comp, fim, t: 0, parado };
        }

        // destaca a sala (piso brilhando + etiqueta verde) e desenha a rota
        function destacar(sala) {
            Object.entries(tagEls).forEach(([id, el]) => el.classList.toggle('ativa', !!sala && sala.id === id));
            api.salas.forEach((s) => {
                if (!s._piso3d) return;
                const ativa = !!sala && sala.id === s.id;
                s._piso3d.material.emissive = ativa ? col(C.accent) : new THREE.Color(0);
                s._piso3d.material.emissiveIntensity = ativa ? 0.55 : 0;
            });
            desenharRota(sala);
        }

        /* ================= CÂMERA ================= */
        let idVoo = 0;
        function voarPara(posicao, alvo, ms = 900) {
            const id = ++idVoo;
            if (api.reduzirMovimento()) { // sem animação: pula direto
                camera.position.copy(posicao);
                controls.target.copy(alvo);
                controls.update();
                return;
            }
            const p0 = camera.position.clone(), a0 = controls.target.clone(), t0 = performance.now();
            const passo = (agora) => {
                if (id !== idVoo || destruido) return;
                const k = Math.min(1, (agora - t0) / ms);
                const e = k < 0.5 ? 4 * k * k * k : 1 - Math.pow(-2 * k + 2, 3) / 2;
                camera.position.lerpVectors(p0, posicao, e);
                controls.target.lerpVectors(a0, alvo, e);
                controls.update();
                if (k < 1) requestAnimationFrame(passo);
            };
            requestAnimationFrame(passo);
        }
        function posicaoInicial() {
            const f = api.estreito() ? 2.2 : 1;
            const c = modo === 'terreo' ? new THREE.Vector3(14, 0.5, 0) : modo === 'andar1' ? new THREE.Vector3(14, FY, 0) : CENTRO.clone();
            const dir = CASA.clone().sub(CENTRO);
            if (modo !== 'todos') dir.multiplyScalar(0.8);
            return { pos: c.clone().add(dir.multiplyScalar(f)), alvo: c };
        }
        function visaoGeral() {
            const { pos, alvo } = posicaoInicial();
            voarPara(pos, alvo);
        }
        function enquadrar(r) {
            const caixa = new THREE.Box3().setFromPoints(pontosDaRota(r));
            const c = caixa.getCenter(new THREE.Vector3()).add(new THREE.Vector3(0, 0.4, 0));
            const s = caixa.getSize(new THREE.Vector3());
            const dist = Math.max(26, Math.max(s.x, s.z, s.y * 2.2) * 1.15 + 18);
            const estreito = api.estreito();
            const dir = new THREE.Vector3(-0.75, 0.95, 0.55).normalize();
            const desvio = estreito ? new THREE.Vector3(0, -2, 0) : new THREE.Vector3(4, 0, 4);
            voarPara(c.clone().add(dir.multiplyScalar(dist * (estreito ? 1.35 : 1))), c.clone().add(desvio));
        }

        /* ================= CLIQUE NAS SALAS ================= */
        const raio = new THREE.Raycaster(), mouse = new THREE.Vector2();
        let apertou = null;
        function salaSobOPonteiro(e, soVisiveis) {
            const rc = renderer.domElement.getBoundingClientRect();
            mouse.set(((e.clientX - rc.left) / rc.width) * 2 - 1, -((e.clientY - rc.top) / rc.height) * 2 + 1);
            raio.setFromCamera(mouse, camera);
            const candidatos = pickables.filter((m) => m.parent.visible && (!soVisiveis || !escondido(m.userData.sala.andar)));
            const acerto = raio.intersectObjects(candidatos)[0];
            return acerto ? acerto.object.userData.sala : null;
        }
        const aoApertar = (e) => {
            apertou = [e.clientX, e.clientY];
            if (controls.autoRotate) { controls.autoRotate = false; api.avisarGiro(false); }
        };
        const aoSoltar = (e) => {
            // só conta como clique se o dedo/mouse quase não andou (senão foi um "girar")
            if (!apertou || Math.hypot(e.clientX - apertou[0], e.clientY - apertou[1]) > 5) return;
            const sala = salaSobOPonteiro(e, true);
            if (sala) api.selecionar(sala);
        };
        const aoMover = (e) => {
            renderer.domElement.style.cursor = salaSobOPonteiro(e, true) ? 'pointer' : 'grab';
        };
        renderer.domElement.addEventListener('pointerdown', aoApertar);
        renderer.domElement.addEventListener('pointerup', aoSoltar);
        renderer.domElement.addEventListener('pointermove', aoMover);

        /* ================= TAMANHO, TEMA E LOOP ================= */
        function redimensionar() {
            const w = largura(), h = altura();
            camera.aspect = w / h;
            // no computador, empurra o desenho um pouco para a direita (a lista ocupa a esquerda)
            if (!api.estreito()) camera.setViewOffset(w, h, -150, 0, w, h);
            else camera.clearViewOffset();
            camera.updateProjectionMatrix();
            renderer.setSize(w, h);
            rotulosR.setSize(w, h);
        }
        const observadorTamanho = new ResizeObserver(redimensionar);
        observadorTamanho.observe(area);

        // o site troca o tema colocando/tirando a classe "tema-escuro" no <html>
        let escuroAntes = api.temaEscuro();
        const observadorTema = new MutationObserver(() => {
            const escuro = api.temaEscuro();
            if (escuro === escuroAntes) return; // ex.: só mudou o tamanho da fonte
            escuroAntes = escuro;
            construirMundo();
        });
        observadorTema.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        let destruido = false;
        let quadro = 0;
        const relogio = new THREE.Clock();
        function animar() {
            if (destruido) return;
            quadro = requestAnimationFrame(animar);
            const dt = Math.min(relogio.getDelta(), 0.05), agora = relogio.elapsedTime;
            controls.update();
            if (rota && !rota.parado) {
                rota.tex.offset.x -= dt * 0.9;
                rota.t = (rota.t + dt / Math.max(4, rota.comp / 5.5)) % 1;
                const p = rota.curva.getPointAt(rota.t);
                rota.andarilho.position.copy(p);
                rota.halo.position.copy(p);
                rota.halo.scale.setScalar(1 + 0.35 * Math.sin(agora * 6));
                rota.pino.position.y = rota.fim.y + 1.7 + Math.sin(agora * 3) * 0.25;
                const k = (agora * 0.8) % 1;
                rota.anel.scale.setScalar(1 + k * 2.2);
                rota.anel.material.opacity = 0.7 * (1 - k);
            }
            renderer.render(scene, camera);
            rotulosR.render(scene, camera);
        }

        // primeira montagem
        modo = api.estado.andar || 'terreo';
        construirMundo();
        redimensionar();
        const inicio = posicaoInicial();
        camera.position.copy(inicio.pos);
        controls.target.copy(inicio.alvo);
        controls.update();
        animar();

        return {
            definirAndar(andar, opcoes = {}) {
                modo = andar;
                aplicarAndar();
                if (!opcoes.semCamera) visaoGeral();
            },
            mostrarSala(sala) {
                selecionada = sala || null;
                destacar(selecionada);
                if (selecionada) {
                    if (controls.autoRotate) { controls.autoRotate = false; api.avisarGiro(false); }
                    enquadrar(selecionada);
                }
            },
            girar() {
                controls.autoRotate = !controls.autoRotate;
                api.avisarGiro(controls.autoRotate);
            },
            visaoGeral,
            // Passeio (W1): voa até [x,y,z] olhando pra [x,y,z]; a promessa resolve quando chega.
            voar(posicao, alvo, ms = 2200) {
                if (controls.autoRotate) { controls.autoRotate = false; api.avisarGiro(false); }
                voarPara(new THREE.Vector3(...posicao), new THREE.Vector3(...alvo), ms);
                return new Promise((resolver) => setTimeout(resolver, api.reduzirMovimento() ? 0 : ms));
            },
            alturaDoAndar1: FY,
            // Sair do 3D: para o loop, solta os eventos e devolve a memória da placa de vídeo
            destruir() {
                destruido = true;
                cancelAnimationFrame(quadro);
                idVoo++;
                observadorTamanho.disconnect();
                observadorTema.disconnect();
                renderer.domElement.removeEventListener('pointerdown', aoApertar);
                renderer.domElement.removeEventListener('pointerup', aoSoltar);
                renderer.domElement.removeEventListener('pointermove', aoMover);
                controls.dispose();
                limparRota();
                if (world) { scene.remove(world); liberar(world); world = null; }
                liberar(scene);
                if (sol.shadow.map) sol.shadow.map.dispose(); // textura de sombra também ocupa memória
                api.salas.forEach((s) => { delete s._piso3d; });
                renderer.dispose();
                renderer.forceContextLoss();
                area.innerHTML = '';
            },
        };
    },
};
