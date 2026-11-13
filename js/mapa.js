// MAPA DO CAMPUS - parte compartilhada entre o 2D e o 3D.
// Este arquivo cuida do que é igual nos dois mapas:
//   - lê os dados das salas (JSON que o PHP colocou na página);
//   - calcula a planta (onde fica cada sala) a partir da ordem/lado/ala;
//   - guarda o ESTADO único: modo (2d/3d), andar ativo e sala selecionada;
//   - controla a lista de setores, o cartão com o passo a passo e os botões;
//   - carrega o 3D só quando o usuário pede (lazy load).
// O desenho em si fica em js/mapa2d.js (SVG) e js/mapa3d.js (three.js).
// Os dois recebem o objeto "api" daqui e avisam de volta quando alguém clica numa sala.

(() => {
'use strict';

const palco = document.getElementById('mapa-palco');
const dadosElemento = document.getElementById('mapa-dados');
if (!palco || !dadosElemento) return;

let dados;
try {
    dados = JSON.parse(dadosElemento.textContent || '{}');
} catch (erro) {
    console.error('Não foi possível ler os dados do mapa:', erro);
    return;
}

// Site em espanhol (js/traducao-es.js): nomes de andares e salas já traduzidos,
// assim a lista e a busca do mapa funcionam em espanhol.
const ESPANHOL = document.documentElement.lang === 'es';
if (window.traduzirTexto) {
    Object.values(dados.andares || {}).forEach((andar) => {
        if (andar && andar.nome) andar.nome = window.traduzirTexto(andar.nome);
    });
    dados.salas = (dados.salas || []).map((sala) => ({
        ...sala,
        nome: window.traduzirTexto(sala.nome || ''),
        nomeCurto: window.traduzirTexto(sala.nomeCurto || sala.nome || ''),
    }));
}

/* =====================================================================
   PLANTA DO PRÉDIO (medidas em "metros" aproximados, iguais nos dois mapas)
   Eixo x = da entrada para o fundo do prédio (0 = porta de entrada).
   Eixo z = para os lados (negativo = ala esquerda, positivo = ala direita).
   Para mudar o tamanho do desenho, mexa só aqui.
   ===================================================================== */
const CW = 3.2;              // largura dos corredores
const RD = 5.4;              // profundidade das salas laterais (do corredor até a parede de fora)
const WH = 2.5;              // altura das paredes (só no 3D)
const FY = 6.5;              // altura em que o 1º andar é desenhado no 3D (vista "explodida")
const X1 = 12;               // distância da porta até o corredor transversal (o que corta o prédio)
const XC = X1 + CW / 2;      // eixo do corredor transversal
const STAIR_RUN = 7;         // comprimento da escada (ela termina no corredor transversal)
const S0 = X1 - STAIR_RUN;   // onde a escada começa
const S1 = X1;               // onde a escada termina
const ALA = { terreo: 16, andar1: 22 }; // comprimento de cada ala, medido do centro do prédio
const ED = 8;                // profundidade das salas de ponta (Auditório, Biblioteca)
const PORTAO_X = -18.5;      // posição do portão, antes da entrada
const ZT = ALA.terreo, ZU = ALA.andar1; // apelidos curtos usados pelos desenhos

const nomesAndar = {};
Object.entries(dados.andares || {}).forEach(([id, andar]) => { nomesAndar[id] = andar.nome; });

/* Calcula a posição de cada sala.
   Em cada ala, as salas de cada lado (frente/fundos) dividem o comprimento da
   ala igualmente, na ordem do campo 'ordem'. A sala de ponta fica depois do fim da ala. */
const salas = (dados.salas || []).map((s) => Object.assign({}, s));
const grupos = {};
salas.forEach((s) => {
    const chave = s.andar + '|' + s.ala;
    (grupos[chave] = grupos[chave] || []).push(s);
});
Object.values(grupos).forEach((lista) => {
    lista.sort((a, b) => a.ordem - b.ordem);
    const andar = lista[0].andar;
    const sgn = lista[0].ala === 'esquerda' ? -1 : 1;
    const Z = ALA[andar] || ALA.terreo;
    const normais = lista.filter((s) => !s.ponta);
    // se o 'lado' vier vazio/errado, alterna como nos protótipos (1ª frente, 2ª fundos...)
    normais.forEach((s, i) => {
        if (s.lado !== 'frente' && s.lado !== 'fundos') s.lado = i % 2 === 0 ? 'frente' : 'fundos';
    });
    ['frente', 'fundos'].forEach((lado) => {
        const doLado = normais.filter((s) => s.lado === lado);
        const largura = (Z - CW / 2) / Math.max(1, doLado.length);
        doLado.forEach((s, i) => {
            const za = CW / 2 + i * largura, zb = za + largura;
            const frente = lado === 'frente';
            const x0 = frente ? X1 - RD : X1 + CW;
            Object.assign(s, {
                sgn, frente, end: false, idx: i,          // idx = qual porta desse lado (0 = primeira)
                x0, x1: x0 + RD, z0: sgn * za, z1: sgn * zb,
                cx: x0 + RD / 2, cz: sgn * (za + zb) / 2, w: RD, d: largura,
                doorX: frente ? X1 : X1 + CW,             // parede do corredor, onde fica a porta
            });
        });
    });
    lista.filter((s) => s.ponta).forEach((s) => {
        Object.assign(s, {
            sgn, frente: false, end: true, idx: 99,
            x0: X1 - RD, x1: X1 + CW + RD, z0: sgn * Z, z1: sgn * (Z + ED),
            cx: XC, cz: sgn * (Z + ED / 2), w: 2 * RD + CW, d: ED,
        });
    });
});
const porId = Object.fromEntries(salas.map((s) => [s.id, s]));

// até onde vai a laje de cada andar (inclui a sala de ponta, se o andar tiver)
function fimDaAla(andar) {
    const temPonta = salas.some((s) => s.andar === andar && s.ponta);
    return (ALA[andar] || ALA.terreo) + (temPonta ? ED : 0);
}

/* ROTA NA PLANTA (pontos x,z), em trechos por andar, NA ORDEM do caminho.
   A origem vem de estado.origem: null = portão, 'entrada' = porta do prédio,
   ou uma sala ("Estou aqui" / QR da porta). O 2D, o 3D, a distância e o passo a passo usam isto. */
const FAIXA_ESCADA = 1.15; // a rota passa ao lado da escada, não por cima dela
function pontoDentroDaSala(s) {
    return s.end ? [XC, s.sgn * (ALA[s.andar] + 1.3)] : [s.doorX + (s.frente ? -1.3 : 1.3), s.cz];
}
function pontoNoCorredor(s) {
    return [XC, s.end ? s.sgn * ALA[s.andar] : s.cz];
}
// O prédio vira um grafo (js/grafo.js) e a rota é o menor caminho nele (Dijkstra).
const grafoPredio = window.Grafo.montarGrafoDoPredio({
    XC, S0, S1, STAIR_RUN, PORTAO_X, FAIXA_ESCADA,
    salas: salas.map((s) => ({ id: s.id, andar: s.andar, dentro: pontoDentroDaSala(s), corredor: pontoNoCorredor(s) })),
});
function calcularRota(s, origem = estado.origem) {
    const de = !origem ? 'portao' : origem === 'entrada' ? 'entrada' : `sala:${origem.id}`;
    return window.Grafo.rota(grafoPredio, de, `sala:${s.id}`) || { distancia: 0, trechos: [] };
}
function trechosDaRota(s, origem = estado.origem) {
    return calcularRota(s, origem).trechos;
}
// formato antigo, por andar: { terreo: [...], andar1: [...] } (o 2D desenha um andar por vez)
function rotaNaPlanta(s) {
    const porAndar = {};
    trechosDaRota(s).forEach((trecho) => { porAndar[trecho.andar] = trecho.pontos; });
    return porAndar;
}
// distância aproximada em metros = peso total do menor caminho (a escada entra com o comprimento dela)
function metros(s) {
    return Math.round(calcularRota(s).distancia);
}
function usaEscada(s) {
    return trechosDaRota(s).length > 1;
}
// segundos a pé (velocidade configurável no admin; 1,2 m/s é o passo normal)
function segundosAPe(s) {
    const velocidade = Number(dados.velocidadeCaminhada) || 1.2;
    return Math.round(metros(s) / velocidade + (usaEscada(s) ? 15 : 0));
}

/* PASSO A PASSO (texto do cartão), em português ou espanhol.
   Os nomes passam por escaparHtml (js/util.js) porque viram HTML. */
function passos(s, origem = estado.origem) {
    const es = ESPANHOL;
    const nome = escaparHtml(s.nome);
    const lado = s.sgn < 0 ? (es ? 'izquierda' : 'esquerda') : (es ? 'derecha' : 'direita');
    const ateFundo = s.end ? (es ? ' hasta el fondo' : ' até o fundo') : '';
    const lista = [];
    if (origem && origem !== 'entrada' && origem.id === s.id) {
        return [es ? `Usted ya está en <b>${nome}</b>.` : `Você já está na <b>${nome}</b>.`];
    }

    if (!origem || origem === 'entrada') {
        if (!origem) {
            lista.push(es
                ? 'Entre por el <b>portón</b> y siga el camino hasta la <b>entrada</b> del edificio.'
                : 'Entre pelo <b>portão</b> e siga o caminho até a <b>entrada</b> do prédio.');
        }
        if (s.andar === 'andar1') {
            lista.push(es
                ? 'Siga recto por el pasillo hasta la <b>escalera</b> y suba al <b>primer piso</b>; termina en el pasillo que cruza el edificio.'
                : 'Siga reto pelo corredor até a <b>escada</b> e suba ao <b>1º andar</b> — ela termina no corredor que corta o prédio.');
        } else {
            lista.push(es
                ? 'Siga recto por el pasillo, rodee la <b>escalera</b> por un lado y entre en el pasillo que cruza el edificio.'
                : 'Siga reto pelo corredor, contorne a <b>escada</b> pelo lado e entre no corredor que corta o prédio.');
        }
        lista.push(es ? `Gire a la <b>${lado}</b> y siga por el ala${ateFundo}.` : `Vire à <b>${lado}</b> e siga pela ala${ateFundo}.`);
    } else {
        const nomeOrigem = escaparHtml(origem.nome);
        lista.push(es ? `Salga de <b>${nomeOrigem}</b> al pasillo.` : `Saia da <b>${nomeOrigem}</b> para o corredor.`);
        if (origem.andar !== s.andar) {
            const subirDescer = s.andar === 'andar1'
                ? (es ? '<b>suba al primer piso</b>' : '<b>suba ao 1º andar</b>')
                : (es ? '<b>baje a la planta baja</b>' : '<b>desça ao térreo</b>');
            lista.push(es
                ? `Vaya hasta la <b>escalera</b>, en el medio del edificio, y ${subirDescer}.`
                : `Vá até a <b>escada</b>, no meio do prédio, e ${subirDescer}.`);
            lista.push(es ? `Gire a la <b>${lado}</b> y siga por el ala${ateFundo}.` : `Vire à <b>${lado}</b> e siga pela ala${ateFundo}.`);
        } else if (origem.sgn !== s.sgn) {
            lista.push(es
                ? `Siga por el pasillo hacia la escalera, pase por ella y continúe hacia el <b>ala ${lado}</b>${ateFundo}.`
                : `Siga pelo corredor em direção à escada, passe por ela e continue para a <b>ala ${lado}</b>${ateFundo}.`);
        } else {
            const vaiParaFundo = Math.abs(s.end ? 999 : s.cz) > Math.abs(origem.end ? 999 : origem.cz);
            lista.push(es
                ? `Siga por el pasillo ${vaiParaFundo ? 'hacia el <b>fondo del ala</b>' : 'hacia la <b>escalera</b>'}.`
                : `Siga pelo corredor ${vaiParaFundo ? 'em direção ao <b>fundo da ala</b>' : 'em direção à <b>escada</b>'}.`);
        }
    }
    if (s.end) {
        lista.push(es ? `La puerta al final del ala da a <b>${nome}</b>.` : `A porta no fim da ala dá na <b>${nome}</b>.`);
    } else if (es) {
        lista.push(`Es la puerta <b>n.º ${s.idx + 1}</b> contando desde la escalera, ${s.frente ? 'del lado frontal (de quien entró al edificio)' : 'del lado del fondo'}: <b>${nome}</b>.`);
    } else {
        lista.push(`É a <b>${s.idx + 1}ª</b> porta contando da escada, ${s.frente ? 'do lado da frente (de quem entrou no prédio)' : 'do lado dos fundos'}: <b>${nome}</b>.`);
    }
    lista.push(es ? `Ha llegado: <b>${nome}</b>.` : `Chegou: <b>${nome}</b>.`);
    return lista;
}

/* =====================================================================
   ESTADO ÚNICO dos dois mapas
   ===================================================================== */
const estado = {
    modo: '2d',        // '2d' ou '3d'
    andar: 'terreo',   // 'terreo', 'andar1' ou 'todos' (só no 3D)
    sala: null,        // objeto da sala selecionada (ou null)
    origem: null,      // de onde a rota sai: null = portão, 'entrada', ou uma sala ("Estou aqui")
};

const $ = (id) => document.getElementById(id);
const html = document.documentElement;
const movimentoReduzido = window.matchMedia('(prefers-reduced-motion: reduce)');

// Tudo que os desenhos (2D e 3D) podem usar. Eles nunca mexem no estado direto:
// pedem pelas funções selecionar() / mudarAndar().
const api = {
    planta: { CW, RD, WH, FY, X1, XC, STAIR_RUN, S0, S1, ZT, ZU, ED, ALA, PORTAO_X, fimDaAla },
    salas, porId, nomesAndar, estado, rotaNaPlanta, trechosDaRota,
    pecas: dados.pecas || [], // cenário editado no admin (Editor do mapa)
    selecionar: (sala) => selecionarSala(sala),
    mudarAndar: (andar) => mudarAndar(andar),
    reduzirMovimento: () => movimentoReduzido.matches,
    temaEscuro: () => html.classList.contains('tema-escuro'),
    estreito: () => palco.clientWidth <= 760,        // celular: painéis viram "folhas" embaixo
    corCss: (nome) => getComputedStyle(palco).getPropertyValue(nome).trim(),
    avisarGiro: (ligado) => {
        const botao = palco.querySelector('[data-acao="girar"]');
        if (botao) botao.setAttribute('aria-pressed', String(ligado));
    },
};

let mapa2d = null;   // desenho 2D (sempre existe)
let mapa3d = null;   // desenho 3D (só existe enquanto o modo 3D está aberto)
const desenhoAtivo = () => (estado.modo === '3d' ? mapa3d : mapa2d);

/* ----------------------- seleção de sala ----------------------- */
function selecionarSala(sala) {
    estado.sala = sala || null;
    // sala de outro andar: troca a aba sozinha (inclusive saindo do "Os dois" no 3D)
    if (sala && estado.andar !== sala.andar) mudarAndar(sala.andar, { semCamera: true });
    atualizarLista();
    atualizarCartao();
    const desenho = desenhoAtivo();
    if (desenho) desenho.mostrarSala(estado.sala);
    if (sala) {
        esconderDica();
    }
    $('mapa-lista').classList.remove('aberta');
    $('mapa-abrir-lista').setAttribute('aria-expanded', 'false');
    atualizarEndereco();
}

// Deixa a URL como mapa.php?sala=<id>, para dar pra copiar e mandar o link
function atualizarEndereco() {
    if (!window.history || !history.replaceState) return;
    const url = new URL(window.location.href);
    if (estado.sala) url.searchParams.set('sala', estado.sala.id);
    else url.searchParams.delete('sala');
    const idOrigem = estado.origem === 'entrada' ? 'entrada' : (estado.origem ? estado.origem.id : '');
    if (idOrigem) url.searchParams.set('de', idOrigem);
    else url.searchParams.delete('de');
    history.replaceState(null, '', url);
}

/* ----------------------- origem da rota ("Estou aqui") ----------------------- */
const seletorOrigem = $('mapa-origem');
function definirOrigem(valor) {
    estado.origem = !valor ? null : (valor === 'entrada' ? 'entrada' : (porId[valor] || null));
    if (seletorOrigem) seletorOrigem.value = estado.origem === 'entrada' ? 'entrada' : (estado.origem ? estado.origem.id : '');
    const desenho = desenhoAtivo();
    if (desenho) desenho.mostrarSala(estado.sala, { semCamera: true });
    if (estado.sala) atualizarCartao();
    atualizarEndereco();
}
seletorOrigem?.addEventListener('change', () => definirOrigem(seletorOrigem.value));

$('mapa-estou-aqui')?.addEventListener('click', () => {
    if (!estado.sala) return;
    const aqui = estado.sala;
    definirOrigem(aqui.id);
    selecionarSala(null);
    avisar(`Você está em ${aqui.nome}. Agora escolha na lista para onde quer ir.`);
    $('mapa-lista').classList.add('aberta');
    $('mapa-abrir-lista').setAttribute('aria-expanded', 'true');
    $('mapa-busca').focus();
});

/* ----------------------- andar ----------------------- */
function mudarAndar(andar, opcoes = {}) {
    if (andar === 'todos' && estado.modo !== '3d') andar = 'terreo';
    estado.andar = andar;
    palco.querySelectorAll('[data-andar]').forEach((botao) => {
        botao.setAttribute('aria-pressed', String(botao.dataset.andar === andar));
    });
    atualizarBotaoTrecho();
    const desenho = desenhoAtivo();
    if (desenho) desenho.definirAndar(andar, opcoes);
}
palco.querySelectorAll('[data-andar]').forEach((botao) => {
    botao.addEventListener('click', () => mudarAndar(botao.dataset.andar));
});

/* ----------------------- lista de setores ----------------------- */
const itensLista = Array.from(palco.querySelectorAll('.mapa-lista-item'));
itensLista.forEach((item) => {
    item.addEventListener('click', () => selecionarSala(porId[item.dataset.sala]));
});

function atualizarLista() {
    itensLista.forEach((item) => {
        const ativo = !!estado.sala && item.dataset.sala === estado.sala.id;
        item.classList.toggle('ativo', ativo);
        item.setAttribute('aria-pressed', String(ativo));
    });
}

// Busca sem acento e sem diferenciar maiúscula ("napne" acha "NAPNE", "pratica" acha "prática")
const normalizar = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
$('mapa-busca').addEventListener('input', (evento) => {
    const termo = normalizar(evento.target.value.trim());
    let encontrados = 0;
    palco.querySelectorAll('.mapa-lista-grupo').forEach((grupo) => {
        let noGrupo = 0;
        grupo.querySelectorAll('.mapa-lista-item').forEach((item) => {
            const sala = porId[item.dataset.sala];
            const combina = !termo || normalizar(sala.nome + ' ' + sala.nomeCurto).includes(termo);
            item.hidden = !combina;
            if (combina) noGrupo++;
        });
        grupo.hidden = noGrupo === 0;
        encontrados += noGrupo;
    });
    $('mapa-lista-vazia').hidden = encontrados > 0;
});

// No celular a lista fica escondida embaixo e abre por este botão
$('mapa-abrir-lista').addEventListener('click', () => {
    const aberta = $('mapa-lista').classList.toggle('aberta');
    $('mapa-abrir-lista').setAttribute('aria-expanded', String(aberta));
    if (aberta) $('mapa-busca').focus();
});

/* ----------------------- cartão da sala ----------------------- */
const cartao = $('mapa-info');

function atualizarCartao() {
    const s = estado.sala;
    cartao.classList.toggle('aberto', !!s);
    cartao.setAttribute('aria-hidden', String(!s));
    if (!s) return;

    $('mapa-info-andar').textContent = nomesAndar[s.andar] || '';
    $('mapa-info-nome').textContent = s.nome;
    $('mapa-info-chips').innerHTML =
        `<span class="mapa-chip">${s.sgn < 0 ? 'Ala esquerda' : 'Ala direita'}</span>` +
        `<span class="mapa-chip">${usaEscada(s) ? 'Via escada' : 'Sem escada'}</span>` +
        `<span class="mapa-chip destaque">≈ ${metros(s)} m · ${Math.max(1, Math.round(segundosAPe(s) / 60))} min a pé</span>`;
    $('mapa-info-passos').innerHTML = passos(s).map((texto) => `<li>${texto}</li>`).join('');
    // I3: destino só por escada (sem elevador informado) -> avisa e sugere térreo/NAPNE
    const avisoEscada = $('mapa-info-escada');
    if (avisoEscada) avisoEscada.hidden = !(usaEscada(s) && !(dados.semEscada || {})[s.id]);

    // botão para a página do setor: só aparece se o PHP confirmou que a página existe
    const link = $('mapa-info-setor');
    // Sala com setor: página do setor. Sala sem setor: ficha simples da sala (sala.php).
    const destino = s.url_setor || s.url_ficha;
    link.hidden = !destino;
    if (destino) link.href = destino;
    const textoLink = $('mapa-info-setor-texto');
    if (textoLink) textoLink.textContent = s.url_setor ? 'Ver página do setor' : 'Ver detalhes da sala';
    // "Estou aqui": só aparece se esta sala ainda não é o ponto de partida
    $('mapa-estou-aqui').hidden = !!(estado.origem && estado.origem.id === s.id);
    atualizarBotaoTrecho();
}

// No 2D cada andar é uma planta separada; este botão alterna para ver o outro trecho da rota
function atualizarBotaoTrecho() {
    const botao = $('mapa-info-trecho');
    const mostrar = estado.modo === '2d' && !!estado.sala && usaEscada(estado.sala);
    botao.hidden = !mostrar;
    if (mostrar) botao.textContent = estado.andar === 'andar1' ? 'Ver o trecho no térreo' : 'Ver o trecho no 1º andar';
}
$('mapa-info-trecho').addEventListener('click', () => mudarAndar(estado.andar === 'andar1' ? 'terreo' : 'andar1'));
$('mapa-info-fechar').addEventListener('click', () => selecionarSala(null));

/* ----------------------- ferramentas ----------------------- */
palco.querySelectorAll('[data-acao]').forEach((botao) => {
    botao.addEventListener('click', () => {
        const desenho = desenhoAtivo();
        const acao = botao.dataset.acao;
        if (desenho && typeof desenho[acao] === 'function') desenho[acao]();
    });
});

/* ----------------------- dica e avisos ----------------------- */
let timerDica = 0;
function mostrarDica() {
    const dica = $('mapa-dica');
    dica.textContent = estado.modo === '3d'
        ? 'arraste para girar · role para zoom · clique numa sala'
        : 'arraste para mover · role para zoom · clique numa sala';
    dica.classList.remove('some');
    clearTimeout(timerDica);
    timerDica = setTimeout(esconderDica, 8000);
}
function esconderDica() { $('mapa-dica').classList.add('some'); }

let timerAviso = 0;
function avisar(texto) {
    const aviso = $('mapa-aviso');
    aviso.textContent = texto;
    aviso.classList.add('visivel');
    clearTimeout(timerAviso);
    timerAviso = setTimeout(() => aviso.classList.remove('visivel'), 7000);
}

/* =====================================================================
   TROCA 2D <-> 3D (o 3D só é baixado no primeiro clique)
   ===================================================================== */
const botoesModo = document.querySelectorAll('button[data-modo]'); // só os botões (o palco também tem data-modo)
const area3d = $('mapa3d');
const carregando = $('mapa-carregando');
let pedidoModo = 0; // evita confusão se a pessoa clicar 3D e 2D rápido enquanto carrega

function temWebGL() {
    try {
        const canvas = document.createElement('canvas');
        return !!(window.WebGLRenderingContext && (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
    } catch (erro) {
        return false;
    }
}

// Carrega um <script> e espera ele terminar. Guarda a promessa para não baixar duas vezes.
const scriptsCarregados = new Map();
function carregarScript(src) {
    if (!scriptsCarregados.has(src)) {
        scriptsCarregados.set(src, new Promise((resolver, rejeitar) => {
            const tag = document.createElement('script');
            tag.src = src;
            tag.async = false;
            tag.onload = resolver;
            tag.onerror = () => {
                scriptsCarregados.delete(src); // deixa tentar de novo depois
                tag.remove();
                rejeitar(new Error('Falha ao carregar ' + src));
            };
            document.head.appendChild(tag);
        }));
    }
    return scriptsCarregados.get(src);
}
// Um de cada vez: o OrbitControls precisa do three.min.js já carregado
async function carregarScripts(lista) {
    for (const src of lista) await carregarScript(src);
}
function esperar(ms, mensagem) {
    return new Promise((_, rejeitar) => setTimeout(() => rejeitar(new Error(mensagem)), ms));
}

function marcarModo(modo) {
    palco.dataset.modo = modo;
    const voltar = $('mapa-voltar-2d');
    if (voltar) voltar.hidden = modo !== '3d';
    botoesModo.forEach((botao) => botao.setAttribute('aria-pressed', String(botao.dataset.modo === modo)));
    // botões que só fazem sentido num dos modos (zoom no 2D, girar no 3D, "Os dois" no 3D)
    palco.querySelectorAll('[data-so]').forEach((botao) => { botao.hidden = botao.dataset.so !== modo; });
    palco.querySelector('[data-andar="todos"]').hidden = modo !== '3d';
}

async function mudarModo(modo) {
    if (modo === estado.modo) return;
    const pedido = ++pedidoModo;

    if (modo === '3d') {
        if (!temWebGL()) {
            avisar('Seu navegador não conseguiu abrir o 3D (sem WebGL). Continue pela planta 2D — ela tem as mesmas rotas.');
            return;
        }
        marcarModo('3d');
        area3d.hidden = false;
        carregando.hidden = false;
        try {
            await Promise.race([carregarScripts(dados.scripts3d || []), esperar(20000, 'Tempo esgotado ao baixar o 3D')]);
            if (pedido !== pedidoModo) return; // a pessoa já voltou para o 2D
            if (!window.Mapa3D || !window.THREE) throw new Error('Scripts do 3D não ficaram disponíveis');
            mapa3d = window.Mapa3D.iniciar(area3d, api);
        } catch (erro) {
            console.error('Mapa 3D:', erro);
            if (mapa3d) { mapa3d.destruir(); mapa3d = null; }
            area3d.hidden = true;
            carregando.hidden = true;
            if (pedido === pedidoModo) {
                marcarModo('2d');
                avisar('Não deu para abrir o mapa 3D agora (conexão ou navegador). Voltamos para a planta 2D.');
            }
            return;
        }
        carregando.hidden = true;
        estado.modo = '3d';
        if (mapa2d) mapa2d.pausar(); // o 2D fica escondido e para de animar
        mapa3d.definirAndar(estado.andar, { semCamera: true });
        mapa3d.mostrarSala(estado.sala);
    } else {
        // Sair do 3D: destrói tudo (para a animação e libera a memória da placa de vídeo)
        if (passeio) { passeio.cancelado = true; passeio = null; painelPasseio.hidden = true; }
        if (mapa3d) { mapa3d.destruir(); mapa3d = null; }
        area3d.hidden = true;
        carregando.hidden = true;
        estado.modo = '2d';
        marcarModo('2d');
        if (estado.andar === 'todos') estado.andar = estado.sala ? estado.sala.andar : 'terreo';
        mudarAndar(estado.andar);
        mapa2d.ativar();
        mapa2d.mostrarSala(estado.sala);
    }
    atualizarBotaoTrecho();
    api.avisarGiro(false);
    mostrarDica();
}
botoesModo.forEach((botao) => botao.addEventListener('click', () => mudarModo(botao.dataset.modo)));

// Acessibilidade (I2): sair do 3D pelo teclado (Esc) ou pelo botão que aparece em cima da maquete
$('mapa-voltar-2d')?.addEventListener('click', () => {
    mudarModo('2d');
    palco.querySelector('button[data-modo="2d"]')?.focus();
});
palco.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape' && estado.modo === '3d') {
        mudarModo('2d');
        palco.querySelector('button[data-modo="2d"]')?.focus();
    }
});

/* =====================================================================
   PASSEIO PELO 3D (W1): a câmera percorre o prédio com legendas.
   As salas citadas vêm dos dados do mapa (mudou no editor, muda aqui).
   mapa.php?passeio=1 abre o 3D e já começa (bom pra divulgação).
   ===================================================================== */
const painelPasseio = $('mapa-passeio');
let passeio = null; // { etapa, pausado, cancelado }

function etapasDoPasseio(alturaAndar1) {
    const nomes = (andar, ala) => salas
        .filter((s) => s.andar === andar && s.sgn === (ala === 'esquerda' ? -1 : 1) && !s.end && !s.vazia)
        .sort((a, b) => a.cz * a.sgn - b.cz * b.sgn)
        .map((s) => s.nomeCurto || s.nome);
    const ponta = (andar, ala) => salas.find((s) => s.andar === andar && s.end && s.sgn === (ala === 'esquerda' ? -1 : 1));
    const lista = (itens) => (itens.length > 1 ? `${itens.slice(0, -1).join(', ')} e ${itens[itens.length - 1]}` : itens.join(''));
    const etapas = [
        { pos: [-34, 9, 14], alvo: [PORTAO_X, 1.5, 0], texto: 'Este é o portão do campus. Daqui, o caminho segue reto até a entrada do prédio.' },
        { pos: [-7, 4.5, 7], alvo: [0, 1.5, 0], texto: 'A entrada fica bem no meio do prédio.' },
        { pos: [1, 6, 6], alvo: [(S0 + S1) / 2, 2, 0], texto: 'Seguindo reto, a escada no meio do prédio leva ao 1º andar.' },
    ];
    const esquerda = nomes('terreo', 'esquerda');
    if (esquerda.length) etapas.push({ pos: [XC, 10, -4], alvo: [XC, 0.5, -ALA.terreo * 0.6], texto: `Ala esquerda do térreo: ${lista(esquerda)}.` });
    const pontaEsquerda = ponta('terreo', 'esquerda');
    if (pontaEsquerda) etapas.push({ pos: [XC + 14, 9, -ALA.terreo - 2], alvo: [pontaEsquerda.cx, 1, pontaEsquerda.cz], texto: `No fim da ala esquerda: ${pontaEsquerda.nome}.` });
    const direita = nomes('terreo', 'direita');
    if (direita.length) etapas.push({ pos: [XC, 10, 4], alvo: [XC, 0.5, ALA.terreo * 0.6], texto: `Ala direita do térreo: ${lista(direita)}.` });
    const pontaDireita = ponta('terreo', 'direita');
    if (pontaDireita) etapas.push({ pos: [XC + 14, 9, ALA.terreo + 2], alvo: [pontaDireita.cx, 1, pontaDireita.cz], texto: `No fim da ala direita: ${pontaDireita.nome}.` });
    const andar1 = [...nomes('andar1', 'esquerda'), ...nomes('andar1', 'direita')];
    if (andar1.length) etapas.push({ pos: [-10, alturaAndar1 + 22, 18], alvo: [XC, alturaAndar1, 0], texto: `No 1º andar ficam ${lista(andar1.slice(0, 6))}${andar1.length > 6 ? ' e outras salas' : ''}.` });
    etapas.push({ fim: true, texto: 'Fim do passeio. Escolha uma sala na lista para ver o caminho até ela.' });
    return etapas;
}

function mostrarLegenda(texto, numero, total) {
    $('mapa-passeio-texto').textContent = texto;
    $('mapa-passeio-contador').textContent = numero && total ? `${numero} de ${total}` : '';
}

async function iniciarPasseio() {
    if (estado.modo !== '3d') {
        await mudarModo('3d');
        if (estado.modo !== '3d' || !mapa3d) return; // sem WebGL: o mudarModo já avisou
    }
    if (estado.sala) selecionarSala(null);
    mudarAndar('todos', { semCamera: true });
    const etapas = etapasDoPasseio(mapa3d.alturaDoAndar1 || 6.5);
    const meu = { etapa: 0, pausado: false, cancelado: false };
    passeio = meu;
    painelPasseio.hidden = false;
    $('mapa-passeio-pausar').textContent = 'Pausar';
    $('mapa-passeio-pausar').focus();

    while (meu.etapa < etapas.length && !meu.cancelado && mapa3d) {
        const etapa = etapas[meu.etapa];
        mostrarLegenda(etapa.texto, meu.etapa + 1, etapas.length);
        if (etapa.fim) {
            mapa3d.visaoGeral();
            await esperarTempo(4000, meu);
            break;
        }
        await mapa3d.voar(etapa.pos, etapa.alvo, 2400);
        await esperarTempo(3200, meu);
        while (meu.pausado && !meu.cancelado) await esperarTempo(300, meu);
        if (!meu.pulou) meu.etapa += 1;
        meu.pulou = false;
    }
    if (passeio === meu) encerrarPasseio();
}

// Espera, mas para na hora se a pessoa pular ou sair.
function esperarTempo(ms, meu) {
    return new Promise((resolver) => {
        const inicio = Date.now();
        const checar = () => {
            if (meu.cancelado || meu.pulou || Date.now() - inicio >= ms) resolver();
            else setTimeout(checar, 100);
        };
        checar();
    });
}

function encerrarPasseio() {
    if (passeio) passeio.cancelado = true;
    passeio = null;
    painelPasseio.hidden = true;
    if (mapa3d) mapa3d.visaoGeral();
    $('mapa-passeio-iniciar')?.focus();
}

$('mapa-passeio-iniciar')?.addEventListener('click', iniciarPasseio);
$('mapa-passeio-sair')?.addEventListener('click', encerrarPasseio);
$('mapa-passeio-pular')?.addEventListener('click', () => {
    if (!passeio) return;
    passeio.etapa += 1;
    passeio.pulou = true;
    passeio.pausado = false;
    $('mapa-passeio-pausar').textContent = 'Pausar';
});
$('mapa-passeio-pausar')?.addEventListener('click', () => {
    if (!passeio) return;
    passeio.pausado = !passeio.pausado;
    $('mapa-passeio-pausar').textContent = passeio.pausado ? 'Continuar' : 'Pausar';
});

/* =====================================================================
   INÍCIO
   ===================================================================== */
if (!window.Mapa2D) {
    console.error('js/mapa2d.js não foi carregado.');
    return;
}
mapa2d = window.Mapa2D.iniciar($('mapa2d'), api);
marcarModo('2d');
mudarAndar('terreo');
mostrarDica();

// mapa.php?de=<id>&sala=<id>: já abre com a origem e o destino escolhidos
if (dados.origemInicial) definirOrigem(dados.origemInicial);
// mapa.php?passeio=1: abre o 3D e já começa o passeio (link de divulgação)
if (new URLSearchParams(window.location.search).get('passeio') === '1') iniciarPasseio();
if (dados.salaInicial && porId[dados.salaInicial]) selecionarSala(porId[dados.salaInicial]);

// Atalho para testar pelo console do navegador: GuiaMapa.selecionar(GuiaMapa.porId.dape)
window.GuiaMapa = api;
})();
