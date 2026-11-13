/* =====================================================================
   MAPA DO CAMPUS — planta 2D, maquete 3D, rotas e EDITOR LIVRE (v3)
   ---------------------------------------------------------------------
   Um único arquivo usado em três lugares:
     - mapa.php e index.php (site) . só encontrar (visitante), com "Saindo de"
     - admin/editor-mapa.php ....... editor + botão "Salvar no site"
     - Mapa do Campus (arquivo avulso) editor + baixar/abrir arquivo
   A página coloca <div id="mapa-campus"> e <script type="application/json"
   id="mapa-campus-dados"> com { dados, editavel, salvarUrl, csrf, setores,
   scripts3d, salaInicial, origemInicial, semEscada, velocidade, idioma,
   chaveLocal, chaveAntiga, avulso }. No site, os dados vêm de model/MapaCampus.php.

   "SAINDO DE" (de onde a rota começa): portão principal, entrada de um prédio,
   qualquer sala (de qualquer andar) ou outro lugar. A rota vira uma lista de
   trechos, um por andar ({ andar, pts }). ?sala= e ?de= no endereço abrem já
   com o destino e a origem (links "Ver no mapa", QR da porta, "Estou aqui").

   MODELO (versão 3) — medidas em metros, x = leste, z = sul:
     prédio  { x, z, rot, andares, partes:[{x,z,w,d}], portas:[{x,z}], escada:{x,z,w,d}, salas:[...] }
             partes, portas, escada e salas usam coordenadas do próprio prédio
             (a partir do centro x,z, antes do giro "rot").
     sala    { id, n, andar, x, z, w, d, porta:{lado:'N'|'S'|'L'|'O', t:0..1}, cat, cor?, setor }
     caminho { pontos:[[x,z]...], larg, curva }   (coordenadas do mapa)
     áreas   { x, z, w, d, rot }
   A ROTA é calculada numa grade de 0,5 m (A*): anda por calçadas, pátios e
   corredores, entra pelas portas e nunca atravessa parede ou sala.
   Mapas antigos (versão 1 e 2, com corredor automático) são convertidos sozinhos.
   ===================================================================== */
(function () {
'use strict';

function iniciarMapaCampus(root, op) {
const ES = op.idioma === 'es';
const L = (pt, es) => (ES ? es : pt);
const $ = (sel) => root.querySelector(sel);
const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
const uid = () => 'i' + Date.now().toString(36) + Math.floor(Math.random() * 1e6).toString(36);
const clone = (o) => JSON.parse(JSON.stringify(o));
const rad = (d) => (d * Math.PI) / 180;
const r1 = (v) => Math.round(v * 10) / 10;
const r2 = (v) => Math.round(v * 100) / 100;
const fin = (v, pad) => (isFinite(+v) && v !== null && v !== '' ? +v : pad);
// sem animação: pedido do aparelho ou a opção "Menos movimento" do site (html.reduzir-movimento)
const reduzMov = () => document.documentElement.classList.contains('reduzir-movimento') || !!(window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches);
function rng(seed) { let a = 0; for (const c of String(seed)) a = (a * 31 + c.charCodeAt(0)) | 0; return () => { a = (a + 0x6D2B79F5) | 0; let t = Math.imul(a ^ (a >>> 15), 1 | a); t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t; return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }

const ANDAR_H = 4, MAX_ANDARES = 4;
const ANDARES = ES ? ['Planta baja', '1.º piso', '2.º piso', '3.º piso'] : ['Térreo', '1º andar', '2º andar', '3º andar'];
const TIPOS = {
  predio: [L('Prédio', 'Edificio'), 'var(--cm-slab)'], patio: [L('Pátio coberto', 'Patio cubierto'), 'var(--cm-roof)'], estac: [L('Estacionamento', 'Estacionamiento'), 'var(--cm-park)'],
  campo: [L('Campo', 'Cancha'), 'var(--cm-field)'], quadra: [L('Quadra', 'Cancha'), 'var(--cm-sand)'], mata: [L('Mata', 'Bosque'), 'var(--cm-woods)'],
  verde: [L('Área verde', 'Área verde'), 'var(--cm-grass-2)'], obra: [L('Obra', 'Obra'), 'var(--cm-work)'], arvore: [L('Árvore', 'Árbol'), 'var(--cm-tree)'],
  caminho: [L('Caminho / calçada', 'Camino / acera'), 'var(--cm-path)'], ponto: [L('Ponto (cantina, bebedouro…)', 'Punto (cantina, bebedero…)'), 'var(--cm-accent)'], rotulo: [L('Texto no mapa', 'Texto en el mapa'), 'var(--cm-fg)'],
};
const CATS = {
  aula: [L('Sala de aula', 'Aula'), 'var(--cm-cat-aula)'], lab: [L('Laboratório', 'Laboratorio'), 'var(--cm-cat-lab)'], banheiro: [L('Banheiro', 'Baño'), 'var(--cm-cat-banheiro)'],
  adm: [L('Administrativo', 'Administrativo'), 'var(--cm-cat-adm)'], atend: [L('Atendimento', 'Atención'), 'var(--cm-cat-atend)'], grande: [L('Auditório / Biblioteca', 'Auditorio / Biblioteca'), 'var(--cm-cat-grande)'],
  apoio: [L('Apoio / serviços', 'Apoyo / servicios'), 'var(--cm-cat-apoio)'], outro: [L('Outro', 'Otro'), 'var(--cm-cat-outro)'],
};
const ICONES = ['📍', '🍽️', '🚻', '🚰', '🅿️', '🚌', '⚽', 'ℹ️', '⭐', '🏫', '🔬', '📚', '💻', '🧪', '🩺', '🚪'];
const LADOS = { N: L('norte', 'norte'), S: L('sul', 'sur'), L: L('leste', 'este'), O: L('oeste', 'oeste') };
function adivinhaCat(n) {
  const s = String(n || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
  if (/banheir|wc\b|sanit/.test(s)) return 'banheiro';
  if (/lab/.test(s)) return 'lab';
  if (/auditor|bibliotec/.test(s)) return 'grande';
  if (/sala de aula|^sala \d|^sala [a-z]?\d|turma/.test(s)) return 'aula';
  if (/coord|diret|direc|^cg|depex|dplad|csg|cpalm|cgp|reuni|gabinete|docente|professor/.test(s)) return 'adm';
  if (/cra\b|atendiment|caed|napne|enferm|secretar|protocolo|nai\b/.test(s)) return 'atend';
  if (/copa|cantina|deposit|almox|manuten|ideias|cozinha|refeit|elevador|apoio|bebedouro/.test(s)) return 'apoio';
  return 'outro';
}
const LIM0 = { x0: -104, x1: 150, z0: -122, z1: 78 };
const setores = op.setores || [];
const setorPorId = Object.fromEntries(setores.map((s) => [s.id, s]));
const editavel = !!op.editavel;
const CHAVE = op.chaveLocal || 'guiaifro-mapa-campus-rascunho';

/* =====================================================================
   GEOMETRIA BÁSICA
   ===================================================================== */
function rotP(b, x, z) { const a = rad(b.rot || 0); if (!a) return { x, z }; const dx = x - b.x, dz = z - b.z, c = Math.cos(a), s = Math.sin(a); return { x: b.x + dx * c - dz * s, z: b.z + dx * s + dz * c }; }
function unrotP(b, x, z) { const a = -rad(b.rot || 0); if (!a) return { x, z }; const dx = x - b.x, dz = z - b.z, c = Math.cos(a), s = Math.sin(a); return { x: b.x + dx * c - dz * s, z: b.z + dx * s + dz * c }; }
function rotV(b, x, z) { const a = rad(b.rot || 0); if (!a) return { x, z }; const c = Math.cos(a), s = Math.sin(a); return { x: x * c - z * s, z: x * s + z * c }; }
const Wl = (b, lx, lz) => rotP(b, b.x + lx, b.z + lz);                       // local -> mapa
const locP = (b, x, z) => { const p = unrotP(b, x, z); return { x: p.x - b.x, z: p.z - b.z }; }; // mapa -> local
function ext(b) {
  if (b.tipo === 'predio' && b.partes && b.partes.length) {
    let x0 = Infinity, x1 = -Infinity, z0 = Infinity, z1 = -Infinity;
    b.partes.forEach((p) => { x0 = Math.min(x0, p.x - p.w / 2); x1 = Math.max(x1, p.x + p.w / 2); z0 = Math.min(z0, p.z - p.d / 2); z1 = Math.max(z1, p.z + p.d / 2); });
    return { x0, x1, z0, z1 };
  }
  if (b.tipo === 'caminho' && b.pontos) { const xs = b.pontos.map((p) => p[0] - b.x), zs = b.pontos.map((p) => p[1] - b.z); return { x0: Math.min(...xs), x1: Math.max(...xs), z0: Math.min(...zs), z1: Math.max(...zs) }; }
  return { x0: -b.w / 2, x1: b.w / 2, z0: -b.d / 2, z1: b.d / 2 };
}
function cantos(b) { const e = ext(b); return [[e.x0, e.z0], [e.x1, e.z0], [e.x1, e.z1], [e.x0, e.z1]].map(([x, z]) => { const p = Wl(b, x, z); return [p.x, p.z]; }); }
function bbox(b) {
  if (b.tipo === 'caminho') { const xs = b.pontos.map((p) => p[0]), zs = b.pontos.map((p) => p[1]), r = (b.larg || 4) / 2; return { x0: Math.min(...xs) - r, x1: Math.max(...xs) + r, z0: Math.min(...zs) - r, z1: Math.max(...zs) + r }; }
  const c = cantos(b), xs = c.map((p) => p[0]), zs = c.map((p) => p[1]); return { x0: Math.min(...xs), x1: Math.max(...xs), z0: Math.min(...zs), z1: Math.max(...zs) };
}
function syncPredio(b) { const e = ext(b); b.w = r2(e.x1 - e.x0); b.d = r2(e.z1 - e.z0); }
function syncCaminho(b) { const xs = b.pontos.map((p) => p[0]), zs = b.pontos.map((p) => p[1]); b.x = r2((Math.min(...xs) + Math.max(...xs)) / 2); b.z = r2((Math.min(...zs) + Math.max(...zs)) / 2); b.w = r2(Math.max(1, Math.max(...xs) - Math.min(...xs))); b.d = r2(Math.max(1, Math.max(...zs) - Math.min(...zs))); }
const dentroPe = (b, x, z, f) => b.partes.some((p) => Math.abs(x - p.x) <= p.w / 2 + (f || 0) && Math.abs(z - p.z) <= p.d / 2 + (f || 0));
const dentroPeW = (b, x, z) => { const p = locP(b, x, z); return dentroPe(b, p.x, p.z); };
// ponto da borda de fora do prédio mais perto de (x,z) — para portas
function borda(b, x, z) {
  let melhor = null, dist = Infinity;
  b.partes.forEach((p) => {
    const x0 = p.x - p.w / 2, x1 = p.x + p.w / 2, z0 = p.z - p.d / 2, z1 = p.z + p.d / 2;
    [[x0, x1, z0, 'h', 0, -1], [x0, x1, z1, 'h', 0, 1], [z0, z1, x0, 'v', -1, 0], [z0, z1, x1, 'v', 1, 0]].forEach(([a0, a1, c, k, nx, nz]) => {
      const q = k === 'h' ? { x: clamp(x, a0 + .6, a1 - .6), z: c } : { x: c, z: clamp(z, a0 + .6, a1 - .6) };
      if (dentroPe(b, q.x + nx * .3, q.z + nz * .3)) return;
      const d = Math.hypot(q.x - x, q.z - z); if (d < dist) { dist = d; melhor = { x: q.x, z: q.z, nx, nz }; }
    });
  });
  return melhor || { x, z, nx: 0, nz: 1 };
}
const retS = (r) => ({ x0: r.x - r.w / 2, x1: r.x + r.w / 2, z0: r.z - r.d / 2, z1: r.z + r.d / 2 });
const emRet = (r, x, z, f) => Math.abs(x - r.x) <= r.w / 2 + (f || 0) && Math.abs(z - r.z) <= r.d / 2 + (f || 0);
function portaSala(r) {
  const q = retS(r), t = clamp(r.porta ? r.porta.t : .5, 0, 1), lado = r.porta ? r.porta.lado : 'S';
  if (lado === 'N') return { x: q.x0 + t * r.w, z: q.z0, nx: 0, nz: -1 };
  if (lado === 'S') return { x: q.x0 + t * r.w, z: q.z1, nx: 0, nz: 1 };
  if (lado === 'O') return { x: q.x0, z: q.z0 + t * r.d, nx: -1, nz: 0 };
  return { x: q.x1, z: q.z0 + t * r.d, nx: 1, nz: 0 };
}
// curva suave pelos pontos (Catmull-Rom -> Bézier)
function bezierSegs(pts) {
  const n = pts.length, P = (i) => pts[clamp(i, 0, n - 1)], out = [];
  for (let i = 0; i < n - 1; i++) {
    const p0 = P(i - 1), p1 = P(i), p2 = P(i + 1), p3 = P(i + 2);
    out.push([p1, [p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6], [p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6], p2]);
  }
  return out;
}
function caminhoD(b) {
  const p = b.pontos; if (!b.curva || p.length < 3) return 'M' + p.map((q) => q.join(' ')).join(' L');
  return 'M' + p[0].join(' ') + bezierSegs(p).map(([, c1, c2, e]) => ` C${c1.join(' ')} ${c2.join(' ')} ${e.join(' ')}`).join('');
}
function amostraCaminho(b, passo) {
  const out = [], p = b.pontos;
  if (!b.curva || p.length < 3) { for (let i = 0; i < p.length - 1; i++) { const L2 = Math.hypot(p[i + 1][0] - p[i][0], p[i + 1][1] - p[i][1]), n = Math.max(1, Math.ceil(L2 / passo)); for (let k = 0; k <= n; k++) out.push([p[i][0] + (p[i + 1][0] - p[i][0]) * k / n, p[i][1] + (p[i + 1][1] - p[i][1]) * k / n]); } return out; }
  bezierSegs(p).forEach(([a, c1, c2, e]) => {
    const L2 = Math.hypot(e[0] - a[0], e[1] - a[1]) * 1.3, n = Math.max(2, Math.ceil(L2 / passo));
    for (let k = 0; k <= n; k++) { const t = k / n, u = 1 - t; out.push([u * u * u * a[0] + 3 * u * u * t * c1[0] + 3 * u * t * t * c2[0] + t * t * t * e[0], u * u * u * a[1] + 3 * u * u * t * c1[1] + 3 * u * t * t * c2[1] + t * t * t * e[1]]); }
  });
  return out;
}
const hexPts = (w, d) => { const hw = w / 2, hd = d / 2, k = Math.min(hd, hw / 2); return [[-hw, 0], [-hw + k, -hd], [hw - k, -hd], [hw, 0], [hw - k, hd], [-hw + k, hd]]; };

/* =====================================================================
   CONVERSÃO DOS MAPAS ANTIGOS (corredor automático) PARA O MODELO LIVRE
   ===================================================================== */
const Velho = (() => {
  const cwOf = (b) => clamp(+b.cw || 3, 1, 10);
  function pecas(b) {
    const hw = b.w / 2, hd = b.d / 2;
    if (b.forma === 'H') { const ww = Math.min(b.alaLarg || 16, b.w / 3), bw = Math.min(b.barraLarg || 14, b.d / 2);
      return [{ id: 'oeste', eixo: 'z', a0: b.z - hd, a1: b.z + hd, c: b.x - hw + ww / 2, meia: ww / 2, ini: b.z - hd, sent: 1 }, { id: 'leste', eixo: 'z', a0: b.z - hd, a1: b.z + hd, c: b.x + hw - ww / 2, meia: ww / 2, ini: b.z - hd, sent: 1 }, { id: 'centro', eixo: 'x', a0: b.x - hw + ww, a1: b.x + hw - ww, c: b.z, meia: bw / 2, ini: b.x - hw + ww, sent: 1 }]; }
    if (b.forma === 'hall') { const hh = Math.min(b.hall || 12, b.w / 3) / 2;
      return [{ id: 'esq', eixo: 'x', a0: b.x - hw, a1: b.x - hh, c: b.z, meia: hd, ini: b.x - hh, sent: -1 }, { id: 'dir', eixo: 'x', a0: b.x + hh, a1: b.x + hw, c: b.z, meia: hd, ini: b.x + hh, sent: 1 }]; }
    let p; if (b.w >= b.d) p = b.porta === 'L' ? { ini: b.x + hw, sent: -1 } : { ini: b.x - hw, sent: 1 }; else p = b.porta === 'N' ? { ini: b.z - hd, sent: 1 } : { ini: b.z + hd, sent: -1 };
    return [Object.assign(b.w >= b.d ? { eixo: 'x', a0: b.x - hw, a1: b.x + hw, c: b.z, meia: hd } : { eixo: 'z', a0: b.z - hd, a1: b.z + hd, c: b.x, meia: hw }, { id: 'centro' }, p)];
  }
  const XZ = (p, al, ac) => (p.eixo === 'x' ? { x: al, z: ac } : { x: ac, z: al });
  function layout(b, andar) {
    const out = [], CW = cwOf(b);
    pecas(b).forEach((p) => {
      const grupo = b.salas.filter((r) => r.andar === andar && r.peca === p.id); if (!grupo.length) return;
      let cA = 0, cB = 0;
      const pos = grupo.map((r) => { const t = Math.max(.25, r.tam || 1); let u0; if (r.lado === 'AB') { u0 = Math.max(cA, cB); cA = cB = u0 + t; } else if (r.lado === 'B') { u0 = cB; cB += t; } else { u0 = cA; cA += t; } return { r, u0, u1: u0 + t }; });
      const unit = Math.abs(p.a1 - p.a0) / Math.max(cA, cB, 1), sA = p.eixo === 'x' ? 1 : -1;
      pos.forEach(({ r, u0, u1 }) => {
        const al0 = p.ini + p.sent * u0 * unit, al1 = p.ini + p.sent * u1 * unit, mid = (al0 + al1) / 2; let c0, c1, door;
        if (r.lado === 'AB') { c0 = p.c - p.meia; c1 = p.c + p.meia; door = XZ(p, al0, p.c); } else { const s = r.lado === 'A' ? sA : -sA; c0 = p.c + s * CW / 2; c1 = p.c + s * p.meia; door = XZ(p, mid, p.c + s * CW / 2); }
        const A = XZ(p, Math.min(al0, al1), Math.min(c0, c1)), B = XZ(p, Math.max(al0, al1), Math.max(c0, c1));
        out.push({ r, x0: A.x, x1: B.x, z0: A.z, z1: B.z, cx: (A.x + B.x) / 2, cz: (A.z + B.z) / 2, door });
      });
    });
    return out;
  }
  function partes(b) {
    if (b.forma === 'H') { const ww = Math.min(b.alaLarg || 16, b.w / 3), bw = Math.min(b.barraLarg || 14, b.d / 2), hw = b.w / 2, hd = b.d / 2;
      return [[b.x - hw, b.x - hw + ww, b.z - hd, b.z + hd], [b.x + hw - ww, b.x + hw, b.z - hd, b.z + hd], [b.x - hw + ww, b.x + hw - ww, b.z - bw / 2, b.z + bw / 2]]; }
    return [[b.x - b.w / 2, b.x + b.w / 2, b.z - b.d / 2, b.z + b.d / 2]];
  }
  function entrada(b) {
    const hw = b.w / 2, hd = b.d / 2, p = b.porta || 'S';
    if (b.forma === 'H') { const bw = Math.min(b.barraLarg || 14, b.d / 2) / 2; if (p === 'N') return { x: b.x, z: b.z - bw }; if (p === 'L') return { x: b.x + hw, z: b.z }; if (p === 'O') return { x: b.x - hw, z: b.z }; return { x: b.x, z: b.z + bw }; }
    if (p === 'N') return { x: b.x, z: b.z - hd }; if (p === 'L') return { x: b.x + hw, z: b.z }; if (p === 'O') return { x: b.x - hw, z: b.z }; return { x: b.x, z: b.z + hd };
  }
  function migrar(o) {
    const preds = o.blocos.filter((b) => b.tipo === 'predio');
    preds.forEach((b) => {
      b.forma = ['barra', 'hall', 'H'].includes(b.forma) ? b.forma : 'barra'; b.w = +b.w || 20; b.d = +b.d || 10; b.rot = +b.rot || 0; b.andares = clamp(parseInt(b.andares, 10) || 1, 1, MAX_ANDARES);
      const ids = pecas(b).map((p) => p.id);
      b.salas = (Array.isArray(b.salas) ? b.salas : []).filter((r) => r && typeof r === 'object');
      b.salas.forEach((r) => { if (!ids.includes(r.peca)) r.peca = ids[0]; if (!['A', 'B', 'AB'].includes(r.lado)) r.lado = 'A'; r.andar = clamp(parseInt(r.andar, 10) || 0, 0, b.andares - 1); r.tam = +r.tam > 0 ? +r.tam : 1; });
    });
    const P = preds.find((b) => b.principal) || preds[0], G = o.portao;
    // calçadas que antes eram desenhadas sozinhas viram caminhos editáveis
    const novos = [], vistos = new Set();
    if (P) {
      const dentro = (x, z) => preds.some((b) => { const q = unrotP(b, x, z); return partes(b).some(([x0, x1, z0, z1]) => q.x > x0 + .3 && q.x < x1 - .3 && q.z > z0 + .3 && q.z < z1 - .3); });
      const PE = entrada(P), Pw = rotP(P, PE.x, PE.z), yC = Math.max(...partes(P).map(([x0, x1, z0, z1]) => [[x0, z0], [x1, z1], [x0, z1], [x1, z0]]).flat().map(([x, z]) => rotP(P, x, z).z)) + 5;
      preds.forEach((b) => {
        const e = entrada(b), E = rotP(b, e.x, e.z), pts = [[G.x, G.z], [G.x, yC]];
        if (b === P || E.z >= yC - 5.5) pts.push([E.x, yC], [E.x, E.z]);
        else if (P.forma === 'hall') { const fundo = rotP(P, P.x, P.z - P.d / 2); pts.push([Pw.x, yC], [Pw.x, Pw.z], [P.x, P.z], [fundo.x, fundo.z], [fundo.x, E.z + 4], [E.x, E.z + 4], [E.x, E.z]); }
        else pts.push([E.x, yC], [E.x, E.z]);
        let run = [pts[0]];
        const flush = () => { if (run.length > 1) { const k = run.map((q) => q.map(Math.round).join(',')).join(';'); if (!vistos.has(k)) { vistos.add(k); novos.push({ id: uid(), nome: 'Calçada', tipo: 'caminho', pontos: run.map((q) => [r1(q[0]), r1(q[1])]), larg: 4, curva: false }); } } run = []; };
        for (let i = 1; i < pts.length; i++) { const m = [(pts[i - 1][0] + pts[i][0]) / 2, (pts[i - 1][1] + pts[i][1]) / 2]; if (dentro(m[0], m[1])) { flush(); run = [pts[i]]; } else { if (!run.length) run = [pts[i - 1]]; run.push(pts[i]); } }
        flush();
      });
    }
    o.blocos.forEach((b) => {
      if (b.tipo === 'caminho' && !Array.isArray(b.pontos)) {
        const w = +b.w || 20, d = +b.d || 4, h = w >= d, a = (h ? w : d) / 2, bb = { x: +b.x || 0, z: +b.z || 0, rot: +b.rot || 0 };
        const p1 = rotP(bb, h ? bb.x - a : bb.x, h ? bb.z : bb.z - a), p2 = rotP(bb, h ? bb.x + a : bb.x, h ? bb.z : bb.z + a);
        b.pontos = [[r1(p1.x), r1(p1.z)], [r1(p2.x), r1(p2.z)]]; b.larg = Math.min(w, d); b.curva = false; b.rot = 0;
      }
      if (b.tipo !== 'predio' || Array.isArray(b.partes)) return;
      const salas = [];
      for (let a = 0; a < b.andares; a++) layout(b, a).forEach((Lr) => {
        if (Lr.r.gap) return;
        const w = Lr.x1 - Lr.x0, d = Lr.z1 - Lr.z0, dz = Lr.door; let lado = 'S', t = .5;
        if (Math.abs(dz.z - Lr.z0) < .05) { lado = 'N'; t = (dz.x - Lr.x0) / w; } else if (Math.abs(dz.z - Lr.z1) < .05) { lado = 'S'; t = (dz.x - Lr.x0) / w; } else if (Math.abs(dz.x - Lr.x0) < .05) { lado = 'O'; t = (dz.z - Lr.z0) / d; } else { lado = 'L'; t = (dz.z - Lr.z0) / d; }
        const r = { id: Lr.r.id, n: Lr.r.n, andar: a, x: r2(Lr.cx - b.x), z: r2(Lr.cz - b.z), w: r2(w), d: r2(d), porta: { lado, t: clamp(r2(t), .1, .9) }, cat: Lr.r.cat, setor: Lr.r.setor == null ? null : Lr.r.setor };
        if (Lr.r.cor) r.cor = Lr.r.cor; salas.push(r);
      });
      const e = entrada(b);
      b.partes = partes(b).map(([x0, x1, z0, z1]) => ({ x: r2((x0 + x1) / 2 - b.x), z: r2((z0 + z1) / 2 - b.z), w: r2(x1 - x0), d: r2(z1 - z0) }));
      b.portas = [{ x: r2(e.x - b.x), z: r2(e.z - b.z) }];
      if (b === P && b.forma === 'hall') b.portas.push({ x: 0, z: r2(-b.d / 2) });
      b.escada = b.forma === 'hall' ? { x: 0, z: -3, w: 4, d: 8 } : { x: 0, z: 0, w: 3, d: 6 };
      b.salas = salas;
      ['forma', 'hall', 'alaLarg', 'barraLarg', 'porta', 'cw', 'principal'].forEach((k) => delete b[k]);
    });
    o.blocos = o.blocos.concat(novos);
  }
  return { migrar };
})();

/* ---------- dados ---------- */
function valid(o) { return !!(o && Array.isArray(o.blocos) && o.portao && typeof o.portao === 'object'); }
function normalizar(o) {
  if (!(+o.versao >= 3)) Velho.migrar(o);
  o.versao = 3;
  const l = o.limite || {};
  o.limite = ['x0', 'x1', 'z0', 'z1'].every((k) => isFinite(+l[k])) && +l.x1 > +l.x0 + 10 && +l.z1 > +l.z0 + 10 ? { x0: +l.x0, x1: +l.x1, z0: +l.z0, z1: +l.z1 } : { ...LIM0 };
  o.portao = { x: fin(o.portao.x, 0), z: fin(o.portao.z, o.limite.z1 + 1) };
  o.norte = clamp(fin(o.norte, 0), -180, 180);
  delete o.fundo;
  const vistos = new Set(), vistasSalas = new Set();
  o.blocos = o.blocos.filter((b) => b && b.id != null && !vistos.has(String(b.id)) && vistos.add(String(b.id))).map((b) => {
    b.id = String(b.id);
    if (!TIPOS[b.tipo]) b.tipo = 'verde';
    b.x = fin(b.x, 0); b.z = fin(b.z, 0); b.rot = fin(b.rot, 0); b.w = +b.w > 0 ? +b.w : 10; b.d = +b.d > 0 ? +b.d : 10;
    b.nome = b.nome == null ? '' : String(b.nome);
    if (b.cor && !/^#[0-9a-f]{6}$/i.test(b.cor)) delete b.cor;
    if (b.tipo === 'predio') {
      b.andares = clamp(parseInt(b.andares, 10) || 1, 1, MAX_ANDARES);
      b.partes = (Array.isArray(b.partes) ? b.partes : []).filter((p) => p && +p.w > .4 && +p.d > .4).map((p) => ({ x: fin(p.x, 0), z: fin(p.z, 0), w: +p.w, d: +p.d }));
      if (!b.partes.length) b.partes = [{ x: 0, z: 0, w: b.w, d: b.d }];
      b.portas = (Array.isArray(b.portas) ? b.portas : []).filter((p) => p && isFinite(+p.x) && isFinite(+p.z)).map((p) => ({ x: +p.x, z: +p.z }));
      if (!b.portas.length) { const e = ext(b); const q = borda(b, 0, e.z1); b.portas = [{ x: q.x, z: q.z }]; }
      const es = b.escada || {}; b.escada = { x: fin(es.x, 0), z: fin(es.z, 0), w: +es.w > .5 ? +es.w : 3, d: +es.d > .5 ? +es.d : 6 };
      b.salas = (Array.isArray(b.salas) ? b.salas : []).filter((r) => r && typeof r === 'object').map((r) => {
        r.id = r.id != null && !vistasSalas.has(String(r.id)) ? String(r.id) : uid(); vistasSalas.add(r.id);
        r.n = r.n == null ? '' : String(r.n);
        r.andar = clamp(parseInt(r.andar, 10) || 0, 0, b.andares - 1);
        r.x = fin(r.x, 0); r.z = fin(r.z, 0); r.w = +r.w > .3 ? +r.w : 6; r.d = +r.d > .3 ? +r.d : 6;
        const pt = r.porta || {}; r.porta = { lado: ['N', 'S', 'L', 'O'].includes(pt.lado) ? pt.lado : 'S', t: clamp(fin(pt.t, .5), 0, 1) };
        if (!('setor' in r)) r.setor = null;
        if (!CATS[r.cat]) r.cat = adivinhaCat(r.n);
        if (r.cor && !/^#[0-9a-f]{6}$/i.test(r.cor)) delete r.cor;
        ['peca', 'lado', 'tam', 'gap'].forEach((k) => delete r[k]);
        return r;
      });
      syncPredio(b);
    } else { b.andares = 1; b.salas = []; }
    if (b.tipo === 'caminho') {
      b.pontos = (Array.isArray(b.pontos) ? b.pontos : []).filter((p) => Array.isArray(p) && isFinite(+p[0]) && isFinite(+p[1])).map((p) => [+p[0], +p[1]]);
      if (b.pontos.length < 2) b.pontos = [[b.x - 10, b.z], [b.x + 10, b.z]];
      b.larg = clamp(fin(b.larg, 4), .5, 30); b.curva = !!b.curva; b.rot = 0; syncCaminho(b);
    }
    if (b.tipo === 'ponto') { if (!b.icone) b.icone = '📍'; b.andar = clamp(parseInt(b.andar, 10) || 0, 0, MAX_ANDARES - 1); }
    return b;
  });
  return o;
}
const ORIG = { v: JSON.stringify(normalizar(clone(op.dados))) };
let S = JSON.parse(ORIG.v);
let FUNDO = null;      // foto de fundo (só no editor): {src, x, z, w, h, asp, rot, op, ver}
if (op.dados && op.dados.fundo && op.dados.fundo.src) FUNDO = op.dados.fundo;
if (editavel) {
  try {
    const r = localStorage.getItem(CHAVE) || (op.chaveAntiga ? localStorage.getItem(op.chaveAntiga) : null);
    if (r) { const o = JSON.parse(r); if (valid(o)) S = normalizar(o); }
  } catch (e) {}
  try { const f = localStorage.getItem(CHAVE + '-foto'); if (f) FUNDO = JSON.parse(f); } catch (e) {}
}
let sujo = false, VER = 1;
const recalcSujo = () => { sujo = editavel && JSON.stringify(S) !== ORIG.v; };
function save() {
  VER++;
  if (!editavel) return;
  recalcSujo();
  try { localStorage.setItem(CHAVE, JSON.stringify(S)); } catch (e) {}
  marcarSujo();
}
function saveFoto() {
  if (!editavel) return;
  try { if (FUNDO) localStorage.setItem(CHAVE + '-foto', JSON.stringify(FUNDO)); else localStorage.removeItem(CHAVE + '-foto'); }
  catch (e) { toast('A foto é grande demais para ficar guardada. Ela some ao fechar a página.'); }
}
let hist = [], futuro = [];
const snapH = () => { hist.push(JSON.stringify(S)); if (hist.length > 150) hist.shift(); futuro = []; };
const blocoById = (id) => S.blocos.find((b) => b.id === id);
let ima = true, grade = false;
const snapV = (v) => (ima ? Math.round(v * 2) / 2 : r1(v));

/* =====================================================================
   ROTAS: grade de 0,5 m + A*
   ===================================================================== */
const RES = .5;
const nav = { ver: -1, chao: {}, andares: {}, rotas: {} };
function navAtual() { if (nav.ver !== VER) { nav.ver = VER; nav.chao = {}; nav.andares = {}; nav.rotas = {}; } return nav; }
function novaGrade(x0, z0, x1, z1) { const nx = Math.max(1, Math.ceil((x1 - x0) / RES)), nz = Math.max(1, Math.ceil((z1 - z0) / RES)); return { x0, z0, nx, nz, c: new Float32Array(nx * nz) }; }
function pinta(g, bb, fn) {
  const i0 = clamp(Math.floor((bb.x0 - g.x0) / RES), 0, g.nx - 1), i1 = clamp(Math.ceil((bb.x1 - g.x0) / RES), 0, g.nx - 1);
  const j0 = clamp(Math.floor((bb.z0 - g.z0) / RES), 0, g.nz - 1), j1 = clamp(Math.ceil((bb.z1 - g.z0) / RES), 0, g.nz - 1);
  for (let j = j0; j <= j1; j++) for (let i = i0; i <= i1; i++) { const v = fn(g.x0 + (i + .5) * RES, g.z0 + (j + .5) * RES); if (v !== undefined) g.c[j * g.nx + i] = v; }
}
// Custo de andar em cada lugar (calçada = 1). Grama custa bem mais, então a rota
// prefere seguir as calçadas e caminhos, como uma pessoa faria.
const GRAMA = 3.2;
const CUSTO = { estac: 1.8, campo: 4.5, quadra: 4.5, mata: 9, obra: 14, verde: GRAMA, patio: 1.1 };
// flex = sala no caminho vira passagem cara (só quando não existe corredor livre)
function gradeChao(flex) {
  const n = navAtual(), kk = flex ? 'f' : 's'; if (n.chao[kk]) return n.chao[kk];
  const SALA = flex ? 25 : 0;
  const l = S.limite, G = S.portao, g = novaGrade(Math.min(l.x0, G.x) - 3, Math.min(l.z0, G.z) - 3, Math.max(l.x1, G.x) + 3, Math.max(l.z1, G.z) + 3);
  pinta(g, l, () => GRAMA);
  pinta(g, { x0: G.x - 5, x1: G.x + 5, z0: Math.min(G.z, l.z1, l.z0 > G.z ? G.z : l.z1) - 2, z1: Math.max(G.z, l.z1) + 2 }, () => 1);
  if (G.z < l.z0 || G.x < l.x0 || G.x > l.x1) pinta(g, { x0: G.x - 3, x1: G.x + 3, z0: G.z - 3, z1: G.z + 3 }, () => 1);
  S.blocos.forEach((b) => {
    if (!CUSTO[b.tipo]) return; const e = ext(b);
    pinta(g, bbox(b), (x, z) => { const p = locP(b, x, z); return p.x >= e.x0 && p.x <= e.x1 && p.z >= e.z0 && p.z <= e.z1 ? CUSTO[b.tipo] : undefined; });
  });
  S.blocos.filter((b) => b.tipo === 'caminho').forEach((b) => {
    const r = b.larg / 2 + .1;
    amostraCaminho(b, .4).forEach(([x, z]) => pinta(g, { x0: x - r, x1: x + r, z0: z - r, z1: z + r }, (cx, cz) => ((cx - x) ** 2 + (cz - z) ** 2 <= r * r ? 1 : undefined)));
  });
  S.blocos.filter((b) => b.tipo === 'predio').forEach((b) => {
    const bb = bbox(b), salas = b.salas.filter((r) => r.andar === 0), portasSalas = salas.map(portaSala);
    pinta(g, { x0: bb.x0 - 1, x1: bb.x1 + 1, z0: bb.z0 - 1, z1: bb.z1 + 1 }, (x, z) => {
      const p = locP(b, x, z); if (!dentroPe(b, p.x, p.z)) return undefined;
      const k = .8, parede = !dentroPe(b, p.x + k, p.z) || !dentroPe(b, p.x - k, p.z) || !dentroPe(b, p.x, p.z + k) || !dentroPe(b, p.x, p.z - k) || !dentroPe(b, p.x + k, p.z + k) || !dentroPe(b, p.x - k, p.z - k) || !dentroPe(b, p.x + k, p.z - k) || !dentroPe(b, p.x - k, p.z + k);
      if (parede && !b.portas.some((q) => Math.hypot(q.x - p.x, q.z - p.z) < 1.6) && !portasSalas.some((q) => Math.hypot(q.x - p.x, q.z - p.z) < 1.1)) return 0;
      if (salas.some((r) => emRet(r, p.x, p.z))) return parede ? 0 : SALA;
      return 1.05;
    });
  });
  n.chao[kk] = g; return g;
}
function gradeAndar(b, k, flex) {
  const n = navAtual(), key = b.id + ':' + k + (flex ? 'f' : ''); if (n.andares[key]) return n.andares[key];
  const bb = bbox(b), g = novaGrade(bb.x0 - 2, bb.z0 - 2, bb.x1 + 2, bb.z1 + 2), salas = b.salas.filter((r) => r.andar === k);
  pinta(g, bb, (x, z) => { const p = locP(b, x, z); if (!dentroPe(b, p.x, p.z)) return undefined; return salas.some((r) => emRet(r, p.x, p.z)) ? (flex ? 25 : 0) : 1.05; });
  n.andares[key] = g; return g;
}
function celula(g, x, z) { return [clamp(Math.floor((x - g.x0) / RES), 0, g.nx - 1), clamp(Math.floor((z - g.z0) / RES), 0, g.nz - 1)]; }
function livre(g, i, j) {
  if (g.c[j * g.nx + i] > 0) return [i, j];
  for (let r = 1; r <= 16; r++) { let melhor = null, d = Infinity;
    for (let dj = -r; dj <= r; dj++) for (let di = -r; di <= r; di++) { if (Math.max(Math.abs(di), Math.abs(dj)) !== r) continue; const a = i + di, b2 = j + dj; if (a < 0 || b2 < 0 || a >= g.nx || b2 >= g.nz) continue; if (g.c[b2 * g.nx + a] > 0) { const dd = di * di + dj * dj; if (dd < d) { d = dd; melhor = [a, b2]; } } }
    if (melhor) return melhor; }
  return null;
}
function aestrela(g, s, t) {
  const a = celula(g, s.x, s.z), z = celula(g, t.x, t.z), A = livre(g, a[0], a[1]), B = livre(g, z[0], z[1]);
  if (!A || !B) return null;
  const nx = g.nx, N = nx * g.nz, gs = new Float32Array(N).fill(Infinity), de = new Int32Array(N).fill(-1), fech = new Uint8Array(N);
  const ini = A[1] * nx + A[0], fim = B[1] * nx + B[0], bi = B[0], bj = B[1];
  const hk = [], hv = [];
  const push = (v, k) => { hk.push(k); hv.push(v); let i = hk.length - 1; while (i > 0) { const p = (i - 1) >> 1; if (hk[p] <= hk[i]) break; [hk[p], hk[i]] = [hk[i], hk[p]]; [hv[p], hv[i]] = [hv[i], hv[p]]; i = p; } };
  const pop = () => { const v = hv[0], lk = hk.pop(), lv = hv.pop(); if (hk.length) { hk[0] = lk; hv[0] = lv; let i = 0; for (;;) { const l2 = 2 * i + 1, r = l2 + 1; let m = i; if (l2 < hk.length && hk[l2] < hk[m]) m = l2; if (r < hk.length && hk[r] < hk[m]) m = r; if (m === i) break; [hk[m], hk[i]] = [hk[i], hk[m]]; [hv[m], hv[i]] = [hv[i], hv[m]]; i = m; } } return v; };
  gs[ini] = 0; push(ini, 0);
  const D = [[1, 0, 1], [-1, 0, 1], [0, 1, 1], [0, -1, 1], [1, 1, 1.4142], [1, -1, 1.4142], [-1, 1, 1.4142], [-1, -1, 1.4142]];
  let achou = ini === fim;
  while (hk.length) {
    const u = pop(); if (u === fim) { achou = true; break; } if (fech[u]) continue; fech[u] = 1;
    const ui = u % nx, uj = (u / nx) | 0, cu = g.c[u];
    for (const [di, dj, k] of D) {
      const vi = ui + di, vj = uj + dj; if (vi < 0 || vj < 0 || vi >= nx || vj >= g.nz) continue;
      const v = vj * nx + vi, cv = g.c[v]; if (!cv || fech[v]) continue;
      if (di && dj && (!g.c[uj * nx + vi] || !g.c[vj * nx + ui])) continue;
      const ng = gs[u] + k * RES * (cu + cv) / 2;
      if (ng < gs[v]) { gs[v] = ng; de[v] = u; push(v, ng + Math.hypot(vi - bi, vj - bj) * RES); }
    }
  }
  if (!achou) return null;
  const cel = []; for (let u = fim; u !== -1; u = de[u]) { cel.push(u); if (u === ini) break; }
  cel.reverse();
  return suaviza(g, cel.map((u) => [g.x0 + ((u % nx) + .5) * RES, g.z0 + (((u / nx) | 0) + .5) * RES]));
}
function custoEm(g, x, z) { const [i, j] = celula(g, x, z); return g.c[j * g.nx + i]; }
function linhaOk(g, a, b2, max) {
  const L2 = Math.hypot(b2[0] - a[0], b2[1] - a[1]), n = Math.max(1, Math.ceil(L2 / (RES / 4)));
  for (let k = 0; k <= n; k++) { const c = custoEm(g, a[0] + (b2[0] - a[0]) * k / n, a[1] + (b2[1] - a[1]) * k / n); if (!c || c > max) return false; }
  return true;
}
function suaviza(g, pts) {
  if (pts.length < 3) return pts;
  const out = [pts[0]]; let i = 0;
  while (i < pts.length - 1) {
    let j = i + 1, maxc = custoEm(g, pts[i][0], pts[i][1]), falhas = 0;
    for (let k = i + 1; k < pts.length; k++) {
      maxc = Math.max(maxc, custoEm(g, pts[k][0], pts[k][1]));
      if (linhaOk(g, pts[i], pts[k], maxc + .01)) { j = k; falhas = 0; } else if (++falhas > 8) break;
    }
    out.push(pts[j]); i = j;
  }
  return out;
}
const comp = (pts) => (pts || []).reduce((s, p, i) => (i ? s + Math.hypot(p[0] - pts[i - 1][0], p[1] - pts[i - 1][1]) : 0), 0);
function limpa(pts) { return pts.filter((p, i) => !i || Math.hypot(p[0] - pts[i - 1][0], p[1] - pts[i - 1][1]) > .05); }
function inicioRota() { const G = S.portao, l = S.limite; return { x: G.x, z: G.z > l.z1 ? l.z1 - .5 : G.z < l.z0 ? l.z0 + .5 : G.z }; }
/* ---------- de onde sai ("Saindo de") e para onde vai ----------
   Um "lugar" é { b, r } (sala), { b } (prédio, ponto, área, caminho) ou null (portão principal).
   A rota é uma lista de TRECHOS, um por andar: { andar, pts }. Ex.: sair de uma sala do 1º andar,
   descer a escada, atravessar o campus e subir em outro prédio = 3 trechos (1º andar, térreo, 1º andar). */
// em que andar/prédio o lugar fica e por onde a rota entra (ou sai) dele
function ponta(lugar) {
  if (!lugar) { const G = S.portao, I = inicioRota(); return { andar: 0, predio: null, opcoes: [{ p: I, extra: [[G.x, G.z]] }], centro: [G.x, G.z] }; }
  const b = lugar.b;
  if (lugar.r) {
    const r = lugar.r, pd = portaSala(r), wf = Wl(b, pd.x + pd.nx * .7, pd.z + pd.nz * .7), wp = Wl(b, pd.x, pd.z), c = Wl(b, r.x, r.z);
    return { andar: r.andar, predio: b, opcoes: [{ p: wf, extra: [[wp.x, wp.z]] }], centro: [c.x, c.z] };
  }
  if (b.tipo === 'predio') {
    // prédio inteiro: vale qualquer uma das entradas (fica a mais perto)
    const opcoes = b.portas.map((q) => { const e = borda(b, q.x, q.z), wo = Wl(b, e.x + e.nx * 1.2, e.z + e.nz * 1.2), wd = Wl(b, e.x, e.z); return { p: wo, extra: [[wd.x, wd.z]] }; });
    return { andar: 0, predio: null, opcoes, centro: [b.x, b.z] };
  }
  let t = { x: b.x, z: b.z };
  if (b.tipo === 'caminho') { const a = amostraCaminho(b, 1); const m = a[Math.floor(a.length / 2)]; t = { x: m[0], z: m[1] }; }
  const pp = b.tipo === 'ponto' ? predioDoPonto(b) : null;
  if (pp && (b.andar || 0) > 0) return { andar: Math.min(b.andar, pp.andares - 1), predio: pp, opcoes: [{ p: t, extra: [] }], centro: [t.x, t.z] };
  return { andar: 0, predio: null, opcoes: [{ p: t, extra: [] }], centro: [t.x, t.z] };
}
const escadaW = (b) => Wl(b, b.escada.x, b.escada.z);
const soPonto = (p) => [{ p, extra: [] }];
// menor caminho entre várias saídas e várias chegadas possíveis (ex.: as entradas de um prédio)
function melhorCaminho(g, saidas, chegadas) {
  let melhor = null;
  saidas.forEach((o) => chegadas.forEach((d) => {
    const a = aestrela(g, o.p, d.p); if (!a) return;
    const pts = limpa(o.extra.concat(a, d.extra)), c = comp(pts);
    if (!melhor || c < melhor.c) melhor = { c, pts };
  }));
  return melhor && melhor.pts;
}
function calcRota(alvo, origem, flex) {
  const O = ponta(origem), D = ponta(alvo), segs = [];
  // mesmo prédio e mesmo andar de cima: anda só pelo corredor desse andar
  if (O.andar > 0 && O.predio === D.predio && O.andar === D.andar) {
    const pts = melhorCaminho(gradeAndar(O.predio, O.andar, flex), O.opcoes, D.opcoes);
    return pts && { segs: [{ andar: O.andar, pts }] };
  }
  let saida = O.opcoes;
  if (O.andar > 0) {
    // sai de um andar de cima: primeiro até a escada desse prédio
    const we = escadaW(O.predio), pts = melhorCaminho(gradeAndar(O.predio, O.andar, flex), O.opcoes, soPonto(we));
    if (!pts) return null;
    segs.push({ andar: O.andar, pts });
    if (D.andar > 0 && D.predio === O.predio) {
      // outro andar do mesmo prédio: só troca de andar pela escada
      const fim = melhorCaminho(gradeAndar(D.predio, D.andar, flex), soPonto(we), D.opcoes);
      if (!fim) return null;
      segs.push({ andar: D.andar, pts: fim });
      return { segs };
    }
    saida = soPonto(we);
  }
  const terreo = melhorCaminho(gradeChao(flex), saida, D.andar > 0 ? soPonto(escadaW(D.predio)) : D.opcoes);
  if (!terreo) return null;
  segs.push({ andar: 0, pts: terreo });
  if (D.andar > 0) {
    const fim = melhorCaminho(gradeAndar(D.predio, D.andar, flex), soPonto(escadaW(D.predio)), D.opcoes);
    if (!fim) return null;
    segs.push({ andar: D.andar, pts: fim });
  }
  return { segs };
}
const chaveLugar = (l) => (!l ? 'portao' : l.r ? 's:' + l.r.id : 'b:' + l.b.id);
// rota até "alvo". Sem origem informada: no modo visitante usa o "Saindo de"; no editor, o portão.
function rotaPara(alvo, origem) {
  if (origem === undefined) origem = tab === 'find' ? origemAtual() : null;
  const n = navAtual(), key = chaveLugar(origem) + '>' + chaveLugar(alvo);
  if (n.rotas[key]) return n.rotas[key];
  let res;
  if (chaveLugar(origem) === chaveLugar(alvo)) res = { segs: [], mesmoLugar: true };
  else {
    res = calcRota(alvo, origem, false);
    if (!res) { res = calcRota(alvo, origem, true); if (res) res.bloqueada = true; }
    if (!res) res = { segs: [{ andar: 0, pts: [ponta(origem).centro, ponta(alvo).centro] }], bloqueada: true };
  }
  res.andar = andarDoAlvo(alvo);
  res.metros = Math.round(res.segs.reduce((s, t) => s + comp(t.pts), 0) + 8 * Math.max(0, res.segs.length - 1));
  res.andares = [...new Set(res.segs.map((t) => t.andar))];
  res.usaEscada = res.segs.some((t) => t.andar > 0);
  n.rotas[key] = res; return res;
}
/* ---------- passo a passo: só "direita", "esquerda" e "em frente" ---------- */
// ângulo da curva no ponto b (vindo de a, indo para c). Positivo = direita (o mapa tem y para baixo).
function anguloCurva(a, b, c) {
  const x1 = b[0] - a[0], z1 = b[1] - a[1], x2 = c[0] - b[0], z2 = c[1] - b[1];
  return Math.atan2(x1 * z2 - z1 * x2, x1 * x2 + z1 * z2) * 180 / Math.PI;
}
const metros = (d) => (d < 4 ? L('alguns passos', 'unos pasos') : L(`uns ${d < 20 ? Math.round(d) : Math.round(d / 5) * 5} m`, `unos ${d < 20 ? Math.round(d) : Math.round(d / 5) * 5} m`));
const ladoCurva = (ang) => (Math.abs(ang) > 135 ? L('volte (meia-volta)', 'dé media vuelta') : ang > 0 ? L('vire à <b>direita</b>', 'gire a la <b>derecha</b>') : L('vire à <b>esquerda</b>', 'gire a la <b>izquierda</b>'));
// tira os pontinhos que quase não mudam a direção (ruído da grade)
function simplificaRota(pts) {
  const p = limpa(pts.slice()); let mudou = true;
  while (mudou && p.length > 2) {
    mudou = false;
    for (let i = 1; i < p.length - 1; i++) {
      const curto = Math.hypot(p[i][0] - p[i - 1][0], p[i][1] - p[i - 1][1]) < 1.5 || Math.hypot(p[i + 1][0] - p[i][0], p[i + 1][1] - p[i][1]) < 1.5;
      if (Math.abs(anguloCurva(p[i - 1], p[i], p[i + 1])) < 28 || (curto && Math.abs(anguloCurva(p[i - 1], p[i], p[i + 1])) < 60)) { p.splice(i, 1); mudou = true; break; }
    }
  }
  return p;
}
// onde fica um ponto do caminho (para avisar "atravesse o Pátio"); "fora" = prédios de onde sai/para onde vai
function marcoEm(x, z, fora) {
  for (const o of S.blocos) {
    if (fora.includes(o)) continue;
    if (o.tipo === 'predio' && dentroPeW(o, x, z)) return o;
    if (o.tipo === 'patio') { const q = locP(o, x, z); if (Math.abs(q.x) <= o.w / 2 && Math.abs(q.z) <= o.d / 2) return o; }
  }
  return null;
}
// instruções de um trecho: "Siga uns 30 m e vire à direita."
// fora = null: sem avisos de prédio/pátio no caminho (trecho dentro de um prédio)
function instrucoes(pts, fora, fim) {
  const p = simplificaRota(pts), out = []; let dist = 0, marcoAtual = null;
  for (let i = 1; i < p.length; i++) {
    const m = [(p[i - 1][0] + p[i][0]) / 2, (p[i - 1][1] + p[i][1]) / 2], marco = fora ? marcoEm(m[0], m[1], fora) : null;
    if (marco && marco !== marcoAtual) {
      out.push(dist > 3 ? L(`Siga ${metros(dist)} até o <b>${esc(marco.nome)}</b>.`, `Siga ${metros(dist)} hasta <b>${esc(marco.nome)}</b>.`) : '');
      out.push(marco.tipo === 'predio' ? L(`Entre no prédio <b>${esc(marco.nome)}</b> e atravesse até a porta do outro lado.`, `Entre en <b>${esc(marco.nome)}</b> y crúcelo hasta la puerta del otro lado.`) : L(`Atravesse o <b>${esc(marco.nome)}</b>.`, `Cruce <b>${esc(marco.nome)}</b>.`));
      dist = 0;
    }
    marcoAtual = marco;
    dist += Math.hypot(p[i][0] - p[i - 1][0], p[i][1] - p[i - 1][1]);
    if (i < p.length - 1) {
      const ang = anguloCurva(p[i - 1], p[i], p[i + 1]);
      if (Math.abs(ang) >= 35 && !(out.length === 0 && dist < 6 && !marcoAtual)) { out.push(L(`Siga ${metros(dist)} e ${ladoCurva(ang)}.`, `Siga ${metros(dist)} y ${ladoCurva(ang)}.`).replace(/^Siga alguns passos e v/, 'V').replace(/^Siga unos pasos y g/, 'G')); dist = 0; }
    }
  }
  if (fim) out.push(fim(dist));
  return out.filter(Boolean).map((t) => t.charAt(0).toUpperCase() + t.slice(1));
}
// prédio em que um ponto do caminho está (ou null, ao ar livre)
const predioEm = (p) => S.blocos.find((o) => o.tipo === 'predio' && dentroPeW(o, p[0], p[1])) || null;
function passos(alvo, rota, origem) {
  const b = alvo.b, r = alvo.r, nomeAlvo = esc(r ? r.n : b.nome), out = [];
  // 1) de onde sai
  if (!origem) out.push(L('Entre pelo <b>portão principal</b>.', 'Entre por el <b>portón principal</b>.'));
  else if (origem.r) out.push(L(`Saia da sala <b>${esc(origem.r.n)}</b>.`, `Salga de <b>${esc(origem.r.n)}</b>.`));
  else if (origem.b.tipo === 'predio') out.push(L(`Saia pela entrada do prédio <b>${esc(origem.b.nome)}</b>.`, `Salga por la entrada de <b>${esc(origem.b.nome)}</b>.`));
  else out.push(L(`Saindo de <b>${esc(origem.b.nome)}</b>.`, `Saliendo de <b>${esc(origem.b.nome)}</b>.`));
  if (rota.mesmoLugar) { out.push(L('Você já está aqui.', 'Ya está aquí.')); return out; }
  // a sala de destino fica à direita ou à esquerda de quem chega pelo corredor?
  const ladoPorta = (pts) => {
    if (!r || !pts || pts.length < 3) return L('logo ali', 'ahí mismo');
    const a = pts[pts.length - 3], c = pts[pts.length - 2], dx = c[0] - a[0], dz = c[1] - a[1], dl = Math.hypot(dx, dz) || 1;
    const centro = Wl(b, r.x, r.z), vx = centro.x - c[0], vz = centro.z - c[1], vl = Math.hypot(vx, vz) || 1;
    const cruz = (dx * vz - dz * vx) / dl / vl, frente = (dx * vx + dz * vz) / dl / vl;
    return frente > .75 ? L('bem à sua frente', 'justo enfrente') : cruz > 0 ? L('à sua <b>direita</b>', 'a su <b>derecha</b>') : L('à sua <b>esquerda</b>', 'a su <b>izquierda</b>');
  };
  // 2) um bloco de instruções por trecho (andar)
  const segs = rota.segs, ult = segs.length - 1;
  segs.forEach((sg, i) => {
    const antes = i > 0 ? segs[i - 1].andar : null;
    if (antes !== null && antes !== sg.andar) out.push(sg.andar > antes ? L(`Suba a escada até o <b>${ANDARES[sg.andar]}</b>.`, `Suba la escalera hasta <b>${ANDARES[sg.andar]}</b>.`) : L(`Desça a escada até o <b>${ANDARES[sg.andar]}</b>.`, `Baje la escalera hasta <b>${ANDARES[sg.andar]}</b>.`));
    const pts = i === 0 && !origem ? sg.pts.slice(1) : sg.pts; // o 1º ponto é o portão
    const fimDaRota = i === ult, comPorta = r && fimDaRota;
    const fraseFim = (d) => {
      if (!fimDaRota) return L(`Siga ${metros(d)} até a escada.`, `Siga ${metros(d)} hasta la escalera.`);
      if (r) return L(`Siga ${metros(d)} pelo corredor: <b>${nomeAlvo}</b> fica ${ladoPorta(sg.pts)}.`, `Siga ${metros(d)} por el pasillo. <b>${nomeAlvo}</b> queda ${ladoPorta(sg.pts)}.`);
      return L(`Siga ${metros(d)} até <b>${nomeAlvo}</b>.`, `Siga ${metros(d)} hasta <b>${nomeAlvo}</b>.`);
    };
    const corredor = (lista) => out.push(...instrucoes(comPorta ? lista.slice(0, -1) : lista, null, fraseFim));
    if (sg.andar > 0 || pts.length < 2) { corredor(pts); return; }
    // térreo: pode começar dentro de um prédio, atravessar o campus e entrar em outro
    const pIni = predioEm(pts[0]), pFim = predioEm(pts[pts.length - 1]);
    let resto = pts;
    if (pIni) {
      const iFora = pts.findIndex((q) => !dentroPeW(pIni, q[0], q[1]));
      if (iFora < 0) { corredor(pts); return; } // o trecho todo é dentro do mesmo prédio
      out.push(...instrucoes(pts.slice(0, iFora), null, (d) => L(`Siga ${metros(d)} até a saída do prédio <b>${esc(pIni.nome)}</b>.`, `Siga ${metros(d)} hasta la salida de <b>${esc(pIni.nome)}</b>.`)));
      resto = pts.slice(Math.max(0, iFora - 1));
    }
    const fora = [pIni, pFim].filter(Boolean);
    if (pFim && pFim !== pIni) {
      const iDentro = resto.findIndex((q, j) => j > 0 && dentroPeW(pFim, q[0], q[1]));
      if (iDentro > 0) {
        const soEntrar = fimDaRota && !r && b.tipo === 'predio'; // o destino é o próprio prédio
        out.push(...instrucoes(resto.slice(0, iDentro + 1), fora, (d) => L(`Siga ${metros(d)} até a entrada do prédio <b>${esc(pFim.nome)}</b>${soEntrar ? '' : ' e entre'}.`, `Siga ${metros(d)} hasta la entrada de <b>${esc(pFim.nome)}</b>${soEntrar ? '' : ' y entre'}.`)));
        // dentro do prédio: começa na porta (com um ponto meio metro antes, só pra saber a direção de quem entra)
        const a = resto[iDentro - 1], e = resto[iDentro], k = .5 / (Math.hypot(e[0] - a[0], e[1] - a[1]) || 1);
        if (!soEntrar) corredor([[e[0] - (e[0] - a[0]) * k, e[1] - (e[1] - a[1]) * k]].concat(resto.slice(iDentro)));
        return;
      }
    }
    out.push(...instrucoes(comPorta ? resto.slice(0, -1) : resto, fora, fraseFim));
  });
  out.push(L(`Chegou: <b>${nomeAlvo}</b>.`, `Ha llegado: <b>${nomeAlvo}</b>.`));
  return out;
}

/* ---------- estrutura da tela ---------- */
root.classList.add('cm');
const abas = editavel ? `<div class="cm-tabs" role="tablist">
  <button type="button" data-t="edit" class="on">${L('Editar', 'Editar')}</button>
  <button type="button" data-t="find">${op.avulso ? L('Encontrar', 'Buscar') : L('Ver como visitante', 'Ver como visitante')}</button>
  <button type="button" data-t="data">${op.salvarUrl ? 'Salvar' : 'Arquivo'}</button></div>` : '';
root.innerHTML = `
  <aside class="cm-panel">${abas}<div class="cm-body"></div></aside>
  <div class="cm-stage">
    <svg class="cm-svg" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet" role="img" aria-label="${L('Mapa do campus visto de cima', 'Mapa del campus visto desde arriba')}"></svg>
    <div class="cm-three" aria-label="${L('Mapa do campus em 3D', 'Mapa del campus en 3D')}"></div>
    <div class="cm-loading">${L('Carregando o 3D…', 'Cargando el 3D…')}</div>
    <div class="cm-bar">
      <div class="cm-pill cm-floors"></div>
      <div class="cm-right">
        <div class="cm-pill cm-modes"><button type="button" data-m="2d" class="on">${L('Planta 2D', 'Plano 2D')}</button><button type="button" data-m="3d">${L('Ver em 3D', 'Ver en 3D')}</button></div>
        <div class="cm-pill cm-tools">
          <button class="cm-ic" data-z="in" type="button" aria-label="${L('Aproximar', 'Acercar')}"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></button>
          <button class="cm-ic" data-z="out" type="button" aria-label="${L('Afastar', 'Alejar')}"><svg viewBox="0 0 24 24"><path d="M5 12h14"/></svg></button>
          <button class="cm-ic" data-z="home" type="button" aria-label="${L('Ver o campus inteiro', 'Ver todo el campus')}"><svg viewBox="0 0 24 24"><path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/></svg></button>
        </div>
        <div class="cm-pill cm-view3" hidden><button type="button" data-v="dentro" class="on">${L('Por dentro', 'Por dentro')}</button><button type="button" data-v="fora">${L('Por fora', 'Por fuera')}</button></div>
      </div>
    </div>
    ${editavel ? `<div class="cm-pill cm-edbar" hidden>
      <button class="cm-ic" data-ed="undo" type="button" aria-label="Desfazer" title="Desfazer (Ctrl+Z)"><svg viewBox="0 0 24 24"><path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-3"/></svg></button>
      <button class="cm-ic" data-ed="redo" type="button" aria-label="Refazer" title="Refazer (Ctrl+Y)"><svg viewBox="0 0 24 24"><path d="m15 14 5-5-5-5"/><path d="M20 9H9a5 5 0 0 0 0 10h3"/></svg></button>
      <button data-ed="ima" type="button" class="on" title="Encaixar de meio em meio metro e nas bordas das outras salas">Ímã</button>
      <button data-ed="grade" type="button" title="Mostrar grade de 10 m">Grade</button>
    </div>` : ''}
    <div class="cm-north"><span class="cm-seta">↑</span> ${L('NORTE', 'NORTE')}</div>
    <div class="cm-toast" role="status" aria-live="polite"></div>
  </div>`;
const svg = $('.cm-svg'), body = $('.cm-body'), stage = $('.cm-stage');
function toast(m) { const t = $('.cm-toast'); t.textContent = m; t.classList.add('show'); clearTimeout(toast.k); toast.k = setTimeout(() => t.classList.remove('show'), 2800); }

/* ---------- estado ---------- */
let floor = 0, tab = editavel ? 'edit' : 'find', mode = '2d', view3 = 'dentro', sel = null, busca = '', filtroCat = null, ajustarFoto = false, formVarias = null, selPt = null;
// "Saindo de": null = portão principal; senão { t: 'sala', b, id } ou { t: 'bloco', id }
let origem = null;
const maxAndares = () => Math.max(1, ...S.blocos.filter((b) => b.tipo === 'predio').map((b) => b.andares || 1));
function lugarDe(ref) {
  if (!ref) return null;
  if (ref.t === 'sala') { const b = blocoById(ref.b); if (!b) return null; const r = b.salas.find((x) => x.id === ref.id); return r ? { b, r } : null; }
  if (ref.t === 'bloco') { const b = blocoById(ref.id); return b && roteavel(b) ? { b } : null; }
  return null;
}
const alvoAtual = () => lugarDe(sel);
const origemAtual = () => lugarDe(origem);
// acha uma sala (pelo id dela ou pelo setor) ou um lugar pelo id — usado por ?sala= e ?de=
function refPorId(id) {
  if (!id) return null;
  if (id === 'entrada' || id === 'portao') {
    if (id === 'portao') return null;
    const p = blocoById('principal') || S.blocos.find((b) => b.tipo === 'predio');
    return p ? { t: 'bloco', id: p.id } : null;
  }
  for (const b of S.blocos) { const r = b.salas.find((x) => x.id === id); if (r) return { t: 'sala', b: b.id, id: r.id }; }
  for (const b of S.blocos) { const r = b.salas.find((x) => x.setor && x.setor === id); if (r) return { t: 'sala', b: b.id, id: r.id }; }
  const b = blocoById(id); return b && roteavel(b) ? { t: 'bloco', id: b.id } : null;
}
const corSala = (r) => r.cor || (CATS[r.cat] || CATS.outro)[1];
const corBloco = (b) => b.cor || TIPOS[b.tipo][1];
const roteavel = (b) => !['rotulo', 'arvore'].includes(b.tipo);
// prédio onde um ponto (bebedouro, cantina…) está; null = ao ar livre
const predioDoPonto = (b) => S.blocos.find((x) => x.tipo === 'predio' && dentroPeW(x, b.x, b.z)) || null;
// ponto dentro de prédio só aparece no andar dele; ao ar livre aparece sempre
const pontoVisivel = (b) => !predioDoPonto(b) || (b.andar || 0) === floor;
const andarDoAlvo = (alvo) => (alvo.r ? alvo.r.andar : alvo.b.tipo === 'ponto' && predioDoPonto(alvo.b) ? (alvo.b.andar || 0) : 0);
const salaSel = () => { if (!sel || sel.t !== 'sala') return null; const b = blocoById(sel.b); const r = b && b.salas.find((x) => x.id === sel.id); return r ? { b, r } : null; };

/* =====================================================================
   2D (SVG)
   ===================================================================== */
const NS = 'http://www.w3.org/2000/svg';
const el = (n, a, p) => { const e = document.createElementNS(NS, n); for (const k in (a || {})) e.setAttribute(k, a[k]); if (p) p.appendChild(e); return e; };
let vb = { x: -110, y: -130, w: 270, h: 235 };
const tamHandle = () => clamp(vb.w * .007, .45, 5);
const escala = () => { const r = svg.getBoundingClientRect(); return Math.min(r.width / vb.w, r.height / vb.h) || 1; };
function setVB() {
  if (!(vb.w > 0 && vb.h > 0 && isFinite(vb.x) && isFinite(vb.y))) { const r = svg.getBoundingClientRect(); if (!r.width || !r.height) return; vb = fit(); }
  svg.setAttribute('viewBox', `${vb.x} ${vb.y} ${vb.w} ${vb.h}`); svg.classList.toggle('cm-far', escala() < 2.6);
  const h = tamHandle(); svg.querySelectorAll('[data-hr]').forEach((c) => { const k = +c.dataset.hr; if (c.tagName === 'rect') { c.setAttribute('x', +c.dataset.cx - h * k); c.setAttribute('y', +c.dataset.cz - h * k); c.setAttribute('width', h * k * 2); c.setAttribute('height', h * k * 2); } else c.setAttribute('r', h * k); });
}
function fit(box) {
  const r = svg.getBoundingClientRect(), a = (r.width || 1) / (r.height || 1), l = S.limite;
  const { x0, x1, z0, z1 } = box || { x0: l.x0 - 8, x1: l.x1 + 8, z0: l.z0 - 8, z1: l.z1 + 22 };
  let w = x1 - x0, h = z1 - z0; if (w / h < a) w = h * a; else h = w / a;
  return { x: (x0 + x1) / 2 - w / 2, y: (z0 + z1) / 2 - h / 2, w, h };
}
let vbAnim = 0;
function animVB(to) {
  const from = { ...vb }, t0 = performance.now(), id = ++vbAnim, reduz = reduzMov();
  const st = (now) => { if (id !== vbAnim) return; const k = reduz ? 1 : Math.min(1, (now - t0) / 600), e = k < .5 ? 4 * k * k * k : 1 - Math.pow(-2 * k + 2, 3) / 2;
    ['x', 'y', 'w', 'h'].forEach((p) => { vb[p] = from[p] + (to[p] - from[p]) * e; }); setVB(); if (k < 1) requestAnimationFrame(st); };
  requestAnimationFrame(st);
}
function wrap(text, max) { const ws = String(text).split(' '), l = []; let c = ''; ws.forEach((w) => { if (!c) c = w; else if ((c + ' ' + w).length <= max) c += ' ' + w; else { l.push(c); c = w; } }); if (c) l.push(c); return l; }
function texto(parent, str, cx, cy, width, height, cls, maxfs) {
  const fs = Math.max(.3, Math.min(maxfs, width * .14, height * .3)), lh = fs * 1.15, lines = wrap(str, Math.max(4, Math.floor(width / (fs * .62))));
  const t = el('text', { x: cx, y: cy - (lines.length - 1) * lh / 2 + fs * .34, class: cls, style: `font-size:${fs}px` }, parent);
  lines.forEach((ln, i) => { const s = el('tspan', { x: cx, dy: i ? lh : 0 }, t); s.textContent = ln; });
  return t;
}
const deCabeca = (b) => { const a = (((b.rot || 0) % 360) + 360) % 360; return a > 90 && a < 270; };
function desvira(t, b, cx, cz) { if (deCabeca(b)) t.setAttribute('transform', `rotate(180 ${cx} ${cz})` + (t.getAttribute('transform') ? ' ' + t.getAttribute('transform') : '')); }
// grupo do bloco: dentro dele as coordenadas são locais (a partir do centro, sem giro)
const grupoLocal = (b, attrs, pai) => el('g', Object.assign({ transform: `translate(${b.x} ${b.z})` + (b.rot ? ` rotate(${b.rot})` : '') }, attrs), pai);
const ehSel = (b) => sel && ((sel.t === 'bloco' && sel.id === b.id) || (sel.t === 'parte' && sel.b === b.id));

function draw() {
  const seta = $('.cm-north .cm-seta'); if (seta) seta.style.transform = `rotate(${S.norte || 0}deg)`;
  svg.innerHTML = '';
  const l = S.limite, defs = el('defs', {}, svg);
  const sh = el('filter', { id: 'cm-sh', x: '-10%', y: '-10%', width: '120%', height: '130%' }, defs);
  el('feDropShadow', { dx: 0, dy: .8, stdDeviation: 1, 'flood-color': '#10202f', 'flood-opacity': .22 }, sh);
  const ph = el('pattern', { id: 'cm-hatch', width: 3, height: 3, patternUnits: 'userSpaceOnUse', patternTransform: 'rotate(45)' }, defs);
  el('rect', { width: 3, height: 3, fill: 'var(--cm-work)' }, ph); el('rect', { width: 1.2, height: 3, fill: 'var(--cm-fg)', opacity: .3 }, ph);
  el('rect', { x: l.x0 - 800, y: l.z0 - 800, width: l.x1 - l.x0 + 1600, height: l.z1 - l.z0 + 1600, fill: 'var(--cm-grass-2)' }, svg);
  const ruas = el('g', {}, svg), X0 = l.x0 - 800, LW = l.x1 - l.x0 + 1600;
  [[X0, l.z1 + 6, LW, 16], [X0, l.z0 - 12, LW, 10]].forEach(([x, y, w, h]) => el('rect', { x, y, width: w, height: h, fill: 'var(--cm-street)' }, ruas));
  [[l.x0 - 16, l.z0 - 800, 10, l.z1 - l.z0 + 1600], [l.x1 + 8, l.z0 - 800, 10, l.z1 - l.z0 + 1600]].forEach(([x, y, w, h]) => el('rect', { x, y, width: w, height: h, fill: 'var(--cm-street)' }, ruas));
  el('line', { x1: X0, y1: l.z1 + 14, x2: X0 + LW, y2: l.z1 + 14, stroke: 'var(--cm-street-line)', 'stroke-width': .5, 'stroke-dasharray': '4 4' }, ruas);
  el('text', { x: (l.x0 + l.x1) / 2 + 40, y: l.z1 + 16.2, class: 'cm-alb', style: 'font-size:3px' }, ruas).textContent = L('Avenida', 'Avenida');
  el('rect', { x: l.x0, y: l.z0, width: l.x1 - l.x0, height: l.z1 - l.z0, fill: 'var(--cm-grass)' }, svg);
  if (FUNDO && FUNDO.src && FUNDO.ver !== false && editavel) {
    el('image', { href: FUNDO.src, x: FUNDO.x - FUNDO.w / 2, y: FUNDO.z - FUNDO.h / 2, width: FUNDO.w, height: FUNDO.h, preserveAspectRatio: 'none', opacity: FUNDO.op == null ? .6 : FUNDO.op, transform: FUNDO.rot ? `rotate(${FUNDO.rot} ${FUNDO.x} ${FUNDO.z})` : '', 'pointer-events': 'none' }, svg);
  }
  el('rect', { x: l.x0, y: l.z0, width: l.x1 - l.x0, height: l.z1 - l.z0, fill: 'none', stroke: sel && sel.t === 'cerca' ? 'var(--cm-accent)' : 'var(--cm-stroke)', 'stroke-width': sel && sel.t === 'cerca' ? 1 : .6, 'stroke-dasharray': '3 2', 'pointer-events': 'none' }, svg);
  if (grade && tab === 'edit') {
    const g = el('g', { 'pointer-events': 'none', opacity: .35 }, svg);
    for (let x = Math.ceil(l.x0 / 10) * 10; x <= l.x1; x += 10) el('line', { x1: x, y1: l.z0, x2: x, y2: l.z1, stroke: 'var(--cm-stroke)', 'stroke-width': x % 50 ? .15 : .35 }, g);
    for (let z = Math.ceil(l.z0 / 10) * 10; z <= l.z1; z += 10) el('line', { x1: l.x0, y1: z, x2: l.x1, y2: z, stroke: 'var(--cm-stroke)', 'stroke-width': z % 50 ? .15 : .35 }, g);
  }
  const gA = el('g', {}, svg), gP = el('g', {}, svg), gB = el('g', {}, svg), gPt = el('g', {}, svg), gR = el('g', {}, svg), gT = el('g', {}, svg), gH = el('g', {}, svg);
  const tipo = (t) => S.blocos.filter((b) => b.tipo === t);
  S.blocos.filter((b) => !['predio', 'patio', 'caminho', 'ponto', 'rotulo'].includes(b.tipo)).forEach((b) => drawArea(gA, b));
  tipo('caminho').forEach((b) => {
    const gr = el('g', { class: 'cm-blk', 'data-b': b.id }, gP), d = caminhoD(b);
    if (ehSel(b)) el('path', { d, fill: 'none', stroke: 'var(--cm-accent)', 'stroke-width': b.larg + 1.2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round', opacity: .55 }, gr);
    el('path', { d, fill: 'none', stroke: corBloco(b), 'stroke-width': b.larg, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, gr);
  });
  tipo('patio').forEach((b) => drawPatio(gB, b));
  tipo('predio').forEach((b) => drawPredio(gB, gT, b));
  tipo('ponto').filter(pontoVisivel).forEach((b) => drawPonto(gPt, b));
  tipo('rotulo').forEach((b) => {
    const gr = el('g', { class: 'cm-blk', 'data-b': b.id }, gPt), fs = clamp(b.w || 6, 1, 40);
    const t = el('text', { x: b.x, y: b.z + fs * .35, class: 'cm-rotulo', style: `font-size:${fs}px;fill:${b.cor || 'var(--cm-fg)'}`, transform: b.rot ? `rotate(${b.rot} ${b.x} ${b.z})` : '' }, gr);
    t.textContent = b.nome || L('Texto', 'Texto');
    if (ehSel(b)) { const w = Math.max(fs, (b.nome || 'Texto').length * fs * .58); el('rect', { x: b.x - w / 2 - 1, y: b.z - fs * .7, width: w + 2, height: fs * 1.4, fill: 'none', stroke: 'var(--cm-accent)', 'stroke-width': .6, 'stroke-dasharray': '2 1.4', transform: b.rot ? `rotate(${b.rot} ${b.x} ${b.z})` : '' }, gr); }
  });
  const G = S.portao, gg = el('g', { class: 'cm-blk', 'data-portao': '1' }, gT);
  el('rect', { x: G.x - 6, y: G.z - 1, width: 12, height: 2, rx: .6, fill: 'var(--cm-red)' }, gg);
  el('rect', { x: G.x - 7, y: G.z - 2, width: 2, height: 4, rx: .5, fill: 'var(--cm-wall)', stroke: 'var(--cm-stroke)', 'stroke-width': .3 }, gg);
  el('rect', { x: G.x + 5, y: G.z - 2, width: 2, height: 4, rx: .5, fill: 'var(--cm-wall)', stroke: 'var(--cm-stroke)', 'stroke-width': .3 }, gg);
  el('text', { x: G.x, y: G.z + 6.5, class: 'cm-blbl', style: 'font-size:3.4px' }, gg).textContent = L('Portão', 'Portón');
  if (sel && sel.t === 'portao') el('rect', { x: G.x - 8.5, y: G.z - 3.5, width: 17, height: 7, rx: 1.5, fill: 'none', stroke: 'var(--cm-accent)', 'stroke-width': .7, 'stroke-dasharray': '2 1.4' }, gg);
  drawRota(gR);
  if (tab === 'edit') drawHandles(gH);
}
function contornoLocal(gr, e, pad) {
  el('rect', { x: e.x0 - pad, y: e.z0 - pad, width: e.x1 - e.x0 + pad * 2, height: e.z1 - e.z0 + pad * 2, rx: 1.5, fill: 'none', stroke: 'var(--cm-accent)', 'stroke-width': .7, 'stroke-dasharray': '2 1.4', 'pointer-events': 'none' }, gr);
}
function drawArea(g, b) {
  const on = ehSel(b);
  if (b.tipo === 'arvore') {
    const gr = el('g', { class: 'cm-blk', 'data-b': b.id }, g), r = b.w / 2;
    el('circle', { cx: b.x + .8, cy: b.z + 1, r, fill: '#10202f', opacity: .16 }, gr);
    el('circle', { cx: b.x, cy: b.z, r, fill: corBloco(b) }, gr); el('circle', { cx: b.x - r * .3, cy: b.z - r * .3, r: r * .45, fill: '#fff', opacity: .14 }, gr);
    if (on) el('circle', { cx: b.x, cy: b.z, r: r + 1, fill: 'none', stroke: 'var(--cm-accent)', 'stroke-width': .7, 'stroke-dasharray': '2 1.4' }, gr);
    return;
  }
  const gr = grupoLocal(b, { class: 'cm-blk', 'data-b': b.id }, g), x = -b.w / 2, y = -b.d / 2;
  el('rect', { x, y, width: b.w, height: b.d, rx: b.tipo === 'mata' ? Math.min(8, b.w / 4, b.d / 4) : 1.2, fill: b.tipo === 'obra' && !b.cor ? 'url(#cm-hatch)' : corBloco(b), stroke: 'var(--cm-wall)', 'stroke-width': .5 }, gr);
  if (b.tipo === 'estac') for (let i = x + 4; i < x + b.w - 3; i += 3) { el('line', { x1: i, y1: y + 2, x2: i, y2: y + Math.min(7, b.d / 3), stroke: 'var(--cm-wall)', 'stroke-width': .25 }, gr); el('line', { x1: i, y1: y + b.d - Math.min(7, b.d / 3), x2: i, y2: y + b.d - 2, stroke: 'var(--cm-wall)', 'stroke-width': .25 }, gr); }
  else if (b.tipo === 'campo') {
    el('rect', { x: x + 3, y: y + 3, width: Math.max(0, b.w - 6), height: Math.max(0, b.d - 6), fill: 'none', stroke: '#fff', 'stroke-width': .4, opacity: .8 }, gr);
    el('line', { x1: 0, y1: y + 3, x2: 0, y2: y + b.d - 3, stroke: '#fff', 'stroke-width': .4, opacity: .8 }, gr);
    el('circle', { cx: 0, cy: 0, r: Math.min(b.w, b.d) * .12, fill: 'none', stroke: '#fff', 'stroke-width': .4, opacity: .8 }, gr);
  } else if (b.tipo === 'quadra') { const v = b.d > b.w; el('line', v ? { x1: x, y1: 0, x2: x + b.w, y2: 0, stroke: 'var(--cm-wall)' } : { x1: 0, y1: y, x2: 0, y2: y + b.d, stroke: 'var(--cm-wall)' }, gr); }
  else if (b.tipo === 'mata') { const rnd = rng(b.id), n = Math.min(300, Math.round(b.w * b.d / 70)); for (let i = 0; i < n; i++) el('circle', { cx: x + 3 + rnd() * Math.max(0, b.w - 6), cy: y + 3 + rnd() * Math.max(0, b.d - 6), r: 2.2 + rnd() * 2.2, fill: 'var(--cm-tree)', opacity: .55 + rnd() * .4 }, gr); }
  const t = el('text', { x: 0, y: 1.2, class: 'cm-alb', style: `font-size:${clamp(Math.min(b.w, b.d) * .14, 2.2, 4.2)}px` }, gr); t.textContent = b.nome; desvira(t, b, 0, 0);
  if (on) contornoLocal(gr, ext(b), 1);
}
function drawPatio(g, b) {
  const on = ehSel(b), gr = grupoLocal(b, { class: 'cm-blk', 'data-b': b.id }, g), pts = hexPts(b.w, b.d).map((p) => p.join(',')).join(' ');
  el('polygon', { points: pts, fill: corBloco(b), stroke: 'var(--cm-wall)', 'stroke-width': .8, filter: 'url(#cm-sh)', opacity: .92 }, gr);
  el('polygon', { points: pts, fill: 'none', stroke: on ? 'var(--cm-accent)' : 'var(--cm-stroke)', 'stroke-width': on ? 1 : .3, 'stroke-dasharray': on ? '2 1.4' : '' }, gr);
  const t = el('text', { x: 0, y: 1.6, class: 'cm-blbl', style: `font-size:${clamp(Math.min(b.w, b.d) * .16, 2, 4.2)}px` }, gr); t.textContent = b.nome; desvira(t, b, 0, 0);
}
function drawPonto(g, b) {
  const gr = el('g', { class: 'cm-blk', 'data-b': b.id }, g), r = clamp(b.w || 6, 2, 30) / 2;
  el('circle', { cx: b.x, cy: b.z, r, fill: 'var(--cm-surface)', stroke: b.cor || 'var(--cm-accent)', 'stroke-width': r * .18, filter: 'url(#cm-sh)' }, gr);
  el('text', { x: b.x, y: b.z + r * .38, class: 'cm-emoji', style: `font-size:${r * 1.1}px` }, gr).textContent = b.icone || '📍';
  if (b.nome) el('text', { x: b.x, y: b.z + r + 3, class: 'cm-blbl', style: 'font-size:2.6px' }, gr).textContent = b.nome;
  if (ehSel(b)) el('circle', { cx: b.x, cy: b.z, r: r + 1.2, fill: 'none', stroke: 'var(--cm-accent)', 'stroke-width': .6, 'stroke-dasharray': '2 1.4' }, gr);
}
function drawPredio(g, gT, b) {
  const on = ehSel(b), gr = grupoLocal(b, { class: 'cm-blk', 'data-b': b.id }, g), temAndar = floor < (b.andares || 1), ed = tab === 'edit';
  b.partes.forEach((p, i) => el('rect', { x: p.x - p.w / 2, y: p.z - p.d / 2, width: p.w, height: p.d, rx: .4, fill: temAndar ? 'var(--cm-corr)' : (b.cor || 'var(--cm-roof)'), stroke: 'var(--cm-wall)', 'stroke-width': .8, filter: 'url(#cm-sh)', 'data-parte': i }, gr));
  // tampa as linhas internas entre as partes (o prédio parece uma peça só)
  b.partes.forEach((p) => el('rect', { x: p.x - p.w / 2, y: p.z - p.d / 2, width: p.w, height: p.d, fill: temAndar ? 'var(--cm-corr)' : (b.cor || 'var(--cm-roof)'), 'pointer-events': 'none' }, gr));
  if (temAndar) {
    if ((b.andares || 1) > 1) {
      const e = b.escada, gE = el('g', { 'data-escada': b.id, class: ed ? 'cm-escada' : '' }, gr);
      el('rect', { x: e.x - e.w / 2, y: e.z - e.d / 2, width: e.w, height: e.d, fill: 'var(--cm-wall)', stroke: 'var(--cm-stroke)', 'stroke-width': .2 }, gE);
      const horiz = e.w > e.d, n = Math.max(3, Math.round((horiz ? e.w : e.d) / 1));
      for (let i = 1; i < n; i++) { const k = i / n; el('line', horiz ? { x1: e.x - e.w / 2 + e.w * k, y1: e.z - e.d / 2, x2: e.x - e.w / 2 + e.w * k, y2: e.z + e.d / 2, stroke: 'var(--cm-stroke)', 'stroke-width': .15 } : { x1: e.x - e.w / 2, y1: e.z - e.d / 2 + e.d * k, x2: e.x + e.w / 2, y2: e.z - e.d / 2 + e.d * k, stroke: 'var(--cm-stroke)', 'stroke-width': .15 }, gE); }
      const t = el('text', { x: e.x, y: e.z + e.d / 2 + 1.6, class: 'cm-rlbl', style: 'font-size:1.4px' }, gE); t.textContent = L('Escada', 'Escalera'); desvira(t, b, e.x, e.z + e.d / 2 + 1.2);
    }
    b.salas.filter((r) => r.andar === floor).forEach((r) => {
      const ativo = sel && sel.t === 'sala' && sel.id === r.id, q = retS(r), daqui = tab === 'find' && origem && origem.t === 'sala' && origem.id === r.id;
      const rg = el('g', { class: 'cm-room' + (ativo ? ' on' : '') + (daqui ? ' de' : ''), 'data-r': r.id, 'data-rb': b.id }, gr);
      el('rect', { class: 'f', x: q.x0, y: q.z0, width: r.w, height: r.d, fill: corSala(r), stroke: 'var(--cm-wall)', 'stroke-width': .45, rx: .2 }, rg);
      // sala de onde a pessoa está saindo ("Saindo de"): contorno tracejado
      if (daqui) el('rect', { x: q.x0 + .25, y: q.z0 + .25, width: Math.max(.1, r.w - .5), height: Math.max(.1, r.d - .5), fill: 'none', stroke: 'var(--cm-accent)', 'stroke-width': .5, 'stroke-dasharray': '1.2 .8', rx: .2, 'pointer-events': 'none' }, rg);
      const pd = portaSala(r), dw = Math.min(1.6, (pd.nx ? r.d : r.w) * .6);
      el('rect', pd.nx ? { x: pd.x - .3, y: pd.z - dw / 2, width: .6, height: dw, fill: 'var(--cm-accent)' } : { x: pd.x - dw / 2, y: pd.z - .3, width: dw, height: .6, fill: 'var(--cm-accent)' }, rg);
      const vert = r.d > r.w * 1.6, t = texto(rg, r.n || '—', vert ? 0 : r.x, vert ? 0 : r.z, vert ? r.d : r.w, vert ? r.w : r.d, 'cm-rlbl', 2.1);
      if (vert) t.setAttribute('transform', `translate(${r.x} ${r.z}) rotate(-90)`);
      desvira(t, b, r.x, r.z);
    });
  } else b.partes.forEach((p) => { for (let z = p.z - p.d / 2 + 2; z < p.z + p.d / 2; z += 2.5) el('line', { x1: p.x - p.w / 2 + 1, y1: z, x2: p.x + p.w / 2 - 1, y2: z, stroke: 'var(--cm-stroke)', 'stroke-width': .12, opacity: .5, 'pointer-events': 'none' }, gr); });
  b.portas.forEach((q) => { const e = borda(b, q.x, q.z); el('rect', e.nx ? { x: e.x - .5, y: e.z - 1.4, width: 1, height: 2.8, rx: .3, fill: 'var(--cm-red)', 'pointer-events': 'none' } : { x: e.x - 1.4, y: e.z - .5, width: 2.8, height: 1, rx: .3, fill: 'var(--cm-red)', 'pointer-events': 'none' }, gr); });
  if (on) b.partes.forEach((p, i) => el('rect', { x: p.x - p.w / 2 - .8, y: p.z - p.d / 2 - .8, width: p.w + 1.6, height: p.d + 1.6, rx: 1.2, fill: 'none', stroke: 'var(--cm-accent)', 'stroke-width': sel.t === 'parte' && sel.i === i ? 1 : .6, 'stroke-dasharray': sel.t === 'parte' && sel.i === i ? '' : '2 1.4', 'pointer-events': 'none' }, gr));
  const bb = bbox(b);
  el('text', { x: (bb.x0 + bb.x1) / 2, y: bb.z0 - 2.2, class: 'cm-blbl', style: 'font-size:4px' }, gT).textContent = b.nome + ((b.andares || 1) > 1 ? ` · ${b.andares} ${L('andares', 'pisos')}` : '');
}
let anim = 0;
function drawRota(g) {
  cancelAnimationFrame(anim);
  const alvo = alvoAtual(); if (!alvo || tab === 'edit') return;
  const rota = rotaPara(alvo), segs = rota.segs, linhas = [];
  let ring = null;
  const rotulo = (p, txt) => el('text', { x: p[0] + 2.6, y: p[1] + 1, class: 'cm-blbl', style: 'font-size:2.6px;text-anchor:start;fill:var(--cm-accent)' }, g).textContent = txt;
  // só os trechos do andar que está na tela (os outros aparecem trocando o andar)
  segs.forEach((sg, i) => {
    const pts = sg.pts; if (sg.andar !== floor || pts.length < 2) return;
    const d = 'M' + pts.map((p) => p.join(' ')).join(' L');
    el('path', { d, fill: 'none', stroke: '#10202f', 'stroke-width': 2.4, opacity: .18, 'stroke-linecap': 'round', 'stroke-linejoin': 'round', transform: 'translate(0 .6)' }, g);
    el('path', { d, fill: 'none', stroke: 'var(--cm-accent-ink)', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, g);
    const base = el('path', { d, fill: 'none', stroke: 'var(--cm-accent)', 'stroke-width': 1.2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, g);
    const flow = el('path', { d, fill: 'none', stroke: 'var(--cm-accent-ink)', 'stroke-width': .6, 'stroke-dasharray': '.3 2.7', 'stroke-linecap': 'round' }, g);
    const first = pts[0], last = pts[pts.length - 1];
    el('circle', { cx: first[0], cy: first[1], r: i === 0 ? 1.4 : 1, fill: 'var(--cm-accent)', stroke: 'var(--cm-accent-ink)', 'stroke-width': .5 }, g);
    if (i === 0 && origem) rotulo(first, L('Você está aqui', 'Usted está aquí'));
    if (i === segs.length - 1) { ring = el('circle', { cx: last[0], cy: last[1], r: 2, fill: 'none', stroke: 'var(--cm-accent)', 'stroke-width': .5 }, g); el('circle', { cx: last[0], cy: last[1], r: 1.5, fill: 'var(--cm-accent-ink)', stroke: 'var(--cm-accent)', 'stroke-width': .7 }, g); }
    else rotulo(last, segs[i + 1].andar > sg.andar ? L(`↑ suba a escada (${ANDARES[segs[i + 1].andar]})`, `↑ suba la escalera (${ANDARES[segs[i + 1].andar]})`) : L(`↓ desça a escada (${ANDARES[segs[i + 1].andar]})`, `↓ baje la escalera (${ANDARES[segs[i + 1].andar]})`));
    const dot = el('circle', { r: 1.2, fill: 'var(--cm-accent-ink)', stroke: 'var(--cm-accent)', 'stroke-width': .5 }, g);
    linhas.push({ base, flow, dot, len: base.getTotalLength() });
  });
  if (!linhas.length) return;
  const t0 = performance.now(), reduz = reduzMov();
  const loop = (now) => {
    if (!svg.isConnected || !linhas[0].dot.isConnected) return;
    const t = (now - t0) / 1000;
    linhas.forEach(({ base, flow, dot, len }) => {
      const k = reduz ? 1 : ((t * 22) % (len + 20)) / len, pt = base.getPointAtLength(Math.min(1, k) * len);
      dot.setAttribute('cx', pt.x); dot.setAttribute('cy', pt.y); dot.setAttribute('opacity', k > 1 ? 0 : 1);
      if (!reduz) flow.setAttribute('stroke-dashoffset', -t * 6);
    });
    if (ring && !reduz) { const p = (t % 1.6) / 1.6; ring.setAttribute('r', 2 + p * 5); ring.setAttribute('opacity', 1 - p); }
    if (!reduz && mode === '2d') anim = requestAnimationFrame(loop);
  };
  anim = requestAnimationFrame(loop);
}
// alças de edição (bolinhas). key diz o que a alça faz.
function drawHandles(g) {
  if (!sel) return;
  const h = tamHandle();
  const add = (x, z, key, cor, forma) => {
    if (forma === 'q') el('rect', { x: x - h, y: z - h, width: h * 2, height: h * 2, rx: h * .3, 'data-hr': 1, 'data-cx': x, 'data-cz': z, fill: cor || 'var(--cm-accent)', stroke: 'var(--cm-surface)', 'stroke-width': h * .35, 'pointer-events': 'none' }, g);
    else el('circle', { cx: x, cy: z, r: h, 'data-hr': 1, fill: cor || 'var(--cm-accent)', stroke: 'var(--cm-surface)', 'stroke-width': h * .35, 'pointer-events': 'none' }, g);
    el('circle', { cx: x, cy: z, r: h * 2.6, 'data-hr': 2.6, fill: 'transparent', 'data-handle': key, class: 'cm-hd' }, g);
  };
  const oito = (b, e, pre) => [[-1, -1], [0, -1], [1, -1], [1, 0], [1, 1], [0, 1], [-1, 1], [-1, 0]].forEach(([sx, sz]) => {
    const lx = sx < 0 ? e.x0 : sx > 0 ? e.x1 : (e.x0 + e.x1) / 2, lz = sz < 0 ? e.z0 : sz > 0 ? e.z1 : (e.z0 + e.z1) / 2, p = Wl(b, lx, lz); add(p.x, p.z, pre + sx + ',' + sz);
  });
  if (sel.t === 'cerca') {
    const l = S.limite;
    [['x0', l.x0, (l.z0 + l.z1) / 2], ['x1', l.x1, (l.z0 + l.z1) / 2], ['z0', (l.x0 + l.x1) / 2, l.z0], ['z1', (l.x0 + l.x1) / 2, l.z1]].forEach(([k, x, z]) => add(x, z, 'cerca:' + k));
    return;
  }
  if (sel.t === 'sala') {
    const s = salaSel(); if (!s) return; const { b, r } = s; if (r.andar !== floor) return;
    oito(b, retS(r), 'stam:');
    const pd = portaSala(r), p = Wl(b, pd.x, pd.z); add(p.x, p.z, 'sporta', 'var(--cm-red)', 'q');
    return;
  }
  if (sel.t === 'parte') {
    const b = blocoById(sel.b), p = b && b.partes[sel.i]; if (!p) return;
    oito(b, { x0: p.x - p.w / 2, x1: p.x + p.w / 2, z0: p.z - p.d / 2, z1: p.z + p.d / 2 }, 'ptam:');
    return;
  }
  if (sel.t !== 'bloco') return;
  const b = blocoById(sel.id); if (!b || b.tipo === 'ponto') return;
  if (b.tipo === 'caminho') {
    b.pontos.forEach((p, i) => add(p[0], p[1], 'pt:' + i, selPt === i ? 'var(--cm-red)' : 'var(--cm-accent)'));
    for (let i = 0; i < b.pontos.length - 1; i++) {
      const a = b.pontos[i], c = b.pontos[i + 1], mx = (a[0] + c[0]) / 2, mz = (a[1] + c[1]) / 2;
      el('circle', { cx: mx, cy: mz, r: h * .8, 'data-hr': .8, fill: 'var(--cm-surface)', stroke: 'var(--cm-accent)', 'stroke-width': h * .3, 'pointer-events': 'none' }, g);
      el('text', { x: mx, y: mz + h * .45, class: 'cm-blbl', style: `font-size:${h * 1.4}px;fill:var(--cm-accent);stroke:none`, 'pointer-events': 'none' }, g).textContent = '+';
      el('circle', { cx: mx, cy: mz, r: h * 2.2, 'data-hr': 2.2, fill: 'transparent', 'data-handle': 'mid:' + i, class: 'cm-hd' }, g);
    }
    return;
  }
  if (b.tipo === 'arvore') return add(b.x + b.w / 2, b.z, 'tam:1,0');
  if (b.tipo !== 'rotulo') oito(b, ext(b), 'tam:');
  if (b.tipo === 'predio') b.portas.forEach((q, i) => { const e = borda(b, q.x, q.z), p = Wl(b, e.x, e.z); add(p.x, p.z, 'porta:' + i, 'var(--cm-red)', 'q'); });
  const e = ext(b), topoL = b.tipo === 'rotulo' ? -(b.w || 6) : e.z0, topo = Wl(b, (e.x0 + e.x1) / 2, topoL - Math.max(5, h * 5)), base = Wl(b, (e.x0 + e.x1) / 2, topoL);
  el('line', { x1: base.x, y1: base.z, x2: topo.x, y2: topo.z, stroke: 'var(--cm-accent)', 'stroke-width': h * .3, 'pointer-events': 'none' }, g);
  add(topo.x, topo.z, 'rot', 'var(--cm-surface)');
  el('text', { x: topo.x, y: topo.z + h * .45, class: 'cm-blbl', style: `font-size:${h * 1.3}px;fill:var(--cm-accent);stroke:none`, 'pointer-events': 'none' }, g).textContent = '⟳';
}

/* ---------- mouse / toque ---------- */
const ptrs = new Map();
let drag = null;
const pd2 = () => { if (ptrs.size < 2) return 0; const [a, c] = [...ptrs.values()]; return Math.hypot(a[0] - c[0], a[1] - c[1]); };
function mundo(cx, cy) { const r = svg.getBoundingClientRect(), sc = escala(), ox = (r.width - vb.w * sc) / 2, oy = (r.height - vb.h * sc) / 2; return { x: vb.x + (cx - r.left - ox) / sc, z: vb.y + (cy - r.top - oy) / sc }; }
function zoomAt(f, cx, cy) {
  const m = mundo(cx, cy), nw = clamp(vb.w * f, 8, 900), k = nw / vb.w;
  vb.x = m.x - (m.x - vb.x) * k; vb.y = m.z - (m.z - vb.y) * k; vb.w = nw; vb.h *= k; setVB();
}
svg.addEventListener('wheel', (e) => { e.preventDefault(); zoomAt(e.deltaY > 0 ? 1.12 : 1 / 1.12, e.clientX, e.clientY); }, { passive: false });
function alvoDo(t) {
  const h = t.closest('[data-handle]'); if (h) return { kind: 'handle', key: h.dataset.handle };
  if (t.closest('[data-portao]')) return { kind: 'portao' };
  const r = t.closest('[data-r]'); if (r) { const b = blocoById(r.dataset.rb); const sala = b && b.salas.find((x) => x.id === r.dataset.r); if (sala) return { kind: 'sala', b, r: sala }; }
  const es = t.closest('[data-escada]'); if (es && tab === 'edit') { const b = blocoById(es.dataset.escada); if (b) return { kind: 'escada', b }; }
  const pa = t.closest('[data-parte]'), bl = t.closest('[data-b]');
  if (bl) { const b = blocoById(bl.dataset.b); if (b) { if (pa && sel && sel.t === 'parte' && sel.b === b.id) return { kind: 'parte', b, i: +pa.dataset.parte }; return { kind: 'bloco', b }; } }
  return null;
}
const jaSel = (a) => a && sel && ((a.kind === 'bloco' && sel.t === 'bloco' && sel.id === a.b.id) || (a.kind === 'sala' && sel.t === 'sala' && sel.id === a.r.id) || (a.kind === 'portao' && sel.t === 'portao') || (a.kind === 'parte' && sel.t === 'parte' && sel.b === a.b.id && sel.i === a.i) || (a.kind === 'escada' && sel.t === 'bloco' && sel.id === a.b.id));
function selecionar(a) {
  selPt = null;
  if (!a) { sel = null; formVarias = null; }
  else if (a.kind === 'bloco' || a.kind === 'escada') { if (!sel || sel.id !== a.b.id) formVarias = null; sel = { t: 'bloco', id: a.b.id }; }
  else if (a.kind === 'sala') sel = { t: 'sala', b: a.b.id, id: a.r.id };
  else if (a.kind === 'portao') sel = { t: 'portao' };
  else if (a.kind === 'parte') sel = { t: 'parte', b: a.b.id, i: a.i };
  draw(); renderPanel();
}
function iniciaObj(a) {
  if (a.kind === 'handle') {
    const k = a.key;
    if (k.startsWith('cerca:')) return { kind: 'cerca', k: k.slice(6), o: { ...S.limite } };
    if (k === 'sporta') { const s = salaSel(); return s && { kind: 'sporta', b: s.b, r: s.r }; }
    if (k.startsWith('stam:')) { const s = salaSel(); const [sx, sz] = k.slice(5).split(',').map(Number); return s && { kind: 'stam', b: s.b, r: s.r, o: { ...s.r }, sx, sz }; }
    if (k.startsWith('ptam:')) { const b = blocoById(sel.b); const [sx, sz] = k.slice(5).split(',').map(Number); return b && { kind: 'ptam', b, p: b.partes[sel.i], o: { ...b.partes[sel.i] }, sx, sz }; }
    const b = sel && sel.t === 'bloco' ? blocoById(sel.id) : null; if (!b) return null;
    if (k === 'rot') return { kind: 'giro', b };
    if (k.startsWith('porta:')) return { kind: 'porta', b, i: +k.slice(6) };
    if (k.startsWith('pt:')) { selPt = +k.slice(3); return { kind: 'pt', b, i: selPt, o: b.pontos[selPt].slice() }; }
    if (k.startsWith('mid:')) return { kind: 'mid', b, i: +k.slice(4) };
    if (k.startsWith('tam:')) { const [sx, sz] = k.slice(4).split(',').map(Number); return { kind: 'tam', b, o: clone(b), sx, sz }; }
    return null;
  }
  if (a.kind === 'portao') return { kind: 'portao', o: { ...S.portao } };
  if (a.kind === 'bloco') return { kind: 'move', b: a.b, o: clone(a.b) };
  if (a.kind === 'sala') return { kind: 'sala', b: a.b, r: a.r, o: { ...a.r } };
  if (a.kind === 'parte') return { kind: 'parte', b: a.b, p: a.b.partes[a.i], o: { ...a.b.partes[a.i] } };
  if (a.kind === 'escada') return { kind: 'escada', b: a.b, o: { ...a.b.escada } };
  return null;
}
svg.addEventListener('pointerdown', (e) => {
  ptrs.set(e.pointerId, [e.clientX, e.clientY]);
  try { svg.setPointerCapture(e.pointerId); } catch (er) {}
  if (ptrs.size > 1) { if (drag) { clearTimeout(drag.timer); drag.obj = null; drag.pinch = true; drag.d = pd2(); } return; }
  const a = alvoDo(e.target);
  drag = { x: e.clientX, y: e.clientY, vx: vb.x, vy: vb.y, d: 0, alvo: a, obj: null, moved: false, timer: 0, pinch: false };
  if (tab !== 'edit') return;
  if (ajustarFoto && FUNDO && (!a || a.kind !== 'handle')) { drag.obj = { kind: 'foto', o: { ...FUNDO } }; return; }
  if (!a) return;
  if (a.kind === 'handle' || jaSel(a)) { drag.obj = iniciaObj(a); return; }
  if (e.pointerType === 'mouse') { selecionar(a); drag.obj = iniciaObj(a); drag.selecionou = true; return; }
  drag.timer = setTimeout(() => {   // toque: segurar um pouco para pegar e arrastar
    if (!drag || drag.moved || drag.pinch) return;
    selecionar(a); drag.obj = iniciaObj(a); drag.selecionou = true; drag.x = drag.ux || drag.x; drag.y = drag.uy || drag.y;
    if (navigator.vibrate) try { navigator.vibrate(15); } catch (er) {}
  }, 380);
});
svg.addEventListener('pointermove', (e) => {
  if (!ptrs.has(e.pointerId) || !drag) return;
  ptrs.set(e.pointerId, [e.clientX, e.clientY]);
  if (ptrs.size === 2) { const d = pd2(), v = [...ptrs.values()]; if (drag.d) zoomAt(drag.d / d, (v[0][0] + v[1][0]) / 2, (v[0][1] + v[1][1]) / 2); drag.d = d; drag.moved = true; return; }
  if (drag.pinch) return;
  drag.ux = e.clientX; drag.uy = e.clientY;
  const dx = e.clientX - drag.x, dy = e.clientY - drag.y, sc = escala();
  if (drag.obj) {
    if (!drag.moved && Math.hypot(dx, dy) < 4) return;
    if (!drag.moved) { drag.moved = true; snapH(); }
    aplicaArrasto(drag.obj, dx / sc, dy / sc, e);
    return;
  }
  if (!drag.moved && Math.hypot(dx, dy) > 7) { drag.moved = true; clearTimeout(drag.timer); }
  if (drag.moved) { vb.x = drag.vx - dx / sc; vb.y = drag.vy - dy / sc; setVB(); }
});
const lv = (b, mx, mz) => { const a = -rad(b.rot || 0); return { x: mx * Math.cos(a) - mz * Math.sin(a), z: mx * Math.sin(a) + mz * Math.cos(a) }; };
// bordas para o ímã: paredes das outras salas do mesmo andar e das partes do prédio
function bordasIma(b, r, andar) {
  const xs = [], zs = [];
  b.partes.forEach((p) => { xs.push(p.x - p.w / 2, p.x + p.w / 2); zs.push(p.z - p.d / 2, p.z + p.d / 2); });
  b.salas.forEach((o) => { if (o === r || o.andar !== andar) return; xs.push(o.x - o.w / 2, o.x + o.w / 2); zs.push(o.z - o.d / 2, o.z + o.d / 2); });
  return { xs, zs };
}
function ajusta(v, lista) { if (!ima) return { v: r1(v), ok: false }; let best = null, d = .7; lista.forEach((c) => { const dd = Math.abs(c - v); if (dd < d) { d = dd; best = c; } }); return best == null ? { v: Math.round(v * 4) / 4, ok: false } : { v: best, ok: true }; }
function aplicaArrasto(o, mx, mz, e) {
  if (o.kind === 'portao') { S.portao.x = snapV(o.o.x + mx); S.portao.z = snapV(o.o.z + mz); }
  else if (o.kind === 'foto') { FUNDO.x = r1(o.o.x + mx); FUNDO.z = r1(o.o.z + mz); draw(); return; }
  else if (o.kind === 'move') {
    const b = o.b;
    if (b.tipo === 'caminho') { const dx = snapV(mx), dz = snapV(mz); b.pontos = o.o.pontos.map((p) => [r2(p[0] + dx), r2(p[1] + dz)]); syncCaminho(b); }
    else { b.x = snapV(o.o.x + mx); b.z = snapV(o.o.z + mz); }
  }
  else if (o.kind === 'cerca') { const l = S.limite, k = o.k, v = snapV(o.o[k] + (k[0] === 'x' ? mx : mz)); if (k === 'x0') l.x0 = Math.min(v, l.x1 - 20); if (k === 'x1') l.x1 = Math.max(v, l.x0 + 20); if (k === 'z0') l.z0 = Math.min(v, l.z1 - 20); if (k === 'z1') l.z1 = Math.max(v, l.z0 + 20); }
  else if (o.kind === 'giro') { const m = mundo(e.clientX, e.clientY); let ang = Math.atan2(m.z - o.b.z, m.x - o.b.x) * 180 / Math.PI + 90; ang = ((ang + 540) % 360) - 180; o.b.rot = ima ? Math.round(ang / 5) * 5 : Math.round(ang); }
  else if (o.kind === 'tam') {
    const b = o.b, lp = lv(b, mx, mz);
    if (b.tipo === 'arvore') b.w = b.d = clamp(snapV(o.o.w + 2 * lp.x), 2, 40);
    else if (b.tipo === 'predio') escalaPredio(b, o.o, o.sx, o.sz, lp);
    else {
      const nw = o.sx ? clamp(snapV(o.o.w + o.sx * lp.x), 1, 600) : o.o.w, nd = o.sz ? clamp(snapV(o.o.d + o.sz * lp.z), 1, 600) : o.o.d;
      const cx = o.sx * (nw - o.o.w) / 2, cz = o.sz * (nd - o.o.d) / 2, v = rotV(b, cx, cz);
      b.w = nw; b.d = nd; b.x = r2(o.o.x + v.x); b.z = r2(o.o.z + v.z);
    }
  }
  else if (o.kind === 'sala') {
    const b = o.b, r = o.r, lp = lv(b, mx, mz), bi = bordasIma(b, r, r.andar);
    let x = o.o.x + lp.x, z = o.o.z + lp.z;
    const ax0 = ajusta(x - r.w / 2, bi.xs), ax1 = ajusta(x + r.w / 2, bi.xs), az0 = ajusta(z - r.d / 2, bi.zs), az1 = ajusta(z + r.d / 2, bi.zs);
    x = ax0.ok ? ax0.v + r.w / 2 : ax1.ok ? ax1.v - r.w / 2 : ax0.v + r.w / 2;
    z = az0.ok ? az0.v + r.d / 2 : az1.ok ? az1.v - r.d / 2 : az0.v + r.d / 2;
    r.x = r2(x); r.z = r2(z);
    o.ultimo = mundo(e.clientX, e.clientY);
  }
  else if (o.kind === 'stam' || o.kind === 'ptam') {
    const b = o.b, obj = o.kind === 'stam' ? o.r : o.p, lp = lv(b, mx, mz), bi = o.kind === 'stam' ? bordasIma(b, o.r, o.r.andar) : { xs: b.partes.filter((p) => p !== o.p).flatMap((p) => [p.x - p.w / 2, p.x + p.w / 2]), zs: b.partes.filter((p) => p !== o.p).flatMap((p) => [p.z - p.d / 2, p.z + p.d / 2]) };
    let x0 = o.o.x - o.o.w / 2, x1 = o.o.x + o.o.w / 2, z0 = o.o.z - o.o.d / 2, z1 = o.o.z + o.o.d / 2;
    if (o.sx > 0) x1 = Math.max(x0 + .8, ajusta(x1 + lp.x, bi.xs).v); if (o.sx < 0) x0 = Math.min(x1 - .8, ajusta(x0 + lp.x, bi.xs).v);
    if (o.sz > 0) z1 = Math.max(z0 + .8, ajusta(z1 + lp.z, bi.zs).v); if (o.sz < 0) z0 = Math.min(z1 - .8, ajusta(z0 + lp.z, bi.zs).v);
    obj.x = r2((x0 + x1) / 2); obj.w = r2(x1 - x0); obj.z = r2((z0 + z1) / 2); obj.d = r2(z1 - z0);
    if (o.kind === 'ptam') syncPredio(b);
  }
  else if (o.kind === 'parte') { const b = o.b, lp = lv(b, mx, mz); o.p.x = r2(Math.round((o.o.x + lp.x) * 4) / 4); o.p.z = r2(Math.round((o.o.z + lp.z) * 4) / 4); syncPredio(b); }
  else if (o.kind === 'escada') { const b = o.b, lp = lv(b, mx, mz); b.escada.x = r2(snapV(o.o.x + lp.x)); b.escada.z = r2(snapV(o.o.z + lp.z)); }
  else if (o.kind === 'sporta') {
    const m = mundo(e.clientX, e.clientY), p = locP(o.b, m.x, m.z), r = o.r, q = retS(r);
    const ds = [['N', Math.abs(p.z - q.z0)], ['S', Math.abs(p.z - q.z1)], ['O', Math.abs(p.x - q.x0)], ['L', Math.abs(p.x - q.x1)]].sort((a, c) => a[1] - c[1]);
    const lado = ds[0][0], t = lado === 'N' || lado === 'S' ? (p.x - q.x0) / r.w : (p.z - q.z0) / r.d;
    r.porta = { lado, t: clamp(Math.round(t * 20) / 20, .05, .95) };
  }
  else if (o.kind === 'porta') { const m = mundo(e.clientX, e.clientY), p = locP(o.b, m.x, m.z), q = borda(o.b, p.x, p.z); o.b.portas[o.i] = { x: r2(q.x), z: r2(q.z) }; }
  else if (o.kind === 'mid') { const b = o.b, a2 = b.pontos[o.i], c = b.pontos[o.i + 1]; b.pontos.splice(o.i + 1, 0, [r2((a2[0] + c[0]) / 2), r2((a2[1] + c[1]) / 2)]); Object.assign(o, { kind: 'pt', i: o.i + 1, o: b.pontos[o.i + 1].slice() }); selPt = o.i; if (b.pontos.length >= 3 && !b.curvaTocada) b.curva = true; return aplicaArrasto(o, mx, mz, e); }
  else if (o.kind === 'pt') { const b = o.b; b.pontos[o.i] = [snapV(o.o[0] + mx), snapV(o.o[1] + mz)]; syncCaminho(b); }
  save(); draw(); atualizaCampos();
}
// muda o tamanho do prédio inteiro: partes, salas, portas e escada esticam juntas
function escalaPredio(b, o, sx, sz, lp) {
  const e = ext(o); let x0 = e.x0, x1 = e.x1, z0 = e.z0, z1 = e.z1;
  if (sx > 0) x1 = Math.max(x0 + 3, snapV(e.x1 + lp.x)); if (sx < 0) x0 = Math.min(x1 - 3, snapV(e.x0 + lp.x));
  if (sz > 0) z1 = Math.max(z0 + 3, snapV(e.z1 + lp.z)); if (sz < 0) z0 = Math.min(z1 - 3, snapV(e.z0 + lp.z));
  const kx = (x1 - x0) / (e.x1 - e.x0), kz = (z1 - z0) / (e.z1 - e.z0), fx = (v) => r2(x0 + (v - e.x0) * kx), fz = (v) => r2(z0 + (v - e.z0) * kz);
  b.partes = o.partes.map((p) => ({ x: fx(p.x), z: fz(p.z), w: r2(p.w * kx), d: r2(p.d * kz) }));
  b.salas.forEach((r, i) => { const q = o.salas[i]; if (!q) return; r.x = fx(q.x); r.z = fz(q.z); r.w = r2(q.w * kx); r.d = r2(q.d * kz); });
  b.portas = o.portas.map((p) => ({ x: fx(p.x), z: fz(p.z) }));
  b.escada = { x: fx(o.escada.x), z: fz(o.escada.z), w: o.escada.w, d: o.escada.d };
  syncPredio(b);
}
const up = (e) => {
  if (!ptrs.has(e.pointerId)) return;
  ptrs.delete(e.pointerId);
  if (!drag) return;
  clearTimeout(drag.timer);
  if (ptrs.size) return;
  const d = drag; drag = null;
  if (d.pinch) return;
  if (d.obj && d.moved) {
    if (d.obj.kind === 'foto') { saveFoto(); renderPanel(); return; }
    if (d.obj.kind === 'sala') trocaPredioSeFora(d.obj);
    if (d.obj.kind === 'ptam' || d.obj.kind === 'parte' || d.obj.kind === 'tam') { const b = d.obj.b; if (b.tipo === 'predio') reprojetaPortas(b); }
    save(); draw(); renderPanel(); if (T3) update3(false); return;
  }
  if (d.moved || d.selecionou) return;
  const a = d.alvo;
  if (tab === 'edit') { if (!a || a.kind !== 'handle') selecionar(a); else if (a.key.startsWith('pt:')) { renderPanel(); draw(); } return; }
  if (!a) return;
  if (a.kind === 'sala') pickSala(a.b, a.r);
  else if ((a.kind === 'bloco' || a.kind === 'escada') && roteavel(a.b)) pickBloco(a.b);
};
svg.addEventListener('pointerup', up); svg.addEventListener('pointercancel', (e) => { up(e); draw(); });
function reprojetaPortas(b) { b.portas = b.portas.map((p) => { const q = borda(b, p.x, p.z); return { x: r2(q.x), z: r2(q.z) }; }); }
// sala solta fora do prédio: se caiu dentro de outro prédio, muda de prédio
function trocaPredioSeFora(o) {
  const b = o.b, r = o.r;
  if (dentroPe(b, r.x, r.z)) return;
  const c = Wl(b, r.x, r.z), nb = S.blocos.filter((x) => x.tipo === 'predio' && x !== b && dentroPeW(x, c.x, c.z)).pop();
  if (!nb) { toast(L('A sala ficou fora do prédio. Se foi sem querer, use Desfazer.', 'La sala quedó fuera del edificio.')); return; }
  const p = locP(nb, c.x, c.z); b.salas = b.salas.filter((x) => x !== r); r.x = r2(p.x); r.z = r2(p.z); r.andar = Math.min(r.andar, nb.andares - 1); nb.salas.push(r);
  sel = { t: 'sala', b: nb.id, id: r.id }; toast(L(`Sala levada para ${nb.nome}.`, `Sala llevada a ${nb.nome}.`));
}
root.querySelectorAll('[data-z]').forEach((bt) => bt.onclick = () => {
  const z = bt.dataset.z;
  if (mode === '3d') { if (z === 'home') home3(); else z3(z === 'in' ? .75 : 1.35); return; }
  if (z === 'home') return animVB(fit());
  const r = svg.getBoundingClientRect(); zoomAt(z === 'in' ? 1 / 1.35 : 1.35, r.left + r.width / 2, r.top + r.height / 2);
});
let iniciado = false;
new ResizeObserver(() => {
  const r = svg.getBoundingClientRect(); if (!r.width || !r.height) return;
  if (!iniciado) { vb = fit(); setVB(); iniciado = true; if (op.salaInicial || op.origemInicial) abrirInicial(); }
  else { const c = { x: vb.x + vb.w / 2, y: vb.y + vb.h / 2 }, a = r.width / r.height; vb.h = vb.w / a; vb.x = c.x - vb.w / 2; vb.y = c.y - vb.h / 2; setVB(); }
  if (T3) resize3();
}).observe(stage);
function focar(alvo, min) {
  if (!alvo) return;
  let xs = [], zs = [];
  const bb = bbox(alvo.b); xs.push(bb.x0, bb.x1); zs.push(bb.z0, bb.z1);
  if (tab !== 'edit') {
    // enquadra o trecho do andar que está na tela (ou a rota toda, se nenhum trecho estiver nele)
    const rota = rotaPara(alvo); let segs = rota.segs.filter((s) => s.andar === floor); if (!segs.length) segs = rota.segs;
    segs.forEach((s) => s.pts.forEach((p) => { xs.push(p[0]); zs.push(p[1]); }));
  }
  const n = fit({ x0: Math.min(...xs) - 10, x1: Math.max(...xs) + 10, z0: Math.min(...zs) - 14, z1: Math.max(...zs) + 10 }), mw = min || 80;
  if (n.w < mw) { const k = mw / n.w; n.x -= (n.w * k - n.w) / 2; n.y -= (n.h * k - n.h) / 2; n.w *= k; n.h *= k; }
  n.y -= n.h * .06;
  animVB(n);
}

/* ---------- teclado (editor) ---------- */
if (editavel) document.addEventListener('keydown', (e) => {
  if (tab !== 'edit' || !root.isConnected) return;
  const a = document.activeElement, digitando = a && (/^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName) || a.isContentEditable);
  const ctrl = e.ctrlKey || e.metaKey, k = e.key.toLowerCase();
  if (ctrl && k === 'z' && !digitando) { e.preventDefault(); e.shiftKey ? refazer() : desfazer(); return; }
  if (ctrl && k === 'y' && !digitando) { e.preventDefault(); refazer(); return; }
  if (e.key === 'Escape' && digitando) { a.blur(); return; }
  if (digitando || !sel) return;
  if (e.key === 'Escape') { selecionar(null); return; }
  if (ctrl && k === 'd') { e.preventDefault(); duplicar(); return; }
  if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); if (sel.t === 'bloco' && selPt != null) removePonto(); else excluir(); return; }
  const passo = e.shiftKey ? 5 : (ima ? .5 : .1), mv = { ArrowLeft: [-passo, 0], ArrowRight: [passo, 0], ArrowUp: [0, -passo], ArrowDown: [0, passo] }[e.key];
  if (!mv) return;
  e.preventDefault(); if (!e.repeat) snapH();
  if (sel.t === 'portao') { S.portao.x = r2(S.portao.x + mv[0]); S.portao.z = r2(S.portao.z + mv[1]); }
  else if (sel.t === 'sala') { const s = salaSel(); if (!s) return; const v = lv(s.b, mv[0], mv[1]); s.r.x = r2(s.r.x + v.x); s.r.z = r2(s.r.z + v.z); }
  else if (sel.t === 'parte') { const b = blocoById(sel.b), p = b && b.partes[sel.i]; if (!p) return; const v = lv(b, mv[0], mv[1]); p.x = r2(p.x + v.x); p.z = r2(p.z + v.z); syncPredio(b); }
  else if (sel.t === 'bloco') { const b = blocoById(sel.id); if (!b) return; if (b.tipo === 'caminho') { b.pontos = b.pontos.map((p) => [r2(p[0] + mv[0]), r2(p[1] + mv[1])]); syncCaminho(b); } else { b.x = r2(b.x + mv[0]); b.z = r2(b.z + mv[1]); } }
  save(); draw(); atualizaCampos();
});

/* =====================================================================
   SELEÇÃO E PAINEL
   ===================================================================== */
// no modo visitante: sem "Saindo de", mostra o andar do destino; com "Saindo de", o andar onde a rota começa
function andarParaMostrar(padrao) {
  if (tab !== 'find' || !origem) return padrao;
  const alvo = alvoAtual(), rota = alvo && rotaPara(alvo);
  return rota && rota.segs.length ? rota.segs[0].andar : padrao;
}
function pickSala(b, r) {
  if (!b || !r) return;
  sel = { t: 'sala', b: b.id, id: r.id };
  if (tab === 'find') floor = andarParaMostrar(r.andar);
  renderFloors(); draw(); renderPanel();
  if (tab === 'find') { focar(alvoAtual()); if (T3) update3(true); atualizarEndereco(); }
}
function pickBloco(b) {
  if (!b) return;
  sel = { t: 'bloco', id: b.id };
  if (tab === 'find') floor = andarParaMostrar(b.tipo === 'ponto' && predioDoPonto(b) ? b.andar || 0 : 0);
  renderFloors(); draw(); renderPanel();
  if (tab === 'find') { focar(alvoAtual()); if (T3) update3(true); atualizarEndereco(); }
}
// muda o "Saindo de" (null = portão principal)
function definirOrigem(ref) {
  origem = lugarDe(ref) ? ref : null;
  if (sel && chaveLugar(lugarDe(sel)) === chaveLugar(origemAtual())) sel = null; // origem e destino iguais
  const o = origemAtual();
  if (!alvoAtual() && o) floor = andarDoAlvo(o); else if (alvoAtual()) floor = andarParaMostrar(floor);
  renderFloors(); draw(); renderPanel(); atualizarEndereco();
  if (alvoAtual()) focar(alvoAtual()); else if (o) focar(o, 70);
  if (T3) update3(!!alvoAtual());
}
// abre já com a sala (?sala=) e/ou a origem (?de=) pedidas no endereço
function abrirInicial() {
  if (op.origemInicial) { const ref = refPorId(op.origemInicial); if (ref) origem = ref; }
  const ref = refPorId(op.salaInicial);
  if (ref) { const l = lugarDe(ref); if (l.r) return pickSala(l.b, l.r); return pickBloco(l.b); }
  if (origem) definirOrigem(origem);
}
// guarda a sala e a origem no endereço (dá pra compartilhar o link e o F5 não perde a rota)
function atualizarEndereco() {
  if (editavel || !window.history || !history.replaceState) return;
  try {
    const u = new URL(location.href), idDe = (ref) => (ref ? ref.id : '');
    if (sel) u.searchParams.set('sala', idDe(sel)); else u.searchParams.delete('sala');
    if (origem) u.searchParams.set('de', idDe(origem)); else u.searchParams.delete('de');
    history.replaceState(null, '', u.pathname + (u.searchParams.toString() ? '?' + u.searchParams.toString() : '') + u.hash);
  } catch (e) { /* endereço sem suporte a URL(): segue sem atualizar */ }
}
function renderFloors() {
  const n = maxAndares(); if (floor >= n) floor = n - 1;
  const box = $('.cm-floors');
  box.innerHTML = ANDARES.slice(0, n).map((nome, i) => `<button type="button" data-f="${i}" class="${i === floor ? 'on' : ''}" aria-pressed="${i === floor}">${nome}</button>`).join('');
  box.querySelectorAll('button').forEach((bt) => bt.onclick = () => { floor = +bt.dataset.f; if (tab === 'edit' && sel && sel.t === 'sala') { const sb = blocoById(sel.b), sr = sb && sb.salas.find((x) => x.id === sel.id); if (sr && sr.andar !== floor) sel = { t: 'bloco', id: sel.b }; } renderFloors(); draw(); if (T3) update3(false); renderPanel(); });
}
function trocaAba(t) {
  tab = t; root.querySelectorAll('.cm-tabs button').forEach((x) => x.classList.toggle('on', x.dataset.t === t));
  const eb = $('.cm-edbar'); if (eb) eb.hidden = t !== 'edit' || mode !== '2d';
  if (t === 'edit' && mode === '3d') setMode('2d');
  if (t !== 'edit' && sel && !['sala', 'bloco'].includes(sel.t)) sel = null;
  ajustarFoto = false;
  draw(); renderPanel(); body.scrollTop = 0;
}
if (editavel) {
  root.querySelectorAll('.cm-tabs button').forEach((bt) => bt.onclick = () => trocaAba(bt.dataset.t));
  root.querySelectorAll('[data-ed]').forEach((bt) => bt.onclick = () => {
    const k = bt.dataset.ed;
    if (k === 'undo') desfazer(); else if (k === 'redo') refazer();
    else if (k === 'ima') { ima = !ima; bt.classList.toggle('on', ima); toast(ima ? 'Ímã ligado: as salas grudam nas paredes das outras.' : 'Ímã desligado: movimento livre.'); }
    else if (k === 'grade') { grade = !grade; bt.classList.toggle('on', grade); draw(); }
  });
}
let ultimaSel = '';
function renderPanel() {
  const chave = tab + ':' + (sel ? sel.t + ':' + (sel.id || sel.b || '') + ':' + (sel.i == null ? '' : sel.i) : '');
  if (chave !== ultimaSel) { ultimaSel = chave; body.scrollTop = 0; }
  if (tab === 'find') panelFind(); else if (tab === 'edit') panelEdit(); else panelData();
}
const norm = (s) => String(s).normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
const nomeSetor = (id) => (setorPorId[id] ? setorPorId[id].nome : '');
const ordNome = (a, c) => String(a.n).localeCompare(String(c.n), 'pt', { numeric: true });

// opções do "Saindo de": portão, entrada de cada prédio, todas as salas (por prédio e andar) e os outros lugares
function opcoesOrigem() {
  const atual = origem ? (origem.t === 'sala' ? 's:' + origem.id : 'b:' + origem.id) : '';
  const opt = (v, txt) => `<option value="${esc(v)}"${v === atual ? ' selected' : ''}>${esc(txt)}</option>`;
  const grupo = (rotulo, itens) => (itens ? `<optgroup label="${esc(rotulo)}">${itens}</optgroup>` : '');
  const preds = S.blocos.filter((b) => b.tipo === 'predio');
  let h = opt('', L('Portão principal do campus', 'Portón principal del campus'));
  h += grupo(L('Entrada de um prédio', 'Entrada de un edificio'), preds.map((b) => opt('b:' + b.id, b.nome)).join(''));
  preds.forEach((b) => { for (let a = 0; a < (b.andares || 1); a++) h += grupo(b.nome + ' · ' + ANDARES[a], b.salas.filter((r) => r.andar === a).sort(ordNome).map((r) => opt('s:' + r.id, r.n || '—')).join('')); });
  h += grupo(L('Outros lugares', 'Otros lugares'), S.blocos.filter((b) => roteavel(b) && !['predio', 'caminho'].includes(b.tipo) && b.nome).map((b) => opt('b:' + b.id, (b.tipo === 'ponto' && b.icone ? b.icone + ' ' : '') + b.nome)).join(''));
  return h;
}
function refDoValor(v) {
  if (!v) return null;
  if (v.startsWith('b:')) return { t: 'bloco', id: v.slice(2) };
  const id = v.slice(2), b = S.blocos.find((x) => x.salas.some((r) => r.id === id));
  return b ? { t: 'sala', b: b.id, id } : null;
}
function panelFind() {
  const alvo = alvoAtual(), o = origemAtual();
  let card = '';
  if (alvo) {
    const b = alvo.b, rota = rotaPara(alvo), m = rota.metros, vel = +op.velocidade > 0 ? +op.velocidade : 1.2;
    const min = Math.max(1, Math.round((m / vel + (rota.usaEscada ? 15 : 0)) / 60));
    const nome = alvo.r ? alvo.r.n : b.nome, setor = alvo.r && alvo.r.setor ? setorPorId[alvo.r.setor] : null;
    const sub = alvo.r ? esc(b.nome) + ' · ' + ANDARES[alvo.r.andar] + (alvo.r.cat !== 'outro' ? ' · ' + esc(CATS[alvo.r.cat][0]) : '') : esc(TIPOS[b.tipo][0].replace(/ \(.*\)/, ''));
    // acessibilidade (I3): a rota usa escada e o destino não tem acesso sem escada informado
    const napne = setorPorId.napne;
    const avisoEscada = rota.usaEscada && !(op.semEscada || {})[alvo.r ? alvo.r.id : b.id]
      ? `<p class="cm-aviso">${L('Este caminho usa <b>escada</b>. Precisa evitar escada? Peça atendimento no térreo ou fale com o', 'Este camino usa <b>escalera</b>. ¿Necesita evitarla? Pida atención en la planta baja o hable con el')} ${napne && napne.url ? `<a href="${esc(napne.url)}">NAPNE</a>` : 'NAPNE'}.</p>` : '';
    const outrosAndares = rota.andares.filter((a) => a !== floor);
    card = `<div class="cm-card"><div><div class="cm-eyebrow">${sub}</div><h2 class="cm-h">${esc(nome)}</h2></div>
      ${rota.mesmoLugar ? '' : `<div class="cm-chips"><span class="cm-chip g">≈ ${m} m ${L('a pé', 'a pie')}</span><span class="cm-chip">≈ ${min} min</span>${rota.usaEscada ? `<span class="cm-chip">${L('via escada', 'por la escalera')}</span>` : ''}</div>`}
      ${rota.bloqueada && !rota.mesmoLugar ? `<p class="cm-hint">${L('Caminho aproximado: o mapa não tem um corredor livre até aqui.', 'Camino aproximado: el mapa no tiene un pasillo libre hasta aquí.')}</p>` : ''}
      <ol class="cm-steps" id="cm-passos">${passos(alvo, rota, o).map((s) => `<li>${s}</li>`).join('')}</ol>
      ${avisoEscada}
      ${outrosAndares.map((a) => `<button class="cm-btn" data-andar="${a}" type="button">${L('Ver o trecho no ', 'Ver el tramo en ') + ANDARES[a]}</button>`).join('')}
      ${!editavel ? `<button class="cm-btn" type="button" data-ouvir="#cm-passos"><span aria-hidden="true">🔊</span> ${L('Ouvir o caminho', 'Escuchar el camino')}</button>` : ''}
      ${setor && setor.url ? `<a class="cm-btn pri" href="${esc(setor.url)}">${L('Ver página do setor', 'Ver página del departamento')} →</a>` : ''}
      ${chaveLugar(o) !== chaveLugar(alvo) ? `<button class="cm-btn" data-acao="daqui" type="button"><span aria-hidden="true">📍</span> ${L('Estou aqui: sair daqui', 'Estoy aquí: salir de aquí')}</button>` : ''}
      <button class="cm-btn" data-acao="limpar" type="button">${L('Fechar', 'Cerrar')}</button></div>`;
  }
  // "Saindo de" (N2): de onde a rota começa — portão, entrada de um prédio, uma sala ou outro lugar
  card = `<label class="cm-lab cm-origem">${L('Saindo de', 'Saliendo de')}<select class="cm-input" data-campo="origem">${opcoesOrigem()}</select></label>`
    + (o && !alvo ? `<p class="cm-hint">${L(`Saindo de <b>${esc(o.r ? o.r.n : o.b.nome)}</b>. Agora escolha para onde quer ir: toque numa sala no mapa ou na lista.`, `Saliendo de <b>${esc(o.r ? o.r.n : o.b.nome)}</b>. Ahora elija a dónde quiere ir: toque una sala en el mapa o en la lista.`)}</p>` : '')
    + card;
  const q = norm(busca), grupos = [], catsUsadas = new Set();
  const preds = S.blocos.filter((b) => b.tipo === 'predio');
  preds.forEach((b) => b.salas.forEach((r) => catsUsadas.add(r.cat)));
  preds.forEach((b) => {
    for (let a = 0; a < (b.andares || 1); a++) {
      const salas = b.salas.filter((r) => r.andar === a && (!filtroCat || r.cat === filtroCat) && (!q || norm(r.n).includes(q) || norm(b.nome).includes(q) || norm(nomeSetor(r.setor)).includes(q) || norm(CATS[r.cat][0]).includes(q))).sort(ordNome);
      if (salas.length) grupos.push(`<h3>${esc(b.nome)} · ${ANDARES[a]}</h3>` + salas.map((r) => `<button type="button" class="cm-item${sel && sel.t === 'sala' && sel.id === r.id ? ' on' : ''}" data-b="${esc(b.id)}" data-r="${esc(r.id)}"><i style="background:${corSala(r)}"></i><span>${esc(r.n)}</span><small>${r.cat !== 'outro' ? esc(CATS[r.cat][0]) : ''}</small></button>`).join(''));
    }
  });
  const lugares = filtroCat ? [] : S.blocos.filter((b) => roteavel(b) && b.tipo !== 'caminho' && (!q || norm(b.nome).includes(q)));
  if (lugares.length) grupos.push(`<h3>${L('Lugares do campus', 'Lugares del campus')}</h3>` + lugares.map((b) => `<button type="button" class="cm-item${sel && sel.t === 'bloco' && sel.id === b.id ? ' on' : ''}" data-bloco="${esc(b.id)}"><i style="background:${corBloco(b)}"></i><span>${b.tipo === 'ponto' ? esc(b.icone || '') + ' ' : ''}${esc(b.nome)}</span><small>${b.tipo === 'ponto' && predioDoPonto(b) ? ANDARES[b.andar || 0] : esc(TIPOS[b.tipo][0].replace(/ \(.*\)/, ''))}</small></button>`).join(''));
  const chips = [...catsUsadas].filter((c) => c !== 'outro');
  body.innerHTML = `${card}
    <label class="cm-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><span class="cm-sr">${L('Buscar sala, setor ou lugar', 'Buscar sala, departamento o lugar')}</span><input class="cm-q" type="search" placeholder="${L('Onde fica… (sala, setor ou lugar)', '¿Dónde está…? (sala, departamento o lugar)')}" value="${esc(busca)}" autocomplete="off"></label>
    ${chips.length > 1 ? `<div class="cm-filtros">${chips.map((c) => `<button type="button" class="cm-fchip${filtroCat === c ? ' on' : ''}" data-cat="${c}"><i style="background:${CATS[c][1]}"></i>${esc(CATS[c][0])}</button>`).join('')}</div>` : ''}
    <div class="cm-list">${grupos.join('') || `<p class="cm-hint">${L('Nada encontrado. Tente outro nome.', 'No se encontró nada. Pruebe otro nombre.')}</p>`}</div>`;
  const qi = body.querySelector('.cm-q');
  qi.oninput = () => { busca = qi.value; const pos = qi.selectionStart; renderPanel(); const n = body.querySelector('.cm-q'); n.focus(); n.setSelectionRange(pos, pos); };
  body.querySelectorAll('[data-cat]').forEach((bt) => bt.onclick = () => { filtroCat = filtroCat === bt.dataset.cat ? null : bt.dataset.cat; renderPanel(); });
  body.querySelectorAll('[data-r]').forEach((bt) => bt.onclick = () => { const b = blocoById(bt.dataset.b); pickSala(b, b.salas.find((r) => r.id === bt.dataset.r)); if (matchMedia('(max-width: 900px)').matches && !root.classList.contains('cm-full')) stage.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
  body.querySelectorAll('[data-bloco]').forEach((bt) => bt.onclick = () => pickBloco(blocoById(bt.dataset.bloco)));
  const lim = body.querySelector('[data-acao="limpar"]'); if (lim) lim.onclick = () => { sel = null; draw(); renderPanel(); if (T3) update3(false); atualizarEndereco(); };
  body.querySelectorAll('[data-andar]').forEach((bt) => bt.onclick = () => { floor = +bt.dataset.andar; renderFloors(); draw(); renderPanel(); focar(alvoAtual()); if (T3) update3(false); });
  const so = body.querySelector('[data-campo="origem"]'); if (so) so.onchange = () => definirOrigem(refDoValor(so.value));
  const dq = body.querySelector('[data-acao="daqui"]');
  if (dq) dq.onclick = () => { const ref = sel; sel = null; definirOrigem(ref); toast(L('Pronto: a rota agora sai daqui. Escolha para onde quer ir.', 'Listo: la ruta sale de aquí. Elija a dónde quiere ir.')); };
}

/* ---------- editor ---------- */
function armar(btn, rotulo, fn, msg) {
  if (!btn.classList.contains('armed')) { btn.classList.add('armed'); btn.textContent = msg || 'Toque de novo para confirmar'; setTimeout(() => { if (btn.isConnected) { btn.classList.remove('armed'); btn.textContent = rotulo; } }, 3000); return; }
  fn();
}
function changed(keep) { save(); renderFloors(); draw(); if (!keep) renderPanel(); if (T3 && mode === '3d') update3(false); }
function limpaSel() {
  if (!sel) return;
  if ((sel.t === 'bloco' && !blocoById(sel.id)) || (sel.t === 'sala' && !salaSel()) || (sel.t === 'parte' && !(blocoById(sel.b) && blocoById(sel.b).partes[sel.i]))) sel = null;
}
function desfazer() { if (!hist.length) return toast('Nada para desfazer.'); futuro.push(JSON.stringify(S)); S = JSON.parse(hist.pop()); limpaSel(); changed(); toast('Desfeito.'); }
function refazer() { if (!futuro.length) return toast('Nada para refazer.'); hist.push(JSON.stringify(S)); S = JSON.parse(futuro.pop()); limpaSel(); changed(); toast('Refeito.'); }
function duplicar() {
  if (!sel) return;
  if (sel.t === 'bloco') {
    const b = blocoById(sel.id); if (!b) return; snapH();
    const c = clone(b); c.id = uid(); c.salas.forEach((r) => { r.id = uid(); }); c.nome = b.nome + ' (cópia)';
    if (c.tipo === 'caminho') { c.pontos = c.pontos.map((p) => [p[0] + 6, p[1] + 6]); syncCaminho(c); } else { c.x += 6; c.z += 6; }
    S.blocos.push(c); sel = { t: 'bloco', id: c.id };
  } else if (sel.t === 'sala') {
    const s = salaSel(); if (!s) return; snapH();
    const c = clone(s.r); c.id = uid(); c.x = r2(c.x + (c.w >= c.d ? c.w : 0)); c.z = r2(c.z + (c.w >= c.d ? 0 : c.d)); c.n = s.r.n; s.b.salas.push(c); sel = { t: 'sala', b: s.b.id, id: c.id };
  } else if (sel.t === 'parte') {
    const b = blocoById(sel.b); if (!b) return; snapH(); const p = { ...b.partes[sel.i] }; p.x += 4; p.z += 4; b.partes.push(p); syncPredio(b); sel = { t: 'parte', b: b.id, i: b.partes.length - 1 };
  } else return;
  changed(); toast('Duplicado. Arraste para o lugar.');
}
function excluir() {
  if (!sel) return;
  if (sel.t === 'bloco') { snapH(); S.blocos = S.blocos.filter((x) => x.id !== sel.id); sel = null; }
  else if (sel.t === 'sala') { const s = salaSel(); if (!s) return; snapH(); s.b.salas = s.b.salas.filter((x) => x !== s.r); sel = { t: 'bloco', id: s.b.id }; }
  else if (sel.t === 'parte') { const b = blocoById(sel.b); if (!b || b.partes.length < 2) return toast('O prédio precisa de pelo menos uma parte.'); snapH(); b.partes.splice(sel.i, 1); syncPredio(b); reprojetaPortas(b); sel = { t: 'bloco', id: b.id }; }
  else return;
  changed(); toast('Excluído. Dá para desfazer (Ctrl+Z ou ↶).');
}
function removePonto() {
  const b = blocoById(sel.id); if (!b || selPt == null) return;
  if (b.pontos.length <= 2) return toast('O caminho precisa de pelo menos 2 pontos.');
  snapH(); b.pontos.splice(selPt, 1); selPt = null; syncCaminho(b); changed(); toast('Ponto removido.');
}
function addBloco(tipo) {
  snapH();
  const tam = { predio: [40, 16], patio: [40, 18], estac: [40, 30], campo: [60, 40], quadra: [16, 22], mata: [40, 30], verde: [30, 20], obra: [30, 20], arvore: [9, 9], caminho: [30, 4], ponto: [6, 6], rotulo: [6, 6] }[tipo];
  const nomes = { predio: 'Novo prédio', obra: 'Obra (em breve)', ponto: 'Bebedouro', rotulo: 'Texto', caminho: 'Calçada' };
  const cx = Math.round(vb.x + vb.w / 2), cz = Math.round(vb.y + vb.h / 2);
  const b = { id: uid(), nome: nomes[tipo] || TIPOS[tipo][0], tipo, x: cx, z: cz, w: tam[0], d: tam[1], rot: 0, andares: 1, salas: [] };
  if (tipo === 'predio') { b.partes = [{ x: 0, z: 0, w: 40, d: 16 }]; b.portas = [{ x: 0, z: 8 }]; b.escada = { x: 0, z: 0, w: 3, d: 6 }; syncPredio(b); }
  if (tipo === 'caminho') { b.pontos = [[cx - 15, cz], [cx, cz - 6], [cx + 15, cz]]; b.larg = 4; b.curva = true; syncCaminho(b); }
  if (tipo === 'ponto') { b.icone = '🚰'; b.andar = floor; }
  S.blocos.push(b); sel = { t: 'bloco', id: b.id }; changed(); toast('Adicionado no meio da tela. Arraste para o lugar certo.');
  setTimeout(() => { const i = body.querySelector('[data-campo="nome"]'); if (i) { i.focus(); i.select(); } }, 60);
}
/* campos numéricos: barra + caixa de número, sempre sincronizados */
function campoNum(k, lab, v, min, max, step, un) {
  return `<div class="cm-field cm-num"><label><span>${lab}</span><span class="cm-numbox"><input type="number" data-n="${k}" value="${r2(v)}" min="${min}" max="${max}" step="${step}" inputmode="decimal">${un || ''}</span></label><input type="range" data-sl="${k}" min="${min}" max="${max}" step="${step}" value="${v}" aria-label="${esc(lab)}"></div>`;
}
let objCampos = null;
function ligaCampos(obj, aoMudar) {
  objCampos = obj;
  body.querySelectorAll('[data-sl],[data-n]').forEach((inp) => {
    const k = inp.dataset.sl || inp.dataset.n;
    inp.addEventListener('pointerdown', snapH); inp.addEventListener('focus', snapH);
    inp.addEventListener('input', () => {
      const v = parseFloat(String(inp.value).replace(',', '.')); if (!isFinite(v)) return;
      const antes = obj[k]; obj[k] = v; if (aoMudar) aoMudar(k, v, antes);
      body.querySelectorAll(`[data-sl="${k}"],[data-n="${k}"]`).forEach((o) => { if (o !== inp) o.value = r2(obj[k]); });
      if (obj === FUNDO) { saveFoto(); draw(); } else { save(); draw(); }
    });
  });
}
function atualizaCampos() {
  if (!objCampos) return;
  body.querySelectorAll('[data-sl],[data-n]').forEach((inp) => { const k = inp.dataset.sl || inp.dataset.n; if (objCampos[k] != null && document.activeElement !== inp) inp.value = r2(objCampos[k]); });
}
const seg = (nome, ops, atual) => `<div class="cm-seg" data-seg="${nome}">${ops.map(([v, n]) => `<button type="button" data-v="${v}" class="${String(atual) === String(v) ? 'on' : ''}">${n}</button>`).join('')}</div>`;
const ligaSeg = (nome, fn) => { const s = body.querySelector(`[data-seg="${nome}"]`); if (s) s.querySelectorAll('button').forEach((bt) => bt.onclick = () => fn(bt.dataset.v)); };
function corHex(v) { return /^#[0-9a-f]{6}$/i.test(v || '') ? v : ''; }
function panelEdit() {
  objCampos = null;
  if (sel && sel.t === 'sala') return panelSala();
  if (sel && sel.t === 'parte') return panelParte();
  if (sel && sel.t === 'portao') {
    body.innerHTML = `<button class="cm-back" data-acao="volta" type="button">← Voltar</button><div><div class="cm-eyebrow">Entrada do campus</div><h2 class="cm-h">Portão principal</h2></div><p class="cm-hint">Arraste o portão no mapa até onde ele fica de verdade. Todas as rotas começam aqui.</p>
      ${campoNum('x', 'Posição leste–oeste', S.portao.x, Math.round(S.limite.x0 - 20), Math.round(S.limite.x1 + 20), .5, ' m')}${campoNum('z', 'Posição norte–sul', S.portao.z, Math.round(S.limite.z0 - 20), Math.round(S.limite.z1 + 20), .5, ' m')}`;
    body.querySelector('[data-acao="volta"]').onclick = () => selecionar(null); ligaCampos(S.portao); return;
  }
  if (sel && sel.t === 'cerca') {
    const l = S.limite;
    body.innerHTML = `<button class="cm-back" data-acao="volta" type="button">← Voltar</button><div><div class="cm-eyebrow">Terreno</div><h2 class="cm-h">Cerca do campus</h2></div><p class="cm-hint">Puxe as bolinhas verdes nos lados da cerca, ou ajuste aqui. As ruas acompanham a cerca.</p>
      ${campoNum('x0', 'Lado esquerdo', l.x0, -500, 500, 1, ' m')}${campoNum('x1', 'Lado direito', l.x1, -500, 500, 1, ' m')}${campoNum('z0', 'Lado de cima', l.z0, -500, 500, 1, ' m')}${campoNum('z1', 'Lado de baixo (avenida)', l.z1, -500, 500, 1, ' m')}
      <hr class="cm-hr"><p class="cm-hint">O mapa fica "reto" pelo prédio principal. A seta do norte (canto do mapa) mostra para onde fica o norte de verdade.</p>
      ${campoNum('norte', 'Direção do norte', S.norte || 0, -180, 180, .5, '°')}`;
    body.querySelector('[data-acao="volta"]').onclick = () => selecionar(null);
    const px = { ...l, norte: S.norte || 0 }; ligaCampos(px, (k, v) => { if (k === 'norte') S.norte = v; else S.limite[k] = v; }); return;
  }
  const b = sel && sel.t === 'bloco' ? blocoById(sel.id) : null;
  if (!b) return panelEditRaiz();
  if (b.tipo === 'predio') return panelPredio(b);
  if (b.tipo === 'caminho') return panelCaminho(b);
  panelBloco(b);
}
function panelEditRaiz() {
  const f = FUNDO;
  body.innerHTML = `<div><div class="cm-eyebrow">Editor do mapa</div><h2 class="cm-h">Toque em qualquer coisa do mapa</h2>
    <p class="cm-hint"><b>No computador:</b> clique e arraste para mover. As bolinhas verdes mudam o tamanho, a de cima (⟳) gira e as vermelhas são portas. Setas movem, Delete apaga, Ctrl+Z desfaz.<br><b>No celular:</b> toque para escolher e depois arraste. Ou segure o dedo em cima para pegar e arrastar direto.<br><b>Salas:</b> toque no prédio, troque o andar lá em cima e arraste cada sala até o lugar exato. A porta vermelha da sala também se arrasta.</p></div>
    <div class="cm-field"><span class="cm-lab">Adicionar no meio da tela</span><div class="cm-grid3">${Object.keys(TIPOS).map((k) => `<button class="cm-btn sm" type="button" data-add="${k}">+ ${esc(TIPOS[k][0].replace(/ \(.*\)/, ''))}</button>`).join('')}</div></div>
    <div class="cm-row2"><button class="cm-btn" data-acao="portao" type="button">🚪 Portão</button><button class="cm-btn" data-acao="cerca" type="button">▭ Cerca do campus</button></div>
    <details class="cm-det"${f ? ' open' : ''}><summary>📷 Foto de fundo (para copiar a planta real)</summary>
      <p class="cm-hint">Coloque a foto aérea ou uma planta por baixo do mapa e arraste os prédios e salas em cima dela. A foto só aparece aqui no editor.</p>
      <label class="cm-btn pri cm-file">Escolher foto<input type="file" accept="image/*" data-acao="foto" hidden></label>
      ${f ? `${campoNum('op', 'Transparência', f.op == null ? .6 : f.op, .05, 1, .05, '')}${campoNum('w', 'Largura da foto', f.w, 20, 1000, 1, ' m')}${campoNum('rot', 'Girar foto', f.rot || 0, -180, 180, .5, '°')}
        <div class="cm-row2"><button class="cm-btn ${ajustarFoto ? 'pri' : ''}" data-acao="ajfoto" type="button">${ajustarFoto ? '✓ Arrastando a foto' : '✥ Arrastar a foto'}</button><button class="cm-btn" data-acao="verfoto" type="button">${f.ver === false ? 'Mostrar foto' : 'Esconder foto'}</button></div>
        <button class="cm-btn danger" data-acao="tirafoto" type="button">Remover foto</button>` : ''}
    </details>
    <label class="cm-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><input class="cm-q" type="search" placeholder="Procurar prédio ou sala…" value="${esc(busca)}" autocomplete="off"></label>
    <div class="cm-list cm-lista-ed"></div>`;
  const lista = () => {
    const q = norm(busca), out = [];
    S.blocos.filter((x) => x.tipo !== 'arvore' && (!q || norm(x.nome).includes(q) || x.salas.some((r) => norm(r.n).includes(q)))).forEach((x) => {
      out.push(`<button type="button" class="cm-item" data-bloco="${esc(x.id)}"><i style="background:${corBloco(x)}"></i><span>${x.tipo === 'ponto' ? esc(x.icone) + ' ' : ''}${esc(x.nome || '(sem nome)')}</span><small>${x.tipo === 'predio' ? x.salas.length + ' salas' : esc(TIPOS[x.tipo][0].replace(/ \(.*\)/, ''))}</small></button>`);
      if (q) x.salas.filter((r) => norm(r.n).includes(q)).sort(ordNome).forEach((r) => out.push(`<button type="button" class="cm-item cm-sub" data-sala="${esc(r.id)}" data-sb="${esc(x.id)}"><i style="background:${corSala(r)}"></i><span>${esc(r.n)}</span><small>${ANDARES[r.andar]}</small></button>`));
    });
    const n = S.blocos.filter((x) => x.tipo === 'arvore').length;
    body.querySelector('.cm-lista-ed').innerHTML = `<h3>No mapa</h3>${out.join('') || '<p class="cm-hint">Nada encontrado.</p>'}${!q && n ? `<p class="cm-hint">+ ${n} árvores (toque nelas no mapa para editar).</p>` : ''}`;
    body.querySelectorAll('[data-bloco]').forEach((bt) => bt.onclick = () => { const x = blocoById(bt.dataset.bloco); sel = { t: 'bloco', id: x.id }; draw(); renderPanel(); focar({ b: x }, 60); });
    body.querySelectorAll('[data-sala]').forEach((bt) => bt.onclick = () => { const x = blocoById(bt.dataset.sb), r = x.salas.find((y) => y.id === bt.dataset.sala); floor = r.andar; sel = { t: 'sala', b: x.id, id: r.id }; renderFloors(); draw(); renderPanel(); focar({ b: x }, 50); });
  };
  lista();
  const qi = body.querySelector('.cm-q'); qi.oninput = () => { busca = qi.value; lista(); };
  body.querySelectorAll('[data-add]').forEach((bt) => bt.onclick = () => addBloco(bt.dataset.add));
  body.querySelector('[data-acao="portao"]').onclick = () => { sel = { t: 'portao' }; draw(); renderPanel(); };
  body.querySelector('[data-acao="cerca"]').onclick = () => { sel = { t: 'cerca' }; draw(); renderPanel(); animVB(fit()); };
  body.querySelector('[data-acao="foto"]').onchange = (e) => carregaFoto(e.target.files && e.target.files[0]);
  if (f) {
    if (!f.asp) f.asp = f.h / f.w;
    ligaCampos(f, (k, v) => { if (k === 'w') f.h = r1(v * f.asp); });
    body.querySelector('[data-acao="ajfoto"]').onclick = () => { ajustarFoto = !ajustarFoto; renderPanel(); toast(ajustarFoto ? 'Agora arrastar no mapa move a foto. Toque de novo para parar.' : 'Pronto.'); };
    body.querySelector('[data-acao="verfoto"]').onclick = () => { f.ver = f.ver === false; saveFoto(); draw(); renderPanel(); };
    body.querySelector('[data-acao="tirafoto"]').onclick = (e) => armar(e.currentTarget, 'Remover foto', () => { FUNDO = null; ajustarFoto = false; saveFoto(); draw(); renderPanel(); });
  }
}
function carregaFoto(file) {
  if (!file) return;
  const fr = new FileReader();
  fr.onload = () => {
    const img = new Image();
    img.onload = () => {
      const max = 2200, k = Math.min(1, max / Math.max(img.width, img.height)), cv = document.createElement('canvas');
      cv.width = Math.round(img.width * k); cv.height = Math.round(img.height * k);
      cv.getContext('2d').drawImage(img, 0, 0, cv.width, cv.height);
      const l = S.limite, asp = cv.height / cv.width, w = r1(l.x1 - l.x0);
      FUNDO = { src: cv.toDataURL('image/jpeg', .82), x: r1((l.x0 + l.x1) / 2), z: r1((l.z0 + l.z1) / 2), w, h: r1(w * asp), asp, rot: 0, op: .6, ver: true };
      saveFoto(); draw(); renderPanel(); animVB(fit());
      toast('Foto colocada. Ajuste a largura e a posição até os prédios baterem.');
    };
    img.onerror = () => toast('Não consegui abrir essa imagem.');
    img.src = fr.result;
  };
  fr.readAsDataURL(file);
}
function blocoComum(b, extra) {
  const arv = b.tipo === 'arvore', rot = b.tipo === 'rotulo', pto = b.tipo === 'ponto';
  return `${arv ? '' : `<div class="cm-field"><label>${rot ? 'Texto' : 'Nome'}<input class="cm-input" data-campo="nome" type="text" value="${esc(b.nome)}" autocomplete="off"></label></div>`}
    ${extra || ''}
    ${!arv ? `<div class="cm-field"><span class="cm-lab">Cor</span><div class="cm-cor"><input type="color" data-campo="cor" value="${corHex(b.cor) || '#cccccc'}"><button class="cm-btn sm" data-acao="corpadrao" type="button" ${b.cor ? '' : 'disabled'}>Cor padrão</button></div></div>` : ''}`;
}
function ligaComum(b) {
  const q = (s) => body.querySelector(s);
  q('[data-acao="volta"]').onclick = () => selecionar(null);
  const nm = q('[data-campo="nome"]'); if (nm) { nm.onfocus = snapH; nm.oninput = () => { b.nome = nm.value; save(); draw(); q('.cm-h').textContent = b.nome || '(sem nome)'; }; }
  const cor = q('[data-campo="cor"]'); if (cor) { cor.onfocus = snapH; cor.oninput = () => { b.cor = cor.value; save(); draw(); q('[data-acao="corpadrao"]').disabled = false; }; }
  const cp = q('[data-acao="corpadrao"]'); if (cp) cp.onclick = () => { snapH(); delete b.cor; changed(); };
  const tp = q('[data-campo="tipo"]'); if (tp) tp.onchange = (e) => trocaTipo(b, e.target.value);
  body.querySelectorAll('[data-giro]').forEach((bt) => bt.onclick = () => { snapH(); b.rot = (((b.rot || 0) + +bt.dataset.giro + 540) % 360) - 180; changed(true); atualizaCampos(); });
  body.querySelectorAll('[data-n2]').forEach((bt) => bt.onclick = () => { const [dx, dz] = bt.dataset.n2.split(',').map(Number); snapH(); if (b.tipo === 'caminho') { b.pontos = b.pontos.map((p) => [p[0] + dx, p[1] + dz]); syncCaminho(b); } else { b.x = r2(b.x + dx); b.z = r2(b.z + dz); } changed(true); atualizaCampos(); });
  const dup = q('[data-acao="dup"]'); if (dup) dup.onclick = duplicar;
  const del = q('[data-acao="del"]'); if (del) del.onclick = (e) => armar(e.currentTarget, 'Excluir', excluir, 'Toque de novo para excluir');
}
const htmlGiro = (b) => campoNum('rot', 'Girar', b.rot || 0, -180, 180, 1, '°') + `<div class="cm-row4"><button class="cm-btn sm" data-giro="-90" type="button">⟲ 90°</button><button class="cm-btn sm" data-giro="-15" type="button">⟲ 15°</button><button class="cm-btn sm" data-giro="15" type="button">⟳ 15°</button><button class="cm-btn sm" data-giro="90" type="button">⟳ 90°</button></div>`;
const htmlPos = (b) => `<details class="cm-det"><summary>Posição exata</summary>${b.tipo === 'caminho' ? '' : campoNum('x', 'Leste–oeste', b.x, Math.round(S.limite.x0 - 60), Math.round(S.limite.x1 + 60), .5, ' m') + campoNum('z', 'Norte–sul', b.z, Math.round(S.limite.z0 - 60), Math.round(S.limite.z1 + 60), .5, ' m')}
      <div class="cm-pad"><span></span><button class="cm-btn" type="button" data-n2="0,-1" aria-label="Para o norte">↑</button><span></span><button class="cm-btn" type="button" data-n2="-1,0" aria-label="Para o oeste">←</button><span></span><button class="cm-btn" type="button" data-n2="1,0" aria-label="Para o leste">→</button><span></span><button class="cm-btn" type="button" data-n2="0,1" aria-label="Para o sul">↓</button><span></span></div></details>`;
const htmlTipo = (b) => `<div class="cm-field"><span class="cm-lab">Trocar o tipo</span><select class="cm-input" data-campo="tipo">${Object.keys(TIPOS).map((k) => `<option value="${k}" ${k === b.tipo ? 'selected' : ''}>${esc(TIPOS[k][0])}</option>`).join('')}</select></div>`;
const htmlFim = '<div class="cm-row2"><button class="cm-btn" data-acao="dup" type="button">Duplicar</button><button class="cm-btn danger" data-acao="del" type="button">Excluir</button></div>';
function trocaTipo(b, t) {
  snapH(); const antes = b.tipo; b.tipo = t;
  if (t === 'predio' && antes !== 'predio') { b.partes = [{ x: 0, z: 0, w: b.w, d: b.d }]; b.portas = [{ x: 0, z: b.d / 2 }]; b.escada = { x: 0, z: 0, w: 3, d: 6 }; b.salas = []; b.andares = 1; syncPredio(b); }
  if (antes === 'predio' && t !== 'predio') { b.salas = []; b.andares = 1; }
  if (t === 'caminho' && antes !== 'caminho') { b.pontos = [[b.x - b.w / 2, b.z], [b.x + b.w / 2, b.z]]; b.larg = 4; b.curva = false; b.rot = 0; syncCaminho(b); }
  if (t === 'ponto' && !b.icone) b.icone = '📍';
  changed();
}
function panelBloco(b) {
  const arv = b.tipo === 'arvore', rot = b.tipo === 'rotulo', pto = b.tipo === 'ponto';
  body.innerHTML = `<button class="cm-back" data-acao="volta" type="button">← Tudo do mapa</button>
    <div><div class="cm-eyebrow">${esc(TIPOS[b.tipo][0])}</div><h2 class="cm-h">${esc(b.nome || '(sem nome)')}</h2></div>
    ${pto ? `<div class="cm-field"><span class="cm-lab">Andar</span>${seg('andarPonto', ANDARES.slice(0, Math.max(maxAndares(), (b.andar || 0) + 1)).map((n, i) => [i, n]), b.andar || 0)}<p class="cm-hint">${predioDoPonto(b) ? `Está dentro do <b>${esc(predioDoPonto(b).nome)}</b>: só aparece no mapa quando esse andar estiver escolhido.` : 'Está ao ar livre: aparece em todos os andares. Arraste para dentro de um prédio para ele ficar só no andar escolhido.'}</p></div>` : ''}
    ${blocoComum(b, pto ? `<div class="cm-field"><span class="cm-lab">Ícone</span><div class="cm-icones">${ICONES.map((i) => `<button type="button" data-ic="${i}" class="${b.icone === i ? 'on' : ''}">${i}</button>`).join('')}</div></div>` : '')}
    ${rot ? campoNum('w', 'Tamanho da letra', b.w, 1, 40, .5, ' m') : pto ? campoNum('w', 'Tamanho do ícone', b.w, 2, 30, .5, ' m') : arv ? campoNum('w', 'Tamanho da copa', b.w, 2, 40, .5, ' m') : campoNum('w', 'Largura', b.w, 1, 600, .5, ' m') + campoNum('d', 'Profundidade', b.d, 1, 600, .5, ' m')}
    ${!arv && !pto ? htmlGiro(b) : ''}${htmlPos(b)}${htmlTipo(b)}${htmlFim}`;
  ligaComum(b);
  ligaCampos(b, (k, v) => { if (k === 'w' && arv) b.d = v; if (k === 'rot') b.rot = ((v + 540) % 360) - 180; });
  body.querySelectorAll('[data-ic]').forEach((bt) => bt.onclick = () => { snapH(); b.icone = bt.dataset.ic; changed(); });
  ligaSeg('andarPonto', (v) => { snapH(); b.andar = +v; floor = +v; changed(); toast(`Ponto no ${ANDARES[+v]}.`); });
}
function panelCaminho(b) {
  body.innerHTML = `<button class="cm-back" data-acao="volta" type="button">← Tudo do mapa</button>
    <div><div class="cm-eyebrow">Caminho / calçada</div><h2 class="cm-h">${esc(b.nome || '(sem nome)')}</h2>
    <p class="cm-hint">Arraste as bolinhas para mudar o traçado. Puxe o <b>+</b> do meio de um trecho para criar um ponto novo e fazer a curva. Toque num ponto e use "Remover ponto" (ou Delete). A rota das pessoas prefere andar pelos caminhos.</p></div>
    ${blocoComum(b)}
    ${campoNum('larg', 'Largura', b.larg, .5, 30, .5, ' m')}
    <div class="cm-field"><span class="cm-lab">Formato</span>${seg('curva', [[1, '〰 Curvo (suave)'], [0, '⟋ Reto (com quinas)']], b.curva ? 1 : 0)}</div>
    <div class="cm-row2"><button class="cm-btn" data-acao="rmpt" type="button" ${selPt == null || b.pontos.length <= 2 ? 'disabled' : ''}>Remover ponto${selPt != null ? ' ' + (selPt + 1) : ''}</button><button class="cm-btn" data-acao="addfim" type="button">+ Ponto no fim</button></div>
    <p class="cm-hint">${b.pontos.length} pontos · ≈ ${Math.round(comp(amostraCaminho(b, 1)))} m de comprimento</p>
    ${htmlPos(b)}${htmlTipo(b)}${htmlFim}`;
  ligaComum(b);
  ligaCampos(b);
  ligaSeg('curva', (v) => { snapH(); b.curva = v === '1'; b.curvaTocada = true; changed(); });
  body.querySelector('[data-acao="rmpt"]').onclick = removePonto;
  body.querySelector('[data-acao="addfim"]').onclick = () => { snapH(); const p = b.pontos, a = p[p.length - 2], c = p[p.length - 1]; p.push([r2(c[0] + (c[0] - a[0]) * .5 + .01), r2(c[1] + (c[1] - a[1]) * .5)]); selPt = p.length - 1; syncCaminho(b); changed(); };
}
const PRESETS = { ret: 'Retângulo', H: 'Em H', L: 'Em L', U: 'Em U', T: 'Em T' };
function aplicaPreset(b, k) {
  const e = ext(b), W = e.x1 - e.x0, D = e.z1 - e.z0, cx = (e.x0 + e.x1) / 2, cz = (e.z0 + e.z1) / 2, a = Math.max(4, r2(W * .22)), br = Math.max(4, r2(D * .28));
  const P = (x, z, w, d) => ({ x: r2(cx + x), z: r2(cz + z), w: r2(w), d: r2(d) });
  b.partes = {
    ret: [P(0, 0, W, D)],
    H: [P(-W / 2 + a / 2, 0, a, D), P(W / 2 - a / 2, 0, a, D), P(0, 0, W - 2 * a, br)],
    L: [P(-W / 2 + a / 2, 0, a, D), P(a / 2, D / 2 - br / 2, W - a, br)],
    U: [P(-W / 2 + a / 2, 0, a, D), P(W / 2 - a / 2, 0, a, D), P(0, D / 2 - br / 2, W - 2 * a, br)],
    T: [P(0, -D / 2 + br / 2, W, br), P(0, br / 2, a, D - br)],
  }[k];
  syncPredio(b); reprojetaPortas(b);
}
// Andar onde entram as salas novas: o que está escolhido lá em cima. Se o prédio ainda
// não tem esse andar, ele passa a ter (antes a sala ia parar no térreo sem avisar).
function andarParaSalas(b) {
  if (floor > b.andares - 1) { b.andares = Math.min(MAX_ANDARES, floor + 1); toast(`${b.nome} agora tem ${b.andares} andares.`); }
  return Math.min(floor, b.andares - 1);
}
function panelPredio(b) {
  const af = floor, faltaAndar = floor > b.andares - 1, salasAndar = b.salas.filter((r) => r.andar === af).sort(ordNome);
  body.innerHTML = `<button class="cm-back" data-acao="volta" type="button">← Tudo do mapa</button>
    <div><div class="cm-eyebrow">Prédio</div><h2 class="cm-h">${esc(b.nome || '(sem nome)')}</h2></div>
    ${blocoComum(b)}
    <div class="cm-field"><span class="cm-lab">Andares</span>${seg('andares', [[1, '1'], [2, '2'], [3, '3'], [4, '4']], b.andares)}</div>
    <hr class="cm-hr">
    <div><div class="cm-eyebrow">Salas · ${ANDARES[af]}</div><p class="cm-hint">${b.andares > 1 ? 'Escolha o andar aqui ou lá em cima do mapa. ' : ''}Arraste as salas no mapa até o lugar exato e puxe as bolinhas para o tamanho. Com o <b>Ímã</b> ligado, as paredes grudam nas das outras salas.</p></div>
    ${seg('andarSalas', ANDARES.slice(0, Math.min(MAX_ANDARES, Math.max(b.andares, af + 1) + (b.andares < MAX_ANDARES ? 1 : 0))).map((n, i) => [i, i < b.andares ? n : '+ ' + n]), af)}
    ${faltaAndar ? `<p class="cm-aviso">Este prédio ainda não tem o <b>${ANDARES[af]}</b>. Ao criar uma sala aqui, ele passa a ter ${af + 1} andares.</p>` : ''}
    <div class="cm-row2"><button class="cm-btn pri" data-acao="addsala" type="button">+ Sala</button><button class="cm-btn" data-acao="varias" type="button">+ Várias salas</button></div>
    ${formVarias && formVarias.b === b.id ? formVariasHtml(b) : ''}
    <div class="cm-list">${salasAndar.map((r) => `<button type="button" class="cm-item" data-sala="${esc(r.id)}"><i style="background:${corSala(r)}"></i><span>${esc(r.n || '(sem nome)')}</span><small>${r2(r.w)}×${r2(r.d)} m</small></button>`).join('') || '<p class="cm-hint">Nenhuma sala neste andar.</p>'}</div>
    <hr class="cm-hr">
    <div><div class="cm-eyebrow">Formato do prédio</div><p class="cm-hint">O prédio é feito de partes (retângulos). Use um modelo pronto e depois ajuste cada parte arrastando, ou crie partes novas.</p></div>
    <div class="cm-grid3">${Object.keys(PRESETS).map((k) => `<button class="cm-btn sm" type="button" data-preset="${k}">${PRESETS[k]}</button>`).join('')}<button class="cm-btn sm" type="button" data-acao="addparte">+ Parte</button></div>
    <div class="cm-list">${b.partes.map((p, i) => `<button type="button" class="cm-item" data-parte="${i}"><i style="background:var(--cm-slab)"></i><span>Parte ${i + 1}</span><small>${r2(p.w)}×${r2(p.d)} m</small></button>`).join('')}</div>
    <div><div class="cm-eyebrow">Entradas</div><p class="cm-hint">As portas vermelhas. Arraste no mapa pela borda do prédio. Um prédio pode ter várias (por exemplo, frente e fundos).</p></div>
    <div class="cm-list">${b.portas.map((p, i) => { return `<div class="cm-item cm-linha"><i style="background:var(--cm-red)"></i><span>Entrada ${i + 1}</span><button class="cm-btn sm danger" type="button" data-rmporta="${i}" ${b.portas.length < 2 ? 'disabled' : ''}>Tirar</button></div>`; }).join('')}</div>
    <button class="cm-btn" data-acao="addporta" type="button">+ Entrada</button>
    ${b.andares > 1 ? '<p class="cm-hint">A <b>escada</b> (o retângulo com degraus) também se arrasta no mapa.</p>' + campoNum('ew', 'Largura da escada', b.escada.w, 1, 20, .5, ' m') + campoNum('ed', 'Comprimento da escada', b.escada.d, 1, 20, .5, ' m') : ''}
    <hr class="cm-hr">
    ${campoNum('w', 'Largura total', b.w, 3, 600, .5, ' m')}${campoNum('d', 'Profundidade total', b.d, 3, 600, .5, ' m')}
    <p class="cm-hint">Mudar a largura/profundidade estica o prédio inteiro, com as salas junto.</p>
    ${htmlGiro(b)}${htmlPos(b)}${htmlTipo(b)}${htmlFim}`;
  ligaComum(b);
  const q = (s) => body.querySelector(s);
  // largura/profundidade esticam o prédio todo; escada separada
  const proxy = { w: b.w, d: b.d, rot: b.rot, x: b.x, z: b.z, ew: b.escada.w, ed: b.escada.d };
  ligaCampos(proxy, (k, v) => {
    if (k === 'w' || k === 'd') { const o = clone(b), e = ext(o); const lp = k === 'w' ? { x: v - (e.x1 - e.x0), z: 0 } : { x: 0, z: v - (e.z1 - e.z0) }; const ima0 = ima; ima = false; escalaPredio(b, o, k === 'w' ? 1 : 0, k === 'd' ? 1 : 0, lp); ima = ima0; }
    else if (k === 'rot') b.rot = ((v + 540) % 360) - 180;
    else if (k === 'ew') b.escada.w = v; else if (k === 'ed') b.escada.d = v;
    else b[k] = v;
  });
  ligaSeg('andares', (v) => { snapH(); b.andares = +v; b.salas.forEach((r) => { if (r.andar >= b.andares) r.andar = b.andares - 1; }); changed(); });
  ligaSeg('andarSalas', (v) => {
    v = +v;
    if (v > b.andares - 1) { snapH(); b.andares = v + 1; save(); toast(`${b.nome} agora tem ${b.andares} andares.`); }
    floor = v; renderFloors(); draw(); renderPanel(); if (T3 && mode === '3d') update3(false);
  });
  body.querySelectorAll('[data-preset]').forEach((bt) => bt.onclick = () => { snapH(); aplicaPreset(b, bt.dataset.preset); changed(); toast('Formato aplicado. Ajuste as partes se precisar.'); });
  q('[data-acao="addparte"]').onclick = () => { snapH(); const e = ext(b); b.partes.push({ x: r2(e.x1 + 5), z: r2((e.z0 + e.z1) / 2), w: 10, d: 10 }); syncPredio(b); sel = { t: 'parte', b: b.id, i: b.partes.length - 1 }; changed(); toast('Parte nova criada à direita. Arraste para encaixar.'); };
  body.querySelectorAll('[data-parte]').forEach((bt) => bt.onclick = () => { sel = { t: 'parte', b: b.id, i: +bt.dataset.parte }; draw(); renderPanel(); });
  body.querySelectorAll('[data-rmporta]').forEach((bt) => bt.onclick = () => { snapH(); b.portas.splice(+bt.dataset.rmporta, 1); changed(); });
  q('[data-acao="addporta"]').onclick = () => { snapH(); const e = ext(b), p = borda(b, (e.x0 + e.x1) / 2, e.z0 - 50); b.portas.push({ x: r2(p.x), z: r2(p.z) }); changed(); toast('Entrada criada. Arraste a porta vermelha pela borda.'); };
  q('[data-acao="addsala"]').onclick = () => { snapH(); const af = andarParaSalas(b), r = novaSala(b, af); b.salas.push(r); sel = { t: 'sala', b: b.id, id: r.id }; floor = af; changed(); toast('Sala criada no meio do prédio. Arraste para o lugar e digite o nome.'); setTimeout(() => { const i = body.querySelector('[data-campo="sala"]'); if (i) { i.focus(); i.select(); } }, 50); };
  q('[data-acao="varias"]').onclick = () => { formVarias = formVarias && formVarias.b === b.id ? null : { b: b.id }; renderPanel(); const t = q('.cm-varias textarea'); if (t) t.focus(); };
  if (formVarias && formVarias.b === b.id) ligaVarias(b);
  body.querySelectorAll('[data-sala]').forEach((bt) => bt.onclick = () => { sel = { t: 'sala', b: b.id, id: bt.dataset.sala }; draw(); renderPanel(); });
}
function novaSala(b, andar) {
  // procura um espaço livre dentro do prédio para a sala nova
  const w = 6, d = 6, salas = b.salas.filter((r) => r.andar === andar);
  for (const p of b.partes) for (let z = p.z - p.d / 2 + d / 2; z <= p.z + p.d / 2 - d / 2 + .01; z += 1) for (let x = p.x - p.w / 2 + w / 2; x <= p.x + p.w / 2 - w / 2 + .01; x += 1) {
    if (!salas.some((r) => Math.abs(r.x - x) < (r.w + w) / 2 - .01 && Math.abs(r.z - z) < (r.d + d) / 2 - .01)) return { id: uid(), n: 'Nova sala', andar, x: r2(x), z: r2(z), w, d, porta: { lado: z < p.z ? 'S' : 'N', t: .5 }, cat: 'aula', setor: null };
  }
  const p = b.partes[0]; return { id: uid(), n: 'Nova sala', andar, x: p.x, z: p.z, w, d, porta: { lado: 'S', t: .5 }, cat: 'aula', setor: null };
}
// coloca salas em fila numa parte do prédio, com corredor no meio
function filaSalas(p, n, modo, cw) {
  const horiz = p.w >= p.d, comp2 = horiz ? p.w : p.d, larg = horiz ? p.d : p.w, out = [];
  const dois = modo === 'dois' && larg > cw + 3, porLado = dois ? Math.ceil(n / 2) : n, tam = comp2 / Math.max(1, porLado), prof = dois ? (larg - cw) / 2 : larg - cw;
  for (let i = 0; i < n; i++) {
    const lado = dois ? i % 2 : 0, k = dois ? Math.floor(i / 2) : i, al = -comp2 / 2 + tam * (k + .5), ac = lado === 0 ? -larg / 2 + prof / 2 : larg / 2 - prof / 2;
    out.push(horiz ? { x: r2(p.x + al), z: r2(p.z + ac), w: r2(tam), d: r2(prof), porta: { lado: lado === 0 ? 'S' : 'N', t: .5 } } : { x: r2(p.x + ac), z: r2(p.z + al), w: r2(prof), d: r2(tam), porta: { lado: lado === 0 ? 'L' : 'O', t: .5 } });
  }
  return out;
}
function formVariasHtml(b) {
  return `<div class="cm-varias cm-card">
    <div class="cm-field"><label>Nomes das salas (uma por linha)<textarea class="cm-input" rows="6" placeholder="Sala 01&#10;Sala 02&#10;Laboratório de Química&#10;Banheiro Masculino"></textarea></label></div>
    <div class="cm-row2"><button class="cm-btn sm" type="button" data-gera="Sala">Gerar Sala 01…</button><button class="cm-btn sm" type="button" data-gera="Laboratório">Gerar Laboratório 01…</button></div>
    <div class="cm-field cm-num"><label><span>Quantas (para os botões acima)</span><span class="cm-numbox"><input type="number" class="cm-qtd" value="10" min="1" max="60"></span></label></div>
    <div class="cm-field"><label>Em qual parte do prédio<select class="cm-input cm-vpeca">${b.partes.map((p, i) => `<option value="${i}">Parte ${i + 1} (${r2(p.w)}×${r2(p.d)} m)</option>`).join('')}</select></label></div>
    <div class="cm-field"><label>Como arrumar<select class="cm-input cm-vlado"><option value="dois">Corredor no meio, salas dos dois lados</option><option value="um">Salas de um lado só</option></select></label></div>
    <div class="cm-field"><label>Tipo<select class="cm-input cm-vcat"><option value="auto">Descobrir pelo nome</option>${Object.keys(CATS).map((c) => `<option value="${c}">${esc(CATS[c][0])}</option>`).join('')}</select></label></div>
    <label class="cm-check"><input type="checkbox" class="cm-vsub" checked> Tirar as salas que já estão nessa parte (neste andar)</label>
    <button class="cm-btn pri" type="button" data-acao="criarvarias">Criar as salas</button>
    <p class="cm-hint">Depois é só arrastar e esticar cada uma até o lugar exato.</p></div>`;
}
function ligaVarias(b) {
  const box = body.querySelector('.cm-varias'), ta = box.querySelector('textarea');
  box.querySelectorAll('[data-gera]').forEach((bt) => bt.onclick = () => { const n = clamp(parseInt(box.querySelector('.cm-qtd').value, 10) || 10, 1, 60); ta.value = Array.from({ length: n }, (_, i) => `${bt.dataset.gera} ${String(i + 1).padStart(2, '0')}`).join('\n'); });
  box.querySelector('[data-acao="criarvarias"]').onclick = () => {
    const nomes = ta.value.split('\n').map((s) => s.trim()).filter(Boolean).slice(0, 120);
    if (!nomes.length) return toast('Escreva pelo menos um nome.');
    snapH();
    const p = b.partes[+box.querySelector('.cm-vpeca').value] || b.partes[0], af = andarParaSalas(b), cat = box.querySelector('.cm-vcat').value;
    if (box.querySelector('.cm-vsub').checked) b.salas = b.salas.filter((r) => !(r.andar === af && emRet(p, r.x, r.z)));
    filaSalas(p, nomes.length, box.querySelector('.cm-vlado').value, 3).forEach((g, i) => b.salas.push(Object.assign({ id: uid(), n: nomes[i], andar: af, cat: cat === 'auto' ? adivinhaCat(nomes[i]) : cat, setor: null }, g)));
    formVarias = null; changed(); toast(`${nomes.length} salas criadas. Arraste para ajustar.`);
  };
}
function panelParte() {
  const b = blocoById(sel.b), p = b && b.partes[sel.i]; if (!p) { sel = null; return renderPanel(); }
  const af = Math.min(floor, b.andares - 1), dentro = b.salas.filter((r) => r.andar === af && emRet(p, r.x, r.z));
  body.innerHTML = `<button class="cm-back" data-acao="volta" type="button">← ${esc(b.nome)}</button>
    <div><div class="cm-eyebrow">${esc(b.nome)} · formato</div><h2 class="cm-h">Parte ${sel.i + 1}</h2><p class="cm-hint">Arraste esta parte no mapa e puxe as bolinhas para mudar o tamanho. Com o Ímã ligado ela gruda nas outras partes.</p></div>
    ${campoNum('w', 'Largura', p.w, 1, 400, .5, ' m')}${campoNum('d', 'Profundidade', p.d, 1, 400, .5, ' m')}
    <details class="cm-det"><summary>Posição dentro do prédio</summary>${campoNum('x', 'Leste–oeste', p.x, -200, 200, .5, ' m')}${campoNum('z', 'Norte–sul', p.z, -200, 200, .5, ' m')}</details>
    <button class="cm-btn" data-acao="org" type="button" ${dentro.length ? '' : 'disabled'}>Organizar as ${dentro.length} salas desta parte em fila</button>
    ${htmlFim}`;
  body.querySelector('[data-acao="volta"]').onclick = () => { sel = { t: 'bloco', id: b.id }; draw(); renderPanel(); };
  ligaCampos(p, () => { syncPredio(b); });
  body.querySelector('[data-acao="org"]').onclick = () => {
    snapH(); const horiz = p.w >= p.d, ordem = dentro.slice().sort((a, c) => (horiz ? a.x - c.x : a.z - c.z) || (horiz ? a.z - c.z : a.x - c.x));
    filaSalas(p, ordem.length, 'dois', 3).forEach((g, i) => Object.assign(ordem[i], g)); changed(); toast('Salas organizadas.');
  };
  body.querySelector('[data-acao="dup"]').onclick = duplicar;
  body.querySelector('[data-acao="del"]').onclick = (e) => armar(e.currentTarget, 'Excluir', excluir, 'Toque de novo para excluir');
}
function panelSala() {
  const s = salaSel(); if (!s) { sel = null; return renderPanel(); }
  const { b, r } = s;
  if (r.andar !== floor) { floor = r.andar; renderFloors(); draw(); }
  body.innerHTML = `<button class="cm-back" data-acao="volta" type="button">← ${esc(b.nome)}</button>
    <div><div class="cm-eyebrow">${esc(b.nome)} · ${ANDARES[r.andar]}</div><h2 class="cm-h">${esc(r.n || 'Sala sem nome')}</h2>
    <p class="cm-hint">Arraste a sala no mapa até o lugar exato e puxe as bolinhas para o tamanho. A porta é o quadradinho vermelho: arraste pela parede.</p></div>
    ${rotaPara({ b, r }).bloqueada ? '<p class="cm-aviso">⚠ Não tem corredor livre do portão até a porta desta sala (as salas em volta fecham a passagem). A rota vai atravessar uma sala. Deixe um espaço de corredor ou mude a porta de parede.</p>' : ''}
    <div class="cm-field"><label>Nome da sala<input class="cm-input" data-campo="sala" type="text" value="${esc(r.n)}" autocomplete="off"></label></div>
    <div class="cm-field"><label>Tipo de sala<select class="cm-input" data-campo="cat">${Object.keys(CATS).map((c) => `<option value="${c}" ${r.cat === c ? 'selected' : ''}>${esc(CATS[c][0])}</option>`).join('')}</select></label></div>
    <div class="cm-field"><span class="cm-lab">Cor</span><div class="cm-cor"><input type="color" data-campo="cor" value="${corHex(r.cor) || '#d9eaf8'}"><button class="cm-btn sm" data-acao="corpadrao" type="button" ${r.cor ? '' : 'disabled'}>Cor do tipo</button></div></div>
    ${setores.length ? `<div class="cm-field"><label>Página do setor (botão "Ver página do setor")<select class="cm-input" data-campo="setor"><option value="">— nenhuma —</option>${setores.map((x) => `<option value="${esc(x.id)}" ${r.setor === x.id ? 'selected' : ''}>${esc(x.nome)}</option>`).join('')}</select></label></div>` : ''}
    ${campoNum('w', 'Largura', r.w, .5, 120, .25, ' m')}${campoNum('d', 'Profundidade', r.d, .5, 120, .25, ' m')}
    <div class="cm-field"><span class="cm-lab">Parede da porta</span>${seg('plado', [['N', 'Norte ↑'], ['S', 'Sul ↓'], ['O', 'Oeste ←'], ['L', 'Leste →']], r.porta.lado)}</div>
    ${campoNum('pt', 'Posição da porta na parede', r.porta.t, .05, .95, .05, '')}
    ${b.andares > 1 ? `<div class="cm-field"><span class="cm-lab">Andar</span>${seg('andar', ANDARES.slice(0, b.andares).map((n, i) => [i, n]), r.andar)}</div>` : ''}
    <details class="cm-det"><summary>Posição exata dentro do prédio</summary>${campoNum('x', 'Leste–oeste', r.x, -200, 200, .25, ' m')}${campoNum('z', 'Norte–sul', r.z, -200, 200, .25, ' m')}</details>
    <div class="cm-field"><label>Mudar para outro prédio<select class="cm-input" data-campo="predio">${S.blocos.filter((x) => x.tipo === 'predio').map((x) => `<option value="${esc(x.id)}" ${x === b ? 'selected' : ''}>${esc(x.nome)}</option>`).join('')}</select></label></div>
    ${htmlFim}`;
  const q = (x) => body.querySelector(x);
  q('[data-acao="volta"]').onclick = () => { sel = { t: 'bloco', id: b.id }; draw(); renderPanel(); };
  const rn = q('[data-campo="sala"]'); rn.onfocus = snapH; rn.oninput = () => { r.n = rn.value; save(); draw(); q('.cm-h').textContent = r.n || 'Sala sem nome'; };
  rn.onchange = () => { if (r.cat === 'outro' || r.cat === 'aula') { const c = adivinhaCat(r.n); if (c !== 'outro' && c !== r.cat) { r.cat = c; save(); draw(); q('[data-campo="cat"]').value = c; } } };
  q('[data-campo="cat"]').onchange = (e) => { snapH(); r.cat = e.target.value; save(); draw(); };
  const cor = q('[data-campo="cor"]'); cor.onfocus = snapH; cor.oninput = () => { r.cor = cor.value; save(); draw(); q('[data-acao="corpadrao"]').disabled = false; };
  q('[data-acao="corpadrao"]').onclick = () => { snapH(); delete r.cor; changed(); };
  const st = q('[data-campo="setor"]'); if (st) st.onchange = () => { snapH(); r.setor = st.value || null; save(); };
  const proxy = { w: r.w, d: r.d, x: r.x, z: r.z, pt: r.porta.t };
  ligaCampos(proxy, (k, v) => { if (k === 'pt') r.porta.t = v; else r[k] = v; });
  ligaSeg('plado', (v) => { snapH(); r.porta.lado = v; changed(); });
  ligaSeg('andar', (v) => { snapH(); r.andar = +v; floor = +v; changed(); });
  q('[data-campo="predio"]').onchange = (e) => {
    const nb = blocoById(e.target.value); if (!nb || nb === b) return; snapH();
    b.salas = b.salas.filter((x) => x !== r); r.andar = Math.min(r.andar, nb.andares - 1); const p = nb.partes[0]; r.x = p.x; r.z = p.z;
    nb.salas.push(r); sel = { t: 'sala', b: nb.id, id: r.id }; floor = r.andar; changed(); focar({ b: nb }, 50); toast('Sala levada. Agora arraste até o lugar certo.');
  };
  q('[data-acao="dup"]').onclick = duplicar;
  q('[data-acao="del"]').onclick = (e) => armar(e.currentTarget, 'Excluir', excluir, 'Toque de novo para excluir');
}
function marcarSujo() { const bt = root.querySelector('.cm-tabs [data-t="data"]'); if (bt) bt.textContent = (op.salvarUrl ? 'Salvar' : 'Arquivo') + (sujo && op.salvarUrl ? ' •' : ''); }
// No Claude (página publicada) o download passa pelo "downloads" da plataforma; no site, link normal.
let dlCap = null;
if (window.claude && typeof window.claude.use === 'function') window.claude.use('downloads').then((d) => { dlCap = d; }).catch(() => {});
function baixar(nome, conteudo, tipo) {
  if (dlCap) { dlCap.save({ filename: nome, data: conteudo }).then(() => toast('Arquivo salvo.'), (e) => { if (e && e.code !== 'declined') toast('Não deu para baixar aqui. Use "Copiar dados".'); }); return; }
  const blob = conteudo instanceof Blob ? conteudo : new Blob([conteudo], { type: tipo || 'application/json' }), a = document.createElement('a');
  a.href = URL.createObjectURL(blob); a.download = nome; document.body.appendChild(a); a.click(); a.remove(); setTimeout(() => URL.revokeObjectURL(a.href), 4000);
  toast('Arquivo baixado.');
}
function carregarObjeto(o) {
  if (!valid(o)) throw new Error('inválido');
  const foto = o.fundo; snapH(); S = normalizar(o); sel = null;
  if (foto && foto.src) { FUNDO = foto; saveFoto(); }
  changed(); animVB(fit());
}
function panelData() {
  const site = !!op.salvarUrl;
  body.innerHTML = `${site ? `<div><div class="cm-eyebrow">Salvar</div><h2 class="cm-h">${sujo ? 'Há mudanças não salvas' : 'Tudo salvo no site'}</h2><p class="cm-hint">${sujo ? 'Suas mudanças estão guardadas só neste navegador. Toque em <b>Salvar no site</b> para todo mundo ver o mapa novo.' : 'O mapa que aparece no site é este.'}</p></div>
    <button class="cm-btn pri" data-acao="salvar" type="button" ${sujo ? '' : 'disabled'}>Salvar no site</button>
    <button class="cm-btn" data-acao="descartar" type="button" ${sujo ? '' : 'disabled'}>Descartar mudanças não salvas</button><hr class="cm-hr">` :
    `<div><div class="cm-eyebrow">Arquivo</div><h2 class="cm-h">Seu mapa fica salvo neste navegador</h2><p class="cm-hint">Tudo que você muda é guardado sozinho aqui. Para levar para outro computador ou para o site (Admin → Editor do mapa → Salvar → Abrir arquivo), baixe o arquivo ou copie os dados.</p></div>`}
    <div class="cm-row2"><button class="cm-btn pri" data-acao="baixar" type="button">⬇ Baixar mapa (.json)</button><label class="cm-btn cm-file">⬆ Abrir arquivo<input type="file" accept=".json,application/json" data-acao="abrir" hidden></label></div>
    <label class="cm-check"><input type="checkbox" data-acao="comfoto" ${FUNDO ? 'checked' : 'disabled'}> Incluir a foto de fundo no arquivo</label>
    <button class="cm-btn" data-acao="png" type="button">🖼 Baixar imagem da planta (PNG, ${ANDARES[floor]})</button>
    <hr class="cm-hr">
    <div><div class="cm-eyebrow">Copiar e colar</div><p class="cm-hint">Também dá para copiar os dados como texto e colar em outro editor do mapa (mapas da versão antiga são convertidos sozinhos).</p></div>
    <div class="cm-field"><label>Dados do mapa<textarea class="cm-input cm-box" spellcheck="false">${esc(JSON.stringify(S))}</textarea></label></div>
    <div class="cm-row2"><button class="cm-btn" data-acao="carregar" type="button">Carregar o texto colado</button><button class="cm-btn" data-acao="copiar" type="button">Copiar dados</button></div>
    <hr class="cm-hr">
    <div><div class="cm-eyebrow">Trazer as salas de outro mapa</div><p class="cm-hint">Mantém a planta deste mapa e traz só as <b>salas</b> (e prédios que faltarem) de um mapa que vocês já editaram. Cada sala vai para a mesma parte do prédio.</p></div>
    ${antigos().length ? `<button class="cm-btn pri" data-acao="impantigo" type="button">Trazer as salas que eu já tinha feito neste navegador</button>` : ''}
    <div class="cm-row2"><label class="cm-btn cm-file">De um arquivo .json<input type="file" accept=".json,application/json" data-acao="impfile" hidden></label><button class="cm-btn" data-acao="imptexto" type="button">Do texto colado acima</button></div>
    <hr class="cm-hr">
    <button class="cm-btn danger" data-acao="original" type="button">Voltar ao mapa original</button>`;
  const q = (s) => body.querySelector(s);
  if (site) {
    q('[data-acao="salvar"]').onclick = async (e) => {
      const bt = e.currentTarget; bt.disabled = true; bt.textContent = 'Salvando…';
      try {
        const resp = await fetch(op.salvarUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': op.csrf }, body: JSON.stringify({ csrf_token: op.csrf, dados: S }) });
        const res = await resp.json().catch(() => ({}));
        if (!resp.ok || !res.ok) throw new Error(res.mensagem || 'Não foi possível salvar.');
        S = normalizar(res.dados || S); ORIG.v = JSON.stringify(S); hist = []; futuro = [];
        save(); renderFloors(); draw(); renderPanel(); toast('Mapa salvo no site.');
      } catch (er) { toast(er.message || 'Erro ao salvar.'); bt.disabled = false; bt.textContent = 'Salvar no site'; }
    };
    q('[data-acao="descartar"]').onclick = (e) => armar(e.currentTarget, 'Descartar mudanças não salvas', () => { snapH(); S = JSON.parse(ORIG.v); sel = null; changed(); toast('Mudanças descartadas.'); }, 'Toque de novo para descartar');
  }
  q('[data-acao="baixar"]').onclick = () => { const o = clone(S); if (FUNDO && q('[data-acao="comfoto"]').checked) o.fundo = FUNDO; baixar(`mapa-campus-${new Date().toISOString().slice(0, 10)}.json`, JSON.stringify(o, null, 1)); };
  q('[data-acao="abrir"]').onchange = (e) => { const f = e.target.files && e.target.files[0]; if (!f) return; f.text().then((t) => { carregarObjeto(JSON.parse(t)); toast('Mapa aberto.' + (site ? ' Agora toque em Salvar no site.' : '')); }).catch(() => toast('Esse arquivo não é um mapa válido.')); };
  q('[data-acao="png"]').onclick = exportarPNG;
  q('[data-acao="carregar"]').onclick = () => { try { carregarObjeto(JSON.parse(q('.cm-box').value)); toast('Mapa carregado.' + (site ? ' Agora toque em Salvar no site.' : '')); } catch (er) { toast('Texto inválido. Cole os dados copiados do editor.'); } };
  q('[data-acao="copiar"]').onclick = () => { const t = JSON.stringify(S); (navigator.clipboard ? navigator.clipboard.writeText(t) : Promise.reject()).then(() => toast('Dados copiados.'), () => { const bx = q('.cm-box'); bx.value = t; bx.select(); toast('Selecionei o texto. Copie com Ctrl+C.'); }); };
  const imp = (o) => { try { importarSalas(o); } catch (er) { toast('Não deu para ler esse mapa.'); } };
  const ia = q('[data-acao="impantigo"]'); if (ia) ia.onclick = () => imp(antigos()[0]);
  q('[data-acao="impfile"]').onchange = (e) => { const f = e.target.files && e.target.files[0]; if (f) f.text().then((t) => imp(JSON.parse(t))).catch(() => toast('Esse arquivo não é um mapa válido.')); };
  q('[data-acao="imptexto"]').onclick = () => { try { imp(JSON.parse(q('.cm-box').value)); } catch (er) { toast('Cole os dados do outro mapa na caixa acima.'); } };
  q('[data-acao="original"]').onclick = (e) => armar(e.currentTarget, 'Voltar ao mapa original', () => { snapH(); S = normalizar(clone(op.dados)); sel = null; changed(); animVB(fit()); toast('Mapa original de volta. Dá para desfazer.'); }, 'Toque de novo para voltar ao original');
}
// rascunhos de versões anteriores deste editor, guardados neste navegador
function antigos() {
  const out = [];
  (op.rascunhosAntigos || []).forEach((k) => { try { const r = localStorage.getItem(k); if (r) { const o = JSON.parse(r); if (valid(o) && o.blocos.some((b) => b.salas && b.salas.length)) out.push(o); } } catch (e) {} });
  return out;
}
function importarSalas(o) {
  if (!valid(o)) throw new Error('inválido');
  const src = normalizar(clone(o)), achaPar = (t) => src.blocos.find((b) => b.tipo === 'predio' && b.id === t.id) || src.blocos.find((b) => b.tipo === 'predio' && norm(b.nome).trim() === norm(t.nome).trim());
  snapH();
  let nSalas = 0, nPred = 0;
  const usados = new Set();
  S.blocos.filter((t) => t.tipo === 'predio').forEach((t) => {
    const sb = achaPar(t); if (!sb || !sb.salas.length) return; usados.add(sb.id);
    const mesmo = sb.partes.length === t.partes.length, es = ext(sb), et = ext(t);
    t.andares = Math.max(t.andares, sb.andares);
    t.salas = sb.salas.map((r) => {
      const n = clone(r);
      let sp = null, tp = null;
      if (mesmo) { const i = sb.partes.findIndex((p) => emRet(p, r.x, r.z)); if (i >= 0) { sp = sb.partes[i]; tp = t.partes[i]; } }
      if (!sp) { sp = { x: (es.x0 + es.x1) / 2, z: (es.z0 + es.z1) / 2, w: es.x1 - es.x0, d: es.z1 - es.z0 }; tp = { x: (et.x0 + et.x1) / 2, z: (et.z0 + et.z1) / 2, w: et.x1 - et.x0, d: et.z1 - et.z0 }; }
      n.x = r2(tp.x + (r.x - sp.x) / sp.w * tp.w); n.z = r2(tp.z + (r.z - sp.z) / sp.d * tp.d); n.w = r2(r.w * tp.w / sp.w); n.d = r2(r.d * tp.d / sp.d);
      if (S.blocos.some((b) => b !== t && b.salas.some((x) => x.id === n.id))) n.id = uid();
      nSalas++; return n;
    });
  });
  // prédios, pontos e textos que existem no outro mapa e não existem aqui
  src.blocos.forEach((b) => {
    if (usados.has(b.id) || !['predio', 'ponto', 'rotulo'].includes(b.tipo)) return;
    if (S.blocos.some((x) => x.id === b.id || (x.tipo === b.tipo && norm(x.nome).trim() === norm(b.nome).trim()))) return;
    const c = clone(b); c.id = uid(); S.blocos.push(c); nPred++;
  });
  sel = null; changed();
  toast(`${nSalas} salas trazidas${nPred ? ` e ${nPred} itens novos` : ''}. Confira e arraste o que precisar.`);
}
function exportarPNG() {
  const salvaVB = { ...vb }, salvaSel = sel, salvaTab = tab, l = S.limite;
  sel = null; tab = 'find'; vb = { x: l.x0 - 20, y: l.z0 - 16, w: l.x1 - l.x0 + 40, h: l.z1 - l.z0 + 40 };
  draw(); setVB(); svg.classList.remove('cm-far');
  const cs = getComputedStyle(root), cl = svg.cloneNode(true);
  const resolve = (s) => s.replace(/var\((--[\w-]+)\)/g, (m, n) => cs.getPropertyValue(n).trim() || '#999');
  cl.querySelectorAll('*').forEach((n) => { for (const at of [...n.attributes]) if (at.value.includes('var(')) n.setAttribute(at.name, resolve(at.value)); });
  const st = document.createElementNS(NS, 'style');
  st.textContent = resolve(`text{font-family:${cs.fontFamily}} .cm-rlbl{font-weight:700;fill:var(--cm-fg);text-anchor:middle} .cm-blbl{font-weight:800;text-anchor:middle;fill:var(--cm-fg);paint-order:stroke;stroke:var(--cm-surface);stroke-width:.9px;stroke-linejoin:round} .cm-alb{font-weight:800;text-anchor:middle;fill:var(--cm-fg);opacity:.78;letter-spacing:.06em;text-transform:uppercase} .cm-rotulo{font-weight:800;text-anchor:middle} .cm-emoji{text-anchor:middle}`);
  cl.insertBefore(st, cl.firstChild);
  const k = 10, W2 = Math.round(vb.w * k), H2 = Math.round(vb.h * k);
  cl.setAttribute('width', W2); cl.setAttribute('height', H2); cl.removeAttribute('class');
  vb = salvaVB; sel = salvaSel; tab = salvaTab; setVB(); draw();
  const url = URL.createObjectURL(new Blob([new XMLSerializer().serializeToString(cl)], { type: 'image/svg+xml' })), img = new Image();
  img.onload = () => {
    const cv = document.createElement('canvas'); cv.width = W2; cv.height = H2; const cx = cv.getContext('2d');
    cx.fillStyle = cs.getPropertyValue('--cm-grass-2').trim() || '#bcd8ae'; cx.fillRect(0, 0, W2, H2); cx.drawImage(img, 0, 0);
    URL.revokeObjectURL(url);
    cv.toBlob((bl) => { if (bl) baixar(`planta-campus-${ANDARES[floor].replace(/\W+/g, '-').toLowerCase()}.png`, bl, 'image/png'); else toast('Não deu para gerar a imagem.'); }, 'image/png');
  };
  img.onerror = () => { URL.revokeObjectURL(url); toast('Não deu para gerar a imagem neste navegador.'); };
  img.src = url;
}

/* =====================================================================
   3D (three.js r128, carregado só quando abre o 3D)
   ===================================================================== */
let T3 = null;
const loadScript = (src) => new Promise((ok, bad) => { const s = document.createElement('script'); s.src = src; s.onload = ok; s.onerror = bad; document.head.appendChild(s); });
async function setMode(m) {
  if (m === mode) return;
  if (m === '3d' && tab === 'edit') trocaAba('find');
  mode = m; root.querySelectorAll('.cm-modes button').forEach((x) => { x.classList.toggle('on', x.dataset.m === m); x.setAttribute('aria-pressed', String(x.dataset.m === m)); });
  stage.classList.toggle('m3d', m === '3d'); $('.cm-view3').hidden = m !== '3d';
  const eb = $('.cm-edbar'); if (eb) eb.hidden = tab !== 'edit' || m !== '2d';
  if (m === '2d') { draw(); return; }
  if (!T3) {
    $('.cm-loading').style.display = 'grid';
    try {
      for (const src of (op.scripts3d || [])) { if (/three(\.min)?\.js/.test(src) && window.THREE) continue; if (/OrbitControls/.test(src) && window.THREE && THREE.OrbitControls) continue; if (/CSS2DRenderer/.test(src) && window.THREE && THREE.CSS2DRenderer) continue; await loadScript(src); }
      init3();
    } catch (e) { toast(L('Não deu para carregar o 3D. Confira a internet.', 'No se pudo cargar el 3D. Revise la conexión.')); $('.cm-loading').style.display = 'none'; mode = '3d'; return setMode('2d'); }
    $('.cm-loading').style.display = 'none';
  }
  update3(true);
}
root.querySelectorAll('.cm-modes button').forEach((b) => b.onclick = () => setMode(b.dataset.m));
root.querySelectorAll('.cm-view3 button').forEach((b) => b.onclick = () => { view3 = b.dataset.v; root.querySelectorAll('.cm-view3 button').forEach((x) => x.classList.toggle('on', x === b)); update3(false); });
function cssVar(n) { return getComputedStyle(root).getPropertyValue(n).trim(); }
function cor(v) { let s = String(v || '#cccccc'); for (let i = 0; i < 3 && s.startsWith('var('); i++) s = cssVar(s.slice(4, -1)) || '#cccccc'; try { return new THREE.Color(s).convertSRGBToLinear(); } catch (e) { return new THREE.Color('#cccccc'); } }
function init3() {
  const box = $('.cm-three');
  const renderer = new THREE.WebGLRenderer({ antialias: true, preserveDrawingBuffer: true });
  renderer.setPixelRatio(Math.min(2, devicePixelRatio)); renderer.outputEncoding = THREE.sRGBEncoding;
  renderer.toneMapping = THREE.ACESFilmicToneMapping; renderer.toneMappingExposure = 1.05;
  renderer.shadowMap.enabled = true; renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  box.appendChild(renderer.domElement);
  const labels = new THREE.CSS2DRenderer(); labels.domElement.style.cssText = 'position:absolute;inset:0;pointer-events:none'; box.appendChild(labels.domElement);
  const scene = new THREE.Scene(), camera = new THREE.PerspectiveCamera(40, 1, 1, 3000);
  const controls = new THREE.OrbitControls(camera, renderer.domElement);
  controls.enableDamping = true; controls.maxPolarAngle = Math.PI * .47; controls.minDistance = 20; controls.maxDistance = 900;
  scene.add(new THREE.HemisphereLight(0xdfefff, 0x5c7a4e, .75));
  const sun = new THREE.DirectionalLight(0xfff1dc, 1.05); sun.position.set(-140, 220, 160); sun.castShadow = true;
  sun.shadow.mapSize.set(2048, 2048); Object.assign(sun.shadow.camera, { left: -260, right: 260, top: 260, bottom: -260, far: 800 }); sun.shadow.bias = -0.0006; scene.add(sun, sun.target);
  const world = new THREE.Group(); scene.add(world);
  const ray = new THREE.Raycaster(), mouse = new THREE.Vector2();
  T3 = { renderer, labels, scene, camera, controls, world, sun, picks: [], roomLabels: [], route: null, mats: {} };
  const l = S.limite, cx = (l.x0 + l.x1) / 2, cz = (l.z0 + l.z1) / 2; controls.target.set(cx, 0, cz); camera.position.set(cx - 80, 230, cz + 300);
  let down = null;
  renderer.domElement.addEventListener('pointerdown', (e) => { down = [e.clientX, e.clientY]; });
  renderer.domElement.addEventListener('pointerup', (e) => {
    if (!down || Math.hypot(e.clientX - down[0], e.clientY - down[1]) > 6) return;
    const r = renderer.domElement.getBoundingClientRect(); mouse.set(((e.clientX - r.left) / r.width) * 2 - 1, -((e.clientY - r.top) / r.height) * 2 + 1);
    ray.setFromCamera(mouse, camera); const hit = ray.intersectObjects(T3.picks, false)[0];
    if (!hit) return; const u = hit.object.userData, b = blocoById(u.b);
    if (u.r) pickSala(b, b.salas.find((x) => x.id === u.r)); else if (b && roteavel(b)) pickBloco(b);
  });
  resize3();
  const clock = new THREE.Clock(), tmp = new THREE.Vector3();
  const loop = () => {
    requestAnimationFrame(loop);
    if (mode !== '3d') return;
    controls.update();
    const t = clock.getElapsedTime();
    if (T3.route) { const k = (t * .12) % 1.15; T3.route.dot.position.copy(T3.route.curve.getPointAt(Math.min(1, k))); T3.route.dot.visible = k <= 1; T3.route.tex.offset.x = -t * .9; if (T3.route.ring) { const q = (t % 1.6) / 1.6; T3.route.ring.scale.setScalar(1 + q * 3); T3.route.ring.material.opacity = 1 - q; } }
    T3.roomLabels.forEach((o) => { o.visible = camera.position.distanceTo(o.getWorldPosition(tmp)) < 150; });
    renderer.render(scene, camera); labels.render(scene, camera);
  };
  loop();
}
function resize3() { if (!T3) return; const r = $('.cm-three').getBoundingClientRect(); if (!r.width) return; T3.renderer.setSize(r.width, r.height); T3.labels.setSize(r.width, r.height); T3.camera.aspect = r.width / r.height; T3.camera.updateProjectionMatrix(); }
function z3(f) { const c = T3.controls, v = T3.camera.position.clone().sub(c.target).multiplyScalar(f); T3.camera.position.copy(c.target).add(v); }
function home3() { const l = S.limite, cx = (l.x0 + l.x1) / 2, cz = (l.z0 + l.z1) / 2, s = Math.max(l.x1 - l.x0, l.z1 - l.z0) / 254; flyTo(new THREE.Vector3(cx, 0, cz), new THREE.Vector3(cx - 80 * s, 230 * s, cz + 300 * s)); }
function flyTo(target, pos) {
  const c = T3.controls, t0 = performance.now(), fT = c.target.clone(), fP = T3.camera.position.clone();
  const st = (now) => { const k = Math.min(1, (now - t0) / 900), e = k < .5 ? 4 * k * k * k : 1 - Math.pow(-2 * k + 2, 3) / 2; c.target.lerpVectors(fT, target, e); T3.camera.position.lerpVectors(fP, pos, e); if (k < 1) requestAnimationFrame(st); };
  requestAnimationFrame(st);
}
function label3(text, cls, pos, pai) { const d = document.createElement('div'); d.className = 'cm-css2d ' + (cls || ''); d.textContent = text; const o = new THREE.CSS2DObject(d); o.position.copy(pos); (pai || T3.world).add(o); return o; }
function mat(c, extra) {
  const key = String(c) + (extra ? JSON.stringify(extra) : '');
  if (!T3.mats[key]) T3.mats[key] = new THREE.MeshStandardMaterial(Object.assign({ color: cor(c), roughness: .9, metalness: 0 }, extra || {}));
  return T3.mats[key];
}
function caixa(x0, x1, z0, z1, y0, h, m, cast, pai) {
  const me = new THREE.Mesh(new THREE.BoxGeometry(Math.max(.01, x1 - x0), Math.max(.01, h), Math.max(.01, z1 - z0)), m);
  me.position.set((x0 + x1) / 2, y0 + h / 2, (z0 + z1) / 2); me.castShadow = cast !== false; me.receiveShadow = true; (pai || T3.world).add(me); return me;
}
// grupo do bloco: dentro dele as coordenadas são locais (centro do bloco = 0,0)
function grupo3(b) { const g = new THREE.Group(); g.position.set(b.x, 0, b.z); g.rotation.y = -rad(b.rot || 0); T3.world.add(g); return g; }
function faixa(pts, larg, y, m) {
  for (let i = 1; i < pts.length; i++) {
    const [ax, az] = pts[i - 1], [bx, bz] = pts[i], len = Math.hypot(bx - ax, bz - az); if (len < .05) continue;
    const me = new THREE.Mesh(new THREE.BoxGeometry(len + larg * .9, .08, larg), m); me.position.set((ax + bx) / 2, y, (az + bz) / 2); me.rotation.y = -Math.atan2(bz - az, bx - ax); me.receiveShadow = true; T3.world.add(me);
  }
}
function build3() {
  const W3 = T3.world;
  const limpar = (o) => { o.children.slice().forEach(limpar); if (o.element && o.element.remove) o.element.remove(); if (o.geometry) o.geometry.dispose(); };
  W3.children.slice().forEach((o) => { limpar(o); W3.remove(o); });
  Object.values(T3.mats).forEach((m) => { if (m.map) m.map.dispose(); m.dispose(); }); T3.mats = {};
  T3.picks = []; T3.roomLabels = [];
  const l = S.limite;
  const chao = new THREE.Mesh(new THREE.PlaneGeometry(2400, 2400), mat('var(--cm-grass-2)')); chao.rotation.x = -Math.PI / 2; chao.position.set((l.x0 + l.x1) / 2, -.05, (l.z0 + l.z1) / 2); chao.receiveShadow = true; W3.add(chao);
  const terreno = new THREE.Mesh(new THREE.PlaneGeometry(l.x1 - l.x0, l.z1 - l.z0), mat('var(--cm-grass)')); terreno.rotation.x = -Math.PI / 2; terreno.position.set((l.x0 + l.x1) / 2, 0, (l.z0 + l.z1) / 2); terreno.receiveShadow = true; W3.add(terreno);
  const rua = mat('var(--cm-street)'), E0 = l.x0 - 1000, E1 = l.x1 + 1000, N0 = l.z0 - 1000, N1 = l.z1 + 1000;
  [[E0, E1, l.z1 + 6, l.z1 + 22], [E0, E1, l.z0 - 12, l.z0 - 2], [l.x0 - 16, l.x0 - 6, N0, N1], [l.x1 + 8, l.x1 + 18, N0, N1]].forEach(([a, b2, c, d]) => caixa(a, b2, c, d, -.04, .06, rua, false));
  const muro = mat('#c9d1d8'), mm = .4, G = S.portao;
  caixa(l.x0, l.x1, l.z0 - mm, l.z0 + mm, 0, 1.6, muro); caixa(l.x0 - mm, l.x0 + mm, l.z0, l.z1, 0, 1.6, muro); caixa(l.x1 - mm, l.x1 + mm, l.z0, l.z1, 0, 1.6, muro);
  if (G.x - 7 > l.x0) caixa(l.x0, G.x - 7, l.z1 - mm, l.z1 + mm, 0, 1.6, muro); if (G.x + 7 < l.x1) caixa(G.x + 7, l.x1, l.z1 - mm, l.z1 + mm, 0, 1.6, muro);
  caixa(G.x - 8, G.x - 6, G.z - 1, G.z + 1, 0, 4, mat('#e9edf0')); caixa(G.x + 6, G.x + 8, G.z - 1, G.z + 1, 0, 4, mat('#e9edf0')); caixa(G.x - 8, G.x + 8, G.z - .6, G.z + .6, 4, .8, mat('#cd191e'));
  label3(L('Portão', 'Portón'), '', new THREE.Vector3(G.x, 7, G.z));
  S.blocos.filter((b) => b.tipo === 'caminho').forEach((b) => faixa(amostraCaminho(b, 1.5), b.larg, .05, mat(b.cor || 'var(--cm-path)')));
  const copas = [];
  S.blocos.forEach((b) => {
    if (b.tipo === 'arvore') copas.push([b.x, b.z, b.w / 2]);
    else if (b.tipo === 'mata') { const r = rng(b.id), n = Math.min(300, Math.round(b.w * b.d / 70)); for (let i = 0; i < n; i++) { const p = Wl(b, -b.w / 2 + 3 + r() * Math.max(0, b.w - 6), -b.d / 2 + 3 + r() * Math.max(0, b.d - 6)); copas.push([p.x, p.z, 2.2 + r() * 2.2]); } }
  });
  if (copas.length) {
    const iC = new THREE.InstancedMesh(new THREE.IcosahedronGeometry(1, 1), mat('#4f9a55', { flatShading: true }), copas.length), iT = new THREE.InstancedMesh(new THREE.CylinderGeometry(.35, .5, 1, 6), mat('#6b4f36'), copas.length), M = new THREE.Matrix4(), q = new THREE.Quaternion();
    copas.forEach(([x, z, r], i) => { const h = r * 1.4; M.compose(new THREE.Vector3(x, h + r * .7, z), q, new THREE.Vector3(r, r * .9, r)); iC.setMatrixAt(i, M); M.compose(new THREE.Vector3(x, h / 2 + .2, z), q, new THREE.Vector3(1, h + .4, 1)); iT.setMatrixAt(i, M); });
    iC.castShadow = iT.castShadow = true; W3.add(iC, iT);
  }
  const coresArea = { estac: '#9aa1aa', campo: '#6fb05f', quadra: '#e6c77a', verde: 'var(--cm-grass-2)', obra: '#e3b53f', mata: '#3f7d45' };
  S.blocos.forEach((b) => {
    if (!coresArea[b.tipo]) return;
    const g = grupo3(b), x0 = -b.w / 2, x1 = b.w / 2, z0 = -b.d / 2, z1 = b.d / 2;
    const me = caixa(x0, x1, z0, z1, 0, b.tipo === 'quadra' ? .2 : .1, mat(b.cor || coresArea[b.tipo]), false, g); me.userData = { b: b.id }; T3.picks.push(me);
    if (b.tipo === 'estac') { const br = mat('#f4f4f4'); for (let i = x0 + 4; i < x1 - 3; i += 3) { caixa(i - .07, i + .07, z0 + 2, z0 + Math.min(7, b.d / 3), .1, .03, br, false, g); caixa(i - .07, i + .07, z1 - Math.min(7, b.d / 3), z1 - 2, .1, .03, br, false, g); } }
    if (b.tipo === 'campo') {
      const br = mat('#f4f4f4'); caixa(-.15, .15, z0 + 3, z1 - 3, .1, .03, br, false, g); caixa(x0 + 3, x1 - 3, z0 + 2.85, z0 + 3.15, .1, .03, br, false, g); caixa(x0 + 3, x1 - 3, z1 - 3.15, z1 - 2.85, .1, .03, br, false, g); caixa(x0 + 2.85, x0 + 3.15, z0 + 3, z1 - 3, .1, .03, br, false, g); caixa(x1 - 3.15, x1 - 2.85, z0 + 3, z1 - 3, .1, .03, br, false, g);
      const gol = mat('#ffffff'); [x0 + 3, x1 - 3].forEach((gx) => { caixa(gx - .1, gx + .1, -3.6, -3.4, 0, 2.4, gol, true, g); caixa(gx - .1, gx + .1, 3.4, 3.6, 0, 2.4, gol, true, g); caixa(gx - .1, gx + .1, -3.6, 3.6, 2.3, .15, gol, true, g); });
    }
    if (b.tipo === 'quadra') { const v = b.d > b.w; caixa(v ? x0 : -.08, v ? x1 : .08, v ? -.08 : z0, v ? .08 : z1, 0, 2.4, mat('#ffffff', { transparent: true, opacity: .55 }), true, g); }
    if (b.nome) label3(b.nome, '', new THREE.Vector3(0, b.tipo === 'mata' ? 14 : 4, 0), g);
  });
  S.blocos.filter((b) => b.tipo === 'ponto').forEach((b) => {
    const pp = predioDoPonto(b);
    if (pp && view3 !== 'fora' && (b.andar || 0) !== Math.min(floor, pp.andares - 1)) return;
    if (pp && view3 === 'fora') return;
    const base = pp ? (b.andar || 0) * ANDAR_H : 0, h = pp ? 2.4 : 5;
    const haste = new THREE.Mesh(new THREE.CylinderGeometry(.15, .15, h, 8), mat('#e9edf0')); haste.position.set(b.x, base + h / 2, b.z); haste.castShadow = true; T3.world.add(haste);
    const bola = new THREE.Mesh(new THREE.SphereGeometry(pp ? .6 : 1, 16, 12), mat(b.cor || 'var(--cm-accent)')); bola.position.set(b.x, base + h + .6, b.z); bola.userData = { b: b.id }; bola.castShadow = true; T3.world.add(bola); T3.picks.push(bola);
    label3(`${b.icone || ''} ${b.nome}`.trim(), '', new THREE.Vector3(b.x, base + h + 2.4, b.z));
  });
  S.blocos.filter((b) => b.tipo === 'rotulo').forEach((b) => label3(b.nome, 'rot3', new THREE.Vector3(b.x, 1, b.z)));
  S.blocos.filter((b) => b.tipo === 'patio').forEach((b) => {
    const g = grupo3(b), hp = hexPts(b.w, b.d), shape = new THREE.Shape(hp.map(([x, z]) => new THREE.Vector2(x, z)));
    const piso = new THREE.Mesh(new THREE.ShapeGeometry(shape), mat('#d8d2c4')); piso.rotation.x = Math.PI / 2; piso.position.y = .12; piso.receiveShadow = true; g.add(piso);
    const teto = new THREE.Mesh(new THREE.ExtrudeGeometry(shape, { depth: .5, bevelEnabled: false }), mat(b.cor || '#cfd6dc', { transparent: view3 === 'dentro', opacity: view3 === 'dentro' ? .45 : 1 }));
    teto.rotation.x = Math.PI / 2; teto.position.y = 6.5; teto.castShadow = true; teto.userData = { b: b.id }; g.add(teto); T3.picks.push(teto);
    const pil = mat('#e9edf0'); hp.forEach(([x, z]) => caixa(x - .3, x + .3, z - .3, z + .3, 0, 6, pil, true, g));
    for (let x = -b.w / 2 + b.d / 2; x <= b.w / 2 - b.d / 2 + .1; x += 10) { caixa(x - .3, x + .3, -b.d / 2 - .3, -b.d / 2 + .3, 0, 6, pil, true, g); caixa(x - .3, x + .3, b.d / 2 - .3, b.d / 2 + .3, 0, 6, pil, true, g); }
    label3(b.nome, '', new THREE.Vector3(0, 9, 0), g);
  });
  const vidro = mat('#7fa6c4', { roughness: .3 }), pw = mat('#fbfaf6'), telhado = mat('#b9c2ca'), corredor = mat('#d6d1c4'), verde = mat('#2f9e41'), alvo = alvoAtual();
  S.blocos.filter((b) => b.tipo === 'predio').forEach((b) => {
    const g = grupo3(b), parede = mat(b.cor || '#f2efe8'), n = b.andares || 1;
    const visto = view3 === 'fora' ? -1 : Math.min(floor, n - 1), cheio = view3 === 'fora' || floor >= n ? n : visto;
    b.partes.forEach((p) => {
      const x0 = p.x - p.w / 2, x1 = p.x + p.w / 2, z0 = p.z - p.d / 2, z1 = p.z + p.d / 2;
      for (let f = 0; f < cheio; f++) {
        const me = caixa(x0, x1, z0, z1, f * ANDAR_H, ANDAR_H - .05, parede, true, g); me.userData = { b: b.id }; T3.picks.push(me);
        for (let x = x0 + 3; x < x1 - 2; x += 4) { if (!dentroPe(b, x + 1, z1 + .5)) caixa(x, x + 2, z1 - .05, z1 + .06, f * ANDAR_H + 1.3, 1.5, vidro, false, g); if (!dentroPe(b, x + 1, z0 - .5)) caixa(x, x + 2, z0 - .06, z0 + .05, f * ANDAR_H + 1.3, 1.5, vidro, false, g); }
        for (let z = z0 + 3; z < z1 - 2; z += 4) { if (!dentroPe(b, x0 - .5, z + 1)) caixa(x0 - .06, x0 + .05, z, z + 2, f * ANDAR_H + 1.3, 1.5, vidro, false, g); if (!dentroPe(b, x1 + .5, z + 1)) caixa(x1 - .05, x1 + .06, z, z + 2, f * ANDAR_H + 1.3, 1.5, vidro, false, g); }
      }
      if (cheio === n) caixa(x0 - .4, x1 + .4, z0 - .4, z1 + .4, n * ANDAR_H, .5, telhado, true, g);
    });
    b.portas.forEach((q) => { const e = borda(b, q.x, q.z); if (e.nx) caixa(e.x + (e.nx > 0 ? -.2 : -.6), e.x + (e.nx > 0 ? .6 : .2), e.z - 1.5, e.z + 1.5, 0, 2.8, mat('#cd191e'), false, g); else caixa(e.x - 1.5, e.x + 1.5, e.z + (e.nz > 0 ? -.2 : -.6), e.z + (e.nz > 0 ? .6 : .2), 0, 2.8, mat('#cd191e'), false, g); });
    const e0 = ext(b);
    label3(b.nome, '', new THREE.Vector3((e0.x0 + e0.x1) / 2, (cheio === n ? n : visto + 1) * ANDAR_H + 6, e0.z0 + 2), g);
    void verde;
    if (cheio === n) return;
    const y = visto * ANDAR_H;
    b.partes.forEach((p) => caixa(p.x - p.w / 2, p.x + p.w / 2, p.z - p.d / 2, p.z + p.d / 2, y, .25, corredor, false, g));
    if (n > 1) { const es = b.escada, st = mat('#ffffff'), horiz = es.w > es.d, k = 8; for (let i = 0; i < k; i++) { if (horiz) caixa(es.x - es.w / 2 + es.w * i / k, es.x - es.w / 2 + es.w * (i + 1) / k, es.z - es.d / 2, es.z + es.d / 2, y + .25, .3 + i * .45, st, true, g); else caixa(es.x - es.w / 2, es.x + es.w / 2, es.z - es.d / 2 + es.d * i / k, es.z - es.d / 2 + es.d * (i + 1) / k, y + .25, .3 + i * .45, st, true, g); } }
    b.salas.filter((r) => r.andar === visto).forEach((r) => {
      const ativo = alvo && alvo.r && alvo.r.id === r.id, h = 1.6, gg = .2, q = retS(r);
      const piso = caixa(q.x0 + gg, q.x1 - gg, q.z0 + gg, q.z1 - gg, y + .25, .06, ativo ? mat('#0f7a43') : mat(corSala(r)), false, g); piso.userData = { b: b.id, r: r.id }; T3.picks.push(piso);
      const pd = portaSala(r), dw = Math.min(1.4, (pd.nx ? r.d : r.w) * .6) / 2;
      const parede2 = (a0, a1, fixo, eixo, temPorta, pc) => {
        const seg2 = temPorta ? [[a0, pc - dw], [pc + dw, a1]] : [[a0, a1]];
        seg2.forEach(([s0, s1]) => { if (s1 - s0 < .05) return; if (eixo === 'x') caixa(s0, s1, fixo - gg / 2, fixo + gg / 2, y + .25, h, pw, true, g); else caixa(fixo - gg / 2, fixo + gg / 2, s0, s1, y + .25, h, pw, true, g); });
      };
      parede2(q.x0, q.x1, q.z0, 'x', r.porta.lado === 'N', pd.x); parede2(q.x0, q.x1, q.z1, 'x', r.porta.lado === 'S', pd.x);
      parede2(q.z0, q.z1, q.x0, 'z', r.porta.lado === 'O', pd.z); parede2(q.z0, q.z1, q.x1, 'z', r.porta.lado === 'L', pd.z);
      const lb = label3(r.n, 'room3' + (ativo ? ' sel' : ''), new THREE.Vector3(r.x, y + 2.4, r.z), g); if (!ativo) T3.roomLabels.push(lb);
    });
  });
  buildRoute3();
}
function buildRoute3() {
  if (T3.route) { T3.world.remove(T3.route.group); T3.route = null; }
  const alvo = alvoAtual(); if (!alvo) return;
  // trechos até o andar que está à mostra ("Por fora" = só o térreo); a escada liga um trecho ao outro
  const rota = rotaPara(alvo), mostra = view3 === 'fora' ? 0 : floor, v = [];
  const usados = rota.segs.filter((s) => s.andar <= mostra);
  usados.forEach((s) => s.pts.forEach(([x, z]) => v.push(new THREE.Vector3(x, s.andar * ANDAR_H + .6, z))));
  const chega = rota.segs.length && rota.segs[rota.segs.length - 1].andar <= mostra;
  const path = new THREE.CurvePath(); for (let i = 1; i < v.length; i++) if (v[i].distanceTo(v[i - 1]) > .01) path.add(new THREE.LineCurve3(v[i - 1], v[i]));
  if (!path.curves.length) return;
  const cv = document.createElement('canvas'); cv.width = 64; cv.height = 8; const cx = cv.getContext('2d'); cx.fillStyle = '#0f7a43'; cx.fillRect(0, 0, 64, 8); cx.fillStyle = '#9ff0c0'; cx.fillRect(0, 0, 18, 8);
  const tex = new THREE.CanvasTexture(cv); tex.wrapS = THREE.RepeatWrapping; tex.repeat.x = path.getLength() / 6; tex.encoding = THREE.sRGBEncoding;
  const group = new THREE.Group();
  group.add(new THREE.Mesh(new THREE.TubeGeometry(path, Math.max(40, path.curves.length * 30), .55, 8, false), new THREE.MeshBasicMaterial({ map: tex })));
  const dot = new THREE.Mesh(new THREE.SphereGeometry(1.2, 20, 14), new THREE.MeshStandardMaterial({ color: 0xffffff, emissive: 0x0f7a43, emissiveIntensity: .35 })); group.add(dot);
  let ring = null; const fim = v[v.length - 1];
  if (chega) { ring = new THREE.Mesh(new THREE.RingGeometry(1.4, 1.9, 40), new THREE.MeshBasicMaterial({ color: 0x0f7a43, transparent: true, side: THREE.DoubleSide })); ring.rotation.x = -Math.PI / 2; ring.position.copy(fim).setY(fim.y + .1); group.add(ring); }
  T3.world.add(group); T3.route = { group, curve: path, dot, tex, ring };
}
function update3(voar) {
  if (!T3) return; build3();
  if (!voar) return;
  const alvo = alvoAtual(); if (!alvo) return home3();
  const c = alvo.r ? Wl(alvo.b, alvo.r.x, alvo.r.z) : { x: alvo.b.x, z: alvo.b.z }, p = new THREE.Vector3(c.x, andarDoAlvo(alvo) * ANDAR_H, c.z);
  const [ox, oz] = ponta(origemAtual()).centro; // de onde sai (portão ou "Saindo de")
  const meio = new THREE.Vector3((p.x + ox) / 2, p.y, (p.z + oz) / 2), dist = Math.max(90, Math.hypot(p.x - ox, p.z - oz) * 1.3);
  flyTo(meio, new THREE.Vector3(meio.x - dist * .35, p.y + dist * .8, meio.z + dist * .75));
}
new MutationObserver(() => { if (T3 && mode === '3d') update3(false); }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

/* ---------- início ---------- */
recalcSujo(); marcarSujo();
const eb0 = $('.cm-edbar'); if (eb0) eb0.hidden = tab !== 'edit';
renderFloors(); draw(); renderPanel();
return { pickSala: (bid, nome) => { const b = blocoById(bid); if (b) pickSala(b, b.salas.find((r) => r.n === nome)); }, pickBloco: (id) => pickBloco(blocoById(id)), setMode, trocaAba, get S() { return S; }, desfazer, refazer, rota: (bid, sid, de) => { const b = blocoById(bid); const r = sid && b.salas.find((x) => x.id === sid); const alvo = r ? { b, r } : { b }, o = lugarDe(de || null); return Object.assign({ passos: passos(alvo, rotaPara(alvo, o), o) }, rotaPara(alvo, o)); }, definirOrigem, get origem() { return origem; }, selecionar: (s) => { sel = s; draw(); renderPanel(); }, custo: (x, z, flex) => custoEm(gradeChao(flex), x, z) };
}

function iniciar() {
  const root = document.getElementById('mapa-campus'), dados = document.getElementById('mapa-campus-dados');
  if (!root || !dados) return;
  let op;
  try { op = JSON.parse(dados.textContent || '{}'); } catch (e) { console.error('Mapa do campus: dados inválidos', e); return; }
  window.mapaCampus = iniciarMapaCampus(root, op);
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar); else iniciar();
})();
