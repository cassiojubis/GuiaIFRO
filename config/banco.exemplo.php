<?php
/**
 * MODELO da conexão com o banco. Copie para config/banco.php e preencha.
 * O banco.php NUNCA vai para o repositório (está no .gitignore).
 *
 * Use um usuário só do Guia, com permissão apenas de SELECT/INSERT/UPDATE/DELETE
 * no banco do Guia. NUNCA use o root aqui. O bin/instalar-banco.php cria esse
 * usuário pra você (ver README).
 */

return [
    'host' => '127.0.0.1',
    'porta' => 3306,          // no XAMPP desta máquina o MariaDB está na 3307
    'banco' => 'guia_ifro',
    'usuario' => 'guia_ifro',
    'senha' => 'troque-por-uma-senha-forte',
];
