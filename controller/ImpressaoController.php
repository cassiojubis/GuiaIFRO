<?php
/**
 * PLACAS PARA IMPRIMIR (N3)
 *   impressao.php?sala=cra  -> placa A4 da porta do CRA
 *   impressao.php?todas=1   -> todas as placas, uma por página
 * Cada placa: nome grande, andar/ala, horário, planta do andar com
 * "você está aqui" na porta e o QR que abre o "Estou aqui, quero ir para...".
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';
require_once __DIR__ . '/../model/QrCode.php';
require_once __DIR__ . '/../model/PlantaSvg.php';

$mapa = listarMapaSalas();
$todas = !empty($_GET['todas']);
$idSala = preg_replace('/[^a-z0-9_-]/i', '', (string) ($_GET['sala'] ?? ''));

$salasParaImprimir = [];
foreach ($mapa['salas'] as $sala) {
    if (!$sala['vazia'] && ($todas || $sala['id'] === $idSala)) {
        $salasParaImprimir[] = $sala;
    }
}
if (!$salasParaImprimir) {
    $termoSugerido = $idSala;
    require __DIR__ . '/Erro404Controller.php';
    exit;
}

$placas = [];
foreach ($salasParaImprimir as $sala) {
    $setor = $sala['setor_id'] ? buscarSetorPorId($sala['setor_id']) : null;
    $textoHorario = '';
    if ($setor) {
        $faixas = listarHorariosSetor($setor['id']);
        $textoHorario = $faixas ? resumoHorarioSemana($faixas) : (string) $setor['horario'];
    }
    $placas[] = [
        'sala' => $sala,
        'titulo' => $setor['nome'] ?? $sala['nome'],
        'sigla' => $setor['sigla'] ?? '',
        'andar' => $mapa['andares'][$sala['andar']]['nome'] ?? '',
        'horario' => $textoHorario,
        'url' => urlDaPorta($sala),
        'qr' => qrCodeSvg(urlDaPorta($sala), 220, 'QR code: ' . ($setor['nome'] ?? $sala['nome'])),
        'planta' => plantaSvg($mapa['salas'], $sala['andar'], $sala['id']),
    ];
}

// Endereço ainda é o do computador local? Avisa antes de imprimir (o QR não abriria no celular).
$enderecoLocal = (bool) preg_match('#//(localhost|127\.0\.0\.1)#', urlBaseSite());

require __DIR__ . '/../view/impressao.php';
