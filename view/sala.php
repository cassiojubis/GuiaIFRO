<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header sector-header">
    <div class="container reveal">
        <nav class="breadcrumb">
            <a href="index.php"><?= icone('arrow-left') ?> Início</a>
            <?= icone('chevron-right') ?>
            <a href="mapa.php">Mapa</a>
            <?= icone('chevron-right') ?>
            <strong><?= htmlspecialchars($sala['nomeCurto'], ENT_QUOTES, 'UTF-8') ?></strong>
        </nav>
        <div class="sector-title-row">
            <span class="sector-main-icon"><?= icone('door-open') ?></span>
            <div>
                <h1><?= htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p><?= htmlspecialchars($descricaoLocal, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>
</section>

<section class="section container">
    <?php require __DIR__ . '/parciais/estou-aqui.php'; ?>
    <article class="content-card setor-em-breve reveal">
        <span class="setor-em-breve-icone"><?= icone('map-pin') ?></span>
        <div>
            <h2>Sobre este espaço</h2>
            <p><?= htmlspecialchars($sala['funcao'] ?: 'Ainda não temos uma descrição deste espaço. Se você precisa de algo aqui, fale com a gente.', ENT_QUOTES, 'UTF-8') ?></p>
            <div class="setor-em-breve-acoes">
                <a href="mapa.php?sala=<?= rawurlencode($sala['id']) ?>" class="button button-primary"><?= icone('map') ?> Como chegar</a>
                <a href="contato.php" class="button button-outline"><?= icone('mail') ?> Falar conosco</a>
            </div>
        </div>
    </article>
    <?php require __DIR__ . '/parciais/acessibilidade-local.php'; ?>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
