<?php
/**
 * MAPA INTERATIVO DO CAMPUS (planta 2D, maquete 3D e rotas), o mesmo na página do mapa e na home.
 * Quem inclui já chamou prepararMapa() (controller/MapaDados.php) e guardou em $dadosMapa.
 * O css/mapa-campus.css precisa estar em $estilosExtras.
 * O js/mapa-campus.js desenha tudo dentro do #mapa-campus: lista de salas, "Saindo de",
 * passo a passo, 2D/3D e andares. Os dados vão em JSON (as flags JSON_HEX_* trocam < > & ' "
 * por códigos, então nenhum nome de sala consegue "fechar" a tag do script).
 */
?>
<div id="mapa-campus" class="cm-root">
    <noscript>
        <p class="cm-sem-js">O mapa desenhado precisa de JavaScript. Use o <a href="mapa-texto.php">mapa em texto</a>: ele tem todas as salas e o caminho até cada uma.</p>
    </noscript>
</div>
<script type="application/json" id="mapa-campus-dados"><?= $dadosMapa['dadosMapaJson'] ?></script>
<script src="js/mapa-campus.js?v=<?= filemtime(__DIR__ . '/../../js/mapa-campus.js') ?>" defer></script>
