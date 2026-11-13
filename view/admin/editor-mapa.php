<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Editor do mapa do campus</h2>
        <p>Toque em qualquer coisa do mapa para mover, mudar o tamanho ou girar. Dentro de um prédio, escolha o andar lá em cima e arraste as salas até o lugar. Quando terminar, vá em <strong>Salvar</strong> &rarr; <strong>Salvar no site</strong>.</p>
    </div>
    <a href="../mapa.php" target="_blank" rel="noopener noreferrer" class="button button-outline button-small"><?= icone('map') ?> <span>Ver o mapa no site</span></a>
</div>

<?php if (!$mapaSalvoNoArquivo): ?>
    <p class="admin-mapa-aviso"><?= icone('info') ?> <span>O site está usando o mapa padrão (<code>model/mapa-campus-padrao.json</code>). Depois do primeiro "Salvar no site", ele passa a usar o arquivo <code>data/mapa-campus.json</code>.</span></p>
<?php endif; ?>
<p class="admin-mapa-aviso"><?= icone('info') ?> <span>Nome, função, setor e acessibilidade de cada sala (usados na ficha da sala e no QR da porta) ficam em <a href="mapa.php">Salas</a>. Use o mesmo código da sala nos dois lugares (ex.: <code>cra</code>) para o "Ver no mapa" e o QR abrirem a sala certa.</span></p>

<div id="mapa-campus" class="cm-root admin-mapa"></div>

<script type="application/json" id="mapa-campus-dados"><?= $dadosEditorJson ?></script>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
