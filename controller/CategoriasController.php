<?php
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$tituloPagina = 'Categorias';
$paginaAtual = 'categorias';
$termoInicial = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$categorias = listarCategorias();

// Busca inteligente (categorias.php?q=declaração): resultados com andar e "ver no mapa".
$resultadosBusca = [];
if (mb_strlen($termoInicial) >= 2 && mb_check_encoding($termoInicial, 'UTF-8')) {
    $termoInicial = mb_substr($termoInicial, 0, 100);
    $resultadosBusca = buscarNoGuia($termoInicial, 12);
    // I3: "só lugares sem escada" (térreo ou com elevador informado)
    $soSemEscada = !empty($_GET['acessivel']);
    if ($soSemEscada) {
        $semEscada = salasSemEscada();
        $resultadosBusca = array_values(array_filter($resultadosBusca, static function ($resultado) use ($semEscada) {
            return $resultado['sala_id'] && !empty($semEscada[$resultado['sala_id']]);
        }));
    }
}
$tags = ['Todos'];
foreach ($categorias as $categoria) {
    if (!in_array($categoria['tag'], $tags, true)) {
        $tags[] = $categoria['tag'];
    }
}

require __DIR__ . '/../view/categorias.php';
