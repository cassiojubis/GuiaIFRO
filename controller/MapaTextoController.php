<?php
/**
 * MAPA EM TEXTO (mapa-texto.php) — I2
 * Todas as salas por andar e ala, com o caminho em frases, sem desenho.
 * Gerado no servidor: funciona sem JavaScript e é lido pelo leitor de tela.
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';
require_once __DIR__ . '/../model/PlantaSvg.php';

$tituloPagina = 'Mapa em texto';
$paginaAtual = 'mapa';
$mapa = listarMapaSalas();

$grupos = [];
foreach ($mapa['andares'] as $idAndar => $andar) {
    $posicoes = calcularPlanta($mapa['salas'], $idAndar);
    foreach (['esquerda', 'direita'] as $ala) {
        $salasDaAla = array_values(array_filter($mapa['salas'], static function ($sala) use ($idAndar, $ala) {
            return $sala['andar'] === $idAndar && $sala['ala'] === $ala && !$sala['vazia'];
        }));
        usort($salasDaAla, static function ($a, $b) { return $a['ordem'] <=> $b['ordem']; });
        foreach ($salasDaAla as &$sala) {
            $sala['passos'] = passosEmTexto($sala, $posicoes);
        }
        unset($sala);
        if ($salasDaAla) {
            $grupos[] = ['titulo' => $andar['nome'] . ', ala ' . $ala, 'salas' => $salasDaAla];
        }
    }
}

require __DIR__ . '/../view/mapa-texto.php';
