<?php
/**
 * GERENCIAMENTO DE SETORES
 *
 * INTEGRAÇÃO FUTURA:
 * Substituir $setoresAdmin por dados vindos do banco. As listas de serviços
 * e documentos são adicionadas dinamicamente pelo JavaScript (botões "+
 * Adicionar serviço" / "+ Adicionar documento"); ao integrar o backend, cada
 * envio do formulário deve gravar essas listas normalizadas no banco de dados.
 */
$tituloPaginaAdmin = 'Setores';
$paginaAtualAdmin = 'setores';
require __DIR__ . '/includes/admin-cabecalho.php';

$categoriasParaSetorAdmin = ['ensino' => 'Diretoria de Ensino', 'assistencia' => 'Assistência Estudantil', 'administracao' => 'Administração', 'pesquisa' => 'Pesquisa e Inovação', 'extensao' => 'Extensão', 'biblioteca' => 'Biblioteca', 'cra' => 'Registro Acadêmico', 'ti' => 'Tecnologia da Informação'];

$setoresAdmin = [
    [
        'id' => 1, 'nome' => 'Acervo Geral', 'sigla' => 'BIB', 'categoria' => 'biblioteca',
        'descricao' => 'Empréstimo e consulta do acervo bibliográfico do campus.',
        'bloco' => 'Biblioteca', 'sala' => 'Térreo', 'telefone' => '(69) 3541-5288', 'email' => 'biblioteca@ifro.edu.br',
        'horario' => '7h às 21h', 'status' => 'ativo',
        'servicos' => ['Empréstimo de livros', 'Renovação online', 'Consulta a periódicos'],
        'documentos' => ['Carteirinha de estudante', 'Documento com foto'],
    ],
    [
        'id' => 2, 'nome' => 'CRA', 'sigla' => 'CRA', 'categoria' => 'cra',
        'descricao' => 'Registro e controle acadêmico, emissão de documentos.',
        'bloco' => 'Bloco Pedagógico', 'sala' => 'Térreo', 'telefone' => '(69) 3541-5287', 'email' => 'cra@ifro.edu.br',
        'horario' => '7h às 17h', 'status' => 'ativo',
        'servicos' => ['Declaração de matrícula', 'Histórico escolar', 'Trancamento de matrícula'],
        'documentos' => ['RG', 'CPF'],
    ],
    [
        'id' => 3, 'nome' => 'Assistência Social', 'sigla' => 'PNAES', 'categoria' => 'assistencia',
        'descricao' => 'Atendimento e concessão de auxílios estudantis.',
        'bloco' => 'Bloco Administrativo', 'sala' => '1º andar', 'telefone' => '(69) 3541-5284', 'email' => 'assistencia@ifro.edu.br',
        'horario' => '7h às 13h', 'status' => 'ativo',
        'servicos' => ['Auxílio permanência', 'Atendimento psicossocial'],
        'documentos' => ['Comprovante de renda', 'Comprovante de residência'],
    ],
    [
        'id' => 4, 'nome' => 'Helpdesk', 'sigla' => 'TI', 'categoria' => 'ti',
        'descricao' => 'Suporte técnico para sistemas e equipamentos do campus.',
        'bloco' => 'Bloco Administrativo', 'sala' => '1º andar', 'telefone' => '(69) 3541-5289', 'email' => 'ti@ifro.edu.br',
        'horario' => '7h às 17h', 'status' => 'inativo',
        'servicos' => ['Suporte a rede', 'Suporte a SUAP'],
        'documentos' => [],
    ],
];
?>

<div class="admin-page-header">
    <div>
        <h2>Setores</h2>
        <p>Cadastre e organize os setores exibidos no mapa e nas categorias do site.</p>
    </div>
    <button type="button" class="button button-primary admin-abrir-modal" data-modal-alvo="modal-setor" data-modo="criar">
        <?= icone('plus') ?>
        <span>Novo setor</span>
    </button>
</div>

<div class="admin-toolbar">
    <div class="admin-search-field">
        <?= icone('search') ?>
        <input type="search" id="busca-setores" placeholder="Buscar setor..." aria-label="Buscar setor">
    </div>

    <select id="filtro-categoria-setores" class="admin-select-filtro" aria-label="Filtrar por categoria">
        <option value="todos">Todas as categorias</option>
        <?php foreach ($categoriasParaSetorAdmin as $valor => $rotulo): ?>
            <option value="<?= htmlspecialchars($valor) ?>"><?= htmlspecialchars($rotulo) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<p class="admin-result-count" id="contador-setores" aria-live="polite"></p>

<div class="admin-table-wrapper" id="tabela-setores">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Setor</th>
                <th>Categoria</th>
                <th>Bloco / Sala</th>
                <th>Contato</th>
                <th>Status</th>
                <th class="admin-table-acoes-col">Ações</th>
            </tr>
        </thead>
        <tbody id="corpo-tabela-setores">
            <?php foreach ($setoresAdmin as $setor): ?>
                <tr
                    data-linha-setor
                    data-id="<?= (int) $setor['id'] ?>"
                    data-categoria="<?= htmlspecialchars($setor['categoria']) ?>"
                    data-status="<?= htmlspecialchars($setor['status']) ?>"
                    data-nome="<?= htmlspecialchars($setor['nome']) ?>"
                    data-sigla="<?= htmlspecialchars($setor['sigla']) ?>"
                    data-descricao="<?= htmlspecialchars($setor['descricao']) ?>"
                    data-bloco="<?= htmlspecialchars($setor['bloco']) ?>"
                    data-sala="<?= htmlspecialchars($setor['sala']) ?>"
                    data-telefone="<?= htmlspecialchars($setor['telefone']) ?>"
                    data-email="<?= htmlspecialchars($setor['email']) ?>"
                    data-horario="<?= htmlspecialchars($setor['horario']) ?>"
                    data-servicos="<?= htmlspecialchars(implode('|', $setor['servicos'])) ?>"
                    data-documentos="<?= htmlspecialchars(implode('|', $setor['documentos'])) ?>"
                >
                    <td data-rotulo="Setor">
                        <div class="admin-cell-categoria">
                            <strong><?= htmlspecialchars($setor['nome']) ?></strong>
                            <span class="admin-cell-subtexto"><?= htmlspecialchars($setor['sigla']) ?></span>
                        </div>
                    </td>
                    <td data-rotulo="Categoria"><?= htmlspecialchars($categoriasParaSetorAdmin[$setor['categoria']] ?? $setor['categoria']) ?></td>
                    <td data-rotulo="Bloco / Sala"><?= htmlspecialchars($setor['bloco']) ?> · <?= htmlspecialchars($setor['sala']) ?></td>
                    <td data-rotulo="Contato" class="admin-cell-descricao"><?= htmlspecialchars($setor['telefone']) ?></td>
                    <td data-rotulo="Status">
                        <button type="button" class="admin-status-badge admin-status-<?= htmlspecialchars($setor['status']) ?>" data-alternar-status aria-pressed="<?= $setor['status'] === 'ativo' ? 'true' : 'false' ?>">
                            <?= icone($setor['status'] === 'ativo' ? 'check-circle-2' : 'circle-slash') ?>
                            <span data-texto-status><?= $setor['status'] === 'ativo' ? 'Ativo' : 'Inativo' ?></span>
                        </button>
                    </td>
                    <td data-rotulo="Ações" class="admin-table-acoes-col">
                        <div class="admin-row-actions">
                            <button type="button" class="admin-icon-button admin-abrir-detalhes-setor" aria-label="Visualizar detalhes do setor">
                                <?= icone('eye') ?>
                            </button>
                            <button type="button" class="admin-icon-button admin-abrir-modal" data-modal-alvo="modal-setor" data-modo="editar" aria-label="Editar setor">
                                <?= icone('pencil') ?>
                            </button>
                            <button type="button" class="admin-icon-button admin-icon-button-danger admin-abrir-exclusao" data-tipo-exclusao="setor" aria-label="Excluir setor">
                                <?= icone('trash-2') ?>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="admin-empty-state" id="estado-vazio-setores" hidden>
        <?= icone('search-x') ?>
        Nenhum setor encontrado para os filtros aplicados.
    </p>
</div>

<!-- DRAWER: visualização somente leitura de um setor -->
<div class="admin-modal" id="modal-detalhes-setor" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-detalhes-setor-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-detalhes-setor-titulo">Detalhes do setor</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar detalhes">
                <?= icone('x') ?>
            </button>
        </div>
        <dl class="admin-detalhes-lista" id="conteudo-detalhes-setor"></dl>
    </div>
</div>

<!-- DRAWER: formulário de criação/edição de setor -->
<div class="admin-modal" id="modal-setor" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel admin-modal-panel-largo" role="dialog" aria-modal="true" aria-labelledby="modal-setor-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-setor-titulo">Novo setor</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário">
                <?= icone('x') ?>
            </button>
        </div>

        <form class="admin-form" id="form-setor" novalidate>
            <div class="admin-field-grid">
                <div class="admin-field">
                    <label for="setor-nome">Nome do setor</label>
                    <input type="text" id="setor-nome" name="nome" required placeholder="Ex.: Acervo Geral">
                    <span class="admin-field-error">Informe o nome do setor.</span>
                </div>
                <div class="admin-field">
                    <label for="setor-sigla">Sigla</label>
                    <input type="text" id="setor-sigla" name="sigla" required placeholder="Ex.: BIB">
                    <span class="admin-field-error">Informe a sigla.</span>
                </div>
            </div>

            <div class="admin-field">
                <label for="setor-categoria">Categoria</label>
                <select id="setor-categoria" name="categoria" required>
                    <?php foreach ($categoriasParaSetorAdmin as $valor => $rotulo): ?>
                        <option value="<?= htmlspecialchars($valor) ?>"><?= htmlspecialchars($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-field">
                <label for="setor-descricao">Descrição</label>
                <textarea id="setor-descricao" name="descricao" rows="2" required placeholder="Descreva o que este setor faz"></textarea>
                <span class="admin-field-error">Informe uma descrição.</span>
            </div>

            <div class="admin-field-grid admin-field-grid-3">
                <div class="admin-field">
                    <label for="setor-bloco">Bloco / localização</label>
                    <input type="text" id="setor-bloco" name="bloco" required placeholder="Ex.: Bloco Pedagógico">
                </div>
                <div class="admin-field">
                    <label for="setor-sala">Sala / andar</label>
                    <input type="text" id="setor-sala" name="sala" required placeholder="Ex.: Térreo">
                </div>
                <div class="admin-field">
                    <label for="setor-horario">Horário de atendimento</label>
                    <input type="text" id="setor-horario" name="horario" required placeholder="Ex.: 7h às 17h">
                </div>
            </div>

            <div class="admin-field-grid">
                <div class="admin-field">
                    <label for="setor-telefone">Telefone</label>
                    <input type="tel" id="setor-telefone" name="telefone" required placeholder="(69) 3541-0000">
                </div>
                <div class="admin-field">
                    <label for="setor-email">E-mail</label>
                    <input type="email" id="setor-email" name="email" required placeholder="setor@ifro.edu.br">
                </div>
            </div>

            <!-- Lista dinâmica: serviços oferecidos -->
            <div class="admin-field">
                <span class="admin-field-legenda">Serviços oferecidos</span>
                <ul class="admin-lista-dinamica" id="lista-servicos-setor" data-lista-dinamica></ul>
                <button type="button" class="admin-adicionar-item" data-adicionar-item="lista-servicos-setor" data-placeholder="Nome do serviço">
                    <?= icone('plus') ?>
                    <span>Adicionar serviço</span>
                </button>
            </div>

            <!-- Lista dinâmica: documentos necessários -->
            <div class="admin-field">
                <span class="admin-field-legenda">Documentos necessários</span>
                <ul class="admin-lista-dinamica" id="lista-documentos-setor" data-lista-dinamica></ul>
                <button type="button" class="admin-adicionar-item" data-adicionar-item="lista-documentos-setor" data-placeholder="Nome do documento">
                    <?= icone('plus') ?>
                    <span>Adicionar documento</span>
                </button>
            </div>

            <div class="admin-field">
                <span class="admin-field-legenda">Status</span>
                <label class="admin-toggle">
                    <input type="checkbox" id="setor-status" name="status" checked>
                    <span class="admin-toggle-track" aria-hidden="true"></span>
                    <span class="admin-toggle-texto" data-texto-toggle-status>Ativo</span>
                </label>
            </div>

            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="submit" class="button button-primary admin-botao-salvar">
                    <span class="admin-botao-texto">Salvar setor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/admin-rodape.php'; ?>
