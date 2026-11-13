<?php
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';
require_once __DIR__ . '/../model/Idiomas.php';

$tituloPagina = 'Detalhes do Setor';
$paginaAtual = 'categorias';
// id vem pela URL (setor.php?id=cra), a linha abaixo filtra caracteres estranhos
$idSolicitado = isset($_GET['id']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $_GET['id']) : '';

// Sem id: manda pra lista de setores.
if ($idSolicitado === '') {
    header('Location: categorias.php');
    exit;
}

$setorSelecionado = buscarSetorPorId($idSolicitado);

// Setor que não existe: página 404 com busca (antes caía no CRA sem avisar).
if ($setorSelecionado === null) {
    $termoSugerido = str_replace(['-', '_'], ' ', $idSolicitado);
    require __DIR__ . '/Erro404Controller.php';
    exit;
}

$tituloPagina = $setorSelecionado['nome'];
$faqSetor = listarFaqSetor($setorSelecionado['id']);

// Conteúdo no idioma escolhido (I6); sem tradução publicada, fica o português.
$nomeSetor = traduzirConteudo('setor', $setorSelecionado['id'], 'nome', $setorSelecionado['nome']);
$textoFacil = traduzirConteudo('setor', $setorSelecionado['id'], 'texto_facil', $setorSelecionado['texto_facil']);
$audioDescricao = audioDoConteudo('setor', $setorSelecionado['id'], 'descricao');
// Setor sem sigla (ex.: "Contadoria") usa o próprio nome no lugar.
$siglaSetor = $setorSelecionado['sigla'] !== '' ? $setorSelecionado['sigla'] : $setorSelecionado['nome'];
$descricaoSetor = traduzirConteudo('setor', $setorSelecionado['id'], 'descricao', $setorSelecionado['descricao'] ?: ($setorSelecionado['descricao_curta'] ?: ''));
// Horário: estruturado (por dia) quando existe; senão o texto antigo.
$estadoHorario = estadoHorarioSetor($setorSelecionado);
$abertoAgora = $estadoHorario['aberto'];
$faixasHorario = $estadoHorario['faixas'];
$textoHorario = $faixasHorario ? resumoHorarioSemana($faixasHorario) : (string) $setorSelecionado['horario'];
// Com horário por dia, o texto livre vira só observação.
$observacaoHorario = $faixasHorario ? (string) $setorSelecionado['horario'] : '';

/** "Flávia Rodrigues Durães" -> "FR" (funciona com acento, por isso mb_*) */
function iniciaisDoNome(string $nome, int $quantas): string
{
    $iniciais = '';
    foreach (preg_split('/\s+/u', trim($nome)) as $parte) {
        if ($parte !== '') {
            $iniciais .= mb_strtoupper(mb_substr($parte, 0, 1));
        }
    }
    return mb_substr($iniciais, 0, $quantas);
}

$responsavel = $setorSelecionado['responsavel'];
$inicialResponsavel = $responsavel ? iniciaisDoNome($responsavel, 2) : '';
// vídeo do responsável explicando o setor (ainda não gravado pra nenhum setor -> fica null)
$videoResponsavel = $setorSelecionado['video'] ?? null;
$equipe = array_map(static function ($nome) {
    return ['iniciais' => iniciaisDoNome($nome, 3), 'nome' => $nome, 'cargo' => 'Equipe do setor'];
}, $setorSelecionado['equipe']);
$servicos = $setorSelecionado['servicos'];
$documentos = array_map(static function ($nome) {
    return ['nome' => $nome, 'tamanho' => 'Não informado'];
}, $setorSelecionado['documentos']);

// Onde fica no mapa (pode não ter sala cadastrada ainda).
$salasDoSetor = listarSalasDoSetor($setorSelecionado['id']);
$salaNoMapa = $salasDoSetor[0] ?? null;
$nomesAndares = array_map(static function ($andar) { return $andar['nome']; }, listarMapaSalas()['andares']);
$localNoMapa = $salaNoMapa
    ? ($nomesAndares[$salaNoMapa['andar']] ?? '') . ' · Ala ' . $salaNoMapa['ala']
    : null;

// "Estou aqui" (N2): o QR da porta abre setor.php?id=cra&aqui=<sala>.
$mapaDestinos = listarMapaSalas();
$veioDoQr = isset($_GET['aqui']);
$salaAqui = $salaNoMapa;
foreach ($salasDoSetor as $salaDoSetor) {
    if (($_GET['aqui'] ?? '') === $salaDoSetor['id']) {
        $salaAqui = $salaDoSetor;
    }
}

// Setor sem nenhuma informação cadastrada: a página mostra "informações em breve" em vez de um monte de "Não informado".
$setorSemDados = !$setorSelecionado['descricao'] && !$setorSelecionado['telefone'] && !$setorSelecionado['email']
    && $textoHorario === '' && !$setorSelecionado['localizacao'] && !$responsavel && !$servicos;

// Acessibilidade física do local (I3)
$salaAcess = $salaNoMapa;
$dadosAcess = $salaNoMapa ? buscarAcessibilidadeSala($salaNoMapa['id']) : null;

require __DIR__ . '/../view/setor.php';
