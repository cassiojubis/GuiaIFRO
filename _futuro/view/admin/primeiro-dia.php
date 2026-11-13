<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Primeiro dia (calouros)</h2>
        <p>Paradas do roteiro de <a href="../primeiro-dia.php" target="_blank" rel="noopener noreferrer">primeiro-dia.php</a> (o ideal é de 6 a 8). Cada parada tem um QR secreto: quem escanear ganha o selo do passaporte e vê a curiosidade.</p>
    </div>
    <div class="admin-row-actions">
        <a href="primeiro-dia.php?imprimir=1" target="_blank" rel="noopener noreferrer" class="button button-outline"><?= icone('printer') ?><span>Imprimir QR das paradas</span></a>
        <button type="button" class="button button-primary" data-abrir-modal="modal-parada" data-titulo="Nova parada"><?= icone('plus') ?><span>Nova parada</span></button>
    </div>
</div>

<ul class="admin-lista-informacoes">
    <?php foreach ($paradasAdmin as $posicao => $parada): ?>
        <li class="admin-item-informacao">
            <div class="admin-item-informacao-corpo">
                <strong><?= ($posicao + 1) . '. ' . htmlspecialchars($parada['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!(int) $parada['publicado']): ?><span class="admin-badge-destaque">Oculta</span><?php endif; ?>
                <p><?= htmlspecialchars($parada['o_que_fazer'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="admin-item-informacao-acoes">
                <?php foreach (['cima' => 'chevron-up', 'baixo' => 'chevron-down'] as $direcao => $iconeDirecao): ?>
                    <form method="post" class="admin-form-inline">
                        <?= campoCsrf() ?>
                        <input type="hidden" name="acao" value="mover"><input type="hidden" name="direcao" value="<?= $direcao ?>"><input type="hidden" name="id" value="<?= (int) $parada['id'] ?>">
                        <button type="submit" class="admin-icon-button" aria-label="Mover para <?= $direcao ?>" <?= ($direcao === 'cima' && $posicao === 0) || ($direcao === 'baixo' && $posicao === count($paradasAdmin) - 1) ? 'disabled' : '' ?>><?= icone($iconeDirecao) ?></button>
                    </form>
                <?php endforeach; ?>
                <button type="button" class="admin-icon-button" aria-label="Editar" data-abrir-modal="modal-parada" data-titulo="Editar parada"
                    data-valores="<?= dadosParaModal(['id' => $parada['id'], 'titulo' => $parada['titulo'], 'setor_id' => (string) $parada['setor_id'], 'o_que_fazer' => $parada['o_que_fazer'], 'curiosidade' => (string) $parada['curiosidade'], 'publicado' => (bool) $parada['publicado']]) ?>"><?= icone('pencil') ?></button>
                <form method="post" class="admin-form-inline">
                    <?= campoCsrf() ?>
                    <input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $parada['id'] ?>">
                    <button type="submit" class="admin-icon-button admin-icon-button-danger" aria-label="Excluir" data-confirmar-exclusao="Excluir a parada &quot;<?= htmlspecialchars($parada['titulo'], ENT_QUOTES, 'UTF-8') ?>&quot;? O QR impresso dela para de dar selo."><?= icone('trash-2') ?></button>
                </form>
            </div>
        </li>
    <?php endforeach; ?>
</ul>

<div class="admin-modal" id="modal-parada" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-parada-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-parada-titulo" data-titulo-modal>Nova parada</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário"><?= icone('x') ?></button>
        </div>
        <form class="admin-form" method="post" novalidate>
            <?= campoCsrf() ?>
            <input type="hidden" name="acao" value="salvar">
            <input type="hidden" name="id" value="" data-limpar>
            <div class="admin-field">
                <label for="parada-titulo">Nome da parada</label>
                <input type="text" id="parada-titulo" name="titulo" required maxlength="150" placeholder="Ex.: Biblioteca">
                <span class="admin-field-error">Informe o nome.</span>
            </div>
            <div class="admin-field">
                <label for="parada-setor">Setor (pra mostrar o caminho no mapa)</label>
                <select id="parada-setor" name="setor_id">
                    <option value="">Nenhum (ex.: SUAP, que é online)</option>
                    <?php foreach ($setoresParaParada as $setor): ?>
                        <option value="<?= htmlspecialchars($setor['id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(($setor['sigla'] ? $setor['sigla'] . ' – ' : '') . $setor['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-field">
                <label for="parada-fazer">O que o calouro faz ali</label>
                <textarea id="parada-fazer" name="o_que_fazer" rows="3" required maxlength="500"></textarea>
                <span class="admin-field-error">Diga o que fazer na parada.</span>
            </div>
            <div class="admin-field">
                <label for="parada-curiosidade">Curiosidade (aparece ao ganhar o selo)</label>
                <textarea id="parada-curiosidade" name="curiosidade" rows="2" maxlength="500" placeholder="Um fato da história do campus"></textarea>
            </div>
            <label class="admin-checkbox"><input type="checkbox" name="publicado" value="1" checked><span>Aparece no roteiro</span></label>
            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Salvar parada</span></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
