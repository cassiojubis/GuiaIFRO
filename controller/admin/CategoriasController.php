<?php
require_once __DIR__ . '/BaseAdmin.php';
exigirAdminGeral();

$tituloPaginaAdmin = 'Categorias';
$paginaAtualAdmin = 'categorias';

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['salvar', 'alternar', 'excluir', 'mover']);
    $id = (string) ($_POST['id'] ?? '');
    $antes = $id !== '' ? buscarGrupoCategoria($id) : null;

    if ($acao !== 'salvar' && !$antes) {
        voltarComAviso('categorias.php', 'Categoria não encontrada.', 'erro');
    }

    if ($acao === 'salvar') {
        $erros = [];
        $dados = [
            'nome' => lerCampo('nome', 'o nome', 150, true, $erros),
            'icone' => lerCampo('icone', 'o ícone', 40, true, $erros),
            'cor' => (string) ($_POST['cor'] ?? ''),
            'ativo' => lerCheckbox('ativo'),
        ];
        if ($dados['icone'] !== '' && !iconeValido($dados['icone'])) {
            $erros['icone'] = 'Use o nome de um ícone do Lucide (ex.: book-open).';
        }
        if (!isset(CORES_ADMIN[$dados['cor']])) {
            $erros['cor'] = 'Escolha uma cor da lista.';
        }
        if ($id !== '' && !$antes) {
            voltarComAviso('categorias.php', 'Categoria não encontrada.', 'erro');
        }
        if ($erros) {
            voltarComAviso('categorias.php', 'Confira os campos marcados.', 'erro', [
                'modal' => 'modal-categoria', 'titulo' => $antes ? 'Editar categoria' : 'Nova categoria',
                'valores' => $dados + ['id' => $id], 'erros' => $erros,
            ]);
        }

        if ($antes) {
            atualizarCategoria($id, $dados);
            registrarAuditoria($usuarioAdmin, 'editar', 'categoria', $id, $antes, $dados, "Categoria \"{$dados['nome']}\" editada");
            voltarComAviso('categorias.php', 'Categoria atualizada.');
        }
        $novoId = criarCategoria($dados);
        registrarAuditoria($usuarioAdmin, 'criar', 'categoria', $novoId, null, $dados, "Categoria \"{$dados['nome']}\" criada");
        voltarComAviso('categorias.php', 'Categoria criada.');
    }

    if ($acao === 'alternar') {
        alternarCategoriaAtiva($id);
        $acaoTexto = (int) $antes['ativo'] ? 'desativada' : 'ativada';
        registrarAuditoria($usuarioAdmin, (int) $antes['ativo'] ? 'desativar' : 'ativar', 'categoria', $id, $antes, null, "Categoria \"{$antes['nome']}\" {$acaoTexto}");
        voltarComAviso('categorias.php', "Categoria {$acaoTexto}.");
    }

    if ($acao === 'mover') {
        moverCategoria($id, ($_POST['direcao'] ?? '') === 'cima' ? 'cima' : 'baixo');
        voltarComAviso('categorias.php', 'Ordem atualizada.');
    }

    if ($acao === 'excluir') {
        excluirCategoria($id);
        registrarAuditoria($usuarioAdmin, 'excluir', 'categoria', $id, $antes, null, "Categoria \"{$antes['nome']}\" excluída");
        voltarComAviso('categorias.php', 'Categoria excluída. Os setores dela ficaram sem categoria.');
    }

    voltarComAviso('categorias.php', 'Ação desconhecida.', 'erro');
}

$categoriasAdmin = listarGruposCategorias(true);
$coresDisponiveisAdmin = CORES_ADMIN;

require __DIR__ . '/../../view/admin/categorias.php';
