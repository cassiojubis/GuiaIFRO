<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="section container" style="padding-top: 4rem;">
    <div class="map-empty" style="max-width: 560px; margin: 0 auto; text-align: center;">
        <span><?= icone('wifi-off') ?></span>
        <h1 style="font-size: 1.8rem;">Você está sem internet</h1>
        <p>Essa página ainda não estava guardada no seu celular. As páginas abaixo funcionam mesmo sem conexão:</p>
        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; margin: 1.25rem 0;">
            <a href="index.php" class="button button-primary"><?= icone('house') ?> Início</a>
            <a href="mapa.php" class="button button-outline"><?= icone('map') ?> Mapa</a>
            <a href="categorias.php" class="button button-outline"><?= icone('list') ?> Setores</a>
        </div>
    </div>
    <ul class="busca-lista" style="max-width: 720px; margin: 0 auto;">
        <?php foreach ($setoresOffline as $setor): ?>
            <li><a href="setor.php?id=<?= rawurlencode($setor['id']) ?>"><?= htmlspecialchars(($setor['sigla'] ? $setor['sigla'] . ' – ' : '') . $setor['nome'], ENT_QUOTES, 'UTF-8') ?></a></li>
        <?php endforeach; ?>
    </ul>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
