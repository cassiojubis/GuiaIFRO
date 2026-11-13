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