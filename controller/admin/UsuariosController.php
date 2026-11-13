<?php
require_once __DIR__ . '/BaseAdmin.php';
exigirAdminGeral();

$tituloPaginaAdmin = 'Usuários';
$paginaAtualAdmin = 'usuarios';

$perfisValidos = array_column(listarPerfis(), 'nome', 'id');

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['salvar', 'alternar', 'derrubar_sessoes']);
    $id = (int) ($_POST['id'] ?? 0);
    $antes = $id > 0 ? buscarUsuarioPorId($id) : null;
    if ($id > 0 && !$antes) {
        voltarComAviso('usuarios.php', 'Usuário não encontrado.', 'erro');
    }
    // O que nunca vai pro log de auditoria.
    $semSenha = static function (?array $usuario): ?array {
        return $usuario ? array_diff_key($usuario, ['senha_hash' => 1, 'senha' => 1]) : null;
    };

    if ($acao === 'salvar') {
        $erros = [];
        $dados = [
            'nome' => lerCampo('nome', 'o nome', 150, true, $erros),
            'usuario' => mb_strtolower(lerCampo('usuario', 'o nome de usuário', 60, true, $erros)),
            'email' => mb_strtolower(lerCampo('email', 'o e-mail', 150, false, $erros)),
            'perfil_id' => (string) ($_POST['perfil_id'] ?? ''),
            'ativo' => lerCheckbox('ativo'),
            'setores' => lerLista('setores', 100, 60),
        ];
        $senha = (string) ($_POST['senha'] ?? '');

        if ($dados['usuario'] !== '' && !preg_match('/^[a-z0-9._-]{3,60}$/', $dados['usuario'])) {
            $erros['usuario'] = 'Use de 3 a 60 letras minúsculas, números, ponto, traço ou _.';
        }
        if ($dados['email'] !== '' && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'E-mail inválido.';
        }
        if (!isset($perfisValidos[$dados['perfil_id']])) {
            $erros['perfil_id'] = 'Escolha um perfil.';
        }
        if (!isset($erros['usuario']) && usuarioOuEmailEmUso($dados['usuario'], $dados['email'] ?: null, $id)) {
            $erros['usuario'] = 'Esse usuário ou e-mail já está em uso.';
        }
        if (!$antes || $senha !== '') {
            $problema = problemaNaSenha($senha, $dados['usuario']);
            if ($problema) {
                $erros['senha'] = $problema;
            }
        }
        if ($dados['perfil_id'] === 'responsavel_setor' && !$dados['setores']) {
            $erros['setores'] = 'Escolha pelo menos um setor para o responsável.';
        }
        // Não dá pra tirar o último admin geral (senão ninguém mais administra).
        if ($antes && $antes['perfil_id'] === 'admin_geral' && ($dados['perfil_id'] !== 'admin_geral' || !$dados['ativo'])
            && contarAdminsGeraisAtivos($id) === 0) {
            $erros['perfil_id'] = 'Este é o único administrador geral ativo. Crie outro antes de mudar este.';
        }
        if ($erros) {
            voltarComAviso('usuarios.php', 'Confira os campos marcados.', 'erro', [
                'modal' => 'modal-usuario', 'titulo' => $antes ? 'Editar usuário' : 'Novo usuário',
                'valores' => $dados + ['id' => $id ?: ''], 'erros' => $erros,
            ]);
        }

        if ($antes) {
            atualizarUsuario($id, $dados);
            if ($senha !== '') {
                definirSenhaUsuario($id, password_hash($senha, PASSWORD_DEFAULT));
            }
        } else {
            $id = criarUsuario($dados, password_hash($senha, PASSWORD_DEFAULT));
        }
        definirSetoresDoUsuario($id, $dados['perfil_id'] === 'responsavel_setor' ? $dados['setores'] : []);
        registrarAuditoria($usuarioAdmin, $antes ? 'editar' : 'criar', 'usuario', (string) $id, $semSenha($antes), $dados,
            'Usuário "' . $dados['usuario'] . '" ' . ($antes ? 'editado' : 'criado') . ($senha !== '' && $antes ? ' (senha trocada)' : ''));
        voltarComAviso('usuarios.php', $antes ? 'Usuário atualizado.' : 'Usuário criado. Passe o usuário e a senha para a pessoa por um canal seguro.');
    }

    if (!$antes) {
        voltarComAviso('usuarios.php', 'Usuário não encontrado.', 'erro');
    }

    if ($acao === 'alternar') {
        if ($id === (int) $usuarioAdmin['id']) {
            voltarComAviso('usuarios.php', 'Você não pode desativar o próprio usuário.', 'erro');
        }
        if ((int) $antes['ativo'] && $antes['perfil_id'] === 'admin_geral' && contarAdminsGeraisAtivos($id) === 0) {
            voltarComAviso('usuarios.php', 'Este é o único administrador geral ativo.', 'erro');
        }
        $dados = ['nome' => $antes['nome'], 'usuario' => $antes['usuario'], 'email' => $antes['email'], 'perfil_id' => $antes['perfil_id'], 'ativo' => !(int) $antes['ativo']];
        atualizarUsuario($id, $dados);
        registrarAuditoria($usuarioAdmin, $dados['ativo'] ? 'ativar' : 'desativar', 'usuario', (string) $id, null, null,
            'Usuário "' . $antes['usuario'] . '" ' . ($dados['ativo'] ? 'ativado' : 'desativado'));
        voltarComAviso('usuarios.php', $dados['ativo'] ? 'Usuário ativado.' : 'Usuário desativado e deslogado.');
    }

    if ($acao === 'derrubar_sessoes') {
        encerrarTodasAsSessoes($id);
        registrarAuditoria($usuarioAdmin, 'editar', 'usuario', (string) $id, null, null, 'Sessões de "' . $antes['usuario'] . '" encerradas');
        if ($id === (int) $usuarioAdmin['id']) {
            encerrarSessaoAdmin();
            header('Location: login.php');
            exit;
        }
        voltarComAviso('usuarios.php', 'Pronto: o usuário foi deslogado de todos os aparelhos.');
    }

    voltarComAviso('usuarios.php', 'Ação desconhecida.', 'erro');
}

$usuariosAdmin = listarUsuarios();
$setoresParaUsuario = listarSetores(true);
$nomesSetores = array_column($setoresParaUsuario, 'nome', 'id');

require __DIR__ . '/../../view/admin/usuarios.php';
