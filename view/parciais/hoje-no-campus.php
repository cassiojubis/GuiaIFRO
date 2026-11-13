<?php
/**
 * FAIXA "HOJE NO CAMPUS" (N5) — na home e em hoje.php (que o JS chama a cada 5 min).
 * Espera $hoje = hojeNoCampus(). Funciona sem JavaScript (vem pronta do servidor).
 */
$e = static function ($texto): string { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };
?>
<div class="hoje-grade" id="hoje-no-campus-conteudo" data-atualizado="<?= $e($hoje['atualizado_em']) ?>">
    <article class="hoje-cartao">
        <span class="hoje-icone"><?= icone('calendar-days') ?></span>
        <div>
            <small>Hoje</small>
            <strong><?= $e($hoje['data']) ?></strong>
            <?php if ($hoje['situacao']): ?>
                <p class="hoje-situacao hoje-<?= $e($hoje['situacao']['tipo']) ?>"><?= $e($hoje['situacao']['texto']) ?></p>
            <?php endif; ?>
        </div>
    </article>

    <article class="hoje-cartao">
        <span class="hoje-icone"><?= icone('door-open') ?></span>
        <div>
            <small>Abertos agora (<?= $e($hoje['atualizado_em']) ?>)</small>
            <?php if ($hoje['abertos']): ?>
                <strong><?= $e(textoQuantidade(count($hoje['abertos']), 'setor aberto', 'setores abertos')) ?></strong>
                <p>
                    <?php foreach (array_slice($hoje['abertos'], 0, 5) as $indice => $setor): ?>
                        <?= $indice ? ' · ' : '' ?><a href="setor.php?id=<?= rawurlencode($setor['id']) ?>"><?= $e($setor['sigla'] ?: $setor['nome']) ?></a>
                    <?php endforeach; ?>
                    <?= count($hoje['abertos']) > 5 ? ' e mais ' . (count($hoje['abertos']) - 5) : '' ?>
                </p>
            <?php else: ?>
                <strong>Nenhum setor aberto agora</strong>
            <?php endif; ?>
            <p class="hoje-nota">Considerando os <?= (int) $hoje['setores_com_horario'] ?> setores com horário cadastrado.</p>
        </div>
    </article>

    <article class="hoje-cartao">
        <span class="hoje-icone"><?= icone('calendar-clock') ?></span>
        <div>
            <small>Próxima data especial</small>
            <?php if ($hoje['proxima_data_especial']): ?>
                <?php $especial = $hoje['proxima_data_especial']; ?>
                <strong><?= $e($especial['motivo']) ?></strong>
                <p><?= $e(formatarDataCurta($especial['data'])) ?> · <?= (int) $especial['fechado'] ? 'campus fechado'
                    : $e('horário especial: ' . substr((string) $especial['abre'], 0, 5) . ' às ' . substr((string) $especial['fecha'], 0, 5)) ?></p>
            <?php else: ?>
                <strong>Nenhum feriado ou fechamento marcado</strong>
                <p><a href="noticias.php">Ver avisos</a></p>
            <?php endif; ?>
        </div>
    </article>
</div>
