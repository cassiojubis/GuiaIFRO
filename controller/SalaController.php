<?php
/**
 * FICHA SIMPLES DE SALA (sala.php?id=copa)
 * Para salas do mapa que não são um setor (banheiros, copa, auditório...).
 * Se a sala for de um setor, manda direto pra página do setor.
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$idSala = isset($_GET['id']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $_GET['id']) : '';
$sala = $idSala !== '' ? buscarSalaDoMapa($idSala) : null;

if ($sala === null) {
    $termoSugerido = str_replace(['-', '_'], ' ', $idSala);
    require __DIR__ . '/Erro404Controller.php';
    exit;
}
if ($sala['url_setor']) {
    header('Location: ' . $sala['url_setor'] . (isset($_GET['aqui']) ? '&aqui=' . rawurlencode($sala['id']) : ''));
    exit;
}

$tituloPagina = $sala['nome'];
$paginaAtual = 'mapa';
$nomeAndar = listarMapaSalas()['andares'][$sala['andar']]['nome'] ?? '';
$descricaoLocal = $nomeAndar . ', ala ' . $sala['ala'] . ($sala['ponta'] ? ', no fim do corredor' : '');

// "Estou aqui" (N2): o QR da porta abre sala.php?id=copa&aqui=1
$salaAqui = $sala;
$veioDoQr = isset($_GET['aqui']);
$mapaDestinos = listarMapaSalas();
$salaAcess = $sala;
$dadosAcess = buscarAcessibilidadeSala($sala['id']); // I3

require __DIR__ . '/../view/sala.php';
