<?php
require_once __DIR__ . '/BaseAdmin.php';
exigirAdminGeral();

$tituloPaginaAdmin = 'Informações';
$paginaAtualAdmin = 'informacoes';

// Cada aba é uma tabela do banco (ver abasInformacao() no Repositorio).
$abasInformacoes = [
    ['id' => 'avisos', 'icone' => 'megaphone', 'texto' => 'Avisos', 'rotulo' => 'aviso'],
    ['id' => 'noticias', 'icone' => 'newspaper', 'texto' => 'Notícias', 'rotulo' => 'notícia'],
    ['id' => 'faq', 'icone' => 'circle-help', 'texto' => 'Perguntas frequentes', 'rotulo' => 'pergunta'],
    ['id' => 'links', 'icone' => 'link', 'texto' => 'Links úteis', 'rotulo' => 'link'],
    ['id' => 'institucional', 'icone' => 'landmark', 'texto' => 'Institucional', 'rotulo' => 'texto'],
];

/** Lê e valida os campos de uma aba. Devolve [valores, erros]. */
function lerFormularioInformacao(string $aba): array
{
    $erros = [];
    switch ($aba) {
        case 'avisos':
            $valores = [
                'titulo' => lerCampo('titulo', 'o título', 200, true, $erros),
                'mensagem' => lerCampo('mensagem', 'a mensagem', 2000, true, $erros),
                'nivel' => ($_POST['nivel'] ?? '') === 'urgente' ? 'urgente' : 'normal',
                'inicio' => lerDataHora('inicio', 'a data de início', $erros) ?: null,
                'fim' => lerDataHora('fim', 'a data de fim', $erros) ?: null,
            ];
            if ($valores['inicio'] && $valores['fim'] && $valores['fim'] < $valores['inicio']) {
                $erros['fim'] = 'O fim precisa ser depois do início.';
            }
            break;
        case 'noticias':
            $valores = [
                'titulo' => lerCampo('titulo', 'o título', 200, true, $erros),
                'resumo' => lerCampo('resumo', 'o resumo', 400, false, $erros) ?: null,
                'conteudo' => lerCampo('conteudo', 'o texto', 10000, false, $erros) ?: null,
                'categoria' => lerCampo('categoria', 'a categoria', 60, false, $erros) ?: null,
                'publicada_em' => lerData('publicada_em', 'a data', true, $erros),
                'destaque' => lerCheckbox('destaque') ? 1 : 0,
            ];
            break;
        case 'faq':
            $valores = [
                'pergunta' => lerCampo('pergunta', 'a pergunta', 300, true, $erros),
                'resposta' => lerCampo('resposta', 'a resposta', 4000, true, $erros),
                'setores' => lerLista('setores', 100, 60),
            ];
            break;
        case 'links':
            $valores = [
                'titulo' => lerCampo('titulo', 'o título', 150, true, $erros),
                'url' => lerCampo('url', 'o endereço', 255, true, $erros),
            ];
            if ($valores['url'] !== '' && (!filter_var($valores['url'], FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $valores['url']))) {
                $erros['url'] = 'Use um endereço completo começando com https://';
            }
            break;
        default: // institucional
            $valores = [
                'titulo' => lerCampo('titulo', 'o título', 150, true, $erros),
                'conteudo' => lerCampo('conteudo', 'o texto', 5000, true, $erros),
            ];
    }
    return [$valores, $erros];
}

if (ehPost()) {
    exigirCsrf();
    $aba = (string) ($_POST['aba'] ?? '');
    $abasValidas = array_column($abasInformacoes, 'rotulo', 'id');
    if (!isset($abasValidas[$aba])) {
        voltarComAviso('informacoes.php', 'Aba desconhecida.', 'erro');
    }
    $voltar = 'informacoes.php#' . $aba;
    $acao = acaoPedida(['salvar', 'alternar', 'excluir', 'mover']);
    $id = (int) ($_POST['id'] ?? 0);
    $antes = $id > 0 ? buscarItemInformacao($aba, $id) : null;
    if ($id > 0 && !$antes) {
        voltarComAviso($voltar, 'Item não encontrado.', 'erro');
    }
    $campoTitulo = abasInformacao()[$aba]['titulo'];
    $rotulo = $abasValidas[$aba];

    if ($acao === 'salvar') {
        [$valores, $erros] = lerFormularioInformacao($aba);
        if ($erros) {
            voltarComAviso($voltar, 'Confira os campos marcados.', 'erro', [
                // datas voltam como foram digitadas (o formato do campo é diferente do banco)
                'modal' => 'modal-' . $aba,
                'valores' => array_merge($valores, array_map(static function ($valor) { return is_string($valor) ? $valor : ''; }, array_intersect_key($_POST, array_flip(['inicio', 'fim', 'publicada_em'])))) + ['id' => $id ?: ''],
                'erros' => $erros,
            ]);
        }
        $idSalvo = salvarItemInformacao($aba, $antes ? $id : null, $valores);
        registrarAuditoria($usuarioAdmin, $antes ? 'editar' : 'criar', abasInformacao()[$aba]['tabela'], (string) $idSalvo, $antes, $valores,
            ucfirst($rotulo) . ' "' . mb_substr($valores[$campoTitulo], 0, 80) . '" ' . ($antes ? 'editado(a)' : 'criado(a)'));
        voltarComAviso($voltar, $antes ? 'Alteração salva.' : ucfirst($rotulo) . ' adicionado(a).');
    }

    if (!$antes) {
        voltarComAviso($voltar, 'Item não encontrado.', 'erro');
    }

    if ($acao === 'alternar') {
        alternarPublicacaoInformacao($aba, $id);
        $publicado = (int) $antes['publicado'] === 1;
        registrarAuditoria($usuarioAdmin, $publicado ? 'desativar' : 'ativar', abasInformacao()[$aba]['tabela'], (string) $id, null, null,
            ucfirst($rotulo) . ' "' . mb_substr($antes[$campoTitulo], 0, 80) . '" ' . ($publicado ? 'ocultado(a)' : 'publicado(a)'));
        voltarComAviso($voltar, $publicado ? 'Ocultado do site.' : 'Publicado no site.');
    }

    if ($acao === 'mover') {
        moverItemInformacao($aba, $id, ($_POST['direcao'] ?? '') === 'cima' ? 'cima' : 'baixo');
        voltarComAviso($voltar, 'Ordem atualizada.');
    }

    if ($acao === 'excluir') {
        excluirItemInformacao($aba, $id);
        registrarAuditoria($usuarioAdmin, 'excluir', abasInformacao()[$aba]['tabela'], (string) $id, $antes, null,
            ucfirst($rotulo) . ' "' . mb_substr($antes[$campoTitulo], 0, 80) . '" excluído(a)');
        voltarComAviso($voltar, 'Excluído.');
    }

    voltarComAviso($voltar, 'Ação desconhecida.', 'erro');
}

$itensPorAba = [];
foreach ($abasInformacoes as $aba) {
    $itensPorAba[$aba['id']] = listarItensInformacao($aba['id']);
}
$setoresParaFaq = listarSetores(true);

/** Valor do banco no formato do <input type="datetime-local">. */
function paraDataHoraLocal(?string $valor): string
{
    return $valor ? (new DateTimeImmutable($valor))->format('Y-m-d\TH:i') : '';
}

/** Linha de resumo de cada item (o que aparece embaixo do título). */
function resumoItemInformacao(string $aba, array $item): string
{
    switch ($aba) {
        case 'avisos':
            $periodo = ($item['inicio'] ? 'de ' . dataHoraBr($item['inicio']) : 'desde já')
                . ($item['fim'] ? ' até ' . dataHoraBr($item['fim']) : ', sem data para sair');
            return mb_substr($item['mensagem'], 0, 140) . ' · ' . $periodo;
        case 'noticias':
            return dataHoraBr($item['publicada_em'], false) . ($item['categoria'] ? ' · ' . $item['categoria'] : '')
                . ($item['resumo'] ? ' · ' . mb_substr($item['resumo'], 0, 120) : '');
        case 'faq':
            return mb_substr($item['resposta'], 0, 140) . ' · ' . ($item['setores'] ? 'Setores: ' . implode(', ', $item['setores']) : 'FAQ geral');
        case 'links':
            return $item['url'];
        default:
            return mb_substr($item['conteudo'], 0, 160);
    }
}

require __DIR__ . '/../../view/admin/informacoes.php';
