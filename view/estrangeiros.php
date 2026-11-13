<?php require __DIR__ . '/parciais/cabecalho.php'; ?>
<?php
$e = static function ($texto): string { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
$textos = [
    'pt' => [
        'titulo' => 'Para quem vem de outro país',
        'intro' => 'Bem-vindo! Se você vem de outro país (por exemplo, da Bolívia, aqui do lado) para estudar no IFRO Guajará-Mirim, comece por aqui.',
        'docs' => 'Documentos',
        'docs_vazio' => 'Ainda estamos confirmando com o campus a lista de documentos exigidos. Enquanto isso, procure o CRA, que cuida da matrícula.',
        'onde' => 'Onde ir',
        'cra' => 'CRA (Registro Acadêmico): matrícula, documentos e certificados.',
        'caed' => 'CAED (Assistência ao Estudante): acolhimento, auxílios e acompanhamento.',
        'chegar' => 'Como chegar ao campus',
        'mapa' => 'Ver no mapa',
        'pagina' => 'Página do setor',
    ],
    'es' => [
        'titulo' => 'Si vienes de otro país',
        'intro' => '¡Bienvenido! Si vienes de otro país (por ejemplo, de Bolivia, aquí al lado) para estudiar en el IFRO Guajará-Mirim, empieza aquí.',
        'docs' => 'Documentos',
        'docs_vazio' => 'Todavía estamos confirmando con el campus la lista de documentos exigidos. Mientras tanto, busca el CRA, que se encarga de la matrícula.',
        'onde' => 'A dónde ir',
        'cra' => 'CRA (Registro Académico): matrícula, documentos y certificados.',
        'caed' => 'CAED (Asistencia al Estudiante): acogida, ayudas y acompañamiento.',
        'chegar' => 'Cómo llegar al campus',
        'mapa' => 'Ver en el mapa',
        'pagina' => 'Página del sector',
    ],
];
?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb"><a href="index.php">Início</a><?= icone('chevron-right') ?><strong>Estrangeiros · Extranjeros</strong></nav>
        <h1>Para quem vem de outro país · <span lang="es">Si vienes de otro país</span></h1>
    </div>
</section>

<section class="section container estrangeiros-colunas">
    <?php foreach (['pt' => 'pt-BR', 'es' => 'es'] as $idioma => $lang): ?>
        <?php $t = $textos[$idioma]; $c = $conteudo[$idioma]; ?>
        <article class="content-card" lang="<?= $lang ?>">
            <h2><?= $e($t['titulo']) ?></h2>
            <p><?= $e($t['intro']) ?></p>

            <h3><?= icone('file-text') ?> <?= $e($t['docs']) ?></h3>
            <?php if ($c['documentos']): ?>
                <ul class="check-list">
                    <?php foreach ($c['documentos'] as $documento): ?>
                        <li><?= icone('circle-check-big') ?><span><?= $e($documento) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="aviso-faixa"><?= icone('info') ?> <span><?= $e($t['docs_vazio']) ?></span></p>
            <?php endif; ?>

            <h3><?= icone('map-pin') ?> <?= $e($t['onde']) ?></h3>
            <ul class="estrangeiros-setores">
                <li><?= $e($t['cra']) ?> <a href="setor.php?id=cra"><?= $e($t['pagina']) ?></a><?php if ($salaCra): ?> · <a href="mapa.php?sala=<?= rawurlencode($salaCra['id']) ?>"><?= $e($t['mapa']) ?></a><?php endif; ?></li>
                <li><?= $e($t['caed']) ?> <a href="setor.php?id=caed"><?= $e($t['pagina']) ?></a><?php if ($salaCaed): ?> · <a href="mapa.php?sala=<?= rawurlencode($salaCaed['id']) ?>"><?= $e($t['mapa']) ?></a><?php endif; ?></li>
            </ul>

            <?php if ($c['chegada'] !== ''): ?>
                <h3><?= icone('bus') ?> <?= $e($t['chegar']) ?></h3>
                <p><?= nl2br($e($c['chegada'])) ?></p>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
