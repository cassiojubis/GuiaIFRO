-- Tabelas dos recursos que saíram do Guia IFRO (calendário, estatísticas, quadro de horários,
-- perguntas ao setor, informar erro e primeiro dia). Rode este arquivo se trouxer algum deles de volta.

-- Calendário acadêmico

CREATE TABLE IF NOT EXISTS eventos (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo          VARCHAR(200) NOT NULL,
    descricao       TEXT         NULL,
    inicio          DATE         NOT NULL,
    fim             DATE         NULL,
    tipo            ENUM('evento','feriado','recesso','letivo','prova') NOT NULL DEFAULT 'evento',
    periodo_letivo  VARCHAR(20)  NULL,
    local_sala_id   VARCHAR(60)  NULL,
    publicado       TINYINT(1)   NOT NULL DEFAULT 1,
    ordem           INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_eventos_data (publicado, inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Tabelas que o código já usava mas não existiam (correção BUG-01, BUG-02,
-- BUG-04 a BUG-07 do relatório de testes de 03/11/2026).
-- =====================================================================

-- Estatísticas anônimas (N9): só um contador por dia, sem IP nem cookie.
-- tipo: pagina, setor, rota, acessibilidade, idioma
CREATE TABLE IF NOT EXISTS estatisticas_dia (
    dia    DATE         NOT NULL,
    tipo   VARCHAR(20)  NOT NULL,
    chave  VARCHAR(120) NOT NULL,
    total  INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (dia, tipo, chave),
    KEY idx_estatisticas_tipo (tipo, dia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Termos buscados (sem saber quem buscou), separando os que não acharam nada.
CREATE TABLE IF NOT EXISTS buscas_log (
    dia             DATE         NOT NULL,
    termo           VARCHAR(100) NOT NULL,
    sem_resultado   TINYINT(1)   NOT NULL DEFAULT 0,
    total           INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (dia, termo, sem_resultado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quadro de horários (N13): cada CSV enviado vira uma versão; só uma fica publicada.
CREATE TABLE IF NOT EXISTS quadro_versoes (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome_arquivo  VARCHAR(255) NOT NULL,
    descricao     VARCHAR(200) NULL,
    enviado_por   VARCHAR(150) NULL,
    enviado_em    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_aulas   INT UNSIGNED NOT NULL DEFAULT 0,
    publicada     TINYINT(1)   NOT NULL DEFAULT 0,
    publicada_em  DATETIME     NULL,
    PRIMARY KEY (id),
    KEY idx_quadro_publicada (publicada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quadro_aulas (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    versao_id   INT UNSIGNED NOT NULL,
    turma       VARCHAR(60)  NOT NULL,
    dia_semana  TINYINT      NOT NULL COMMENT '0 = domingo ... 6 = sábado',
    inicio      TIME         NOT NULL,
    fim         TIME         NOT NULL,
    disciplina  VARCHAR(150) NOT NULL,
    professor   VARCHAR(150) NULL,
    sala_texto  VARCHAR(100) NULL,
    sala_id     VARCHAR(60)  NULL,
    PRIMARY KEY (id),
    KEY idx_quadro_aulas_busca (versao_id, turma, dia_semana, inicio),
    CONSTRAINT fk_quadro_aulas_versao FOREIGN KEY (versao_id) REFERENCES quadro_versoes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- "Pergunte ao setor": perguntas dos visitantes que viram FAQ quando respondidas.
CREATE TABLE IF NOT EXISTS perguntas_setor (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    setor_id    VARCHAR(60)  NOT NULL,
    pergunta    VARCHAR(500) NOT NULL,
    email       VARCHAR(150) NULL,
    status      ENUM('pendente','respondida','descartada') NOT NULL DEFAULT 'pendente',
    vezes       INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'mesma pergunta repetida soma aqui',
    criada_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultima_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    faq_id      INT UNSIGNED NULL,
    tratada_por VARCHAR(150) NULL,
    tratada_em  DATETIME     NULL,
    PRIMARY KEY (id),
    KEY idx_perguntas_status (status, setor_id),
    CONSTRAINT fk_perguntas_setor FOREIGN KEY (setor_id) REFERENCES setores (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- "Informar erro" na página do setor.
CREATE TABLE IF NOT EXISTS reportes_erro (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    setor_id   VARCHAR(60)  NOT NULL,
    campo      VARCHAR(30)  NOT NULL,
    correto    TEXT         NOT NULL,
    email      VARCHAR(150) NULL,
    criado_em  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolvido  TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_reportes_setor (setor_id, resolvido),
    CONSTRAINT fk_reportes_setor FOREIGN KEY (setor_id) REFERENCES setores (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roteiro "Primeiro dia" (N12): paradas com um QR "escondido" (selo do passaporte).
CREATE TABLE IF NOT EXISTS paradas_primeiro_dia (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo       VARCHAR(150) NOT NULL,
    setor_id     VARCHAR(60)  NULL,
    o_que_fazer  VARCHAR(500) NOT NULL,
    curiosidade  VARCHAR(500) NULL,
    codigo_selo  CHAR(16)     NOT NULL,
    publicado    TINYINT(1)   NOT NULL DEFAULT 1,
    ordem        INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_paradas_selo (codigo_selo),
    CONSTRAINT fk_paradas_setor FOREIGN KEY (setor_id) REFERENCES setores (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
