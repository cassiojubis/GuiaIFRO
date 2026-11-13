<?php
/**
 * "ACESSIBILIDADE DO LOCAL" (I3) — na página do setor e na ficha da sala.
 * Espera $salaAcess (sala do mapa) e $dadosAcess (buscarAcessibilidadeSala).
 * Nada é presumido: o que ninguém conferiu aparece como "Não informado".
 */
$rotulosAcess = itensAcessibilidadeSala();
?>
<article class="content-card acessibilidade-local">
    <h2><?= icone('accessibility') ?> Acessibilidade do local</h2>
    <?php if ($salaAcess['andar'] !== 'terreo' && $dadosAcess['elevador'] !== 1): ?>
        <p class="aviso-faixa"><?= icone('triangle-alert') ?>
            <span>Fica no <?= htmlspecialchars(listarMapaSalas()['andares'][$salaAcess['andar']]['nome'] ?? 'andar de cima', ENT_QUOTES, 'UTF-8') ?> e o acesso cadastrado é pela escada.
            Se precisar evitar escada, peça atendimento no térreo ou fale com o <a href="setor.php?id=napne">NAPNE</a>.</span></p>
    <?php endif; ?>
    <ul class="acessibilidade-itens">
        <?php foreach ($rotulosAcess as $item => $rotulo): ?>
            <?php $valor = $dadosAcess[$item]; ?>
            <li class="<?= $valor === 1 ? 'tem' : ($valor === 0 ? 'nao-tem' : 'nao-informado') ?>">
                <span><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= $valor === 1 ? 'Sim' : ($valor === 0 ? 'Não' : 'Não informado') ?></strong>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if (!empty($dadosAcess['observacao'])): ?>
        <p><?= htmlspecialchars($dadosAcess['observacao'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
</article>
