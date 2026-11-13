<?php require __DIR__ . '/parciais/cabecalho.php'; ?>



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

<?php if ($termoInicial !== ''): ?>
<section class="section container busca-resultados-pagina" aria-labelledby="titulo-resultados">
    <h2 id="titulo-resultados">Resultados para “<?= htmlspecialchars($termoInicial, ENT_QUOTES, 'UTF-8') ?>”</h2>
    <form method="get" class="busca-filtro-acessivel">
        <input type="hidden" name="q" value="<?= htmlspecialchars($termoInicial, ENT_QUOTES, 'UTF-8') ?>">
        <label><input type="checkbox" name="acessivel" value="1" <?= !empty($soSemEscada) ? 'checked' : '' ?> data-enviar-ao-mudar> Só lugares sem escada (térreo ou com elevador)</label>
        <noscript><button type="submit" class="button button-outline button-small">Filtrar</button></noscript>
    </form>
    <?php if ($resultadosBusca): ?>
        <ul class="busca-lista">
            <?php foreach ($resultadosBusca as $resultado): ?>
                <li class="busca-item content-card">
                    <div>
                        <a href="<?= htmlspecialchars($resultado['url'], ENT_QUOTES, 'UTF-8') ?>" class="busca-item-nome">
                            <?php if ($resultado['sigla']): ?><strong><?= htmlspecialchars($resultado['sigla'], ENT_QUOTES, 'UTF-8') ?></strong> · <?php endif; ?><?= htmlspecialchars($resultado['nome'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <small>
                            <?= htmlspecialchars($resultado['andar'] ?? 'Local ainda não marcado no mapa', ENT_QUOTES, 'UTF-8') ?>
                            <?= $resultado['motivo'] ? ' · ' . htmlspecialchars($resultado['motivo'], ENT_QUOTES, 'UTF-8') : '' ?>
                        </small>
                    </div>
                    <?php if ($resultado['url_mapa']): ?>
                        <a href="<?= htmlspecialchars($resultado['url_mapa'], ENT_QUOTES, 'UTF-8') ?>" class="button button-primary button-small"><?= icone('map-pin') ?> Ver no mapa</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Não achamos nada com essas palavras. Tente o nome do setor, a sigla (ex.: CRA) ou o serviço (ex.: matrícula). Se não achar, <a href="contato.php">fale com a gente</a>.</p>
    <?php endif; ?>
</section>
<?php endif; ?>

<section class="section container">
    <!-- ÁREA DE BUSCA E FILTROS -->
    <div class="category-toolbar reveal">
        <label class="search-field category-search">
            <?= icone('search') ?>
            <input
                id="category-search"
                type="search"
                value=""
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

                <footer class="category-card-footer">
                    <?php // Só vira link quando o setor tem página; senão avisa em vez de levar a um 404. ?>
                    <?php if ($categoria['url_setor']): ?>
                        <a href="<?= htmlspecialchars($categoria['url_setor'], ENT_QUOTES, 'UTF-8') ?>">
                            Ver detalhes <?= icone('arrow-right') ?>
                        </a>
                    <?php else: ?>
                        <span class="category-sem-pagina">Informações em breve</span>
                    <?php endif; ?>
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

<?php require __DIR__ . '/parciais/rodape.php'; ?>
