<?php
/**
 * AUTENTICAÇÃO DO PAINEL ADMINISTRATIVO (Model)
 *
 * Usuários ficam na tabela `usuarios` (senha com password_hash).
 * Perfis:
 *   - admin_geral: pode tudo;
 *   - responsavel_setor: só edita os setores ligados a ele (tabela usuario_setor).
 *
 * Proteções:
 *   - bloqueio por tentativas erradas guardado NO BANCO, por usuário+IP e por IP
 *     (antes era na sessão e bastava apagar o cookie pra tentar de novo);
 *   - session_regenerate_id no login (evita session fixation);
 *   - sessão expira por inatividade;
 *   - "sair de todos os aparelhos": cada usuário tem uma sessao_versao; mudar
 *     esse número derruba todas as sessões abertas dele.
 *
 * O primeiro admin é criado pelo bin/instalar-banco.php a partir de
 * config/segredos.php. Perdeu a senha? C:\xampp\php\php.exe bin\redefinir-senha.php
 */

require_once __DIR__ . '/Repositorio.php';

const LOGIN_MAX_ERROS_USUARIO = 5;      // por usuário+IP
const LOGIN_MAX_ERROS_IP = 20;          // por IP, qualquer usuário
const LOGIN_JANELA_SEGUNDOS = 900;      // 15 minutos
const SESSAO_INATIVIDADE_SEGUNDOS = 1800;            // 30 minutos
const SESSAO_INATIVIDADE_LEMBRAR_SEGUNDOS = 604800;  // 7 dias com "lembrar acesso"
const SENHA_TAMANHO_MINIMO = 10;
// O bcrypt (password_hash) só usa os primeiros 72 bytes: acima disso o resto seria ignorado sem aviso (BUG-03).
const SENHA_TAMANHO_MAXIMO = 72;

/* =============================================================
 * SESSÃO E USUÁRIO ATUAL
 * ============================================================= */

/**
 * Usuário logado (linha da tabela usuarios + 'setores' que ele pode editar), ou null.
 * Confere no banco a cada requisição: usuário desativado ou que clicou em
 * "sair de todos os aparelhos" perde o acesso na hora.
 */
function usuarioAtual(): ?array
{
    static $usuario = false;
    if ($usuario !== false) {
        return $usuario;
    }
    $usuario = null;

    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    $limite = !empty($_SESSION['admin_lembrar']) ? SESSAO_INATIVIDADE_LEMBRAR_SEGUNDOS : SESSAO_INATIVIDADE_SEGUNDOS;
    if (time() - (int) ($_SESSION['admin_ultimo_acesso'] ?? 0) > $limite) {
        encerrarSessaoAdmin();
        return null;
    }

    $dados = buscarUsuarioPorId((int) $_SESSION['admin_id']);
    if (!$dados || !(int) $dados['ativo'] || (int) $dados['sessao_versao'] !== (int) ($_SESSION['admin_versao'] ?? 0)) {
        encerrarSessaoAdmin();
        return null;
    }

    $_SESSION['admin_ultimo_acesso'] = time();
    $dados['setores'] = listarIdsSetoresDoUsuario((int) $dados['id']);
    $usuario = $dados;
    return $usuario;
}

function adminEstaLogado(): bool
{
    return usuarioAtual() !== null;
}

function ehAdminGeral(): bool
{
    $usuario = usuarioAtual();
    return $usuario !== null && $usuario['perfil_id'] === 'admin_geral';
}

/** O usuário logado pode editar este setor? (admin geral: todos; responsável: só os dele) */
function podeEditarSetor(string $idSetor): bool
{
    $usuario = usuarioAtual();
    if ($usuario === null) {
        return false;
    }
    return $usuario['perfil_id'] === 'admin_geral' || in_array($idSetor, $usuario['setores'], true);
}

/**
 * Chame isso no topo de TODO controller do admin (menos o de login).
 * Se não estiver logado, manda pro login e para a execução ali mesmo.
 */
function exigirLoginAdmin(): void
{
    if (!adminEstaLogado()) {
        header('Location: login.php');
        exit;
    }
    // Entrou com a senha padrão ou fraca: o painel fica preso em "Meus dados" até trocar.
    $pagina = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if (!empty($_SESSION['admin_trocar_senha']) && !in_array($pagina, ['meus-dados.php', 'logout.php'], true)) {
        header('Location: meus-dados.php?trocar=1');
        exit;
    }
}

/** Páginas só do admin geral (categorias, usuários, configurações...). */
function exigirAdminGeral(): void
{
    exigirLoginAdmin();
    if (!ehAdminGeral()) {
        http_response_code(403);
        definirFlash('admin', ['tipo' => 'erro', 'texto' => 'Seu perfil não tem acesso a essa página.']);
        header('Location: setores.php');
        exit;
    }
}

/* =============================================================
 * LOGIN
 * ============================================================= */

/**
 * Tenta autenticar. Devolve:
 *   'ok'        — entrou;
 *   'invalido'  — usuário ou senha errados;
 *   'bloqueado' — tentativas demais (espere alguns minutos).
 */
function tentarLoginAdmin(string $login, string $senha, bool $lembrar = false): string
{
    $chaveUsuario = hashDoVisitante('login|' . mb_strtolower($login));
    $chaveIp = hashDoVisitante('login-ip');

    if (limiteAtingido('login', $chaveUsuario, LOGIN_MAX_ERROS_USUARIO, LOGIN_JANELA_SEGUNDOS)
        || limiteAtingido('login-ip', $chaveIp, LOGIN_MAX_ERROS_IP, LOGIN_JANELA_SEGUNDOS)) {
        return 'bloqueado';
    }

    $usuario = $login !== '' ? buscarUsuarioPorLogin($login) : null;
    // Mesmo sem usuário, roda o password_verify num hash qualquer: assim a resposta
    // demora o mesmo tempo e ninguém descobre quais usuários existem.
    $hash = $usuario['senha_hash'] ?? '$2y$10$.4/7qDUekFZpvb/v3niuFOTY09KQUHjdCu765qzaPWP60hKIpeFSa';
    $senhaCerta = password_verify($senha, $hash);

    if (!$usuario || !$senhaCerta || !(int) $usuario['ativo']) {
        registrarNoLimite('login', $chaveUsuario);
        registrarNoLimite('login-ip', $chaveIp);
        return 'invalido';
    }

    // Login certo: zera as tentativas e troca o ID de sessão (session fixation).
    limparLimite('login', $chaveUsuario);
    session_regenerate_id(true);

    if (password_needs_rehash($usuario['senha_hash'], PASSWORD_DEFAULT)) {
        definirSenhaUsuario((int) $usuario['id'], password_hash($senha, PASSWORD_DEFAULT), false);
    }
    registrarLoginUsuario((int) $usuario['id']);

    $_SESSION['admin_id'] = (int) $usuario['id'];
    $_SESSION['admin_versao'] = (int) $usuario['sessao_versao'];
    $_SESSION['admin_ultimo_acesso'] = time();
    $_SESSION['admin_lembrar'] = $lembrar;
    // Senha fraca ou ainda a padrão (admin123): só deixa usar o painel depois de trocar.
    $_SESSION['admin_trocar_senha'] = problemaNaSenha($senha, (string) $usuario['usuario']) !== null;

    if ($lembrar) {
        // Estende o cookie de sessão pra 30 dias em vez de expirar ao fechar o navegador.
        setcookie(session_name(), session_id(), [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => conexaoSegura(),
        ]);
    }

    return 'ok';
}

function encerrarSessaoAdmin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $parametros['path'],
            'domain' => $parametros['domain'],
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => conexaoSegura(),
        ]);
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/**
 * Política mínima de senha. Devolve a mensagem de erro, ou null se está ok.
 */
function problemaNaSenha(string $senha, string $usuario = ''): ?string
{
    if (mb_strlen($senha) < SENHA_TAMANHO_MINIMO) {
        return 'A senha precisa ter pelo menos ' . SENHA_TAMANHO_MINIMO . ' caracteres.';
    }
    if (strlen($senha) > SENHA_TAMANHO_MAXIMO) {
        return 'A senha pode ter no máximo ' . SENHA_TAMANHO_MAXIMO . ' caracteres (letra com acento conta como 2).';
    }
    if (!preg_match('/\p{L}/u', $senha) || !preg_match('/\d/', $senha)) {
        return 'A senha precisa ter letras e números.';
    }
    if ($usuario !== '' && mb_stripos($senha, $usuario) !== false) {
        return 'A senha não pode conter o nome de usuário.';
    }
    $fracas = ['1234567890', 'senha12345', 'ifro123456', 'admin12345', 'guiaifro123', 'qwerty1234'];
    if (in_array(mb_strtolower($senha), $fracas, true)) {
        return 'Essa senha é muito comum. Escolha outra.';
    }
    return null;
}

/* =============================================================
 * CSRF: token que confirma que o formulário foi mesmo carregado pelo
 * nosso site, e não enviado escondido a partir de outra página.
 * ============================================================= */

function gerarTokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function tokenCsrfValido(?string $tokenRecebido): bool
{
    if (empty($_SESSION['csrf_token']) || empty($tokenRecebido)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $tokenRecebido);
}

/**
 * Para a execução com 403 se o token CSRF não bater.
 * Aceita o token do formulário (campo csrf_token) ou do cabeçalho
 * X-CSRF-Token (usado pelos fetch do admin.js, que lê a <meta name="csrf-token">).
 * Chame no começo de todo tratamento de POST.
 */
function exigirCsrf(): void
{
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (tokenCsrfValido(is_string($token) ? $token : null)) {
        return;
    }

    http_response_code(403);
    if (pedidoQuerJson() || !empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        responderJson(['ok' => false, 'erro' => 'Sessão expirada. Recarregue a página e tente de novo.'], 403);
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<p>Sessão expirada ou formulário inválido. Volte à página anterior, recarregue e tente de novo.</p>';
    exit;
}

/** Campo escondido com o token, pra colar dentro de todo <form method="post">. */
function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') . '">';
}
