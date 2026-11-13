<?php
// Fragmento da faixa "Hoje no campus" (hoje.php). A home busca isto a cada 5 minutos pra atualizar sem recarregar.
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$hoje = hojeNoCampus();
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
require __DIR__ . '/../view/parciais/hoje-no-campus.php';
