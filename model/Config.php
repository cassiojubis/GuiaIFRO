<?php
// Configs e helpers usados em todas as páginas.

/**
 * Erros de PHP não aparecem mais na tela (isso vazaria caminho de
 * arquivo e estrutura interna pra quem visita o site). Em vez disso,
 * ficam registrados num arquivo de log, só pra quem tem acesso ao
 * servidor.
 */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Fuso do campus. Sem isso o PHP usa o fuso do servidor (o XAMPP vem com
// Europe/Berlin) e o selo "Aberto agora" erra por várias horas.
date_default_timezone_set('America/Porto_Velho');

/** O site está sendo acessado por HTTPS? (no XAMPP local normalmente não) */
function conexaoSegura(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

/**
 * Número aleatório desta requisição, usado na Content-Security-Policy.
 * Só os <script> inline que tiverem nonce="<?= cspNonce() ?>" rodam; um script
 * injetado por alguém (XSS) não tem esse número e o navegador bloqueia.
 */
function cspNonce(): string
{
    static $nonce = null;
    if ($nonce === null) {
        $nonce = base64_encode(random_bytes(16));
    }
    return $nonce;
}

/**
 * Cabeçalhos de segurança mandados em toda página (site e admin).
 * A CSP libera só o que o site usa de fora: Google Fonts, VLibras (governo
 * federal) e o jsDelivr (three.js do mapa 3D). O VLibras roda em Unity/WebGL,
 * por isso precisa de 'unsafe-eval' e de blob: — sem isso o tradutor não abre.
 */
function enviarCabecalhosSeguranca(): void
{
    if (headers_sent()) {
        return;
    }
    // O VLibras também puxa imagens e arquivos do jsDelivr (cdn.jsdelivr.net/gh/spbgovbr-vlibras).
    $vlibras = 'https://vlibras.gov.br https://*.vlibras.gov.br https://cdn.jsdelivr.net';
    $politica = [
        "default-src 'self'",
        "script-src 'self' 'nonce-" . cspNonce() . "' 'unsafe-eval' 'wasm-unsafe-eval' {$vlibras}",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com {$vlibras}",
        "font-src 'self' data: https://fonts.gstatic.com {$vlibras}",
        "img-src 'self' data: blob: {$vlibras}",
        "media-src 'self' blob: {$vlibras}",
        "connect-src 'self' {$vlibras}",
        "worker-src 'self' blob:",
        "frame-src 'self' {$vlibras}",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
    ];
    header('Content-Security-Policy: ' . implode('; ', $politica));
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
enviarCabecalhosSeguranca();

if (session_status() === PHP_SESSION_NONE) {
    // Cookie de sessão mais seguro: JS não consegue ler (httponly) e não
    // é enviado em requisições vindas de outros sites (samesite=Lax).
    // 'secure' só é ligado quando o site já estiver em HTTPS — em HTTP
    // (como no XAMPP local) ligar isso quebraria o login.
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => conexaoSegura(),
    ]);
    session_start(); // guarda o idioma escolhido e o login do admin
}

define('NOME_SISTEMA', 'Guia IFRO');
define('NOME_CAMPUS', 'Campus Guajará-Mirim');
define('ANO_ATUAL', date('Y'));

// Endereço público do site, usado no sitemap.xml e no robots.txt.
// TODO: preencher com o domínio real quando o site for publicado (ex.: 'https://guia.guajara.ifro.edu.br').
// Vazio = usa o endereço por onde a página foi acessada (bom pro XAMPP local).
define('URL_SITE_CONFIGURADA', '');

/** Endereço base do site, sem a barra no final (ex.: http://localhost/guiaIFRO). */
function urlBaseSite(): string
{
    if (URL_SITE_CONFIGURADA !== '') {
        return rtrim(URL_SITE_CONFIGURADA, '/');
    }
    $host = preg_replace('/[^a-z0-9.:-]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $pasta = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return (conexaoSegura() ? 'https' : 'http') . '://' . $host . $pasta;
}

// classe "active" no link do menu quando é a página atual
function linkAtivo(string $pagina, string $paginaAtual): string
{
    return $pagina === $paginaAtual ? 'active' : '';
}

// ícone do Lucide (o script.js troca essa tag pelo SVG)
function icone(string $nome, string $classe = ''): string
{
    // Desenho de cada ícone (Lucide 0.468, o mesmo do js/lucide.min.js), gerado já no
    // servidor: o site não precisa baixar os 349 KB do lucide.min.js em toda página.
    static $icones = null;
    if ($icones === null) {
        $icones = json_decode((string) @file_get_contents(__DIR__ . '/icones-lucide.json'), true) ?: [];
    }
    // "check-circle-2" -> "CheckCircle2" (mesma regra do Lucide)
    $chave = str_replace(' ', '', ucwords(strtolower(preg_replace('/[^a-z0-9]+/i', ' ', $nome))));
    $desenho = $icones[$chave] ?? ($icones['Circle'] ?? '');
    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $classeSegura = htmlspecialchars($classe, ENT_QUOTES, 'UTF-8');

    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
        . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" '
        . "class=\"lucide lucide-{$nomeSeguro} icon {$classeSegura}\">{$desenho}</svg>";
}


/** "1 sala" / "3 salas": escolhe singular ou plural pelo número. */
function textoQuantidade(int $quantidade, string $singular, string $plural): string
{
    return $quantidade . ' ' . ($quantidade === 1 ? $singular : $plural);
}

/**
 * Lê um valor de config/segredos.php (fora do repositório).
 * Devolve '' quando não existe.
 */
function segredo(string $chave): string
{
    static $segredos = null;
    if ($segredos === null) {
        $arquivo = __DIR__ . '/../config/segredos.php';
        $segredos = is_file($arquivo) ? (array) require $arquivo : [];
    }
    return (string) ($segredos[$chave] ?? '');
}

/**
 * Hash do IP de quem está acessando (com o sal secreto). Serve pra contar
 * envios por pessoa sem guardar o IP de verdade em lugar nenhum.
 */
function hashDoVisitante(string $complemento = ''): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    return hash('sha256', segredo('sal') . '|' . $ip . '|' . $complemento);
}

/** A requisição veio do fetch() do nosso JS pedindo JSON? */
function pedidoQuerJson(): bool
{
    return stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
}

/** Responde JSON e para. */
function responderJson(array $dados, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    exit;
}

/** Mensagem de uma vez só ("flash"): guarda na sessão pra próxima página mostrar. */
function definirFlash(string $chave, $valor): void
{
    $_SESSION['flash'][$chave] = $valor;
}

function pegarFlash(string $chave, $padrao = null)
{
    $valor = $_SESSION['flash'][$chave] ?? $padrao;
    unset($_SESSION['flash'][$chave]);
    return $valor;
}

/**
 * Erro inesperado (ex.: banco caiu no meio da página): registra no log e mostra
 * uma mensagem simples, sem caminho de arquivo nem SQL na tela.
 */
set_exception_handler(static function (Throwable $erro): void {
    error_log('Guia IFRO: ' . get_class($erro) . ': ' . $erro->getMessage() . ' em ' . $erro->getFile() . ':' . $erro->getLine());
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $erro->getMessage() . "\n");
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (pedidoQuerJson()) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['ok' => false, 'mensagem' => 'Erro inesperado no servidor. Tente de novo em alguns minutos.']);
        return;
    }
    echo '<p style="font-family:sans-serif;max-width:520px;margin:3rem auto">Aconteceu um erro inesperado. '
        . 'Tente de novo em alguns minutos. Se continuar, avise o campus pelo telefone (69) 99985-4314.</p>';
});

/**
 * "Calendário Acadêmico 2026/2" -> "calendario-academico-2026-2" (endereços e ids).
 * Não usa iconv: no Windows ele troca "á" por "'a" e o resultado fica estranho.
 */
function criarSlugTexto(string $texto, int $tamanhoMaximo = 150): string
{
    $texto = mb_strtolower($texto, 'UTF-8');
    $texto = strtr($texto, [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'é' => 'e', 'ê' => 'e', 'è' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n',
    ]);
    $slug = trim(preg_replace('/[^a-z0-9]+/', '-', $texto), '-');
    return substr($slug, 0, $tamanhoMaximo) ?: 'item';
}
