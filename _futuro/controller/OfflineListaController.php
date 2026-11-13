<?php
/**
 * LISTA DO QUE O APP GUARDA PRA FUNCIONAR SEM INTERNET (offline-lista.php)
 * O service worker (sw.js) lê esta lista quando é instalado e salva tudo:
 * páginas principais, página de cada setor, ficha de cada sala, CSS, JS e ícones.
 * Nada do admin e nenhum formulário entra aqui.
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$arquivos = ['css/style.css', 'css/responsivo.css', 'css/mapa-campus.css', 'js/util.js', 'js/script.js', 'js/lucide.min.js',
    'js/mapa-campus.js', 'js/vlibras.js', 'js/primeiro-dia.js', 'js/minha-turma.js', 'js/ouvir.js', 'img/logo-guiaifro.png', 'img/icones/icone-192.png'];
$paginas = ['index.php', 'categorias.php', 'mapa.php', 'sobre.php', 'contato.php', 'acessibilidade.php', 'privacidade.php', 'offline.php', 'noticias.php', 'calendario.php', 'como-faco.php', 'primeiro-dia.php', 'minha-turma.php'];
foreach (listarFluxos() as $fluxo) {
    $paginas[] = 'como-faco.php?f=' . rawurlencode($fluxo['slug']);
}
foreach (listarSetores() as $setor) {
    $paginas[] = 'setor.php?id=' . rawurlencode($setor['id']);
}
foreach (listarMapaSalas()['salas'] as $sala) {
    if (!$sala['url_setor'] && !$sala['vazia']) {
        $paginas[] = 'sala.php?id=' . rawurlencode($sala['id']);
    }
}

header('Cache-Control: no-store');
responderJson(['ok' => true, 'arquivos' => $arquivos, 'paginas' => $paginas]);
