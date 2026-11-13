<?php
require_once __DIR__ . '/BaseAdmin.php';

$tituloPaginaAdmin = ehAdminGeral() ? 'Setores' : 'Meus setores';
$paginaAtualAdmin = 'setores';

// O que o responsável de setor pode mudar (o admin geral muda tudo).
const CAMPOS_DO_RESPONSAVEL = ['descricao', 'localizacao', 'telefone', 'email', 'responsavel', 'horario_texto', 'equipe', 'servicos', 'documentos', 'sinonimos'];

/** Lê e valida o formulário do setor. Devolve [dados, erros]. */
function lerFormularioSetor(bool $completo): array
{
    $erros = [];
    $dados = [
        'descricao' => lerCampo('descricao', 'a descrição', 2000, false, $erros),
        'localizacao' => lerCampo('localizacao', 'a localização', 255, false, $erros),
        'telefone' => lerCampo('telefone', 'o telefone', 60, false, $erros),
        'email' => lerCampo('email', 'o e-mail', 150, false, $erros),
        'responsavel' => lerCampo('responsavel', 'o responsável', 150, false, $erros),
        'horario_texto' => lerCampo('horario_texto', 'a observação de horário', 255, false, $erros),
        'equipe' => lerLista('equipe', 40, 150),
        'servicos' => lerLista('servicos', 40, 255),
        'documentos' => lerLista('documentos', 40, 255),
        'sinonimos' => lerLista('sinonimos', 40, 100),
    ];
    if ($dados['email'] !== '' && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'E-mail inválido.';
    }

    if ($completo) {
        $dados += [
            'nome' => lerCampo('nome', 'o nome', 200, true, $erros),
            'sigla' => lerCampo('sigla', 'a sigla', 30, false, $erros),
            'categoria_id' => (string) ($_POST['categoria_id'] ?? ''),
            'icone' => lerCampo('icone', 'o ícone', 40, true, $erros),
            'cor' => (string) ($_POST['cor'] ?? 'green'),
            'descricao_curta' => lerCampo('descricao_curta', 'a descrição curta', 255, false, $erros),
            'texto_facil' => lerCampo('texto_facil', 'o texto em leitura fácil', 2000, false, $erros),
            'bloco' => lerCampo('bloco', 'o bloco', 150, false, $erros),
            'sala_texto' => lerCampo('sala_texto', 'a sala', 150, false, $erros),
            'ativo' => lerCheckbox('ativo'),
        ];
        if ($dados['icone'] !== '' && !iconeValido($dados['icone'])) {
            $erros['icone'] = 'Use o nome de um ícone do Lucide (ex.: book-open).';
        }
        if (!isset(CORES_ADMIN[$dados['cor']])) {
            $erros['cor'] = 'Escolha uma cor da lista.';
        }
        if ($dados['categoria_id'] !== '' && !buscarGrupoCategoria($dados['categoria_id'])) {
            $erros['categoria_id'] = 'Escolha uma categoria da lista.';
        }
    }
    return [$dados, $erros];
}

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['salvar', 'alternar', 'excluir']);

    $id = (string) ($_POST['id'] ?? '');
    $antes = $id !== '' ? buscarSetorPorId($id, true) : null;

    if ($id !== '' && !$antes) {
        voltarComAviso('setores.php', 'Setor não encontrado.', 'erro');
    }
    // Criar, excluir e ativar/desativar: só admin geral. Editar: admin geral ou responsável do setor.
    $soAdminGeral = $acao !== 'salvar' || $id === '';
    if (($soAdminGeral && !ehAdminGeral()) || ($id !== '' && !podeEditarSetor($id))) {
        voltarComAviso('setores.php', 'Você não tem permissão para isso.', 'erro');
    }

    if ($acao === 'salvar') {
        [$dados, $erros] = lerFormularioSetor(ehAdminGeral());
        if ($erros) {
            voltarComAviso('setores.php', 'Confira os campos marcados.', 'erro', [
                'modal' => 'modal-setor', 'titulo' => $antes ? 'Editar setor' : 'Novo setor',
                'valores' => $dados + ['id' => $id], 'erros' => $erros,
            ]);
        }
        if (!ehAdminGeral()) {
            $dados = array_intersect_key($dados, array_flip(CAMPOS_DO_RESPONSAVEL));
        }

        $idSalvo = salvarSetor($antes ? $id : null, $dados, $usuarioAdmin['nome']);
        $nome = $dados['nome'] ?? $antes['nome'];
        registrarAuditoria($usuarioAdmin, $antes ? 'editar' : 'criar', 'setor', $idSalvo, $antes, $dados, 'Setor "' . $nome . '" ' . ($antes ? 'editado' : 'criado'));
        voltarComAviso('setores.php', $antes ? 'Setor atualizado.' : 'Setor criado. Ele já tem página no site.');
    }

    if ($acao === 'alternar') {
        alternarSetorAtivo($id);
        $ativo = (int) $antes['ativo'] === 1;
        registrarAuditoria($usuarioAdmin, $ativo ? 'desativar' : 'ativar', 'setor', $id, null, null, 'Setor "' . $antes['nome'] . '" ' . ($ativo ? 'desativado' : 'ativado'));
        voltarComAviso('setores.php', $ativo ? 'Setor desativado: sumiu do site.' : 'Setor ativado: já aparece no site.');
    }

    if ($acao === 'excluir') {
        excluirSetor($id);
        registrarAuditoria($usuarioAdmin, 'excluir', 'setor', $id, $antes, null, 'Setor "' . $antes['nome'] . '" excluído');
        voltarComAviso('setores.php', 'Setor excluído.');
    }

    voltarComAviso('setores.php', 'Ação desconhecida.', 'erro');
}

$categoriasParaSetorAdmin = array_column(listarGruposCategorias(true), 'nome', 'id');
$coresDisponiveisAdmin = CORES_ADMIN;
$setoresAdmin = [];
foreach (listarSetores(true) as $basico) {
    if (!podeEditarSetor($basico['id'])) {
        continue;
    }
    $setoresAdmin[] = buscarSetorPorId($basico['id'], true);
}

require __DIR__ . '/../../view/admin/setores.php';
