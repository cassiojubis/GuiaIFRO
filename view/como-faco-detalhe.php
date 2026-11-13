<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb">
            <a href="index.php">Início</a><?= icone('chevron-right') ?>
            <a href="como-faco.php">Como faço para…</a><?= icone('chevron-right') ?>
            <strong><?= htmlspecialchars($fluxo['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
        </nav>
        <h1><?= htmlspecialchars($fluxo['titulo'], ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if ($fluxo['resumo']): ?><p><?= htmlspecialchars($fluxo['resumo'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    </div>
</section>

<section class="section container">
    <div class="info-chip-grid">
        <?php
        $chips = [
            ['building-2', 'Onde', $fluxo['setor_nome'] ? (($fluxo['setor_sigla'] ? $fluxo['setor_sigla'] . ' – ' : '') . $fluxo['setor_nome']) : null],
            ['monitor-smartphone', 'Como pedir', $canais[$fluxo['canal']] ?? null],
            ['hourglass', 'Prazo', $fluxo['prazo']],
        ];
        ?>
        <?php foreach ($chips as [$iconeChip, $rotuloChip, $valorChip]): ?>
            <article class="info-chip">
                <span><?= icone($iconeChip) ?></span>
                <div><small><?= htmlspecialchars($rotuloChip, ENT_QUOTES, 'UTF-8') ?></small><strong><?= htmlspecialchars($valorChip ?: 'Não informado', ENT_QUOTES, 'UTF-8') ?></strong></div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="sector-layout">
        <div class="sector-main-column">
            <article class="content-card">
                <h2>Passo a passo</h2>
                <ol class="fluxo-passos">
                    <?php foreach ($fluxo['passos'] as $passo): ?>
                        <li><?= htmlspecialchars($passo, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ol>
                <div class="setor-em-breve-acoes">
                    <?php if ($salaDoFluxo): ?>
                        <a href="mapa.php?sala=<?= rawurlencode($salaDoFluxo['id']) ?>" class="button button-primary"><?= icone('map-pin') ?> Ver o caminho no mapa</a>
                    <?php endif; ?>
                    <?php if ($fluxo['setor_id']): ?>
                        <a href="setor.php?id=<?= rawurlencode($fluxo['setor_id']) ?>" class="button button-outline"><?= icone('building-2') ?> Página do setor</a>
                    <?php endif; ?>
                    <?php if (in_array($fluxo['canal'], ['suap', 'ambos'], true)): ?>
                        <a href="https://suap.ifro.edu.br" target="_blank" rel="noopener noreferrer" class="button button-outline"><?= icone('external-link') ?> Abrir o SUAP</a>
                    <?php endif; ?>
                </div>
            </article>
        </div>
        <aside class="sector-sidebar">
            <article class="content-card">
                <h2><?= icone('file-text') ?> O que levar</h2>
                <?php if ($fluxo['documentos']): ?>
                    <ul class="check-list">
                        <?php foreach ($fluxo['documentos'] as $documento): ?>
                            <li><?= icone('circle-check-big') ?><span><?= htmlspecialchars($documento, ENT_QUOTES, 'UTF-8') ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nenhum documento específico informado.</p>
                <?php endif; ?>
            </article>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
