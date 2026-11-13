<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Olá, <?= htmlspecialchars(explode(' ', $usuarioAdmin['nome'])[0], ENT_QUOTES, 'UTF-8') ?></h2>
        <p>Resumo do que está publicado no Guia IFRO.</p>
    </div>
</div>

<section class="admin-stats-grid" aria-label="Números do sistema">
    <?php foreach ($estatisticasAdmin as $item): ?>
        <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>" class="admin-stat-card color-solid-<?= htmlspecialchars($item['cor'], ENT_QUOTES, 'UTF-8') ?>">
            <span class="admin-stat-icon"><?= icone($item['icone']) ?></span>
            <strong><?= (int) $item['valor'] ?></strong>
            <span><?= htmlspecialchars($item['rotulo'], ENT_QUOTES, 'UTF-8') ?></span>
        </a>
    <?php endforeach; ?>
</section>

<div class="admin-dashboard-columns">
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('zap') ?> Atalhos rápidos</h2>
        <div class="admin-shortcuts-grid">
            <?php foreach ($atalhosAdmin as $atalho): ?>
                <a href="<?= htmlspecialchars($atalho['url'], ENT_QUOTES, 'UTF-8') ?>" class="admin-shortcut-card"
                    <?= !empty($atalho['externo']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                    <span><?= icone($atalho['icone']) ?></span>
                    <span><?= htmlspecialchars($atalho['texto'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('history') ?> Alterações recentes</h2>
        <?php if ($alteracoesRecentesAdmin): ?>
            <ul class="admin-activity-list">
                <?php foreach ($alteracoesRecentesAdmin as $alteracao): ?>
                    <li>
                        <span class="admin-activity-icon"><?= icone($iconesAcao[$alteracao['acao']] ?? 'history') ?></span>
                        <span class="admin-activity-texto">
                            <?= htmlspecialchars((string) $alteracao['resumo'], ENT_QUOTES, 'UTF-8') ?>
                            <small><?= htmlspecialchars($alteracao['usuario_nome'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars(tempoRelativo($alteracao['quando']), ENT_QUOTES, 'UTF-8') ?></small>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="admin-campo-ajuda">Nenhuma alteração ainda.</p>
        <?php endif; ?>
    </section>
</div>

<?php if ($setoresSemDadosAdmin): ?>
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('clipboard-list') ?> Setores sem informações (<?= count($setoresSemDadosAdmin) ?>)</h2>
        <p class="admin-campo-ajuda">Estes setores têm página no site, mas ainda aparecem como "informações em breve". Preencha contato, horário ou localização, ou ligue um responsável para ele mesmo preencher.</p>
        <ul class="admin-lista-simples">
            <?php foreach ($setoresSemDadosAdmin as $setor): ?>
                <li>
                    <span><strong><?= htmlspecialchars($setor['sigla'] ?: $setor['nome'], ENT_QUOTES, 'UTF-8') ?></strong> — <?= htmlspecialchars($setor['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                    <small><?= htmlspecialchars((string) $setor['categoria_nome'], ENT_QUOTES, 'UTF-8') ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
