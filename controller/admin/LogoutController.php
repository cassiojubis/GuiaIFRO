<?php
require_once __DIR__ . '/../../model/Config.php';
require_once __DIR__ . '/../../model/Autenticacao.php';

// Sair só por POST com token: um link ou imagem de outro site não consegue
// mais deslogar o admin (antes bastava abrir logout.php).
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

exigirCsrf();
encerrarSessaoAdmin();
header('Location: login.php');
exit;
