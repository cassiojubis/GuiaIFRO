-- =============================================================
-- BANCO DO GUIA IFRO (MariaDB 10.4+ / MySQL 8)
-- Desenho das tabelas em docs/BANCO.md.
-- Pode rodar de novo sem medo: tudo é "IF NOT EXISTS".
-- Quem roda isso é o bin/instalar-banco.php (com um usuário que pode criar tabela).
-- =============================================================

SET NAMES utf8mb4;

-- ---------- MODELO PRINCIPAL: categoria -> setor -> sala ----------

CREATE TABLE IF NOT EXISTS categoria (
cate_id          VARCHAR(60)  NOT NULL,
cate_nome        VARCHAR(150) NOT NULL,
cate_icone       VARCHAR(60)  NOT NULL DEFAULT 'shapes',
cate_cor         VARCHAR(20)  NOT NULL DEFAULT 'green',
cate_ordem       INT          NOT NULL DEFAULT 0,
cate_ativo       TINYINT(1)   NOT NULL DEFAULT 1,
cate_atualizado_em DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (cate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS setor (
seto_id 			 VARCHAR(60)  NOT NULL,
cate_id 	         VARCHAR(60)  NULL,
seto_nome            VARCHAR(200) NOT NULL,
seto_sigla           VARCHAR(30)  NOT NULL DEFAULT '',
seto_icone           VARCHAR(60)  NOT NULL DEFAULT 'building-2',
seto_cor             VARCHAR(20)  NOT NULL DEFAULT 'green',
seto_descricao       TEXT         NULL,
seto_descricao_curta VARCHAR(255) NULL,
seto_texto_facil     TEXT         NULL COMMENT 'versão em Leitura Fácil (I4)',
seto_localizacao     VARCHAR(255) NULL,
seto_bloco           VARCHAR(150) NULL,
seto_sala_texto      VARCHAR(150) NULL,
seto_telefone        VARCHAR(60)  NULL,
seto_email           VARCHAR(150) NULL,
seto_responsavel     VARCHAR(150) NULL,
seto_video           VARCHAR(255) NULL,
seto_horario_texto   VARCHAR(255) NULL COMMENT 'só observação; o horário de verdade fica em setor_horario',
seto_ordem           INT          NOT NULL DEFAULT 0,
seto_ativo           TINYINT(1)   NOT NULL DEFAULT 1,
seto_fila_status     VARCHAR(20)  NULL COMMENT 'tranquilo / movimentado / cheio (W5)',
seto_fila_atualizada_em DATETIME  NULL,
seto_atualizado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
seto_atualizado_por  VARCHAR(150) NULL,
PRIMARY KEY (seto_id),
	KEY idx_setor_categoria (cate_id),
	CONSTRAINT fk_setor_categoria FOREIGN KEY (cate_id) REFERENCES categoria (cate_id)
	ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS setor_equipe (
    equi_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    seto_id VARCHAR(60) NOT NULL,
    equi_nome VARCHAR(150) NOT NULL,
    equi_cargo VARCHAR(100) NULL,
    equi_ordem INT NOT NULL DEFAULT 0,
    PRIMARY KEY (equi_id),
    KEY idx_equipe_setor (seto_id),
    CONSTRAINT fk_equipe_setor
        FOREIGN KEY (seto_id) REFERENCES setor (seto_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS setor_servico (
serv_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
seto_id VARCHAR(60) NOT NULL,
serv_nome VARCHAR(255) NOT NULL,
serv_ordem INT NOT NULL DEFAULT 0,
    PRIMARY KEY (serv_id),
    KEY idx_servico_setor (seto_id),
    CONSTRAINT fk_servico_setor
        FOREIGN KEY (seto_id) REFERENCES setor (seto_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS setor_documento (
    docu_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    seto_id VARCHAR(60) NOT NULL,
    docu_nome VARCHAR(255) NOT NULL,
    docu_arquivo VARCHAR(255) NULL,
    docu_ordem INT NOT NULL DEFAULT 0,
    PRIMARY KEY (docu_id),
    KEY idx_documento_setor (seto_id),
    CONSTRAINT fk_documento_setor
        FOREIGN KEY (seto_id) REFERENCES setor (seto_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- dia_semana: 0 = domingo ... 6 = sábado (igual ao date('w') do PHP)
CREATE TABLE IF NOT EXISTS setor_horario (
    hora_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    seto_id VARCHAR(60) NOT NULL,
    hora_dia_semana TINYINT NOT NULL,
    hora_abre TIME NOT NULL,
    hora_fecha TIME NOT NULL,
    hora_observacao VARCHAR(150) NULL,
    PRIMARY KEY (hora_id),
    KEY idx_horario_setor_dia (seto_id, hora_dia_semana),
    CONSTRAINT fk_horario_setor
        FOREIGN KEY (seto_id) REFERENCES setor (seto_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feriados, recessos e dias especiais. seto_id NULL = vale para o campus todo.
-- fechado = 1: fechado o dia inteiro. fechado = 0 com abre/fecha: horário especial.
CREATE TABLE IF NOT EXISTS setor_excecao (
    exce_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    seto_id VARCHAR(60) NULL,
    exce_data DATE NOT NULL,
    exce_fechado TINYINT(1) NOT NULL DEFAULT 1,
    exce_abre TIME NULL,
    exce_fecha TIME NULL,
    exce_motivo VARCHAR(150) NOT NULL,
    PRIMARY KEY (exce_id),
    KEY idx_excecao_data (exce_data),
    KEY idx_excecao_setor (seto_id),
    CONSTRAINT fk_excecao_setor
        FOREIGN KEY (seto_id) REFERENCES setor (seto_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS andar (
anda_id     VARCHAR(20) NOT NULL,
anda_nome   VARCHAR(60) NOT NULL,
anda_ordem  INT         NOT NULL DEFAULT 0,
    PRIMARY KEY (anda_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Salas do prédio (ficha, QR, placa, mapa em texto). O desenho do mapa fica em data/mapa-campus.json.
-- Ordem dentro de cada ala: 1 = mais perto da escada. lado NULL nas pontas.
CREATE TABLE IF NOT EXISTS sala (
    sala_id VARCHAR(60) NOT NULL,
    sala_nome VARCHAR(150) NOT NULL,
    sala_nome_curto VARCHAR(60) NOT NULL,
    sala_funcao VARCHAR(255) NULL,
    anda_id VARCHAR(20) NOT NULL,
    sala_ala ENUM('esquerda','direita') NOT NULL,
    sala_ordem INT NOT NULL,
    sala_lado ENUM('frente','fundos') NULL,
    sala_ponta TINYINT(1) NOT NULL DEFAULT 0,
    sala_vazia TINYINT(1) NOT NULL DEFAULT 0,
    seto_id VARCHAR(60) NULL,
    PRIMARY KEY (sala_id),
    KEY idx_sala_andar (anda_id, sala_ala, sala_ordem),
    KEY idx_sala_setor (seto_id),
    CONSTRAINT fk_sala_andar
        FOREIGN KEY (anda_id) REFERENCES andar (anda_id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_sala_setor
        FOREIGN KEY (seto_id) REFERENCES setor (seto_id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Acessibilidade física de cada sala (I3). NULL = não informado.
CREATE TABLE IF NOT EXISTS acessibilidade_sala (
    sala_id VARCHAR(60) NOT NULL,
    acess_rampa TINYINT(1) NULL,
    acess_elevador TINYINT(1) NULL,
    acess_banheiro_adaptado TINYINT(1) NULL,
    acess_balcao_baixo TINYINT(1) NULL,
    acess_piso_tatil TINYINT(1) NULL,
    acess_porta_larga TINYINT(1) NULL,
    acess_observacao VARCHAR(255) NULL,
    acess_atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (sala_id),
    CONSTRAINT fk_acess_sala
        FOREIGN KEY (sala_id) REFERENCES sala (sala_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cenário do editor antigo (admin › Salas) (B6). x/z em metros a partir do centro da entrada.
-- inicio/fim preenchidos = peça temporária (W3: estande, obra, evento).
CREATE TABLE IF NOT EXISTS peca_mapa (
peca_id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
peca_tipo          VARCHAR(30)  NOT NULL,
peca_nome          VARCHAR(150) NOT NULL DEFAULT '',
peca_x             DECIMAL(7,2) NOT NULL DEFAULT 0,
peca_z             DECIMAL(7,2) NOT NULL DEFAULT 0,
peca_largura       DECIMAL(7,2) NOT NULL DEFAULT 4,
peca_profundidade  DECIMAL(7,2) NOT NULL DEFAULT 4,
peca_rotacao       DECIMAL(6,2) NOT NULL DEFAULT 0,
peca_inicio        DATETIME     NULL,
peca_fim           DATETIME     NULL,
    PRIMARY KEY (peca_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- CONTEÚDO ----------

CREATE TABLE IF NOT EXISTS aviso (
avis_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
avis_titulo     VARCHAR(200) NOT NULL,
avis_mensagem   TEXT         NOT NULL,
avis_nivel      ENUM('normal','urgente') NOT NULL DEFAULT 'normal',
avis_inicio     DATETIME     NULL,
avis_fim        DATETIME     NULL,
avis_publicado  TINYINT(1)   NOT NULL DEFAULT 1,
avis_ordem      INT          NOT NULL DEFAULT 0,
avis_criado_em  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (avis_id),
    KEY idx_aviso_periodo (avis_publicado, avis_inicio, avis_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS noticia (
noti_id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
noti_slug          VARCHAR(160) NOT NULL,
noti_titulo        VARCHAR(200) NOT NULL,
noti_resumo        VARCHAR(400) NULL,
noti_conteudo      TEXT         NULL,
noti_categoria     VARCHAR(60)  NULL,
noti_publicada_em  DATE         NOT NULL,
noti_destaque      TINYINT(1)   NOT NULL DEFAULT 0,
noti_publicado     TINYINT(1)   NOT NULL DEFAULT 1,
noti_ordem         INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (noti_id),
    UNIQUE KEY uq_noticia_slug (noti_slug),
    KEY idx_noticia_data (noti_publicado, noti_publicada_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Calendário acadêmico: o recurso está guardado em _futuro/ (o site não usa esta tabela por enquanto).
CREATE TABLE IF NOT EXISTS evento (
even_id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
even_titulo          VARCHAR(200) NOT NULL,
even_descricao       TEXT         NULL,
even_inicio          DATE         NOT NULL,
even_fim             DATE         NULL,
even_tipo            ENUM('evento','feriado','recesso','letivo','prova') NOT NULL DEFAULT 'evento',
even_periodo_letivo  VARCHAR(20)  NULL,
even_local_sala_id   VARCHAR(60)  NULL,
even_publicado       TINYINT(1)   NOT NULL DEFAULT 1,
even_ordem           INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (even_id),
    KEY idx_evento_data (even_publicado, even_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faq (
faq_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
faq_pergunta   VARCHAR(300) NOT NULL,
faq_resposta   TEXT         NOT NULL,
faq_publicado  TINYINT(1)   NOT NULL DEFAULT 1,
faq_ordem      INT          NOT NULL DEFAULT 0,
faq_atualizado_em DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (faq_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Liga uma pergunta a um ou mais setores. Pergunta sem ligação = FAQ geral.
CREATE TABLE IF NOT EXISTS faq_setor (
    faq_id INT UNSIGNED NOT NULL,
    seto_id VARCHAR(60) NOT NULL,
    PRIMARY KEY (faq_id, seto_id),
    KEY idx_faq_setor_setor (seto_id),
    CONSTRAINT fk_faqsetor_faq
        FOREIGN KEY (faq_id) REFERENCES faq (faq_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_faqsetor_setor
        FOREIGN KEY (seto_id) REFERENCES setor (seto_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS link_util (
link_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
link_titulo     VARCHAR(150) NOT NULL,
link_url        VARCHAR(255) NOT NULL,
link_publicado  TINYINT(1)   NOT NULL DEFAULT 1,
link_ordem      INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (link_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS texto_institucional (
text_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
text_titulo     VARCHAR(150) NOT NULL,
text_conteudo   TEXT         NOT NULL,
text_publicado  TINYINT(1)   NOT NULL DEFAULT 1,
text_ordem      INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (text_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS atalho_home (
atal_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
atal_titulo     VARCHAR(80)  NOT NULL,
atal_descricao  VARCHAR(120) NOT NULL DEFAULT '',
atal_icone      VARCHAR(60)  NOT NULL DEFAULT 'building-2',
atal_cor        VARCHAR(20)  NOT NULL DEFAULT 'green',
atal_url        VARCHAR(255) NOT NULL,
atal_ordem      INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (atal_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configurações editáveis no admin (contato geral, redes sociais, link do WhatsApp...)
CREATE TABLE IF NOT EXISTS configuracao (
conf_chave  VARCHAR(80) NOT NULL,
conf_valor  TEXT        NULL,
    PRIMARY KEY (conf_chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- USUÁRIOS E PERMISSÕES ----------

CREATE TABLE IF NOT EXISTS perfil (
perf_id    VARCHAR(30) NOT NULL,
perf_nome  VARCHAR(80) NOT NULL,
    PRIMARY KEY (perf_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario (
    usua_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usua_nome VARCHAR(150) NOT NULL,
    usua_usuario VARCHAR(60) NOT NULL,
    usua_email VARCHAR(150) NULL,
    usua_senha_hash VARCHAR(255) NOT NULL,
    perf_id VARCHAR(30) NOT NULL,
    usua_ativo TINYINT(1) NOT NULL DEFAULT 1,
    usua_sessao_versao INT UNSIGNED NOT NULL DEFAULT 1,
    usua_ultimo_login_em DATETIME NULL,
    usua_criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usua_id),
    UNIQUE KEY uq_usuario_usuario (usua_usuario),
    UNIQUE KEY uq_usuario_email (usua_email),
    CONSTRAINT fk_usuario_perfil
        FOREIGN KEY (perf_id) REFERENCES perfil (perf_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_setor (
    usua_id INT UNSIGNED NOT NULL,
    seto_id VARCHAR(60) NOT NULL,
    PRIMARY KEY (usua_id, seto_id),
    KEY idx_usuario_setor_setor (seto_id),
    CONSTRAINT fk_ususetor_usuario
        FOREIGN KEY (usua_id) REFERENCES usuario (usua_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_ususetor_setor
        FOREIGN KEY (seto_id) REFERENCES setor (seto_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- REGISTROS ----------

-- Limite de envios/tentativas (contato e login).
-- limi_chave_hash = hash do IP (ou usuário+IP) com sal: não dá pra saber o IP olhando a tabela.
CREATE TABLE IF NOT EXISTS limite (
limi_id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
limi_acao        VARCHAR(40)  NOT NULL,
limi_chave_hash  CHAR(64)     NOT NULL,
limi_criado_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (limi_id),
    KEY idx_limite_busca (limi_acao, limi_chave_hash, limi_criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auditoria (
audi_id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
usua_id    		   INT UNSIGNED NULL,
audi_usuario_nome		   VARCHAR(150) NOT NULL,
audi_acao          VARCHAR(30)  NOT NULL COMMENT 'criar, editar, excluir, ativar, desativar...',
audi_tabela        VARCHAR(60)  NOT NULL,
audi_registro_id   VARCHAR(60)  NULL,
audi_resumo        VARCHAR(255) NULL,
audi_antes         LONGTEXT     NULL COMMENT 'JSON',
audi_depois        LONGTEXT     NULL COMMENT 'JSON',
audi_quando        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (audi_id),
    KEY idx_auditoria_quando (audi_quando),
    KEY idx_auditoria_registro (audi_tabela, audi_registro_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contato_mensagem (
cont_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
cont_nome       VARCHAR(150) NOT NULL,
cont_email      VARCHAR(150) NOT NULL,
cont_telefone   VARCHAR(40)  NULL,
cont_assunto    VARCHAR(100) NOT NULL,
cont_mensagem   TEXT         NOT NULL,
cont_email_enviado TINYINT(1) NOT NULL DEFAULT 0,
cont_criada_em  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
cont_lida_em    DATETIME     NULL,
    PRIMARY KEY (cont_id),
    KEY idx_contato_criada (cont_criada_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sinônimos da busca (N1): "declaração" acha o CRA, "auxílio" acha a CAED...
CREATE TABLE IF NOT EXISTS sinonimo (
    sino_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    seto_id VARCHAR(60) NOT NULL,
    sino_nome VARCHAR(100) NOT NULL,
    sino_ordem INT NOT NULL DEFAULT 0,
    PRIMARY KEY (sino_id),
    KEY idx_sinonimo_setor (seto_id),
    CONSTRAINT fk_sinonimo_setor
        FOREIGN KEY (seto_id) REFERENCES setor (seto_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- "Como faço para..." (N11): fluxos passo a passo.
CREATE TABLE IF NOT EXISTS fluxo (
    flux_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    flux_slug VARCHAR(160) NOT NULL,
    flux_titulo VARCHAR(200) NOT NULL,
    flux_resumo VARCHAR(400) NULL,
    seto_id VARCHAR(60) NULL,
    flux_canal ENUM('suap','presencial','ambos') NULL,
    flux_prazo VARCHAR(150) NULL,
    flux_publicado TINYINT(1) NOT NULL DEFAULT 1,
    flux_ordem INT NOT NULL DEFAULT 0,
    flux_atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (flux_id),
    UNIQUE KEY uq_fluxo_slug (flux_slug),
    CONSTRAINT fk_fluxo_setor
        FOREIGN KEY (seto_id) REFERENCES setor (seto_id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fluxo_passo (
    fpasso_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    flux_id INT UNSIGNED NOT NULL,
    fpasso_nome VARCHAR(400) NOT NULL,
    fpasso_ordem INT NOT NULL DEFAULT 0,
    PRIMARY KEY (fpasso_id),
    KEY idx_passo_fluxo (flux_id),
    CONSTRAINT fk_passo_fluxo
        FOREIGN KEY (flux_id) REFERENCES fluxo (flux_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fluxo_documento (
    fdocu_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    flux_id INT UNSIGNED NOT NULL,
    fdocu_nome VARCHAR(255) NOT NULL,
    fdocu_ordem INT NOT NULL DEFAULT 0,
    PRIMARY KEY (fdocu_id),
    KEY idx_doc_fluxo (flux_id),
    CONSTRAINT fk_doc_fluxo
        FOREIGN KEY (flux_id) REFERENCES fluxo (flux_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Traduções de conteúdo (I6). status: rascunho -> revisado -> publicado.
-- Oro Wari só aparece no site quando status = publicado (validado por falante).
CREATE TABLE IF NOT EXISTS traducao (
    trad_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    trad_entidade VARCHAR(40) NOT NULL,
    trad_entidade_id VARCHAR(60) NOT NULL DEFAULT '',
    trad_campo VARCHAR(60) NOT NULL,
    trad_idioma VARCHAR(10) NOT NULL,
    trad_texto TEXT NOT NULL,
    trad_status ENUM('rascunho','revisado','publicado') NOT NULL DEFAULT 'rascunho',
    trad_validado_por VARCHAR(150) NULL,
    trad_validado_em DATETIME NULL,
    trad_audio_url VARCHAR(255) NULL,
    trad_pictograma VARCHAR(120) NULL,
    trad_atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (trad_id),
    UNIQUE KEY uq_traducao (trad_entidade, trad_entidade_id, trad_campo, trad_idioma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================
-- REGRAS DE NEGÓCIO NO BANCO (VIEW, PROCEDURE e TRIGGER)
-- Autor: Cayo Rodrigues do Santos
-- Data: 13/11/2026
-- Objetivo: levar para dentro do banco as regras mais importantes do
-- horário dos setores, para que elas valham mesmo fora do site.
--
-- Uma VIEW, uma PROCEDURE e um TRIGGER (em duas partes: inserir e alterar).
-- Cada objeto é apagado e criado de novo, então dá para rodar outra vez.
-- O bin/instalar-banco.php entende o DELIMITER usado na procedure e no trigger.
-- =============================================================
-- =============================================================
-- 1) VIEW — vw_situacao_setores_hoje
-- -------------------------------------------------------------
-- Autor: Cayo Rodrigues do Santos
-- Data: 13/11/2026
-- Objetivo: responder, numa consulta só, a pergunta que o aluno faz
-- quando chega no campus: "esse setor está aberto agora? onde ele fica?".
-- É a mesma informação da faixa "Hoje no campus" da página inicial.
--
-- Regras de negócio:
--   R1. Só aparecem setores ativos (setor desativado não existe para o público).
--   R2. Feriado ou recesso vence o horário normal: se existe uma exceção
--       "fechado" para hoje — do setor ou do campus inteiro (seto_id NULL) —
--       o setor está "Fechado hoje", não importa o horário da semana.
--   R3. Horário especial do dia (exceção com abre/fecha) substitui o horário
--       normal só naquele dia.
--   R4. Sem exceção, vale o horário da semana (setor_horario). O setor está
--       aberto se a hora atual está dentro de alguma faixa de hoje.
--       Faixa 08:00–12:00: às 12:00 em ponto já conta como fechado.
--   R5. Setor sem nenhum horário cadastrado aparece como "Horário não
--       informado" (e não como "fechado", para não enganar quem procura).
--   R6. O local mostrado é a primeira sala ligada ao setor (com o andar);
--       se ele não tem sala no mapa, usa o texto de localização do setor.
--
-- Observação: "agora" é a hora do servidor do banco (NOW / CURTIME).
-- No banco: 0 = domingo em hora_dia_semana; DAYOFWEEK() começa em 1 = domingo.
-- =============================================================

DROP VIEW IF EXISTS vw_situacao_setores_hoje;

CREATE VIEW vw_situacao_setores_hoje AS
SELECT
    s.seto_id                                   AS setor_id,
    s.seto_nome                                 AS setor,
    s.seto_sigla                                AS sigla,
    COALESCE(c.cate_nome, 'Sem categoria')      AS categoria,

    -- R6: onde fica
    COALESCE(
        (SELECT CONCAT(sa.sala_nome, ' (', an.anda_nome, ')')
           FROM sala sa
           JOIN andar an ON an.anda_id = sa.anda_id
          WHERE sa.seto_id = s.seto_id
          ORDER BY an.anda_ordem, sa.sala_ordem
          LIMIT 1),
        NULLIF(s.seto_localizacao, ''),
        'Local não informado'
    )                                           AS onde_fica,

    -- horário de hoje, já formatado: "08:00 às 12:00 | 14:00 às 18:00"
    -- (R2/R3: em feriado não tem horário; em horário especial, mostra o especial)
    CASE
        WHEN EXISTS (SELECT 1 FROM setor_excecao e
                      WHERE e.exce_data = CURDATE() AND e.exce_fechado = 1
                        AND (e.seto_id = s.seto_id OR e.seto_id IS NULL))
            THEN NULL
        ELSE COALESCE(
            (SELECT GROUP_CONCAT(CONCAT(TIME_FORMAT(e.exce_abre, '%H:%i'), ' às ',
                                        TIME_FORMAT(e.exce_fecha, '%H:%i'))
                                 ORDER BY e.exce_abre SEPARATOR ' | ')
               FROM setor_excecao e
              WHERE e.exce_data = CURDATE() AND e.exce_fechado = 0
                AND e.exce_abre IS NOT NULL AND e.exce_fecha IS NOT NULL
                AND (e.seto_id = s.seto_id OR e.seto_id IS NULL)),
            (SELECT GROUP_CONCAT(CONCAT(TIME_FORMAT(h.hora_abre, '%H:%i'), ' às ',
                                        TIME_FORMAT(h.hora_fecha, '%H:%i'))
                                 ORDER BY h.hora_abre SEPARATOR ' | ')
               FROM setor_horario h
              WHERE h.seto_id = s.seto_id
                AND h.hora_dia_semana = DAYOFWEEK(CURDATE()) - 1))
    END                                         AS horario_de_hoje,

    CASE
        -- R2: feriado/recesso (do setor ou do campus inteiro)
        WHEN EXISTS (SELECT 1 FROM setor_excecao e
                      WHERE e.exce_data = CURDATE()
                        AND e.exce_fechado = 1
                        AND (e.seto_id = s.seto_id OR e.seto_id IS NULL))
            THEN 'Fechado hoje'

        -- R3: horário especial só hoje
        WHEN EXISTS (SELECT 1 FROM setor_excecao e
                      WHERE e.exce_data = CURDATE()
                        AND e.exce_fechado = 0
                        AND e.exce_abre IS NOT NULL AND e.exce_fecha IS NOT NULL
                        AND (e.seto_id = s.seto_id OR e.seto_id IS NULL))
            THEN IF(EXISTS (SELECT 1 FROM setor_excecao e
                             WHERE e.exce_data = CURDATE()
                               AND e.exce_fechado = 0
                               AND (e.seto_id = s.seto_id OR e.seto_id IS NULL)
                               AND CURTIME() >= e.exce_abre AND CURTIME() < e.exce_fecha),
                    'Aberto agora (horário especial)',
                    'Fechado agora (horário especial)')

        -- R5: nada cadastrado
        WHEN NOT EXISTS (SELECT 1 FROM setor_horario h WHERE h.seto_id = s.seto_id)
            THEN 'Horário não informado'

        -- R4: horário normal da semana
        WHEN EXISTS (SELECT 1 FROM setor_horario h
                      WHERE h.seto_id = s.seto_id
                        AND h.hora_dia_semana = DAYOFWEEK(CURDATE()) - 1
                        AND CURTIME() >= h.hora_abre AND CURTIME() < h.hora_fecha)
            THEN 'Aberto agora'

        ELSE 'Fechado agora'
    END                                         AS situacao,

    -- motivo do dia, se houver (o do setor tem prioridade sobre o do campus)
    (SELECT e.exce_motivo FROM setor_excecao e
      WHERE e.exce_data = CURDATE()
        AND (e.seto_id = s.seto_id OR e.seto_id IS NULL)
      ORDER BY e.seto_id IS NULL
      LIMIT 1)                                  AS aviso_do_dia

FROM setor s
LEFT JOIN categoria c ON c.cate_id = s.cate_id
-- R1: só setores ativos
WHERE s.seto_ativo = 1;


-- =============================================================
-- 2) PROCEDURE — sp_registrar_excecao_horario
-- -------------------------------------------------------------
-- Autor: Cayo Rodrigues do Santos
-- Data: 13/11/2026
-- Objetivo: cadastrar um feriado, recesso ou horário especial,
-- para um setor ou para o campus inteiro, já registrando na auditoria
-- quem fez. É o que a tela "Horários do setor" do painel faz.
--
-- Parâmetros:
--   p_usuario_id  quem está cadastrando (tabela usuario)
--   p_setor_id    setor (ex.: 'cra'), ou NULL para valer para o campus todo
--   p_data        dia da exceção
--   p_fechado     1 = fechado o dia inteiro | 0 = horário especial
--   p_abre        hora que abre (só quando p_fechado = 0)
--   p_fecha       hora que fecha (só quando p_fechado = 0)
--   p_motivo      ex.: 'Feriado de Finados', 'Conselho de classe'
--
-- Regras de negócio:
--   R1. Só usuário ativo pode cadastrar.
--   R2. O motivo é obrigatório (é ele que aparece para o aluno no site).
--   R3. Não se cadastra exceção em data que já passou.
--   R4. O setor informado precisa existir.
--   R5. Exceção para o campus inteiro (setor NULL) só o administrador geral.
--   R6. O responsável de setor só cadastra para o setor que é dele
--       (tabela usuario_setor). O administrador geral pode em qualquer um.
--   R7. Horário especial precisa das duas horas, e a de fechar depois da de
--       abrir. Se for "fechado o dia inteiro", as horas são ignoradas.
--   R8. Não pode haver duas exceções no mesmo dia para o mesmo setor
--       (ou duas para o campus inteiro no mesmo dia).
--   R9. Tudo ou nada: a exceção e o registro na auditoria entram juntos.
--       Se qualquer parte falhar, nada é gravado.
--
-- Exemplo:
--   CALL sp_registrar_excecao_horario(1, NULL, '2026-11-20', 1, NULL, NULL, 'Dia da Consciência Negra');
--   CALL sp_registrar_excecao_horario(2, 'cra', '2026-11-25', 0, '08:00', '11:00', 'Atendimento só pela manhã');
-- =============================================================

DROP PROCEDURE IF EXISTS sp_registrar_excecao_horario;

DELIMITER $$

CREATE PROCEDURE sp_registrar_excecao_horario(
    IN p_usuario_id INT UNSIGNED,
    IN p_setor_id   VARCHAR(60),
    IN p_data       DATE,
    IN p_fechado    TINYINT,
    IN p_abre       TIME,
    IN p_fecha      TIME,
    IN p_motivo     VARCHAR(150)
)
BEGIN
    DECLARE v_nome_usuario VARCHAR(150);
    DECLARE v_perfil       VARCHAR(30);
    DECLARE v_nome_setor   VARCHAR(200);
    DECLARE v_id_novo      INT UNSIGNED;
    DECLARE v_mensagem     VARCHAR(255);

    -- R9: qualquer erro desfaz tudo e devolve a mensagem para quem chamou
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    -- limpa o texto e as horas antes de validar
    SET p_motivo = TRIM(p_motivo);
    SET p_setor_id = NULLIF(TRIM(p_setor_id), '');
    IF p_fechado = 1 THEN
        SET p_abre = NULL;
        SET p_fecha = NULL;
    END IF;

    -- R1
    SELECT usua_nome, perf_id INTO v_nome_usuario, v_perfil
      FROM usuario
     WHERE usua_id = p_usuario_id AND usua_ativo = 1;
    IF v_nome_usuario IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Usuário não encontrado ou desativado.';
    END IF;

    -- R2
    IF p_motivo IS NULL OR CHAR_LENGTH(p_motivo) < 3 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Informe o motivo (ex.: Feriado de Finados). É ele que aparece para o aluno.';
    END IF;

    -- R3
    IF p_data IS NULL OR p_data < CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A data precisa ser hoje ou um dia que ainda vai chegar.';
    END IF;

    IF p_setor_id IS NOT NULL THEN
        -- R4
        SELECT seto_nome INTO v_nome_setor FROM setor WHERE seto_id = p_setor_id;
        IF v_nome_setor IS NULL THEN
            SET v_mensagem = CONCAT('O setor "', p_setor_id, '" não existe.');
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_mensagem;
        END IF;

        -- R6
        IF v_perfil <> 'admin_geral'
           AND NOT EXISTS (SELECT 1 FROM usuario_setor
                            WHERE usua_id = p_usuario_id AND seto_id = p_setor_id) THEN
            SET v_mensagem = CONCAT('Você não é responsável pelo setor ', v_nome_setor, '.');
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_mensagem;
        END IF;
    ELSE
        -- R5
        IF v_perfil <> 'admin_geral' THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Só o administrador geral fecha o campus inteiro.';
        END IF;
        SET v_nome_setor = 'Campus inteiro';
    END IF;

    -- R7
    IF p_fechado = 0 AND (p_abre IS NULL OR p_fecha IS NULL OR p_fecha <= p_abre) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No horário especial, informe as duas horas, e a de fechar depois da de abrir.';
    END IF;

    -- R8 (o "<=>" compara também quando os dois são NULL = campus inteiro)
    IF EXISTS (SELECT 1 FROM setor_excecao
                WHERE exce_data = p_data AND seto_id <=> p_setor_id) THEN
        SET v_mensagem = CONCAT(v_nome_setor, ' já tem uma exceção cadastrada em ',
                                DATE_FORMAT(p_data, '%d/%m/%Y'), '. Exclua a antiga antes.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_mensagem;
    END IF;

    -- R9: grava a exceção e a auditoria juntas
    START TRANSACTION;

    INSERT INTO setor_excecao (seto_id, exce_data, exce_fechado, exce_abre, exce_fecha, exce_motivo)
    VALUES (p_setor_id, p_data, IF(p_fechado = 1, 1, 0), p_abre, p_fecha, p_motivo);
    SET v_id_novo = LAST_INSERT_ID();

    INSERT INTO auditoria (usua_id, audi_usuario_nome, audi_acao, audi_tabela, audi_registro_id,
                           audi_resumo, audi_antes, audi_depois)
    VALUES (p_usuario_id, v_nome_usuario, 'criar', 'setor_excecao', v_id_novo,
            CONCAT(v_nome_setor, ': ', IF(p_fechado = 1, 'fechado', 'horário especial'),
                   ' em ', DATE_FORMAT(p_data, '%d/%m/%Y'), ' (', p_motivo, ')'),
            NULL,
            JSON_OBJECT('setor', p_setor_id, 'data', p_data, 'fechado', p_fechado,
                        'abre', p_abre, 'fecha', p_fecha, 'motivo', p_motivo));

    COMMIT;

    -- resposta amigável para quem chamou
    SELECT v_id_novo AS id_excecao,
           CONCAT('Pronto! ', v_nome_setor, ' — ',
                  IF(p_fechado = 1, 'fechado', CONCAT('aberto das ', TIME_FORMAT(p_abre, '%H:%i'),
                                                     ' às ', TIME_FORMAT(p_fecha, '%H:%i'))),
                  ' em ', DATE_FORMAT(p_data, '%d/%m/%Y'), ' (', p_motivo, ').') AS mensagem;
END$$

DELIMITER ;


-- =============================================================
-- 3) TRIGGER — trg_horario_valida_inserir / trg_horario_valida_alterar
-- -------------------------------------------------------------
-- Autor: Cayo Rodrigues do Santos
-- Data: 13/11/2026
-- Objetivo: proteger o horário dos setores. Mesmo que alguém grave
-- direto pelo Workbench (sem passar pelo painel), o banco não aceita um
-- horário que quebre o "Aberto agora" do site.
--
-- É uma regra só, em dois gatilhos, porque o MySQL/MariaDB pede um trigger
-- para cada evento (um para INSERT e outro para UPDATE).
--
-- Regras de negócio:
--   R1. O dia da semana vai de 0 (domingo) a 6 (sábado).
--   R2. A hora de fechar precisa ser depois da hora de abrir
--       (o campus não tem atendimento que vira a meia-noite).
--   R3. As faixas de um mesmo setor no mesmo dia não podem se sobrepor.
--       Ex.: 08:00–12:00 e 11:00–14:00 no mesmo dia é recusado.
--       Faixas que só se encostam (08:00–12:00 e 12:00–18:00) são aceitas.
-- =============================================================

DROP TRIGGER IF EXISTS trg_horario_valida_inserir;
DROP TRIGGER IF EXISTS trg_horario_valida_alterar;

DELIMITER $$

CREATE TRIGGER trg_horario_valida_inserir
BEFORE INSERT ON setor_horario
FOR EACH ROW
BEGIN
    DECLARE v_mensagem VARCHAR(255);
    DECLARE v_dia VARCHAR(20);

    -- R1
    IF NEW.hora_dia_semana NOT BETWEEN 0 AND 6 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Dia da semana inválido: use 0 (domingo) a 6 (sábado).';
    END IF;

    SET v_dia = ELT(NEW.hora_dia_semana + 1, 'domingo', 'segunda-feira', 'terça-feira',
                    'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado');

    -- R2
    IF NEW.hora_fecha <= NEW.hora_abre THEN
        SET v_mensagem = CONCAT('Horário de ', v_dia, ': a hora de fechar (', TIME_FORMAT(NEW.hora_fecha, '%H:%i'),
                                ') precisa ser depois da de abrir (', TIME_FORMAT(NEW.hora_abre, '%H:%i'), ').');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_mensagem;
    END IF;

    -- R3
    IF EXISTS (SELECT 1 FROM setor_horario
                WHERE seto_id = NEW.seto_id
                  AND hora_dia_semana = NEW.hora_dia_semana
                  AND NEW.hora_abre < hora_fecha
                  AND NEW.hora_fecha > hora_abre) THEN
        SET v_mensagem = CONCAT('Horário de ', v_dia, ': a faixa ', TIME_FORMAT(NEW.hora_abre, '%H:%i'), ' às ',
                                TIME_FORMAT(NEW.hora_fecha, '%H:%i'), ' se sobrepõe a outra já cadastrada nesse dia.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_mensagem;
    END IF;
END$$

CREATE TRIGGER trg_horario_valida_alterar
BEFORE UPDATE ON setor_horario
FOR EACH ROW
BEGIN
    DECLARE v_mensagem VARCHAR(255);
    DECLARE v_dia VARCHAR(20);

    -- R1
    IF NEW.hora_dia_semana NOT BETWEEN 0 AND 6 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Dia da semana inválido: use 0 (domingo) a 6 (sábado).';
    END IF;

    SET v_dia = ELT(NEW.hora_dia_semana + 1, 'domingo', 'segunda-feira', 'terça-feira',
                    'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado');

    -- R2
    IF NEW.hora_fecha <= NEW.hora_abre THEN
        SET v_mensagem = CONCAT('Horário de ', v_dia, ': a hora de fechar (', TIME_FORMAT(NEW.hora_fecha, '%H:%i'),
                                ') precisa ser depois da de abrir (', TIME_FORMAT(NEW.hora_abre, '%H:%i'), ').');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_mensagem;
    END IF;

    -- R3 (ignora a própria linha que está sendo alterada)
    IF EXISTS (SELECT 1 FROM setor_horario
                WHERE seto_id = NEW.seto_id
                  AND hora_dia_semana = NEW.hora_dia_semana
                  AND hora_id <> OLD.hora_id
                  AND NEW.hora_abre < hora_fecha
                  AND NEW.hora_fecha > hora_abre) THEN
        SET v_mensagem = CONCAT('Horário de ', v_dia, ': a faixa ', TIME_FORMAT(NEW.hora_abre, '%H:%i'), ' às ',
                                TIME_FORMAT(NEW.hora_fecha, '%H:%i'), ' se sobrepõe a outra já cadastrada nesse dia.');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_mensagem;
    END IF;
END$$

DELIMITER ;


-- =============================================================
-- TESTES RÁPIDOS (para mostrar na apresentação)
-- Tire o "-- " da frente para rodar.
-- =============================================================

-- VIEW: como está cada setor agora
-- SELECT setor, onde_fica, horario_de_hoje, situacao, aviso_do_dia FROM vw_situacao_setores_hoje ORDER BY setor;
-- SELECT setor, horario_de_hoje FROM vw_situacao_setores_hoje WHERE situacao LIKE 'Aberto%';

-- PROCEDURE: feriado no campus inteiro (usuário 1 = administrador geral)
-- CALL sp_registrar_excecao_horario(1, NULL, CURDATE() + INTERVAL 7 DAY, 1, NULL, NULL, 'Feriado municipal');
-- PROCEDURE: deve dar erro — data no passado
-- CALL sp_registrar_excecao_horario(1, 'cra', '2020-01-01', 1, NULL, NULL, 'Teste');

-- TRIGGER: deve dar erro — 11:00 às 14:00 se sobrepõe ao 08:00 às 12:00 do CRA na segunda
-- INSERT INTO setor_horario (seto_id, hora_dia_semana, hora_abre, hora_fecha) VALUES ('cra', 1, '11:00', '14:00');
-- TRIGGER: deve dar erro — fecha antes de abrir
-- INSERT INTO setor_horario (seto_id, hora_dia_semana, hora_abre, hora_fecha) VALUES ('cra', 3, '18:00', '08:00');
