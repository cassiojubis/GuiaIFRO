<?php
/**
 * BUSCA AO VIVO (busca.php?q=...) — devolve JSON pro js/script.js.
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$termo = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
if (!mb_check_encoding($termo, 'UTF-8')) {
    $termo = '';
}

$resultados = mb_strlen($termo) >= 2 ? buscarNoGuia($termo, 8) : [];

header('Cache-Control: no-store');
responderJson([
    'ok' => true,
    'termo' => $termo,
    'resultados' => array_map(static function ($resultado) {
        unset($resultado['pontos']);
        return $resultado;
    }, $resultados),
]);
