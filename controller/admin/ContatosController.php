<?php
require_once __DIR__ . '/BaseAdmin.php';
exigirAdminGeral();

$tituloPaginaAdmin = 'Contatos e mensagens';
$paginaAtualAdmin = 'contatos';

// Campos do contato geral (tabela configuracoes). Aparecem no rodapé, na home e em Contato.
$camposContatoGeral = [
    'contato_telefone' => ['Telefone principal', 60, 'tel'],
    'contato_email' => ['E-mail institucional', 150, 'email'],
    'contato_endereco' => ['Endereço', 255, 'text'],
    'contato_horario' => ['Horário de atendimento geral', 150, 'text'],
    'rede_facebook' => ['Facebook (endereço completo)', 255, 'url'],
    'rede_instagram' => ['Instagram (endereço completo)', 255, 'url'],
    'rede_youtube' => ['YouTube (endereço completo)', 255, 'url'],
];

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['salvar_contato', 'marcar_lida', 'marcar_nao_lida', 'excluir_mensagem']);

    if ($acao === 'salvar_contato') {
        $erros = [];
        $valores = [];
        foreach ($camposContatoGeral as $chave => [$rotulo, $maximo, $tipo]) {
            $valores[$chave] = lerCampo($chave, mb_strtolower($rotulo), $maximo, false, $erros);
            if ($valores[$chave] !== '' && $tipo === 'email' && !filter_var($valores[$chave], FILTER_VALIDATE_EMAIL)) {
                $erros[$chave] = 'E-mail inválido.';
            }
            if ($valores[$chave] !== '' && $tipo === 'url' && !preg_match('#^https://#i', $valores[$chave])) {
                $erros[$chave] = 'Use o endereço completo, começando com https://';
            }
        }
        if ($erros) {
            definirFlash('erros_contato', $erros);
            definirFlash('valores_contato', $valores);
            voltarComAviso('contatos.php', 'Confira os campos marcados.', 'erro');
        }
        $antes = array_intersect_key(listarConfiguracoes(), $valores);
        salvarConfiguracoes($valores);
        registrarAuditoria($usuarioAdmin, 'editar', 'configuracao', 'contato', $antes, $valores, 'Contato geral do campus atualizado');
        voltarComAviso('contatos.php', 'Contato geral salvo. Já aparece no site.');
    }

    $idMensagem = (int) ($_POST['id'] ?? 0);
    if ($acao === 'marcar_lida' || $acao === 'marcar_nao_lida') {
        marcarMensagemLida($idMensagem, $acao === 'marcar_lida');
        voltarComAviso('contatos.php#mensagens', $acao === 'marcar_lida' ? 'Marcada como lida.' : 'Marcada como não lida.');
    }
    if ($acao === 'excluir_mensagem') {
        excluirMensagemContato($idMensagem);
        registrarAuditoria($usuarioAdmin, 'excluir', 'contato_mensagem', (string) $idMensagem, null, null, 'Mensagem de contato excluída');
        voltarComAviso('contatos.php#mensagens', 'Mensagem excluída.');
    }
    voltarComAviso('contatos.php', 'Ação desconhecida.', 'erro');
}

$errosContato = pegarFlash('erros_contato', []);
$valoresContato = pegarFlash('valores_contato') ?? listarConfiguracoes();
$mensagensContato = listarMensagensContato(200);

require __DIR__ . '/../../view/admin/contatos.php';
