<?php
// cabeçalho do painel admin - reaproveita as constantes/icone() do site público.
// Os controllers do admin já carregaram BaseAdmin.php ($usuarioAdmin = quem está logado).
$tituloPaginaAdmin = $tituloPaginaAdmin ?? 'Painel';
$paginaAtualAdmin = $paginaAtualAdmin ?? 'dashboard';

// Aviso da última ação (salvou, excluiu, deu erro...) e formulário pra reabrir com erro.
$avisoAdmin = pegarFlash('admin');
$reabrirAdmin = pegarFlash('reabrir');
$versaoCssAdmin = filemtime(__DIR__ . '/../../../admin/assets/css/admin.css');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <!-- token CSRF lido pelo admin.js para mandar no cabeçalho X-CSRF-Token dos fetch -->
    <meta name="csrf-token" content="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($tituloPaginaAdmin, ENT_QUOTES, 'UTF-8') ?> · Área Administrativa | <?= NOME_SISTEMA ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Reaproveita a base visual do site público: variáveis de cor, botões, cards, ícones -->
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../../../css/style.css') ?>">
    <link rel="stylesheet" href="../css/responsivo.css">

    <!-- Estilos exclusivos do painel administrativo -->
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= $versaoCssAdmin ?>">
    <link rel="stylesheet" href="assets/css/admin-responsive.css">
    <?php foreach ($estilosExtrasAdmin ?? [] as $arquivoCss): // CSS só de uma tela (ex.: editor do mapa) ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($arquivoCss, ENT_QUOTES, 'UTF-8') ?>?v=<?= filemtime(__DIR__ . '/../../../admin/' . $arquivoCss) ?>">
    <?php endforeach; ?>
</head>
<body class="admin-body" data-admin-page="<?= htmlspecialchars($paginaAtualAdmin, ENT_QUOTES, 'UTF-8') ?>">
<a class="pular-conteudo" href="#admin-main">Pular para o conteúdo</a>

<?php // Dados pro admin.js mostrar o aviso e reabrir o formulário (JSON escapado, não executa). ?>
<script type="application/json" id="admin-aviso-dados"><?= json_encode(['aviso' => $avisoAdmin, 'reabrir' => $reabrirAdmin], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

<!-- Fundo escurecido atrás da gaveta lateral no celular -->
<div class="admin-overlay" id="admin-overlay"></div>

<?php require __DIR__ . '/admin-sidebar.php'; ?>

<div class="admin-shell">

    <!-- NAVBAR SUPERIOR: reutilizada em todas as páginas administrativas -->
    <header class="admin-topbar">
        <button class="admin-menu-button" id="admin-menu-button" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="admin-sidebar">
            <?= icone('menu') ?>
        </button>

        <h1 class="admin-topbar-title"><?= htmlspecialchars($tituloPaginaAdmin, ENT_QUOTES, 'UTF-8') ?></h1>

        <div class="admin-topbar-actions">
            <a href="../index.php" target="_blank" rel="noopener noreferrer" class="button button-outline button-small admin-topbar-view-site">
                <?= icone('external-link') ?>
                <span>Ver site</span>
            </a>

            <a href="meus-dados.php" class="admin-user" title="Meus dados">
                <span class="admin-user-avatar" aria-hidden="true"><?= icone('user') ?></span>
                <span class="admin-user-name" id="admin-user-name"><?= htmlspecialchars($usuarioAdmin['nome'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?></span>
            </a>

            <!-- Sair é um POST com token (um link de outro site não consegue deslogar) -->
            <form method="post" action="logout.php" class="admin-logout-form">
                <?= campoCsrf() ?>
                <button class="button button-small admin-logout-button" id="admin-logout-button" type="submit">
                    <?= icone('log-out') ?>
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </header>

    <main class="admin-main" id="admin-main" tabindex="-1">
