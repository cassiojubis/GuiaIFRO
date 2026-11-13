<?php
/**
 * CONEXÃO COM O BANCO (Model)
 *
 * banco() devolve sempre a mesma conexão PDO durante a requisição.
 * Credenciais em config/banco.php (fora do repositório).
 * Configurada para: erro vira exceção, prepared statements de verdade
 * (sem emulação) e utf8mb4.
 */

function banco(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $arquivo = __DIR__ . '/../config/banco.php';
    if (!is_file($arquivo)) {
        bancoIndisponivel('config/banco.php não existe (copie de config/banco.exemplo.php).');
    }
    $config = require $arquivo;

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $config['host'] ?? '127.0.0.1',
        (int) ($config['porta'] ?? 3306),
        $config['banco'] ?? 'guia_ifro'
    );

    try {
        $pdo = new PDO($dsn, $config['usuario'] ?? '', $config['senha'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        // Mesmo fuso do PHP (Config.php), pra NOW() e CURRENT_TIMESTAMP baterem com date().
        $pdo->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");
    } catch (PDOException $erro) {
        bancoIndisponivel($erro->getMessage());
    }

    return $pdo;
}

/**
 * Sem banco não dá pra montar a página: mostra um aviso simples (503) em vez
 * de uma tela de erro do PHP. O detalhe técnico vai só pro log do servidor.
 */
function bancoIndisponivel(string $detalhe): void
{
    error_log('Guia IFRO: banco indisponível — ' . $detalhe);
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Banco indisponível: {$detalhe}\n");
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
        header('Retry-After: 120');
    }
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Guia IFRO fora do ar</title></head><body style="font-family:sans-serif;max-width:520px;margin:4rem auto;padding:0 1rem;line-height:1.5">'
        . '<h1>O Guia IFRO está fora do ar por alguns minutos</h1>'
        . '<p>Não conseguimos acessar os dados agora. Tente de novo daqui a pouco.</p>'
        . '<p>Se for urgente: (69) 99985-4314 · guajara-mirim@ifro.edu.br</p></body></html>';
    exit;
}

/** Executa uma consulta com parâmetros e devolve todas as linhas. */
function consultar(string $sql, array $parametros = []): array
{
    $comando = banco()->prepare($sql);
    $comando->execute($parametros);
    return $comando->fetchAll();
}

/** Uma linha só (ou null). */
function consultarUm(string $sql, array $parametros = []): ?array
{
    $comando = banco()->prepare($sql);
    $comando->execute($parametros);
    $linha = $comando->fetch();
    return $linha === false ? null : $linha;
}

/** Um valor só (ex.: COUNT(*)). */
function consultarValor(string $sql, array $parametros = [])
{
    $comando = banco()->prepare($sql);
    $comando->execute($parametros);
    return $comando->fetchColumn();
}

/** INSERT/UPDATE/DELETE. Devolve quantas linhas mudaram. */
function executar(string $sql, array $parametros = []): int
{
    $comando = banco()->prepare($sql);
    $comando->execute($parametros);
    return $comando->rowCount();
}
