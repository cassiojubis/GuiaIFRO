<?php
/**
 * GERENCIAMENTO DE CONTATOS
 *
 * INTEGRAÇÃO FUTURA:
 * Os valores abaixo alimentam o formulário e a pré-visualização. Ao salvar,
 * o JavaScript hoje apenas atualiza a pré-visualização e mostra um toast de
 * sucesso. Quando o backend existir, o envio deve gravar estes dados no
 * banco e também atualizar o rodapé/página de contato do site público.
 */
$tituloPaginaAdmin = 'Contatos';
$paginaAtualAdmin = 'contatos';
require __DIR__ . '/includes/admin-cabecalho.php';

$contatoGeralAdmin = [
    'telefone' => '(69) 3541-5282',
    'email' => 'guajara-mirim@ifro.edu.br',
    'endereco' => 'Av. Jorge Teixeira, 3146, Jardim Tropical, Guajará-Mirim – RO',
    'horario' => 'Segunda a sexta, das 7h às 13h e das 14h às 17h',
    'facebook' => 'https://facebook.com/ifro',
    'instagram' => 'https://instagram.com/ifro',
    'youtube' => 'https://youtube.com/@ifro',
    'mapaEmbed' => 'https://maps.google.com/?q=Instituto+Federal+de+Rondonia+Guajara-Mirim',
];

$contatosSetoresAdmin = [
    ['id' => 1, 'setor' => 'CRA', 'telefone' => '(69) 3541-5287', 'email' => 'cra@ifro.edu.br'],
    ['id' => 2, 'setor' => 'Biblioteca', 'telefone' => '(69) 3541-5288', 'email' => 'biblioteca@ifro.edu.br'],
    ['id' => 3, 'setor' => 'Assistência Estudantil', 'telefone' => '(69) 3541-5284', 'email' => 'assistencia@ifro.edu.br'],
];
?>

<div class="admin-page-header">
    <div>
        <h2>Contatos</h2>
        <p>Informações exibidas na página de contato e no rodapé do site público.</p>
    </div>
</div>

<div class="admin-contatos-layout">
    <form class="admin-form admin-panel" id="form-contatos" novalidate>
        <h2 class="admin-panel-title"><?= icone('building') ?> Contato institucional</h2>

        <div class="admin-field-grid">
            <div class="admin-field">
                <label for="contato-telefone">Telefone principal</label>
                <input type="tel" id="contato-telefone" name="telefone" required value="<?= htmlspecialchars($contatoGeralAdmin['telefone']) ?>">
            </div>
            <div class="admin-field">
                <label for="contato-email">E-mail institucional</label>
                <input type="email" id="contato-email" name="email" required value="<?= htmlspecialchars($contatoGeralAdmin['email']) ?>">
            </div>
        </div>

        <div class="admin-field">
            <label for="contato-endereco">Endereço</label>
            <input type="text" id="contato-endereco" name="endereco" required value="<?= htmlspecialchars($contatoGeralAdmin['endereco']) ?>">
        </div>

        <div class="admin-field">
            <label for="contato-horario">Horário de atendimento</label>
            <input type="text" id="contato-horario" name="horario" required value="<?= htmlspecialchars($contatoGeralAdmin['horario']) ?>">
        </div>

        <div class="admin-field">
            <label for="contato-mapa">Link de localização do mapa</label>
            <input type="url" id="contato-mapa" name="mapaEmbed" required value="<?= htmlspecialchars($contatoGeralAdmin['mapaEmbed']) ?>">
        </div>

        <h3 class="admin-form-subtitulo">Redes sociais</h3>
        <div class="admin-field-grid admin-field-grid-3">
            <div class="admin-field">
                <label for="contato-facebook"><?= icone('facebook') ?> Facebook</label>
                <input type="url" id="contato-facebook" name="facebook" value="<?= htmlspecialchars($contatoGeralAdmin['facebook']) ?>">
            </div>
            <div class="admin-field">
                <label for="contato-instagram"><?= icone('instagram') ?> Instagram</label>
                <input type="url" id="contato-instagram" name="instagram" value="<?= htmlspecialchars($contatoGeralAdmin['instagram']) ?>">
            </div>
            <div class="admin-field">
                <label for="contato-youtube"><?= icone('youtube') ?> YouTube</label>
                <input type="url" id="contato-youtube" name="youtube" value="<?= htmlspecialchars($contatoGeralAdmin['youtube']) ?>">
            </div>
        </div>

        <div class="admin-form-footer-inline">
            <button type="button" class="button button-outline" id="botao-visualizar-contato">
                <?= icone('eye') ?><span>Atualizar pré-visualização</span>
            </button>
            <button type="submit" class="button button-primary admin-botao-salvar">
                <span class="admin-botao-texto">Salvar contatos</span>
            </button>
        </div>
    </form>

    <!-- PRÉ-VISUALIZAÇÃO: espelha como aparecerá no rodapé do site público -->
    <aside class="admin-panel admin-preview-contato">
        <h2 class="admin-panel-title"><?= icone('monitor') ?> Pré-visualização</h2>
        <div class="admin-preview-card">
            <div class="admin-preview-card-marca">
                <span class="brand-mark">IFRO</span>
                <span class="brand-copy">
                    <strong><?= NOME_SISTEMA ?></strong>
                    <small><?= NOME_CAMPUS ?></small>
                </span>
            </div>
            <ul class="admin-preview-lista">
                <li><?= icone('phone') ?><span id="preview-telefone"><?= htmlspecialchars($contatoGeralAdmin['telefone']) ?></span></li>
                <li><?= icone('mail') ?><span id="preview-email"><?= htmlspecialchars($contatoGeralAdmin['email']) ?></span></li>
                <li><?= icone('map-pin') ?><span id="preview-endereco"><?= htmlspecialchars($contatoGeralAdmin['endereco']) ?></span></li>
                <li><?= icone('clock') ?><span id="preview-horario"><?= htmlspecialchars($contatoGeralAdmin['horario']) ?></span></li>
            </ul>
        </div>
    </aside>
</div>

<section class="admin-panel">
    <div class="admin-tab-painel-header">
        <h2 class="admin-panel-title"><?= icone('contact') ?> Contatos específicos dos setores</h2>
        <button type="button" class="button button-primary button-small admin-abrir-modal" data-modal-alvo="modal-contato-setor" data-modo="criar">
            <?= icone('plus') ?><span>Adicionar contato</span>
        </button>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Setor</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th class="admin-table-acoes-col">Ações</th>
                </tr>
            </thead>
            <tbody id="corpo-tabela-contatos-setores">
                <?php foreach ($contatosSetoresAdmin as $contato): ?>
                    <tr
                        data-linha-contato-setor
                        data-id="<?= (int) $contato['id'] ?>"
                        data-setor="<?= htmlspecialchars($contato['setor']) ?>"
                        data-telefone="<?= htmlspecialchars($contato['telefone']) ?>"
                        data-email="<?= htmlspecialchars($contato['email']) ?>"
                    >
                        <td data-rotulo="Setor"><strong><?= htmlspecialchars($contato['setor']) ?></strong></td>
                        <td data-rotulo="Telefone"><?= htmlspecialchars($contato['telefone']) ?></td>
                        <td data-rotulo="E-mail"><?= htmlspecialchars($contato['email']) ?></td>
                        <td data-rotulo="Ações" class="admin-table-acoes-col">
                            <div class="admin-row-actions">
                                <button type="button" class="admin-icon-button admin-abrir-modal" data-modal-alvo="modal-contato-setor" data-modo="editar" aria-label="Editar contato do setor">
                                    <?= icone('pencil') ?>
                                </button>
                                <button type="button" class="admin-icon-button admin-icon-button-danger admin-abrir-exclusao" data-tipo-exclusao="contato-setor" aria-label="Excluir contato do setor">
                                    <?= icone('trash-2') ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- DRAWER: formulário de contato específico de setor -->
<div class="admin-modal" id="modal-contato-setor" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-contato-setor-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-contato-setor-titulo">Novo contato de setor</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário">
                <?= icone('x') ?>
            </button>
        </div>
        <form class="admin-form" id="form-contato-setor" novalidate>
            <div class="admin-field">
                <label for="contato-setor-nome">Setor</label>
                <input type="text" id="contato-setor-nome" name="setor" required placeholder="Ex.: CRA">
            </div>
            <div class="admin-field">
                <label for="contato-setor-telefone">Telefone</label>
                <input type="tel" id="contato-setor-telefone" name="telefone" required placeholder="(69) 3541-0000">
            </div>
            <div class="admin-field">
                <label for="contato-setor-email">E-mail</label>
                <input type="email" id="contato-setor-email" name="email" required placeholder="setor@ifro.edu.br">
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="submit" class="button button-primary admin-botao-salvar">
                    <span class="admin-botao-texto">Salvar contato</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/admin-rodape.php'; ?>
