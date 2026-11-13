<?php
// menu lateral do admin (no celular vira gaveta, ver admin.js).
// 'todos' = qualquer perfil; 'geral' = só o admin geral.
$naoLidasMenu = ehAdminGeral() ? contarMensagensNaoLidas() : 0;
$itensMenuAdmin = [
    ['id' => 'dashboard', 'icone' => 'layout-dashboard', 'texto' => 'Dashboard', 'url' => 'dashboard.php', 'perfil' => 'todos'],
    ['id' => 'setores', 'icone' => 'building-2', 'texto' => ehAdminGeral() ? 'Setores' : 'Meus setores', 'url' => 'setores.php', 'perfil' => 'todos'],
    ['id' => 'categorias', 'icone' => 'shapes', 'texto' => 'Categorias', 'url' => 'categorias.php', 'perfil' => 'geral'],
    ['id' => 'editor-mapa', 'icone' => 'map', 'texto' => 'Editor do mapa', 'url' => 'editor-mapa.php', 'perfil' => 'geral'],
    ['id' => 'mapa', 'icone' => 'door-open', 'texto' => 'Salas (ficha e QR)', 'url' => 'mapa.php', 'perfil' => 'geral'],
    ['id' => 'fluxos', 'icone' => 'list-checks', 'texto' => 'Como faço para…', 'url' => 'fluxos.php', 'perfil' => 'geral'],
    ['id' => 'informacoes', 'icone' => 'newspaper', 'texto' => 'Informações', 'url' => 'informacoes.php', 'perfil' => 'geral'],
    ['id' => 'contatos', 'icone' => 'phone', 'texto' => 'Contatos e mensagens', 'url' => 'contatos.php', 'perfil' => 'geral', 'contador' => $naoLidasMenu],
    ['id' => 'traducoes', 'icone' => 'languages', 'texto' => 'Traduções', 'url' => 'traducoes.php', 'perfil' => 'geral'],
    ['id' => 'usuarios', 'icone' => 'users', 'texto' => 'Usuários', 'url' => 'usuarios.php', 'perfil' => 'geral'],
    ['id' => 'configuracoes', 'icone' => 'settings', 'texto' => 'Configurações', 'url' => 'configuracoes.php', 'perfil' => 'geral'],
];
?>
<aside class="admin-sidebar" id="admin-sidebar" aria-label="Menu administrativo">
    <div class="admin-sidebar-brand">
        <span class="brand-mark">IFRO</span>
        <span class="brand-copy">
            <strong><?= NOME_SISTEMA ?></strong>
            <small><?= ehAdminGeral() ? 'Área Administrativa' : 'Responsável de setor' ?></small>
        </span>
    </div>

    <nav class="admin-sidebar-nav" aria-label="Navegação do painel">
        <ul>
            <?php foreach ($itensMenuAdmin as $item): ?>
                <?php if (($item['perfil'] === 'geral' && !ehAdminGeral()) || $item['perfil'] === 'ninguem') { continue; } ?>
                <li>
                    <a
                        href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>"
                        class="admin-sidebar-link <?= $paginaAtualAdmin === $item['id'] ? 'active' : '' ?>"
                        <?= $paginaAtualAdmin === $item['id'] ? 'aria-current="page"' : '' ?>
                    >
                        <?= icone($item['icone']) ?>
                        <span><?= htmlspecialchars($item['texto'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if (!empty($item['contador'])): ?>
                            <span class="admin-menu-contador" aria-label="<?= (int) $item['contador'] ?> não lidas"><?= (int) $item['contador'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="admin-sidebar-footer">
        <a href="meus-dados.php" class="admin-sidebar-link <?= $paginaAtualAdmin === 'meus-dados' ? 'active' : '' ?>">
            <?= icone('user-cog') ?>
            <span>Meus dados</span>
        </a>
        <a href="../index.php" target="_blank" rel="noopener noreferrer" class="admin-sidebar-link">
            <?= icone('globe') ?>
            <span>Visualizar site</span>
        </a>
        <form method="post" action="logout.php" class="admin-logout-form">
            <?= campoCsrf() ?>
            <button type="submit" class="admin-sidebar-link admin-sidebar-logout" id="admin-sidebar-logout-button">
                <?= icone('log-out') ?>
                <span>Sair</span>
            </button>
        </form>
    </div>
</aside>
