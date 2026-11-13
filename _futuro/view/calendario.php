<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb"><a href="index.php">Início</a><?= icone('chevron-right') ?><strong>Calendário</strong></nav>
        <h1>Calendário acadêmico</h1>
        <p>Feriados, recessos, provas e eventos do campus.</p>
        <?php if ($eventosPorMes): ?>
            <a href="calendario.php?ics=todos" class="button button-primary"><?= icone('calendar-plus') ?> Adicionar tudo à minha agenda</a>
        <?php endif; ?>
    </div>
</section>

<section class="section container">
    <?php if (!$eventosPorMes): ?>
        <p>Nenhuma data publicada no calendário ainda.</p>
    <?php endif; ?>
    <?php foreach ($eventosPorMes as $mes => $eventos): ?>
        <h2 class="calendario-mes"><?= htmlspecialchars($mes, ENT_QUOTES, 'UTF-8') ?></h2>
        <ul class="busca-lista">
            <?php foreach ($eventos as $evento): ?>
                <li class="busca-item content-card calendario-<?= htmlspecialchars($evento['tipo'], ENT_QUOTES, 'UTF-8') ?>">
                    <div>
                        <strong><?= htmlspecialchars($evento['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <small>
                            <?= htmlspecialchars(formatarDataCurta($evento['inicio']) . ($evento['fim'] ? ' a ' . formatarDataCurta($evento['fim']) : ''), ENT_QUOTES, 'UTF-8') ?>
                            · <?= htmlspecialchars($tiposEvento[$evento['tipo']] ?? 'Evento', ENT_QUOTES, 'UTF-8') ?>
                            <?= $evento['periodo_letivo'] ? ' · ' . htmlspecialchars($evento['periodo_letivo'], ENT_QUOTES, 'UTF-8') : '' ?>
                        </small>
                        <?php if ($evento['descricao']): ?><p><?= htmlspecialchars($evento['descricao'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                    </div>
                    <a href="calendario.php?ics=<?= (int) $evento['id'] ?>" class="button button-outline button-small" aria-label="Adicionar <?= htmlspecialchars($evento['titulo'], ENT_QUOTES, 'UTF-8') ?> à minha agenda"><?= icone('calendar-plus') ?> Agenda</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
