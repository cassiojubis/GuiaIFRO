<?php
/**
 * PEDAÇO DE TELA DO QUIOSQUE (quiosque-sala.php?sala=cra) — W2
 * Planta do andar com a sala destacada, o caminho em texto saindo da entrada
 * do prédio e o QR pra levar a rota no celular. Tudo pronto do servidor.
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';
require_once __DIR__ . '/../model/PlantaSvg.php';
require_once __DIR__ . '/../model/QrCode.php';

$idSala = preg_replace('/[^a-z0-9_-]/i', '', (string) ($_GET['sala'] ?? ''));
$sala = $idSala !== '' ? buscarSalaDoMapa($idSala) : null;
if (!$sala) {
    http_response_code(404);
    exit;
}
$mapa = listarMapaSalas();
$posicoes = calcularPlanta($mapa['salas'], $sala['andar']);
// O quiosque fica na entrada: o caminho começa ali (sem a parte do portão).
$passos = array_slice(passosEmTexto($sala, $posicoes), 1);
$setor = $sala['setor_id'] ? buscarSetorPorId($sala['setor_id']) : null;
$urlCelular = urlBaseSite() . '/mapa.php?de=entrada&sala=' . rawurlencode($sala['id']);
contarEstatistica('rota', 'quiosque>' . $sala['id']);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
require __DIR__ . '/../view/quiosque-sala.php';
