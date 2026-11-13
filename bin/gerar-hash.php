<?php
/**
 * Gera o hash de uma senha nova para o admin.
 * Uso (no terminal, na pasta do projeto):
 *   C:\xampp\php\php.exe bin\gerar-hash.php
 *
 * Depois é só colar o hash em config/segredos.php ('admin_senha_hash').
 * Só funciona pelo terminal — pelo navegador a pasta bin/ é bloqueada.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

const TAMANHO_MINIMO = 10;

function perguntar(string $texto): string
{
    echo $texto;
    $linha = fgets(STDIN);
    return $linha === false ? '' : rtrim($linha, "\r\n");
}

echo "Atenção: a senha vai aparecer na tela enquanto você digita.\n";
$senha = perguntar('Nova senha: ');

if (mb_strlen($senha) < TAMANHO_MINIMO) {
    fwrite(STDERR, 'A senha precisa ter pelo menos ' . TAMANHO_MINIMO . " caracteres.\n");
    exit(1);
}

if (perguntar('Repita a senha: ') !== $senha) {
    fwrite(STDERR, "As senhas não são iguais.\n");
    exit(1);
}

echo "\nCole isto em config/segredos.php, no campo 'admin_senha_hash':\n\n";
echo password_hash($senha, PASSWORD_DEFAULT) . "\n";
