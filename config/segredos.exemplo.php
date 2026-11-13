<?php
/**
 * MODELO do arquivo de segredos.
 *
 * Copie este arquivo para config/segredos.php e preencha com os dados reais.
 * O segredos.php NUNCA vai para o repositório (está no .gitignore).
 *
 * Pra gerar o hash da senha, rode no terminal (na pasta do projeto):
 *   C:\xampp\php\php.exe bin\gerar-hash.php
 */

return [
    'admin_usuario' => 'troque-este-usuario',
    'admin_senha_hash' => 'cole-aqui-o-hash-gerado',
    // Sal secreto pra guardar IP só como hash (limite de envios). Gere um com:
    //   C:\xampp\php\php.exe -r "echo bin2hex(random_bytes(32));"
    'sal' => 'troque-por-um-texto-aleatorio-longo',
];
