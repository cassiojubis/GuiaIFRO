<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <a href="setores.php" class="text-link"><?= icone('arrow-left') ?> Voltar para setores</a>
        <h2>Horário de atendimento — <?= htmlspecialchars($setor['nome'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p>
            <?php if ($estadoAgora['resumo']): ?>
                Agora no site: <strong><?= htmlspecialchars($estadoAgora['resumo'], ENT_QUOTES, 'UTF-8') ?></strong>.
            <?php elseif ($setor['horario']): ?>
                Ainda sem horário por dia. O site está usando o texto antigo: "<?= htmlspecialchars($setor['horario'], ENT_QUOTES, 'UTF-8') ?>".
            <?php else: ?>
                Ainda sem horário cadastrado: o site não mostra o selo "Aberto agora".
            <?php endif; ?>
        </p>
    </div>
</div>

<form class="admin-form admin-panel" method="post" novalidate>
    <?= campoCsrf() ?>
    <input type="hidden" name="acao" value="salvar_semana">
    <input type="hidden" name="setor_id" value="<?= htmlspecialchars($idSetor, ENT_QUOTES, 'UTF-8') ?>">
    <h2 class="admin-panel-title"><?= icone('calendar-clock') ?> Semana</h2>
    <p class="admin-campo-ajuda">Uma linha por período (ex.: 08:00 às 12:00 e 14:00 às 18:00). Dia sem linha = fechado. Para fechar no almoço, use duas linhas.</p>

    <div class="admin-horarios-semana">
        <?php foreach ([1, 2, 3, 4, 5, 6, 0] as $dia): ?>
            <fieldset class="admin-horario-dia">
                <legend><?= ucfirst(NOMES_DIAS[$dia]) ?></legend>
                <ul data-faixas-dia="<?= $dia ?>">
                    <?php foreach ($faixasPorDia[$dia] ?? [] as $faixa): ?>
                        <li class="admin-faixa">
                            <input type="time" name="abre[<?= $dia ?>][]" value="<?= substr($faixa['abre'], 0, 5) ?>" aria-label="<?= ucfirst(NOMES_DIAS[$dia]) ?>: abre às">
                            <span>às</span>
                            <input type="time" name="fecha[<?= $dia ?>][]" value="<?= substr($faixa['fecha'], 0, 5) ?>" aria-label="<?= ucfirst(NOMES_DIAS[$dia]) ?>: fecha às">
                            <button type="button" class="admin-remover-item" aria-label="Remover este período"><?= icone('x') ?></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="admin-adicionar-item" data-adicionar-faixa="<?= $dia ?>"><?= icone('plus') ?><span>Período</span></button>
                <?php if ($dia === 1): ?>
                    <button type="button" class="admin-adicionar-item" data-copiar-segunda><?= icone('copy') ?><span>Copiar para terça a sexta</span></button>
                <?php endif; ?>
            </fieldset>
        <?php endforeach; ?>
    </div>

    <div class="admin-form-footer-inline">
        <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Salvar horário</span></button>
    </div>
</form>

<template id="modelo-faixa">
    <li class="admin-faixa">
        <input type="time" name="abre[__DIA__][]" aria-label="Abre às">
        <span>às</span>
        <input type="time" name="fecha[__DIA__][]" aria-label="Fecha às">
        <button type="button" class="admin-remover-item" aria-label="Remover este período"><i data-lucide="x" class="icon"></i></button>
    </li>
</template>

<section class="admin-panel">
    <h2 class="admin-panel-title"><?= icone('calendar-x') ?> Feriados, recessos e dias especiais</h2>
    <p class="admin-campo-ajuda">Feriados e recessos publicados no Calendário (Informações) já fecham todos os setores automaticamente.</p>

    <?php if ($excecoesSetor): ?>
        <ul class="admin-lista-simples">
            <?php foreach ($excecoesSetor as $excecao): ?>
                <li>
                    <span>
                        <strong><?= htmlspecialchars(dataHoraBr($excecao['data'], false), ENT_QUOTES, 'UTF-8') ?></strong> —
                        <?= (int) $excecao['fechado'] ? 'Fechado' : 'Das ' . htmlspecialchars(formatarHora($excecao['abre']) . ' às ' . formatarHora($excecao['fecha']), ENT_QUOTES, 'UTF-8') ?>
                        · <?= htmlspecialchars($excecao['motivo'], ENT_QUOTES, 'UTF-8') ?>
                        <?= $excecao['setor_id'] === null ? '<span class="admin-badge-destaque">Campus todo</span>' : '' ?>
                    </span>
                    <?php if ($excecao['setor_id'] !== null || ehAdminGeral()): ?>
                        <form method="post" class="admin-form-inline">
                            <?= campoCsrf() ?>
                            <input type="hidden" name="acao" value="excluir_excecao">
                            <input type="hidden" name="setor_id" value="<?= htmlspecialchars($idSetor, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id" value="<?= (int) $excecao['id'] ?>">
                            <button type="submit" class="admin-icon-button admin-icon-button-danger" aria-label="Excluir exceção"
                                data-confirmar-exclusao="Excluir a exceção de <?= htmlspecialchars(dataHoraBr($excecao['data'], false), ENT_QUOTES, 'UTF-8') ?>?"><?= icone('trash-2') ?></button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="admin-campo-ajuda">Nenhuma exceção marcada para os próximos dias.</p>
    <?php endif; ?>

    <form class="admin-form" method="post" novalidate>
        <?= campoCsrf() ?>
        <input type="hidden" name="acao" value="nova_excecao">
        <input type="hidden" name="setor_id" value="<?= htmlspecialchars($idSetor, ENT_QUOTES, 'UTF-8') ?>">
        <h3 class="admin-form-subtitulo">Nova exceção</h3>
        <div class="admin-field-grid admin-field-grid-3">
            <div class="admin-field">
                <label for="excecao-data">Data</label>
                <input type="date" id="excecao-data" name="data" required min="<?= date('Y-m-d') ?>">
                <span class="admin-field-error">Informe a data.</span>
            </div>
            <div class="admin-field">
                <label for="excecao-tipo">O que acontece</label>
                <select id="excecao-tipo" name="tipo">
                    <option value="fechado">Fechado o dia todo</option>
                    <option value="especial">Horário especial</option>
                </select>
            </div>
            <div class="admin-field">
                <label for="excecao-motivo">Motivo</label>
                <input type="text" id="excecao-motivo" name="motivo" required maxlength="150" placeholder="Ex.: Recesso, reunião, evento">
                <span class="admin-field-error">Informe o motivo.</span>
            </div>
        </div>
        <div class="admin-field-grid">
            <div class="admin-field">
                <label for="excecao-abre">Abre às (só horário especial)</label>
                <input type="time" id="excecao-abre" name="abre">
            </div>
            <div class="admin-field">
                <label for="excecao-fecha">Fecha às (só horário especial)</label>
                <input type="time" id="excecao-fecha" name="fecha">
            </div>
        </div>
        <?php if (ehAdminGeral()): ?>
            <label class="admin-checkbox"><input type="checkbox" name="campus_todo" value="1"><span>Vale para o campus todo (todos os setores)</span></label>
        <?php endif; ?>
        <div class="admin-form-footer-inline">
            <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Adicionar exceção</span></button>
        </div>
    </form>
</section>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
