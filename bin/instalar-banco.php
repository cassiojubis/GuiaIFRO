<?php
/**
 * INSTALA (OU ATUALIZA) O BANCO DO GUIA IFRO
 *
 * Uso no terminal, na pasta do projeto:
 *   C:\xampp\php\php.exe bin\instalar-banco.php
 *   C:\xampp\php\php.exe bin\instalar-banco.php --admin-senha=SENHA_DO_ROOT
 *   C:\xampp\php\php.exe bin\instalar-banco.php --recriar     (apaga o conteúdo e importa de novo)
 *
 * O que faz:
 *  1. Conecta com um usuário que pode criar banco (padrão: root sem senha, como no XAMPP).
 *  2. Cria o banco e o usuário do site que estão em config/banco.php, com permissão
 *     SÓ de SELECT/INSERT/UPDATE/DELETE nesse banco (o site nunca usa root).
 *  3. Cria as tabelas (sql/guiaifro.sql) e aplica as atualizações de estrutura.
 *  4. Se o banco estiver vazio (ou com --recriar), importa os dados iniciais (sql/seed.php).
 *
 * Pode rodar quantas vezes quiser: não apaga nada sem o --recriar.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

date_default_timezone_set('America/Porto_Velho');
require __DIR__ . '/../sql/seed.php';

$opcoes = getopt('', ['admin-usuario::', 'admin-senha::', 'recriar']);
$adminUsuario = $opcoes['admin-usuario'] ?? 'root';
$adminSenha = $opcoes['admin-senha'] ?? '';
$recriar = isset($opcoes['recriar']);

$arquivoConfig = __DIR__ . '/../config/banco.php';
if (!is_file($arquivoConfig)) {
    fwrite(STDERR, "Falta config/banco.php. Copie config/banco.exemplo.php, preencha e rode de novo.\n");
    exit(1);
}
$config = require $arquivoConfig;

// Nomes de banco/usuário entram direto no SQL (não dá pra usar "?" neles), então só aceitamos letras, números e _.
foreach (['banco', 'usuario'] as $campo) {
    if (!preg_match('/^[a-z0-9_]{1,32}$/i', (string) ($config[$campo] ?? ''))) {
        fwrite(STDERR, "config/banco.php: '{$campo}' só pode ter letras, números e _.\n");
        exit(1);
    }
}
if (strlen((string) ($config['senha'] ?? '')) < 12) {
    fwrite(STDERR, "config/banco.php: use uma senha com pelo menos 12 caracteres para o usuário do site.\n");
    exit(1);
}

$host = $config['host'] ?? '127.0.0.1';
$porta = (int) ($config['porta'] ?? 3306);
$nomeBanco = $config['banco'];
$usuarioSite = $config['usuario'];

echo "Conectando em {$host}:{$porta} como {$adminUsuario}...\n";
try {
    $pdo = new PDO("mysql:host={$host};port={$porta};charset=utf8mb4", $adminUsuario, $adminSenha, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $erro) {
    fwrite(STDERR, 'Não consegui conectar: ' . $erro->getMessage() . "\nUse --admin-usuario e --admin-senha.\n");
    exit(1);
}

echo "Criando o banco {$nomeBanco} (se não existir)...\n";
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$nomeBanco}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

echo "Criando o usuário {$usuarioSite} com permissões mínimas...\n";
$senhaSql = $pdo->quote($config['senha']);
try {
    foreach (['localhost', '127.0.0.1'] as $origem) {
        $pdo->exec("CREATE USER IF NOT EXISTS '{$usuarioSite}'@'{$origem}' IDENTIFIED BY {$senhaSql}");
        $pdo->exec("ALTER USER '{$usuarioSite}'@'{$origem}' IDENTIFIED BY {$senhaSql}");
        $pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON `{$nomeBanco}`.* TO '{$usuarioSite}'@'{$origem}'");
    }
    $pdo->exec('FLUSH PRIVILEGES');
} catch (PDOException $erro) {
    fwrite(STDERR, "\nNão consegui criar/dar permissão ao usuário do site: " . $erro->getMessage() . "\n");
    if (strpos($erro->getMessage(), 'wrong checksum') !== false || strpos($erro->getMessage(), 'Corrupt') !== false) {
        // Problema conhecido do XAMPP: as tabelas de permissão do MariaDB (mysql.db etc.) corrompem.
        fwrite(STDERR, "As tabelas de permissão do MariaDB estão corrompidas (problema comum do XAMPP).\n"
            . "Para consertar (faça backup antes se houver outros bancos importantes nesta máquina):\n"
            . "  C:\\xampp\\mysql\\bin\\mysqlcheck.exe -h {$host} -P {$porta} -u {$adminUsuario} -p --repair mysql db tables_priv proxies_priv\n"
            . "Depois rode este instalador de novo.\n");
    }
    exit(1);
}

$pdo->exec("USE `{$nomeBanco}`");
$pdo->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");

echo "Criando as tabelas, a view, a procedure e os triggers...\n";
foreach (comandosSql(file_get_contents(__DIR__ . '/../sql/guiaifro.sql')) as $comando) {
    $pdo->exec($comando);
}

aplicarAtualizacoesDeEstrutura($pdo, $nomeBanco);

$temDados = (int) $pdo->query('SELECT COUNT(*) FROM setor')->fetchColumn() > 0;
if ($recriar || !$temDados) {
    echo $recriar ? "Apagando o conteúdo e importando de novo...\n" : "Banco vazio: importando os dados iniciais...\n";
    $arquivoSegredos = __DIR__ . '/../config/segredos.php';
    $segredos = is_file($arquivoSegredos) ? require $arquivoSegredos : [];
    $pdo->beginTransaction();
    try {
        semearBanco($pdo, $segredos);
        $pdo->commit();
        if (!empty($GLOBALS['horariosNaoConvertidos'])) {
            echo "\nHorários que NÃO deu pra converter (o texto não diz os dias da semana).\n"
                . "Eles continuam funcionando pelo texto; cadastre os dias em admin > Setores > Horários:\n  - "
                . implode("\n  - ", $GLOBALS['horariosNaoConvertidos']) . "\n\n";
        }
    } catch (Throwable $erro) {
        $pdo->rollBack();
        fwrite(STDERR, 'Erro ao importar: ' . $erro->getMessage() . "\n");
        exit(1);
    }
} else {
    echo "O banco já tem dados: nada foi apagado (use --recriar para importar de novo).\n";
}

foreach (['categoria', 'setor', 'sala', 'faq', 'noticia', 'usuario'] as $tabela) {
    echo str_pad($tabela, 12) . ': ' . $pdo->query("SELECT COUNT(*) FROM {$tabela}")->fetchColumn() . "\n";
}
echo "\nPronto. O site já pode usar o banco.\n";

/**
 * Separa um arquivo .sql em comandos, como o cliente mysql faz:
 * tira as linhas de comentário (--), termina cada comando no ";" do fim da linha
 * e respeita o "DELIMITER $$" usado em procedure e trigger (lá dentro o ";" não
 * termina o comando, só o "$$"). A linha DELIMITER em si não vai para o banco.
 */
function comandosSql(string $sql): array
{
    $comandos = [];
    $delimitador = ';';
    $atual = '';
    foreach (preg_split('/\R/', $sql) as $linha) {
        if (preg_match('/^\s*--/', $linha)) {
            continue;
        }
        if (preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i', $linha, $m)) {
            $delimitador = $m[1];
            continue;
        }
        $atual .= $linha . "\n";
        $fim = rtrim($linha);
        if ($fim !== '' && substr($fim, -strlen($delimitador)) === $delimitador) {
            $comando = trim(substr(rtrim($atual), 0, -strlen($delimitador)));
            if ($comando !== '') {
                $comandos[] = $comando;
            }
            $atual = '';
        }
    }
    if (trim($atual) !== '') {
        $comandos[] = trim($atual);
    }
    return $comandos;
}

/**
 * Mudanças de estrutura em tabelas que já existem (coluna nova etc.).
 * Cada item só roda se ainda não foi aplicado, então é seguro rodar sempre.
 */
function aplicarAtualizacoesDeEstrutura(PDO $pdo, string $nomeBanco): void
{
    $colunaExiste = static function (string $tabela, string $coluna) use ($pdo, $nomeBanco): bool {
        $comando = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?');
        $comando->execute([$nomeBanco, $tabela, $coluna]);
        return (int) $comando->fetchColumn() > 0;
    };

    $atualizacoes = [
        // ['tabela', 'coluna', 'definição'] — adicionar aqui quando uma tabela antiga ganhar coluna nova.
        ['traducao', 'trad_pictograma', 'VARCHAR(120) NULL COMMENT "ícone do Lucide ou caminho de imagem (I6)" AFTER trad_audio_url'],
    ];
    foreach ($atualizacoes as [$tabela, $coluna, $definicao]) {
        if (!$colunaExiste($tabela, $coluna)) {
            echo "  + {$tabela}.{$coluna}\n";
            $pdo->exec("ALTER TABLE {$tabela} ADD COLUMN {$coluna} {$definicao}");
        }
    }
}
