-- =============================================================
-- GUIA IFRO — REGRAS DE NEGÓCIO NO BANCO
-- Autor: Cayo Rodrigues do Santos
-- Data: 13/11/2026
-- Objetivo: levar para dentro do banco as regras mais importantes do
-- horário dos setores, para que elas valham mesmo fora do site.
--
-- Uma VIEW, uma PROCEDURE e um TRIGGER (em duas partes: inserir e alterar).
--
-- Como rodar: abra no MySQL Workbench (ou no phpMyAdmin, aba SQL) com o
-- banco do Guia IFRO selecionado e execute o arquivo inteiro.
-- Rodar este arquivo DEPOIS do guiaifro.sql (as tabelas precisam existir).
-- Pode rodar de novo sem medo: cada objeto é apagado e criado outra vez.
--
-- Por que fora do guiaifro.sql: o instalador (bin/instalar-banco.php) separa
-- os comandos por ";" e não entende o DELIMITER que procedure e trigger usam.
-- =============================================================

SET NAMES utf8mb4;


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
WHERE s.seto_ativo = 1;                                         -- R1


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
