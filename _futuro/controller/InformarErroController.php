<?php
/**
 * "INFORMAR ERRO" (informar-erro.php) — N8
 * Recebe o mini-formulário da página do setor. Vira pendência para o
 * responsável do setor e para o admin (admin › Setores).
 * Proteções: CSRF, campo-isca, até 5 envios por hora por pessoa (IP só como hash).
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Autenticacao.php';
require_once __DIR__ . '/../model/Repositorio.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: categorias.php');
    exit;
}

$idSetor = preg_replace('/[^a-z0-9_-]/i', '', (string) ($_POST['setor'] ?? ''));
$voltar = 'setor.php?id=' . rawurlencode($idSetor) . '#informar-erro-resultado';
$setor = $idSetor !== '' ? buscarSetorPorId($idSetor) : null;

/** Volta pra página do setor com o resultado (sem JavaScript também funciona). */
function voltarDoReporte(string $voltar, bool $ok, string $mensagem): void
{
    definirFlash('reporte_erro', ['ok' => $ok, 'mensagem' => $mensagem]);
    header('Location: ' . $voltar);
    exit;
}

if (!$setor) {
    header('Location: categorias.php');
    exit;
}
if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
    voltarDoReporte($voltar, false, 'Sua sessão expirou. Recarregue a página e tente de novo.');
}
if (trim((string) ($_POST['site'] ?? '')) !== '') {
    voltarDoReporte($voltar, true, 'Obrigado! Vamos conferir.'); // robô: finge que deu certo
}

$campo = (string) ($_POST['campo'] ?? '');
$correto = trim((string) ($_POST['correto'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));

if (!isset(camposReporteErro()[$campo])) {
    voltarDoReporte($voltar, false, 'Escolha qual informação está errada.');
}
if (!mb_check_encoding($correto, 'UTF-8') || mb_strlen($correto) < 3 || mb_strlen($correto) > 1000) {
    voltarDoReporte($voltar, false, 'Conte o que está certo (de 3 a 1000 caracteres).');
}
if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150)) {
    voltarDoReporte($voltar, false, 'O e-mail não parece certo. Ele é opcional: pode deixar em branco.');
}

$chave = hashDoVisitante();
if (limiteAtingido('reporte_erro', $chave, 5, 3600)) {
    voltarDoReporte($voltar, false, 'Você já enviou vários avisos na última hora. Obrigado! Tente de novo mais tarde.');
}

salvarReporteErro($idSetor, $campo, $correto, $email);
registrarNoLimite('reporte_erro', $chave);
voltarDoReporte($voltar, true, 'Obrigado! O responsável pelo setor vai conferir.');
