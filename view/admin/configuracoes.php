<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Configurações</h2>
        <p>Preferências gerais do Guia IFRO. Valem para o site inteiro.</p>
    </div>
</div>

<form class="admin-form admin-panel" method="post" novalidate>
    <?= campoCsrf() ?>

    <?php foreach ($camposConfiguracao as $grupo => $campos): ?>
        <h2 class="admin-panel-title admin-panel-title-espacada"><?= icone('sliders-horizontal') ?> <?= htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8') ?></h2>
        <?php foreach ($campos as $chave => [$rotulo, $tipo, $ajuda]): ?>
            <?php $valor = (string) ($valoresConfig[$chave] ?? ''); ?>
            <div class="admin-field <?= isset($errosConfig[$chave]) ? 'admin-field-invalido' : '' ?>">
                <?php if ($tipo === 'sim_nao'): ?>
                    <label class="admin-toggle">
                        <input type="checkbox" name="<?= $chave ?>" value="1" <?= $valor === '1' ? 'checked' : '' ?>>
                        <span class="admin-toggle-track" aria-hidden="true"></span>
                        <span class="admin-toggle-texto"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></span>
                    </label>
                <?php elseif ($tipo === 'texto_longo'): ?>
                    <label for="config-<?= $chave ?>"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></label>
                    <textarea id="config-<?= $chave ?>" name="<?= $chave ?>" rows="4" maxlength="3000"><?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php else: ?>
                    <label for="config-<?= $chave ?>"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></label>
                    <input type="<?= ['email' => 'email', 'url' => 'url', 'telefone' => 'tel', 'numero' => 'number'][$tipo] ?? 'text' ?>"
                        id="config-<?= $chave ?>" name="<?= $chave ?>" maxlength="255" value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $tipo === 'numero' ? 'min="7" max="730"' : '' ?> <?= $tipo === 'decimal' ? 'inputmode="decimal"' : '' ?>>
                <?php endif; ?>
                <p class="admin-campo-ajuda"><?= htmlspecialchars($ajuda, ENT_QUOTES, 'UTF-8') ?></p>
                <span class="admin-field-error"><?= htmlspecialchars($errosConfig[$chave] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <div class="admin-modal-footer admin-modal-footer-inline">
        <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Salvar configurações</span></button>
    </div>
</form>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
