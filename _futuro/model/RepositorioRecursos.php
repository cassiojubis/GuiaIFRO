<?php
/**
 * FUNÇÕES DE DADOS QUE O CÓDIGO JÁ CHAMAVA, MAS NÃO EXISTIAM
 *
 * Correção dos testes de 03/11/2026 (BUG-01, BUG-02 e BUG-04 a BUG-07):
 * as telas de Estatísticas, Quadro de horários, Minha turma, Próxima aula,
 * Primeiro dia, Perguntas recebidas, o contador anônimo (contar.php) e o
 * quiosque da porta davam erro 500 porque estas funções e as tabelas delas
 * (sql/schema.sql, final do arquivo) não existiam.
 *
 * Carregado pelo model/Repositorio.php. Mesmas regras de lá: só aqui se
 * escreve SQL, sempre com prepared statements.
 */

require_once __DIR__ . '/Banco.php';

/* =============================================================
 * ESTATÍSTICAS ANÔNIMAS (N9)
 * ============================================================= */

/** Soma 1 no contador do dia. tipo: pagina, setor, rota, acessibilidade, idioma. */
function contarEstatistica(string $tipo, string $chave): void
{
    $chave = mb_substr(trim($chave), 0, 120);
    if ($chave === '' || !preg_match('/^[a-z]{1,20}$/', $tipo)) {
        return;
    }
    // O service worker baixa as páginas de uma vez pro modo offline: isso não é visita.
    if (!empty($_SERVER['HTTP_X_GUIA_PRECACHE'])) {
        return;
    }
    try {
        executar(
            'INSERT INTO estatisticas_dia (dia, tipo, chave, total) VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE total = total + 1',
            [date('Y-m-d'), $tipo, $chave]
        );
    } catch (Throwable $erro) {
        // Contador nunca pode derrubar a página.
        error_log('Guia IFRO: contador não gravou (' . $erro->getMessage() . ')');
    }
}

/** Guarda o termo buscado (sem saber quem buscou), separando os que não acharam nada. */
function registrarBusca(string $termo, bool $achouAlgo): void
{
    $termo = mb_strtolower(mb_substr(trim(preg_replace('/\s+/u', ' ', $termo)), 0, 100));
    if (mb_strlen($termo) < 2 || !empty($_SERVER['HTTP_X_GUIA_PRECACHE'])) {
        return;
    }
    try {
        executar(
            'INSERT INTO buscas_log (dia, termo, sem_resultado, total) VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE total = total + 1',
            [date('Y-m-d'), $termo, $achouAlgo ? 0 : 1]
        );
    } catch (Throwable $erro) {
        error_log('Guia IFRO: busca não gravou (' . $erro->getMessage() . ')');
    }
}

/** Primeiro dia do período ("últimos N dias" conta hoje). */
function inicioDoPeriodo(int $dias): string
{
    return (new DateTimeImmutable('today'))->modify('-' . max(0, $dias - 1) . ' days')->format('Y-m-d');
}

/** [ 'AAAA-MM-DD' => total ] de todos os dias do período, inclusive os sem acesso (0). */
function totalPorDia(string $tipo, int $dias): array
{
    $inicio = inicioDoPeriodo($dias);
    $porDia = [];
    $data = new DateTimeImmutable($inicio);
    for ($i = 0; $i < $dias; $i++) {
        $porDia[$data->format('Y-m-d')] = 0;
        $data = $data->modify('+1 day');
    }
    $linhas = consultar(
        'SELECT dia, SUM(total) AS total FROM estatisticas_dia WHERE tipo = ? AND dia >= ? GROUP BY dia',
        [$tipo, $inicio]
    );
    foreach ($linhas as $linha) {
        if (isset($porDia[$linha['dia']])) {
            $porDia[$linha['dia']] = (int) $linha['total'];
        }
    }
    return $porDia;
}

/** Mais acessados do tipo no período: [['chave' => ..., 'total' => ...], ...] */
function rankingEstatistica(string $tipo, int $dias, int $limite = 10): array
{
    return array_map(static function (array $linha) {
        return ['chave' => $linha['chave'], 'total' => (int) $linha['total']];
    }, consultar(
        'SELECT chave, SUM(total) AS total FROM estatisticas_dia
          WHERE tipo = ? AND dia >= ?
          GROUP BY chave ORDER BY total DESC, chave LIMIT ' . max(1, min(100, $limite)),
        [$tipo, inicioDoPeriodo($dias)]
    ));
}

/** Termos mais buscados (ou os que não acharam nada) no período. */
function rankingBuscas(int $dias, bool $soSemResultado, int $limite = 15): array
{
    return array_map(static function (array $linha) {
        return ['chave' => $linha['chave'], 'total' => (int) $linha['total']];
    }, consultar(
        'SELECT termo AS chave, SUM(total) AS total FROM buscas_log
          WHERE dia >= ? AND sem_resultado = ?
          GROUP BY termo ORDER BY total DESC, termo LIMIT ' . max(1, min(100, $limite)),
        [inicioDoPeriodo($dias), $soSemResultado ? 1 : 0]
    ));
}

/** Tudo do período pra planilha: dia, tipo, chave, quantidade (buscas entram como tipo "busca"). */
function exportarEstatisticas(int $dias): array
{
    $inicio = inicioDoPeriodo($dias);
    return array_map(static function (array $linha) {
        return ['dia' => $linha['dia'], 'tipo' => $linha['tipo'], 'chave' => $linha['chave'], 'quantidade' => (int) $linha['quantidade']];
    }, consultar(
        "SELECT dia, tipo, chave, total AS quantidade FROM estatisticas_dia WHERE dia >= ?
         UNION ALL
         SELECT dia, IF(sem_resultado = 1, 'busca-sem-resultado', 'busca') AS tipo, termo AS chave, total AS quantidade
           FROM buscas_log WHERE dia >= ?
         ORDER BY dia, tipo, chave",
        [$inicio, $inicio]
    ));
}

/* =============================================================
 * QUADRO DE HORÁRIOS (N13)
 * ============================================================= */

/** Grava o CSV lido como uma versão nova (ainda não publicada). Devolve o id. */
function salvarVersaoQuadro(string $nomeArquivo, string $descricao, string $enviadoPor, array $aulas): int
{
    $pdo = banco();
    $pdo->beginTransaction();
    try {
        executar(
            'INSERT INTO quadro_versoes (nome_arquivo, descricao, enviado_por, total_aulas) VALUES (?, ?, ?, ?)',
            [mb_substr($nomeArquivo, 0, 255), $descricao !== '' ? $descricao : null, $enviadoPor, count($aulas)]
        );
        $idVersao = (int) $pdo->lastInsertId();
        foreach ($aulas as $aula) {
            executar(
                'INSERT INTO quadro_aulas (versao_id, turma, dia_semana, inicio, fim, disciplina, professor, sala_texto, sala_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$idVersao, $aula['turma'], (int) $aula['dia_semana'], $aula['inicio'], $aula['fim'], $aula['disciplina'],
                    $aula['professor'] ?? null, $aula['sala_texto'] ?? null, $aula['sala_id'] ?? null]
            );
        }
        $pdo->commit();
        return $idVersao;
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

function listarVersoesQuadro(): array
{
    return consultar('SELECT * FROM quadro_versoes ORDER BY enviado_em DESC, id DESC');
}

function buscarVersaoQuadro(int $id): ?array
{
    return $id > 0 ? consultarUm('SELECT * FROM quadro_versoes WHERE id = ?', [$id]) : null;
}

function versaoQuadroPublicada(): ?array
{
    return consultarUm('SELECT * FROM quadro_versoes WHERE publicada = 1 ORDER BY publicada_em DESC LIMIT 1');
}

/** Só uma versão fica publicada: publicar uma tira a anterior. */
function publicarVersaoQuadro(int $id): void
{
    $pdo = banco();
    $pdo->beginTransaction();
    try {
        executar('UPDATE quadro_versoes SET publicada = 0 WHERE publicada = 1');
        executar('UPDATE quadro_versoes SET publicada = 1, publicada_em = NOW() WHERE id = ?', [$id]);
        $pdo->commit();
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

/** Apaga uma versão (as aulas vão junto). O controller já impede apagar a publicada. */
function excluirVersaoQuadro(int $id): void
{
    executar('DELETE FROM quadro_versoes WHERE id = ? AND publicada = 0', [$id]);
}

/** Campos de hora como "07:00" (as telas comparam com date('H:i')). */
const CAMPOS_AULA = "id, turma, dia_semana, DATE_FORMAT(inicio, '%H:%i') AS inicio, DATE_FORMAT(fim, '%H:%i') AS fim,
                     disciplina, professor, sala_texto, sala_id";

function aulasDaVersao(int $idVersao): array
{
    return consultar('SELECT ' . CAMPOS_AULA . ' FROM quadro_aulas WHERE versao_id = ? ORDER BY turma, dia_semana, inicio', [$idVersao]);
}

/** Turmas do quadro publicado (lista vazia se nada foi publicado ainda). */
function listarTurmasPublicadas(): array
{
    return array_column(consultar(
        'SELECT DISTINCT a.turma FROM quadro_aulas a JOIN quadro_versoes v ON v.id = a.versao_id
          WHERE v.publicada = 1 ORDER BY a.turma'
    ), 'turma');
}

/** Aulas de uma turma num dia da semana (0 = domingo), do quadro publicado. */
function aulasDaTurmaNoDia(string $turma, int $diaSemana): array
{
    return consultar(
        'SELECT ' . str_replace(['turma,', 'id,'], ['a.turma,', 'a.id,'], CAMPOS_AULA) . '
           FROM quadro_aulas a JOIN quadro_versoes v ON v.id = a.versao_id
          WHERE v.publicada = 1 AND a.turma = ? AND a.dia_semana = ?
          ORDER BY a.inicio',
        [$turma, $diaSemana]
    );
}

/* =============================================================
 * PERGUNTE AO SETOR
 * ============================================================= */

/** Grava a pergunta como pendente. A mesma pergunta repetida no mesmo setor só soma em "vezes". */
function registrarPerguntaSetor(string $idSetor, string $pergunta, string $email): void
{
    $existente = consultarUm(
        "SELECT id FROM perguntas_setor WHERE setor_id = ? AND status = 'pendente' AND pergunta = ? LIMIT 1",
        [$idSetor, $pergunta]
    );
    if ($existente) {
        executar(
            'UPDATE perguntas_setor SET vezes = vezes + 1, ultima_em = NOW(), email = COALESCE(email, ?) WHERE id = ?',
            [$email !== '' ? $email : null, (int) $existente['id']]
        );
        return;
    }
    executar(
        'INSERT INTO perguntas_setor (setor_id, pergunta, email) VALUES (?, ?, ?)',
        [$idSetor, $pergunta, $email !== '' ? $email : null]
    );
}

/** Pendentes dos setores informados (null = todos, pro admin geral). */
function listarPerguntasPendentes(?array $idsSetores = null): array
{
    if ($idsSetores !== null && !$idsSetores) {
        return [];
    }
    $filtro = '';
    $parametros = [];
    if ($idsSetores !== null) {
        $filtro = ' AND p.setor_id IN (' . implode(',', array_fill(0, count($idsSetores), '?')) . ')';
        $parametros = array_values($idsSetores);
    }
    return consultar(
        "SELECT p.*, s.nome AS setor_nome FROM perguntas_setor p JOIN setores s ON s.id = p.setor_id
          WHERE p.status = 'pendente'{$filtro}
          ORDER BY p.vezes DESC, p.ultima_em DESC",
        $parametros
    );
}

function buscarPerguntaSetor(int $id): ?array
{
    return $id > 0 ? consultarUm('SELECT * FROM perguntas_setor WHERE id = ?', [$id]) : null;
}

/** Publica a resposta no FAQ do setor e fecha a pergunta. Devolve o id do FAQ. */
function responderPerguntaSetor(int $id, string $pergunta, string $resposta, string $quem): int
{
    $original = buscarPerguntaSetor($id);
    if (!$original) {
        throw new RuntimeException('Pergunta não encontrada.');
    }
    $pdo = banco();
    $pdo->beginTransaction();
    try {
        $ordem = (int) consultarValor('SELECT COALESCE(MAX(ordem), 0) + 1 FROM faq');
        executar('INSERT INTO faq (pergunta, resposta, publicado, ordem) VALUES (?, ?, 1, ?)', [$pergunta, $resposta, $ordem]);
        $idFaq = (int) $pdo->lastInsertId();
        executar('INSERT INTO faq_setor (faq_id, setor_id) VALUES (?, ?)', [$idFaq, $original['setor_id']]);
        executar(
            "UPDATE perguntas_setor SET status = 'respondida', faq_id = ?, tratada_por = ?, tratada_em = NOW() WHERE id = ?",
            [$idFaq, $quem, $id]
        );
        $pdo->commit();
        return $idFaq;
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

function descartarPerguntaSetor(int $id, string $quem): void
{
    executar(
        "UPDATE perguntas_setor SET status = 'descartada', tratada_por = ?, tratada_em = NOW() WHERE id = ? AND status = 'pendente'",
        [$quem, $id]
    );
}

/* =============================================================
 * INFORMAR ERRO NA PÁGINA DO SETOR
 * ============================================================= */

/** Campos que o visitante pode apontar como errados. */
function camposReporteErro(): array
{
    return [
        'telefone' => 'Telefone',
        'email' => 'E-mail',
        'horario' => 'Horário de atendimento',
        'localizacao' => 'Localização / sala',
        'responsavel' => 'Responsável',
        'servicos' => 'Serviços ou documentos',
        'outro' => 'Outra informação',
    ];
}

function salvarReporteErro(string $idSetor, string $campo, string $correto, string $email): void
{
    executar(
        'INSERT INTO reportes_erro (setor_id, campo, correto, email) VALUES (?, ?, ?, ?)',
        [$idSetor, $campo, $correto, $email !== '' ? $email : null]
    );
}

/* =============================================================
 * PRIMEIRO DIA NO CAMPUS (N12)
 * ============================================================= */

/** Paradas na ordem do roteiro, com a sala do setor (pra "Como chegar"). */
function listarParadasPrimeiroDia(bool $incluirOcultas = false): array
{
    $paradas = consultar(
        'SELECT * FROM paradas_primeiro_dia WHERE publicado = 1 OR ? = 1 ORDER BY ordem, id',
        [$incluirOcultas ? 1 : 0]
    );
    foreach ($paradas as &$parada) {
        $sala = $parada['setor_id'] ? (listarSalasDoSetor($parada['setor_id'])[0] ?? null) : null;
        $parada['sala_id'] = $sala['id'] ?? null;
    }
    unset($parada);
    return $paradas;
}

function buscarParadaPrimeiroDia(int $id): ?array
{
    return $id > 0 ? consultarUm('SELECT * FROM paradas_primeiro_dia WHERE id = ?', [$id]) : null;
}

/** Parada publicada cujo QR foi escaneado (?selo=codigo). */
function buscarParadaPeloSelo(string $codigo): ?array
{
    return consultarUm('SELECT * FROM paradas_primeiro_dia WHERE codigo_selo = ? AND publicado = 1', [$codigo]);
}

/** Cria (com código de selo aleatório) ou atualiza. Devolve o id. */
function salvarParadaPrimeiroDia(?int $id, array $dados): int
{
    $valores = [
        $dados['titulo'], $dados['setor_id'] !== '' ? $dados['setor_id'] : null, $dados['o_que_fazer'],
        $dados['curiosidade'] !== '' ? $dados['curiosidade'] : null, $dados['publicado'] ? 1 : 0,
    ];
    if ($id) {
        executar(
            'UPDATE paradas_primeiro_dia SET titulo = ?, setor_id = ?, o_que_fazer = ?, curiosidade = ?, publicado = ? WHERE id = ?',
            array_merge($valores, [$id])
        );
        return $id;
    }
    $ordem = (int) consultarValor('SELECT COALESCE(MAX(ordem), -1) + 1 FROM paradas_primeiro_dia');
    executar(
        'INSERT INTO paradas_primeiro_dia (titulo, setor_id, o_que_fazer, curiosidade, publicado, codigo_selo, ordem)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        array_merge($valores, [bin2hex(random_bytes(8)), $ordem])
    );
    return (int) banco()->lastInsertId();
}

function excluirParadaPrimeiroDia(int $id): void
{
    executar('DELETE FROM paradas_primeiro_dia WHERE id = ?', [$id]);
}

/* =============================================================
 * AVISO DE REVISÃO DOS SETORES (bin/avisar-revisao.php)
 * ============================================================= */

/**
 * Setores ativos sem atualização há mais de $dias dias e que não receberam
 * aviso nesse período. Cada um vem com 'revisado_em' e 'responsaveis' (com e-mail).
 */
function listarAvisosDeRevisaoPendentes(int $dias): array
{
    $limite = (new DateTimeImmutable('now'))->modify('-' . max(1, $dias) . ' days')->format('Y-m-d H:i:s');
    $setores = consultar(
        'SELECT id, nome, sigla, atualizado_em AS revisado_em FROM setores
          WHERE ativo = 1 AND atualizado_em < ? AND (aviso_revisao_em IS NULL OR aviso_revisao_em < ?)
          ORDER BY nome',
        [$limite, $limite]
    );
    foreach ($setores as &$setor) {
        $setor['responsaveis'] = array_values(array_filter(listarResponsaveisDoSetor($setor['id']), static function ($pessoa) {
            return !empty($pessoa['email']);
        }));
    }
    unset($setor);
    return $setores;
}

function marcarAvisoDeRevisaoEnviado(string $idSetor): void
{
    executar('UPDATE setores SET aviso_revisao_em = NOW() WHERE id = ?', [$idSetor]);
}
