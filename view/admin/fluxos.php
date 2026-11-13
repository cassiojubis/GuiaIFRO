<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Como faço para…</h2>
        <p>Passo a passo das coisas mais pedidas (declaração, matrícula, trancamento…). Aparece em <a href="../como-faco.php" target="_blank" rel="noopener noreferrer">como-faco.php</a>.</p>
    </div>
    <button type="button" class="button button-primary" data-abrir-modal="modal-fluxo" data-titulo="Novo passo a passo"><?= icone('plus') ?><span>Novo passo a passo</span></button>
</div>

<ul class="admin-lista-informacoes">
    <?php foreach ($fluxosAdmin as $posicao => $fluxo): ?>
        <?php $publicado = (int) $fluxo['publicado'] === 1; ?>
        <li class="admin-item-informacao">
            <div class="admin-item-informacao-corpo">
                <strong><?= htmlspecialchars($fluxo['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                <p><?= htmlspecialchars(implode(' · ', array_filter([$fluxo['setor_sigla'] ?: $fluxo['setor_nome'], $canais[(string) $fluxo['canal']] ?? null, $fluxo['prazo'], textoQuantidade(count($fluxo['passos']), 'passo', 'passos')])), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="admin-item-informacao-acoes">
                <?php foreach (['cima' => 'chevron-up', 'baixo' => 'chevron-down'] as $direcao => $iconeDirecao): ?>
                    <form method="post" class="admin-form-inline">
                        <?= campoCsrf() ?>
                        <input type="hidden" name="acao" value="mover"><input type="hidden" name="direcao" value="<?= $direcao ?>"><input type="hidden" name="id" value="<?= (int) $fluxo['id'] ?>">
                        <button type="submit" class="admin-icon-button" aria-label="Mover para <?= $direcao ?>" <?= ($direcao === 'cima' && $posicao === 0) || ($direcao === 'baixo' && $posicao === count($fluxosAdmin) - 1) ? 'disabled' : '' ?>><?= icone($iconeDirecao) ?></button>
                    </form>
                <?php endforeach; ?>
                <form method="post" class="admin-form-inline">
                    <?= campoCsrf() ?>
                    <input type="hidden" name="acao" value="alternar"><input type="hidden" name="id" value="<?= (int) $fluxo['id'] ?>">
                    <button type="submit" class="admin-status-badge admin-status-<?= $publicado ? 'ativo' : 'inativo' ?>"><?= icone($publicado ? 'eye' : 'eye-off') ?><span><?= $publicado ? 'Publicado' : 'Oculto' ?></span></button>
                </form>
                <button type="button" class="admin-icon-button" aria-label="Editar" data-abrir-modal="modal-fluxo" data-titulo="Editar passo a passo"
                    data-valores="<?= dadosParaModal(['id' => $fluxo['id'], 'titulo' => $fluxo['titulo'], 'resumo' => (string) $fluxo['resumo'], 'setor_id' => (string) $fluxo['setor_id'], 'canal' => (string) $fluxo['canal'], 'prazo' => (string) $fluxo['prazo'], 'publicado' => $publicado, 'passos' => $fluxo['passos'], 'documentos' => $fluxo['documentos']]) ?>"><?= icone('pencil') ?></button>
                <form method="post" class="admin-form-inline">
                    <?= campoCsrf() ?>
                    <input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $fluxo['id'] ?>">
                    <button type="submit" class="admin-icon-button admin-icon-button-danger" aria-label="Excluir" data-confirmar-exclusao="Excluir o passo a passo &quot;<?= htmlspecialchars($fluxo['titulo'], ENT_QUOTES, 'UTF-8') ?>&quot;?"><?= icone('trash-2') ?></button>
                </form>
            </div>
        </li>
    <?php endforeach; ?>
</ul>

<div class="admin-modal" id="modal-fluxo" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel admin-modal-panel-largo" role="dialog" aria-modal="true" aria-labelledby="modal-fluxo-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-fluxo-titulo" data-titulo-modal>Novo passo a passo</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário"><?= icone('x') ?></button>
        </div>
        <form class="admin-form" method="post" novalidate>
            <?= campoCsrf() ?>
            <input type="hidden" name="acao" value="salvar">
            <input type="hidden" name="id" value="" data-limpar>
            <div class="admin-field">
                <label for="fluxo-titulo">Título (começando pelo verbo)</label>
                <input type="text" id="fluxo-titulo" name="titulo" required maxlength="200" placeholder="Ex.: Pedir histórico escolar">
                <span class="admin-field-error">Informe o título.</span>
            </div>
            <div class="admin-field">
                <label for="fluxo-resumo">Resumo</label>
                <input type="text" id="fluxo-resumo" name="resumo" maxlength="400">
            </div>
            <div class="admin-field-grid admin-field-grid-3">
                <div class="admin-field">
                    <label for="fluxo-setor">Setor responsável</label>
                    <select id="fluxo-setor" name="setor_id">
                        <option value="">Nenhum</option>
                        <?php foreach ($setoresParaFluxo as $setor): ?>
                            <option value="<?= htmlspecialchars($setor['id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(($setor['sigla'] ? $setor['sigla'] . ' – ' : '') . $setor['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="fluxo-canal">Como pedir</label>
                    <select id="fluxo-canal" name="canal">
                        <?php foreach ($canais as $valor => $rotulo): ?>
                            <option value="<?= $valor ?>"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="fluxo-prazo">Prazo</label>
                    <input type="text" id="fluxo-prazo" name="prazo" maxlength="150" placeholder="Ex.: Até 3 dias úteis">
                </div>
            </div>
            <div class="admin-field">
                <span class="admin-field-legenda">Passos (na ordem)</span>
                <ul class="admin-lista-dinamica" data-lista-dinamica="passos" data-placeholder="O que a pessoa faz neste passo" data-maximo="400"></ul>
                <button type="button" class="admin-adicionar-item" data-adicionar-item="passos"><?= icone('plus') ?><span>Adicionar passo</span></button>
                <span class="admin-field-error">Escreva pelo menos um passo.</span>
            </div>
            <div class="admin-field">
                <span class="admin-field-legenda">O que levar (documentos)</span>
                <ul class="admin-lista-dinamica" data-lista-dinamica="documentos" data-placeholder="Nome do documento"></ul>
                <button type="button" class="admin-adicionar-item" data-adicionar-item="documentos"><?= icone('plus') ?><span>Adicionar documento</span></button>
            </div>
            <label class="admin-checkbox"><input type="checkbox" name="publicado" value="1" checked><span>Publicado no site</span></label>
            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Salvar</span></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
