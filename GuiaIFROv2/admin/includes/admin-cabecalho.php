<?php
/**
 * CABEÇALHO ADMINISTRATIVO COMPARTILHADO
 *
 * Antes de incluir este arquivo, cada página do painel define:
 * $tituloPaginaAdmin e $paginaAtualAdmin (ex.: 'dashboard', 'categorias'...).
 *
 * Reaproveita as constantes e a função icone() já definidas no site público,
 * garantindo a mesma identidade visual (cores, fontes e ícones Lucide).
 */
require_once __DIR__ . '/../../componentes/config.php';

$tituloPaginaAdmin = $tituloPaginaAdmin ?? 'Painel';
$paginaAtualAdmin = $paginaAtualAdmin ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($tituloPaginaAdmin) ?> · Área Administrativa | <?= NOME_SISTEMA ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Reaproveita a base visual do site público: variáveis de cor, botões, cards, ícones -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsivo.css">

    <!-- Estilos exclusivos do painel administrativo -->
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-responsive.css">
</head>
<body class="admin-body" data-admin-page="<?= htmlspecialchars($paginaAtualAdmin) ?>">

<!-- Fundo escurecido atrás da gaveta lateral no celular -->
<div class="admin-overlay" id="admin-overlay"></div>

<?php require __DIR__ . '/admin-sidebar.php'; ?>

<div class="admin-shell">

    <!-- NAVBAR SUPERIOR: reutilizada em todas as páginas administrativas -->
    <header class="admin-topbar">
        <button class="admin-menu-button" id="admin-menu-button" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="admin-sidebar">
            <?= icone('menu') ?>
        </button>

        <h1 class="admin-topbar-title"><?= htmlspecialchars($tituloPaginaAdmin) ?></h1>

        <div class="admin-topbar-actions">
            <a href="../index.php" target="_blank" rel="noopener noreferrer" class="button button-outline button-small admin-topbar-view-site">
                <?= icone('external-link') ?>
                <span>Ver site</span>
            </a>

            <div class="admin-user">
                <span class="admin-user-avatar" aria-hidden="true"><?= icone('user') ?></span>
                <span class="admin-user-name" id="admin-user-name">Administrador</span>
            </div>

            <button class="button button-small admin-logout-button" id="admin-logout-button" type="button">
                <?= icone('log-out') ?>
                <span>Sair</span>
            </button>
        </div>
    </header>

    <main class="admin-main" id="admin-main">
