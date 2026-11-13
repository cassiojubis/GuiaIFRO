<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Categorias</h2>
        <p>Grupos do organograma usados para organizar os setores no site público.</p>
    </div>
    <button type="button" class="button button-primary" data-abrir-modal="modal-categoria" data-titulo="Nova categoria">
        <?= icone('plus') ?>
        <span>Nova categoria</span>
    </button>
</div>

<div class="admin-toolbar">
    <div class="admin-search-field">
        <?= icone('search') ?>
        <input type="search" placeholder="Buscar categoria..." aria-label="Buscar categoria" data-busca-lista="#corpo-tabela-categorias">
    </div>

    <div class="admin-filter-group" role="group" aria-label="Filtrar por status">
        <button type="button" class="admin-filter-chip active" aria-pressed="true" data-filtro="#corpo-tabela-categorias" data-filtro-chave="status" data-filtro-valor="todos">Todas</button>
        <button type="button" class="admin-filter-chip" aria-pressed="false" data-filtro="#corpo-tabela-categorias" data-filtro-chave="status" data-filtro-valor="ativo">Ativas</button>
        <button type="button" class="admin-filter-chip" aria-pressed="false" data-filtro="#corpo-tabela-categorias" data-filtro-chave="status" data-filtro-valor="inativo">Inativas</button>
    </div>
</div>

<p class="admin-result-count" aria-live="polite" data-contador-lista="#corpo-tabela-categorias" data-singular="categoria encontrada" data-plural="categorias encontradas"></p>

<div class="admin-table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Categoria</th>
                <th>Setores</th>
                <th>Status</th>
                <th>Atualizado em</th>
                <th class="admin-table-acoes-col">Ações</th>
            </tr>
        </thead>
        <tbody id="corpo-tabela-categorias">
            <?php foreach ($categoriasAdmin as $indice => $categoria): ?>
                <?php $ativa = (int) $categoria['ativo'] === 1; ?>
                <tr data-linha data-status="<?= $ativa ? 'ativo' : 'inativo' ?>" data-busca="<?= htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8') ?>">
                    <td data-rotulo="Categoria">
                        <div class="admin-cell-categoria">
                            <span class="admin-cell-icone color-solid-<?= htmlspecialchars($categoria['cor'], ENT_QUOTES, 'UTF-8') ?>"><?= icone($categoria['icone']) ?></span>
                            <strong><?= htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    </td>
                    <td data-rotulo="Setores"><?= (int) $categoria['total_setores'] ?></td>
                    <td data-rotulo="Status">
                        <form method="post" class="admin-form-inline">
                            <?= campoCsrf() ?>
                            <input type="hidden" name="acao" value="alternar">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($categoria['id'], ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="admin-status-badge admin-status-<?= $ativa ? 'ativo' : 'inativo' ?>" aria-label="<?= $ativa ? 'Ativa. Clique para desativar' : 'Inativa. Clique para ativar' ?>">
                                <?= icone($ativa ? 'check-circle-2' : 'circle-slash') ?>
                                <span><?= $ativa ? 'Ativa' : 'Inativa' ?></span>
                            </button>
                        </form>
                    </td>
                    <td data-rotulo="Atualizado em"><?= htmlspecialchars(dataHoraBr($categoria['atualizado_em'], false), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-rotulo="Ações" class="admin-table-acoes-col">
                        <div class="admin-row-actions">
                            <?php foreach (['cima' => 'chevron-up', 'baixo' => 'chevron-down'] as $direcao => $iconeDirecao): ?>
                                <form method="post" class="admin-form-inline">
                                    <?= campoCsrf() ?>
                                    <input type="hidden" name="acao" value="mover">
                                    <input type="hidden" name="direcao" value="<?= $direcao ?>">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($categoria['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="admin-icon-button" aria-label="Mover <?= htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8') ?> para <?= $direcao ?>" <?= ($direcao === 'cima' && $indice === 0) || ($direcao === 'baixo' && $indice === count($categoriasAdmin) - 1) ? 'disabled' : '' ?>><?= icone($iconeDirecao) ?></button>
                                </form>
                            <?php endforeach; ?>
                            <button type="button" class="admin-icon-button" aria-label="Editar <?= htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                data-abrir-modal="modal-categoria" data-titulo="Editar categoria"
                                data-valores="<?= dadosParaModal(['id' => $categoria['id'], 'nome' => $categoria['nome'], 'icone' => $categoria['icone'], 'cor' => $categoria['cor'], 'ativo' => $ativa]) ?>">
                                <?= icone('pencil') ?>
                            </button>
                            <form method="post" class="admin-form-inline">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($categoria['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="admin-icon-button admin-icon-button-danger" aria-label="Excluir <?= htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-confirmar-exclusao="Excluir a categoria &quot;<?= htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8') ?>&quot;? Os <?= (int) $categoria['total_setores'] ?> setores dela continuam existindo, mas ficam sem categoria.">
                                    <?= icone('trash-2') ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="admin-empty-state" data-vazio-lista="#corpo-tabela-categorias" hidden>
        <?= icone('search-x') ?>
        Nenhuma categoria encontrada para os filtros aplicados.
    </p>
</div>

<!-- DRAWER: formulário de criação/edição de categoria -->
<div class="admin-modal" id="modal-categoria" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-categoria-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-categoria-titulo" data-titulo-modal>Nova categoria</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário">
                <?= icone('x') ?>
            </button>
        </div>

        <form class="admin-form" method="post" novalidate>
            <?= campoCsrf() ?>
            <input type="hidden" name="acao" value="salvar">
            <input type="hidden" name="id" value="" data-limpar>

            <div class="admin-field">
                <label for="categoria-nome">Nome da categoria</label>
                <input type="text" id="categoria-nome" name="nome" required maxlength="150" placeholder="Ex.: Diretoria de Ensino (DE)">
                <span class="admin-field-error">Informe o nome da categoria.</span>
            </div>

            <div class="admin-field-grid">
                <div class="admin-field">
                    <label for="categoria-icone">Ícone (<a href="https://lucide.dev/icons/" target="_blank" rel="noopener noreferrer">Lucide</a>)</label>
                    <div class="admin-input-with-icon">
                        <span id="categoria-icone-previa"><?= icone('shapes') ?></span>
                        <input type="text" id="categoria-icone" name="icone" required maxlength="40" placeholder="Ex.: book-open" value="shapes" data-previa-icone="#categoria-icone-previa">
                    </div>
                    <span class="admin-field-error">Informe o ícone.</span>
                </div>

                <div class="admin-field">
                    <label for="categoria-cor">Cor</label>
                    <select id="categoria-cor" name="cor" required>
                        <?php foreach ($coresDisponiveisAdmin as $valor => $rotulo): ?>
                            <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="admin-field">
                <span class="admin-field-legenda">Status</span>
                <label class="admin-toggle">
                    <input type="checkbox" name="ativo" value="1" checked>
                    <span class="admin-toggle-track" aria-hidden="true"></span>
                    <span class="admin-toggle-texto" data-texto-toggle data-ligado="Ativa" data-desligado="Inativa">Ativa</span>
                </label>
            </div>

            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="submit" class="button button-primary admin-botao-salvar">
                    <span class="admin-botao-texto">Salvar categoria</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
