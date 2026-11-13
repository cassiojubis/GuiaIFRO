<?php require __DIR__ . '/parciais/cabecalho.php'; ?>
<?php $e = static function ($texto): string { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); }; ?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb"><a href="index.php">Início</a><?= icone('chevron-right') ?><strong>Minha turma</strong></nav>
        <h1>Minha turma <?= $diaEscolhido === $hoje ? 'hoje' : '— ' . $e(NOMES_DIAS[$diaEscolhido]) ?></h1>
        <p>Escolha a sua turma uma vez: o Guia lembra neste aparelho.</p>
    </div>
</section>

<section class="section container">
    <?php if (!$turmas): ?>
        <p>O quadro de horários ainda não foi publicado pelo DAPE.</p>
    <?php else: ?>
        <form method="get" action="minha-turma.php" class="estou-aqui-form minha-turma-form" id="minha-turma-form">
            <label for="minha-turma-turma" class="visually-hidden">Turma</label>
            <select id="minha-turma-turma" name="turma" required>
                <option value="">Escolha sua turma…</option>
                <?php foreach ($turmas as $turma): ?>
                    <option value="<?= $e($turma) ?>" <?= $turma === $turmaEscolhida ? 'selected' : '' ?>><?= $e($turma) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="minha-turma-dia" class="visually-hidden">Dia</label>
            <select id="minha-turma-dia" name="dia">
                <?php foreach ([1, 2, 3, 4, 5, 6, 0] as $dia): ?>
                    <option value="<?= $dia ?>" <?= $dia === $diaEscolhido ? 'selected' : '' ?>><?= $e(ucfirst(NOMES_DIAS[$dia])) ?><?= $dia === $hoje ? ' (hoje)' : '' ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button button-primary">Ver aulas</button>
        </form>

        <?php if ($turmaEscolhida !== ''): ?>
            <?php if (!$aulas): ?>
                <p style="margin-top: 1.5rem;">Sem aulas para a turma <?= $e($turmaEscolhida) ?> neste dia.</p>
            <?php else: ?>
                <ol class="primeiro-dia-lista minha-turma-aulas">
                    <?php foreach ($aulas as $aula): ?>
                        <?php $agoraNaAula = $diaEscolhido === $hoje && $agora >= $aula['inicio'] && $agora < $aula['fim']; ?>
                        <li class="content-card primeiro-dia-parada <?= $agoraNaAula ? 'feita' : '' ?>" data-inicio="<?= $e($aula['inicio']) ?>" data-fim="<?= $e($aula['fim']) ?>">
                            <div class="minha-turma-hora"><strong><?= $e($aula['inicio']) ?></strong><small><?= $e($aula['fim']) ?></small></div>
                            <div class="primeiro-dia-corpo">
                                <h2><?= $e($aula['disciplina']) ?> <?= $agoraNaAula ? '<span class="primeiro-dia-selo">agora</span>' : '' ?></h2>
                                <p><?= $e(implode(' · ', array_filter([$aula['sala_texto'] ? 'Sala: ' . $aula['sala_texto'] : null, $aula['professor']]))) ?></p>
                                <?php if ($aula['sala_id']): ?>
                                    <a href="mapa.php?sala=<?= rawurlencode($aula['sala_id']) ?>" class="button button-outline button-small"><?= icone('map-pin') ?> Como chegar</a>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($versaoQuadro): ?>
            <p class="hoje-nota">Quadro publicado pelo DAPE em <?= $e((new DateTimeImmutable($versaoQuadro['publicada_em']))->format('d/m/Y')) ?>. Mudanças de última hora podem não estar aqui.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
