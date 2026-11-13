<?php
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';
require_once __DIR__ . '/../model/Idiomas.php';
require_once __DIR__ . '/../model/Autenticacao.php'; // usa gerarTokenCsrf()/tokenCsrfValido()

// Processa a troca de idioma (form da própria página de acessibilidade)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['idioma'])) {
    if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $escolha = $_POST['idioma'];
    if (is_string($escolha) && idiomaPodeSerUsado($escolha)) {
        // Troca de verdade e vale para o site inteiro (o que faltar aparece em português).
        salvarIdioma($escolha);
        $_SESSION['aviso_wari'] = $escolha === 'wari';
    } elseif ($escolha === 'wari') {
        // Oro Wari ainda sem nenhum texto validado por falante (ver model/Idiomas.php):
        // mantém o idioma atual e só liga o aviso explicativo na página.
        $_SESSION['aviso_wari'] = true;
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$tituloPagina = 'Acessibilidade';
$paginaAtual = 'acessibilidade';
$tokenCsrf = gerarTokenCsrf();

require __DIR__ . '/../view/acessibilidade.php';
