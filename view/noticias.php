<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb"><a href="index.php">Início</a><?= icone('chevron-right') ?><strong>Avisos e notícias</strong></nav>
        <h1>Avisos e notícias</h1>
        <p>O que está acontecendo no campus.</p>
        <?php if ($linkWhatsapp !== ''): ?>
            <a href="<?= htmlspecialchars($linkWhatsapp, ENT_QUOTES, 'UTF-8') ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?= icone('message-circle') ?> Receber avisos no WhatsApp</a>
        <?php endif; ?>
    </div>
</section>

<section class="section container" id="avisos" aria-labelledby="titulo-avisos">
    <h2 id="titulo-avisos">Avisos de agora</h2>
    <?php if ($avisosAtivos): ?>
        <div class="avisos-lista">
            <?php foreach ($avisosAtivos as $aviso): ?>
                <article class="content-card aviso-cartao <?= $aviso['nivel'] === 'urgente' ? 'aviso-urgente' : '' ?>">
                    <h3><?= $aviso['nivel'] === 'urgente' ? icone('triangle-alert') . ' ' : '' ?><?= htmlspecialchars($aviso['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= nl2br(htmlspecialchars($aviso['mensagem'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <?php if ($aviso['fim']): ?>
                        <small>Vale até <?= htmlspecialchars((new DateTimeImmutable($aviso['fim']))->format('d/m/Y \à\s H:i'), ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Nenhum aviso no momento.</p>
    <?php endif; ?>
</section>

<section class="section container" aria-labelledby="titulo-noticias">
    <h2 id="titulo-noticias">Notícias</h2>
    <?php if ($todasNoticias): ?>
        <div class="news-list">
            <?php foreach ($todasNoticias as $noticia): ?>
                <a href="noticia.php?slug=<?= rawurlencode($noticia['slug']) ?>" class="news-card">
                    <span class="news-icon <?= $noticia['urgente'] ? 'urgent' : '' ?>"><?= icone('newspaper') ?></span>
                    <span class="news-copy">
                        <strong><?= htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if ($noticia['resumo']): ?><span><?= htmlspecialchars($noticia['resumo'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                        <span class="news-meta">
                            <span><?= icone('clock') ?> <?= htmlspecialchars($noticia['data'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($noticia['categoria']): ?><em><?= htmlspecialchars($noticia['categoria'], ENT_QUOTES, 'UTF-8') ?></em><?php endif; ?>
                        </span>
                    </span>
                    <?= icone('chevron-right', 'news-arrow') ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Nenhuma notícia publicada.</p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
