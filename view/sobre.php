<?php require __DIR__ . '/parciais/cabecalho.php'; ?>



<!-- HERO DA PÁGINA SOBRE -->
<section class="about-hero">
    <div class="about-decoration about-decoration-one"></div>
    <div class="about-decoration about-decoration-two"></div>

    <div class="container reveal">
        <nav class="breadcrumb breadcrumb-light">
            <a href="index.php">Início</a><?= icone('chevron-right') ?><strong>Sobre</strong>
        </nav>
        <h1>Sobre o IFRO</h1>
        <p>Conheça a história, missão, visão e valores do Campus Guajará-Mirim — referência em educação na Amazônia.</p>
    </div>


</section>

<section class="section container about-content">
    <!-- História e princípios -->
    <div class="about-history-grid">
        <article class="reveal">
            <header class="section-heading">
                <p>Nossa trajetória</p>
                <h2>Nossa História</h2>
            </header>
            <div class="prose">
                <p>O Instituto Federal de Educação, Ciência e Tecnologia de Rondônia (IFRO) foi criado em 2008, através da Lei nº 11.892, como parte da expansão da Rede Federal de Educação Profissional, Científica e Tecnológica no Brasil.</p>
                <p>O Campus Guajará-Mirim iniciou suas atividades em 2010, com a missão de promover educação profissional e tecnológica de excelência, contribuindo para o desenvolvimento econômico e social da região fronteiriça entre Brasil e Bolívia.</p>
                <p>Ao longo dos anos, o campus consolidou-se como referência em educação na região, oferecendo cursos técnicos integrados ao ensino médio, cursos superiores, licenciaturas e pós-graduação.</p>
                <p>Hoje contamos com infraestrutura moderna, laboratórios equipados, biblioteca atualizada e corpo docente altamente qualificado.</p>
            </div>
        </article>

        <div class="principles-list">
            <?php
            $principios = [
                ['target', 'Missão', 'Promover educação profissional, científica e tecnológica por meio do ensino, pesquisa e extensão, com foco na formação integral do cidadão e no desenvolvimento sustentável.'],
                ['eye', 'Visão', 'Ser referência em educação profissional e tecnológica na Amazônia, reconhecida pela qualidade, inovação e compromisso com o desenvolvimento regional.'],
                ['heart', 'Valores', 'Ética, transparência, inclusão social, excelência no ensino, respeito à diversidade e compromisso com o desenvolvimento sustentável da região.'],
            ];
            ?>
            <?php foreach ($principios as $indice => [$iconePrincipio, $tituloPrincipio, $textoPrincipio]): ?>
                <article class="principle-card reveal" style="--delay: <?= $indice * 100 ?>ms">
                    <span><?= icone($iconePrincipio) ?></span>
                    <div><h2><?= htmlspecialchars($tituloPrincipio, ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars($textoPrincipio, ENT_QUOTES, 'UTF-8') ?></p></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Valores institucionais -->
    <section class="about-values-section">
        <header class="section-heading centered reveal">
            <p>Princípios que nos guiam</p>
            <h2>Nossos Valores</h2>
        </header>
        <div class="values-grid">
            <?php foreach ($valores as $indice => $valor): ?>
                <article class="value-card reveal" style="--delay: <?= $indice * 60 ?>ms">
                    <?= icone('circle-check-big') ?><strong><?= htmlspecialchars($valor) ?></strong>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Diversidade e inclusão -->
    <section class="section" id="diversidade">
        <header class="section-heading centered reveal">
            <p>Para todo mundo</p>
            <h2>Diversidade e Inclusão</h2>
        </header>
        <div class="pilares-grid">
            <?php foreach ($pilaresInclusao as [$iconePilar, $tituloPilar, $textoPilar, $linkPilar, $textoLinkPilar]): ?>
                <div class="acessibilidade-secao reveal" style="margin-bottom: 0;">
                    <span class="acessibilidade-kicker"><?= icone($iconePilar) ?></span>
                    <h3><?= htmlspecialchars($tituloPilar, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="acessibilidade-descricao" style="margin-bottom: 0;">
                        <?= htmlspecialchars($textoPilar, ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($linkPilar): ?>
                            <a href="<?= htmlspecialchars($linkPilar, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($textoLinkPilar, ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Linha do tempo -->
    <section class="timeline-section">
        <header class="section-heading centered reveal">
            <p>Momentos marcantes</p>
            <h2>Linha do Tempo</h2>
        </header>

        <div class="timeline">
            <?php foreach ($linhaDoTempo as $indice => [$ano, $titulo, $descricao, $imagemMarco]): ?>
                <article class="timeline-item <?= $indice % 2 !== 0 ? 'right' : 'left' ?> reveal">
                    <span class="timeline-dot"></span>
                    <div class="timeline-card">
                        <?php if (!empty($imagemMarco)): ?>
                            <img class="timeline-card-image" src="<?= htmlspecialchars($imagemMarco) ?>" alt="Foto ilustrativa de <?= htmlspecialchars($titulo) ?> (<?= htmlspecialchars($ano) ?>)" loading="lazy">
                        <?php endif; ?>
                        <em><?= htmlspecialchars($ano, ENT_QUOTES, 'UTF-8') ?></em>
                        <h3><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
