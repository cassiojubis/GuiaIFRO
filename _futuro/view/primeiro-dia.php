<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb"><a href="index.php">Início</a><?= icone('chevron-right') ?><strong>Primeiro dia</strong></nav>
        <h1>Seu primeiro dia no campus</h1>
        <p>Um roteiro com os lugares que todo calouro precisa conhecer. Siga na ordem que quiser e marque cada parada.</p>
    </div>
</section>

<section class="section container">
    <?php if ($seloGanho): ?>
        <!-- Abriu pelo QR escondido da parada: ganhou o selo -->
        <article class="content-card selo-ganho" id="selo-ganho" data-selo-id="<?= (int) $seloGanho['id'] ?>" role="status">
            <span class="selo-icone"><?= icone('stamp') ?></span>
            <div>
                <h2>Selo ganho: <?= htmlspecialchars($seloGanho['titulo'], ENT_QUOTES, 'UTF-8') ?>!</h2>
                <?php if ($seloGanho['curiosidade']): ?>
                    <p><strong>Você sabia?</strong> <?= htmlspecialchars($seloGanho['curiosidade'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <div class="primeiro-dia-progresso" id="primeiro-dia-progresso" hidden>
        <p><strong id="primeiro-dia-contagem">0 de <?= count($paradas) ?></strong> paradas feitas · <span id="primeiro-dia-selos">0</span> selos no passaporte</p>
        <div class="primeiro-dia-barra" role="progressbar" aria-labelledby="primeiro-dia-contagem" aria-valuemin="0" aria-valuemax="<?= count($paradas) ?>" aria-valuenow="0"><span id="primeiro-dia-barra"></span></div>
    </div>

    <article class="content-card primeiro-dia-fim" id="primeiro-dia-fim" hidden>
        <span class="selo-icone"><?= icone('party-popper') ?></span>
        <div>
            <h2>Parabéns, você conhece o campus!</h2>
            <p>Você passou por todas as paradas. Bem-vindo ao IFRO Campus Guajará-Mirim! Se precisar de algo, o <a href="mapa.php">mapa</a> e a <a href="categorias.php">busca</a> estão sempre aqui.</p>
            <button type="button" class="button button-outline button-small" id="primeiro-dia-recomecar">Recomeçar o roteiro</button>
        </div>
    </article>

    <?php if (!$paradas): ?>
        <p>O roteiro ainda não foi montado.</p>
    <?php endif; ?>

    <ol class="primeiro-dia-lista">
        <?php foreach ($paradas as $numero => $parada): ?>
            <li class="content-card primeiro-dia-parada" data-parada="<?= (int) $parada['id'] ?>">
                <div class="primeiro-dia-numero" aria-hidden="true"><?= $numero + 1 ?></div>
                <div class="primeiro-dia-corpo">
                    <h2><?= htmlspecialchars($parada['titulo'], ENT_QUOTES, 'UTF-8') ?> <span class="primeiro-dia-selo" hidden title="Selo do passaporte"><?= icone('stamp') ?> selo</span></h2>
                    <p><?= htmlspecialchars($parada['o_que_fazer'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="setor-em-breve-acoes">
                        <?php if ($parada['sala_id']): ?>
                            <a href="mapa.php?sala=<?= rawurlencode($parada['sala_id']) ?>" class="button button-outline button-small"><?= icone('map-pin') ?> Como chegar</a>
                        <?php endif; ?>
                        <?php if ($parada['setor_id']): ?>
                            <a href="setor.php?id=<?= rawurlencode($parada['setor_id']) ?>" class="button button-outline button-small"><?= icone('info') ?> Sobre o setor</a>
                        <?php endif; ?>
                        <button type="button" class="button button-primary button-small primeiro-dia-feito" aria-pressed="false" hidden><?= icone('check') ?> <span>Marcar como feito</span></button>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
    <p class="hoje-nota">Seu progresso fica salvo só neste aparelho.</p>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
