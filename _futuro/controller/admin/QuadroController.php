<?php
/**
 * QUADRO DE HORÁRIOS (admin/quadro.php) — N13
 * O DAPE envia o CSV -> o sistema valida -> mostra a prévia -> alguém publica.
 * Toda versão fica guardada: dá pra voltar pra uma anterior.
 * Quem pode: admin geral ou responsável do setor DAPE.
 */
require_once __DIR__ . '/BaseAdmin.php';
require_once __DIR__ . '/../../model/QuadroCsv.php';

if (!ehAdminGeral() && !podeEditarSetor('dape')) {
    voltarComAviso('dashboard.php', 'Só o DAPE e o administrador mexem no quadro de horários.', 'erro');
}

$tituloPaginaAdmin = 'Quadro de horários';
$paginaAtualAdmin = 'quadro';
const QUADRO_TAMANHO_MAXIMO = 1024 * 1024; // 1 MB

if (!empty($_GET['modelo'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="modelo-quadro-horarios.csv"');
    readfile(__DIR__ . '/../../docs/modelo-quadro-horarios.csv');
    exit;
}

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['enviar', 'publicar', 'excluir']);

    if ($acao === 'enviar') {
        $arquivo = $_FILES['arquivo'] ?? null;
        if (!$arquivo || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'])) {
            voltarComAviso('quadro.php', 'Escolha o arquivo CSV do quadro.', 'erro');
        }
        if ($arquivo['size'] > QUADRO_TAMANHO_MAXIMO) {
            voltarComAviso('quadro.php', 'Arquivo grande demais (máximo 1 MB).', 'erro');
        }
        $extensao = strtolower(pathinfo((string) $arquivo['name'], PATHINFO_EXTENSION));
        $tipoReal = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
        if (!in_array($extensao, ['csv', 'txt'], true) || !in_array($tipoReal, ['text/plain', 'text/csv', 'application/csv', 'text/x-csv', 'application/vnd.ms-excel'], true)) {
            voltarComAviso('quadro.php', 'O arquivo precisa ser CSV (no Excel: Salvar como › CSV).', 'erro');
        }

        $resultado = lerQuadroCsv((string) file_get_contents($arquivo['tmp_name']), listarMapaSalas()['salas']);
        if ($resultado['erros']) {
            $lista = [];
            foreach (array_slice($resultado['erros'], 0, 15, true) as $linha => $mensagem) {
                $lista[] = "linha {$linha}: {$mensagem}";
            }
            definirFlash('erros_quadro', $lista);
            voltarComAviso('quadro.php', 'O arquivo tem ' . count($resultado['erros']) . ' linha(s) com problema. Nada foi salvo.', 'erro');
        }
        if (!$resultado['aulas']) {
            voltarComAviso('quadro.php', 'Não achei nenhuma aula no arquivo.', 'erro');
        }
        $errosDescricao = [];
        $descricao = lerCampo('descricao', 'a descrição', 200, false, $errosDescricao);
        $idVersao = salvarVersaoQuadro(basename((string) $arquivo['name']), $descricao, $usuarioAdmin['nome'], $resultado['aulas']);
        registrarAuditoria($usuarioAdmin, 'criar', 'quadro_versoes', (string) $idVersao, null, ['aulas' => count($resultado['aulas']), 'turmas' => $resultado['turmas']], 'Quadro de horários enviado (ainda não publicado)');
        voltarComAviso('quadro.php?versao=' . $idVersao, 'Arquivo lido: ' . count($resultado['aulas']) . ' aulas de ' . count($resultado['turmas']) . ' turma(s). Confira a prévia e publique.');
    }

    $versao = buscarVersaoQuadro((int) ($_POST['versao'] ?? 0));
    if (!$versao) {
        voltarComAviso('quadro.php', 'Versão não encontrada.', 'erro');
    }
    if ($acao === 'publicar') {
        publicarVersaoQuadro((int) $versao['id']);
        registrarAuditoria($usuarioAdmin, 'ativar', 'quadro_versoes', (string) $versao['id'], null, null, 'Quadro de horários publicado (' . $versao['nome_arquivo'] . ')');
        voltarComAviso('quadro.php', 'Quadro publicado: os alunos já veem as aulas novas.');
    }
    if ($acao === 'excluir') {
        if ((int) $versao['publicada']) {
            voltarComAviso('quadro.php', 'Não dá pra excluir a versão publicada. Publique outra antes.', 'erro');
        }
        excluirVersaoQuadro((int) $versao['id']);
        registrarAuditoria($usuarioAdmin, 'excluir', 'quadro_versoes', (string) $versao['id'], null, null, 'Versão do quadro excluída (' . $versao['nome_arquivo'] . ')');
        voltarComAviso('quadro.php', 'Versão excluída.');
    }
    voltarComAviso('quadro.php', 'Ação desconhecida.', 'erro');
}

$versoes = listarVersoesQuadro();
$errosQuadro = pegarFlash('erros_quadro', []);
$versaoPrevia = !empty($_GET['versao']) ? buscarVersaoQuadro((int) $_GET['versao']) : null;
$aulasPrevia = [];
if ($versaoPrevia) {
    foreach (aulasDaVersao((int) $versaoPrevia['id']) as $aula) {
        $aulasPrevia[$aula['turma']][] = $aula;
    }
}

require __DIR__ . '/../../view/admin/quadro.php';
