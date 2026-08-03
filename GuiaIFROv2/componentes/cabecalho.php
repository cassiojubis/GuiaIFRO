<?php
/**
 * CABEÇALHO COMPARTILHADO
 *
 * Antes de incluir este arquivo, cada página define:
 * $tituloPagina e $paginaAtual.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/dados.php';

$tituloPagina = $tituloPagina ?? 'Início';
$paginaAtual = $paginaAtual ?? 'inicio';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de navegação e informações do IFRO Campus Guajará-Mirim.">
    <title><?= htmlspecialchars($tituloPagina) ?> | <?= NOME_SISTEMA ?></title>

    <!-- Fonte utilizada no protótipo original -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Estilos próprios: nenhum framework CSS é necessário -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsivo.css">
</head>
<body data-page="<?= htmlspecialchars($paginaAtual) ?>">

<!-- NAVBAR FIXA: reutilizada em todas as páginas -->
<header class="site-header" id="site-header">
    <nav class="navbar container" aria-label="Navegação principal">
        <a href="index.php" class="brand" aria-label="Ir para a página inicial">
            <span class="brand-mark">IFRO</span>
            <span class="brand-copy">
                <strong><?= NOME_SISTEMA ?></strong>
                <small><?= NOME_CAMPUS ?></small>
            </span>
        </a>

        <div class="desktop-nav">
            <a class="nav-link <?= linkAtivo('inicio', $paginaAtual) ?>" href="index.php">Início</a>
            <a class="nav-link <?= linkAtivo('categorias', $paginaAtual) ?>" href="categorias.php">Categorias</a>
            <a class="nav-link <?= linkAtivo('mapa', $paginaAtual) ?>" href="mapa.php">Mapa</a>
            <a class="nav-link <?= linkAtivo('sobre', $paginaAtual) ?>" href="sobre.php">Sobre</a>
            <a class="nav-link <?= linkAtivo('contato', $paginaAtual) ?>" href="contato.php">Contato</a>
        </div>

        <div class="navbar-actions desktop-only">
            <!-- A busca envia o termo para a página de categorias -->
            <form class="navbar-search" action="categorias.php" method="get">
                <?= icone('search') ?>
                <input type="search" name="q" placeholder="Buscar setor..." aria-label="Buscar setor">
            </form>
            <a href="contato.php" class="button button-primary button-small">Fale conosco</a>
        </div>

        <!-- Botão visível apenas no celular -->
        <button class="menu-button" id="menu-button" type="button" aria-label="Abrir menu" aria-expanded="false">
            <?= icone('menu', 'menu-open-icon') ?>
            <?= icone('x', 'menu-close-icon') ?>
        </button>
    </nav>

    <!-- Menu mobile controlado por assets/js/script.js -->
    <div class="mobile-menu container" id="mobile-menu">
        <a class="mobile-link <?= linkAtivo('inicio', $paginaAtual) ?>" href="index.php">Início <?= icone('chevron-right') ?></a>
        <a class="mobile-link <?= linkAtivo('categorias', $paginaAtual) ?>" href="categorias.php">Categorias <?= icone('chevron-right') ?></a>
        <a class="mobile-link <?= linkAtivo('mapa', $paginaAtual) ?>" href="mapa.php">Mapa <?= icone('chevron-right') ?></a>
        <a class="mobile-link <?= linkAtivo('sobre', $paginaAtual) ?>" href="sobre.php">Sobre <?= icone('chevron-right') ?></a>
        <a class="mobile-link <?= linkAtivo('contato', $paginaAtual) ?>" href="contato.php">Contato <?= icone('chevron-right') ?></a>

        <form class="mobile-search" action="categorias.php" method="get">
            <?= icone('search') ?>
            <input type="search" name="q" placeholder="Buscar setor..." aria-label="Buscar setor">
        </form>
    </div>
</header>

<main class="page-transition">