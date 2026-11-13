<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Meus dados</h2>
        <p>
            Você entra como <strong><?= htmlspecialchars($usuarioAdmin['usuario'], ENT_QUOTES, 'UTF-8') ?></strong>
            (<?= ehAdminGeral() ? 'administrador geral' : 'responsável de setor' ?>).
            <?php if (!ehAdminGeral() && $usuarioAdmin['setores']): ?>
                Setores: <?= htmlspecialchars(implode(', ', array_map(static function ($id) use ($nomesSetores) { return $nomesSetores[$id] ?? $id; }, $usuarioAdmin['setores'])), ENT_QUOTES, 'UTF-8') ?>.
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (!empty($_SESSION['admin_trocar_senha'])): ?>
    <div class="admin-aviso-senha" role="alert">
        <?= icone('shield-alert') ?>
        <div>
            <strong>Troque sua senha para continuar.</strong>
            Você entrou com a senha padrão (ou com uma senha fraca). Por segurança, o painel só libera as outras telas depois da troca.
            A senha nova precisa ter <?= SENHA_TAMANHO_MINIMO ?>+ caracteres, com letras e números.
        </div>
    </div>
<?php endif; ?>

<div class="admin-dashboard-columns">
    <form class="admin-form admin-panel" method="post" novalidate>
        <?= campoCsrf() ?>
        <input type="hidden" name="acao" value="salvar_dados">
        <h2 class="admin-panel-title"><?= icone('user') ?> Dados</h2>
        <div class="admin-field">
            <label for="meu-nome">Nome</label>
            <input type="text" id="meu-nome" name="nome" required maxlength="150" value="<?= htmlspecialchars($usuarioAdmin['nome'], ENT_QUOTES, 'UTF-8') ?>">
            <span class="admin-field-error">Informe o nome.</span>
        </div>
        <div class="admin-field">
            <label for="meu-email">E-mail</label>
            <input type="email" id="meu-email" name="email" maxlength="150" value="<?= htmlspecialchars((string) $usuarioAdmin['email'], ENT_QUOTES, 'UTF-8') ?>">
            <p class="admin-campo-ajuda">Usado para avisos de revisão do setor. Também serve para entrar.</p>
        </div>
        <div class="admin-form-footer-inline">
            <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Salvar dados</span></button>
        </div>
    </form>

    <form class="admin-form admin-panel" method="post" novalidate autocomplete="off">
        <?= campoCsrf() ?>
        <input type="hidden" name="acao" value="trocar_senha">
        <h2 class="admin-panel-title"><?= icone('key-round') ?> Trocar senha</h2>
        <?php foreach (['senha_atual' => ['Senha atual', 'current-password'], 'senha_nova' => ['Senha nova', 'new-password'], 'senha_repetida' => ['Repita a senha nova', 'new-password']] as $campo => [$rotulo, $autocompletar]): ?>
            <div class="admin-field">
                <label for="campo-<?= $campo ?>"><?= $rotulo ?></label>
                <div class="admin-input-with-icon">
                    <?= icone('lock') ?>
                    <input type="password" id="campo-<?= $campo ?>" name="<?= $campo ?>" required maxlength="<?= SENHA_TAMANHO_MAXIMO ?>" autocomplete="<?= $autocompletar ?>">
                    <button type="button" class="admin-toggle-senha" aria-label="Mostrar senha" aria-pressed="false">
                        <?= icone('eye', 'icon-mostrar-senha') ?><?= icone('eye-off', 'icon-ocultar-senha') ?>
                    </button>
                </div>
                <span class="admin-field-error">Preencha este campo.</span>
            </div>
        <?php endforeach; ?>
        <p class="admin-campo-ajuda">De <?= SENHA_TAMANHO_MINIMO ?> a <?= SENHA_TAMANHO_MAXIMO ?> caracteres, com letras e números. Ao trocar, os outros aparelhos conectados são deslogados.</p>
        <div class="admin-form-footer-inline">
            <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Trocar senha</span></button>
        </div>
    </form>
</div>

<section class="admin-panel">
    <h2 class="admin-panel-title"><?= icone('monitor-off') ?> Sair de todos os aparelhos</h2>
    <p class="admin-campo-ajuda">Esqueceu o painel aberto num computador do laboratório? Isso desloga você de todos os aparelhos, inclusive deste.</p>
    <form method="post">
        <?= campoCsrf() ?>
        <input type="hidden" name="acao" value="sair_de_todos">
        <button type="submit" class="button button-outline"><?= icone('log-out') ?><span>Sair de todos os aparelhos</span></button>
    </form>
</section>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
