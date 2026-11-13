<?php
/**
 * Testes do grafo e da rota do mapa (js/grafo.js).
 * O Dijkstra é JavaScript, então este script abre o tests/grafo_test.html
 * no Chrome (ou Edge) sem janela e mostra o resultado aqui no terminal.
 *
 * Rodar no terminal, na pasta do projeto:
 *   C:\xampp\php\php.exe tests\grafo_test.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$navegadores = [
    'C:\Program Files\Google\Chrome\Application\chrome.exe',
    'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
    'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
    'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
];
$navegador = null;
foreach ($navegadores as $caminho) {
    if (is_file($caminho)) {
        $navegador = $caminho;
        break;
    }
}
if ($navegador === null) {
    fwrite(STDERR, "Não achei Chrome nem Edge. Abra tests/grafo_test.html direto no navegador.\n");
    exit(1);
}

$pagina = 'file:///' . str_replace('\\', '/', realpath(__DIR__ . '/grafo_test.html'));
$perfil = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'guiaifro-teste-grafo';
$comando = '"' . $navegador . '" --headless=new --disable-gpu --no-first-run --allow-file-access-from-files'
    . ' --user-data-dir=' . escapeshellarg($perfil) . ' --dump-dom ' . escapeshellarg($pagina) . ' 2>NUL';
$html = (string) shell_exec($comando);

if (!preg_match('#<pre id="resultado" data-falhas="(\d+)">(.*?)</pre>#s', $html, $m)) {
    fwrite(STDERR, "O navegador não devolveu o resultado dos testes.\n");
    exit(1);
}
echo html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8') . "\n";
exit((int) $m[1] === 0 ? 0 : 1);
