<?php
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';
require_once __DIR__ . '/MapaDados.php';

$tituloPagina = 'Início';
$paginaAtual = 'inicio';
$chipsBusca = ['matrícula', 'biblioteca', 'auxílio', 'SUAP', 'CRA', 'estágio', 'documentos'];
$atalhosRapidos = listarAtalhosRapidos();
$categorias = listarCategorias();
$noticias = listarNoticias();
$hoje = hojeNoCampus(); // faixa "Hoje no campus" (N5)

// Mapa interativo logo no topo. index.php?sala=cra também abre com a rota (o mapa.js grava a sala no endereço).
$dadosMapa = prepararMapa();

require __DIR__ . '/../view/index.php';
