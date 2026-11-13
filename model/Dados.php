<?php
// dados simulados (viriam do banco depois)

$atalhosRapidos = [
    ['icone' => 'book-open', 'titulo' => 'Biblioteca', 'descricao' => 'Acervo e estudos', 'url' => 'setor.php?id=biblioteca', 'cor' => 'blue'],
    ['icone' => 'heart-handshake', 'titulo' => 'CAED', 'descricao' => 'Apoio ao ensino e discente', 'url' => 'setor.php?id=caed', 'cor' => 'rose'],
    ['icone' => 'accessibility', 'titulo' => 'NAPNE', 'descricao' => 'Acessibilidade e inclusão', 'url' => 'setor.php?id=napne', 'cor' => 'green'],
    ['icone' => 'heart-pulse', 'titulo' => 'Enfermaria', 'descricao' => 'Primeiros socorros', 'url' => 'setor.php?id=enfermaria', 'cor' => 'rose'],
    ['icone' => 'users', 'titulo' => 'CRA', 'descricao' => 'Registro acadêmico', 'url' => 'setor.php?id=cra', 'cor' => 'purple'],
    ['icone' => 'graduation-cap', 'titulo' => 'DAPE', 'descricao' => 'Apoio pedagógico', 'url' => 'setor.php?id=dape', 'cor' => 'green'],
];

$setoresCampus = [
    ['id' => 'biblioteca', 'icone' => 'book-open', 'nome' => 'Biblioteca', 'sigla' => 'BIB', 'categoria' => 'servicos', 'descricao' => 'Apoio às atividades de ensino, pesquisa e extensão.', 'localizacao' => 'Ao lado da CAED', 'bloco' => 'Ao lado da CAED', 'sala' => 'Não informado', 'telefone' => 'Não possui', 'email' => 'biblioteca.guajara@ifro.edu.br', 'horario' => '07:00 às 13:00 | 13:00 às 18:00 | 18:00 às 21:00', 'responsavel' => 'Professora Conceição', 'video' => null, // caminho do vídeo do responsável (ex: 'video/setores/cra.mp4') — ainda não gravado
     'equipe' => ['Joel Javier', 'Maria Eusete', 'Raulane'], 'servicos' => ['Empréstimo', 'Consulta de acervo', 'Espaço de estudos'], 'documentos' => []],
    ['id' => 'caed', 'icone' => 'heart-handshake', 'nome' => 'CAED', 'sigla' => 'CAED', 'categoria' => 'estudante', 'descricao' => 'Suporte pedagógico, psicológico e social aos discentes.', 'localizacao' => 'Próximo à CRA e à Enfermaria', 'bloco' => 'Próximo à CRA e à Enfermaria', 'sala' => 'Não informado', 'telefone' => '(69) 99953-3545', 'email' => 'caed.guajara@ifro.edu.br', 'horario' => 'Segunda a quinta: 07:00 às 13:15 | Sexta: 07:00 às 19:00', 'responsavel' => 'Flávia Rodrigues de Araújo Durães', 'video' => null, // caminho do vídeo do responsável (ex: 'video/setores/cra.mp4') — ainda não gravado
     'equipe' => ['Fernanda Léia B. S. Estevão', 'Felipe Nunez', 'Jéssica Félix Bastos Freire', 'Altina Maria Perereira de Souza', 'Leonora Moreira Fonseca de Souza', 'Flávia Rodrigues Araújo Durães'], 'servicos' => ['Orientação educacional: dificuldades acadêmicas, baixo rendimento, aprendizagem, organização dos estudos e risco de reprovação', 'Acompanhamento da vida escolar: frequência, desempenho, recuperação e outras situações que possam prejudicar o aproveitamento', 'Orientação e acolhimento para dificuldades pessoais, acadêmicas, familiares ou outras situações que afetem os estudos', 'Apoio e acompanhamento biopsicossocial, com encaminhamento para serviços especializados quando necessário', 'Assistência estudantil: informações e orientações sobre programas, auxílios e benefícios', 'Convivência escolar: orientação e mediação de conflitos, dificuldades de relacionamento, bullying e desrespeito', 'Diversidade e interculturalidade: acolhimento e encaminhamento de demandas culturais, étnicas e sociais', 'Prevenção da evasão: acompanhamento de estudantes com faltas frequentes, dificuldades de permanência ou risco de abandono'], 'documentos' => []],
    ['id' => 'napne', 'icone' => 'accessibility', 'nome' => 'NAPNE', 'sigla' => 'NAPNE', 'categoria' => 'ensino', 'descricao' => 'Ações e diretrizes voltadas à acessibilidade e inclusão escolar.', 'localizacao' => 'Próximo ao auditório', 'bloco' => 'Próximo ao auditório', 'sala' => 'Não informado', 'telefone' => 'Não possui', 'email' => 'napne.guajara@ifro.edu.br', 'horario' => '07:00 às 23:00', 'responsavel' => 'Ronsangela Schmidt', 'video' => null, // caminho do vídeo do responsável (ex: 'video/setores/cra.mp4') — ainda não gravado
     'equipe' => ['Em conjunto com a CAED'], 'servicos' => ['Monitoria do processo de inclusão de pessoas com deficiência no âmbito do campus', 'Atendimento Educacional Especializado (AEE)'], 'documentos' => []],
    ['id' => 'enfermaria', 'icone' => 'heart-pulse', 'nome' => 'Enfermaria', 'sigla' => 'ENF', 'categoria' => 'estudante', 'descricao' => 'Unidade de suporte e primeiros socorros do campus.', 'localizacao' => 'Próximo à CRA', 'bloco' => 'Próximo à CRA', 'sala' => 'Não informado', 'telefone' => 'Não possui', 'email' => 'Não possui', 'horario' => 'Durante os turnos letivos', 'responsavel' => 'Flávia Rodrigues de Araújo Durães', 'video' => null, // caminho do vídeo do responsável (ex: 'video/setores/cra.mp4') — ainda não gravado
     'equipe' => ['Em conjunto com a CAED'], 'servicos' => ['Primeiros socorros', 'Acolhimento', 'Triagem inicial de saúde'], 'documentos' => []],
    ['id' => 'cra', 'icone' => 'users', 'nome' => 'Coordenação de Registros Acadêmicos', 'sigla' => 'CRA', 'categoria' => 'estudante', 'descricao' => 'Registro, arquivo e controle da vida acadêmica do estudante.', 'localizacao' => 'Primeiro prédio da escola, primeira sala à direita logo após a entrada', 'bloco' => 'Primeiro prédio da escola', 'sala' => 'Primeira sala à direita logo após a entrada', 'telefone' => 'Não possui', 'email' => 'cra.guajara@ifro.edu.br', 'horario' => 'Segunda: 08:00 às 12:00 e 14:00 às 18:00 | Terça: 13:00 às 18:00 e 19:00 às 22:00', 'responsavel' => 'Walnei de Lima Oliveira', 'video' => null, // caminho do vídeo do responsável (ex: 'video/setores/cra.mp4') — ainda não gravado
     'equipe' => ['Não possui'], 'servicos' => ['Matrícula', 'Transferência', 'Renovação de matrícula', 'Emissão de certificados e diplomas', 'Alimentação de sistemas do censo escolar e do governo federal', 'Registros das atas do conselho de classe', 'Atendimento ao público interno e externo'], 'documentos' => []],
    ['id' => 'dape', 'icone' => 'graduation-cap', 'nome' => 'Departamento de Apoio Pedagógico', 'sigla' => 'DAPE', 'categoria' => 'ensino', 'descricao' => 'Apoio pedagógico e estrutural às rotinas acadêmicas.', 'localizacao' => 'Segundo andar do primeiro prédio, próximo à coordenação de cursos', 'bloco' => 'Segundo andar do primeiro prédio', 'sala' => 'Próximo à coordenação de cursos', 'telefone' => 'Não possui', 'email' => 'dape.guajara@ifro.edu.br', 'horario' => '07:00 às 19:00 | 19:00 às 22:50', 'responsavel' => 'Venicio Favoretti', 'video' => null, // caminho do vídeo do responsável (ex: 'video/setores/cra.mp4') — ainda não gravado
     'equipe' => ['Eduardo', 'Vanilson Paes da Costa (estagiário)', 'Luana Paola Alves Sosa (estagiária)', 'Eduardo Quimas'], 'servicos' => ['Suporte pedagógico aos alunos e às coordenações', 'Organização do quadro de horários'], 'documentos' => []],
    ['id' => 'biotecnologia', 'icone' => 'flask-conical', 'nome' => 'Coordenação de Cursos (Biotecnologia Integrado ao Ensino Médio)', 'sigla' => 'CCTBIOTEC', 'categoria' => 'ensino', 'descricao' => 'Coordena as atividades acadêmicas e administrativas do curso técnico em Biotecnologia Integrado ao Ensino Médio.', 'localizacao' => 'Segundo andar do primeiro prédio, próximo ao DAPE', 'bloco' => 'Segundo andar do primeiro prédio', 'sala' => 'Próximo ao DAPE', 'telefone' => 'Não possui', 'email' => 'cctbiotec.guajara@ifro.edu.br', 'horario' => '08:00 às 17:00', 'responsavel' => 'Tássia Alana Alves Ferreira', 'video' => null, // caminho do vídeo do responsável (ex: 'video/setores/cra.mp4') — ainda não gravado
     'equipe' => ['Leonardo Almeida', 'Marcela Cristina', 'Marinês Vieira Matos'], 'servicos' => ['Coordenação das atividades acadêmicas e administrativas', 'Acompanhamento de docentes', 'Organização de colegiados e horários', 'Apoio às ações institucionais'], 'documentos' => []],
    ['id' => 'dplad', 'icone' => 'briefcase-business', 'nome' => 'Diretoria de Planejamento e Administração', 'sigla' => 'DPLAD', 'categoria' => 'Planejamento e Administração', 'descricao' => 'Cuida do planejamento e da administração do campus.', 'localizacao' => 'Não informado', 'bloco' => 'Não informado', 'sala' => 'Não informado', 'telefone' => 'Não informado', 'email' => 'dplad.guajara@ifro.edu.br', 'horario' => '08:00 às 12:00 | 14:00 às 18:00', 'responsavel' => 'Rodrigo Marques Machado', 'video' => null,
     'equipe' => ['Julia Benevenuto (Coordenação de Patrimônio)', 'Deoclides de Lima (Coordenação de Serviços Gerais)', 'Ismaelson Moura (Coordenação de Compras e Licitações)', 'Ana Paula (Coordenação de Orçamento e Finanças)', 'Maria do Carmo (Coordenação de Contratos e Convênios)'], 'servicos' => ['Gestão patrimonial', 'Administração de frota e serviços gerais', 'Controle financeiro e coordenação orçamentária', 'Gestão de contratos', 'Operação de licitações'], 'documentos' => []],
];

// Organograma completo do campus, agrupado por diretoria/coordenação.
$gruposCategorias = [
    'Órgãos de direção e colegiados' => [
        ['id' => 'direcao-geral', 'icone' => 'building-2', 'titulo' => 'Direção-Geral (DG)', 'descricao' => 'responsável pela direção geral e pela administração do campus.', 'cor' => 'slate'],
        ['id' => 'colegiado-curso', 'icone' => 'users', 'titulo' => 'Colegiado de Curso', 'descricao' => 'grupo que trata de assuntos relacionados ao curso.', 'cor' => 'green'],
        ['id' => 'conselho-classe', 'icone' => 'school', 'titulo' => 'Conselho de Classe', 'descricao' => 'discute o desempenho e a situação dos estudantes.', 'cor' => 'amber'],
        ['id' => 'conselho-escolar', 'icone' => 'briefcase-business', 'titulo' => 'Conselho Escolar', 'descricao' => 'participa de decisões importantes relacionadas ao campus.', 'cor' => 'purple'],
    ],
    'Coordenações ligadas à Direção-Geral' => [
        ['id' => 'ccom', 'icone' => 'megaphone', 'titulo' => 'CCOM – Comunicação e Eventos', 'descricao' => 'cuida da comunicação e dos eventos do campus.', 'cor' => 'rose'],
        ['id' => 'caci', 'icone' => 'clipboard-check', 'titulo' => 'CACI – Avaliação e Controle Interno', 'descricao' => 'trabalha com avaliação e controle interno.', 'cor' => 'blue'],
        ['id' => 'cgp', 'icone' => 'users-round', 'titulo' => 'CGP – Gestão de Pessoas', 'descricao' => 'cuida de assuntos relacionados aos servidores.', 'cor' => 'green'],
        ['id' => 'cgti', 'icone' => 'laptop', 'titulo' => 'CGTI – Gestão da Tecnologia da Informação', 'descricao' => 'cuida da informática, sistemas e tecnologia.', 'cor' => 'teal'],
        ['id' => 'cgab', 'icone' => 'briefcase', 'titulo' => 'CGAB – Chefia de Gabinete', 'descricao' => 'auxilia diretamente a Direção-Geral.', 'cor' => 'slate'],
        ['id' => 'protocolo-arquivo', 'icone' => 'folder-open', 'titulo' => 'Protocolo e Arquivo', 'descricao' => 'organiza documentos, protocolos e arquivos.', 'cor' => 'amber'],
    ],
    'Diretoria de Ensino (DE)' => [
        ['id' => 'dape-ensino', 'icone' => 'graduation-cap', 'titulo' => 'DAPE – Departamento de Apoio ao Ensino', 'descricao' => 'dá suporte às atividades de ensino.', 'cor' => 'green'],
        ['id' => 'cead', 'icone' => 'monitor', 'titulo' => 'CEaD – Coordenação de Educação a Distância', 'descricao' => 'trata das atividades de educação a distância.', 'cor' => 'blue'],
        ['id' => 'coordenacoes-cursos', 'icone' => 'book-open', 'titulo' => 'Coordenações de Cursos', 'descricao' => 'acompanham e organizam os cursos.', 'cor' => 'green'],
        ['id' => 'cra-academico', 'icone' => 'file-text', 'titulo' => 'CRA – Coordenação de Registros Acadêmicos', 'descricao' => 'cuida dos registros acadêmicos dos estudantes, como documentação e informações escolares.', 'cor' => 'purple'],
    ],
    'Pesquisa e Pós-Graduação' => [
        ['id' => 'depesp', 'icone' => 'flask-conical', 'titulo' => 'DEPESP – Departamento de Pesquisa, Inovação e Pós-Graduação', 'descricao' => 'coordena atividades de pesquisa, inovação e pós-graduação.', 'cor' => 'blue'],
        ['id' => 'cpi', 'icone' => 'search', 'titulo' => 'CPI – Coordenação de Pesquisa e Inovação', 'descricao' => 'acompanha projetos de pesquisa e inovação.', 'cor' => 'teal'],
        ['id' => 'cposg', 'icone' => 'graduation-cap', 'titulo' => 'CPOSG – Coordenação de Pós-Graduação', 'descricao' => 'trabalha com atividades de pós-graduação.', 'cor' => 'green'],
        ['id' => 'nit', 'icone' => 'lightbulb', 'titulo' => 'NIT – Núcleo de Inovação Tecnológica', 'descricao' => 'apoia ações relacionadas à inovação e tecnologia.', 'cor' => 'amber'],
    ],
    'Extensão' => [
        ['id' => 'depex', 'icone' => 'handshake', 'titulo' => 'DEPEX – Departamento de Extensão', 'descricao' => 'coordena atividades de extensão do campus.', 'cor' => 'rose'],
        ['id' => 'cfic', 'icone' => 'badge-check', 'titulo' => 'CFIC – Coordenação de Formação Inicial e Continuada', 'descricao' => 'trabalha com cursos e ações de formação inicial e continuada.', 'cor' => 'green'],
    ],
    'Planejamento e Administração' => [
        ['id' => 'dplad', 'icone' => 'briefcase-business', 'titulo' => 'DPLAD – Diretoria de Planejamento e Administração', 'descricao' => 'cuida do planejamento e da administração.', 'cor' => 'slate'],
        ['id' => 'cofin', 'icone' => 'wallet', 'titulo' => 'COFIN – Coordenação de Orçamento e Finanças', 'descricao' => 'trabalha com orçamento e recursos financeiros.', 'cor' => 'amber'],
        ['id' => 'contadoria', 'icone' => 'file-check-2', 'titulo' => 'Contadoria', 'descricao' => 'trabalha com registros e controles contábeis.', 'cor' => 'blue'],
        ['id' => 'csg', 'icone' => 'hammer', 'titulo' => 'CSG – Coordenação de Serviços Gerais', 'descricao' => 'cuida dos serviços gerais do campus.', 'cor' => 'green'],
        ['id' => 'cpalm', 'icone' => 'warehouse', 'titulo' => 'CPALM – Patrimônio e Almoxarifado', 'descricao' => 'cuida dos materiais, estoque e patrimônio.', 'cor' => 'purple'],
        ['id' => 'ccl', 'icone' => 'shopping-cart', 'titulo' => 'CCL – Compras e Licitações', 'descricao' => 'trabalha com compras e processos de licitação.', 'cor' => 'rose'],
        ['id' => 'cconv', 'icone' => 'contract', 'titulo' => 'CCONV – Gestão de Contratos e Convênios', 'descricao' => 'acompanha contratos e convênios.', 'cor' => 'teal'],
    ],
    'Setores de apoio aos estudantes' => [
        ['id' => 'caed', 'icone' => 'heart-handshake', 'titulo' => 'CAED – Coordenação de Assistência ao Educando', 'descricao' => 'trabalha com assistência e apoio aos estudantes.', 'cor' => 'rose'],
        ['id' => 'cbib', 'icone' => 'book-open', 'titulo' => 'CBIB – Coordenação de Biblioteca', 'descricao' => 'administra os serviços da biblioteca.', 'cor' => 'blue'],
        ['id' => 'cieec', 'icone' => 'building-2', 'titulo' => 'CIEEC – Coordenação de Integração Escola, Empresa e Comunidade', 'descricao' => 'promove a integração entre o campus, empresas e comunidade.', 'cor' => 'green'],
        ['id' => 'laboratorios', 'icone' => 'flask-round', 'titulo' => 'Laboratórios', 'descricao' => 'dão suporte às atividades práticas.', 'cor' => 'amber'],
        ['id' => 'nupem', 'icone' => 'sparkles', 'titulo' => 'NUPEM – Núcleo Pedagógico Multidisciplinar', 'descricao' => 'oferece apoio relacionado às questões pedagógicas.', 'cor' => 'purple'],
        ['id' => 'napne', 'icone' => 'accessibility', 'titulo' => 'NAPNE – Núcleo de Atendimento às Pessoas com Necessidades Educacionais Específicas', 'descricao' => 'trabalha com apoio e inclusão de estudantes que necessitam de atendimento educacional específico.', 'cor' => 'slate'],
    ],
    'Outros núcleos' => [
        ['id' => 'nde', 'icone' => 'users', 'titulo' => 'NDE – Núcleo Docente Estruturante', 'descricao' => 'grupo ligado ao planejamento e acompanhamento do curso.', 'cor' => 'green'],
    ],
];

$categorias = [];
foreach ($gruposCategorias as $grupoNome => $itens) {
    foreach ($itens as $categoria) {
        $categorias[] = [
            'id' => $categoria['id'],
            'icone' => $categoria['icone'],
            'titulo' => $categoria['titulo'],
            'descricaoCurta' => $categoria['descricao'],
            'descricao' => $categoria['descricao'],
            'quantidade' => 1,
            'tag' => $grupoNome,
            'cor' => $categoria['cor'],
            'setores' => [$categoria['titulo']],
        ];
    }
}

$noticias = [
    ['titulo' => 'Inscrições abertas para auxílio estudantil 2026', 'data' => '24 mai 2026', 'categoria' => 'Assistência', 'urgente' => true],
    ['titulo' => 'Novo laboratório de informática inaugurado', 'data' => '22 mai 2026', 'categoria' => 'Infraestrutura', 'urgente' => false],
    ['titulo' => 'Calendário acadêmico 2026/2 disponível no SUAP', 'data' => '20 mai 2026', 'categoria' => 'Acadêmico', 'urgente' => false],
    ['titulo' => 'Processo seletivo para monitoria — edital publicado', 'data' => '18 mai 2026', 'categoria' => 'Ensino', 'urgente' => false],
];

$setoresPorId = [];
foreach ($setoresCampus as $setor) {
    $setoresPorId[$setor['id']] = $setor;
}
// SALAS DO PRÉDIO PRINCIPAL (mapa.php), usadas enquanto o mapa não for salvo pelo
// "Editor do mapa" do admin (depois disso o mapa vem de data/mapa-campus.json).
// A ordem de cada ala é a de quem entra no prédio e segue para o fundo.
// Aqui não tem nenhuma coordenada: o js/mapa-campus.js calcula sozinho o tamanho e a
// posição de cada sala a partir desta lista. Campos de cada sala:
//   id        -> identificador único (também usado em mapa.php?sala=<id>)
//   nome      -> nome completo, mostrado na lista e no cartão
//   nomeCurto -> etiqueta que aparece em cima da sala no desenho
//   andar     -> 'terreo' ou 'andar1'
//   ala       -> 'esquerda' ou 'direita' (vista por quem entra no prédio)
//   ordem     -> posição dentro da ala (1 = mais perto da escada)
//   lado      -> 'frente' (parede do lado da entrada) ou 'fundos'; null nas pontas
//   ponta     -> true só para as salas grandes no fim da ala (Auditório e Biblioteca)
//   setor_id  -> id em $setoresPorId quando a sala tem página própria (senão null).
//                Ids que ainda não existem em $setoresPorId são ignorados pelo
//                Repositorio.php, então o botão "Ver página do setor" só aparece
//                quando a página existir de verdade.
// ATENÇÃO: o 'lado' está alternando (frente, fundos, frente...) igual aos protótipos.
// Conferir no prédio e corrigir aqui; o desenho se ajusta sozinho.
$mapaSalas = [
    'andares' => [
        'terreo' => ['nome' => 'Térreo'],
        'andar1' => ['nome' => '1º Andar'],
    ],
    'salas' => [
        // TÉRREO - ala direita
        ['id' => 'cra', 'nome' => 'CRA', 'nomeCurto' => 'CRA', 'andar' => 'terreo', 'ala' => 'direita', 'ordem' => 1, 'lado' => 'frente', 'ponta' => false, 'setor_id' => 'cra'],
        ['id' => 'enfermagem', 'nome' => 'Sala de Enfermagem', 'nomeCurto' => 'Sala de Enfermagem', 'andar' => 'terreo', 'ala' => 'direita', 'ordem' => 2, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => 'enfermaria'],
        ['id' => 'atendimento-1', 'nome' => 'Sala de Atendimento', 'nomeCurto' => 'Sala de Atendimento', 'andar' => 'terreo', 'ala' => 'direita', 'ordem' => 3, 'lado' => 'frente', 'ponta' => false, 'setor_id' => null],
        ['id' => 'caed', 'nome' => 'CAED', 'nomeCurto' => 'CAED', 'andar' => 'terreo', 'ala' => 'direita', 'ordem' => 4, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => 'caed'],
        ['id' => 'atendimento-2', 'nome' => 'Sala de Atendimento', 'nomeCurto' => 'Sala de Atendimento', 'andar' => 'terreo', 'ala' => 'direita', 'ordem' => 5, 'lado' => 'frente', 'ponta' => false, 'setor_id' => null],
        ['id' => 'biblioteca', 'nome' => 'Biblioteca', 'nomeCurto' => 'Biblioteca', 'andar' => 'terreo', 'ala' => 'direita', 'ordem' => 6, 'lado' => null, 'ponta' => true, 'setor_id' => 'biblioteca'],
        // TÉRREO - ala esquerda
        ['id' => 'banheiros-terreo', 'nome' => 'Banheiros', 'nomeCurto' => 'Banheiros', 'andar' => 'terreo', 'ala' => 'esquerda', 'ordem' => 1, 'lado' => 'frente', 'ponta' => false, 'setor_id' => null],
        ['id' => 'napne', 'nome' => 'NAPNE', 'nomeCurto' => 'NAPNE', 'andar' => 'terreo', 'ala' => 'esquerda', 'ordem' => 2, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => 'napne'],
        ['id' => 'copa', 'nome' => 'Copa', 'nomeCurto' => 'Copa', 'andar' => 'terreo', 'ala' => 'esquerda', 'ordem' => 3, 'lado' => 'frente', 'ponta' => false, 'setor_id' => null],
        ['id' => 'espaco-ideias', 'nome' => 'Espaço Coletivo de Ideias', 'nomeCurto' => 'Espaço Coletivo de Ideias', 'andar' => 'terreo', 'ala' => 'esquerda', 'ordem' => 4, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => null],
        ['id' => 'auditorio', 'nome' => 'Auditório', 'nomeCurto' => 'Auditório', 'andar' => 'terreo', 'ala' => 'esquerda', 'ordem' => 5, 'lado' => null, 'ponta' => true, 'setor_id' => null],
        // 1º ANDAR - ala direita
        ['id' => 'banheiros-andar1', 'nome' => 'Banheiros', 'nomeCurto' => 'Banheiros', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 1, 'lado' => 'frente', 'ponta' => false, 'setor_id' => null],
        ['id' => 'cgab', 'nome' => 'CGAB', 'nomeCurto' => 'CGAB', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 2, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => 'cgab'],
        ['id' => 'manutencao-ti', 'nome' => 'Sala de Manutenção TI', 'nomeCurto' => 'Manutenção TI', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 3, 'lado' => 'frente', 'ponta' => false, 'setor_id' => null],
        ['id' => 'direcao-geral', 'nome' => 'Direção Geral', 'nomeCurto' => 'Direção Geral', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 4, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => 'direcao-geral'],
        ['id' => 'cgti', 'nome' => 'CGTI', 'nomeCurto' => 'CGTI', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 5, 'lado' => 'frente', 'ponta' => false, 'setor_id' => 'cgti'],
        ['id' => 'depex', 'nome' => 'DEPEX', 'nomeCurto' => 'DEPEX', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 6, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => 'depex'],
        ['id' => 'cgp', 'nome' => 'CGP', 'nomeCurto' => 'CGP', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 7, 'lado' => 'frente', 'ponta' => false, 'setor_id' => 'cgp'],
        ['id' => 'sala-reunioes', 'nome' => 'Sala de Reuniões', 'nomeCurto' => 'Sala de Reuniões', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 8, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => null],
        ['id' => 'dplad', 'nome' => 'DPLAD', 'nomeCurto' => 'DPLAD', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 9, 'lado' => 'frente', 'ponta' => false, 'setor_id' => 'dplad'],
        ['id' => 'cpalm', 'nome' => 'CPALM', 'nomeCurto' => 'CPALM', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 10, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => 'cpalm'],
        ['id' => 'csg', 'nome' => 'CSG', 'nomeCurto' => 'CSG', 'andar' => 'andar1', 'ala' => 'direita', 'ordem' => 11, 'lado' => 'frente', 'ponta' => false, 'setor_id' => 'csg'],
        // 1º ANDAR - ala esquerda
        ['id' => 'diretoria-ensino', 'nome' => 'Diretoria de Ensino', 'nomeCurto' => 'Diretoria de Ensino', 'andar' => 'andar1', 'ala' => 'esquerda', 'ordem' => 1, 'lado' => 'frente', 'ponta' => false, 'setor_id' => null],
        ['id' => 'coord-superiores', 'nome' => 'Coordenação de Cursos Superiores', 'nomeCurto' => 'Coord. Cursos Superiores', 'andar' => 'andar1', 'ala' => 'esquerda', 'ordem' => 2, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => null],
        ['id' => 'coord-tecnicos', 'nome' => 'Coordenação de Cursos Técnicos', 'nomeCurto' => 'Coord. Cursos Técnicos', 'andar' => 'andar1', 'ala' => 'esquerda', 'ordem' => 3, 'lado' => 'frente', 'ponta' => false, 'setor_id' => null],
        ['id' => 'dape', 'nome' => 'DAPE', 'nomeCurto' => 'DAPE', 'andar' => 'andar1', 'ala' => 'esquerda', 'ordem' => 4, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => 'dape'],
        ['id' => 'docente-bem-estar', 'nome' => 'Sala Docente — Espaço Bem-Estar', 'nomeCurto' => 'Sala Docente — Bem-Estar', 'andar' => 'andar1', 'ala' => 'esquerda', 'ordem' => 5, 'lado' => 'frente', 'ponta' => false, 'setor_id' => null],
        ['id' => 'docente-professores', 'nome' => 'Sala Docente — Professores', 'nomeCurto' => 'Sala Docente — Professores', 'andar' => 'andar1', 'ala' => 'esquerda', 'ordem' => 6, 'lado' => 'fundos', 'ponta' => false, 'setor_id' => null],
    ],
];

$faqSetor = [
    ['pergunta' => 'Como faço para solicitar uma declaração de matrícula?', 'resposta' => 'Você pode solicitar declarações através do SUAP ou presencialmente no CRA com documento de identificação. O prazo de emissão é de até 3 dias úteis.'],
    ['pergunta' => 'Quais documentos são necessários para matrícula?', 'resposta' => 'RG, CPF, comprovante de residência, histórico escolar, certificado de conclusão e 2 fotos 3x4. Documentos devem ser apresentados com originais e cópias.'],
    ['pergunta' => 'Como faço o trancamento de matrícula?', 'resposta' => 'Solicite através do SUAP ou presencialmente no CRA com justificativa. O pedido será analisado pela coordenação do curso em até 5 dias úteis.'],
    ['pergunta' => 'Qual o horário de atendimento?', 'resposta' => 'O atendimento é de segunda a sexta, das 7h às 13h e das 14h às 17h. Sem atendimento nos feriados e pontos facultativos.'],
];
// MAPA DO CAMPUS INTEIRO (mapa.php): prédios, pátio, estacionamento, campo etc.
// Medidas em metros, aproximadas da foto aérea (x = leste, z = sul; o prédio principal fica em 0,0).
// As salas do prédio principal NÃO estão aqui: elas vêm do $mapaSalas acima
// (ver mapaCampus() no Repositorio.php). Depois que o admin salvar o mapa no
// "Editor do mapa", tudo passa a vir do arquivo data/mapa-campus.json.
$mapaCampusBase = [
    'portao' => ['x' => -10, 'z' => 79],
    'blocos' => [
        ['id' => 'principal', 'nome' => 'Bloco de servidores', 'tipo' => 'predio', 'forma' => 'hall', 'x' => 0, 'z' => 0, 'w' => 110, 'd' => 20, 'andares' => 2, 'hall' => 12, 'principal' => true, 'salas' => []],
        ['id' => 'patio', 'nome' => 'Pátio', 'tipo' => 'patio', 'x' => 0, 'z' => -30, 'w' => 84, 'd' => 26, 'andares' => 1, 'salas' => []],
        ['id' => 'salas', 'nome' => 'Sala dos alunos e laboratórios', 'tipo' => 'predio', 'forma' => 'H', 'x' => 0, 'z' => -80, 'w' => 72, 'd' => 62, 'andares' => 1, 'alaLarg' => 16, 'barraLarg' => 14, 'salas' => []],
        ['id' => 'estac', 'nome' => 'Estacionamento', 'tipo' => 'estac', 'x' => -60, 'z' => 47, 'w' => 70, 'd' => 50, 'andares' => 1, 'salas' => []],
        ['id' => 'campo', 'nome' => 'Campo', 'tipo' => 'campo', 'x' => 86, 'z' => -80, 'w' => 84, 'd' => 62, 'andares' => 1, 'salas' => []],
        ['id' => 'q1', 'nome' => 'Quadra de areia', 'tipo' => 'quadra', 'x' => 116, 'z' => -94, 'w' => 16, 'd' => 22, 'andares' => 1, 'salas' => []],
        ['id' => 'q2', 'nome' => 'Quadra de areia', 'tipo' => 'quadra', 'x' => 116, 'z' => -66, 'w' => 16, 'd' => 22, 'andares' => 1, 'salas' => []],
        ['id' => 'mata', 'nome' => 'Mata', 'tipo' => 'mata', 'x' => 92, 'z' => 26, 'w' => 104, 'd' => 84, 'andares' => 1, 'salas' => []],
    ],
    // árvores soltas (x, z)
    'arvores' => [[-92, -30], [-88, -60], [-90, 6], [-30, 22], [30, 22], [-96, 75], [-40, 76], [24, 74], [44, -40], [-46, -40], [40, -116], [-44, -116]],
];
