<?php
$tituloPagina = 'Categorias';
$paginaAtual = 'categorias';
require __DIR__ . '/componentes/cabecalho.php';

$termoInicial = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$tags = ['Todos', 'Ensino', 'Estudante', 'Administração', 'Pesquisa', 'Extensão', 'Serviços'];
?>

<!-- CABEÇALHO DA PÁGINA -->
<section class="page-header light-header">
    <div class="container reveal">
        <nav class="breadcrumb" aria-label="Caminho de navegação">
            <a href="index.php">Início</a>
            <?= icone('chevron-right') ?>
            <strong>Categorias</strong>
        </nav>
        <h1>Setores do Campus</h1>
        <p>Explore todos os setores, coordenações e serviços disponíveis no Campus Guajará-Mirim.</p>
    </div>
</section>

<section class="section container">
    <!-- ÁREA DE BUSCA E FILTROS -->
    <div class="category-toolbar reveal">
        <label class="search-field category-search">
            <?= icone('search') ?>
            <input
                id="category-search"
                type="search"
                value="<?= htmlspecialchars($termoInicial) ?>"
                placeholder="Buscar setor, serviço ou coordenação..."
                aria-label="Buscar categoria"
            >
            <button type="button" id="clear-category-search" aria-label="Limpar busca"><?= icone('x') ?></button>
        </label>

        <div class="filter-group" aria-label="Filtrar categorias">
            <?= icone('sliders-horizontal', 'filter-symbol') ?>
            <?php foreach ($tags as $indice => $tag): ?>
                <button type="button" class="filter-button <?= $indice === 0 ? 'active' : '' ?>" data-category-filter="<?= htmlspecialchars($tag) ?>">
                    <?= htmlspecialchars($tag) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <p class="result-count reveal" id="category-result-count">
        <strong><?= count($categorias) ?></strong> categorias encontradas
        <button type="button" id="reset-category-filters">Limpar filtros</button>
    </p>

    <!-- Os atributos data-* permitem que o JavaScript filtre os cards -->
    <div class="categories-grid" id="categories-grid">
        <?php foreach ($categorias as $indice => $categoria): ?>
            <article
                class="category-card reveal"
                style="--delay: <?= $indice * 40 ?>ms"
                data-category-card
                data-title="<?= htmlspecialchars(strtolower($categoria['titulo'])) ?>"
                data-description="<?= htmlspecialchars(strtolower($categoria['descricao'])) ?>"
                data-tag="<?= htmlspecialchars($categoria['tag']) ?>"
                data-sectors="<?= htmlspecialchars(strtolower(implode(' ', $categoria['setores']))) ?>"
            >
                <div class="category-card-top">
                    <span class="category-icon color-<?= htmlspecialchars($categoria['cor']) ?>">
                        <?= icone($categoria['icone']) ?>
                    </span>
                    <span class="category-tag"><?= htmlspecialchars($categoria['tag']) ?></span>
                </div>

                <h2><?= htmlspecialchars($categoria['titulo']) ?></h2>
                <p><?= htmlspecialchars($categoria['descricao']) ?></p>

                <div class="sector-tags" hidden>
                    <?php foreach ($categoria['setores'] as $setor): ?>
                        <span><?= htmlspecialchars($setor) ?></span>
                    <?php endforeach; ?>
                </div>

                <footer class="category-card-footer">
                    <button type="button" class="toggle-sectors" aria-expanded="false">
                        <?= (int) $categoria['quantidade'] ?> setores
                    </button>
                    <a href="setor.php?id=<?= urlencode($categoria['id']) ?>">
                        Ver detalhes <?= icone('arrow-right') ?>
                    </a>
                </footer>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- Mensagem exibida somente quando nenhum card corresponde aos filtros -->
    <div class="empty-state" id="category-empty-state" hidden>
        <span><?= icone('search') ?></span>
        <h2>Nenhum resultado encontrado</h2>
        <p>Tente outros termos ou remova os filtros.</p>
        <button type="button" class="button button-primary" id="show-all-categories">Ver todas as categorias</button>
    </div>

    <article class="bottom-green-cta reveal">
        <div>
            <h2>Não encontrou o que procura?</h2>
            <p>Entre em contato com a nossa equipe e teremos prazer em orientar você.</p>
        </div>
        <a href="contato.php" class="button button-white">Fale conosco <?= icone('arrow-right') ?></a>
    </article>
</section>

<?php require __DIR__ . '/componentes/rodape.php'; ?>