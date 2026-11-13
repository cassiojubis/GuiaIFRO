<?php
/**
 * "PERGUNTE AO SETOR" (perguntar.php) — N10
 * A pergunta vai pro responsável do setor (admin › Perguntas). Quando ele
 * responde, vira item do FAQ daquele setor. Perguntas parecidas são juntadas.
 * Proteções: CSRF, campo-isca, até 5 perguntas por hora por pessoa (IP só como hash).
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Autenticacao.php';
require_once __DIR__ . '/../model/Repositorio.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: categorias.php');
    exit;
}

$idSetor = preg_replace('/[^a-z0-9_-]/i', '', (string) ($_POST['setor'] ?? ''));
$setor = $idSetor !== '' ? buscarSetorPorId($idSetor) : null;
if (!$setor) {
    header('Location: categorias.php');
    exit;
}
$voltar = 'setor.php?id=' . rawurlencode($idSetor) . '#pergunte-ao-setor';

$responder = static function (bool $ok, string $mensagem, string $pergunta = '') use ($voltar): void {
    definirFlash('pergunta_setor', ['ok' => $ok, 'mensagem' => $mensagem, 'pergunta' => $ok ? '' : $pergunta]);
    header('Location: ' . $voltar);
    exit;
};

$pergunta = trim((string) ($_POST['pergunta'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));

if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
    $responder(false, 'Sua sessão expirou. Recarregue a página e envie de novo.', $pergunta);
}
if (trim((string) ($_POST['site'] ?? '')) !== '') {
    $responder(true, 'Pergunta enviada! Obrigado.');
}
if (!mb_check_encoding($pergunta, 'UTF-8') || mb_strlen($pergunta) < 10 || mb_strlen($pergunta) > 500) {
    $responder(false, 'Escreva a pergunta com 10 a 500 caracteres.', mb_check_encoding($pergunta, 'UTF-8') ? $pergunta : '');
}
if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150)) {
    $responder(false, 'O e-mail não parece certo (ele é opcional).', $pergunta);
}
$chave = hashDoVisitante();
if (limiteAtingido('pergunta_setor', $chave, 5, 3600)) {
    $responder(false, 'Você já mandou várias perguntas na última hora. Tente de novo mais tarde.', $pergunta);
}

registrarPerguntaSetor($idSetor, $pergunta, $email);
registrarNoLimite('pergunta_setor', $chave);
$responder(true, 'Pergunta enviada! Quando o setor responder, ela aparece aqui nas perguntas frequentes.');
