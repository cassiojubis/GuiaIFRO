<?php
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';
require_once __DIR__ . '/../model/Idiomas.php';

http_response_code(404);

$tituloPagina = 'Página não encontrada';
$paginaAtual = '';

require __DIR__ . '/../view/404.php';
