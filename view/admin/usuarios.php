<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Usuários</h2>
        <p>Quem acessa o painel. O <strong>administrador geral</strong> pode tudo; o <strong>responsável de setor</strong> só edita os setores ligados a ele.</p>
    </div>
    <button type="button" class="button button-primary" data-abrir-modal="modal-usuario" data-titulo="Novo usuário">
        <?= icone('user-plus') ?><span>Novo usuário</span>
    </button>
</div>

<div class="admin-table-wrapper">
    <table class="admin-table">
        <thead>
            <tr><th>Nome</th><th>Perfil</th><th>Setores</th><th>Último acesso</th><th>Status</th><th class="admin-table-acoes-col">Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($usuariosAdmin as $usuario): ?>
                <?php $ativo = (int) $usuario['ativo'] === 1; $souEu = (int) $usuario['id'] === (int) $usuarioAdmin['id']; ?>
                <tr>
                    <td data-rotulo="Nome">
                        <strong><?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?></strong><?= $souEu ? ' <small>(você)</small>' : '' ?><br>
                        <span class="admin-cell-subtexto"><?= htmlspecialchars($usuario['usuario'], ENT_QUOTES, 'UTF-8') ?><?= $usuario['email'] ? ' · ' . htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') : '' ?></span>
                    </td>
                    <td data-rotulo="Perfil"><?= htmlspecialchars($usuario['perfil_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-rotulo="Setores">
                        <?php if ($usuario['perfil_id'] === 'admin_geral'): ?>
                            Todos
                        <?php else: ?>
                            <?= htmlspecialchars(implode(', ', array_map(static function ($idSetor) use ($nomesSetores) { return $nomesSetores[$idSetor] ?? $idSetor; }, $usuario['setores'])) ?: '—', ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                    </td>
                    <td data-rotulo="Último acesso"><?= htmlspecialchars(dataHoraBr($usuario['ultimo_login_em']), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-rotulo="Status">
                        <form method="post" class="admin-form-inline">
                            <?= campoCsrf() ?>
                            <input type="hidden" name="acao" value="alternar">
                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                            <button type="submit" class="admin-status-badge admin-status-<?= $ativo ? 'ativo' : 'inativo' ?>" <?= $souEu ? 'disabled' : '' ?>
                                aria-label="<?= $ativo ? 'Ativo. Clique para desativar' : 'Inativo. Clique para ativar' ?>">
                                <?= icone($ativo ? 'check-circle-2' : 'circle-slash') ?><span><?= $ativo ? 'Ativo' : 'Inativo' ?></span>
                            </button>
                        </form>
                    </td>
                    <td data-rotulo="Ações" class="admin-table-acoes-col">
                        <div class="admin-row-actions">
                            <button type="button" class="admin-icon-button" aria-label="Editar <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                data-abrir-modal="modal-usuario" data-titulo="Editar usuário"
                                data-valores="<?= dadosParaModal(['id' => $usuario['id'], 'nome' => $usuario['nome'], 'usuario' => $usuario['usuario'], 'email' => (string) $usuario['email'], 'perfil_id' => $usuario['perfil_id'], 'ativo' => $ativo, 'setores' => $usuario['setores']]) ?>">
                                <?= icone('pencil') ?>
                            </button>
                            <form method="post" class="admin-form-inline">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="acao" value="derrubar_sessoes">
                                <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                <button type="submit" class="admin-icon-button" aria-label="Deslogar <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?> de todos os aparelhos" title="Sair de todos os aparelhos"><?= icone('monitor-off') ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="admin-modal" id="modal-usuario" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-usuario-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-usuario-titulo" data-titulo-modal>Novo usuário</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário"><?= icone('x') ?></button>
        </div>
        <form class="admin-form" method="post" novalidate autocomplete="off">
            <?= campoCsrf() ?>
            <input type="hidden" name="acao" value="salvar">
            <input type="hidden" name="id" value="" data-limpar>

            <div class="admin-field">
                <label for="usuario-nome">Nome</label>
                <input type="text" id="usuario-nome" name="nome" required maxlength="150">
                <span class="admin-field-error">Informe o nome.</span>
            </div>
            <div class="admin-field-grid">
                <div class="admin-field">
                    <label for="usuario-login">Usuário (para entrar)</label>
                    <input type="text" id="usuario-login" name="usuario" required maxlength="60" placeholder="ex.: maria.silva" autocapitalize="none">
                    <span class="admin-field-error">Informe o usuário.</span>
                </div>
                <div class="admin-field">
                    <label for="usuario-email">E-mail (opcional, também serve para entrar)</label>
                    <input type="email" id="usuario-email" name="email" maxlength="150">
                    <span class="admin-field-error">E-mail inválido.</span>
                </div>
            </div>
            <div class="admin-field">
                <label for="usuario-senha">Senha</label>
                <div class="admin-input-with-icon">
                    <?= icone('lock') ?>
                    <input type="password" id="usuario-senha" name="senha" maxlength="<?= SENHA_TAMANHO_MAXIMO ?>" autocomplete="new-password">
                    <button type="button" class="admin-toggle-senha" aria-label="Mostrar senha" aria-pressed="false">
                        <?= icone('eye', 'icon-mostrar-senha') ?><?= icone('eye-off', 'icon-ocultar-senha') ?>
                    </button>
                </div>
                <p class="admin-campo-ajuda">De <?= SENHA_TAMANHO_MINIMO ?> a <?= SENHA_TAMANHO_MAXIMO ?> caracteres, com letras e números. Ao editar, deixe em branco para manter a senha atual.</p>
                <span class="admin-field-error">Senha inválida.</span>
            </div>
            <div class="admin-field">
                <label for="usuario-perfil">Perfil</label>
                <select id="usuario-perfil" name="perfil_id">
                    <?php foreach ($perfisValidos as $idPerfil => $nomePerfil): ?>
                        <option value="<?= htmlspecialchars($idPerfil, ENT_QUOTES, 'UTF-8') ?>" <?= $idPerfil === 'responsavel_setor' ? 'selected' : '' ?>><?= htmlspecialchars($nomePerfil, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="admin-field-error">Escolha um perfil.</span>
            </div>
            <div class="admin-field">
                <span class="admin-field-legenda">Setores que pode editar (só para responsável de setor)</span>
                <div class="admin-checkbox-grade">
                    <?php foreach ($setoresParaUsuario as $setor): ?>
                        <label><input type="checkbox" name="setores[]" value="<?= htmlspecialchars($setor['id'], ENT_QUOTES, 'UTF-8') ?>"> <?= htmlspecialchars($setor['sigla'] ?: $setor['nome'], ENT_QUOTES, 'UTF-8') ?></label>
                    <?php endforeach; ?>
                </div>
                <span class="admin-field-error">Escolha pelo menos um setor.</span>
            </div>
            <div class="admin-field">
                <label class="admin-toggle">
                    <input type="checkbox" name="ativo" value="1" checked>
                    <span class="admin-toggle-track" aria-hidden="true"></span>
                    <span class="admin-toggle-texto" data-texto-toggle data-ligado="Ativo (pode entrar)" data-desligado="Inativo (não pode entrar)">Ativo (pode entrar)</span>
                </label>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Salvar usuário</span></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
