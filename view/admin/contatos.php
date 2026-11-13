<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Contatos e mensagens</h2>
        <p>Contato geral do campus (rodapé, página inicial e página Contato) e as mensagens enviadas pelo formulário do site.</p>
    </div>
</div>

<section class="admin-panel" id="mensagens">
    <h2 class="admin-panel-title"><?= icone('inbox') ?> Mensagens recebidas (<?= count($mensagensContato) ?>)</h2>
    <p class="admin-campo-ajuda">
        <?php if (configuracao('contato_email_destino') !== ''): ?>
            Cada mensagem nova também gera um aviso para <?= htmlspecialchars(configuracao('contato_email_destino'), ENT_QUOTES, 'UTF-8') ?>.
        <?php else: ?>
            Nenhum e-mail recebe aviso de mensagem nova: elas ficam só aqui. Configure um e-mail em Configurações.
        <?php endif; ?>
    </p>

    <?php if (!$mensagensContato): ?>
        <p class="admin-empty-state"><?= icone('inbox') ?> Nenhuma mensagem recebida ainda.</p>
    <?php else: ?>
        <div class="admin-toolbar">
            <div class="admin-search-field">
                <?= icone('search') ?>
                <input type="search" placeholder="Buscar mensagem..." aria-label="Buscar mensagem" data-busca-lista="#corpo-tabela-mensagens">
            </div>
            <div class="admin-filter-group" role="group" aria-label="Filtrar mensagens">
                <button type="button" class="admin-filter-chip active" aria-pressed="true" data-filtro="#corpo-tabela-mensagens" data-filtro-chave="lida" data-filtro-valor="todos">Todas</button>
                <button type="button" class="admin-filter-chip" aria-pressed="false" data-filtro="#corpo-tabela-mensagens" data-filtro-chave="lida" data-filtro-valor="nao">Não lidas</button>
            </div>
        </div>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr><th>Recebida</th><th>De</th><th>Assunto e mensagem</th><th class="admin-table-acoes-col">Ações</th></tr>
                </thead>
                <tbody id="corpo-tabela-mensagens">
                    <?php foreach ($mensagensContato as $mensagem): ?>
                        <?php $lida = $mensagem['lida_em'] !== null; ?>
                        <tr data-linha data-lida="<?= $lida ? 'sim' : 'nao' ?>" class="<?= $lida ? '' : 'admin-linha-nao-lida' ?>"
                            data-busca="<?= htmlspecialchars($mensagem['nome'] . ' ' . $mensagem['email'] . ' ' . $mensagem['assunto'] . ' ' . $mensagem['mensagem'], ENT_QUOTES, 'UTF-8') ?>">
                            <td data-rotulo="Recebida"><?= htmlspecialchars(dataHoraBr($mensagem['criada_em']), ENT_QUOTES, 'UTF-8') ?><?= $lida ? '' : '<br><span class="admin-badge-destaque">Nova</span>' ?></td>
                            <td data-rotulo="De">
                                <strong><?= htmlspecialchars($mensagem['nome'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <span class="admin-cell-subtexto"><?= htmlspecialchars($mensagem['email'], ENT_QUOTES, 'UTF-8') ?><?= $mensagem['telefone'] ? ' · ' . htmlspecialchars($mensagem['telefone'], ENT_QUOTES, 'UTF-8') : '' ?></span>
                            </td>
                            <td data-rotulo="Mensagem">
                                <strong><?= htmlspecialchars($mensagem['assunto'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <p class="admin-mensagem-texto"><?= htmlspecialchars($mensagem['mensagem'], ENT_QUOTES, 'UTF-8') ?></p>
                            </td>
                            <td data-rotulo="Ações" class="admin-table-acoes-col">
                                <div class="admin-row-actions">
                                    <a class="admin-icon-button" aria-label="Responder por e-mail"
                                        href="mailto:<?= htmlspecialchars(rawurlencode($mensagem['email']), ENT_QUOTES, 'UTF-8') ?>?subject=<?= htmlspecialchars(rawurlencode('Re: ' . $mensagem['assunto']), ENT_QUOTES, 'UTF-8') ?>"><?= icone('reply') ?></a>
                                    <form method="post" class="admin-form-inline">
                                        <?= campoCsrf() ?>
                                        <input type="hidden" name="acao" value="<?= $lida ? 'marcar_nao_lida' : 'marcar_lida' ?>">
                                        <input type="hidden" name="id" value="<?= (int) $mensagem['id'] ?>">
                                        <button type="submit" class="admin-icon-button" aria-label="<?= $lida ? 'Marcar como não lida' : 'Marcar como lida' ?>"><?= icone($lida ? 'mail' : 'mail-open') ?></button>
                                    </form>
                                    <form method="post" class="admin-form-inline">
                                        <?= campoCsrf() ?>
                                        <input type="hidden" name="acao" value="excluir_mensagem">
                                        <input type="hidden" name="id" value="<?= (int) $mensagem['id'] ?>">
                                        <button type="submit" class="admin-icon-button admin-icon-button-danger" aria-label="Excluir mensagem"
                                            data-confirmar-exclusao="Excluir a mensagem de <?= htmlspecialchars($mensagem['nome'], ENT_QUOTES, 'UTF-8') ?>?"><?= icone('trash-2') ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="admin-empty-state" data-vazio-lista="#corpo-tabela-mensagens" hidden><?= icone('search-x') ?> Nenhuma mensagem com esse filtro.</p>
        </div>
    <?php endif; ?>
</section>

<form class="admin-form admin-panel" method="post" novalidate>
    <?= campoCsrf() ?>
    <input type="hidden" name="acao" value="salvar_contato">
    <h2 class="admin-panel-title"><?= icone('building') ?> Contato geral do campus</h2>
    <p class="admin-campo-ajuda">O telefone e o e-mail de cada setor ficam em Setores.</p>

    <div class="admin-field-grid">
        <?php foreach ($camposContatoGeral as $chave => [$rotulo, $maximo, $tipo]): ?>
            <div class="admin-field <?= isset($errosContato[$chave]) ? 'admin-field-invalido' : '' ?>">
                <label for="campo-<?= $chave ?>"><?= htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') ?></label>
                <input type="<?= $tipo ?>" id="campo-<?= $chave ?>" name="<?= $chave ?>" maxlength="<?= $maximo ?>"
                    value="<?= htmlspecialchars((string) ($valoresContato[$chave] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <span class="admin-field-error"><?= htmlspecialchars($errosContato[$chave] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="admin-form-footer-inline">
        <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Salvar contato geral</span></button>
    </div>
</form>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
