<?php
$tituloPagina = 'Mapa Interativo';
$paginaAtual = 'mapa';
require __DIR__ . '/componentes/cabecalho.php';
?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb">
            <a href="index.php">Início</a>
            <?= icone('chevron-right') ?>
            <strong>Mapa Interativo</strong>
        </nav>
        <h1>Mapa do Campus</h1>
        <p>Clique nos blocos para ver setores, horários e contatos.</p>
    </div>
</section>

<section class="section container map-page-grid">
    <div>
        <!-- MAPA VISUAL: cada botão representa um bloco do campus -->
        <div class="campus-map reveal" id="campus-map">
            <div class="map-grid-pattern"></div>

            <div class="map-blocks-grid">
                <?php foreach ($blocosMapa as $bloco): ?>
                    <button
                        type="button"
                        class="map-block <?= htmlspecialchars($bloco['posicao']) ?>"
                        data-map-block="<?= htmlspecialchars($bloco['id']) ?>"
                        data-name="<?= htmlspecialchars($bloco['nome']) ?>"
                        data-abbr="<?= htmlspecialchars($bloco['sigla']) ?>"
                        data-icon="<?= htmlspecialchars($bloco['icone']) ?>"
                        data-color="<?= htmlspecialchars($bloco['cor']) ?>"
                        aria-label="Mostrar detalhes de <?= htmlspecialchars($bloco['nome']) ?>"
                    >
                        <span class="map-block-icon color-solid-<?= htmlspecialchars($bloco['cor']) ?>">
                            <?= icone($bloco['icone']) ?>
                        </span>
                        <strong><?= htmlspecialchars($bloco['sigla']) ?></strong>
                        <small><?= count($bloco['setores']) ?> setor<?= count($bloco['setores']) !== 1 ? 'es' : '' ?></small>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="map-entrance">
                <span></span>
                Entrada principal — Av. Jorge Teixeira
            </div>

            <aside class="map-legend desktop-only">
                <strong>Legenda</strong>
                <?php foreach ($blocosMapa as $bloco): ?>
                    <span><i class="legend-color color-solid-<?= htmlspecialchars($bloco['cor']) ?>"></i><?= htmlspecialchars($bloco['nome']) ?></span>
                <?php endforeach; ?>
            </aside>
        </div>

        <div class="map-shortcuts">
            <?php foreach (array_slice($blocosMapa, 0, 4) as $bloco): ?>
                <button type="button" data-map-shortcut="<?= htmlspecialchars($bloco['id']) ?>">
                    <span class="color-solid-<?= htmlspecialchars($bloco['cor']) ?>"><?= icone($bloco['icone']) ?></span>
                    <span><strong><?= htmlspecialchars($bloco['sigla']) ?></strong><small><?= count($bloco['setores']) ?> setores</small></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PAINEL LATERAL: preenchido pelo JavaScript ao selecionar um bloco -->
    <aside class="map-detail-panel reveal" id="map-detail-panel">
        <div class="map-empty" id="map-empty">
            <span><?= icone('map-pin') ?></span>
            <h2>Selecione um bloco</h2>
            <p>Clique em qualquer bloco no mapa para ver setores e informações de contato.</p>
            <small><?= icone('info') ?> <?= count($blocosMapa) ?> blocos disponíveis no campus</small>
        </div>

        <div id="map-detail-content" hidden>
            <div class="map-detail-heading">
                <div class="map-detail-title">
                    <span id="map-detail-icon"></span>
                    <div><h2 id="map-detail-name"></h2><p id="map-detail-count"></p></div>
                </div>
                <button type="button" id="close-map-detail" aria-label="Fechar detalhes"><?= icone('x') ?></button>
            </div>
            <div class="map-sector-list" id="map-sector-list"></div>
            <a href="setor.php" id="map-detail-link" class="button button-primary button-full">
                Ver detalhes completos <?= icone('arrow-right') ?>
            </a>
        </div>
    </aside>
</section>

<!-- Dados do mapa disponibilizados ao JavaScript em formato JSON -->
<script type="application/json" id="map-data"><?= json_encode($blocosMapa, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<?php require __DIR__ . '/componentes/rodape.php'; ?>