<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>
<?php $e = static function ($texto): string { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); }; ?>

<div class="admin-page-header">
    <div>
        <h2>Traduções</h2>
        <p>O português é o original. Só aparece no site o que estiver <strong>publicado</strong>; o que faltar continua em português.
            Fluxo: rascunho → revisado (por quem?) → publicado.</p>
    </div>
    <form method="get" class="admin-form-inline">
        <label for="idioma-traducao" class="visually-hidden">Idioma</label>
        <select id="idioma-traducao" name="idioma" class="admin-select-filtro" data-enviar-ao-mudar>
            <?php foreach ($idiomasTraducao as $codigo => $nome): ?>
                <option value="<?= $codigo ?>" <?= $codigo === $idioma ? 'selected' : '' ?>><?= $e($nome) ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="button button-outline button-small">Ver</button></noscript>
    </form>
</div>

<?php if ($idioma === 'wari'): ?>
    <p class="admin-aviso-info"><?= icone('info') ?>
        <span>Oro Wari é a língua de uma comunidade específica: traduza junto com falantes (professores, NAPNE, lideranças) e só publique o que um falante revisou. Dá pra anexar o áudio gravado pelo falante (o botão "Ouvir" do site toca esse áudio).</span></p>
<?php endif; ?>

<section class="admin-stats-grid" aria-label="Situação das traduções">
    <?php foreach (['falta' => ['Falta traduzir', 'rose'], 'rascunho' => ['Rascunho', 'amber'], 'revisado' => ['Revisado', 'blue'], 'publicado' => ['Publicado', 'green']] as $chave => [$rotulo, $cor]): ?>
        <div class="admin-stat-card color-solid-<?= $cor ?>"><strong><?= (int) $contagem[$chave] ?></strong><span><?= $e($rotulo) ?></span></div>
    <?php endforeach; ?>
</section>

<div class="admin-toolbar">
    <div class="admin-search-field">
        <?= icone('search') ?>
        <input type="search" placeholder="Buscar texto..." aria-label="Buscar texto" data-busca-lista="#corpo-traducoes">
    </div>
    <select class="admin-select-filtro" aria-label="Filtrar por situação" data-filtro="#corpo-traducoes" data-filtro-chave="situacao">
        <option value="todos">Todas as situações</option>
        <option value="falta">Falta traduzir</option>
        <option value="rascunho">Rascunho</option>
        <option value="revisado">Revisado</option>
        <option value="publicado">Publicado</option>
    </select>
</div>

<div class="admin-table-wrapper">
    <table class="admin-table">
        <thead><tr><th>Onde</th><th>Português</th><th>Tradução</th><th>Situação</th><th class="admin-table-acoes-col">Ações</th></tr></thead>
        <tbody id="corpo-traducoes">
            <?php foreach ($linhas as $linha): ?>
                <?php $t = $linha['traducao']; ?>
                <tr data-linha data-situacao="<?= $e($linha['situacao']) ?>" data-busca="<?= $e($linha['rotulo'] . ' ' . $linha['textoPt'] . ' ' . ($t['texto'] ?? '')) ?>">
                    <td data-rotulo="Onde"><?= $e($linha['rotulo']) ?></td>
                    <td data-rotulo="Português" class="admin-cell-descricao"><?= $e(mb_strimwidth($linha['textoPt'], 0, 140, '…')) ?></td>
                    <td data-rotulo="Tradução" class="admin-cell-descricao"><?= $t ? $e(mb_strimwidth($t['texto'], 0, 140, '…')) : '—' ?></td>
                    <td data-rotulo="Situação">
                        <?php if ($linha['situacao'] === 'falta'): ?>
                            <span class="admin-badge-destaque">Falta</span>
                        <?php else: ?>
                            <span class="admin-status-badge admin-status-<?= $linha['situacao'] === 'publicado' ? 'ativo' : 'inativo' ?>"><?= $e(ucfirst($linha['situacao'])) ?></span>
                            <?php if ($t['validado_por']): ?><br><small><?= $e($t['validado_por'] . ($t['validado_em'] ? ', ' . dataHoraBr($t['validado_em'], false) : '')) ?></small><?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td data-rotulo="Ações" class="admin-table-acoes-col">
                        <button type="button" class="admin-icon-button" aria-label="Traduzir <?= $e($linha['rotulo']) ?>" data-abrir-modal="modal-traducao" data-titulo="<?= $e($linha['rotulo']) ?>"
                            data-valores="<?= dadosParaModal([
                                'entidade' => $linha['entidade'], 'entidade_id' => $linha['idEntidade'], 'campo' => $linha['campo'],
                                'original' => $linha['textoPt'], 'texto' => (string) ($t['texto'] ?? ''), 'status' => $t['status'] ?? 'rascunho',
                                'validado_por' => (string) ($t['validado_por'] ?? ''), 'audio_url' => (string) ($t['audio_url'] ?? ''), 'pictograma' => (string) ($t['pictograma'] ?? ''),
                            ]) ?>"><?= icone('languages') ?></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p class="admin-empty-state" data-vazio-lista="#corpo-traducoes" hidden><?= icone('search-x') ?> Nada com esse filtro.</p>
</div>

<div class="admin-modal" id="modal-traducao" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel admin-modal-panel-largo" role="dialog" aria-modal="true" aria-labelledby="modal-traducao-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-traducao-titulo" data-titulo-modal>Tradução</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário"><?= icone('x') ?></button>
        </div>
        <form class="admin-form" method="post" action="traducoes.php?idioma=<?= rawurlencode($idioma) ?>" novalidate>
            <?= campoCsrf() ?>
            <input type="hidden" name="idioma" value="<?= $e($idioma) ?>">
            <input type="hidden" name="entidade" value="">
            <input type="hidden" name="entidade_id" value="">
            <input type="hidden" name="campo" value="">
            <div class="admin-field">
                <label for="traducao-original">Original em português</label>
                <textarea id="traducao-original" name="original" rows="2" readonly></textarea>
            </div>
            <div class="admin-field">
                <label for="traducao-texto">Tradução (<?= $e($idiomasTraducao[$idioma]) ?>)</label>
                <textarea id="traducao-texto" name="texto" rows="3" required maxlength="4000"></textarea>
                <span class="admin-field-error">Escreva a tradução.</span>
            </div>
            <div class="admin-field-grid">
                <div class="admin-field">
                    <label for="traducao-status">Situação</label>
                    <select id="traducao-status" name="status">
                        <?php foreach ($statusPossiveis as $valor => $rotulo): ?>
                            <option value="<?= $valor ?>"><?= $e($rotulo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="traducao-revisor">Revisado por (nome)</label>
                    <input type="text" id="traducao-revisor" name="validado_por" maxlength="150" placeholder="Obrigatório pra revisar ou publicar">
                </div>
            </div>
            <div class="admin-field-grid">
                <div class="admin-field">
                    <label for="traducao-audio">Áudio gravado (opcional)</label>
                    <input type="text" id="traducao-audio" name="audio_url" maxlength="255" placeholder="audio/wari/cra-descricao.mp3 ou https://">
                </div>
                <div class="admin-field">
                    <label for="traducao-pictograma">Pictograma (opcional)</label>
                    <input type="text" id="traducao-pictograma" name="pictograma" maxlength="120" placeholder="book-open ou img/pictogramas/arquivo.png">
                </div>
            </div>
            <?php if ($idioma === 'wari'): ?>
                <label class="admin-checkbox"><input type="checkbox" name="confirma_falante" value="1"><span>Confirmo que um falante de Oro Wari revisou este texto (obrigatório pra publicar).</span></label>
            <?php endif; ?>
            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Salvar</span></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
