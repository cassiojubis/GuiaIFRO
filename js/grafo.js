// GRAFO DO PRÉDIO + MENOR CAMINHO (Dijkstra) - usado pela rota do mapa (js/mapa.js).
//
// O prédio é representado como um GRAFO PONDERADO:
//   - nó     = um ponto onde a pessoa pode estar: portão, entrada, porta de cada sala,
//              pontos do corredor, pé e topo da escada;
//   - aresta = um trecho que dá para andar em linha reta entre dois nós;
//   - peso   = o comprimento desse trecho, em metros (a escada pesa o comprimento dela).
// A rota é o caminho de MENOR PESO TOTAL entre a origem e o destino (algoritmo de Dijkstra).
//
// Este arquivo não sabe nada de desenho nem de página: recebe números e devolve números.
// Por isso dá pra testar sozinho (tests/grafo_test.html).

window.Grafo = (() => {
'use strict';

/* ---------------- grafo genérico ---------------- */

function criarGrafo() {
    const nos = new Map();      // id -> { id, andar, x, z }
    const arestas = new Map();  // id -> [{ para, peso }]

    return {
        nos,
        adicionarNo(id, andar, x, z) {
            if (!nos.has(id)) {
                nos.set(id, { id, andar, x, z });
                arestas.set(id, []);
            }
            return id;
        },
        // aresta nos dois sentidos (dá pra andar de A pra B e de B pra A);
        // sem peso informado, vale a distância em linha reta entre os dois pontos
        ligar(a, b, peso) {
            const na = nos.get(a), nb = nos.get(b);
            if (!na || !nb) throw new Error(`Nó inexistente: ${na ? b : a}`);
            const p = peso ?? Math.hypot(na.x - nb.x, na.z - nb.z);
            arestas.get(a).push({ para: b, peso: p });
            arestas.get(b).push({ para: a, peso: p });
        },
        vizinhos(id) {
            return arestas.get(id) || [];
        },
    };
}

/* Dijkstra: menor caminho de 'origem' até 'destino'.
   Devolve { nos: [ids na ordem do caminho], distancia } ou null se não houver caminho.

   Ideia: cada nó guarda a menor distância conhecida até ele (começa em infinito, a origem em 0).
   A cada passo pega o nó NÃO VISITADO com a menor distância — ela já é definitiva, porque
   todos os pesos são positivos — e tenta melhorar a distância dos vizinhos passando por ele.
   Quando o destino é visitado, volta pelo "anterior" de cada nó para montar o caminho.

   Com poucas dezenas de nós, procurar o menor numa lista (O(V²)) é instantâneo e mais
   simples de ler do que uma fila de prioridade (heap). */
function menorCaminho(grafo, origem, destino) {
    if (!grafo.nos.has(origem) || !grafo.nos.has(destino)) return null;

    const distancia = new Map([[origem, 0]]);
    const anterior = new Map();
    const visitados = new Set();

    while (true) {
        let atual = null;
        distancia.forEach((d, id) => {
            if (!visitados.has(id) && (atual === null || d < distancia.get(atual))) atual = id;
        });
        if (atual === null) return null; // acabaram os nós alcançáveis: não há caminho
        if (atual === destino) break;
        visitados.add(atual);

        grafo.vizinhos(atual).forEach(({ para, peso }) => {
            if (visitados.has(para)) return;
            const candidata = distancia.get(atual) + peso;
            if (candidata < (distancia.get(para) ?? Infinity)) {
                distancia.set(para, candidata);
                anterior.set(para, atual);
            }
        });
    }

    const caminho = [destino];
    while (caminho[0] !== origem) caminho.unshift(anterior.get(caminho[0]));
    return { nos: caminho, distancia: distancia.get(destino) };
}

/* ---------------- o prédio do campus ---------------- */

/* Monta o grafo a partir da planta calculada no js/mapa.js.
   planta = { XC, S0, S1, STAIR_RUN, PORTAO_X, FAIXA_ESCADA,
              salas: [{ id, andar, dentro: [x, z], corredor: [x, z] }] }
   (x = da entrada para o fundo do prédio; z = para os lados; o corredor transversal fica em x = XC)

   Térreo:  portão → entrada → saguão → contorna a escada por um dos lados → corredor transversal
   Escada:  liga o pé da escada (térreo) ao topo (1º andar); peso = comprimento da escada
   1º andar: topo da escada → corredor transversal
   Cada sala: porta (ponto do corredor em frente) → dentro da sala. Duas salas frente a frente
   dividem o mesmo nó do corredor. */
function montarGrafoDoPredio(planta) {
    const { XC, S0, S1, STAIR_RUN, PORTAO_X, FAIXA_ESCADA } = planta;
    const g = criarGrafo();
    const noCorredor = (andar, z) => g.adicionarNo(`${andar}:corredor:${z.toFixed(3)}`, andar, XC, z);

    g.adicionarNo('portao', 'terreo', PORTAO_X, 0);
    g.adicionarNo('entrada', 'terreo', -0.1, 0);
    g.adicionarNo('saguao', 'terreo', S0 - 1, 0);
    g.adicionarNo('escada:terreo', 'terreo', S0 + 0.5, 0);
    g.adicionarNo('escada:andar1', 'andar1', S1 - 0.5, 0);
    g.ligar('portao', 'entrada');
    g.ligar('entrada', 'saguao');
    g.ligar('saguao', 'escada:terreo');
    g.ligar('escada:terreo', 'escada:andar1', STAIR_RUN);

    // a escada fica no meio do caminho: no térreo dá a volta por ela, à esquerda (-1) ou à direita (+1)
    [-1, 1].forEach((lado) => {
        const contorno = g.adicionarNo(`contorno:${lado}`, 'terreo', S0 - 0.4, lado * FAIXA_ESCADA);
        g.ligar('saguao', contorno);
        g.ligar(contorno, noCorredor('terreo', lado * FAIXA_ESCADA));
    });
    // no 1º andar a escada desemboca no meio do corredor transversal
    g.ligar('escada:andar1', noCorredor('andar1', 0));

    planta.salas.forEach((s) => {
        const porta = noCorredor(s.andar, s.corredor[1]);
        const dentro = g.adicionarNo(`sala:${s.id}`, s.andar, s.dentro[0], s.dentro[1]);
        g.ligar(porta, dentro);
    });

    // o corredor transversal é uma linha reta: liga cada ponto ao vizinho seguinte (ordenados por z)
    ['terreo', 'andar1'].forEach((andar) => {
        const pontos = [...g.nos.values()]
            .filter((n) => n.andar === andar && n.id.startsWith(`${andar}:corredor:`))
            .sort((a, b) => a.z - b.z);
        for (let i = 1; i < pontos.length; i++) g.ligar(pontos[i - 1].id, pontos[i].id);
    });

    return g;
}

/* Rota do prédio, já no formato que o desenho usa:
   { distancia, trechos: [{ andar, pontos: [[x, z], ...] }, ...] } — um trecho por andar, na ordem.
   origem/destino: 'portao', 'entrada' ou 'sala:<id>'. */
function rota(grafo, origem, destino) {
    const caminho = menorCaminho(grafo, origem, destino);
    if (!caminho) return null;
    const trechos = [];
    caminho.nos.forEach((id) => {
        const no = grafo.nos.get(id);
        const ultimo = trechos[trechos.length - 1];
        if (ultimo && ultimo.andar === no.andar) ultimo.pontos.push([no.x, no.z]);
        else trechos.push({ andar: no.andar, pontos: [[no.x, no.z]] });
    });
    return { distancia: caminho.distancia, trechos };
}

return { criarGrafo, menorCaminho, montarGrafoDoPredio, rota };
})();
