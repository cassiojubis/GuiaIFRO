<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb"><a href="index.php">Início</a><?= icone('chevron-right') ?><a href="mapa.php">Mapa</a><?= icone('chevron-right') ?><strong>Mapa em texto</strong></nav>
        <h1>Mapa em texto</h1>
        <p>Todas as salas do prédio, por andar e ala, com o caminho escrito desde o portão. A "ala esquerda" e a "ala direita" são de quem entra no prédio.</p>
    </div>
</section>

<section class="section container mapa-texto">
    <nav aria-label="Ir para um andar e ala" class="mapa-texto-indice">
        <ul>
            <?php foreach ($grupos as $indice => $grupo): ?>
                <li><a href="#grupo-<?= $indice ?>"><?= htmlspecialchars($grupo['titulo'], ENT_QUOTES, 'UTF-8') ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <?php foreach ($grupos as $indice => $grupo): ?>
        <h2 id="grupo-<?= $indice ?>"><?= htmlspecialchars($grupo['titulo'], ENT_QUOTES, 'UTF-8') ?></h2>
        <ol class="mapa-texto-salas">
            <?php foreach ($grupo['salas'] as $sala): ?>
                <li class="content-card">
                    <h3><?= htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <ol class="fluxo-passos">
                        <?php foreach ($sala['passos'] as $passo): ?>
                            <li><?= htmlspecialchars($passo, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ol>
                    <p>
                        <a href="<?= htmlspecialchars($sala['url_setor'] ?: $sala['url_ficha'], ENT_QUOTES, 'UTF-8') ?>"><?= $sala['url_setor'] ? 'Página do setor' : 'Sobre esta sala' ?></a>
                        · <a href="mapa.php?sala=<?= rawurlencode($sala['id']) ?>">Ver no mapa desenhado</a>
                    </p>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
