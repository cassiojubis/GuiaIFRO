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

        <?php if ($erroLogin): ?>
            <div class="admin-form-alert admin-form-alert-error" role="alert">
                <?= icone('circle-alert') ?>
                <span><?= htmlspecialchars($erroLogin) ?></span>
            </div>
        <?php endif; ?>

        <form id="admin-login-form" method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenCsrf) ?>">
            <div class="admin-field">
                <label for="login-usuario">E-mail ou usuário</label>
                <div class="admin-input-with-icon">
                    <?= icone('user') ?>
                    <input
                        type="text"
                        id="login-usuario"
                        name="usuario"
                        autocomplete="username"
                        placeholder="seu usuário ou e-mail"
                        value="<?= htmlspecialchars($usuarioDigitado, ENT_QUOTES, 'UTF-8') ?>"
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
            <span>Acesso exclusivo para a equipe do Guia IFRO e responsáveis de setor. Depois de várias tentativas erradas o acesso fica bloqueado por alguns minutos.</span>
        </p>

        <a href="../index.php" class="text-link admin-login-voltar">
            <?= icone('arrow-left') ?>
            <span>Voltar ao site público</span>
        </a>
    </div>
</main>

<script src="../js/lucide.min.js"></script>
<script src="../js/util.js"></script>
<script src="assets/js/admin.js"></script>
</body>
</html>
