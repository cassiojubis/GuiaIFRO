<?php
/**
 * TELA DE LOGIN DA ÁREA ADMINISTRATIVA
 *
 * Página isolada, sem sidebar/topbar (o administrador ainda não está "logado").
 * Reaproveita as constantes e a função icone() do site público para manter a
 * mesma identidade visual (cores, fontes, ícones).
 *
 * INTEGRAÇÃO FUTURA:
 * Este formulário hoje é validado inteiramente em JavaScript (assets/js/admin.js),
 * com um usuário e senha temporários apenas para demonstração visual.
 * Quando o backend existir, substituir o envio simulado por uma requisição real
 * (ex.: POST para um endpoint PHP que valide as credenciais no banco de dados
 * e inicie uma sessão PHP verdadeira). Nada aqui deve ser considerado seguro.
 */
require_once __DIR__ . '/../componentes/config.php';

$tituloPagina = 'Entrar';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($tituloPagina) ?> · Área Administrativa | <?= NOME_SISTEMA ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsivo.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/admin-responsive.css">
</head>
<body class="admin-body admin-login-page" data-admin-page="login">

<main class="admin-login-screen">
    <div class="admin-login-card">

        <div class="admin-login-brand">
            <span class="brand-mark">IFRO</span>
            <span class="brand-copy">
                <strong><?= NOME_SISTEMA ?></strong>
                <small><?= NOME_CAMPUS ?></small>
            </span>
        </div>

        <h1 class="admin-login-title">Área Administrativa</h1>
        <p class="admin-login-subtitle">Acesso restrito à equipe responsável pela manutenção do Guia IFRO.</p>

        <!-- Aviso visual: mensagem de erro, oculta por padrão -->
        <div class="admin-form-alert admin-form-alert-error" id="login-error" role="alert" hidden>
            <?= icone('circle-alert') ?>
            <span id="login-error-texto">Usuário ou senha inválidos. Tente novamente.</span>
        </div>

        <form id="admin-login-form" novalidate>
            <div class="admin-field">
                <label for="login-usuario">E-mail ou usuário</label>
                <div class="admin-input-with-icon">
                    <?= icone('user') ?>
                    <input
                        type="text"
                        id="login-usuario"
                        name="usuario"
                        autocomplete="username"
                        placeholder="admin"
                        required
                    >
                </div>
            </div>

            <div class="admin-field">
                <label for="login-senha">Senha</label>
                <div class="admin-input-with-icon">
                    <?= icone('lock') ?>
                    <input
                        type="password"
                        id="login-senha"
                        name="senha"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        required
                    >
                    <button type="button" class="admin-toggle-senha" id="toggle-senha" aria-label="Mostrar senha" aria-pressed="false">
                        <?= icone('eye', 'icon-mostrar-senha') ?>
                        <?= icone('eye-off', 'icon-ocultar-senha') ?>
                    </button>
                </div>
            </div>

            <div class="admin-field-row">
                <label class="admin-checkbox">
                    <input type="checkbox" id="login-lembrar" name="lembrar">
                    <span>Lembrar acesso</span>
                </label>
            </div>

            <button type="submit" class="button button-primary button-full admin-login-submit" id="admin-login-submit">
                <span class="admin-login-submit-texto">Entrar</span>
            </button>
        </form>

        <p class="admin-login-aviso">
            <?= icone('shield-alert') ?>
            <span>Acesso exclusivo para administradores do Guia IFRO. Tentativas indevidas podem ser registradas futuramente pelo backend.</span>
        </p>

        <a href="../index.php" class="text-link admin-login-voltar">
            <?= icone('arrow-left') ?>
            <span>Voltar ao site público</span>
        </a>
    </div>
</main>

<script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
<script src="assets/js/admin.js"></script>
</body>
</html>
