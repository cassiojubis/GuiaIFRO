<?php
/**
 * "MINHA TURMA HOJE" (minha-turma.php?turma=1A&dia=2) — N13
 * Sem login: o aluno escolhe a turma (o js/minha-turma.js lembra no aparelho)
 * e vê as aulas do dia com a sala e o "Como chegar".
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$tituloPagina = 'Minha turma hoje';
$paginaAtual = 'minha-turma';
$turmas = listarTurmasPublicadas();
$turmaEscolhida = (string) ($_GET['turma'] ?? '');
if (!in_array($turmaEscolhida, $turmas, true)) {
    $turmaEscolhida = '';
}
$hoje = (int) date('w');
$diaEscolhido = isset($_GET['dia']) && preg_match('/^[0-6]$/', (string) $_GET['dia']) ? (int) $_GET['dia'] : $hoje;
$aulas = $turmaEscolhida !== '' ? aulasDaTurmaNoDia($turmaEscolhida, $diaEscolhido) : [];
$agora = date('H:i');
$versaoQuadro = versaoQuadroPublicada();
$scriptsExtras = ['js/minha-turma.js'];

require __DIR__ . '/../view/minha-turma.php';
