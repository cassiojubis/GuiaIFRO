<?php
// O CSS do mapa só é carregado nesta página e na home (ver parciais/cabecalho.php)
$estilosExtras = ['css/mapa-campus.css'];
require __DIR__ . '/parciais/cabecalho.php';
?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb">
            <a href="index.php">Início</a>
            <?= icone('chevron-right') ?>
            <strong>Mapa Interativo</strong>
        </nav>
        <h1>Mapa do Campus</h1>
        <p>Escolha para onde quer ir na lista ou no mapa. Em <strong>Saindo de</strong>, diga onde você está (portão, prédio ou sala): o caminho aparece passo a passo, na planta 2D ou em 3D.</p>
        <p class="mapa-texto-link"><?= icone('list') ?> Prefere sem desenho (leitor de tela, celular simples)? <a href="mapa-texto.php">Veja o mapa em texto</a>.</p>
    </div>
</section>

<section class="section container mapa-secao">
    <?php if ($dadosMapa['salaNaoEncontrada']): ?>
        <p class="aviso-faixa" role="alert"><?= icone('triangle-alert') ?> <span>Não encontramos a sala <strong><?= htmlspecialchars($dadosMapa['idSalaPedida'], ENT_QUOTES, 'UTF-8') ?></strong>. Escolha o destino na lista do mapa.</span></p>
    <?php endif; ?>
    <?php require __DIR__ . '/parciais/mapa-interativo.php'; ?>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
