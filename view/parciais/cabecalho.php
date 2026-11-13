<?php
// cabeçalho compartilhado - a página que inclui isso já define $tituloPagina e $paginaAtual
require_once __DIR__ . '/../../model/Config.php';
require_once __DIR__ . '/../../model/Repositorio.php';
require_once __DIR__ . '/../../model/Idiomas.php';

$tituloPagina = $tituloPagina ?? 'Início';
$paginaAtual = $paginaAtual ?? 'inicio';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaHtml(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= t('meta_descricao') ?>">
    <title><?= htmlspecialchars($tituloPagina) ?> | <?= NOME_SISTEMA ?></title>

    <meta name="theme-color" content="#0f7a43">
    <link rel="apple-touch-icon" href="img/icones/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="img/icones/icone-192.png">

    <!-- Aplica as preferências de acessibilidade ANTES de desenhar a página (pra não "piscar").
         Sem escolha salva, segue o que o aparelho pede (modo escuro, mais contraste, menos movimento). -->
    <script nonce="<?= cspNonce() ?>">
        (function () {
            var html = document.documentElement;
            var ler = function (chave) { try { return localStorage.getItem(chave); } catch (e) { return null; } };
            var sistema = function (consulta) { return window.matchMedia && window.matchMedia(consulta).matches; };
            var fonte = ler('guiaifro-fonte');
            if (fonte === 'media' || fonte === 'grande') html.classList.add('fonte-' + fonte);
            var tema = ler('guiaifro-tema');
            if (tema === 'escuro' || (!tema && sistema('(prefers-color-scheme: dark)'))) html.classList.add('tema-escuro');
            var contraste = ler('guiaifro-pref-alto-contraste');
            if (contraste === '1' || (contraste === null && sistema('(prefers-contrast: more)'))) html.classList.add('alto-contraste');
            var movimento = ler('guiaifro-pref-reduzir-movimento');
            if (movimento === '1' || (movimento === null && sistema('(prefers-reduced-motion: reduce)'))) html.classList.add('reduzir-movimento');
            if (ler('guiaifro-pref-fonte-leitura') === '1') {
                html.classList.add('fonte-leitura');
                // Fonte de leitura fácil (Atkinson Hyperlegible): só baixa pra quem ligou a opção
                var folha = document.createElement('link');
                folha.rel = 'stylesheet';
                folha.href = 'https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&display=swap';
                document.head.appendChild(folha);
            }
            if (ler('guiaifro-pref-leitura-facil') === '1') html.classList.add('leitura-facil');
        })();
    </script>

    <?php if (!empty($mostrarAbertura)): ?>
    <!-- Abertura (vídeo do logo) só na home, uma vez por sessão, e nunca com "menos movimento" ligado -->
    <link rel="stylesheet" href="css/abertura.css?v=<?= filemtime(__DIR__ . '/../../css/abertura.css') ?>">
    <script nonce="<?= cspNonce() ?>">
        (function () {
            var html = document.documentElement;
            var vista = false;
            try { vista = sessionStorage.getItem('guiaifro-abertura-vista') === '1'; } catch (e) { vista = true; }
            if (!vista && !html.classList.contains('reduzir-movimento')) html.classList.add('com-abertura');
        })();
    </script>
    <?php endif; ?>

    <!-- Fonte utilizada no protótipo original -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Estilos próprios: nenhum framework CSS é necessário -->
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/../../css/style.css') ?>">
    <link rel="stylesheet" href="css/responsivo.css?v=<?= filemtime(__DIR__ . '/../../css/responsivo.css') ?>">
    <?php // CSS só de uma página (ex.: o mapa define $estilosExtras = ['css/mapa-campus.css']) ?>
    <?php foreach ($estilosExtras ?? [] as $arquivoCss): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($arquivoCss) ?>?v=<?= filemtime(__DIR__ . '/../../' . $arquivoCss) ?>">
    <?php endforeach; ?>
</head>
<body data-page="<?= htmlspecialchars($paginaAtual) ?>" data-pagina-traducao="<?= htmlspecialchars(paginaTraducao(), ENT_QUOTES, 'UTF-8') ?>">

<?php if (!empty($mostrarAbertura)): ?>
<!-- ABERTURA: vídeo do logo em tela cheia (css/abertura.css + js/abertura.js) -->
<div class="abertura" id="abertura" role="region" aria-label="Abertura do <?= NOME_SISTEMA ?>">
    <video class="abertura-video" autoplay muted playsinline preload="auto" aria-hidden="true" tabindex="-1">
        <source src="img/abertura.mp4" type="video/mp4">
        <source src="img/abertura.webm" type="video/webm">
    </video>
    <button type="button" class="abertura-pular"><?= idiomaAtual() === 'es' ? 'Saltar introducción' : 'Pular abertura' ?> <?= icone('chevron-right') ?></button>
    <div class="abertura-progresso" aria-hidden="true"></div>
</div>
<script src="js/abertura.js?v=<?= filemtime(__DIR__ . '/../../js/abertura.js') ?>"></script>
<?php endif; ?>

<!-- Primeiro item da página: quem usa teclado ou leitor de tela pula direto pro conteúdo -->
<a class="pular-conteudo" href="#conteudo">Pular para o conteúdo</a>

<!-- Dados dos setores em JSON, usados pela busca ao vivo e por favoritos/recentes (js/script.js) -->
<script type="application/json" id="setores-busca-data"><?php
    echo json_encode(array_map(static function ($setor) {
        return [
            'id' => $setor['id'],
            'nome' => $setor['nome'],
            'sigla' => $setor['sigla'],
            'url' => 'setor.php?id=' . $setor['id'],
        ];
    }, listarSetores()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
?></script>

<!-- NAVBAR FIXA: reutilizada em todas as páginas -->
<header class="site-header" id="site-header">
    <nav class="navbar container" aria-label="Navegação principal">
        <a href="index.php" class="brand" aria-label="Ir para a página inicial">
            <img class="brand-logo" src="img/logo-guiaifro.png" alt="" width="217" height="285">
            <span class="brand-copy">
                <strong><?= NOME_SISTEMA ?></strong>
                <small><?= NOME_CAMPUS ?></small>
            </span>
        </a>

        <div class="desktop-nav">
            <a class="nav-link <?= linkAtivo('inicio', $paginaAtual) ?>" href="index.php"><?= t('nav_inicio') ?></a>
            <a class="nav-link <?= linkAtivo('categorias', $paginaAtual) ?>" href="categorias.php"><?= t('nav_categorias') ?></a>
            <a class="nav-link <?= linkAtivo('mapa', $paginaAtual) ?>" href="mapa.php"><?= t('nav_mapa') ?></a>
            <a class="nav-link <?= linkAtivo('sobre', $paginaAtual) ?>" href="sobre.php"><?= t('nav_sobre') ?></a>
            <a class="nav-link <?= linkAtivo('contato', $paginaAtual) ?>" href="contato.php"><?= t('nav_contato') ?></a>
        </div>

        <div class="navbar-actions desktop-only">
            <!-- A busca envia o termo para a página de categorias, mas também mostra sugestões ao vivo -->
            <div class="busca-com-sugestoes">
                <form class="navbar-search" action="categorias.php" method="get">
                    <?= icone('search') ?>
                    <input type="search" name="q" placeholder="<?= t('busca_placeholder') ?>" aria-label="<?= t('busca_placeholder') ?>" autocomplete="off" data-busca-ao-vivo>
                </form>
                <div class="busca-resultados" hidden></div>
            </div>
            <a href="contato.php" class="button button-primary button-small"><?= t('btn_fale_conosco') ?></a>
        </div>

        <!-- Botão visível apenas no celular -->
        <button class="menu-button" id="menu-button" type="button" aria-label="Abrir menu" aria-expanded="false">
            <?= icone('menu', 'menu-open-icon') ?>
            <?= icone('x', 'menu-close-icon') ?>
        </button>
    </nav>

    <!-- Menu mobile controlado por assets/js/script.js -->
    <div class="mobile-menu container" id="mobile-menu">
        <a class="mobile-link <?= linkAtivo('inicio', $paginaAtual) ?>" href="index.php"><?= t('nav_inicio') ?> <?= icone('chevron-right') ?></a>
        <a class="mobile-link <?= linkAtivo('categorias', $paginaAtual) ?>" href="categorias.php"><?= t('nav_categorias') ?> <?= icone('chevron-right') ?></a>
        <a class="mobile-link <?= linkAtivo('mapa', $paginaAtual) ?>" href="mapa.php"><?= t('nav_mapa') ?> <?= icone('chevron-right') ?></a>
        <a class="mobile-link <?= linkAtivo('sobre', $paginaAtual) ?>" href="sobre.php"><?= t('nav_sobre') ?> <?= icone('chevron-right') ?></a>
        <a class="mobile-link <?= linkAtivo('contato', $paginaAtual) ?>" href="contato.php"><?= t('nav_contato') ?> <?= icone('chevron-right') ?></a>

        <div class="busca-com-sugestoes">
            <form class="mobile-search" action="categorias.php" method="get">
                <?= icone('search') ?>
                <input type="search" name="q" placeholder="<?= t('busca_placeholder') ?>" aria-label="<?= t('busca_placeholder') ?>" autocomplete="off" data-busca-ao-vivo>
            </form>
            <div class="busca-resultados" hidden></div>
        </div>
    </div>
</header>

<?php if (idiomaAtual() === 'wari'): ?>
    <!-- Oro Wari ligado: avisa que a tradução ainda está em validação (o que falta aparece em português) -->
    <div class="faixa-idioma" role="note" data-sem-traducao>
        <div class="container">
            <?= icone('languages') ?>
            <span><strong lang="pnv"><?= htmlspecialchars(textoWari('Sobre o Oro Wari (Pakaa Nova)', 'acessibilidade') ?? 'Oro Wari (Pakaa Nova)', ENT_QUOTES, 'UTF-8') ?></strong>
                Tradução em validação com falantes da língua — os textos ainda sem tradução aparecem em português.</span>
            <a href="acessibilidade.php#idioma">Trocar idioma</a>
        </div>
    </div>
<?php endif; ?>

<?php $avisoUrgente = avisoUrgenteAtual(); ?>
<?php if ($avisoUrgente): ?>
    <!-- Aviso urgente (admin › Informações › Avisos, nível urgente) -->
    <div class="faixa-urgente" role="alert">
        <div class="container">
            <?= icone('triangle-alert') ?>
            <span><strong><?= htmlspecialchars($avisoUrgente['titulo'], ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars($avisoUrgente['mensagem'], ENT_QUOTES, 'UTF-8') ?></span>
            <a href="noticias.php#avisos">Ver avisos</a>
        </div>
    </div>
<?php endif; ?>

<main class="page-transition" id="conteudo" tabindex="-1">
