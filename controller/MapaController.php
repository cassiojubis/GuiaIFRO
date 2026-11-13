<?php
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';
require_once __DIR__ . '/MapaDados.php';

$tituloPagina = 'Mapa Interativo';
$paginaAtual = 'mapa';

// mapa.php?sala=dape já abre com a sala selecionada (link "Ver no mapa" da página do setor).
$dadosMapa = prepararMapa();

// Pediram uma sala que não existe: o mapa abre normal, mas com aviso e status 404.
if ($dadosMapa['salaNaoEncontrada']) {
    http_response_code(404);
}

require __DIR__ . '/../view/mapa.php';
