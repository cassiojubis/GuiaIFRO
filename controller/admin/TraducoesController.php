<?php
/**
 * TRADUÇÕES (admin/traducoes.php?idioma=es) — I6
 * Mostra o que falta traduzir e quem validou. Fluxo: rascunho -> revisado -> publicado.
 * Regras:
 *  - publicar exige o nome de quem revisou;
 *  - Oro Wari só publica com a confirmação de que foi revisado por falante da língua.
 */
require_once __DIR__ . '/BaseAdmin.php';
require_once __DIR__ . '/../../model/Idiomas.php';
exigirAdminGeral();

$tituloPaginaAdmin = 'Traduções';
$paginaAtualAdmin = 'traducoes';
$idiomasTraducao = ['es' => 'Espanhol', 'wari' => 'Oro Wari (Pakaa Nova)'];
$idioma = (string) ($_GET['idioma'] ?? $_POST['idioma'] ?? 'es');
if (!isset($idiomasTraducao[$idioma])) {
    $idioma = 'es';
}
$statusPossiveis = ['rascunho' => 'Rascunho', 'revisado' => 'Revisado', 'publicado' => 'Publicado'];

/** Tudo que pode ser traduzido: [entidade, id, campo, rótulo, texto em português]. */
function itensTraduziveis(): array
{
    $itens = [];
    foreach (TEXTOS_INTERFACE_PT as $chave => $texto) {
        $itens[] = ['interface', '', $chave, 'Interface: ' . $chave, $texto];
    }
    foreach (listarSetores() as $basico) {
        $setor = buscarSetorPorId($basico['id']);
        foreach (['nome' => 'nome', 'descricao' => 'descrição', 'texto_facil' => 'leitura fácil'] as $campo => $rotulo) {
            if (!empty($setor[$campo])) {
                $itens[] = ['setor', $setor['id'], $campo, ($setor['sigla'] ?: $setor['nome']) . ' — ' . $rotulo, $setor[$campo]];
            }
        }
    }
    return $itens;
}

if (ehPost()) {
    exigirCsrf();
    $voltar = 'traducoes.php?idioma=' . rawurlencode($idioma);
    $entidade = (string) ($_POST['entidade'] ?? '');
    $idEntidade = (string) ($_POST['entidade_id'] ?? '');
    $campo = (string) ($_POST['campo'] ?? '');
    $valido = false;
    foreach (itensTraduziveis() as [$e, $i, $c]) {
        $valido = $valido || ($e === $entidade && $i === $idEntidade && $c === $campo);
    }
    if (!$valido) {
        voltarComAviso($voltar, 'Item não encontrado.', 'erro');
    }

    $erros = [];
    $dados = [
        'texto' => lerCampo('texto', 'a tradução', 4000, true, $erros),
        'status' => (string) ($_POST['status'] ?? 'rascunho'),
        'validado_por' => lerCampo('validado_por', 'quem revisou', 150, false, $erros),
        'audio_url' => lerCampo('audio_url', 'o áudio', 255, false, $erros),
        'pictograma' => lerCampo('pictograma', 'o pictograma', 120, false, $erros),
        'validado_em' => null,
    ];
    if (!isset($statusPossiveis[$dados['status']])) {
        $erros['status'] = 'Escolha o status.';
    }
    if ($dados['status'] !== 'rascunho' && $dados['validado_por'] === '') {
        $erros['validado_por'] = 'Pra revisar ou publicar, diga quem revisou.';
    }
    if ($idioma === 'wari' && $dados['status'] === 'publicado' && !lerCheckbox('confirma_falante')) {
        $erros['confirma_falante'] = 'Oro Wari só é publicado depois de revisado por falante da língua. Marque a confirmação.';
    }
    if ($dados['audio_url'] !== '' && !preg_match('#^(https://|audio/)[^"\'<>\s]+$#i', $dados['audio_url'])) {
        $erros['audio_url'] = 'Use um endereço https:// ou um arquivo da pasta audio/.';
    }
    if ($dados['pictograma'] !== '' && !preg_match('/^[a-z0-9-]{2,40}$|^img\/[a-z0-9\/._-]+$/i', $dados['pictograma'])) {
        $erros['pictograma'] = 'Use o nome de um ícone do Lucide (ex.: book-open) ou um arquivo da pasta img/.';
    }
    if ($erros) {
        voltarComAviso($voltar, implode(' ', $erros), 'erro');
    }
    $antes = buscarTraducao($entidade, $idEntidade, $campo, $idioma);
    $dados['validado_em'] = $dados['status'] === 'rascunho' ? null
        : (($antes && $antes['validado_por'] === $dados['validado_por'] && $antes['validado_em']) ? $antes['validado_em'] : date('Y-m-d H:i:s'));
    salvarTraducao($entidade, $idEntidade, $campo, $idioma, $dados);
    registrarAuditoria($usuarioAdmin, $antes ? 'editar' : 'criar', 'traducao', "{$idioma}:{$entidade}:{$idEntidade}:{$campo}", $antes, $dados,
        'Tradução (' . $idiomasTraducao[$idioma] . ') de ' . $entidade . ($idEntidade ? " {$idEntidade}" : '') . " {$campo}: {$dados['status']}");
    voltarComAviso($voltar, 'Tradução salva como ' . mb_strtolower($statusPossiveis[$dados['status']]) . '.');
}

$traducoesExistentes = listarTraducoesDoIdioma($idioma);
$linhas = [];
$contagem = ['falta' => 0, 'rascunho' => 0, 'revisado' => 0, 'publicado' => 0];
foreach (itensTraduziveis() as [$entidade, $idEntidade, $campo, $rotulo, $textoPt]) {
    $traducao = $traducoesExistentes["{$entidade}|{$idEntidade}|{$campo}"] ?? null;
    $situacao = $traducao['status'] ?? 'falta';
    $contagem[$situacao]++;
    $linhas[] = compact('entidade', 'idEntidade', 'campo', 'rotulo', 'textoPt', 'traducao', 'situacao');
}

require __DIR__ . '/../../view/admin/traducoes.php';
