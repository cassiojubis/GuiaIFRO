<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb"><a href="index.php">Início</a><?= icone('chevron-right') ?><strong>Como faço para…</strong></nav>
        <h1>Como faço para…</h1>
        <p>Passo a passo das coisas mais pedidas: onde ir, o que levar e quanto tempo demora.</p>
    </div>
</section>

<section class="section container">
    <?php if ($fluxos): ?>
        <ul class="busca-lista">
            <?php foreach ($fluxos as $fluxo): ?>
                <li class="busca-item content-card">
                    <div>
                        <a href="como-faco.php?f=<?= rawurlencode($fluxo['slug']) ?>" class="busca-item-nome"><?= htmlspecialchars($fluxo['titulo'], ENT_QUOTES, 'UTF-8') ?></a>
                        <small>
                            <?= htmlspecialchars(implode(' · ', array_filter([
                                $fluxo['setor_sigla'] ?: $fluxo['setor_nome'],
                                $canais[$fluxo['canal']] ?? null,
                                $fluxo['prazo'],
                            ])), ENT_QUOTES, 'UTF-8') ?>
                        </small>
                    </div>
                    <a href="como-faco.php?f=<?= rawurlencode($fluxo['slug']) ?>" class="button button-outline button-small">Ver o passo a passo <?= icone('arrow-right') ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Ainda não temos nenhum passo a passo publicado.</p>
    <?php endif; ?>
    <p style="margin-top: 1.5rem;">Não achou? <a href="contato.php">Pergunte pra gente</a> ou use a <a href="categorias.php">busca de setores</a>.</p>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
