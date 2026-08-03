<?php
/**
 * MENU LATERAL ADMINISTRATIVO
 *
 * Aparece em todas as páginas do painel. No celular, abre como uma
 * gaveta lateral controlada por assets/js/admin.js (classe .open).
 * $paginaAtualAdmin já foi definida em admin-cabecalho.php.
 */
$itensMenuAdmin = [
    ['id' => 'dashboard', 'icone' => 'layout-dashboard', 'texto' => 'Dashboard', 'url' => 'dashboard.php'],
    ['id' => 'categorias', 'icone' => 'shapes', 'texto' => 'Categorias', 'url' => 'categorias.php'],
    ['id' => 'setores', 'icone' => 'building-2', 'texto' => 'Setores', 'url' => 'setores.php'],
    ['id' => 'informacoes', 'icone' => 'newspaper', 'texto' => 'Informações', 'url' => 'informacoes.php'],
    ['id' => 'contatos', 'icone' => 'phone', 'texto' => 'Contatos', 'url' => 'contatos.php'],
    ['id' => 'configuracoes', 'icone' => 'settings', 'texto' => 'Configurações', 'url' => 'configuracoes.php'],
];
?>
<aside class="admin-sidebar" id="admin-sidebar" aria-label="Menu administrativo">
    <div class="admin-sidebar-brand">
        <span class="brand-mark">IFRO</span>
        <span class="brand-copy">
            <strong><?= NOME_SISTEMA ?></strong>
            <small>Área Administrativa</small>
        </span>
    </div>

    <nav class="admin-sidebar-nav" aria-label="Navegação do painel">
        <ul>
            <?php foreach ($itensMenuAdmin as $item): ?>
                <li>
                    <a
                        href="<?= htmlspecialchars($item['url']) ?>"
                        class="admin-sidebar-link <?= $paginaAtualAdmin === $item['id'] ? 'active' : '' ?>"
                        <?= $paginaAtualAdmin === $item['id'] ? 'aria-current="page"' : '' ?>
                    >
                        <?= icone($item['icone']) ?>
                        <span><?= htmlspecialchars($item['texto']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="admin-sidebar-footer">
        <a href="../index.php" target="_blank" rel="noopener noreferrer" class="admin-sidebar-link">
            <?= icone('globe') ?>
            <span>Visualizar site</span>
        </a>
        <button type="button" class="admin-sidebar-link admin-sidebar-logout" id="admin-sidebar-logout-button">
            <?= icone('log-out') ?>
            <span>Sair</span>
        </button>
    </div>
</aside>
