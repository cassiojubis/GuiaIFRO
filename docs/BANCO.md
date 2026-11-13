# Banco de dados do Guia IFRO

MariaDB/MySQL, `utf8mb4`. Criado por `sql/guiaifro.sql` e preenchido por `sql/seed.php`
(o jeito fácil é rodar `bin/instalar-banco.php`, ver README).

## Padrão de nomes (pedido do professor)

- Tabela no **singular**: `setor`, `categoria`, `usuario`, `link_util`...
- Coluna com o **prefixo da tabela** (4 letras): `seto_nome`, `cate_ordem`, `usua_email`.
  Exceções: `faq_` (a tabela já se chama faq), `acess_` (acessibilidade_sala),
  `fpasso_` (fluxo_passo) e `fdocu_` (fluxo_documento), pra não repetir o `flux_` do fluxo.
- **Chave estrangeira** com o mesmo nome da chave da tabela de origem:
  `setor.cate_id` -> `categoria.cate_id`, `sala.anda_id` -> `andar.anda_id`, `usuario.perf_id` -> `perfil.perf_id`.

No PHP, só o `model/Repositorio.php` (e o `sql/seed.php`) usam esses nomes. Cada SELECT devolve as
colunas com o nome curto (`seto_nome AS nome`), então controllers e views continuam usando
`$setor['nome']`, `$usuario['email']` etc. A lista completa está em `COLUNAS_BANCO`, no começo do Repositorio.

## Modelo principal: categoria -> setor -> sala

```
categoria (grupo do organograma)           andar
  cate_id (slug) PK                          anda_id (slug) PK  'terreo', 'andar1'
  cate_nome, cate_icone, cate_cor, cate_ordem anda_nome, anda_ordem
      │ 1                                        │ 1
      │ N                                        │ N
setor ──────────────────────────────1:N──────── sala
  seto_id (slug) PK  'cra', 'biblioteca'...      sala_id (slug) PK
  cate_id FK                                     anda_id FK, sala_ala, sala_ordem, sala_lado, sala_ponta
  seto_nome, seto_sigla, seto_icone, seto_cor    sala_nome, sala_nome_curto, sala_funcao
  seto_descricao, seto_descricao_curta           seto_id FK (NULL = sala sem setor: banheiro, copa...)
  seto_localizacao, seto_bloco, seto_sala_texto       │
  seto_telefone, seto_email, seto_responsavel         └── acessibilidade_sala (sala_id PK/FK: acess_rampa, acess_elevador...)
  seto_horario_texto (só observação)
  seto_ativo, seto_atualizado_em, seto_atualizado_por
      │
      ├── setor_equipe     (equi_id, seto_id, equi_nome, equi_cargo, equi_ordem)
      ├── setor_servico    (serv_id, seto_id, serv_nome, serv_ordem)
      ├── setor_documento  (docu_id, seto_id, docu_nome, docu_arquivo, docu_ordem)
      ├── setor_horario    (hora_id, seto_id, hora_dia_semana 0-6, hora_abre, hora_fecha, hora_observacao)
      ├── setor_excecao    (exce_id, seto_id NULL = campus todo, exce_data, exce_fechado, exce_abre, exce_fecha, exce_motivo)
      ├── sinonimo         (sino_id, seto_id, sino_nome)  palavras que a busca também aceita
      ├── faq_setor ── faq (N:N; FAQ sem setor = FAQ geral)
      └── usuario_setor ── usuario ── perfil

peca_mapa (cenário do editor antigo: peca_tipo, peca_nome, peca_x, peca_z, peca_largura, peca_profundidade, peca_rotacao, peca_inicio, peca_fim)
```

O desenho do mapa do campus (prédios, salas, calçadas) não fica no banco: fica em `data/mapa-campus.json`.

## Conteúdo

```
aviso                avis_titulo, avis_mensagem, avis_nivel (normal/urgente), avis_inicio, avis_fim, avis_publicado
noticia              noti_slug, noti_titulo, noti_resumo, noti_conteudo, noti_categoria, noti_publicada_em, noti_destaque, noti_publicado
faq                  faq_pergunta, faq_resposta, faq_publicado, faq_ordem
link_util            link_titulo, link_url, link_ordem, link_publicado
texto_institucional  text_titulo, text_conteudo, text_ordem, text_publicado
atalho_home          atal_titulo, atal_descricao, atal_icone, atal_cor, atal_url, atal_ordem
configuracao         conf_chave PK, conf_valor  (contato geral, redes sociais, link do WhatsApp...)
traducao             trad_entidade, trad_entidade_id, trad_campo, trad_idioma, trad_texto, trad_status, trad_validado_por...
fluxo                "Como faço para...": flux_slug, flux_titulo, seto_id, flux_canal, flux_prazo, flux_publicado
  ├── fluxo_passo      (fpasso_id, flux_id, fpasso_nome, fpasso_ordem)
  └── fluxo_documento  (fdocu_id, flux_id, fdocu_nome, fdocu_ordem)
evento               calendário acadêmico (o recurso está guardado em _futuro/; o site não usa a tabela por enquanto)
```

## Usuários, segurança e registros

```
perfil            perf_id (slug) 'admin_geral', 'responsavel_setor', perf_nome
usuario           usua_nome, usua_usuario, usua_email, usua_senha_hash, perf_id, usua_ativo, usua_sessao_versao, usua_ultimo_login_em
usuario_setor     usua_id, seto_id   (quais setores o responsável pode editar)
limite            limi_acao, limi_chave_hash, limi_criado_em   (limite de envios/tentativas; IP guardado só como hash)
auditoria         usua_id, audi_usuario_nome, audi_acao, audi_tabela, audi_registro_id, audi_antes, audi_depois, audi_quando
contato_mensagem  cont_nome, cont_email, cont_telefone, cont_assunto, cont_mensagem, cont_criada_em, cont_lida_em
```

## Regras

- O site só fala com o banco por `model/Repositorio.php` (PDO + prepared statements).
- O usuário do banco usado pelo site (`config/banco.php`) só tem SELECT/INSERT/UPDATE/DELETE
  no banco do Guia. Nunca usar root no site.
- Toda alteração feita pelo admin vai para `auditoria` (antes/depois em JSON).

## View, procedure e triggers (regras de negócio no banco)

Ficam no fim do `sql/guiaifro.sql` (autor: Cayo Rodrigues do Santos) e são criados pelo
`bin/instalar-banco.php`, que entende o `DELIMITER $$` usado na procedure e nos triggers.

- `vw_situacao_setores_hoje` — cada setor ativo com onde fica, horário de hoje e situação
  ("Aberto agora", "Fechado hoje", "Horário não informado"...), já contando feriados e horários especiais.
- `sp_registrar_excecao_horario(usuario, setor, data, fechado, abre, fecha, motivo)` — cadastra feriado,
  recesso ou horário especial validando permissão, data e duplicidade, e grava na `auditoria` (tudo ou nada).
- `trg_horario_valida_inserir` / `trg_horario_valida_alterar` — o banco recusa horário com dia inválido,
  com hora de fechar antes da de abrir ou que se sobrepõe a outra faixa do mesmo setor no mesmo dia.
