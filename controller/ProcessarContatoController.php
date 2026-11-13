<?php
/**
 * RECEBE O FORMULÁRIO DE CONTATO (processar-contato.php)
 *
 * Funciona dos dois jeitos:
 *  - com JavaScript: o js/script.js manda por fetch() e recebe JSON;
 *  - sem JavaScript: POST normal, e a gente volta pra contato.php com o resultado.
 *
 * Proteções: token CSRF, campo "isca" (honeypot) que só robô preenche,
 * limite de 3 envios por hora por pessoa (IP guardado só como hash) e
 * validação de todos os campos aqui no servidor.
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Autenticacao.php';
require_once __DIR__ . '/../model/Repositorio.php';

const CONTATO_MAX_POR_HORA = 3;
const CONTATO_MENSAGEM_MIN = 10;
const CONTATO_MENSAGEM_MAX = 3000;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: contato.php');
    exit;
}

/** Termina a requisição do jeito certo pra cada caso (JSON pro fetch, redirect pro form normal). */
function responderContato(bool $ok, string $mensagem, array $erros = [], array $valores = [], int $status = 200): void
{
    if (pedidoQuerJson()) {
        responderJson(['ok' => $ok, 'mensagem' => $mensagem, 'erros' => $erros], $status);
    }
    definirFlash('contato', ['ok' => $ok, 'mensagem' => $mensagem, 'erros' => $erros, 'valores' => $ok ? [] : $valores]);
    header('Location: contato.php#contact-form');
    exit;
}

if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
    responderContato(false, 'Sua sessão expirou. Recarregue a página e envie de novo.', [], [], 403);
}

$dados = [
    'nome' => trim((string) ($_POST['nome'] ?? '')),
    'email' => trim((string) ($_POST['email'] ?? '')),
    'telefone' => trim((string) ($_POST['telefone'] ?? '')),
    'assunto' => trim((string) ($_POST['assunto'] ?? '')),
    'mensagem' => trim((string) ($_POST['mensagem'] ?? '')),
];

// Honeypot: o campo "site" fica escondido. Gente de verdade não vê nem preenche.
// Pra robô a gente finge que deu certo e não grava nada.
if (trim((string) ($_POST['site'] ?? '')) !== '') {
    responderContato(true, 'Mensagem enviada! Obrigado pelo contato.');
}

$erros = [];
$tamanhoNome = mb_strlen($dados['nome']);
if ($tamanhoNome < 2 || $tamanhoNome > 150) {
    $erros['nome'] = 'Informe seu nome (até 150 letras).';
}
if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($dados['email']) > 150) {
    $erros['email'] = 'Informe um e-mail válido.';
}
if ($dados['telefone'] !== '' && !preg_match('/^[0-9()+\-\s]{8,20}$/', $dados['telefone'])) {
    $erros['telefone'] = 'Telefone inválido. Use só números, espaço, parênteses e traço.';
}
if (!in_array($dados['assunto'], listarAssuntosContato(), true)) {
    $erros['assunto'] = 'Escolha um assunto da lista.';
}
$tamanhoMensagem = mb_strlen($dados['mensagem']);
if ($tamanhoMensagem < CONTATO_MENSAGEM_MIN || $tamanhoMensagem > CONTATO_MENSAGEM_MAX) {
    $erros['mensagem'] = 'A mensagem precisa ter entre ' . CONTATO_MENSAGEM_MIN . ' e ' . CONTATO_MENSAGEM_MAX . ' caracteres.';
}

// Texto com codificação quebrada (não é UTF-8): avisa e não devolve o valor estragado pra tela.
foreach ($dados as $campo => $valor) {
    if (!mb_check_encoding($valor, 'UTF-8')) {
        $dados[$campo] = '';
        $erros[$campo] = 'Esse campo tem caracteres inválidos. Digite de novo.';
    }
}

if ($erros) {
    responderContato(false, 'Confira os campos marcados.', $erros, $dados, 422);
}

$chaveVisitante = hashDoVisitante();
if (limiteAtingido('contato', $chaveVisitante, CONTATO_MAX_POR_HORA, 3600)) {
    responderContato(false, 'Você já enviou ' . CONTATO_MAX_POR_HORA . ' mensagens na última hora. Tente de novo mais tarde ou ligue para o campus.', [], $dados, 429);
}

$idMensagem = salvarMensagemContato($dados);
registrarNoLimite('contato', $chaveVisitante);

// Aviso por e-mail ao setor. Se não tiver destino configurado ou o mail() falhar,
// a mensagem continua gravada e aparece em admin › Contatos › Mensagens recebidas.
$destino = configuracao('contato_email_destino');
if ($destino !== '' && filter_var($destino, FILTER_VALIDATE_EMAIL)) {
    $assuntoEmail = '[Guia IFRO] ' . $dados['assunto'];
    $corpo = "Nova mensagem pelo formulário do Guia IFRO\n\n"
        . "Nome: {$dados['nome']}\nE-mail: {$dados['email']}\nTelefone: " . ($dados['telefone'] ?: '-') . "\n"
        . "Assunto: {$dados['assunto']}\n\n{$dados['mensagem']}\n";
    // Reply-To só com e-mail já validado (sem quebra de linha, então não dá pra injetar cabeçalho).
    $cabecalhos = "Content-Type: text/plain; charset=UTF-8\r\nReply-To: {$dados['email']}\r\n";
    $enviou = @mail($destino, '=?UTF-8?B?' . base64_encode($assuntoEmail) . '?=', $corpo, $cabecalhos);
    if ($enviou) {
        marcarEmailEnviado($idMensagem);
    } else {
        error_log("Guia IFRO: mail() falhou para a mensagem de contato {$idMensagem} (ficou só no banco).");
    }
}

responderContato(true, 'Mensagem enviada! Obrigado pelo contato. Vamos responder pelo e-mail informado.');
