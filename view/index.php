<?php
// O mapa interativo fica logo no topo da home (o CSS dele normalmente só carrega na página do mapa)
$estilosExtras = ['css/mapa-campus.css'];
// Abertura com o vídeo do logo antes de mostrar a home (ver view/parciais/cabecalho.php)
$mostrarAbertura = true;
require __DIR__ . '/parciais/cabecalho.php';
?>

<!-- HERO PRINCIPAL (curto, pra o mapa aparecer logo depois) -->
<section class="hero-home">
    <div class="hero-decoration hero-circle-large"></div>
    <div class="hero-decoration hero-circle-small"></div>

    <div class="container hero-home-content">
        <div class="hero-copy reveal">
            <div class="official-badge">
                <span class="status-dot"></span>
                Guia do IFRO — Campus Guajará-Mirim
            </div>

            <h1>Bem-vindo ao <span>Guia IFRO</span></h1>
            <p>Encontre setores, coordenações, serviços, horários e informações do campus em um só lugar.</p>
        </div>

        <!-- Vídeo de abertura: toca uma vez e para no logo; passar o mouse toca de novo -->
        <div class="hero-video reveal" aria-hidden="true">
            <div class="hero-video-glow"></div>
            <div class="hero-video-card" id="hero-video-card">
                <video id="hero-video" autoplay muted playsinline preload="auto" poster="img/home-poster.jpg" data-poster="img/home-poster.jpg">
                    <source src="img/home.mp4" type="video/mp4" media="(min-width: 901px)">
                </video>
            </div>
            <span class="hero-video-chip hero-video-chip-top"><?= icone('map-pin') ?> Mapa do campus</span>
            <span class="hero-video-chip hero-video-chip-bottom"><?= icone('search') ?> Ache qualquer setor</span>
        </div>
    </div>
</section>

<!-- MAPA INTERATIVO: o ponto principal do Guia, já na primeira tela -->
<section class="section-small container mapa-secao mapa-home" aria-labelledby="titulo-mapa-home">
    <header class="mapa-home-topo">
        <div>
            <h2 id="titulo-mapa-home"><?= icone('map-pin') ?> Para onde você quer ir?</h2>
            <p>Escolha o destino na lista ou clique no mapa. Em <strong>Saindo de</strong>, diga onde você está: o caminho aparece passo a passo.</p>
        </div>
        <p class="mapa-texto-link"><?= icone('list') ?> <a href="mapa-texto.php">Mapa em texto</a> · <a href="mapa.php">Abrir em tela cheia</a></p>
    </header>
    <?php require __DIR__ . '/parciais/mapa-interativo.php'; ?>

    <!-- Busca logo embaixo do mapa; o termo é enviado para categorias.php -->
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
</section>

<!-- LEITURA FÁCIL (I4): botões grandes com desenho. Só aparecem com a opção ligada. -->
<section class="section-small container so-leitura-facil" aria-labelledby="titulo-facil">
    <h2 id="titulo-facil">O que você quer fazer?</h2>
    <div class="facil-grade">
        <a href="como-faco.php?f=fazer-a-matricula" class="facil-botao"><?= icone('file-text') ?><span>Matrícula</span></a>
        <a href="setor.php?id=enfermaria" class="facil-botao"><?= icone('heart-pulse') ?><span>Saúde</span></a>
        <a href="setor.php?id=biblioteca" class="facil-botao"><?= icone('book-open') ?><span>Biblioteca</span></a>
        <a href="setor.php?id=caed" class="facil-botao"><?= icone('hand-heart') ?><span>Ajuda e auxílio</span></a>
        <a href="mapa.php" class="facil-botao"><?= icone('map') ?><span>Mapa</span></a>
        <a href="contato.php" class="facil-botao"><?= icone('message-circle') ?><span>Falar com alguém</span></a>
    </div>
</section>

<!-- HOJE NO CAMPUS (N5): vem pronto do servidor e o js/script.js atualiza a cada 5 min (hoje.php) -->
<section class="section-small container hoje-no-campus" aria-labelledby="titulo-hoje" data-atualizar-hoje>
    <h2 id="titulo-hoje" class="visually-hidden">Hoje no campus</h2>
    <?php require __DIR__ . '/parciais/hoje-no-campus.php'; ?>
</section>

<!-- FAVORITOS E RECENTES: preenchidos por JS a partir do localStorage (ficam escondidos se vazio) -->
<section class="section container" id="secao-favoritos" hidden>
    <header class="section-heading reveal">
        <p><?= icone('star') ?> Seus favoritos</p>
    </header>
    <div class="quick-grid" id="lista-favoritos"></div>
</section>

<section class="section container" id="secao-recentes" hidden>
    <header class="section-heading reveal">
        <p><?= icone('history') ?> Visitados recentemente</p>
    </header>
    <div class="quick-grid" id="lista-recentes"></div>
</section>

<!-- ACESSO RÁPIDO -->
<section class="section container quick-access-section">
    <header class="section-heading reveal">
        <p>Acesso rápido</p>
        <h2>O que você procura?</h2>
    </header>
    <p class="home-como-faco"><a href="como-faco.php" class="text-link"><?= icone('list-checks') ?> Passo a passo: declaração, matrícula, trancamento…</a></p>

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
                    <em><?= htmlspecialchars($categoria['tag'], ENT_QUOTES, 'UTF-8') ?></em>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
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
                    <a href="noticia.php?slug=<?= rawurlencode($noticia['slug']) ?>" class="news-card reveal" style="--delay: <?= $indice * 60 ?>ms">
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
            <a href="noticias.php" class="text-link">Todas as notícias e avisos <?= icone('arrow-right') ?></a>
        </div>

        <aside>
            <header class="section-heading reveal">
                <p>Por que usar</p>
                <h2 class="heading-medium">Diferenciais</h2>
            </header>

            <?php
            $diferenciais = [
                ['zap', 'Tudo num lugar só', 'Contatos, horários e localização de cada setor reunidos numa página.'],
                ['map', 'Mapa do prédio', 'Veja em qual andar e ala fica cada sala, com o caminho desde o portão.'],
                ['trending-up', 'Acesso rápido', 'Encontre qualquer setor em segundos, sem precisar perguntar a ninguém.'],
            ];
            ?>
            <div class="feature-list">
                <?php foreach ($diferenciais as $indice => [$iconeDif, $tituloDif, $textoDif]): ?>
                    <article class="feature-card reveal" style="--delay: <?= $indice * 80 ?>ms">
                        <span><?= icone($iconeDif) ?></span>
                        <div>
                            <strong><?= htmlspecialchars($tituloDif, ENT_QUOTES, 'UTF-8') ?></strong>
                            <p><?= htmlspecialchars($textoDif, ENT_QUOTES, 'UTF-8') ?></p>
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
                <span><?= icone('phone') ?> <strong><?= htmlspecialchars(configuracao('contato_telefone', 'Não informado'), ENT_QUOTES, 'UTF-8') ?></strong></span>
                <span><?= icone('mail') ?> <strong><?= htmlspecialchars(configuracao('contato_email', 'Não informado'), ENT_QUOTES, 'UTF-8') ?></strong></span>
            </div>
        </div>
        <a href="contato.php" class="button button-primary">Formulário de contato <?= icone('arrow-right') ?></a>
    </article>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
