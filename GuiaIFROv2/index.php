<?php
$tituloPagina = 'Início';
$paginaAtual = 'inicio';
require __DIR__ . '/componentes/cabecalho.php';

$chipsBusca = ['matrícula', 'biblioteca', 'auxílio', 'SUAP', 'CRA', 'estágio', 'calendário'];
$estatisticas = [
    ['icone' => 'graduation-cap', 'valor' => '15+', 'rotulo' => 'Cursos'],
    ['icone' => 'users', 'valor' => '1.200+', 'rotulo' => 'Alunos'],
    ['icone' => 'star', 'valor' => '120+', 'rotulo' => 'Servidores'],
    ['icone' => 'building-2', 'valor' => '30+', 'rotulo' => 'Setores'],
];
?>

<!-- HERO PRINCIPAL -->
<section class="hero-home">
    <div class="hero-decoration hero-circle-large"></div>
    <div class="hero-decoration hero-circle-small"></div>

    <div class="container hero-home-content">
        <div class="hero-copy reveal">
            <div class="official-badge">
                <span class="status-dot"></span>
                Sistema Oficial do IFRO — Campus Guajará-Mirim
            </div>

            <h1>Bem-vindo ao<br><span>Guia IFRO</span></h1>
            <p>Encontre setores, coordenações, serviços, horários e informações do campus em um só lugar.</p>

            <!-- Busca principal; o termo é enviado para categorias.php -->
            <form class="hero-search" action="categorias.php" method="get" id="hero-search-form">
                <div class="hero-search-row">
                    <?= icone('search') ?>
                    <input
                        type="search"
                        id="hero-search-input"
                        name="q"
                        placeholder="Ex: matrícula, biblioteca, assistência estudantil..."
                        aria-label="Buscar informação no campus"
                    >
                    <button type="submit" class="button button-primary">Buscar</button>
                </div>

                <div class="search-chips" aria-label="Sugestões de busca">
                    <?php foreach ($chipsBusca as $chip): ?>
                        <button type="button" class="search-chip" data-search-chip="<?= htmlspecialchars($chip) ?>">
                            <?= htmlspecialchars($chip) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Card flutuante com os números do campus -->
    <div class="container stats-wrapper reveal">
        <div class="stats-card">
            <?php foreach ($estatisticas as $item): ?>
                <article class="stat-item">
                    <?= icone($item['icone']) ?>
                    <strong><?= htmlspecialchars($item['valor']) ?></strong>
                    <span><?= htmlspecialchars($item['rotulo']) ?></span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ACESSO RÁPIDO -->
<section class="section container quick-access-section">
    <header class="section-heading reveal">
        <p>Acesso rápido</p>
        <h2>O que você procura?</h2>
    </header>

    <div class="quick-grid">
        <?php foreach ($atalhosRapidos as $indice => $atalho): ?>
            <a href="<?= htmlspecialchars($atalho['url']) ?>" class="quick-card reveal" style="--delay: <?= $indice * 60 ?>ms">
                <span class="quick-icon color-<?= htmlspecialchars($atalho['cor']) ?>">
                    <?= icone($atalho['icone']) ?>
                </span>
                <strong><?= htmlspecialchars($atalho['titulo']) ?></strong>
                <small><?= htmlspecialchars($atalho['descricao']) ?></small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- CATEGORIAS EM DESTAQUE -->
<section class="section container">
    <div class="section-heading-row reveal">
        <header class="section-heading">
            <p>Setores do campus</p>
            <h2>Categorias</h2>
        </header>
        <a href="categorias.php" class="text-link desktop-only">Ver todas <?= icone('arrow-right') ?></a>
    </div>

    <div class="category-preview-grid">
        <?php foreach (array_slice($categorias, 0, 8) as $indice => $categoria): ?>
            <a href="categorias.php" class="category-preview-card reveal" style="--delay: <?= $indice * 45 ?>ms">
                <span class="category-icon color-<?= htmlspecialchars($categoria['cor']) ?>">
                    <?= icone($categoria['icone']) ?>
                </span>
                <span class="category-preview-copy">
                    <strong><?= htmlspecialchars($categoria['titulo']) ?></strong>
                    <small><?= htmlspecialchars($categoria['descricaoCurta']) ?></small>
                    <em><?= (int) $categoria['quantidade'] ?> setores</em>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- CHAMADA PARA O MAPA -->
<section class="section-small container">
    <article class="map-cta reveal">
        <div class="map-cta-circles"></div>

        <div class="map-cta-copy">
            <span class="white-badge"><?= icone('map-pin') ?> Mapa interativo</span>
            <h2>Explore o Campus<br>Guajará-Mirim</h2>
            <p>Visualize todos os blocos, laboratórios e setores do campus de forma interativa. Clique nos blocos para ver informações e contatos.</p>
            <a href="mapa.php" class="button button-white">Abrir mapa interativo <?= icone('arrow-right') ?></a>
        </div>

        <div class="map-mini-grid desktop-only">
            <?php
            $miniBlocos = [
                ['building-2', 'Administrativo', '3 setores'],
                ['graduation-cap', 'Pedagógico', '5 setores'],
                ['book-open', 'Biblioteca', '2 setores'],
                ['laptop', 'Laboratórios', '3 setores'],
            ];
            ?>
            <?php foreach ($miniBlocos as [$iconeMini, $nomeMini, $subMini]): ?>
                <div class="map-mini-card">
                    <?= icone($iconeMini) ?>
                    <strong><?= $nomeMini ?></strong>
                    <small><?= $subMini ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<!-- NOTÍCIAS E DIFERENCIAIS -->
<section class="section container">
    <div class="home-columns">
        <div>
            <header class="section-heading reveal">
                <p>Avisos</p>
                <h2 class="heading-medium">Notícias do campus</h2>
            </header>

            <div class="news-list">
                <?php foreach ($noticias as $indice => $noticia): ?>
                    <a href="#" class="news-card reveal" style="--delay: <?= $indice * 60 ?>ms">
                        <span class="news-icon <?= $noticia['urgente'] ? 'urgent' : '' ?>">
                            <?= icone('bell') ?>
                        </span>
                        <span class="news-copy">
                            <strong><?= htmlspecialchars($noticia['titulo']) ?></strong>
                            <span class="news-meta">
                                <span><?= icone('clock') ?> <?= htmlspecialchars($noticia['data']) ?></span>
                                <em class="<?= $noticia['urgente'] ? 'urgent' : '' ?>"><?= htmlspecialchars($noticia['categoria']) ?></em>
                            </span>
                        </span>
                        <?= icone('chevron-right', 'news-arrow') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <aside>
            <header class="section-heading reveal">
                <p>Por que usar</p>
                <h2 class="heading-medium">Diferenciais</h2>
            </header>

            <?php
            $diferenciais = [
                ['zap', 'Informações atualizadas', 'Dados de contato e horários sempre atualizados pela administração do campus.'],
                ['shield', 'Sistema oficial', 'Plataforma homologada pelo IFRO — conteúdo verificado e confiável.'],
                ['trending-up', 'Acesso rápido', 'Encontre qualquer setor em segundos, sem precisar perguntar a ninguém.'],
            ];
            ?>
            <div class="feature-list">
                <?php foreach ($diferenciais as $indice => [$iconeDif, $tituloDif, $textoDif]): ?>
                    <article class="feature-card reveal" style="--delay: <?= $indice * 80 ?>ms">
                        <span><?= icone($iconeDif) ?></span>
                        <div>
                            <strong><?= $tituloDif ?></strong>
                            <p><?= $textoDif ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>
</section>

<!-- CHAMADA FINAL PARA CONTATO -->
<section class="container contact-cta-section">
    <article class="contact-cta reveal">
        <div>
            <h2>Precisa de ajuda?</h2>
            <p>Entre em contato pelos nossos canais oficiais — estamos prontos para atender.</p>
            <div class="contact-inline-list">
                <span><?= icone('phone') ?> <strong>(69) 3541-5282</strong></span>
                <span><?= icone('mail') ?> <strong>guajara-mirim@ifro.edu.br</strong></span>
            </div>
        </div>
        <a href="contato.php" class="button button-primary">Formulário de contato <?= icone('arrow-right') ?></a>
    </article>
</section>

<?php require __DIR__ . '/componentes/rodape.php'; ?>