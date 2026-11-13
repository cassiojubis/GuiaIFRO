<?php
/**
 * REDEFINE A SENHA DE UM USUÁRIO DO PAINEL (pelo terminal)
 * Serve pra quando ninguém consegue mais entrar. Também desbloqueia o usuário
 * e o desloga de todos os aparelhos.
 *
 * Uso, na pasta do projeto:
 *   C:\xampp\php\php.exe bin\redefinir-senha.php admin
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../model/Config.php';
require __DIR__ . '/../model/Autenticacao.php';

$login = $argv[1] ?? '';
if ($login === '') {
    fwrite(STDERR, "Uso: php bin\\redefinir-senha.php <usuario-ou-email>\n");
    exit(1);
}
$usuario = buscarUsuarioPorLogin($login);
if (!$usuario) {
    fwrite(STDERR, "Usuário não encontrado: {$login}\n");
    exit(1);
}

echo "Redefinindo a senha de {$usuario['nome']} ({$usuario['usuario']}).\n";
echo "Atenção: a senha vai aparecer na tela enquanto você digita.\n";
echo 'Nova senha: ';
$senha = rtrim((string) fgets(STDIN), "\r\n");
echo 'Repita a senha: ';
if (rtrim((string) fgets(STDIN), "\r\n") !== $senha) {
    fwrite(STDERR, "As senhas não são iguais.\n");
    exit(1);
}
$problema = problemaNaSenha($senha, $usuario['usuario']);
if ($problema) {
    fwrite(STDERR, $problema . "\n");
    exit(1);
}

definirSenhaUsuario((int) $usuario['id'], password_hash($senha, PASSWORD_DEFAULT));
executar('UPDATE usuario SET usua_ativo = 1 WHERE usua_id = ?', [(int) $usuario['id']]);
executar("DELETE FROM limite WHERE limi_acao IN ('login', 'login-ip')");
registrarAuditoria(null, 'editar', 'usuarios', (string) $usuario['id'], null, null, 'Senha redefinida pelo terminal');
echo "Pronto. Senha trocada, usuário ativo e bloqueios de login zerados.\n";
