<?php
/**
 * GERENCIAMENTO DE CATEGORIAS
 *
 * INTEGRAÇÃO FUTURA:
 * Substituir o array $categoriasAdmin abaixo por uma consulta ao banco de
 * dados. As ações de adicionar, editar, excluir e ativar/desativar são hoje
 * manipuladas inteiramente pelo JavaScript (assets/js/admin.js), direto no
 * HTML da tabela/cards. Quando o backend existir, cada ação deve enviar uma
 * requisição real (ex.: fetch para uma API) em vez de apenas alterar a tela.
 */
$tituloPaginaAdmin = 'Categorias';
$paginaAtualAdmin = 'categorias';
require __DIR__ . '/includes/admin-cabecalho.php';

$coresDisponiveisAdmin = ['slate' => 'Cinza-azulado', 'green' => 'Verde', 'blue' => 'Azul', 'amber' => 'Âmbar', 'rose' => 'Rosa', 'purple' => 'Roxo', 'teal' => 'Verde-azulado'];

$categoriasAdmin = [
    ['id' => 1, 'nome' => 'Diretoria de Ensino', 'descricao' => 'Coordenação pedagógica, supervisão de cursos e processos de ensino-aprendizagem.', 'icone' => 'graduation-cap', 'cor' => 'green', 'setores' => 5, 'status' => 'ativo', 'atualizadoEm' => '24/05/2026'],
    ['id' => 2, 'nome' => 'Assistência Estudantil', 'descricao' => 'Programas de apoio ao estudante, auxílios, bolsas e atendimento psicossocial.', 'icone' => 'heart', 'cor' => 'rose', 'setores' => 4, 'status' => 'ativo', 'atualizadoEm' => '22/05/2026'],
    ['id' => 3, 'nome' => 'Administração', 'descricao' => 'Setor administrativo, compras, patrimônio e recursos humanos.', 'icone' => 'briefcase-business', 'cor' => 'slate', 'setores' => 5, 'status' => 'ativo', 'atualizadoEm' => '20/05/2026'],
    ['id' => 4, 'nome' => 'Pesquisa e Inovação', 'descricao' => 'Projetos de pesquisa, iniciação científica e inovação tecnológica.', 'icone' => 'file-text', 'cor' => 'blue', 'setores' => 4, 'status' => 'ativo', 'atualizadoEm' => '18/05/2026'],
    ['id' => 5, 'nome' => 'Extensão', 'descricao' => 'Programas de extensão, projetos comunitários e parcerias com a sociedade.', 'icone' => 'heart', 'cor' => 'rose', 'setores' => 6, 'status' => 'inativo', 'atualizadoEm' => '02/05/2026'],
    ['id' => 6, 'nome' => 'Biblioteca', 'descricao' => 'Acervo bibliográfico, empréstimos, renovações e serviços de pesquisa.', 'icone' => 'book-open', 'cor' => 'amber', 'setores' => 2, 'status' => 'ativo', 'atualizadoEm' => '25/05/2026'],
    ['id' => 7, 'nome' => 'Registro Acadêmico', 'descricao' => 'CRA, matrículas, documentação acadêmica e históricos escolares.', 'icone' => 'users', 'cor' => 'purple', 'setores' => 3, 'status' => 'ativo', 'atualizadoEm' => '19/05/2026'],
    ['id' => 8, 'nome' => 'Tecnologia da Informação', 'descricao' => 'Suporte técnico, infraestrutura de TI e sistemas institucionais.', 'icone' => 'laptop', 'cor' => 'teal', 'setores' => 3, 'status' => 'ativo', 'atualizadoEm' => '15/05/2026'],
];
?>

<div class="admin-page-header">
    <div>
        <h2>Categorias</h2>
        <p>Organize os grupos de setores exibidos no site público.</p>
    </div>
    <button type="button" class="button button-primary admin-abrir-modal" data-modal-alvo="modal-categoria" data-modo="criar">
        <?= icone('plus') ?>
        <span>Nova categoria</span>
    </button>
</div>

<div class="admin-toolbar">
    <div class="admin-search-field">
        <?= icone('search') ?>
        <input type="search" id="busca-categorias" placeholder="Buscar categoria..." aria-label="Buscar categoria">
    </div>

    <div class="admin-filter-group" role="group" aria-label="Filtrar por status">
        <button type="button" class="admin-filter-chip active" data-filtro-status="todos">Todos</button>
        <button type="button" class="admin-filter-chip" data-filtro-status="ativo">Ativas</button>
        <button type="button" class="admin-filter-chip" data-filtro-status="inativo">Inativas</button>
    </div>
</div>

<p class="admin-result-count" id="contador-categorias" aria-live="polite"></p>

<!-- TABELA: visível no desktop -->
<div class="admin-table-wrapper" id="tabela-categorias">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Categoria</th>
                <th>Descrição</th>
                <th>Setores</th>
                <th>Status</th>
                <th>Atualizado em</th>
                <th class="admin-table-acoes-col">Ações</th>
            </tr>
        </thead>
        <tbody id="corpo-tabela-categorias">
            <?php foreach ($categoriasAdmin as $categoria): ?>
                <tr
                    data-linha-categoria
                    data-id="<?= (int) $categoria['id'] ?>"
                    data-nome="<?= htmlspecialchars($categoria['nome']) ?>"
                    data-descricao="<?= htmlspecialchars($categoria['descricao']) ?>"
                    data-icone="<?= htmlspecialchars($categoria['icone']) ?>"
                    data-cor="<?= htmlspecialchars($categoria['cor']) ?>"
                    data-setores="<?= (int) $categoria['setores'] ?>"
                    data-status="<?= htmlspecialchars($categoria['status']) ?>"
                >
                    <td data-rotulo="Categoria">
                        <div class="admin-cell-categoria">
                            <span class="admin-cell-icone color-solid-<?= htmlspecialchars($categoria['cor']) ?>"><?= icone($categoria['icone']) ?></span>
                            <strong><?= htmlspecialchars($categoria['nome']) ?></strong>
                        </div>
                    </td>
                    <td data-rotulo="Descrição" class="admin-cell-descricao"><?= htmlspecialchars($categoria['descricao']) ?></td>
                    <td data-rotulo="Setores"><?= (int) $categoria['setores'] ?></td>
                    <td data-rotulo="Status">
                        <button type="button" class="admin-status-badge admin-status-<?= htmlspecialchars($categoria['status']) ?>" data-alternar-status aria-pressed="<?= $categoria['status'] === 'ativo' ? 'true' : 'false' ?>">
                            <?= icone($categoria['status'] === 'ativo' ? 'check-circle-2' : 'circle-slash') ?>
                            <span data-texto-status><?= $categoria['status'] === 'ativo' ? 'Ativa' : 'Inativa' ?></span>
                        </button>
                    </td>
                    <td data-rotulo="Atualizado em"><?= htmlspecialchars($categoria['atualizadoEm']) ?></td>
                    <td data-rotulo="Ações" class="admin-table-acoes-col">
                        <div class="admin-row-actions">
                            <button type="button" class="admin-icon-button admin-abrir-modal" data-modal-alvo="modal-categoria" data-modo="editar" aria-label="Editar categoria">
                                <?= icone('pencil') ?>
                            </button>
                            <button type="button" class="admin-icon-button admin-icon-button-danger admin-abrir-exclusao" data-tipo-exclusao="categoria" aria-label="Excluir categoria">
                                <?= icone('trash-2') ?>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="admin-empty-state" id="estado-vazio-categorias" hidden>
        <?= icone('search-x') ?>
        Nenhuma categoria encontrada para os filtros aplicados.
    </p>
</div>

<!-- DRAWER: formulário de criação/edição de categoria -->
<div class="admin-modal" id="modal-categoria" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-categoria-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-categoria-titulo">Nova categoria</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário">
                <?= icone('x') ?>
            </button>
        </div>

        <form class="admin-form" id="form-categoria" novalidate>
            <div class="admin-field">
                <label for="categoria-nome">Nome da categoria</label>
                <input type="text" id="categoria-nome" name="nome" required placeholder="Ex.: Biblioteca">
                <span class="admin-field-error">Informe o nome da categoria.</span>
            </div>

            <div class="admin-field">
                <label for="categoria-descricao">Descrição</label>
                <textarea id="categoria-descricao" name="descricao" rows="3" required placeholder="Descreva brevemente esta categoria"></textarea>
                <span class="admin-field-error">Informe uma descrição.</span>
            </div>

            <div class="admin-field-grid">
                <div class="admin-field">
                    <label for="categoria-icone">Ícone (Lucide)</label>
                    <div class="admin-input-with-icon">
                        <span id="categoria-icone-preview"><?= icone('shapes') ?></span>
                        <input type="text" id="categoria-icone" name="icone" required placeholder="Ex.: book-open">
                    </div>
                </div>

                <div class="admin-field">
                    <label for="categoria-cor">Cor</label>
                    <select id="categoria-cor" name="cor" required>
                        <?php foreach ($coresDisponiveisAdmin as $valor => $rotulo): ?>
                            <option value="<?= htmlspecialchars($valor) ?>"><?= htmlspecialchars($rotulo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="admin-field">
                <span class="admin-field-legenda">Status</span>
                <label class="admin-toggle">
                    <input type="checkbox" id="categoria-status" name="status" checked>
                    <span class="admin-toggle-track" aria-hidden="true"></span>
                    <span class="admin-toggle-texto" data-texto-toggle-status>Ativa</span>
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

<?php require __DIR__ . '/includes/admin-rodape.php'; ?>
