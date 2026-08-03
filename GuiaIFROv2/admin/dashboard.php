<?php
/**
 * DASHBOARD ADMINISTRATIVO
 *
 * INTEGRAÇÃO FUTURA:
 * Os números dos cards e a lista de alterações recentes são simulados.
 * Substituir pelos totais reais consultados no banco de dados quando o
 * backend estiver disponível.
 */
$tituloPaginaAdmin = 'Dashboard';
$paginaAtualAdmin = 'dashboard';
require __DIR__ . '/includes/admin-cabecalho.php';

$estatisticasAdmin = [
    ['icone' => 'shapes', 'valor' => 12, 'rotulo' => 'Categorias cadastradas', 'cor' => 'green'],
    ['icone' => 'building-2', 'valor' => 34, 'rotulo' => 'Setores cadastrados', 'cor' => 'blue'],
    ['icone' => 'newspaper', 'valor' => 8, 'rotulo' => 'Informações publicadas', 'cor' => 'amber'],
    ['icone' => 'phone', 'valor' => 15, 'rotulo' => 'Contatos cadastrados', 'cor' => 'purple'],
];

$atalhosAdmin = [
    ['icone' => 'plus', 'texto' => 'Adicionar categoria', 'url' => 'categorias.php?novo=1'],
    ['icone' => 'plus', 'texto' => 'Adicionar setor', 'url' => 'setores.php?novo=1'],
    ['icone' => 'pencil', 'texto' => 'Editar contato', 'url' => 'contatos.php'],
    ['icone' => 'refresh-cw', 'texto' => 'Atualizar informações', 'url' => 'informacoes.php'],
    ['icone' => 'globe', 'texto' => 'Visualizar site público', 'url' => '../index.php', 'externo' => true],
];

$alteracoesRecentesAdmin = [
    ['texto' => 'Categoria "Biblioteca" foi atualizada', 'autor' => 'Admin', 'quando' => 'há 2 horas', 'icone' => 'shapes'],
    ['texto' => 'Novo setor "Lab. de Física" foi cadastrado', 'autor' => 'Admin', 'quando' => 'há 5 horas', 'icone' => 'building-2'],
    ['texto' => 'Aviso "Calendário acadêmico 2026/2" foi publicado', 'autor' => 'Admin', 'quando' => 'ontem', 'icone' => 'newspaper'],
    ['texto' => 'Telefone do setor Biblioteca foi corrigido', 'autor' => 'Admin', 'quando' => 'ontem', 'icone' => 'phone'],
    ['texto' => 'Categoria "Extensão" foi desativada temporariamente', 'autor' => 'Admin', 'quando' => 'há 3 dias', 'icone' => 'shapes'],
];
?>

<div class="admin-page-header">
    <div>
        <h2>Visão geral</h2>
        <p>Resumo das informações administradas no Guia IFRO.</p>
    </div>
</div>

<!-- INTEGRAÇÃO FUTURA: substituir os cards abaixo por dados reais do banco -->
<section class="admin-stats-grid" aria-label="Estatísticas gerais">
    <?php foreach ($estatisticasAdmin as $item): ?>
        <article class="admin-stat-card color-solid-<?= htmlspecialchars($item['cor']) ?>">
            <span class="admin-stat-icon"><?= icone($item['icone']) ?></span>
            <strong><?= htmlspecialchars((string) $item['valor']) ?></strong>
            <span><?= htmlspecialchars($item['rotulo']) ?></span>
        </article>
    <?php endforeach; ?>
</section>

<div class="admin-dashboard-columns">
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('zap') ?> Atalhos rápidos</h2>
        <div class="admin-shortcuts-grid">
            <?php foreach ($atalhosAdmin as $atalho): ?>
                <a
                    href="<?= htmlspecialchars($atalho['url']) ?>"
                    class="admin-shortcut-card"
                    <?= !empty($atalho['externo']) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                >
                    <span><?= icone($atalho['icone']) ?></span>
                    <span><?= htmlspecialchars($atalho['texto']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('history') ?> Alterações recentes</h2>
        <ul class="admin-activity-list">
            <?php foreach ($alteracoesRecentesAdmin as $alteracao): ?>
                <li>
                    <span class="admin-activity-icon"><?= icone($alteracao['icone']) ?></span>
                    <span class="admin-activity-texto">
                        <?= htmlspecialchars($alteracao['texto']) ?>
                        <small><?= htmlspecialchars($alteracao['autor']) ?> · <?= htmlspecialchars($alteracao['quando']) ?></small>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>

<div class="admin-integration-note">
    <?= icone('server-cog') ?>
    <p>Este painel ainda não está conectado a um banco de dados. Os números, atalhos e alterações acima são simulados apenas para demonstrar o layout e o funcionamento visual da área administrativa.</p>
</div>

<?php require __DIR__ . '/includes/admin-rodape.php'; ?>
