<?php
/**
 * CONFIGURAÇÕES DO PAINEL
 *
 * INTEGRAÇÃO FUTURA:
 * Não há upload real de arquivos aqui — a pré-visualização do logotipo é
 * feita inteiramente no navegador (FileReader), sem enviar nada a um
 * servidor. Quando o backend existir, o formulário deve enviar os dados via
 * multipart/form-data para um endpoint que grave o arquivo e as
 * configurações no banco.
 */
$tituloPaginaAdmin = 'Configurações';
$paginaAtualAdmin = 'configuracoes';
require __DIR__ . '/includes/admin-cabecalho.php';

$coresPrincipaisAdmin = ['green' => ['rotulo' => 'Verde institucional', 'hex' => '#0f7a43'], 'blue' => ['rotulo' => 'Azul', 'hex' => '#1d63d6'], 'teal' => ['rotulo' => 'Verde-azulado', 'hex' => '#0f8a7a'], 'slate' => ['rotulo' => 'Cinza-azulado', 'hex' => '#334155']];
?>

<div class="admin-page-header">
    <div>
        <h2>Configurações</h2>
        <p>Ajustes visuais e preferências gerais do painel administrativo.</p>
    </div>
</div>

<div class="admin-config-layout">
    <form class="admin-form admin-panel" id="form-configuracoes" novalidate>

        <h2 class="admin-panel-title"><?= icone('sliders-horizontal') ?> Identidade do sistema</h2>

        <div class="admin-field-grid">
            <div class="admin-field">
                <label for="config-nome-sistema">Nome do sistema</label>
                <input type="text" id="config-nome-sistema" name="nomeSistema" required value="<?= htmlspecialchars(NOME_SISTEMA) ?>">
            </div>
            <div class="admin-field">
                <label for="config-nome-campus">Nome do campus</label>
                <input type="text" id="config-nome-campus" name="nomeCampus" required value="<?= htmlspecialchars(NOME_CAMPUS) ?>">
            </div>
        </div>

        <div class="admin-field">
            <label for="config-rodape">Texto do rodapé</label>
            <input type="text" id="config-rodape" name="textoRodape" required value="Todos os direitos reservados.">
        </div>

        <div class="admin-field">
            <span class="admin-field-legenda">Logotipo</span>
            <div class="admin-upload-preview">
                <div class="admin-upload-preview-caixa" id="logo-preview-caixa">
                    <span class="brand-mark" id="logo-preview-marca">IFRO</span>
                </div>
                <div>
                    <label for="config-logo" class="button button-outline button-small">
                        <?= icone('image-up') ?>
                        <span>Escolher imagem</span>
                    </label>
                    <input type="file" id="config-logo" name="logo" accept="image/*" class="admin-input-arquivo-oculto">
                    <p class="admin-campo-ajuda">Apenas pré-visualização — nenhum arquivo é enviado a um servidor por enquanto.</p>
                </div>
            </div>
        </div>

        <h2 class="admin-panel-title admin-panel-title-espacada"><?= icone('palette') ?> Cor principal</h2>
        <div class="admin-cor-opcoes" role="radiogroup" aria-label="Cor principal do sistema">
            <?php foreach ($coresPrincipaisAdmin as $chave => $cor): ?>
                <label class="admin-cor-opcao">
                    <input type="radio" name="corPrincipal" value="<?= htmlspecialchars($chave) ?>" <?= $chave === 'green' ? 'checked' : '' ?>>
                    <span class="admin-cor-amostra" style="background: <?= htmlspecialchars($cor['hex']) ?>"></span>
                    <span><?= htmlspecialchars($cor['rotulo']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <h2 class="admin-panel-title admin-panel-title-espacada"><?= icone('user-cog') ?> Informações do administrador</h2>
        <div class="admin-field-grid">
            <div class="admin-field">
                <label for="config-admin-nome">Nome exibido no painel</label>
                <input type="text" id="config-admin-nome" name="adminNome" required value="Administrador">
            </div>
            <div class="admin-field">
                <label for="config-admin-email">E-mail de contato</label>
                <input type="email" id="config-admin-email" name="adminEmail" required value="admin@ifro.edu.br">
            </div>
        </div>

        <h2 class="admin-panel-title admin-panel-title-espacada"><?= icone('layout-panel-left') ?> Preferências do painel</h2>
        <div class="admin-field">
            <label class="admin-toggle">
                <input type="checkbox" id="config-menu-recolhido" name="menuRecolhidoPadrao">
                <span class="admin-toggle-track" aria-hidden="true"></span>
                <span class="admin-toggle-texto">Iniciar com o menu lateral recolhido</span>
            </label>
        </div>
        <div class="admin-field">
            <label class="admin-toggle">
                <input type="checkbox" id="config-confirmar-exclusoes" name="confirmarExclusoes" checked>
                <span class="admin-toggle-track" aria-hidden="true"></span>
                <span class="admin-toggle-texto">Sempre pedir confirmação antes de excluir</span>
            </label>
        </div>

        <div class="admin-modal-footer admin-modal-footer-inline">
            <button type="submit" class="button button-primary admin-botao-salvar">
                <span class="admin-botao-texto">Salvar configurações</span>
            </button>
        </div>
    </form>

    <aside class="admin-panel admin-config-nota">
        <h2 class="admin-panel-title"><?= icone('info') ?> Sobre estas configurações</h2>
        <p>Estas opções ainda não estão conectadas a um banco de dados. Elas servem para demonstrar visualmente como o administrador poderá personalizar o sistema quando o backend for implementado.</p>
        <div class="admin-integration-note admin-integration-note-compacta">
            <?= icone('server-cog') ?>
            <p>INTEGRAÇÃO FUTURA: salvar estas preferências no banco de dados e aplicá-las dinamicamente em todo o site público.</p>
        </div>
    </aside>
</div>

<?php require __DIR__ . '/includes/admin-rodape.php'; ?>
