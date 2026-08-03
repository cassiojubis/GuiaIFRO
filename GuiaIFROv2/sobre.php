<?php
$tituloPagina = 'Sobre o IFRO';
$paginaAtual = 'sobre';
require __DIR__ . '/componentes/cabecalho.php';

$numeros = [
    ['book-open', '15+', 'Cursos oferecidos'],
    ['users', '1.200+', 'Estudantes ativos'],
    ['award', '120+', 'Servidores'],
    ['trending-up', '85%', 'Empregabilidade'],
];

$valores = [
    'Educação pública, gratuita e de qualidade',
    'Inclusão social e democratização do conhecimento',
    'Ética, transparência e compromisso social',
    'Inovação e excelência no ensino e na pesquisa',
    'Desenvolvimento sustentável da região amazônica',
    'Respeito à diversidade e aos direitos humanos',
];

$linhaDoTempo = [
    ['2008', 'Criação do IFRO', 'Instituto Federal de Rondônia criado pela Lei nº 11.892, como parte da expansão da Rede Federal de Educação.'],
    ['2010', 'Inauguração do Campus', 'Campus Guajará-Mirim inicia suas atividades com os primeiros cursos técnicos integrados ao ensino médio.'],
    ['2014', 'Expansão de Cursos', 'Ampliação da oferta com cursos superiores de tecnologia e licenciaturas para atender a região.'],
    ['2018', 'Nova Infraestrutura', 'Inauguração de novos laboratórios modernos e reforma e expansão da biblioteca do campus.'],
    ['2022', 'Certificação de Excelência', 'Campus recebe certificação de excelência em ensino técnico-profissional pela avaliação nacional.'],
    ['2026', 'Hoje', 'Continuamos crescendo e transformando vidas por meio de educação de qualidade para toda a região.'],
];
?>

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

    <div class="container about-stats-wrapper">
        <div class="stats-card reveal">
            <?php foreach ($numeros as $indice => [$iconeNumero, $valorNumero, $rotuloNumero]): ?>
                <article class="stat-item" style="--delay: <?= $indice * 80 ?>ms">
                    <?= icone($iconeNumero) ?>
                    <strong><?= $valorNumero ?></strong>
                    <span><?= $rotuloNumero ?></span>
                </article>
            <?php endforeach; ?>
        </div>
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
                    <div><h2><?= $tituloPrincipio ?></h2><p><?= $textoPrincipio ?></p></div>
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

    <!-- Linha do tempo -->
    <section class="timeline-section">
        <header class="section-heading centered reveal">
            <p>Momentos marcantes</p>
            <h2>Linha do Tempo</h2>
        </header>

        <div class="timeline">
            <?php foreach ($linhaDoTempo as $indice => [$ano, $titulo, $descricao]): ?>
                <article class="timeline-item <?= $indice % 2 !== 0 ? 'right' : 'left' ?> reveal">
                    <span class="timeline-dot"></span>
                    <div class="timeline-card">
                        <em><?= $ano ?></em>
                        <h3><?= $titulo ?></h3>
                        <p><?= $descricao ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

<?php require __DIR__ . '/componentes/rodape.php'; ?>