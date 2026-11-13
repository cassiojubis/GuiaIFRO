<?php
require_once __DIR__ . '/../../model/Config.php';
require_once __DIR__ . '/../../model/Autenticacao.php';

// Já logado? Não faz sentido mostrar o login de novo.
if (adminEstaLogado()) {
    header('Location: dashboard.php');
    exit;
}

$tituloPagina = 'Entrar';
$erroLogin = null;
$usuarioDigitado = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $usuarioDigitado = trim((string) ($_POST['usuario'] ?? ''));

    if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
        $erroLogin = 'Sessão expirada, tente novamente.';
    } else {
        $senha = (string) ($_POST['senha'] ?? '');
        $lembrar = !empty($_POST['lembrar']);
        $resultado = tentarLoginAdmin(mb_substr($usuarioDigitado, 0, 150), $senha, $lembrar);

        if ($resultado === 'ok') {
            header('Location: dashboard.php');
            exit;
        }

        $erroLogin = $resultado === 'bloqueado'
            ? 'Muitas tentativas erradas. Espere ' . (int) (LOGIN_JANELA_SEGUNDOS / 60) . ' minutos e tente de novo.'
            : 'Usuário ou senha inválidos. Tente novamente.';
    }
}

$tokenCsrf = gerarTokenCsrf();

require __DIR__ . '/../../view/admin/login.php';
