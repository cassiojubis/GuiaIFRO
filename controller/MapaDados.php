<?php
/**
 * DADOS DO MAPA INTERATIVO — usados pela página do mapa (mapa.php) e pela home (index.php),
 * que mostram o mesmo mapa (view/parciais/mapa-interativo.php + js/mapa-campus.js).
 *
 * ?sala=<id> já abre com a rota até a sala (aceita o id da sala, do setor ou de um lugar do mapa).
 * ?de=<id> diz de onde a rota sai — o "Saindo de" ("Estou aqui" / QR da porta). Aceita também
 * ?de=entrada (entrada do prédio principal). Id que não existe no mapa é ignorado.
 */
require_once __DIR__ . '/../model/MapaCampus.php';
require_once __DIR__ . '/../model/Idiomas.php';

/** Devolve o que a parcial do mapa precisa: a sala pedida e o JSON do JavaScript. */
function prepararMapa(): array
{
    // Mesmo filtro do setor.php: só letras, números, _ e -.
    $idSalaPedida = isset($_GET['sala']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $_GET['sala']) : '';
    $salaEncontrada = $idSalaPedida !== '' && existeNoMapaCampus($idSalaPedida);

    $idOrigemPedida = isset($_GET['de']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $_GET['de']) : '';
    $origemValida = $idOrigemPedida === 'entrada' || existeNoMapaCampus($idOrigemPedida);

    // Tudo que o JavaScript precisa, entregue de uma vez como JSON.
    $dadosMapaJson = json_encode([
        'dados' => mapaCampus(),
        'editavel' => false,
        'setores' => listarSetoresParaMapa(),
        'salaInicial' => $salaEncontrada ? $idSalaPedida : null,
        'origemInicial' => $origemValida ? $idOrigemPedida : null,
        'semEscada' => salasSemEscada(), // I3: sala_id => chega sem escada? (térreo ou elevador informado)
        'velocidade' => (float) str_replace(',', '.', configuracao('velocidade_caminhada', '1.2')),
        'scripts3d' => scriptsMapaCampus3d(),
        'idioma' => idiomaAtual() === 'es' ? 'es' : 'pt',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    return [
        'idSalaPedida' => $idSalaPedida,
        'salaNaoEncontrada' => $idSalaPedida !== '' && !$salaEncontrada,
        'dadosMapaJson' => $dadosMapaJson,
    ];
}
