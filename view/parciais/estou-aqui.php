<?php
/**
 * BLOCO "ESTOU AQUI, QUERO IR PARA..." (N2)
 * Usado na página do setor e na ficha da sala. Espera:
 *   $salaAqui     — a sala onde a pessoa está (do listarMapaSalas)
 *   $veioDoQr     — true quando a página foi aberta pelo QR da porta (?aqui=)
 *   $mapaDestinos — listarMapaSalas() (pra lista de destinos)
 * É um formulário GET pra mapa.php?de=...&sala=..., então funciona sem JavaScript.
 */
$destinosPorAndar = [];
foreach ($mapaDestinos['salas'] as $destino) {
    if ($destino['id'] !== $salaAqui['id'] && empty($destino['vazia'])) {
        $destinosPorAndar[$destino['andar']][] = $destino;
    }
}
?>
<article class="content-card estou-aqui reveal <?= $veioDoQr ? 'estou-aqui-destaque' : '' ?>" id="estou-aqui" aria-labelledby="estou-aqui-titulo">
    <h2 id="estou-aqui-titulo"><?= icone('locate-fixed') ?> <?= $veioDoQr ? 'Você está aqui: ' . htmlspecialchars($salaAqui['nome'], ENT_QUOTES, 'UTF-8') : 'Está nesta sala?' ?></h2>
    <p>Escolha para onde quer ir e veja o caminho saindo daqui.</p>
    <form class="estou-aqui-form" action="mapa.php" method="get">
        <input type="hidden" name="de" value="<?= htmlspecialchars($salaAqui['id'], ENT_QUOTES, 'UTF-8') ?>">
        <label for="estou-aqui-destino" class="visually-hidden">Para onde você quer ir?</label>
        <select id="estou-aqui-destino" name="sala" required>
            <option value="">Quero ir para…</option>
            <?php foreach ($mapaDestinos['andares'] as $idAndar => $andar): ?>
                <?php if (empty($destinosPorAndar[$idAndar])) continue; ?>
                <optgroup label="<?= htmlspecialchars($andar['nome'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($destinosPorAndar[$idAndar] as $destino): ?>
                        <option value="<?= htmlspecialchars($destino['id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($destino['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button button-primary"><?= icone('route') ?> Mostrar o caminho</button>
    </form>
</article>
