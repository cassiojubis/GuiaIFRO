<?php
/**
 * CAMADA DE ACESSO A DADOS (Model)
 *
 * Único lugar do site que fala com o banco. Controllers e views só chamam
 * estas funções (nunca escrevem SQL). Tudo com prepared statements
 * (ver consultar()/executar() em model/Banco.php).
 */

require_once __DIR__ . '/Banco.php';
require_once __DIR__ . '/Horario.php';

/* =============================================================
 * NOMES DO BANCO
 * As tabelas e colunas seguem o padrão pedido pelo professor (tabela no
 * singular, coluna com o prefixo da tabela: seto_nome, cate_id...).
 * O resto do site (controllers e views) continua usando os nomes curtos
 * ('id', 'nome', 'sigla'...): cada SELECT daqui devolve as colunas com
 * esses apelidos (AS). Esta lista é usada nos SELECT * e nas telas que
 * montam o SQL a partir de uma lista de campos.
 * ============================================================= */

const COLUNAS_BANCO = [
    'categoria' => ['cate_id' => 'id', 'cate_nome' => 'nome', 'cate_icone' => 'icone', 'cate_cor' => 'cor', 'cate_ordem' => 'ordem', 'cate_ativo' => 'ativo', 'cate_atualizado_em' => 'atualizado_em'],
    'setor' => [
        'seto_id' => 'id', 'cate_id' => 'categoria_id', 'seto_nome' => 'nome', 'seto_sigla' => 'sigla', 'seto_icone' => 'icone', 'seto_cor' => 'cor',
        'seto_descricao' => 'descricao', 'seto_descricao_curta' => 'descricao_curta', 'seto_texto_facil' => 'texto_facil', 'seto_localizacao' => 'localizacao',
        'seto_bloco' => 'bloco', 'seto_sala_texto' => 'sala_texto', 'seto_telefone' => 'telefone', 'seto_email' => 'email', 'seto_responsavel' => 'responsavel',
        'seto_video' => 'video', 'seto_horario_texto' => 'horario_texto', 'seto_ordem' => 'ordem', 'seto_ativo' => 'ativo', 'seto_fila_status' => 'fila_status',
        'seto_fila_atualizada_em' => 'fila_atualizada_em', 'seto_atualizado_em' => 'atualizado_em', 'seto_atualizado_por' => 'atualizado_por',
    ],
    'setor_excecao' => ['exce_id' => 'id', 'seto_id' => 'setor_id', 'exce_data' => 'data', 'exce_fechado' => 'fechado', 'exce_abre' => 'abre', 'exce_fecha' => 'fecha', 'exce_motivo' => 'motivo'],
    'sala' => [
        'sala_id' => 'id', 'sala_nome' => 'nome', 'sala_nome_curto' => 'nome_curto', 'sala_funcao' => 'funcao', 'anda_id' => 'andar_id', 'sala_ala' => 'ala',
        'sala_ordem' => 'ordem', 'sala_lado' => 'lado', 'sala_ponta' => 'ponta', 'sala_vazia' => 'vazia', 'seto_id' => 'setor_id',
    ],
    'acessibilidade_sala' => [
        'sala_id' => 'sala_id', 'acess_rampa' => 'rampa', 'acess_elevador' => 'elevador', 'acess_banheiro_adaptado' => 'banheiro_adaptado',
        'acess_balcao_baixo' => 'balcao_baixo', 'acess_piso_tatil' => 'piso_tatil', 'acess_porta_larga' => 'porta_larga', 'acess_observacao' => 'observacao',
        'acess_atualizado_em' => 'atualizado_em',
    ],
    'aviso' => ['avis_id' => 'id', 'avis_titulo' => 'titulo', 'avis_mensagem' => 'mensagem', 'avis_nivel' => 'nivel', 'avis_inicio' => 'inicio', 'avis_fim' => 'fim', 'avis_publicado' => 'publicado', 'avis_ordem' => 'ordem', 'avis_criado_em' => 'criado_em'],
    'noticia' => [
        'noti_id' => 'id', 'noti_slug' => 'slug', 'noti_titulo' => 'titulo', 'noti_resumo' => 'resumo', 'noti_conteudo' => 'conteudo', 'noti_categoria' => 'categoria',
        'noti_publicada_em' => 'publicada_em', 'noti_destaque' => 'destaque', 'noti_publicado' => 'publicado', 'noti_ordem' => 'ordem',
    ],
    'faq' => ['faq_id' => 'id', 'faq_pergunta' => 'pergunta', 'faq_resposta' => 'resposta', 'faq_publicado' => 'publicado', 'faq_ordem' => 'ordem', 'faq_atualizado_em' => 'atualizado_em'],
    'link_util' => ['link_id' => 'id', 'link_titulo' => 'titulo', 'link_url' => 'url', 'link_publicado' => 'publicado', 'link_ordem' => 'ordem'],
    'texto_institucional' => ['text_id' => 'id', 'text_titulo' => 'titulo', 'text_conteudo' => 'conteudo', 'text_publicado' => 'publicado', 'text_ordem' => 'ordem'],
    'usuario' => [
        'usua_id' => 'id', 'usua_nome' => 'nome', 'usua_usuario' => 'usuario', 'usua_email' => 'email', 'usua_senha_hash' => 'senha_hash', 'perf_id' => 'perfil_id',
        'usua_ativo' => 'ativo', 'usua_sessao_versao' => 'sessao_versao', 'usua_ultimo_login_em' => 'ultimo_login_em', 'usua_criado_em' => 'criado_em',
    ],
    'contato_mensagem' => [
        'cont_id' => 'id', 'cont_nome' => 'nome', 'cont_email' => 'email', 'cont_telefone' => 'telefone', 'cont_assunto' => 'assunto', 'cont_mensagem' => 'mensagem',
        'cont_email_enviado' => 'email_enviado', 'cont_criada_em' => 'criada_em', 'cont_lida_em' => 'lida_em',
    ],
    'traducao' => [
        'trad_id' => 'id', 'trad_entidade' => 'entidade', 'trad_entidade_id' => 'entidade_id', 'trad_campo' => 'campo', 'trad_idioma' => 'idioma', 'trad_texto' => 'texto',
        'trad_status' => 'status', 'trad_validado_por' => 'validado_por', 'trad_validado_em' => 'validado_em', 'trad_audio_url' => 'audio_url',
        'trad_pictograma' => 'pictograma', 'trad_atualizado_em' => 'atualizado_em',
    ],
    'fluxo' => [
        'flux_id' => 'id', 'flux_slug' => 'slug', 'flux_titulo' => 'titulo', 'flux_resumo' => 'resumo', 'seto_id' => 'setor_id', 'flux_canal' => 'canal',
        'flux_prazo' => 'prazo', 'flux_publicado' => 'publicado', 'flux_ordem' => 'ordem', 'flux_atualizado_em' => 'atualizado_em',
    ],
];

/**
 * "s.seto_id AS id, s.seto_nome AS nome, ..." — todas as colunas da tabela com o
 * nome curto que o PHP usa. Substitui o SELECT * ($tabela vem sempre do código).
 */
function colunasComApelido(string $tabela, string $prefixo = ''): string
{
    $partes = [];
    foreach (COLUNAS_BANCO[$tabela] as $coluna => $apelido) {
        $partes[] = ($prefixo !== '' ? $prefixo . '.' : '') . $coluna . ' AS ' . $apelido;
    }
    return implode(', ', $partes);
}

/** Nome da coluna no banco a partir do nome curto: colunaDoBanco('setor', 'nome') = 'seto_nome'. */
function colunaDoBanco(string $tabela, string $campo): string
{
    $coluna = array_search($campo, COLUNAS_BANCO[$tabela], true);
    if ($coluna === false) {
        throw new InvalidArgumentException("Campo {$campo} não existe em {$tabela}.");
    }
    return $coluna;
}

/* =============================================================
 * SETORES E CATEGORIAS
 * ============================================================= */

/** Categorias de verdade (grupos do organograma), na ordem do admin. */
function listarGruposCategorias(bool $incluirInativas = false): array
{
    return consultar(
        'SELECT ' . colunasComApelido('categoria', 'c') . ',
                (SELECT COUNT(*) FROM setor s WHERE s.cate_id = c.cate_id AND s.seto_ativo = 1) AS total_setores
           FROM categoria c
          WHERE c.cate_ativo = 1 OR :todas = 1
          ORDER BY c.cate_ordem, c.cate_nome',
        ['todas' => $incluirInativas ? 1 : 0]
    );
}

function buscarGrupoCategoria(string $id): ?array
{
    return consultarUm('SELECT ' . colunasComApelido('categoria') . ' FROM categoria WHERE cate_id = ?', [$id]);
}

/**
 * Cards da página Categorias e da home: um card por SETOR, com o grupo em 'tag'.
 * (O nome "listarCategorias" ficou de quando cada item do organograma era
 * chamado de categoria.) Todo setor ativo tem página, então todos têm url_setor.
 */
function listarCategorias(): array
{
    $linhas = consultar(
        'SELECT s.seto_id AS id, s.seto_icone AS icone, s.seto_nome AS nome, s.seto_sigla AS sigla, s.seto_cor AS cor,
                s.seto_descricao AS descricao, s.seto_descricao_curta AS descricao_curta, c.cate_nome AS grupo
           FROM setor s
           LEFT JOIN categoria c ON c.cate_id = s.cate_id
          WHERE s.seto_ativo = 1 AND (c.cate_id IS NULL OR c.cate_ativo = 1)
          ORDER BY c.cate_ordem, s.seto_ordem, s.seto_nome'
    );

    return array_map(static function (array $setor) {
        $titulo = $setor['sigla'] !== '' && stripos($setor['nome'], $setor['sigla']) === false
            ? $setor['sigla'] . ' – ' . $setor['nome']
            : $setor['nome'];
        $descricao = $setor['descricao_curta'] ?: ($setor['descricao'] ?: 'Informações em breve.');
        return [
            'id' => $setor['id'],
            'setor_id' => $setor['id'],
            'icone' => $setor['icone'],
            'titulo' => $titulo,
            'descricaoCurta' => $descricao,
            'descricao' => $descricao,
            'tag' => $setor['grupo'] ?? 'Outros',
            'cor' => $setor['cor'],
            'setores' => [$setor['nome'], $setor['sigla']],
            'url_setor' => 'setor.php?id=' . rawurlencode($setor['id']),
        ];
    }, $linhas);
}

/** Card de um setor (mesmo formato de listarCategorias), ou null. */
function buscarCategoriaPorId(string $id): ?array
{
    foreach (listarCategorias() as $categoria) {
        if ($categoria['id'] === $id) {
            return $categoria;
        }
    }
    return null;
}

/** Setores ativos, com os campos básicos (busca ao vivo, sitemap, listas). */
function listarSetores(bool $incluirInativos = false): array
{
    return consultar(
        'SELECT s.seto_id AS id, s.seto_nome AS nome, s.seto_sigla AS sigla, s.seto_icone AS icone, s.seto_cor AS cor,
                s.cate_id AS categoria_id, c.cate_nome AS categoria_nome,
                s.seto_descricao AS descricao, s.seto_descricao_curta AS descricao_curta, s.seto_localizacao AS localizacao,
                s.seto_bloco AS bloco, s.seto_sala_texto AS sala, s.seto_telefone AS telefone, s.seto_email AS email,
                s.seto_responsavel AS responsavel, s.seto_horario_texto AS horario,
                s.seto_ativo AS ativo, s.seto_ordem AS ordem, s.seto_atualizado_em AS atualizado_em, s.seto_atualizado_por AS atualizado_por
           FROM setor s
           LEFT JOIN categoria c ON c.cate_id = s.cate_id
          WHERE s.seto_ativo = 1 OR :todos = 1
          ORDER BY c.cate_ordem, s.seto_ordem, s.seto_nome',
        ['todos' => $incluirInativos ? 1 : 0]
    );
}

/**
 * Setor completo pra página do setor (com equipe, serviços e documentos), ou null.
 * Setor desativado no admin some do site (devolve null = página 404).
 */
function buscarSetorPorId(string $id, bool $incluirInativo = false): ?array
{
    $setor = consultarUm(
        'SELECT ' . colunasComApelido('setor', 's') . ', s.seto_sala_texto AS sala, s.seto_horario_texto AS horario,
                s.cate_id AS categoria, c.cate_nome AS categoria_nome
           FROM setor s
           LEFT JOIN categoria c ON c.cate_id = s.cate_id
          WHERE s.seto_id = ?',
        [$id]
    );
    if ($setor === null || (!$incluirInativo && !(int) $setor['ativo'])) {
        return null;
    }

    $setor['equipe'] = array_column(consultar('SELECT equi_nome AS nome FROM setor_equipe WHERE seto_id = ? ORDER BY equi_ordem, equi_id', [$id]), 'nome');
    $setor['servicos'] = array_column(consultar('SELECT serv_nome AS nome FROM setor_servico WHERE seto_id = ? ORDER BY serv_ordem, serv_id', [$id]), 'nome');
    $setor['documentos'] = array_column(consultar('SELECT docu_nome AS nome FROM setor_documento WHERE seto_id = ? ORDER BY docu_ordem, docu_id', [$id]), 'nome');
    $setor['sinonimos'] = array_column(consultar('SELECT sino_nome AS nome FROM sinonimo WHERE seto_id = ? ORDER BY sino_ordem, sino_id', [$id]), 'nome');
    return $setor;
}

/* =============================================================
 * HOME E CONTEÚDO
 * ============================================================= */

function listarAtalhosRapidos(): array
{
    return consultar(
        'SELECT atal_titulo AS titulo, atal_descricao AS descricao, atal_icone AS icone, atal_cor AS cor, atal_url AS url
           FROM atalho_home ORDER BY atal_ordem, atal_id'
    );
}

/** "2026-05-24" -> "24 mai 2026" */
function formatarDataCurta(string $data): string
{
    $meses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
    $momento = new DateTimeImmutable($data);
    return $momento->format('j') . ' ' . $meses[(int) $momento->format('n') - 1] . ' ' . $momento->format('Y');
}

/** Notícias publicadas, mais novas primeiro. */
function listarNoticias(int $limite = 6): array
{
    $linhas = consultar(
        'SELECT noti_id AS id, noti_slug AS slug, noti_titulo AS titulo, noti_resumo AS resumo, noti_categoria AS categoria,
                noti_publicada_em AS publicada_em, noti_destaque AS destaque
           FROM noticia
          WHERE noti_publicado = 1
          ORDER BY noti_publicada_em DESC, noti_ordem, noti_id DESC
          LIMIT ' . max(1, $limite)
    );
    return array_map(static function (array $noticia) {
        $noticia['data'] = formatarDataCurta($noticia['publicada_em']);
        $noticia['urgente'] = (bool) $noticia['destaque'];
        return $noticia;
    }, $linhas);
}

/* =============================================================
 * MAPA
 * ============================================================= */

/**
 * Andares e salas do prédio principal (tabela sala): ficha, QR, placa, mapa em texto
 * e busca. Cada sala ganha 'url_setor' quando tem setor com página.
 */
function listarMapaSalas(): array
{
    $andares = [];
    foreach (consultar('SELECT anda_id AS id, anda_nome AS nome FROM andar ORDER BY anda_ordem') as $andar) {
        $andares[$andar['id']] = ['nome' => $andar['nome']];
    }

    $salas = array_map(static function (array $sala) {
        $temSetor = $sala['setor_id'] !== null && (int) $sala['setor_ativo'] === 1;
        return [
            'id' => $sala['id'],
            'nome' => $sala['nome'],
            'nomeCurto' => $sala['nome_curto'],
            'funcao' => $sala['funcao'],
            'andar' => $sala['andar_id'],
            'ala' => $sala['ala'],
            'ordem' => (int) $sala['ordem'],
            'lado' => $sala['lado'],
            'ponta' => (bool) $sala['ponta'],
            'vazia' => (bool) $sala['vazia'],
            'setor_id' => $temSetor ? $sala['setor_id'] : null,
            'setor_id_cadastrado' => $sala['setor_id'], // mesmo se o setor estiver desativado (pro editor)
            'url_setor' => $temSetor ? 'setor.php?id=' . rawurlencode($sala['setor_id']) : null,
            // Sala sem setor (banheiro, copa, auditório...) ganha uma ficha simples.
            'url_ficha' => 'sala.php?id=' . rawurlencode($sala['id']),
        ];
    }, consultar(
        'SELECT ' . colunasComApelido('sala', 'sa') . ', se.seto_ativo AS setor_ativo
           FROM sala sa
           LEFT JOIN setor se ON se.seto_id = sa.seto_id
          ORDER BY sa.anda_id, sa.sala_ala, sa.sala_ordem'
    ));

    return ['andares' => $andares, 'salas' => $salas];
}

function buscarSalaDoMapa(string $id): ?array
{
    foreach (listarMapaSalas()['salas'] as $sala) {
        if ($sala['id'] === $id) {
            return $sala;
        }
    }
    return null;
}

/** Salas do mapa ligadas a um setor (normalmente uma só). */
function listarSalasDoSetor(string $idSetor): array
{
    return array_values(array_filter(listarMapaSalas()['salas'], static function ($sala) use ($idSetor) {
        return $sala['setor_id'] === $idSetor;
    }));
}

/* =============================================================
 * FAQ
 * ============================================================= */

/** Perguntas frequentes de um setor (só as publicadas). Sem setor = FAQ geral. */
function listarFaqSetor(?string $idSetor = null): array
{
    if ($idSetor === null) {
        return consultar(
            'SELECT f.faq_id AS id, f.faq_pergunta AS pergunta, f.faq_resposta AS resposta FROM faq f
              WHERE f.faq_publicado = 1 AND NOT EXISTS (SELECT 1 FROM faq_setor fs WHERE fs.faq_id = f.faq_id)
              ORDER BY f.faq_ordem, f.faq_id'
        );
    }
    return consultar(
        'SELECT f.faq_id AS id, f.faq_pergunta AS pergunta, f.faq_resposta AS resposta FROM faq f
           JOIN faq_setor fs ON fs.faq_id = f.faq_id
          WHERE fs.seto_id = ? AND f.faq_publicado = 1
          ORDER BY f.faq_ordem, f.faq_id',
        [$idSetor]
    );
}

/* =============================================================
 * CONFIGURAÇÕES (contato geral, redes sociais...)
 * ============================================================= */

/** Todas as configurações como [chave => valor]. Lidas uma vez por requisição. */
function listarConfiguracoes(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (consultar('SELECT conf_chave AS chave, conf_valor AS valor FROM configuracao') as $linha) {
            $cache[$linha['chave']] = (string) $linha['valor'];
        }
    }
    return $cache;
}

function configuracao(string $chave, string $padrao = ''): string
{
    $valor = listarConfiguracoes()[$chave] ?? '';
    return $valor !== '' ? $valor : $padrao;
}

/* =============================================================
 * LIMITE DE ENVIOS (contato e login)
 * Guarda só o hash da chave (IP com sal), nunca o IP.
 * ============================================================= */

/** Já passou do limite? Ex.: limiteAtingido('contato', $hash, 3, 3600) = 3 por hora. */
function limiteAtingido(string $acao, string $chaveHash, int $maximo, int $janelaSegundos): bool
{
    $quantidade = (int) consultarValor(
        'SELECT COUNT(*) FROM limite WHERE limi_acao = ? AND limi_chave_hash = ? AND limi_criado_em > (NOW() - INTERVAL ? SECOND)',
        [$acao, $chaveHash, $janelaSegundos]
    );
    return $quantidade >= $maximo;
}

function registrarNoLimite(string $acao, string $chaveHash): void
{
    executar('INSERT INTO limite (limi_acao, limi_chave_hash) VALUES (?, ?)', [$acao, $chaveHash]);
    // De vez em quando limpa o que tem mais de 1 dia (não precisa guardar).
    if (random_int(1, 50) === 1) {
        executar('DELETE FROM limite WHERE limi_criado_em < (NOW() - INTERVAL 1 DAY)');
    }
}

function limparLimite(string $acao, string $chaveHash): void
{
    executar('DELETE FROM limite WHERE limi_acao = ? AND limi_chave_hash = ?', [$acao, $chaveHash]);
}

/* =============================================================
 * MENSAGENS DO FORMULÁRIO DE CONTATO
 * ============================================================= */

/** Assuntos aceitos no formulário (o servidor recusa qualquer outro). */
function listarAssuntosContato(): array
{
    return [
        'Dúvida sobre matrícula',
        'Assistência estudantil',
        'Biblioteca',
        'Estágio e SUAP',
        'Solicitação de documentos',
        'Dúvida para um setor',
        'Informação errada no site',
        'Sugestão ou elogio',
        'Outro assunto',
    ];
}

/** Grava a mensagem e devolve o id. */
function salvarMensagemContato(array $dados): int
{
    executar(
        'INSERT INTO contato_mensagem (cont_nome, cont_email, cont_telefone, cont_assunto, cont_mensagem) VALUES (?, ?, ?, ?, ?)',
        [$dados['nome'], $dados['email'], $dados['telefone'] !== '' ? $dados['telefone'] : null, $dados['assunto'], $dados['mensagem']]
    );
    return (int) banco()->lastInsertId();
}

function marcarEmailEnviado(int $idMensagem): void
{
    executar('UPDATE contato_mensagem SET cont_email_enviado = 1 WHERE cont_id = ?', [$idMensagem]);
}

function listarMensagensContato(int $limite = 100): array
{
    return consultar('SELECT ' . colunasComApelido('contato_mensagem') . ' FROM contato_mensagem ORDER BY cont_criada_em DESC, cont_id DESC LIMIT ' . max(1, $limite));
}

function contarMensagensNaoLidas(): int
{
    return (int) consultarValor('SELECT COUNT(*) FROM contato_mensagem WHERE cont_lida_em IS NULL');
}

function marcarMensagemLida(int $id, bool $lida): void
{
    executar('UPDATE contato_mensagem SET cont_lida_em = ' . ($lida ? 'NOW()' : 'NULL') . ' WHERE cont_id = ?', [$id]);
}

function excluirMensagemContato(int $id): void
{
    executar('DELETE FROM contato_mensagem WHERE cont_id = ?', [$id]);
}

/* =============================================================
 * USUÁRIOS E PERMISSÕES (B3)
 * ============================================================= */

/** Busca pelo nome de usuário OU pelo e-mail (o login aceita os dois). */
function buscarUsuarioPorLogin(string $login): ?array
{
    return consultarUm(
        'SELECT ' . colunasComApelido('usuario') . ' FROM usuario WHERE usua_usuario = ? OR (usua_email IS NOT NULL AND usua_email = ?) LIMIT 1',
        [$login, $login]
    );
}

function buscarUsuarioPorId(int $id): ?array
{
    return consultarUm('SELECT ' . colunasComApelido('usuario') . ' FROM usuario WHERE usua_id = ?', [$id]);
}

function listarUsuarios(): array
{
    $usuarios = consultar(
        'SELECT u.usua_id AS id, u.usua_nome AS nome, u.usua_usuario AS usuario, u.usua_email AS email, u.perf_id AS perfil_id,
                p.perf_nome AS perfil_nome, u.usua_ativo AS ativo, u.usua_ultimo_login_em AS ultimo_login_em, u.usua_criado_em AS criado_em
           FROM usuario u JOIN perfil p ON p.perf_id = u.perf_id
          ORDER BY u.usua_ativo DESC, u.usua_nome'
    );
    foreach ($usuarios as &$usuario) {
        $usuario['setores'] = listarIdsSetoresDoUsuario((int) $usuario['id']);
    }
    return $usuarios;
}

function listarPerfis(): array
{
    return consultar('SELECT perf_id AS id, perf_nome AS nome FROM perfil ORDER BY perf_nome');
}

function listarIdsSetoresDoUsuario(int $idUsuario): array
{
    return array_column(consultar('SELECT seto_id AS setor_id FROM usuario_setor WHERE usua_id = ? ORDER BY seto_id', [$idUsuario]), 'setor_id');
}

/** Responsáveis de um setor (pra mostrar "atualizado por" e mandar aviso de revisão). */
function listarResponsaveisDoSetor(string $idSetor): array
{
    return consultar(
        'SELECT u.usua_id AS id, u.usua_nome AS nome, u.usua_email AS email FROM usuario u JOIN usuario_setor us ON us.usua_id = u.usua_id
          WHERE us.seto_id = ? AND u.usua_ativo = 1 ORDER BY u.usua_nome',
        [$idSetor]
    );
}

function usuarioOuEmailEmUso(string $usuario, ?string $email, int $ignorarId = 0): bool
{
    $email = $email ?: null;
    return (int) consultarValor(
        'SELECT COUNT(*) FROM usuario WHERE usua_id <> ? AND (usua_usuario = ? OR (usua_email IS NOT NULL AND usua_email = ?))',
        [$ignorarId, $usuario, $email]
    ) > 0;
}

function criarUsuario(array $dados, string $senhaHash): int
{
    executar(
        'INSERT INTO usuario (usua_nome, usua_usuario, usua_email, usua_senha_hash, perf_id, usua_ativo) VALUES (?, ?, ?, ?, ?, ?)',
        [$dados['nome'], $dados['usuario'], $dados['email'] ?: null, $senhaHash, $dados['perfil_id'], $dados['ativo'] ? 1 : 0]
    );
    return (int) banco()->lastInsertId();
}

/** Atualiza o cadastro. Desativar o usuário também derruba as sessões dele. */
function atualizarUsuario(int $id, array $dados): void
{
    executar(
        'UPDATE usuario SET usua_nome = ?, usua_usuario = ?, usua_email = ?, perf_id = ?, usua_ativo = ?,
                usua_sessao_versao = usua_sessao_versao + IF(? = 0, 1, 0)
          WHERE usua_id = ?',
        [$dados['nome'], $dados['usuario'], $dados['email'] ?: null, $dados['perfil_id'], $dados['ativo'] ? 1 : 0, $dados['ativo'] ? 1 : 0, $id]
    );
}

/** Troca a senha. Com $derrubarSessoes, desloga o usuário de todos os aparelhos. */
function definirSenhaUsuario(int $id, string $senhaHash, bool $derrubarSessoes = true): void
{
    executar(
        'UPDATE usuario SET usua_senha_hash = ?' . ($derrubarSessoes ? ', usua_sessao_versao = usua_sessao_versao + 1' : '') . ' WHERE usua_id = ?',
        [$senhaHash, $id]
    );
}

function atualizarMeusDados(int $id, string $nome, ?string $email): void
{
    executar('UPDATE usuario SET usua_nome = ?, usua_email = ? WHERE usua_id = ?', [$nome, $email ?: null, $id]);
}

/** "Sair de todos os aparelhos": muda a versão e toda sessão antiga deixa de valer. Devolve a versão nova. */
function encerrarTodasAsSessoes(int $id): int
{
    executar('UPDATE usuario SET usua_sessao_versao = usua_sessao_versao + 1 WHERE usua_id = ?', [$id]);
    return (int) consultarValor('SELECT usua_sessao_versao FROM usuario WHERE usua_id = ?', [$id]);
}

function registrarLoginUsuario(int $id): void
{
    executar('UPDATE usuario SET usua_ultimo_login_em = NOW() WHERE usua_id = ?', [$id]);
}

function definirSetoresDoUsuario(int $idUsuario, array $idsSetores): void
{
    // Apaga e grava de novo numa transação: se der erro no meio, fica como estava.
    $pdo = banco();
    $pdo->beginTransaction();
    try {
        executar('DELETE FROM usuario_setor WHERE usua_id = ?', [$idUsuario]);
        foreach (array_unique($idsSetores) as $idSetor) {
            executar('INSERT INTO usuario_setor (usua_id, seto_id) SELECT ?, seto_id FROM setor WHERE seto_id = ?', [$idUsuario, $idSetor]);
        }
        $pdo->commit();
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

function contarAdminsGeraisAtivos(int $ignorarId = 0): int
{
    return (int) consultarValor("SELECT COUNT(*) FROM usuario WHERE perf_id = 'admin_geral' AND usua_ativo = 1 AND usua_id <> ?", [$ignorarId]);
}

/* =============================================================
 * AUDITORIA: toda alteração feita no admin fica registrada
 * ============================================================= */

/**
 * $usuario = usuarioAtual() (quem fez). $antes/$depois viram JSON.
 * $resumo é a frase que aparece no dashboard (ex.: 'Setor "CRA" editado').
 */
function registrarAuditoria(?array $usuario, string $acao, string $tabela, ?string $registroId, ?array $antes, ?array $depois, string $resumo): void
{
    $json = static function (?array $dados): ?string {
        return $dados === null ? null : json_encode($dados, JSON_UNESCAPED_UNICODE);
    };
    executar(
        'INSERT INTO auditoria (usua_id, audi_usuario_nome, audi_acao, audi_tabela, audi_registro_id, audi_resumo, audi_antes, audi_depois) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [$usuario['id'] ?? null, $usuario['nome'] ?? 'Sistema', $acao, $tabela, $registroId, mb_substr($resumo, 0, 255), $json($antes), $json($depois)]
    );
}

function listarAuditoria(int $limite = 10, ?string $tabela = null, ?string $registroId = null): array
{
    $filtros = [];
    $parametros = [];
    if ($tabela !== null) {
        $filtros[] = 'audi_tabela = ?';
        $parametros[] = $tabela;
    }
    if ($registroId !== null) {
        $filtros[] = 'audi_registro_id = ?';
        $parametros[] = $registroId;
    }
    return consultar(
        'SELECT audi_id AS id, audi_usuario_nome AS usuario_nome, audi_acao AS acao, audi_tabela AS tabela,
                audi_registro_id AS registro_id, audi_resumo AS resumo, audi_quando AS quando FROM auditoria'
        . ($filtros ? ' WHERE ' . implode(' AND ', $filtros) : '')
        . ' ORDER BY audi_quando DESC, audi_id DESC LIMIT ' . max(1, $limite),
        $parametros
    );
}

/* =============================================================
 * ADMIN: ORDEM DAS LISTAS
 * ============================================================= */

/**
 * Sobe/desce um item na lista (troca a ordem com o vizinho).
 * $tabela, $colunaId e $colunaOrdem vêm sempre do próprio código (nunca do usuário).
 */
function moverNaOrdem(string $tabela, string $colunaId, string $colunaOrdem, $id, string $direcao): void
{
    $atual = consultarUm("SELECT {$colunaId} AS id FROM {$tabela} WHERE {$colunaId} = ?", [$id]);
    if (!$atual) {
        return;
    }
    $todos = consultar("SELECT {$colunaId} AS id FROM {$tabela} ORDER BY {$colunaOrdem}, {$colunaId}");
    $ids = array_column($todos, 'id');
    $posicao = array_search($atual['id'], $ids, false);
    $alvo = $direcao === 'cima' ? $posicao - 1 : $posicao + 1;
    if ($posicao === false || $alvo < 0 || $alvo >= count($ids)) {
        return;
    }
    // Troca os dois de lugar e regrava a ordem de todo mundo (0, 1, 2...): simples e sem empate.
    [$ids[$posicao], $ids[$alvo]] = [$ids[$alvo], $ids[$posicao]];
    foreach ($ids as $novaOrdem => $idItem) {
        executar("UPDATE {$tabela} SET {$colunaOrdem} = ? WHERE {$colunaId} = ?", [$novaOrdem, $idItem]);
    }
}

function moverCategoria(string $id, string $direcao): void
{
    moverNaOrdem('categoria', 'cate_id', 'cate_ordem', $id, $direcao);
}

function moverFluxo(int $id, string $direcao): void
{
    moverNaOrdem('fluxo', 'flux_id', 'flux_ordem', $id, $direcao);
}

/** Gera um id (slug) livre: "Biblioteca" -> "biblioteca", "biblioteca-2"... ($tabela e $coluna vêm do código) */
function gerarIdLivre(string $tabela, string $texto, string $coluna): string
{
    $base = criarSlugTexto($texto, 50);
    $id = $base;
    $numero = 2;
    while ((int) consultarValor("SELECT COUNT(*) FROM {$tabela} WHERE {$coluna} = ?", [$id]) > 0) {
        $id = $base . '-' . $numero++;
    }
    return $id;
}

/* =============================================================
 * ADMIN: CATEGORIAS
 * ============================================================= */

function criarCategoria(array $dados): string
{
    $id = gerarIdLivre('categoria', $dados['nome'], 'cate_id');
    $ordem = (int) consultarValor('SELECT COALESCE(MAX(cate_ordem), 0) + 1 FROM categoria');
    executar(
        'INSERT INTO categoria (cate_id, cate_nome, cate_icone, cate_cor, cate_ordem, cate_ativo) VALUES (?, ?, ?, ?, ?, ?)',
        [$id, $dados['nome'], $dados['icone'], $dados['cor'], $ordem, $dados['ativo'] ? 1 : 0]
    );
    return $id;
}

function atualizarCategoria(string $id, array $dados): void
{
    executar(
        'UPDATE categoria SET cate_nome = ?, cate_icone = ?, cate_cor = ?, cate_ativo = ? WHERE cate_id = ?',
        [$dados['nome'], $dados['icone'], $dados['cor'], $dados['ativo'] ? 1 : 0, $id]
    );
}

function alternarCategoriaAtiva(string $id): void
{
    executar('UPDATE categoria SET cate_ativo = 1 - cate_ativo WHERE cate_id = ?', [$id]);
}

/** Exclui a categoria. Os setores dela ficam "sem categoria" (não são apagados). */
function excluirCategoria(string $id): void
{
    executar('DELETE FROM categoria WHERE cate_id = ?', [$id]);
}

/* =============================================================
 * ADMIN: SETORES
 * ============================================================= */

function setorExiste(string $id): bool
{
    return (int) consultarValor('SELECT COUNT(*) FROM setor WHERE seto_id = ?', [$id]) > 0;
}

/**
 * Cria ($idExistente null) ou atualiza um setor, com as listas (equipe, serviços,
 * documentos) quando vierem em $dados. Tudo numa transação: grava tudo ou nada.
 * Devolve o id do setor.
 */
function salvarSetor(?string $idExistente, array $dados, string $autor): string
{
    $colunasPermitidas = [
        'categoria_id', 'nome', 'sigla', 'icone', 'cor', 'descricao', 'descricao_curta', 'texto_facil',
        'localizacao', 'bloco', 'sala_texto', 'telefone', 'email', 'responsavel', 'horario_texto', 'ativo',
    ];
    // $campos: [coluna do banco => valor] (ex.: 'seto_nome' => 'Biblioteca')
    $campos = [];
    foreach ($colunasPermitidas as $campo) {
        if (array_key_exists($campo, $dados)) {
            $valor = $dados[$campo];
            $campos[colunaDoBanco('setor', $campo)] = is_bool($valor) ? (int) $valor : ($valor === '' && $campo !== 'sigla' ? null : $valor);
        }
    }
    $campos['seto_atualizado_por'] = $autor;

    $pdo = banco();
    $pdo->beginTransaction();
    try {
        if ($idExistente === null) {
            $id = gerarIdLivre('setor', ($dados['sigla'] ?? '') !== '' ? $dados['sigla'] : $dados['nome'], 'seto_id');
            $campos['seto_id'] = $id;
            $campos['seto_ordem'] = (int) consultarValor('SELECT COALESCE(MAX(seto_ordem), 0) + 1 FROM setor');
            $colunas = array_keys($campos);
            executar(
                'INSERT INTO setor (' . implode(', ', $colunas) . ', seto_atualizado_em) VALUES (' . implode(', ', array_fill(0, count($colunas), '?')) . ', NOW())',
                array_values($campos)
            );
        } else {
            $id = $idExistente;
            $sets = implode(', ', array_map(static function ($coluna) { return "{$coluna} = ?"; }, array_keys($campos)));
            executar("UPDATE setor SET {$sets}, seto_atualizado_em = NOW() WHERE seto_id = ?", array_merge(array_values($campos), [$id]));
        }

        // [tabela, prefixo das colunas, chave em $dados]
        foreach ([['setor_equipe', 'equi', 'equipe'], ['setor_servico', 'serv', 'servicos'], ['setor_documento', 'docu', 'documentos'], ['sinonimo', 'sino', 'sinonimos']] as [$tabela, $prefixo, $chave]) {
            if (!array_key_exists($chave, $dados)) {
                continue;
            }
            executar("DELETE FROM {$tabela} WHERE seto_id = ?", [$id]);
            foreach (array_values($dados[$chave]) as $posicao => $nome) {
                executar("INSERT INTO {$tabela} (seto_id, {$prefixo}_nome, {$prefixo}_ordem) VALUES (?, ?, ?)", [$id, $nome, $posicao]);
            }
        }

        $pdo->commit();
        return $id;
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

function alternarSetorAtivo(string $id): void
{
    executar('UPDATE setor SET seto_ativo = 1 - seto_ativo, seto_atualizado_em = NOW() WHERE seto_id = ?', [$id]);
}

/** Exclui o setor (equipe, serviços, horários... vão junto; salas do mapa ficam sem setor). */
function excluirSetor(string $id): void
{
    executar('DELETE FROM setor WHERE seto_id = ?', [$id]);
}

/** Setores ativos que ainda não têm nenhuma informação de contato/horário/local (pra preencher). */
function listarSetoresSemDados(): array
{
    return consultar(
        "SELECT s.seto_id AS id, s.seto_nome AS nome, s.seto_sigla AS sigla, c.cate_nome AS categoria_nome
           FROM setor s LEFT JOIN categoria c ON c.cate_id = s.cate_id
          WHERE s.seto_ativo = 1
            AND COALESCE(s.seto_telefone, '') = '' AND COALESCE(s.seto_email, '') = ''
            AND COALESCE(s.seto_horario_texto, '') = '' AND COALESCE(s.seto_localizacao, '') = ''
            AND NOT EXISTS (SELECT 1 FROM setor_horario h WHERE h.seto_id = s.seto_id)
          ORDER BY c.cate_ordem, s.seto_ordem"
    );
}

/* =============================================================
 * ADMIN: INFORMAÇÕES (avisos, notícias, calendário, FAQ, links, institucional)
 * Cada aba é uma tabela. Nomes de tabela/coluna vêm desta lista fixa,
 * nunca do que o usuário mandou.
 * ============================================================= */

function abasInformacao(): array
{
    return [
        'avisos' => ['tabela' => 'aviso', 'campos' => ['titulo', 'mensagem', 'nivel', 'inicio', 'fim'], 'titulo' => 'titulo'],
        'noticias' => ['tabela' => 'noticia', 'campos' => ['titulo', 'resumo', 'conteudo', 'categoria', 'publicada_em', 'destaque'], 'titulo' => 'titulo'],
        'faq' => ['tabela' => 'faq', 'campos' => ['pergunta', 'resposta'], 'titulo' => 'pergunta'],
        'links' => ['tabela' => 'link_util', 'campos' => ['titulo', 'url'], 'titulo' => 'titulo'],
        'institucional' => ['tabela' => 'texto_institucional', 'campos' => ['titulo', 'conteudo'], 'titulo' => 'titulo'],
    ];
}

function listarItensInformacao(string $aba): array
{
    $config = abasInformacao()[$aba] ?? null;
    if (!$config) {
        return [];
    }
    $tabela = $config['tabela'];
    $itens = consultar('SELECT ' . colunasComApelido($tabela) . " FROM {$tabela} ORDER BY " . colunaDoBanco($tabela, 'ordem') . ', ' . colunaDoBanco($tabela, 'id') . ' DESC');
    if ($aba === 'faq') {
        foreach ($itens as &$item) {
            $item['setores'] = array_column(consultar('SELECT seto_id AS setor_id FROM faq_setor WHERE faq_id = ?', [$item['id']]), 'setor_id');
        }
    }
    return $itens;
}

function buscarItemInformacao(string $aba, int $id): ?array
{
    $config = abasInformacao()[$aba] ?? null;
    if (!$config) {
        return null;
    }
    $tabela = $config['tabela'];
    return consultarUm('SELECT ' . colunasComApelido($tabela) . " FROM {$tabela} WHERE " . colunaDoBanco($tabela, 'id') . ' = ?', [$id]);
}

/** Cria (id null) ou atualiza um item. Devolve o id. */
function salvarItemInformacao(string $aba, ?int $id, array $valores): int
{
    $config = abasInformacao()[$aba];
    $tabela = $config['tabela'];
    $colunaId = colunaDoBanco($tabela, 'id');
    // $campos: [coluna do banco => valor]
    $campos = [];
    foreach ($config['campos'] as $campo) {
        $campos[colunaDoBanco($tabela, $campo)] = $valores[$campo] ?? null;
    }

    $pdo = banco();
    $pdo->beginTransaction();
    try {
        if ($id === null) {
            $colunaOrdem = colunaDoBanco($tabela, 'ordem');
            $campos[$colunaOrdem] = (int) consultarValor("SELECT COALESCE(MIN({$colunaOrdem}), 0) - 1 FROM {$tabela}");
            if ($aba === 'noticias') {
                $campos['noti_slug'] = gerarIdLivre('noticia', $valores['titulo'], 'noti_slug');
            }
            $colunas = array_keys($campos);
            executar(
                "INSERT INTO {$tabela} (" . implode(', ', $colunas) . ') VALUES (' . implode(', ', array_fill(0, count($colunas), '?')) . ')',
                array_values($campos)
            );
            $id = (int) $pdo->lastInsertId();
        } else {
            $sets = implode(', ', array_map(static function ($coluna) { return "{$coluna} = ?"; }, array_keys($campos)));
            executar("UPDATE {$tabela} SET {$sets} WHERE {$colunaId} = ?", array_merge(array_values($campos), [$id]));
        }

        if ($aba === 'faq' && array_key_exists('setores', $valores)) {
            executar('DELETE FROM faq_setor WHERE faq_id = ?', [$id]);
            foreach (array_unique($valores['setores']) as $idSetor) {
                executar('INSERT INTO faq_setor (faq_id, seto_id) SELECT ?, seto_id FROM setor WHERE seto_id = ?', [$id, $idSetor]);
            }
        }
        $pdo->commit();
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
    return $id;
}

function alternarPublicacaoInformacao(string $aba, int $id): void
{
    $tabela = abasInformacao()[$aba]['tabela'];
    $colunaPublicado = colunaDoBanco($tabela, 'publicado');
    executar("UPDATE {$tabela} SET {$colunaPublicado} = 1 - {$colunaPublicado} WHERE " . colunaDoBanco($tabela, 'id') . ' = ?', [$id]);
}

function excluirItemInformacao(string $aba, int $id): void
{
    $tabela = abasInformacao()[$aba]['tabela'];
    executar("DELETE FROM {$tabela} WHERE " . colunaDoBanco($tabela, 'id') . ' = ?', [$id]);
}

function moverItemInformacao(string $aba, int $id, string $direcao): void
{
    $tabela = abasInformacao()[$aba]['tabela'];
    moverNaOrdem($tabela, colunaDoBanco($tabela, 'id'), colunaDoBanco($tabela, 'ordem'), $id, $direcao);
}

/* =============================================================
 * ADMIN: CONFIGURAÇÕES E DASHBOARD
 * ============================================================= */

function salvarConfiguracoes(array $valores): void
{
    foreach ($valores as $chave => $valor) {
        executar(
            'INSERT INTO configuracao (conf_chave, conf_valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE conf_valor = VALUES(conf_valor)',
            [$chave, $valor]
        );
    }
}

/** Números reais do dashboard. */
function contagensDashboard(): array
{
    return [
        'categorias' => (int) consultarValor('SELECT COUNT(*) FROM categoria WHERE cate_ativo = 1'),
        'setores' => (int) consultarValor('SELECT COUNT(*) FROM setor WHERE seto_ativo = 1'),
        'informacoes' => (int) consultarValor(
            'SELECT (SELECT COUNT(*) FROM aviso WHERE avis_publicado = 1) + (SELECT COUNT(*) FROM noticia WHERE noti_publicado = 1)
                  + (SELECT COUNT(*) FROM faq WHERE faq_publicado = 1)'
        ),
        'mensagens_nao_lidas' => contarMensagensNaoLidas(),
        'setores_sem_dados' => count(listarSetoresSemDados()),
    ];
}

/* =============================================================
 * HORÁRIOS ESTRUTURADOS (B4)
 * ============================================================= */

function listarHorariosSetor(string $idSetor): array
{
    return consultar(
        'SELECT hora_id AS id, hora_dia_semana AS dia_semana, TIME_FORMAT(hora_abre, "%H:%i:00") AS abre,
                TIME_FORMAT(hora_fecha, "%H:%i:00") AS fecha, hora_observacao AS observacao
           FROM setor_horario WHERE seto_id = ? ORDER BY FIELD(hora_dia_semana, 1, 2, 3, 4, 5, 6, 0), hora_abre',
        [$idSetor]
    );
}

/** Troca todas as faixas do setor pelas novas (numa transação). */
function salvarHorariosSetor(string $idSetor, array $faixas): void
{
    $pdo = banco();
    $pdo->beginTransaction();
    try {
        executar('DELETE FROM setor_horario WHERE seto_id = ?', [$idSetor]);
        foreach ($faixas as $faixa) {
            executar(
                'INSERT INTO setor_horario (seto_id, hora_dia_semana, hora_abre, hora_fecha) VALUES (?, ?, ?, ?)',
                [$idSetor, (int) $faixa['dia_semana'], $faixa['abre'], $faixa['fecha']]
            );
        }
        executar('UPDATE setor SET seto_atualizado_em = NOW() WHERE seto_id = ?', [$idSetor]);
        $pdo->commit();
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

/** Exceções futuras (e de hoje) do setor. $incluirCampus traz também as do campus todo. */
function listarExcecoes(?string $idSetor, bool $incluirCampus = false): array
{
    if ($idSetor === null) {
        return consultar('SELECT ' . colunasComApelido('setor_excecao') . ' FROM setor_excecao WHERE seto_id IS NULL AND exce_data >= CURDATE() ORDER BY exce_data');
    }
    return consultar(
        'SELECT ' . colunasComApelido('setor_excecao') . ' FROM setor_excecao
          WHERE (seto_id = ?' . ($incluirCampus ? ' OR seto_id IS NULL' : '') . ') AND exce_data >= CURDATE() ORDER BY exce_data',
        [$idSetor]
    );
}

function buscarExcecao(int $id): ?array
{
    return consultarUm('SELECT ' . colunasComApelido('setor_excecao') . ' FROM setor_excecao WHERE exce_id = ?', [$id]);
}

function criarExcecao(?string $idSetor, string $data, bool $fechado, ?string $abre, ?string $fecha, string $motivo): int
{
    executar(
        'INSERT INTO setor_excecao (seto_id, exce_data, exce_fechado, exce_abre, exce_fecha, exce_motivo) VALUES (?, ?, ?, ?, ?, ?)',
        [$idSetor, $data, $fechado ? 1 : 0, $fechado ? null : $abre, $fechado ? null : $fecha, $motivo]
    );
    return (int) banco()->lastInsertId();
}

function excluirExcecao(int $id): void
{
    executar('DELETE FROM setor_excecao WHERE exce_id = ?', [$id]);
}

/**
 * Exceções dos próximos dias (hoje + 7), por data, pra um setor:
 * as do setor e as do campus todo (feriado, recesso: exceção sem setor).
 */
function excecoesProximosDias(string $idSetor, DateTimeImmutable $agora): array
{
    $inicio = $agora->format('Y-m-d');
    $fim = $agora->modify('+8 day')->format('Y-m-d');
    $porData = [];
    foreach (consultar(
        'SELECT exce_data AS data, exce_fechado AS fechado, exce_abre AS abre, exce_fecha AS fecha, exce_motivo AS motivo
           FROM setor_excecao
          WHERE (seto_id = ? OR seto_id IS NULL) AND exce_data BETWEEN ? AND ?',
        [$idSetor, $inicio, $fim]
    ) as $excecao) {
        $porData[$excecao['data']][] = $excecao;
    }
    return $porData;
}

/**
 * Selo "Aberto agora" de um setor: usa o horário estruturado; se o setor ainda
 * não tem horário estruturado, cai no texto antigo (setorEstaAberto).
 * Devolve ['aberto' => bool|null, 'resumo' => string|null, 'faixas' => [...]].
 */
function estadoHorarioSetor(array $setor, ?DateTimeImmutable $agora = null): array
{
    $agora = $agora ?? new DateTimeImmutable('now');
    $faixas = listarHorariosSetor($setor['id']);
    if ($faixas) {
        return estadoHorario($faixas, excecoesProximosDias($setor['id'], $agora), $agora) + ['faixas' => $faixas];
    }
    // Setor só com o texto antigo de horário: feriado/fechamento de hoje (do setor ou do campus) também vale (BUG-10).
    foreach (excecoesProximosDias($setor['id'], $agora)[$agora->format('Y-m-d')] ?? [] as $excecao) {
        if ((int) $excecao['fechado'] === 1) {
            return ['aberto' => false, 'resumo' => 'Fechado hoje (' . $excecao['motivo'] . ')', 'faixas' => []];
        }
    }
    return ['aberto' => setorEstaAberto((string) ($setor['horario'] ?? ''), $agora), 'resumo' => null, 'faixas' => []];
}

/* =============================================================
 * EDITOR DO MAPA (B6): salas e peças do cenário
 * ============================================================= */

/** Tipos de peça do cenário e o rótulo que aparece no editor. */
function tiposPecaMapa(): array
{
    return [
        'bloco' => 'Bloco / prédio', 'obra' => 'Obra', 'quadra' => 'Quadra',
        'estacionamento' => 'Estacionamento', 'area_verde' => 'Área verde', 'arvore' => 'Árvore',
        'estande' => 'Estande (evento)',
    ];
}

/**
 * Peças do cenário. Com $soDeAgora, só as que valem agora (peça com início/fim
 * é temporária: estande de feira, obra, evento — W3).
 */
function listarPecasMapa(bool $soDeAgora = true): array
{
    $pecas = consultar(
        'SELECT peca_id AS id, peca_tipo AS tipo, peca_nome AS nome, peca_x AS x, peca_z AS z, peca_largura AS largura,
                peca_profundidade AS profundidade, peca_rotacao AS rotacao, peca_inicio AS inicio, peca_fim AS fim FROM peca_mapa'
        . ($soDeAgora ? ' WHERE (peca_inicio IS NULL OR peca_inicio <= NOW()) AND (peca_fim IS NULL OR peca_fim >= NOW())' : '')
        . ' ORDER BY peca_id'
    );
    return array_map(static function (array $peca) {
        foreach (['x', 'z', 'largura', 'profundidade', 'rotacao'] as $numero) {
            $peca[$numero] = (float) $peca[$numero];
        }
        $peca['id'] = (int) $peca['id'];
        return $peca;
    }, $pecas);
}

/** Troca todas as peças pelas novas (o editor manda a lista inteira). */
function salvarPecasMapa(array $pecas): void
{
    $pdo = banco();
    $pdo->beginTransaction();
    try {
        executar('DELETE FROM peca_mapa');
        foreach ($pecas as $peca) {
            executar(
                'INSERT INTO peca_mapa (peca_tipo, peca_nome, peca_x, peca_z, peca_largura, peca_profundidade, peca_rotacao, peca_inicio, peca_fim)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$peca['tipo'], $peca['nome'], $peca['x'], $peca['z'], $peca['largura'], $peca['profundidade'], $peca['rotacao'], $peca['inicio'], $peca['fim']]
            );
        }
        $pdo->commit();
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

function buscarSalaBruta(string $id): ?array
{
    return consultarUm('SELECT ' . colunasComApelido('sala') . ' FROM sala WHERE sala_id = ?', [$id]);
}

/** Cria ($idExistente null) ou atualiza uma sala. Sala nova vai pro fim da ala. */
function salvarSala(?string $idExistente, array $dados): string
{
    // [coluna do banco => valor]
    $campos = [
        'sala_nome' => $dados['nome'],
        'sala_nome_curto' => $dados['nome_curto'],
        'sala_funcao' => $dados['funcao'] ?: null,
        'anda_id' => $dados['andar_id'],
        'sala_ala' => $dados['ala'],
        'sala_lado' => $dados['ponta'] ? null : $dados['lado'],
        'sala_ponta' => $dados['ponta'] ? 1 : 0,
        'sala_vazia' => $dados['vazia'] ? 1 : 0,
        'seto_id' => $dados['setor_id'] ?: null,
    ];
    $proximaOrdem = static function () use ($dados): int {
        return (int) consultarValor('SELECT COALESCE(MAX(sala_ordem), 0) + 1 FROM sala WHERE anda_id = ? AND sala_ala = ?', [$dados['andar_id'], $dados['ala']]);
    };
    if ($idExistente === null) {
        $id = gerarIdLivre('sala', $dados['nome'], 'sala_id');
        $campos['sala_id'] = $id;
        $campos['sala_ordem'] = $proximaOrdem();
        $colunas = array_keys($campos);
        executar('INSERT INTO sala (' . implode(', ', $colunas) . ') VALUES (' . implode(', ', array_fill(0, count($colunas), '?')) . ')', array_values($campos));
        return $id;
    }
    $atual = buscarSalaBruta($idExistente);
    // Mudou de andar/ala: entra no fim da nova ala.
    if ($atual && ($atual['andar_id'] !== $dados['andar_id'] || $atual['ala'] !== $dados['ala'])) {
        $campos['sala_ordem'] = $proximaOrdem();
    }
    $sets = implode(', ', array_map(static function ($coluna) { return "{$coluna} = ?"; }, array_keys($campos)));
    executar("UPDATE sala SET {$sets} WHERE sala_id = ?", array_merge(array_values($campos), [$idExistente]));
    return $idExistente;
}

function excluirSala(string $id): void
{
    executar('DELETE FROM sala WHERE sala_id = ?', [$id]);
}

/**
 * Nova arrumação das salas vinda do arrasta-e-solta:
 * [['id' => 'cra', 'andar_id' => 'terreo', 'ala' => 'direita', 'ordem' => 1], ...]
 */
function reordenarSalas(array $posicoes): void
{
    $pdo = banco();
    $pdo->beginTransaction();
    try {
        foreach ($posicoes as $posicao) {
            executar('UPDATE sala SET anda_id = ?, sala_ala = ?, sala_ordem = ? WHERE sala_id = ?', [$posicao['andar_id'], $posicao['ala'], $posicao['ordem'], $posicao['id']]);
        }
        $pdo->commit();
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

/* =============================================================
 * BUSCA "ONDE FICA..." (N1)
 * ============================================================= */

/** "Declaração  de Matrícula" -> "declaracao de matricula" (sem acento, minúsculo, espaços simples) */
function normalizarBusca(string $texto): string
{
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    $texto = strtr($texto, [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'é' => 'e', 'ê' => 'e', 'è' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n',
    ]);
    return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}\s-]/u', ' ', $texto)));
}

/** Palavras que não ajudam a achar nada ("onde fica a ..."). */
function palavrasVaziasBusca(): array
{
    return ['onde', 'fica', 'o', 'a', 'os', 'as', 'de', 'da', 'do', 'das', 'dos', 'e', 'em', 'no', 'na', 'um', 'uma', 'para', 'pra', 'como', 'que', 'quero', 'preciso', 'qual', 'setor', 'sala'];
}

/**
 * Procura setores pelo que a pessoa digitou. Cada palavra precisa aparecer em
 * algum lugar do setor (nome, sigla, sinônimo, serviço, pergunta do FAQ ou descrição).
 * Devolve até $limite resultados, do mais certeiro pro menos:
 *   ['id', 'nome', 'sigla', 'url', 'motivo', 'andar', 'sala_id', 'url_mapa']
 */
function buscarNoGuia(string $termo, int $limite = 8): array
{
    $termoNormal = normalizarBusca($termo);
    $palavras = array_values(array_filter(explode(' ', $termoNormal), static function ($palavra) {
        return mb_strlen($palavra) >= 2 && !in_array($palavra, palavrasVaziasBusca(), true);
    }));
    if (!$palavras) {
        return [];
    }

    // Tudo que dá pra buscar, por setor (são poucos setores: cabe na memória).
    $extras = [];
    foreach (consultar('SELECT seto_id AS setor_id, serv_nome AS nome FROM setor_servico') as $linha) {
        $extras[$linha['setor_id']]['servicos'][] = $linha['nome'];
    }
    foreach (consultar('SELECT seto_id AS setor_id, sino_nome AS nome FROM sinonimo') as $linha) {
        $extras[$linha['setor_id']]['sinonimos'][] = $linha['nome'];
    }
    foreach (consultar('SELECT fs.seto_id AS setor_id, f.faq_pergunta AS pergunta FROM faq f JOIN faq_setor fs ON fs.faq_id = f.faq_id WHERE f.faq_publicado = 1') as $linha) {
        $extras[$linha['setor_id']]['faq'][] = $linha['pergunta'];
    }
    $salaDoSetor = [];
    $mapa = listarMapaSalas();
    foreach ($mapa['salas'] as $sala) {
        if ($sala['setor_id'] && !isset($salaDoSetor[$sala['setor_id']])) {
            $salaDoSetor[$sala['setor_id']] = $sala;
        }
    }

    $resultados = [];
    foreach (listarSetores() as $setor) {
        $id = $setor['id'];
        // [peso, texto, como explicar o achado]
        $campos = [[100, $setor['sigla'], null, true], [60, $setor['nome'], null, false]];
        foreach ($extras[$id]['sinonimos'] ?? [] as $sinonimo) {
            $campos[] = [80, $sinonimo, 'Também procurado como "' . $sinonimo . '"', false];
        }
        foreach ($extras[$id]['servicos'] ?? [] as $servico) {
            $campos[] = [40, $servico, 'Serviço: ' . $servico, false];
        }
        foreach ($extras[$id]['faq'] ?? [] as $pergunta) {
            $campos[] = [30, $pergunta, 'Pergunta: ' . $pergunta, false];
        }
        $campos[] = [15, (string) $setor['descricao'] . ' ' . (string) $setor['descricao_curta'], null, false];

        $pontos = 0;
        $motivo = null;
        foreach ($palavras as $palavra) {
            $melhor = 0;
            foreach ($campos as [$peso, $texto, $explicacao, $soInteiro]) {
                $textoNormal = normalizarBusca((string) $texto);
                if ($textoNormal === '') {
                    continue;
                }
                $achou = $soInteiro ? $textoNormal === $palavra : (strpos(' ' . $textoNormal . ' ', ' ' . $palavra) !== false);
                if (!$achou && !$soInteiro && mb_strlen($palavra) >= 4) {
                    $achou = strpos($textoNormal, $palavra) !== false; // pedaço no meio da palavra
                    $peso = (int) ($peso / 2);
                }
                if ($achou && $peso > $melhor) {
                    $melhor = $peso;
                    if ($explicacao && $peso >= 30) {
                        $motivo = $motivo ?? $explicacao;
                    }
                }
            }
            if ($melhor === 0) {
                continue 2; // uma palavra não achou nada: setor fora
            }
            $pontos += $melhor;
        }

        $sala = $salaDoSetor[$id] ?? null;
        $resultados[] = [
            'id' => $id,
            'nome' => $setor['nome'],
            'sigla' => $setor['sigla'],
            'url' => 'setor.php?id=' . rawurlencode($id),
            'motivo' => $motivo,
            'andar' => $sala ? ($mapa['andares'][$sala['andar']]['nome'] ?? '') : null,
            'sala_id' => $sala['id'] ?? null,
            'url_mapa' => $sala ? 'mapa.php?sala=' . rawurlencode($sala['id']) : null,
            'pontos' => $pontos,
        ];
    }

    // Nenhum setor tem TODAS as palavras: tenta com cada palavra sozinha
    // ("empréstimo de livro" ainda acha a Biblioteca pelo "empréstimo").
    if (!$resultados && count($palavras) > 1) {
        $porId = [];
        foreach ($palavras as $palavra) {
            foreach (buscarNoGuia($palavra, $limite) as $achado) {
                if (!isset($porId[$achado['id']]) || $porId[$achado['id']]['pontos'] < $achado['pontos']) {
                    $porId[$achado['id']] = $achado;
                }
            }
        }
        $resultados = array_values($porId);
    }

    usort($resultados, static function ($a, $b) {
        return $b['pontos'] <=> $a['pontos'] ?: strcmp($a['nome'], $b['nome']);
    });
    return array_slice($resultados, 0, $limite);
}

/* =============================================================
 * QR DA PORTA (N2): cada sala tem um endereço que abre a página
 * dela já sabendo que a pessoa "está aqui".
 * ============================================================= */

/** Endereço completo que vai no QR da porta da sala. */
function urlDaPorta(array $sala): string
{
    $caminho = $sala['url_setor']
        ? $sala['url_setor'] . '&aqui=' . rawurlencode($sala['id'])
        : 'sala.php?id=' . rawurlencode($sala['id']) . '&aqui=1';
    return urlBaseSite() . '/' . $caminho;
}

/* =============================================================
 * AVISOS, NOTÍCIAS E CALENDÁRIO (N6) + "HOJE NO CAMPUS" (N5)
 * ============================================================= */

/** Avisos publicados que valem agora (dentro do período, se tiver). Urgentes primeiro. */
function listarAvisosAtivos(): array
{
    return consultar(
        "SELECT avis_id AS id, avis_titulo AS titulo, avis_mensagem AS mensagem, avis_nivel AS nivel, avis_inicio AS inicio, avis_fim AS fim
           FROM aviso
          WHERE avis_publicado = 1 AND (avis_inicio IS NULL OR avis_inicio <= NOW()) AND (avis_fim IS NULL OR avis_fim >= NOW())
          ORDER BY avis_nivel = 'urgente' DESC, avis_ordem, avis_id DESC"
    );
}

/** O aviso urgente que vira faixa no topo do site (ou null). */
function avisoUrgenteAtual(): ?array
{
    foreach (listarAvisosAtivos() as $aviso) {
        if ($aviso['nivel'] === 'urgente') {
            return $aviso;
        }
    }
    return null;
}

function buscarNoticiaPorSlug(string $slug): ?array
{
    $noticia = consultarUm('SELECT ' . colunasComApelido('noticia') . ' FROM noticia WHERE noti_slug = ? AND noti_publicado = 1', [$slug]);
    if ($noticia) {
        $noticia['data'] = formatarDataCurta($noticia['publicada_em']);
    }
    return $noticia;
}





/**
 * Tudo da faixa "Hoje no campus" da home.
 * Não inventa "dia letivo": só diz o que o calendário cadastrado informa.
 */
function hojeNoCampus(?DateTimeImmutable $agora = null): array
{
    $agora = $agora ?? new DateTimeImmutable('now');
    $hoje = $agora->format('Y-m-d');
    $diaSemana = (int) $agora->format('w');
    $meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    $dataPorExtenso = ucfirst(NOMES_DIAS[$diaSemana]) . ', ' . (int) $agora->format('j') . ' de ' . $meses[(int) $agora->format('n') - 1];

    // Feriado, recesso ou horário especial do campus inteiro = exceção sem setor (admin › Setores › Horário).
    $situacao = null;
    $excecaoHoje = consultarUm(
        'SELECT ' . colunasComApelido('setor_excecao') . ' FROM setor_excecao WHERE seto_id IS NULL AND exce_data = ? ORDER BY exce_id DESC LIMIT 1',
        [$hoje]
    );
    if ($excecaoHoje) {
        $situacao = (int) $excecaoHoje['fechado']
            ? ['tipo' => 'feriado', 'texto' => 'Campus fechado: ' . $excecaoHoje['motivo']]
            : ['tipo' => 'especial', 'texto' => 'Horário especial (' . substr((string) $excecaoHoje['abre'], 0, 5) . ' às '
                . substr((string) $excecaoHoje['fecha'], 0, 5) . '): ' . $excecaoHoje['motivo']];
    } elseif ($diaSemana === 0 || $diaSemana === 6) {
        $situacao = ['tipo' => 'fim_de_semana', 'texto' => 'Fim de semana'];
    }

    $abertos = [];
    $comHorario = 0;
    foreach (listarSetores() as $setor) {
        $estado = estadoHorarioSetor($setor, $agora);
        if ($estado['aberto'] !== null) {
            $comHorario++;
        }
        if ($estado['aberto'] === true) {
            $abertos[] = ['id' => $setor['id'], 'nome' => $setor['nome'], 'sigla' => $setor['sigla'], 'resumo' => $estado['resumo']];
        }
    }

    return [
        'data' => $dataPorExtenso,
        'situacao' => $situacao,
        'abertos' => $abertos,
        'setores_com_horario' => $comHorario,
        'proxima_data_especial' => consultarUm(
            'SELECT ' . colunasComApelido('setor_excecao') . ' FROM setor_excecao WHERE seto_id IS NULL AND exce_data > ? ORDER BY exce_data LIMIT 1', [$hoje]
        ),
        'aviso_urgente' => avisoUrgenteAtual(),
        'atualizado_em' => $agora->format('H:i'),
    ];
}

/* =============================================================
 * "COMO FAÇO PARA..." (N11)
 * ============================================================= */

function listarFluxos(bool $incluirOcultos = false): array
{
    return consultar(
        'SELECT ' . colunasComApelido('fluxo', 'f') . ', s.seto_nome AS setor_nome, s.seto_sigla AS setor_sigla
           FROM fluxo f LEFT JOIN setor s ON s.seto_id = f.seto_id
          WHERE f.flux_publicado = 1 OR :todos = 1 ORDER BY f.flux_ordem, f.flux_titulo',
        ['todos' => $incluirOcultos ? 1 : 0]
    );
}

/** Fluxo completo (com passos e documentos) por slug ou id. */
function buscarFluxo(string $slugOuId, bool $incluirOculto = false): ?array
{
    $fluxo = consultarUm(
        'SELECT ' . colunasComApelido('fluxo', 'f') . ', s.seto_nome AS setor_nome, s.seto_sigla AS setor_sigla
           FROM fluxo f LEFT JOIN setor s ON s.seto_id = f.seto_id
          WHERE f.flux_slug = ? OR f.flux_id = ?',
        [$slugOuId, ctype_digit($slugOuId) ? (int) $slugOuId : 0]
    );
    if (!$fluxo || (!$incluirOculto && !(int) $fluxo['publicado'])) {
        return null;
    }
    $fluxo['passos'] = array_column(consultar('SELECT fpasso_nome AS nome FROM fluxo_passo WHERE flux_id = ? ORDER BY fpasso_ordem, fpasso_id', [$fluxo['id']]), 'nome');
    $fluxo['documentos'] = array_column(consultar('SELECT fdocu_nome AS nome FROM fluxo_documento WHERE flux_id = ? ORDER BY fdocu_ordem, fdocu_id', [$fluxo['id']]), 'nome');
    return $fluxo;
}

/** Cria ($id null) ou atualiza um fluxo com passos e documentos. Devolve o id. */
function salvarFluxo(?int $id, array $dados): int
{
    $pdo = banco();
    $pdo->beginTransaction();
    try {
        $campos = [$dados['titulo'], $dados['resumo'] ?: null, $dados['setor_id'] ?: null, $dados['canal'] ?: null, $dados['prazo'] ?: null, $dados['publicado'] ? 1 : 0];
        if ($id === null) {
            executar(
                'INSERT INTO fluxo (flux_titulo, flux_resumo, seto_id, flux_canal, flux_prazo, flux_publicado, flux_slug, flux_ordem) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                array_merge($campos, [gerarIdLivre('fluxo', $dados['titulo'], 'flux_slug'), (int) consultarValor('SELECT COALESCE(MAX(flux_ordem), 0) + 1 FROM fluxo')])
            );
            $id = (int) $pdo->lastInsertId();
        } else {
            executar(
                'UPDATE fluxo SET flux_titulo = ?, flux_resumo = ?, seto_id = ?, flux_canal = ?, flux_prazo = ?, flux_publicado = ? WHERE flux_id = ?',
                array_merge($campos, [$id])
            );
        }
        foreach ([['fluxo_passo', 'fpasso', 'passos'], ['fluxo_documento', 'fdocu', 'documentos']] as [$tabela, $prefixo, $chave]) {
            executar("DELETE FROM {$tabela} WHERE flux_id = ?", [$id]);
            foreach (array_values($dados[$chave]) as $posicao => $nome) {
                executar("INSERT INTO {$tabela} (flux_id, {$prefixo}_nome, {$prefixo}_ordem) VALUES (?, ?, ?)", [$id, $nome, $posicao]);
            }
        }
        $pdo->commit();
        return $id;
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

function excluirFluxo(int $id): void
{
    executar('DELETE FROM fluxo WHERE flux_id = ?', [$id]);
}

function alternarFluxoPublicado(int $id): void
{
    executar('UPDATE fluxo SET flux_publicado = 1 - flux_publicado WHERE flux_id = ?', [$id]);
}

/* =============================================================
 * ACESSIBILIDADE FÍSICA DAS SALAS (I3)
 * Cada item: 1 = tem, 0 = não tem, NULL = não informado (nada é presumido).
 * ============================================================= */

function itensAcessibilidadeSala(): array
{
    return [
        'rampa' => 'Rampa ou acesso sem degrau',
        'elevador' => 'Chega de elevador',
        'banheiro_adaptado' => 'Banheiro adaptado perto',
        'balcao_baixo' => 'Balcão de atendimento baixo',
        'piso_tatil' => 'Piso tátil até a porta',
        'porta_larga' => 'Porta larga (cadeira de rodas)',
    ];
}

/** Dados de acessibilidade de uma sala (todos NULL = não informado quando não há cadastro). */
function buscarAcessibilidadeSala(string $idSala): array
{
    $linha = consultarUm('SELECT ' . colunasComApelido('acessibilidade_sala') . ' FROM acessibilidade_sala WHERE sala_id = ?', [$idSala]) ?? [];
    $dados = [];
    foreach (array_keys(itensAcessibilidadeSala()) as $item) {
        $dados[$item] = isset($linha[$item]) ? (int) $linha[$item] : null;
    }
    $dados['observacao'] = $linha['observacao'] ?? null;
    return $dados;
}

function salvarAcessibilidadeSala(string $idSala, array $dados): void
{
    $itens = array_keys(itensAcessibilidadeSala());
    $valores = array_map(static function ($item) use ($dados) { return $dados[$item]; }, $itens);
    $colunas = array_map(static function ($item) { return 'acess_' . $item; }, $itens); // rampa -> acess_rampa
    executar(
        'INSERT INTO acessibilidade_sala (sala_id, ' . implode(', ', $colunas) . ', acess_observacao) VALUES (?, ' . implode(', ', array_fill(0, count($colunas), '?')) . ', ?)
         ON DUPLICATE KEY UPDATE ' . implode(', ', array_map(static function ($coluna) { return "{$coluna} = VALUES({$coluna})"; }, $colunas)) . ', acess_observacao = VALUES(acess_observacao)',
        array_merge([$idSala], $valores, [$dados['observacao'] ?: null])
    );
}

/** Mapa sala_id => true quando a sala é acessível sem escada (térreo ou elevador informado). */
function salasSemEscada(): array
{
    $comElevador = array_column(consultar('SELECT sala_id FROM acessibilidade_sala WHERE acess_elevador = 1'), 'sala_id', 'sala_id');
    $resultado = [];
    foreach (listarMapaSalas()['salas'] as $sala) {
        $resultado[$sala['id']] = $sala['andar'] === 'terreo' || isset($comElevador[$sala['id']]);
    }
    return $resultado;
}

/* =============================================================
 * TRADUÇÕES (I6) — rascunho -> revisado -> publicado
 * ============================================================= */

/** Traduções PUBLICADAS de um idioma, por "entidade|id|campo". Lidas uma vez por requisição. */
function traducoesPublicadas(string $idioma): array
{
    static $cache = [];
    if (!isset($cache[$idioma])) {
        $cache[$idioma] = [];
        foreach (consultar(
            "SELECT trad_entidade AS entidade, trad_entidade_id AS entidade_id, trad_campo AS campo, trad_texto AS texto,
                    trad_audio_url AS audio_url, trad_pictograma AS pictograma
               FROM traducao WHERE trad_idioma = ? AND trad_status = 'publicado'",
            [$idioma]
        ) as $linha) {
            $cache[$idioma][$linha['entidade'] . '|' . $linha['entidade_id'] . '|' . $linha['campo']] = $linha;
        }
    }
    return $cache[$idioma];
}

function contarTraducoesPublicadas(string $idioma): int
{
    static $cache = [];
    if (!isset($cache[$idioma])) {
        $cache[$idioma] = (int) consultarValor("SELECT COUNT(*) FROM traducao WHERE trad_idioma = ? AND trad_status = 'publicado'", [$idioma]);
    }
    return $cache[$idioma];
}

/** Todas as traduções de um idioma (qualquer status), por "entidade|id|campo" — pro admin. */
function listarTraducoesDoIdioma(string $idioma): array
{
    $porChave = [];
    foreach (consultar('SELECT ' . colunasComApelido('traducao') . ' FROM traducao WHERE trad_idioma = ?', [$idioma]) as $linha) {
        $porChave[$linha['entidade'] . '|' . $linha['entidade_id'] . '|' . $linha['campo']] = $linha;
    }
    return $porChave;
}

/** Cria ou atualiza uma tradução. Quem chama decide o status (e confere as regras do fluxo). */
function salvarTraducao(string $entidade, string $idEntidade, string $campo, string $idioma, array $dados): void
{
    executar(
        'INSERT INTO traducao (trad_entidade, trad_entidade_id, trad_campo, trad_idioma, trad_texto, trad_status, trad_validado_por,
                               trad_validado_em, trad_audio_url, trad_pictograma)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE trad_texto = VALUES(trad_texto), trad_status = VALUES(trad_status), trad_validado_por = VALUES(trad_validado_por),
                                 trad_validado_em = VALUES(trad_validado_em), trad_audio_url = VALUES(trad_audio_url), trad_pictograma = VALUES(trad_pictograma)',
        [$entidade, $idEntidade, $campo, $idioma, $dados['texto'], $dados['status'], $dados['validado_por'] ?: null,
            $dados['validado_em'] ?: null, $dados['audio_url'] ?: null, $dados['pictograma'] ?: null]
    );
}

function buscarTraducao(string $entidade, string $idEntidade, string $campo, string $idioma): ?array
{
    return consultarUm(
        'SELECT ' . colunasComApelido('traducao') . ' FROM traducao WHERE trad_entidade = ? AND trad_entidade_id = ? AND trad_campo = ? AND trad_idioma = ?',
        [$entidade, $idEntidade, $campo, $idioma]
    );
}
