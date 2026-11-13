<?php
require_once __DIR__ . '/BaseAdmin.php';

$tituloPaginaAdmin = 'Meus dados';
$paginaAtualAdmin = 'meus-dados';

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['salvar_dados', 'trocar_senha', 'sair_de_todos']);
    $idUsuario = (int) $usuarioAdmin['id'];

    if ($acao === 'salvar_dados') {
        $erros = [];
        $nome = lerCampo('nome', 'o nome', 150, true, $erros);
        $email = mb_strtolower(lerCampo('email', 'o e-mail', 150, false, $erros));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'E-mail inválido.';
        } elseif ($email !== '' && usuarioOuEmailEmUso('', $email, $idUsuario)) {
            $erros['email'] = 'Esse e-mail já está em uso por outro usuário.';
        }
        if ($erros) {
            voltarComAviso('meus-dados.php', reset($erros), 'erro');
        }
        atualizarMeusDados($idUsuario, $nome, $email);
        registrarAuditoria($usuarioAdmin, 'editar', 'usuario', (string) $idUsuario, null, ['nome' => $nome, 'email' => $email], 'Dados pessoais atualizados');
        voltarComAviso('meus-dados.php', 'Seus dados foram salvos.');
    }

    if ($acao === 'trocar_senha') {
        $atual = (string) ($_POST['senha_atual'] ?? '');
        $nova = (string) ($_POST['senha_nova'] ?? '');
        $repetida = (string) ($_POST['senha_repetida'] ?? '');
        $dadosCompletos = buscarUsuarioPorId($idUsuario);

        if (!password_verify($atual, $dadosCompletos['senha_hash'])) {
            voltarComAviso('meus-dados.php', 'A senha atual está errada.', 'erro');
        }
        if ($nova !== $repetida) {
            voltarComAviso('meus-dados.php', 'A senha nova e a repetição não são iguais.', 'erro');
        }
        $problema = problemaNaSenha($nova, $usuarioAdmin['usuario']);
        if ($problema) {
            voltarComAviso('meus-dados.php', $problema, 'erro');
        }
        if (password_verify($nova, $dadosCompletos['senha_hash'])) {
            voltarComAviso('meus-dados.php', 'A senha nova precisa ser diferente da atual.', 'erro');
        }

        // Troca a senha e derruba as outras sessões; esta continua aberta com a versão nova.
        definirSenhaUsuario($idUsuario, password_hash($nova, PASSWORD_DEFAULT));
        $_SESSION['admin_versao'] = (int) buscarUsuarioPorId($idUsuario)['sessao_versao'];
        session_regenerate_id(true);
        unset($_SESSION['admin_trocar_senha']);
        registrarAuditoria($usuarioAdmin, 'editar', 'usuario', (string) $idUsuario, null, null, 'Senha trocada pelo próprio usuário');
        voltarComAviso('meus-dados.php', 'Senha trocada. Outros aparelhos conectados foram deslogados.');
    }

    if ($acao === 'sair_de_todos') {
        encerrarTodasAsSessoes($idUsuario);
        registrarAuditoria($usuarioAdmin, 'editar', 'usuario', (string) $idUsuario, null, null, 'Saiu de todos os aparelhos');
        encerrarSessaoAdmin();
        header('Location: login.php');
        exit;
    }

    voltarComAviso('meus-dados.php', 'Ação desconhecida.', 'erro');
}

$nomesSetores = array_column(listarSetores(true), 'nome', 'id');

require __DIR__ . '/../../view/admin/meus-dados.php';
