// Funções pequenas usadas por mais de um script (script.js, mapa.js e admin.js).
// Precisa ser carregado ANTES deles.

/**
 * Escapa texto antes de colocar dentro de innerHTML ou de um atributo.
 * Troca & < > " ' pelos códigos HTML, então nenhum nome vindo dos dados
 * consegue virar tag nem "fechar" um atributo.
 */
function escaparHtml(valor) {
    return String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/**
 * Cores das peças do cenário do mapa (bloco, obra, quadra...).
 * Usadas no editor do admin, na planta 2D e na maquete 3D, então as três
 * mostram a mesma coisa. 'redonda' = desenhada como círculo (árvore).
 */
const PECAS_MAPA = {
    bloco: { cor: '#94a3b8', altura: 4, rotulo: 'Bloco' },
    obra: { cor: '#f59e0b', altura: 2, rotulo: 'Obra' },
    quadra: { cor: '#3b82f6', altura: 0.15, rotulo: 'Quadra' },
    estacionamento: { cor: '#64748b', altura: 0.1, rotulo: 'Estacionamento' },
    area_verde: { cor: '#4ade80', altura: 0.12, rotulo: 'Área verde' },
    arvore: { cor: '#15803d', altura: 4, rotulo: 'Árvore', redonda: true },
    estande: { cor: '#a855f7', altura: 2.4, rotulo: 'Estande' },
};

