<?php
/**
 * PERGUNTAS RECEBIDAS (admin/perguntas.php) — N10
 * Responder publica no FAQ do setor. O responsável só vê as do setor dele.
 */
require_once __DIR__ . '/BaseAdmin.php';

$tituloPaginaAdmin = 'Perguntas recebidas';
$paginaAtualAdmin = 'perguntas';

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['responder', 'descartar']);
    $pergunta = buscarPerguntaSetor((int) ($_POST['id'] ?? 0));
    if (!$pergunta || $pergunta['status'] !== 'pendente' || !podeEditarSetor($pergunta['setor_id'])) {
        voltarComAviso('perguntas.php', 'Pergunta não encontrada.', 'erro');
    }

    if ($acao === 'responder') {
        $erros = [];
        $textoPergunta = lerCampo('pergunta', 'a pergunta', 300, true, $erros);
        $resposta = lerCampo('resposta', 'a resposta', 4000, true, $erros);
        if ($erros) {
            voltarComAviso('perguntas.php', reset($erros), 'erro');
        }
        $idFaq = responderPerguntaSetor((int) $pergunta['id'], $textoPergunta, $resposta, $usuarioAdmin['nome']);
        registrarAuditoria($usuarioAdmin, 'criar', 'faq', (string) $idFaq, null, ['pergunta' => $textoPergunta, 'resposta' => $resposta],
            'Pergunta respondida e publicada no FAQ de "' . $pergunta['setor_id'] . '"');
        $aviso = 'Resposta publicada no FAQ do setor.';
        if ($pergunta['email']) {
            $aviso .= ' Quem perguntou deixou e-mail: ' . $pergunta['email'] . ' (avise se quiser).';
        }
        voltarComAviso('perguntas.php', $aviso);
    }

    if ($acao === 'descartar') {
        descartarPerguntaSetor((int) $pergunta['id'], $usuarioAdmin['nome']);
        registrarAuditoria($usuarioAdmin, 'excluir', 'perguntas_setor', (string) $pergunta['id'], $pergunta, null, 'Pergunta descartada');
        voltarComAviso('perguntas.php', 'Pergunta descartada.');
    }
    voltarComAviso('perguntas.php', 'Ação desconhecida.', 'erro');
}

$perguntasPendentes = listarPerguntasPendentes(ehAdminGeral() ? null : $usuarioAdmin['setores']);

require __DIR__ . '/../../view/admin/perguntas.php';
