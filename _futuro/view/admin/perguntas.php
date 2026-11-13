<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Perguntas recebidas</h2>
        <p>Perguntas feitas na página dos setores ("Pergunte ao setor"). Ao responder, a pergunta vira item das perguntas frequentes do setor. Perguntas parecidas aparecem juntas, com o número de vezes.</p>
    </div>
</div>

<?php if (!$perguntasPendentes): ?>
    <p class="admin-empty-state"><?= icone('inbox') ?> Nenhuma pergunta esperando resposta.</p>
<?php endif; ?>

<?php foreach ($perguntasPendentes as $pergunta): ?>
    <section class="admin-panel">
        <p class="admin-campo-ajuda">
            <strong><?= htmlspecialchars($pergunta['setor_nome'], ENT_QUOTES, 'UTF-8') ?></strong> ·
            <?= (int) $pergunta['vezes'] === 1 ? 'perguntado 1 vez' : 'perguntado ' . (int) $pergunta['vezes'] . ' vezes' ?> ·
            última em <?= htmlspecialchars(dataHoraBr($pergunta['ultima_em']), ENT_QUOTES, 'UTF-8') ?>
            <?= $pergunta['email'] ? ' · e-mail de quem perguntou: ' . htmlspecialchars($pergunta['email'], ENT_QUOTES, 'UTF-8') : '' ?>
        </p>
        <form class="admin-form" method="post" novalidate>
            <?= campoCsrf() ?>
            <input type="hidden" name="acao" value="responder">
            <input type="hidden" name="id" value="<?= (int) $pergunta['id'] ?>">
            <div class="admin-field">
                <label for="pergunta-<?= (int) $pergunta['id'] ?>">Pergunta (pode corrigir antes de publicar)</label>
                <input type="text" id="pergunta-<?= (int) $pergunta['id'] ?>" name="pergunta" required maxlength="300" value="<?= htmlspecialchars(mb_substr($pergunta['pergunta'], 0, 300), ENT_QUOTES, 'UTF-8') ?>">
                <span class="admin-field-error">Informe a pergunta.</span>
            </div>
            <div class="admin-field">
                <label for="resposta-<?= (int) $pergunta['id'] ?>">Resposta</label>
                <textarea id="resposta-<?= (int) $pergunta['id'] ?>" name="resposta" rows="3" required maxlength="4000"></textarea>
                <span class="admin-field-error">Escreva a resposta.</span>
            </div>
            <div class="admin-form-footer-inline">
                <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Publicar no FAQ</span></button>
            </div>
        </form>
        <form method="post" class="admin-form-inline">
            <?= campoCsrf() ?>
            <input type="hidden" name="acao" value="descartar">
            <input type="hidden" name="id" value="<?= (int) $pergunta['id'] ?>">
            <button type="submit" class="button button-outline button-small" data-confirmar-exclusao="Descartar esta pergunta? Ela some da lista e não vai pro FAQ."><?= icone('x') ?><span>Descartar (spam ou fora do assunto)</span></button>
        </form>
    </section>
<?php endforeach; ?>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
