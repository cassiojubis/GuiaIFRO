<?php
// Página mostrada pelo app (PWA) quando não tem internet e a página pedida não foi guardada.
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$tituloPagina = 'Sem internet';
$paginaAtual = '';
$setoresOffline = listarSetores();

require __DIR__ . '/../view/offline.php';
