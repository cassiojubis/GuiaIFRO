<?php
/**
 * PRÓXIMA AULA DA TURMA HOJE (proxima-aula.php?turma=1A) — N14 "Dá tempo?"
 * JSON pro mapa: início, sala e quantos minutos faltam.
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$turma = mb_substr((string) ($_GET['turma'] ?? ''), 0, 60);
$agora = date('H:i');
$proxima = null;
if ($turma !== '' && mb_check_encoding($turma, 'UTF-8')) {
    foreach (aulasDaTurmaNoDia($turma, (int) date('w')) as $aula) {
        if ($aula['inicio'] > $agora) {
            $proxima = $aula;
            break;
        }
    }
}
$minutos = null;
if ($proxima) {
    [$h, $m] = array_map('intval', explode(':', $proxima['inicio']));
    [$ha, $ma] = array_map('intval', explode(':', $agora));
    $minutos = ($h * 60 + $m) - ($ha * 60 + $ma);
}
header('Cache-Control: no-store');
responderJson(['ok' => true, 'agora' => $agora, 'proxima' => $proxima, 'minutos_ate' => $minutos]);
