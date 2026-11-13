<?php
/**
 * "PRIMEIRO DIA" DO CALOURO (primeiro-dia.php) — N12
 * Roteiro de paradas com o caminho no mapa. O progresso fica só no aparelho
 * (localStorage). primeiro-dia.php?selo=<código> é o QR escondido de cada
 * parada: dá o selo do "passaporte" e mostra uma curiosidade do campus.
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$tituloPagina = 'Primeiro dia no campus';
$paginaAtual = 'primeiro-dia';
$paradas = listarParadasPrimeiroDia();

$codigo = preg_replace('/[^a-f0-9]/', '', strtolower((string) ($_GET['selo'] ?? '')));
$seloGanho = $codigo !== '' ? buscarParadaPeloSelo($codigo) : null;
$scriptsExtras = ['js/primeiro-dia.js'];

require __DIR__ . '/../view/primeiro-dia.php';
