<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="section container" style="padding-top: 4rem; padding-bottom: 5rem;">
    <div class="map-empty reveal" style="max-width: 480px; margin: 0 auto; text-align: center;">
        <span><?= icone('map-pin-off') ?></span>
        <h2 style="font-size: 2.4rem; margin-bottom: 0.25rem;">404</h2>
        <h2>Página não encontrada</h2>
        <p>A página que você tentou acessar não existe ou foi movida. Tente buscar o setor pelo nome ou sigla:</p>

        <?php // Busca já preenchida com o que veio no endereço (ex.: setor.php?id=protocolo -> "protocolo") ?>
        <form class="erro404-busca" action="categorias.php" method="get" role="search">
            <label for="erro404-busca-campo" class="visually-hidden">Buscar setor</label>
            <input type="search" id="erro404-busca-campo" name="q" value="<?= htmlspecialchars($termoSugerido ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex.: CRA, biblioteca, matrícula">
            <button type="submit" class="button button-primary"><?= icone('search') ?> Buscar</button>
        </form>

        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem;">
            <a href="index.php" class="button button-outline"><?= icone('house') ?> Ir para o início</a>
            <a href="mapa.php" class="button button-outline"><?= icone('map') ?> Ver o mapa</a>
            <a href="contato.php" class="button button-outline"><?= icone('mail') ?> Falar conosco</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
