<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>
<?php
$e = static function ($texto): string { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); };

/** Lista com barrinhas proporcionais (CSS puro, sem biblioteca de gráfico). */
$barras = static function (array $linhas, callable $rotulo, string $vazio) use ($e): void {
    if (!$linhas) {
        echo '<p class="admin-campo-ajuda">' . $e($vazio) . '</p>';
        return;
    }
    $maior = max(array_map(static function ($linha) { return (int) $linha['total']; }, $linhas)) ?: 1;
    echo '<ol class="estatistica-barras">';
    foreach ($linhas as $linha) {
        $porcento = round((int) $linha['total'] * 100 / $maior);
        echo '<li><span class="estatistica-rotulo">' . $e($rotulo($linha)) . '</span>'
            . '<span class="estatistica-barra" aria-hidden="true"><span style="width:' . $porcento . '%"></span></span>'
            . '<span class="estatistica-numero">' . (int) $linha['total'] . '</span></li>';
    }
    echo '</ol>';
};
?>

<div class="admin-page-header">
    <div>
        <h2>Estatísticas</h2>
        <p>Só contadores por dia: o Guia não guarda IP, cookie nem nada que identifique quem acessou.</p>
    </div>
    <div class="admin-row-actions">
        <form method="get" class="admin-form-inline">
            <label for="periodo" class="visually-hidden">Período</label>
            <select id="periodo" name="dias" class="admin-select-filtro" data-enviar-ao-mudar>
                <?php foreach ($periodos as $valor => $rotulo): ?>
                    <option value="<?= $valor ?>" <?= $valor === $dias ? 'selected' : '' ?>><?= $e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="button button-outline button-small">Ver</button></noscript>
        </form>
        <a href="estatisticas.php?dias=<?= $dias ?>&amp;csv=1" class="button button-outline"><?= icone('download') ?><span>Baixar CSV</span></a>
    </div>
</div>

<section class="admin-panel">
    <h2 class="admin-panel-title"><?= icone('chart-column') ?> Acessos por dia (<?= (int) $totalAcessos ?> no período)</h2>
    <?php
    $maiorDia = max($acessosPorDia) ?: 1;
    $larguraBarra = 100 / count($acessosPorDia);
    ?>
    <svg class="estatistica-grafico" viewBox="0 0 100 40" preserveAspectRatio="none" role="img"
        aria-label="Gráfico de acessos por dia. Maior dia: <?= (int) max($acessosPorDia) ?> acessos.">
        <?php $i = 0; foreach ($acessosPorDia as $dia => $total): ?>
            <?php $altura = $total * 36 / $maiorDia; ?>
            <rect x="<?= round($i * $larguraBarra + $larguraBarra * 0.15, 3) ?>" y="<?= round(38 - $altura, 3) ?>"
                width="<?= round($larguraBarra * 0.7, 3) ?>" height="<?= round(max($altura, 0.2), 3) ?>" rx="0.3">
                <title><?= $e((new DateTimeImmutable($dia))->format('d/m') . ': ' . $total . ' acessos') ?></title>
            </rect>
        <?php $i++; endforeach; ?>
    </svg>
    <p class="admin-campo-ajuda"><?= $e((new DateTimeImmutable(array_key_first($acessosPorDia)))->format('d/m')) ?> até hoje. Passe o mouse numa barra para ver o número.</p>
</section>

<div class="admin-dashboard-columns">
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('building-2') ?> Setores mais acessados</h2>
        <?php $barras($setoresMais, static function ($linha) use ($nomesSetores) { return $nomesSetores[$linha['chave']] ?? $linha['chave']; }, 'Ainda sem acessos no período.'); ?>
    </section>
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('file') ?> Páginas mais acessadas</h2>
        <?php $barras($paginasMais, static function ($linha) use ($nomesPaginas) { return $nomesPaginas[$linha['chave']] ?? $linha['chave']; }, 'Ainda sem acessos no período.'); ?>
    </section>
</div>

<div class="admin-dashboard-columns">
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('search') ?> Buscas mais feitas</h2>
        <?php $barras($buscasMais, static function ($linha) { return '“' . $linha['chave'] . '”'; }, 'Nenhuma busca no período.'); ?>
    </section>
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('search-x') ?> Buscas SEM resultado</h2>
        <p class="admin-campo-ajuda">O que as pessoas procuram e não acham. Bom pra criar conteúdo ou cadastrar como "palavra de busca" no setor certo.</p>
        <?php $barras($buscasSemResultado, static function ($linha) { return '“' . $linha['chave'] . '”'; }, 'Nenhuma busca sem resultado. 🎉'); ?>
    </section>
</div>

<div class="admin-dashboard-columns">
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('route') ?> Rotas mais pedidas no mapa</h2>
        <?php $barras($rotasMais, static function ($linha) { return $linha['rotulo']; }, 'Nenhuma rota pedida no período.'); ?>
    </section>
    <section class="admin-panel">
        <h2 class="admin-panel-title"><?= icone('accessibility') ?> Acessibilidade e idioma</h2>
        <?php $barras($acessibilidade, static function ($linha) use ($nomesAcessibilidade) { return $nomesAcessibilidade[$linha['chave']] ?? $linha['chave']; }, 'Ninguém mudou fonte ou tema no período.'); ?>
        <?php $barras($idiomas, static function ($linha) { return 'Idioma escolhido: ' . (['pt' => 'Português', 'es' => 'Espanhol', 'wari' => 'Oro Wari'][$linha['chave']] ?? $linha['chave']); }, 'Ninguém trocou o idioma no período.'); ?>
    </section>
</div>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
