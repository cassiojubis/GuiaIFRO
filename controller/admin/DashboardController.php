<?php
require_once __DIR__ . '/BaseAdmin.php';

$tituloPaginaAdmin = 'Dashboard';
$paginaAtualAdmin = 'dashboard';

$contagens = contagensDashboard();
if (ehAdminGeral()) {
    $estatisticasAdmin = [
        ['icone' => 'shapes', 'valor' => $contagens['categorias'], 'rotulo' => 'Categorias ativas', 'cor' => 'green', 'url' => 'categorias.php'],
        ['icone' => 'building-2', 'valor' => $contagens['setores'], 'rotulo' => 'Setores ativos', 'cor' => 'blue', 'url' => 'setores.php'],
        ['icone' => 'newspaper', 'valor' => $contagens['informacoes'], 'rotulo' => 'Informações publicadas', 'cor' => 'amber', 'url' => 'informacoes.php'],
        ['icone' => 'mail', 'valor' => $contagens['mensagens_nao_lidas'], 'rotulo' => 'Mensagens não lidas', 'cor' => 'purple', 'url' => 'contatos.php#mensagens'],
    ];
    $atalhosAdmin = [
        ['icone' => 'plus', 'texto' => 'Adicionar setor', 'url' => 'setores.php'],
        ['icone' => 'megaphone', 'texto' => 'Publicar aviso', 'url' => 'informacoes.php#avisos'],
        ['icone' => 'map', 'texto' => 'Editar o mapa', 'url' => 'mapa.php'],
        ['icone' => 'users', 'texto' => 'Usuários e responsáveis', 'url' => 'usuarios.php'],
        ['icone' => 'globe', 'texto' => 'Visualizar site público', 'url' => '../index.php', 'externo' => true],
    ];
    $alteracoesRecentesAdmin = listarAuditoria(10);
    $setoresSemDadosAdmin = listarSetoresSemDados();
} else {
    $meusSetores = $usuarioAdmin['setores'];
    $estatisticasAdmin = [
        ['icone' => 'building-2', 'valor' => count($meusSetores), 'rotulo' => count($meusSetores) === 1 ? 'Setor sob sua responsabilidade' : 'Setores sob sua responsabilidade', 'cor' => 'blue', 'url' => 'setores.php'],
    ];
    $atalhosAdmin = [
        ['icone' => 'pencil', 'texto' => 'Atualizar meu setor', 'url' => 'setores.php'],
        ['icone' => 'user-cog', 'texto' => 'Meus dados e senha', 'url' => 'meus-dados.php'],
    ];
    $alteracoesRecentesAdmin = [];
    foreach ($meusSetores as $idSetor) {
        $alteracoesRecentesAdmin = array_merge($alteracoesRecentesAdmin, listarAuditoria(5, 'setor', $idSetor));
    }
    usort($alteracoesRecentesAdmin, static function ($a, $b) { return strcmp($b['quando'], $a['quando']); });
    $alteracoesRecentesAdmin = array_slice($alteracoesRecentesAdmin, 0, 10);
    $setoresSemDadosAdmin = [];
}

$iconesAcao = ['criar' => 'plus', 'editar' => 'pencil', 'excluir' => 'trash-2', 'ativar' => 'check-circle-2', 'desativar' => 'circle-slash'];

/** "há 5 minutos", "ontem", "07/10/2026" */
function tempoRelativo(string $quando): string
{
    $segundos = time() - strtotime($quando);
    if ($segundos < 60) {
        return 'agora há pouco';
    }
    if ($segundos < 3600) {
        return 'há ' . textoQuantidade((int) floor($segundos / 60), 'minuto', 'minutos');
    }
    if ($segundos < 86400) {
        return 'há ' . textoQuantidade((int) floor($segundos / 3600), 'hora', 'horas');
    }
    if ($segundos < 172800) {
        return 'ontem';
    }
    return dataHoraBr($quando, false);
}

require __DIR__ . '/../../view/admin/dashboard.php';
