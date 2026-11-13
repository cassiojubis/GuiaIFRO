<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb">
            <a href="index.php">Início</a><?= icone('chevron-right') ?>
            <a href="noticias.php">Notícias</a><?= icone('chevron-right') ?>
            <strong><?= htmlspecialchars(mb_strimwidth($noticia['titulo'], 0, 40, '…'), ENT_QUOTES, 'UTF-8') ?></strong>
        </nav>
        <h1><?= htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= icone('clock') ?> <?= htmlspecialchars($noticia['data'], ENT_QUOTES, 'UTF-8') ?><?= $noticia['categoria'] ? ' · ' . htmlspecialchars($noticia['categoria'], ENT_QUOTES, 'UTF-8') : '' ?></p>
    </div>
</section>

<section class="section container">
    <article class="content-card noticia-texto">
        <?php if ($noticia['resumo']): ?>
            <p class="noticia-resumo"><?= htmlspecialchars($noticia['resumo'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php foreach ($paragrafos as $paragrafo): ?>
            <p><?= nl2br(htmlspecialchars($paragrafo, ENT_QUOTES, 'UTF-8')) ?></p>
        <?php endforeach; ?>
        <?php if (!$paragrafos && !$noticia['resumo']): ?>
            <p>Mais detalhes em breve.</p>
        <?php endif; ?>
    </article>
    <p style="margin-top: 1.5rem;"><a href="noticias.php" class="text-link"><?= icone('arrow-left') ?> Todas as notícias</a></p>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
