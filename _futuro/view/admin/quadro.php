<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>
<?php $e = static function ($texto): string { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); }; ?>

<div class="admin-page-header">
    <div>
        <h2>Quadro de horários</h2>
        <p>Os alunos escolhem a turma em <a href="../minha-turma.php" target="_blank" rel="noopener noreferrer">minha-turma.php</a> e veem as aulas do dia com a sala e o caminho no mapa.</p>
    </div>
    <a href="quadro.php?modelo=1" class="button button-outline"><?= icone('download') ?><span>Baixar modelo CSV</span></a>
</div>

<?php if ($errosQuadro): ?>
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('triangle-alert') ?> Problemas no arquivo</h2>
        <ul><?php foreach ($errosQuadro as $erro): ?><li><?= $e($erro) ?></li><?php endforeach; ?></ul>
        <p class="admin-campo-ajuda">Corrija no Excel e envie de novo. Nada foi salvo.</p>
    </section>
<?php endif; ?>

<form class="admin-form admin-panel" method="post" enctype="multipart/form-data" novalidate>
    <?= campoCsrf() ?>
    <input type="hidden" name="acao" value="enviar">
    <h2 class="admin-panel-title"><?= icone('upload') ?> Enviar quadro novo</h2>
    <p class="admin-campo-ajuda">Colunas: <strong>turma; dia; inicio; fim; disciplina; professor; sala</strong>. No Excel, use "Salvar como › CSV (separado por ponto e vírgula)". Se a sala tiver o mesmo nome de uma sala do mapa, o aluno ganha o botão "Como chegar".</p>
    <div class="admin-field-grid">
        <div class="admin-field">
            <label for="quadro-arquivo">Arquivo CSV (até 1 MB)</label>
            <input type="file" id="quadro-arquivo" name="arquivo" accept=".csv,.txt,text/csv" required>
            <span class="admin-field-error">Escolha o arquivo.</span>
        </div>
        <div class="admin-field">
            <label for="quadro-descricao">Descrição (opcional)</label>
            <input type="text" id="quadro-descricao" name="descricao" maxlength="200" placeholder="Ex.: 2026/2 — versão de agosto">
        </div>
    </div>
    <div class="admin-form-footer-inline">
        <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Ler arquivo e ver prévia</span></button>
    </div>
</form>

<?php if ($versaoPrevia): ?>
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('eye') ?> Prévia: <?= $e($versaoPrevia['nome_arquivo']) ?> (<?= (int) $versaoPrevia['total_aulas'] ?> aulas)</h2>
        <?php if (!(int) $versaoPrevia['publicada']): ?>
            <form method="post" class="admin-form-inline">
                <?= campoCsrf() ?>
                <input type="hidden" name="acao" value="publicar"><input type="hidden" name="versao" value="<?= (int) $versaoPrevia['id'] ?>">
                <button type="submit" class="button button-primary"><?= icone('check') ?><span>Está certo: publicar para os alunos</span></button>
            </form>
        <?php else: ?>
            <p class="admin-aviso-info"><?= icone('check') ?> <span>Esta é a versão publicada.</span></p>
        <?php endif; ?>
        <?php foreach ($aulasPrevia as $turma => $aulas): ?>
            <h3 class="admin-form-subtitulo">Turma <?= $e($turma) ?></h3>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead><tr><th>Dia</th><th>Horário</th><th>Disciplina</th><th>Professor</th><th>Sala</th></tr></thead>
                    <tbody>
                        <?php foreach ($aulas as $aula): ?>
                            <tr>
                                <td data-rotulo="Dia"><?= $e(ucfirst(NOMES_DIAS[(int) $aula['dia_semana']])) ?></td>
                                <td data-rotulo="Horário"><?= $e($aula['inicio'] . ' – ' . $aula['fim']) ?></td>
                                <td data-rotulo="Disciplina"><?= $e($aula['disciplina']) ?></td>
                                <td data-rotulo="Professor"><?= $e($aula['professor'] ?? '—') ?></td>
                                <td data-rotulo="Sala"><?= $e($aula['sala_texto'] ?? '—') ?><?= $aula['sala_id'] ? ' ' . icone('map-pin') : '' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
        <p class="admin-campo-ajuda"><?= icone('map-pin') ?> = sala reconhecida no mapa (o aluno vê "Como chegar").</p>
    </section>
<?php endif; ?>

<section class="admin-panel">
    <h2 class="admin-panel-title"><?= icone('history') ?> Versões enviadas</h2>
    <?php if (!$versoes): ?>
        <p class="admin-campo-ajuda">Nenhum quadro enviado ainda.</p>
    <?php else: ?>
        <ul class="admin-lista-simples">
            <?php foreach ($versoes as $versao): ?>
                <li>
                    <span>
                        <strong><?= $e($versao['nome_arquivo']) ?></strong>
                        <?= (int) $versao['publicada'] ? '<span class="admin-badge-destaque">Publicada</span>' : '' ?><br>
                        <small><?= $e(($versao['descricao'] ? $versao['descricao'] . ' · ' : '') . $versao['total_aulas'] . ' aulas · enviado por ' . $versao['enviado_por'] . ' em ' . dataHoraBr($versao['enviado_em'])) ?></small>
                    </span>
                    <span class="admin-row-actions">
                        <a href="quadro.php?versao=<?= (int) $versao['id'] ?>" class="button button-outline button-small"><?= icone('eye') ?><span>Ver</span></a>
                        <?php if (!(int) $versao['publicada']): ?>
                            <form method="post" class="admin-form-inline">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="acao" value="publicar"><input type="hidden" name="versao" value="<?= (int) $versao['id'] ?>">
                                <button type="submit" class="button button-outline button-small"><?= icone('rotate-ccw') ?><span>Publicar esta</span></button>
                            </form>
                            <form method="post" class="admin-form-inline">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="acao" value="excluir"><input type="hidden" name="versao" value="<?= (int) $versao['id'] ?>">
                                <button type="submit" class="admin-icon-button admin-icon-button-danger" aria-label="Excluir versão" data-confirmar-exclusao="Excluir a versão <?= $e($versao['nome_arquivo']) ?>?"><?= icone('trash-2') ?></button>
                            </form>
                        <?php endif; ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
