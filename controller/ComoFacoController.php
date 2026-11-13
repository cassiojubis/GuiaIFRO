<?php
/**
 * "COMO FAÇO PARA..." (como-faco.php e como-faco.php?f=pedir-declaracao-de-matricula) — N11
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$paginaAtual = 'como-faco';
$canais = ['suap' => 'Pelo SUAP', 'presencial' => 'Presencial', 'ambos' => 'Pelo SUAP ou presencial'];
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_GET['f'] ?? '')));

if ($slug === '') {
    $tituloPagina = 'Como faço para…';
    $fluxos = listarFluxos();
    require __DIR__ . '/../view/como-faco.php';
    return;
}

$fluxo = buscarFluxo($slug);
if (!$fluxo) {
    $termoSugerido = str_replace('-', ' ', $slug);
    require __DIR__ . '/Erro404Controller.php';
    exit;
}
$tituloPagina = $fluxo['titulo'];
$salaDoFluxo = $fluxo['setor_id'] ? (listarSalasDoSetor($fluxo['setor_id'])[0] ?? null) : null;

require __DIR__ . '/../view/como-faco-detalhe.php';
