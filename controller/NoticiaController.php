<?php
// Página de uma notícia (noticia.php?slug=...). N6.
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_GET['slug'] ?? '')));
$noticia = $slug !== '' ? buscarNoticiaPorSlug($slug) : null;
if (!$noticia) {
    $termoSugerido = str_replace('-', ' ', $slug);
    require __DIR__ . '/Erro404Controller.php';
    exit;
}

$tituloPagina = $noticia['titulo'];
$paginaAtual = 'noticias';
// Texto simples: linha em branco separa parágrafos (nada de HTML vindo do admin).
$paragrafos = array_values(array_filter(array_map('trim', preg_split('/\R{2,}/u', (string) $noticia['conteudo']))));

require __DIR__ . '/../view/noticia.php';
