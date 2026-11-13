<?php
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Autenticacao.php'; // gerarTokenCsrf()
require_once __DIR__ . '/../model/Repositorio.php';

$tituloPagina = 'Contato';
$paginaAtual = 'contato';

// Contato geral vem das Configurações do admin (tabela configuracoes).
$canaisContato = [
    ['icone' => 'phone', 'titulo' => 'Telefone', 'linha1' => 'Atendimento geral', 'destaque' => configuracao('contato_telefone', 'Não informado')],
    ['icone' => 'mail', 'titulo' => 'E-mail', 'linha1' => 'Resposta em até 2 dias úteis', 'destaque' => configuracao('contato_email', 'Não informado')],
    ['icone' => 'clock', 'titulo' => 'Horário', 'linha1' => 'Atendimento geral', 'destaque' => configuracao('contato_horario', 'Não informado')],
];
$enderecoCampus = configuracao('contato_endereco');

$assuntos = listarAssuntosContato();
$tokenCsrf = gerarTokenCsrf();

// Resultado do envio sem JavaScript (o ProcessarContatoController guarda na sessão e volta pra cá).
$resultadoContato = pegarFlash('contato');
$errosContato = $resultadoContato['erros'] ?? [];
$valoresContato = $resultadoContato['valores'] ?? [];
$contatoEnviado = !empty($resultadoContato['ok']);
// Link da página do setor ("Avisar informação errada", "Mandar dúvida") já chega com o assunto escolhido.
if (empty($valoresContato['assunto']) && in_array($_GET['assunto'] ?? '', $assuntos, true)) {
    $valoresContato['assunto'] = $_GET['assunto'];
}

require __DIR__ . '/../view/contato.php';
