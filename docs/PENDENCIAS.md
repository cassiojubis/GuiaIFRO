# Pendências que dependem do campus

Tudo aqui precisa de confirmação de alguém do campus (setor responsável, direção, TI).
Nada disso foi inventado no código: onde falta dado, o site mostra "Não informado" ou "Informações em breve".

Quando confirmar um item, corrija no painel admin (ou no banco) e apague a linha daqui.

## C10 — Textos e localizações para conferir no prédio

| Setor | O que estava | O que ficou | Conferir |
|---|---|---|---|
| Biblioteca | "Ao lado da CAED" | "Térreo, no fim da ala direita (depois da CAED)" | No mapa ela fica na ponta da ala direita e entre ela e a CAED há uma "Sala de Atendimento". Está certo? |
| NAPNE | "Próximo ao auditório" | "Térreo, ala esquerda (mesma ala do Auditório)" | No mapa, entre o NAPNE e o Auditório ficam a Copa e o Espaço Coletivo de Ideias. Fica mesmo "próximo"? |
| CRA | "Primeiro prédio da escola" | "Térreo do prédio principal" | "Primeiro prédio" e "prédio principal" são o mesmo prédio? |
| DAPE | "Segundo andar do primeiro prédio" | "1º Andar do prédio principal, ala esquerda" | "Segundo andar" = 1º Andar (contando o térreo como primeiro)? O mapa já mostra o DAPE no 1º Andar. |
| Biotecnologia | "Segundo andar do primeiro prédio, próximo ao DAPE" | "1º Andar do prédio principal, próximo ao DAPE" | Qual sala do mapa é a coordenação de Biotecnologia? (Hoje ela não aparece no mapa; talvez seja a "Coordenação de Cursos Técnicos".) |
| Mapa (todas as salas) | — | — | O lado de cada sala (frente/fundos) está alternando igual ao protótipo. Conferir sala por sala (comentário em `sql/dados-iniciais.php`). |

## C10 — Outras inconsistências encontradas

- **E-mail da CAED** está como `caed.guajara@ifro.com.br` (com **.com.br**). Todos os outros são `@ifro.edu.br`. Provavelmente é erro de digitação, mas não troquei sem confirmar.
- **Horário de atendimento geral** aparece de três jeitos:
  - página Contato: "Segunda a sexta, 7h às 17h";
  - admin › Contatos: "Segunda a sexta, das 7h às 13h e das 14h às 17h";
  - FAQ: "segunda a sexta, das 7h às 13h e das 14h às 17h".
- **FAQ do CRA** diz que o atendimento é "7h às 13h e 14h às 17h", mas o horário do CRA cadastrado é "Segunda: 08:00 às 12:00 e 14:00 às 18:00 | Terça: 13:00 às 18:00 e 19:00 às 22:00". Qual vale?
- **Horários sem dia da semana** (Biblioteca, NAPNE, DAPE, Biotecnologia) são tratados como "todo dia", inclusive sábado e domingo, no selo "Aberto agora". Falta saber os dias de cada um.
- **Enfermaria**: horário "Durante os turnos letivos" não tem hora, então o selo "Aberto agora" não aparece.
- **Equipe do CRA** está como "Não possui". É isso mesmo?

## C8 — Números da home

- Quantidade de **cursos** e de **alunos**: precisa de número oficial (DAPE/CRA). Hoje a home mostra só o que dá pra contar nos dados (salas por andar, setores com página, itens do organograma).

## C1 — Senha do admin

- O primeiro admin é criado com a senha de `config/segredos.php` (`admin123`). Quem entra com ela (ou com qualquer senha fraca) é levado direto para admin › Meus dados e o painel só libera depois da troca.

## B5 — Setores que ainda não têm nenhuma informação (29)

Todos já têm página no site, mostrando "Informações em breve" com o botão de contato. Para cada um falta: telefone, e-mail, horário, localização, responsável e serviços. Dá pra preencher em admin › Setores (ou ligar um responsável em admin › Usuários pra ele mesmo preencher). A lista atualizada fica sempre no Dashboard do admin.

| Sigla | Setor | Categoria |
|---|---|---|
| DG | Direção-Geral | Órgãos de direção e colegiados |
| — | Colegiado de Curso | Órgãos de direção e colegiados |
| — | Conselho de Classe | Órgãos de direção e colegiados |
| — | Conselho Escolar | Órgãos de direção e colegiados |
| CCOM | Comunicação e Eventos | Coordenações ligadas à Direção-Geral |
| CACI | Avaliação e Controle Interno | Coordenações ligadas à Direção-Geral |
| CGP | Gestão de Pessoas | Coordenações ligadas à Direção-Geral |
| CGTI | Gestão da Tecnologia da Informação | Coordenações ligadas à Direção-Geral |
| CGAB | Chefia de Gabinete | Coordenações ligadas à Direção-Geral |
| — | Protocolo e Arquivo | Coordenações ligadas à Direção-Geral |
| CEaD | Coordenação de Educação a Distância | Diretoria de Ensino (DE) |
| — | Coordenações de Cursos | Diretoria de Ensino (DE) |
| DEPESP | Departamento de Pesquisa, Inovação e Pós-Graduação | Pesquisa e Pós-Graduação |
| CPI | Coordenação de Pesquisa e Inovação | Pesquisa e Pós-Graduação |
| CPOSG | Coordenação de Pós-Graduação | Pesquisa e Pós-Graduação |
| NIT | Núcleo de Inovação Tecnológica | Pesquisa e Pós-Graduação |
| DEPEX | Departamento de Extensão | Extensão |
| CFIC | Coordenação de Formação Inicial e Continuada | Extensão |
| DPLAD | Diretoria de Planejamento e Administração | Planejamento e Administração |
| COFIN | Coordenação de Orçamento e Finanças | Planejamento e Administração |
| — | Contadoria | Planejamento e Administração |
| CSG | Coordenação de Serviços Gerais | Planejamento e Administração |
| CPALM | Patrimônio e Almoxarifado | Planejamento e Administração |
| CCL | Compras e Licitações | Planejamento e Administração |
| CCONV | Gestão de Contratos e Convênios | Planejamento e Administração |
| CIEEC | Coordenação de Integração Escola, Empresa e Comunidade | Setores de apoio aos estudantes |
| — | Laboratórios | Setores de apoio aos estudantes |
| NUPEM | Núcleo Pedagógico Multidisciplinar | Setores de apoio aos estudantes |
| NDE | Núcleo Docente Estruturante | Outros núcleos |

## B4 — Horários que não deu pra converter para "por dia"

O texto não diz os dias da semana, então não dá pra saber se abre no sábado. O site continua usando o texto, mas tratando como "todo dia". Cadastre os dias em admin › Setores › Horários:

- Biblioteca: "07:00 às 13:00 / 13:00 às 18:00 / 18:00 às 21:00"
- Biotecnologia: "08:00 às 17:00"
- DAPE: "07:00 às 19:00 / 19:00 às 22:50"
- NAPNE: "07:00 às 23:00"
- Enfermaria: "Durante os turnos letivos" (sem hora nenhuma)

## B5 — Salas do mapa sem setor

Banheiros, Copa, Espaço Coletivo de Ideias, Auditório, Salas de Atendimento, Sala de Manutenção TI, Sala de Reuniões, Diretoria de Ensino, Coordenações de Cursos Superiores e Técnicos, Salas Docentes. Elas têm uma ficha simples (`sala.php`). Falta a **função** de cada uma (o que acontece ali) e saber se "Diretoria de Ensino" e as "Coordenações de Cursos" do mapa são os setores de mesmo nome do organograma (aí dá pra ligar a sala ao setor no Editor do mapa).

## Conteúdo que veio do protótipo

- **Notícias** da página inicial (4) e **avisos**/**calendário** do admin vieram do protótipo. As notícias continuam publicadas; avisos e datas do calendário foram importados como *ocultos*. Confirme se são reais ou apague em admin › Informações.

## N1 — Palavras de busca (sinônimos)

- Cadastrados: "declaração" e "histórico" → CRA; "auxílio" → CAED (do backlog).
- **"carteirinha"**: qual setor faz a carteirinha de estudante? Cadastre a palavra no setor certo (admin › Setores › Palavras de busca).

## N11 — "Como faço para..." que ainda faltam

Publicados só os 3 que o FAQ já explicava (declaração de matrícula, matrícula, trancamento), com o texto do FAQ.
Faltam, com confirmação do setor (passos, setor responsável, prazo, documentos, SUAP ou presencial):

- Renovação de matrícula
- Auxílio estudantil
- Histórico escolar
- Segunda chamada de prova
- Estágio

Cadastre em admin › Como faço para…

## I3 — Acessibilidade física do prédio

Tudo está como **"não informado"**. Confira no prédio e preencha em admin › Editor do mapa › (editar sala) › Acessibilidade do local:

- Tem **elevador**? Se tiver, marque "Chega de elevador = Sim" nas salas do 1º andar (a rota e a busca "só sem escada" passam a considerar).
- Para cada sala: rampa/acesso sem degrau, banheiro adaptado perto, balcão baixo, piso tátil, porta larga.
- Enquanto não houver elevador cadastrado, todo destino no 1º andar avisa "acesso pela escada" e sugere atendimento no térreo ou o NAPNE.

## I4 — Leitura Fácil

- Nenhum setor tem ainda o texto em **leitura fácil** (frases curtas). Escreva em admin › Setores › (editar) › "Leitura fácil" — o ideal é com o NAPNE. O filtro "Sem texto em leitura fácil" mostra quais faltam.

## I6 — Traduções

- **Espanhol**: os textos fixos das páginas ficam no dicionário de `js/traducao-es.js` (veio do Guia antigo); os 10 textos do menu/rodapé vieram do protótipo e estão publicados em admin › Traduções como "Protótipo (revisar)". Peça para alguém fluente conferir.
- **Oro Wari**: tradução em andamento com falantes do IFRO. Cada texto entra em admin › Traduções como rascunho, passa a revisado e só é publicado com a confirmação de que um falante revisou. Áudios gravados por eles vão no campo "Áudio gravado" (arquivos na pasta `audio/`). Enquanto nada estiver publicado, a opção mostra o aviso explicando isso.

## I7 — Para quem vem de outro país

- A página `estrangeiros.php` está pronta em português e espanhol, mas **a lista de documentos e o "como chegar" estão vazios**: preencha em admin › Configurações quando o CRA informar o que o campus exige. Nenhuma regra migratória foi escrita.
