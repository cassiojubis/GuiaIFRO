<?php
/**
 * BASE DE TODOS OS CONTROLLERS DO ADMIN
 *
 * Carrega o necessário e traz os ajudantes usados nas telas:
 *  - ler e validar campos do POST (sempre no servidor);
 *  - voltar pra página com um aviso (padrão POST -> redirect -> GET,
 *    assim recarregar a página não envia o formulário de novo);
 *  - reabrir o formulário com o que a pessoa digitou quando der erro.
 */
require_once __DIR__ . '/../../model/Config.php';
require_once __DIR__ . '/../../model/Autenticacao.php';
require_once __DIR__ . '/../../model/Repositorio.php';

/** Cores que existem no CSS (color-solid-*, color-*). */
const CORES_ADMIN = [
    'slate' => 'Cinza-azulado', 'green' => 'Verde', 'blue' => 'Azul', 'amber' => 'Âmbar',
    'rose' => 'Rosa', 'purple' => 'Roxo', 'teal' => 'Verde-azulado',
];

function ehPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Volta pra página com um aviso (toast). $reabrir = formulário pra abrir de novo
 * com os valores e erros (quando a validação falhou).
 */
function voltarComAviso(string $pagina, string $texto, string $tipo = 'sucesso', ?array $reabrir = null): void
{
    definirFlash('admin', ['tipo' => $tipo, 'texto' => $texto]);
    if ($reabrir !== null) {
        definirFlash('reabrir', $reabrir);
    }
    header('Location: ' . $pagina);
    exit;
}

/**
 * Lê um texto do POST: tira espaços das pontas e confere tamanho e se é obrigatório.
 * O erro vai pra $erros[$campo]. Devolve '' quando inválido.
 */
function lerCampo(string $campo, string $rotulo, int $maximo, bool $obrigatorio, array &$erros): string
{
    $valor = $_POST[$campo] ?? '';
    $valor = is_string($valor) ? trim($valor) : '';
    if (!mb_check_encoding($valor, 'UTF-8')) {
        $erros[$campo] = "{$rotulo}: caracteres inválidos.";
        return '';
    }
    if ($obrigatorio && $valor === '') {
        $erros[$campo] = "Informe {$rotulo}.";
    } elseif (mb_strlen($valor) > $maximo) {
        $erros[$campo] = ucfirst($rotulo) . " pode ter no máximo {$maximo} caracteres.";
    }
    return $valor;
}

/** Lista do POST (campo[]), sem itens vazios nem repetidos. */
function lerLista(string $campo, int $maximoItens, int $tamanhoItem): array
{
    $valores = $_POST[$campo] ?? [];
    if (!is_array($valores)) {
        return [];
    }
    $lista = [];
    foreach ($valores as $valor) {
        $valor = is_string($valor) ? trim($valor) : '';
        if ($valor !== '' && mb_check_encoding($valor, 'UTF-8')) {
            $lista[] = mb_substr($valor, 0, $tamanhoItem);
        }
    }
    return array_slice(array_values(array_unique($lista)), 0, $maximoItens);
}

function lerCheckbox(string $campo): bool
{
    return !empty($_POST[$campo]);
}

/** Nome de ícone do Lucide: só letras minúsculas, números e hífen. */
function iconeValido(string $icone): bool
{
    return (bool) preg_match('/^[a-z0-9-]{2,40}$/', $icone);
}

/** Data no formato do <input type="date"> (AAAA-MM-DD) ou ''. */
function lerData(string $campo, string $rotulo, bool $obrigatorio, array &$erros): string
{
    $valor = trim((string) ($_POST[$campo] ?? ''));
    if ($valor === '') {
        if ($obrigatorio) {
            $erros[$campo] = "Informe {$rotulo}.";
        }
        return '';
    }
    $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
    if (!$data || $data->format('Y-m-d') !== $valor) {
        $erros[$campo] = ucfirst($rotulo) . ' inválida.';
        return '';
    }
    return $valor;
}

/** Data e hora do <input type="datetime-local"> -> 'AAAA-MM-DD HH:MM:00' ou ''. */
function lerDataHora(string $campo, string $rotulo, array &$erros): string
{
    $valor = trim((string) ($_POST[$campo] ?? ''));
    if ($valor === '') {
        return '';
    }
    $data = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $valor);
    if (!$data) {
        $erros[$campo] = ucfirst($rotulo) . ' inválida.';
        return '';
    }
    return $data->format('Y-m-d H:i:00');
}

/** Ação pedida no POST, só se estiver na lista de ações aceitas. */
function acaoPedida(array $aceitas): string
{
    $acao = (string) ($_POST['acao'] ?? '');
    return in_array($acao, $aceitas, true) ? $acao : '';
}

// Toda página do admin precisa disso.
exigirLoginAdmin();
$usuarioAdmin = usuarioAtual();

/** Valores pro data-valores do botão "editar" (JSON já escapado pra ficar dentro do atributo). */
function dadosParaModal(array $valores): string
{
    return htmlspecialchars(json_encode($valores, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
}

/** "2026-10-07 13:20:00" -> "07/10/2026 13:20" */
function dataHoraBr(?string $valor, bool $comHora = true): string
{
    if (!$valor) {
        return '—';
    }
    $momento = new DateTimeImmutable($valor);
    return $momento->format($comHora ? 'd/m/Y H:i' : 'd/m/Y');
}
