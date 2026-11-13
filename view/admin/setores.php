<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>
<?php $adminGeral = ehAdminGeral(); ?>

<div class="admin-page-header">
    <div>
        <h2><?= $adminGeral ? 'Setores' : 'Meus setores' ?></h2>
        <p><?= $adminGeral
            ? 'Todo setor ativo tem página no site e pode ser ligado a uma sala do mapa.'
            : 'Mantenha os dados do seu setor em dia: contato, horário, serviços e equipe.' ?></p>
    </div>
    <?php if ($adminGeral): ?>
        <button type="button" class="button button-primary" data-abrir-modal="modal-setor" data-titulo="Novo setor">
            <?= icone('plus') ?>
            <span>Novo setor</span>
        </button>
    <?php endif; ?>
</div>

<?php if (!$setoresAdmin): ?>
    <p class="admin-aviso-info"><?= icone('info') ?> <span>Nenhum setor ligado ao seu usuário ainda. Peça ao administrador para ligar você ao seu setor.</span></p>
<?php endif; ?>

<div class="admin-toolbar">
    <div class="admin-search-field">
        <?= icone('search') ?>
        <input type="search" placeholder="Buscar setor..." aria-label="Buscar setor" data-busca-lista="#corpo-tabela-setores">
    </div>

    <?php if ($adminGeral): ?>
        <select class="admin-select-filtro" aria-label="Filtrar por categoria" data-filtro="#corpo-tabela-setores" data-filtro-chave="categoria">
            <option value="todos">Todas as categorias</option>
            <?php foreach ($categoriasParaSetorAdmin as $valor => $rotulo): ?>
                <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <select class="admin-select-filtro" aria-label="Filtrar por preenchimento" data-filtro="#corpo-tabela-setores" data-filtro-chave="dados">
            <option value="todos">Com e sem dados</option>
            <option value="sem">Sem informações (preencher)</option>
            <option value="com">Com informações</option>
        </select>
        <select class="admin-select-filtro" aria-label="Filtrar por leitura fácil" data-filtro="#corpo-tabela-setores" data-filtro-chave="facil">
            <option value="todos">Com e sem leitura fácil</option>
            <option value="nao">Sem texto em leitura fácil</option>
            <option value="sim">Com texto em leitura fácil</option>
        </select>
    <?php endif; ?>
</div>

<p class="admin-result-count" aria-live="polite" data-contador-lista="#corpo-tabela-setores" data-singular="setor encontrado" data-plural="setores encontrados"></p>

<div class="admin-table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Setor</th>
                <th>Categoria</th>
                <th>Contato</th>
                <th>Atualizado</th>
                <th>Status</th>
                <th class="admin-table-acoes-col">Ações</th>
            </tr>
        </thead>
        <tbody id="corpo-tabela-setores">
            <?php foreach ($setoresAdmin as $setor): ?>
                <?php
                $ativo = (int) $setor['ativo'] === 1;
                $temDados = $setor['telefone'] || $setor['email'] || $setor['horario'] || $setor['localizacao'];
                $valoresSetor = [
                    'id' => $setor['id'], 'nome' => $setor['nome'], 'sigla' => $setor['sigla'],
                    'categoria_id' => (string) $setor['categoria_id'], 'icone' => $setor['icone'], 'cor' => $setor['cor'],
                    'descricao' => (string) $setor['descricao'], 'descricao_curta' => (string) $setor['descricao_curta'],
                    'texto_facil' => (string) $setor['texto_facil'],
                    'localizacao' => (string) $setor['localizacao'], 'bloco' => (string) $setor['bloco'], 'sala_texto' => (string) $setor['sala'],
                    'telefone' => (string) $setor['telefone'], 'email' => (string) $setor['email'],
                    'responsavel' => (string) $setor['responsavel'], 'horario_texto' => (string) $setor['horario'],
                    'equipe' => $setor['equipe'], 'servicos' => $setor['servicos'], 'documentos' => $setor['documentos'], 'sinonimos' => $setor['sinonimos'],
                    'ativo' => $ativo,
                ];
                ?>
                <tr data-linha data-categoria="<?= htmlspecialchars((string) $setor['categoria_id'], ENT_QUOTES, 'UTF-8') ?>" data-dados="<?= $temDados ? 'com' : 'sem' ?>" data-facil="<?= !empty($setor['texto_facil']) ? 'sim' : 'nao' ?>"
                    data-busca="<?= htmlspecialchars($setor['nome'] . ' ' . $setor['sigla'], ENT_QUOTES, 'UTF-8') ?>">
                    <td data-rotulo="Setor">
                        <div class="admin-cell-categoria">
                            <span class="admin-cell-icone color-solid-<?= htmlspecialchars($setor['cor'], ENT_QUOTES, 'UTF-8') ?>"><?= icone($setor['icone']) ?></span>
                            <span>
                                <strong><?= htmlspecialchars($setor['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="admin-cell-subtexto"><?= htmlspecialchars($setor['sigla'] ?: '—', ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                        </div>
                    </td>
                    <td data-rotulo="Categoria"><?= htmlspecialchars($setor['categoria_nome'] ?? 'Sem categoria', ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-rotulo="Contato" class="admin-cell-descricao">
                        <?php if ($temDados): ?>
                            <?= htmlspecialchars($setor['telefone'] ?: ($setor['email'] ?: 'Só horário/local'), ENT_QUOTES, 'UTF-8') ?>
                        <?php else: ?>
                            <span class="admin-badge-destaque">Sem informações</span>
                        <?php endif; ?>
                    </td>
                    <td data-rotulo="Atualizado">
                        <?= htmlspecialchars(dataHoraBr($setor['atualizado_em'], false), ENT_QUOTES, 'UTF-8') ?>
                        <span class="admin-cell-subtexto"><?= htmlspecialchars((string) $setor['atualizado_por'], ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td data-rotulo="Status">
                        <?php if ($adminGeral): ?>
                            <form method="post" class="admin-form-inline">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="acao" value="alternar">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($setor['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="admin-status-badge admin-status-<?= $ativo ? 'ativo' : 'inativo' ?>" aria-label="<?= $ativo ? 'Ativo. Clique para desativar' : 'Inativo. Clique para ativar' ?>">
                                    <?= icone($ativo ? 'check-circle-2' : 'circle-slash') ?>
                                    <span><?= $ativo ? 'Ativo' : 'Inativo' ?></span>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="admin-status-badge admin-status-<?= $ativo ? 'ativo' : 'inativo' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span>
                        <?php endif; ?>
                    </td>
                    <td data-rotulo="Ações" class="admin-table-acoes-col">
                        <div class="admin-row-actions">
                            <a class="admin-icon-button" href="../setor.php?id=<?= rawurlencode($setor['id']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Ver a página de <?= htmlspecialchars($setor['nome'], ENT_QUOTES, 'UTF-8') ?> no site"><?= icone('eye') ?></a>
                            <a class="admin-icon-button" href="setor-horarios.php?id=<?= rawurlencode($setor['id']) ?>" aria-label="Horários de <?= htmlspecialchars($setor['nome'], ENT_QUOTES, 'UTF-8') ?>"><?= icone('clock') ?></a>
                            <button type="button" class="admin-icon-button" aria-label="Editar <?= htmlspecialchars($setor['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                data-abrir-modal="modal-setor" data-titulo="Editar setor" data-valores="<?= dadosParaModal($valoresSetor) ?>">
                                <?= icone('pencil') ?>
                            </button>
                            <?php if ($adminGeral): ?>
                                <form method="post" class="admin-form-inline">
                                    <?= campoCsrf() ?>
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($setor['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="admin-icon-button admin-icon-button-danger" aria-label="Excluir <?= htmlspecialchars($setor['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-confirmar-exclusao="Excluir o setor &quot;<?= htmlspecialchars($setor['nome'], ENT_QUOTES, 'UTF-8') ?>&quot;? A página dele sai do site e equipe, serviços e horários são apagados. Se for só temporário, prefira desativar.">
                                        <?= icone('trash-2') ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="admin-empty-state" data-vazio-lista="#corpo-tabela-setores" hidden>
        <?= icone('search-x') ?>
        Nenhum setor encontrado para os filtros aplicados.
    </p>
</div>

<!-- DRAWER: formulário de criação/edição de setor -->
<div class="admin-modal" id="modal-setor" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel admin-modal-panel-largo" role="dialog" aria-modal="true" aria-labelledby="modal-setor-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-setor-titulo" data-titulo-modal>Novo setor</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário">
                <?= icone('x') ?>
            </button>
        </div>

        <form class="admin-form" method="post" novalidate>
            <?= campoCsrf() ?>
            <input type="hidden" name="acao" value="salvar">
            <input type="hidden" name="id" value="" data-limpar>

            <?php if ($adminGeral): ?>
                <div class="admin-field-grid">
                    <div class="admin-field">
                        <label for="setor-nome">Nome do setor</label>
                        <input type="text" id="setor-nome" name="nome" required maxlength="200" placeholder="Ex.: Coordenação de Registros Acadêmicos">
                        <span class="admin-field-error">Informe o nome do setor.</span>
                    </div>
                    <div class="admin-field">
                        <label for="setor-sigla">Sigla (opcional)</label>
                        <input type="text" id="setor-sigla" name="sigla" maxlength="30" placeholder="Ex.: CRA">
                    </div>
                </div>

                <div class="admin-field-grid admin-field-grid-3">
                    <div class="admin-field">
                        <label for="setor-categoria">Categoria</label>
                        <select id="setor-categoria" name="categoria_id">
                            <option value="">Sem categoria</option>
                            <?php foreach ($categoriasParaSetorAdmin as $valor => $rotulo): ?>
                                <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label for="setor-icone">Ícone (Lucide)</label>
                        <div class="admin-input-with-icon">
                            <span id="setor-icone-previa"><?= icone('building-2') ?></span>
                            <input type="text" id="setor-icone" name="icone" required maxlength="40" value="building-2" data-previa-icone="#setor-icone-previa">
                        </div>
                    </div>
                    <div class="admin-field">
                        <label for="setor-cor">Cor</label>
                        <select id="setor-cor" name="cor">
                            <?php foreach ($coresDisponiveisAdmin as $valor => $rotulo): ?>
                                <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>" <?= $valor === 'green' ? 'selected' : '' ?>><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="setor-descricao-curta">Descrição curta (aparece no card da lista)</label>
                    <input type="text" id="setor-descricao-curta" name="descricao_curta" maxlength="255" placeholder="Ex.: Cuida dos registros acadêmicos dos estudantes.">
                </div>
            <?php endif; ?>

            <div class="admin-field">
                <label for="setor-descricao">Descrição (página do setor)</label>
                <textarea id="setor-descricao" name="descricao" rows="3" maxlength="2000" placeholder="O que este setor faz e quando procurar"></textarea>
            </div>

            <div class="admin-field">
                <label for="setor-localizacao">Como encontrar (texto)</label>
                <input type="text" id="setor-localizacao" name="localizacao" maxlength="255" placeholder="Ex.: Térreo do prédio principal, primeira sala à direita">
                <p class="admin-campo-ajuda">A sala no desenho do mapa é escolhida em "Editor do mapa".</p>
            </div>

            <?php if ($adminGeral): ?>
                <div class="admin-field-grid">
                    <div class="admin-field">
                        <label for="setor-bloco">Bloco / andar</label>
                        <input type="text" id="setor-bloco" name="bloco" maxlength="150" placeholder="Ex.: Térreo do prédio principal">
                    </div>
                    <div class="admin-field">
                        <label for="setor-sala">Sala</label>
                        <input type="text" id="setor-sala" name="sala_texto" maxlength="150" placeholder="Ex.: Sala 3">
                    </div>
                </div>
            <?php endif; ?>

            <div class="admin-field-grid">
                <div class="admin-field">
                    <label for="setor-telefone">Telefone</label>
                    <input type="tel" id="setor-telefone" name="telefone" maxlength="60" placeholder="(69) 0000-0000 ou Não possui">
                </div>
                <div class="admin-field">
                    <label for="setor-email">E-mail</label>
                    <input type="email" id="setor-email" name="email" maxlength="150" placeholder="setor.guajara@ifro.edu.br">
                    <span class="admin-field-error">E-mail inválido.</span>
                </div>
            </div>

            <div class="admin-field-grid">
                <div class="admin-field">
                    <label for="setor-responsavel">Responsável</label>
                    <input type="text" id="setor-responsavel" name="responsavel" maxlength="150" placeholder="Nome do responsável">
                </div>
                <div class="admin-field">
                    <label for="setor-horario">Observação sobre o horário</label>
                    <input type="text" id="setor-horario" name="horario_texto" maxlength="255" placeholder="Ex.: Atendimento só com agendamento">
                    <p class="admin-campo-ajuda">Os dias e horas de atendimento ficam no botão <?= icone('clock') ?> Horários.</p>
                </div>
            </div>

            <div class="admin-field">
                <span class="admin-field-legenda">Equipe</span>
                <ul class="admin-lista-dinamica" data-lista-dinamica="equipe" data-placeholder="Nome da pessoa" data-maximo="150"></ul>
                <button type="button" class="admin-adicionar-item" data-adicionar-item="equipe"><?= icone('plus') ?><span>Adicionar pessoa</span></button>
            </div>

            <div class="admin-field">
                <span class="admin-field-legenda">Serviços oferecidos</span>
                <ul class="admin-lista-dinamica" data-lista-dinamica="servicos" data-placeholder="Nome do serviço"></ul>
                <button type="button" class="admin-adicionar-item" data-adicionar-item="servicos"><?= icone('plus') ?><span>Adicionar serviço</span></button>
            </div>

            <div class="admin-field">
                <span class="admin-field-legenda">Documentos úteis</span>
                <ul class="admin-lista-dinamica" data-lista-dinamica="documentos" data-placeholder="Nome do documento"></ul>
                <button type="button" class="admin-adicionar-item" data-adicionar-item="documentos"><?= icone('plus') ?><span>Adicionar documento</span></button>
            </div>

            <div class="admin-field">
                <span class="admin-field-legenda">Palavras de busca (sinônimos)</span>
                <p class="admin-campo-ajuda">Palavras que as pessoas digitam procurando este setor e que não estão no nome nem nos serviços. Ex.: declaração, histórico.</p>
                <ul class="admin-lista-dinamica" data-lista-dinamica="sinonimos" data-placeholder="Palavra ou expressão" data-maximo="100"></ul>
                <button type="button" class="admin-adicionar-item" data-adicionar-item="sinonimos"><?= icone('plus') ?><span>Adicionar palavra</span></button>
            </div>

            <?php if ($adminGeral): ?>
                <div class="admin-field">
                    <label for="setor-texto-facil">Leitura fácil (frases curtas, opcional)</label>
                    <textarea id="setor-texto-facil" name="texto_facil" rows="3" maxlength="2000" placeholder="Ex.: Aqui você pede declaração. Leve seu documento."></textarea>
                </div>

                <div class="admin-field">
                    <span class="admin-field-legenda">Status</span>
                    <label class="admin-toggle">
                        <input type="checkbox" name="ativo" value="1" checked>
                        <span class="admin-toggle-track" aria-hidden="true"></span>
                        <span class="admin-toggle-texto" data-texto-toggle data-ligado="Ativo (aparece no site)" data-desligado="Inativo (escondido do site)">Ativo (aparece no site)</span>
                    </label>
                </div>
            <?php endif; ?>

            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="submit" class="button button-primary admin-botao-salvar">
                    <span class="admin-botao-texto">Salvar setor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
