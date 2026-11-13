<?php
/**
 * "PRIMEIRO DIA" NO ADMIN (admin/primeiro-dia.php) — N12
 * Paradas do roteiro do calouro e os QR "escondidos" do passaporte.
 * ?imprimir=1 gera a folha com o QR secreto de cada parada (só o admin vê).
 */
require_once __DIR__ . '/BaseAdmin.php';
require_once __DIR__ . '/../../model/QrCode.php';
exigirAdminGeral();

$tituloPaginaAdmin = 'Primeiro dia (calouros)';
$paginaAtualAdmin = 'primeiro-dia';

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['salvar', 'excluir', 'mover']);
    $id = (int) ($_POST['id'] ?? 0);
    $antes = $id > 0 ? buscarParadaPrimeiroDia($id) : null;
    if ($id > 0 && !$antes) {
        voltarComAviso('primeiro-dia.php', 'Parada não encontrada.', 'erro');
    }
    if ($acao === 'salvar') {
        $erros = [];
        $dados = [
            'titulo' => lerCampo('titulo', 'o nome da parada', 150, true, $erros),
            'setor_id' => (string) ($_POST['setor_id'] ?? ''),
            'o_que_fazer' => lerCampo('o_que_fazer', 'o que fazer ali', 500, true, $erros),
            'curiosidade' => lerCampo('curiosidade', 'a curiosidade', 500, false, $erros),
            'publicado' => lerCheckbox('publicado'),
        ];
        if ($dados['setor_id'] !== '' && !setorExiste($dados['setor_id'])) {
            $erros['setor_id'] = 'Escolha um setor da lista.';
        }
        if ($erros) {
            voltarComAviso('primeiro-dia.php', 'Confira os campos marcados.', 'erro', ['modal' => 'modal-parada', 'valores' => $dados + ['id' => $id ?: ''], 'erros' => $erros]);
        }
        $idSalvo = salvarParadaPrimeiroDia($antes ? $id : null, $dados);
        registrarAuditoria($usuarioAdmin, $antes ? 'editar' : 'criar', 'paradas_primeiro_dia', (string) $idSalvo, $antes, $dados, 'Parada "' . $dados['titulo'] . '" do Primeiro dia ' . ($antes ? 'editada' : 'criada'));
        voltarComAviso('primeiro-dia.php', 'Parada salva.');
    }
    if (!$antes) {
        voltarComAviso('primeiro-dia.php', 'Parada não encontrada.', 'erro');
    }
    if ($acao === 'mover') {
        moverNaOrdem('paradas_primeiro_dia', 'id', $id, ($_POST['direcao'] ?? '') === 'cima' ? 'cima' : 'baixo');
        voltarComAviso('primeiro-dia.php', 'Ordem atualizada.');
    }
    if ($acao === 'excluir') {
        excluirParadaPrimeiroDia($id);
        registrarAuditoria($usuarioAdmin, 'excluir', 'paradas_primeiro_dia', (string) $id, $antes, null, 'Parada "' . $antes['titulo'] . '" excluída');
        voltarComAviso('primeiro-dia.php', 'Parada excluída.');
    }
    voltarComAviso('primeiro-dia.php', 'Ação desconhecida.', 'erro');
}

$paradasAdmin = listarParadasPrimeiroDia(true);

if (!empty($_GET['imprimir'])) {
    // Folha simples pra imprimir e esconder um QR em cada parada.
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>QR do passaporte</title>'
        . '<style>body{font-family:Arial,sans-serif;margin:10mm}.qr{display:inline-block;width:85mm;margin:0 4mm 8mm 0;padding:5mm;border:1px dashed #999;text-align:center;vertical-align:top}'
        . '.qr svg{width:55mm;height:55mm}h2{font-size:14pt;margin:2mm 0}p{font-size:10pt;margin:0}</style></head><body>';
    echo '<p>Recorte e esconda cada QR na sua parada. Quem escanear ganha o selo do passaporte.</p>';
    foreach ($paradasAdmin as $parada) {
        $url = urlBaseSite() . '/primeiro-dia.php?selo=' . $parada['codigo_selo'];
        echo '<div class="qr"><h2>' . htmlspecialchars($parada['titulo'], ENT_QUOTES, 'UTF-8') . '</h2>'
            . qrCodeSvg($url, 220, 'QR do passaporte: ' . $parada['titulo'])
            . '<p>Passaporte do calouro · Guia IFRO</p></div>';
    }
    echo '</body></html>';
    exit;
}

$setoresParaParada = listarSetores(true);

require __DIR__ . '/../../view/admin/primeiro-dia.php';
