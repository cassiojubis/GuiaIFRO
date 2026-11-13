<?php
/** "COMO FAÇO PARA..." NO ADMIN (admin/fluxos.php) — N11 */
require_once __DIR__ . '/BaseAdmin.php';
exigirAdminGeral();

$tituloPaginaAdmin = 'Como faço para…';
$paginaAtualAdmin = 'fluxos';
$canais = ['' => 'Não informado', 'suap' => 'Pelo SUAP', 'presencial' => 'Presencial', 'ambos' => 'Pelo SUAP ou presencial'];

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['salvar', 'alternar', 'excluir', 'mover']);
    $id = (int) ($_POST['id'] ?? 0);
    $antes = $id > 0 ? buscarFluxo((string) $id, true) : null;
    if ($id > 0 && !$antes) {
        voltarComAviso('fluxos.php', 'Passo a passo não encontrado.', 'erro');
    }

    if ($acao === 'salvar') {
        $erros = [];
        $dados = [
            'titulo' => lerCampo('titulo', 'o título', 200, true, $erros),
            'resumo' => lerCampo('resumo', 'o resumo', 400, false, $erros),
            'setor_id' => (string) ($_POST['setor_id'] ?? ''),
            'canal' => (string) ($_POST['canal'] ?? ''),
            'prazo' => lerCampo('prazo', 'o prazo', 150, false, $erros),
            'publicado' => lerCheckbox('publicado'),
            'passos' => lerLista('passos', 30, 400),
            'documentos' => lerLista('documentos', 30, 255),
        ];
        if (!isset($canais[$dados['canal']])) {
            $erros['canal'] = 'Escolha como pedir.';
        }
        if ($dados['setor_id'] !== '' && !setorExiste($dados['setor_id'])) {
            $erros['setor_id'] = 'Escolha um setor da lista.';
        }
        if (!$dados['passos']) {
            $erros['passos'] = 'Escreva pelo menos um passo.';
        }
        if ($erros) {
            voltarComAviso('fluxos.php', 'Confira os campos marcados.', 'erro', ['modal' => 'modal-fluxo', 'valores' => $dados + ['id' => $id ?: ''], 'erros' => $erros]);
        }
        $idSalvo = salvarFluxo($antes ? $id : null, $dados);
        registrarAuditoria($usuarioAdmin, $antes ? 'editar' : 'criar', 'fluxo', (string) $idSalvo, $antes, $dados, 'Passo a passo "' . $dados['titulo'] . '" ' . ($antes ? 'editado' : 'criado'));
        voltarComAviso('fluxos.php', 'Passo a passo salvo.');
    }
    if (!$antes) {
        voltarComAviso('fluxos.php', 'Passo a passo não encontrado.', 'erro');
    }
    if ($acao === 'alternar') {
        alternarFluxoPublicado($id);
        registrarAuditoria($usuarioAdmin, (int) $antes['publicado'] ? 'desativar' : 'ativar', 'fluxo', (string) $id, null, null, 'Passo a passo "' . $antes['titulo'] . '" ' . ((int) $antes['publicado'] ? 'ocultado' : 'publicado'));
        voltarComAviso('fluxos.php', (int) $antes['publicado'] ? 'Ocultado do site.' : 'Publicado no site.');
    }
    if ($acao === 'mover') {
        moverFluxo($id, ($_POST['direcao'] ?? '') === 'cima' ? 'cima' : 'baixo');
        voltarComAviso('fluxos.php', 'Ordem atualizada.');
    }
    if ($acao === 'excluir') {
        excluirFluxo($id);
        registrarAuditoria($usuarioAdmin, 'excluir', 'fluxo', (string) $id, $antes, null, 'Passo a passo "' . $antes['titulo'] . '" excluído');
        voltarComAviso('fluxos.php', 'Passo a passo excluído.');
    }
    voltarComAviso('fluxos.php', 'Ação desconhecida.', 'erro');
}

$fluxosAdmin = array_map(static function ($fluxo) { return buscarFluxo((string) $fluxo['id'], true); }, listarFluxos(true));
$setoresParaFluxo = listarSetores(true);

require __DIR__ . '/../../view/admin/fluxos.php';
