<?php
/**
 * EDITOR DO MAPA DO CAMPUS (admin/editor-mapa.php) — js/mapa-campus.js no modo editor
 *  GET  -> mostra o mapa com o editor (prédios, salas, calçadas, portão, cerca, foto de fundo...)
 *  POST -> recebe o mapa em JSON (aba Salvar › "Salvar no site") e grava em data/mapa-campus.json
 * Enquanto ninguém salvar, o site usa o model/mapa-campus-padrao.json.
 * O cadastro das salas no banco (nome, função, setor, acessibilidade, QR) continua em admin › Salas.
 */
require_once __DIR__ . '/../../model/Config.php';
require_once __DIR__ . '/../../model/Autenticacao.php';
require_once __DIR__ . '/../../model/MapaCampus.php';

$ehPostDoEditor = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
$responder = static function (int $status, array $corpo): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    http_response_code($status);
    echo json_encode($corpo, JSON_UNESCAPED_UNICODE);
    exit;
};
// O "Salvar no site" é um fetch: responde em JSON até quando a sessão expirou
// (o BaseAdmin só sabe mandar pra tela de login, e o editor mostraria um erro sem explicação).
if ($ehPostDoEditor && (!adminEstaLogado() || !ehAdminGeral())) {
    $responder(401, ['ok' => false, 'mensagem' => 'Sua sessão expirou. Entre de novo no painel (suas mudanças continuam guardadas neste navegador).']);
}

require_once __DIR__ . '/BaseAdmin.php';

if ($ehPostDoEditor) {
    $corpo = json_decode((string) file_get_contents('php://input', false, null, 0, 3000000), true);
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? (is_array($corpo) ? ($corpo['csrf_token'] ?? null) : null);
    if (!tokenCsrfValido(is_string($token) ? $token : null)) {
        $responder(403, ['ok' => false, 'mensagem' => 'Não foi possível confirmar o envio. Recarregue a página e tente de novo.']);
    }
    $salvo = salvarMapaCampus(is_array($corpo) ? ($corpo['dados'] ?? null) : null);
    if ($salvo === null) {
        $responder(422, ['ok' => false, 'mensagem' => 'Não foi possível salvar: o mapa está incompleto ou a pasta data/ não pode ser gravada.']);
    }
    $totalSalas = array_sum(array_map(static fn (array $bloco): int => count($bloco['salas']), $salvo['blocos']));
    registrarAuditoria($usuarioAdmin, 'editar', 'mapa_campus', 'mapa', null, null,
        'Mapa do campus salvo (' . count($salvo['blocos']) . ' itens, ' . $totalSalas . ' salas)');
    $responder(200, ['ok' => true, 'dados' => $salvo]);
}

exigirAdminGeral();

$tituloPaginaAdmin = 'Editor do mapa';
$paginaAtualAdmin = 'editor-mapa';
$estilosExtrasAdmin = ['../css/mapa-campus.css'];
$scriptsExtrasAdmin = ['../js/mapa-campus.js'];
$mapaSalvoNoArquivo = is_file(arquivoMapaCampus());

$dadosEditorJson = json_encode([
    'dados' => mapaCampus(),
    'editavel' => true,
    'salvarUrl' => 'editor-mapa.php',
    'csrf' => gerarTokenCsrf(),
    'setores' => listarSetoresParaMapa('../'),
    'scripts3d' => scriptsMapaCampus3d('../'),
    'idioma' => 'pt',
    'chaveLocal' => 'guiaifro-admin-mapa-campus-v3',
    'rascunhosAntigos' => ['guiaifro-mapa-campus-rascunho'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

require __DIR__ . '/../../view/admin/editor-mapa.php';
