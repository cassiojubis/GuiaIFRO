<?php
// sitemap.xml gerado na hora: páginas fixas + uma URL por setor.
// O .htaccess faz o endereço /sitemap.xml cair aqui.
require_once __DIR__ . '/model/Config.php';
require_once __DIR__ . '/model/Repositorio.php';

$base = urlBaseSite();
$paginas = [
    ['index.php', '1.0'],
    ['categorias.php', '0.8'],
    ['mapa.php', '0.8'],
    ['sobre.php', '0.6'],
    ['contato.php', '0.6'],
    ['acessibilidade.php', '0.5'],
    ['privacidade.php', '0.3'],
    ['noticias.php', '0.6'],
    ['como-faco.php', '0.7'],
    ['estrangeiros.php', '0.6'],
];
foreach (listarFluxos() as $fluxo) {
    $paginas[] = ['como-faco.php?f=' . rawurlencode($fluxo['slug']), '0.6'];
}
foreach (listarNoticias(200) as $noticia) {
    $paginas[] = ['noticia.php?slug=' . rawurlencode($noticia['slug']), '0.4'];
}
foreach (listarSetores() as $setor) {
    $paginas[] = ['setor.php?id=' . rawurlencode($setor['id']), '0.7'];
}
foreach (listarMapaSalas()['salas'] as $sala) {
    if (!$sala['url_setor']) {
        $paginas[] = [$sala['url_ficha'], '0.4'];
    }
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($paginas as [$caminho, $prioridade]) {
    echo '    <url><loc>' . htmlspecialchars($base . '/' . $caminho, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>'
        . '<priority>' . $prioridade . '</priority></url>' . "\n";
}
echo '</urlset>' . "\n";
