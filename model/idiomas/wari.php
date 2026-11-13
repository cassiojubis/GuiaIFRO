<?php
/**
 * TRADUÇÕES EM ORO WARI (PAKAA NOVA)
 *
 * Fonte: documento "tradução oro wari (pakaa nova).pdf" (21 páginas), preenchido
 * por quem fala a língua. Aqui está CADA linha do documento, na mesma ordem:
 *  - com texto  => tradução que aparece no site;
 *  - vazio ('') => ainda pendente: o site mostra o português (nunca a célula vazia).
 *
 * Regras (não mudar sem conferir com quem traduziu):
 *  - a grafia é a do documento (só os espaços duplos viraram um espaço);
 *  - o que o documento escreveu entre parênteses como explicação (ex.: "(busca ao vivo...)")
 *    não entra no site, só a tradução da frase;
 *  - a chave é o texto em português EXATAMENTE como aparece no site. A frase só é trocada
 *    se for igual à chave inteira — nunca traduzimos pedaço de frase.
 *
 * Como é usado:
 *  - model/Idiomas.php: menu, rodapé e título (t()), aviso e seletor de idioma;
 *  - js/traducao-wari.js: o resto do texto da página (igual ao espanhol).
 *
 * Partes:
 *  'comum'   => vale em todas as páginas;
 *  'paginas' => só naquela página (nome do arquivo: index, mapa, setor...; '404' = página de erro).
 *               Ganha do 'comum' (o mesmo texto pode ter tradução diferente em cada página);
 *  'escopos' => só dentro de um pedaço da tela (seletor CSS), opcionalmente numa página.
 *               Ganha de tudo (ex.: "Acesso rápido" tem duas traduções na home);
 *  'padroes' => frases com número/ano no meio.
 *
 * Relatório do que já foi traduzido: php bin/relatorio-traducao-wari.php
 */

return [
    // ===== Elementos comuns (menu, busca, rodapé) — PDF pág. 2 =====
    'comum' => [
        'Início' => "Ka xo ara' xine'",
        'Categorias' => 'Ka to pi pene',
        'Mapa' => 'Makan',
        'Sobre' => "Ka tomi pi xine'",
        'Contato' => 'Mam tomi wa wari nem',
        'Buscar setor...' => 'Ka noro xine',
        'Nada encontrado' => 'Om ka mam kamai xine',
        'Fale conosco' => 'Tomi xuk',
        'Ver detalhes' => 'Noro pi xine',
        'Navegação' => 'Ka noro xine',
        'Todos os direitos reservados.' => "Ka pe pain ratakanawa ka wara ak' xine",
        'Acessibilidade' => 'Oro ko xiarak nana',
        'Acesso Rápido' => 'Ka Kohro rap pene',
        'Portal IFRO' => 'Kohro yei IFRO',
        'Mapa Interativo' => 'Mi nein ka towha ne',
        'Sobre o IFRO' => 'Ka tomi xine IFRO',
        'Diversidade e Inclusão' => 'Ka tomi tohwa oro wari',
        'Política de Privacidade' => 'Ka takaro main karawa nem',
        'Área do Administrador' => 'Ka to kaka oro wari nein xitok',
        'Mais opções de acessibilidade' => 'Ka xirao in hwe ma xine',
        'Modo escuro' => 'Ka yitaka nein',
        'Modo claro' => 'Ka yitaka wiyimain nein',
        'Sistema de navegação e informações do IFRO Campus Guajará-Mirim.' => 'Ka mam ara xine kayi arai too ,pain tomi xine IFRO makak nein Guajará -Mirim.',

        // Notícias (aparecem na home e em noticias.php) — PDF pág. 4
        'Inscrições abertas para auxílio estudantil 2026' => "Mo ta xirao' main ma pin ak quen tara kayi tam",
        'Novo laboratório de informática inaugurado' => "Xokori ka man xirao' to wa",
        'Calendário acadêmico 2026/2 disponível no SUAP' => 'Pain ka noro main oro menen pain SUAP',
        'Processo seletivo para monitoria — edital publicado' => "Oro ka man ara wa, pain ka xo' konkom wa",
    ],

    'paginas' => [
        // ===== Página: Início (index.php) — PDF págs. 2 a 5 =====
        'index' => [
            'Sistema Oficial do IFRO — Campus Guajará-Mirim' => 'ka ara xine mi nein IFRO-campus Guajará mirim',
            'Bem-vindo ao Guia IFRO' => "Hraram ye' pain IFRO",
            'Encontre setores, coordenações, serviços, horários e informações do campus em um só lugar.' => 'Om ka man kamain xine katho nein Oro wiyimain ka tomi wa pain campus pain oro xiyein makak',
            'Ex: matrícula, biblioteca, assistência estudantil...' => "Ka ara ak to xo main ka xirao' nem ,ka noro main ka xirao' ,pain ka hwei momon oro querek nenem",
            'Buscar' => "Mo an rai'",
            'Seus favoritos' => 'Karawa ka ya ya ma',
            'Visitados recentemente' => 'Ka noro main ,mam ak main na',
            'Acesso rápido' => 'Ka piram main ,mam hram ak main na',
            'O que você procura?' => 'Ma ka piram ma?',
            'Setores do campus' => 'Ka to ne ka ara wa oro karawa',
            'Categorias' => "Ka to pi pene'",
            'Ver todas' => 'Ka noro pi xine',
            // Chamada para o mapa
            'Mapa interativo' => 'Mi nein ka tohwa ne',
            'Explore o Campus Guajará-Mirim' => 'Ka ara to xine oro kayi arai too pain Guajarajá-Mirim',
            'Visualize todos os blocos, laboratórios e setores do campus de forma interativa. Clique nos blocos para ver informações e contatos.' => "Pain ka noro pi xine,ka pe pan ne ka noro pi xine oro karawa yimai pain campus ye'pain,ka yuk main tahwen pin ak ne na",
            'Abrir mapa interativo' => 'pain ka noro xine oro xijein',
            'Administrativo' => 'Pain ka ara awi pin xo nein',
            'Pedagógico' => "Oro ka to ne ka xirao'",
            'Laboratórios' => 'Ka to ne ka men men pe wa arai too',
            // Notícias do campus
            'Avisos' => 'Pain ka tomi wa',
            'Notícias do campus' => 'Xa i nein campus',
            // Diferenciais
            'Por que usar' => 'Ma krawa ka ara ma',
            'Diferenciais' => 'xijein',
            'Informações atualizadas' => "Xokri' ka tomi wa",
            'Dados de contato e horários sempre atualizados pela administração do campus.' => 'Mam tomi wa ye tokon xina',
            'Sistema oficial' => 'Oro mam noro wa',
            'Plataforma homologada pelo IFRO — conteúdo verificado e confiável.' => "Ka mam taxi' wa pain IFRO_,ka mam ara pi xine quem",
            'Encontre qualquer setor em segundos, sem precisar perguntar a ninguém.' => 'Mam noro wa xukun setor pain xijein xek, ye pain ka om mam wa wari quem',
            // Chamada final para contato
            'Precisa de ajuda?' => 'Ma ka pram ma',
            'Entre em contato pelos nossos canais oficiais — estamos prontos para atender.' => 'ka to pine ka tomi tokwa oro wari nem',
            'Formulário de contato' => 'Ka xirao pe xine',
        ],

        // ===== Página: Mapa Interativo (mapa.php) — PDF pág. 5 =====
        'mapa' => [
            'Mapa do Campus' => 'Makan nein campus',
            'Clique nos blocos para ver setores, horários e contatos.' => "Yukum rain kayi arain too' pain ka mam nro main tokon xina ye mam tomi' wa",
            'Entrada principal' => 'Mam krom ma',
            'Legenda' => 'Me nein legenda',
            'Voltar para os andares' => 'Ka in mam nein oro andares',
            'Lado esquerdo' => "Xikati' praixi'",
            'Lado direito' => "Xikati' tapanaxi",
            'Escada / Entrada' => 'Mam pri wa / krom wa',
            'Corredor' => 'Kote ne wana',
            'Selecione um bloco' => 'Yukum arain bloco',
            'Clique em qualquer bloco no mapa para ver setores e informações de contato.' => 'yukum arain piram ak mam tara bloco,noro ak mam tara mapa pain ka oro xijein sotores noro ak mam tara mam tomi wa',
            'Ver detalhes completos' => 'Ka noro mam detalhas ka tomi pi xine',
        ],

        // ===== Página: Categorias / Setores do Campus (categorias.php) — PDF págs. 7 a 10 =====
        'categorias' => [
            'Setores do Campus' => 'Oro setores nein campus',
            'Explore todos os setores, coordenações e serviços disponíveis no Campus Guajará-Mirim.' => 'Pain noro pi pin momon oro setores,oro coordenação ye pain serviços wiri ko ko to nanin disponíveis pain campus nein Guajará – Mirim.',
            'Buscar setor, serviço ou coordenação...' => '',
            'Limpar filtros' => '',
            'Ocultar setores' => '',
            'Nenhum resultado encontrado' => '',
            'Tente outros termos ou remova os filtros.' => '',
            'Ver todas as categorias' => '',
            'Não encontrou o que procura?' => 'Om ka mam xi ne ka pran ma',
            'Entre em contato com a nossa equipe e teremos prazer em orientar você.' => '',
            // Grupos do organograma
            'Órgãos de direção e colegiados' => '',
            'Coordenações ligadas à Direção-Geral' => '',
            'Diretoria de Ensino (DE)' => '',
            'Pesquisa e Pós-Graduação' => '',
            'Extensão' => '',
            'Planejamento e Administração' => '',
            'Setores de apoio aos estudantes' => '',
            'Outros núcleos' => '',
            // Cartões (título — descrição)
            'Direção-Geral (DG)' => '',
            'responsável pela direção geral e pela administração do campus.' => '',
            'Colegiado de Curso' => '',
            'grupo que trata de assuntos relacionados ao curso.' => '',
            'Conselho de Classe' => '',
            'discute o desempenho e a situação dos estudantes.' => '',
            'Conselho Escolar' => '',
            'participa de decisões importantes relacionadas ao campus.' => '',
            'CCOM – Comunicação e Eventos' => '',
            'cuida da comunicação e dos eventos do campus.' => '',
            'CACI – Avaliação e Controle Interno' => '',
            'trabalha com avaliação e controle interno.' => '',
            'CGP – Gestão de Pessoas' => '',
            'cuida de assuntos relacionados aos servidores.' => '',
            'CGTI – Gestão da Tecnologia da Informação' => '',
            'cuida da informática, sistemas e tecnologia.' => '',
            'CGAB – Chefia de Gabinete' => '',
            'auxilia diretamente a Direção-Geral.' => '',
            'Protocolo e Arquivo' => '',
            'organiza documentos, protocolos e arquivos.' => '',
            'DAPE – Departamento de Apoio ao Ensino' => '',
            'dá suporte às atividades de ensino.' => '',
            'CEaD – Coordenação de Educação a Distância' => '',
            'trata das atividades de educação a distância.' => '',
            'Coordenações de Cursos' => '',
            'acompanham e organizam os cursos.' => '',
            'CRA – Coordenação de Registros Acadêmicos' => '',
            'cuida dos registros acadêmicos dos estudantes, como documentação e informações escolares.' => '',
            'DEPESP – Departamento de Pesquisa, Inovação e Pós-Graduação' => '',
            'coordena atividades de pesquisa, inovação e pós-graduação.' => '',
            'CPI – Coordenação de Pesquisa e Inovação' => '',
            'acompanha projetos de pesquisa e inovação.' => '',
            'CPOSG – Coordenação de Pós-Graduação' => '',
            'trabalha com atividades de pós-graduação.' => '',
            'NIT – Núcleo de Inovação Tecnológica' => '',
            'apoia ações relacionadas à inovação e tecnologia.' => '',
            'DEPEX – Departamento de Extensão' => '',
            'coordena atividades de extensão do campus.' => '',
            'CFIC – Coordenação de Formação Inicial e Continuada' => '',
            'trabalha com cursos e ações de formação inicial e continuada.' => '',
            'DPLAD – Diretoria de Planejamento e Administração' => '',
            'cuida do planejamento e da administração.' => '',
            'COFIN – Coordenação de Orçamento e Finanças' => '',
            'trabalha com orçamento e recursos financeiros.' => '',
            'Contadoria' => '',
            'trabalha com registros e controles contábeis.' => '',
            'CSG – Coordenação de Serviços Gerais' => '',
            'cuida dos serviços gerais do campus.' => '',
            'CPALM – Patrimônio e Almoxarifado' => '',
            'cuida dos materiais, estoque e patrimônio.' => '',
            'CCL – Compras e Licitações' => '',
            'trabalha com compras e processos de licitação.' => '',
            'CCONV – Gestão de Contratos e Convênios' => '',
            'acompanha contratos e convênios.' => '',
            'CAED – Coordenação de Assistência ao Educando' => '',
            'trabalha com assistência e apoio aos estudantes.' => '',
            'CBIB – Coordenação de Biblioteca' => '',
            'administra os serviços da biblioteca.' => '',
            'CIEEC – Coordenação de Integração Escola, Empresa e Comunidade' => '',
            'promove a integração entre o campus, empresas e comunidade.' => '',
            'dão suporte às atividades práticas.' => '',
            'NUPEM – Núcleo Pedagógico Multidisciplinar' => '',
            'oferece apoio relacionado às questões pedagógicas.' => '',
            'NAPNE – Núcleo de Atendimento às Pessoas com Necessidades Educacionais Específicas' => '',
            'trabalha com apoio e inclusão de estudantes que necessitam de atendimento educacional específico.' => '',
            'NDE – Núcleo Docente Estruturante' => '',
            'grupo ligado ao planejamento e acompanhamento do curso.' => '',
        ],

        // ===== Página: Detalhes do Setor (setor.php) — PDF págs. 10 a 13 =====
        'setor' => [
            'Aberto agora' => 'Pa nain xokrin ka',
            'Fechado agora' => "Tao' pa nain xokrin ka",
            'Telefone' => 'Ka tomi´ wa',
            'E-mail' => 'Mam xirao mi mon wari nem',
            'Horário' => 'Yamikom xina',
            'Localização' => 'Pain pe nem',
            'Responsável' => "Kotema / yim'",
            'Responsável pelo setor' => 'Taramaxikon nein setor',
            'Equipe' => 'Oro wari nem',
            'Perguntas Frequentes' => 'Ka tomi',
            'Serviços Oferecidos' => 'Kayi wari',
            'Documentos Úteis' => "Oro ka mam xirao xo' wa",
            'Não informado.' => 'Om ka tomi',
            'Dúvidas?' => 'Ko om tarahu pa?',
            'Entre em contato diretamente com nossa equipe.' => 'Ka mam tomi mon wari nem',
            // Textos de cada setor (vêm do banco) — todos pendentes no documento
            'Apoio às atividades de ensino, pesquisa e extensão.' => '',
            'Ao lado da CAED' => '',
            'Empréstimo' => '',
            'Consulta de acervo' => '',
            'Espaço de estudos' => '',
            'Suporte pedagógico, psicológico e social aos discentes.' => '',
            'Próximo à CRA e à Enfermaria' => '',
            'Orientação Educacional (baixo rendimento, aprendizagem, organização e risco de reprovação)' => '',
            'Acompanhamento da vida escolar (frequência e desempenho)' => '',
            'Orientação, acolhimento e suporte biopsicossocial' => '',
            'Assistência Estudantil (auxílios e programas)' => '',
            'Mediação de conflitos e convivência escolar' => '',
            'Atendimento à diversidade, interculturalidade e prevenção da evasão escolar' => '',
            'Ações e diretrizes voltadas à acessibilidade e inclusão escolar.' => '',
            'Próximo ao auditório' => '',
            'Monitoria do processo de inclusão de pessoas com deficiência' => '',
            'Atendimento Educacional Especializado (AEE)' => '',
            'Unidade de suporte e primeiros socorros do campus.' => '',
            'Próximo à CRA' => '',
            'Primeiros socorros' => '',
            'Acolhimento' => '',
            'Triagem inicial de saúde' => '',
            'Registro, arquivo e controle da vida acadêmica do estudante.' => '',
            'Primeiro prédio da escola, primeira sala à direita logo após a entrada' => '',
            'Matrícula' => '',
            'Transferência' => '',
            'Renovação de matrícula' => '',
            'Emissão de certificados e diplomas' => '',
            'Alimentação de sistemas (censo escolar, governo federal)' => '',
            'Suporte ao conselho de classe' => '',
            'Apoio pedagógico e estrutural às rotinas acadêmicas.' => '',
            'Segundo andar do primeiro prédio, próximo à coordenação de cursos' => '',
            'Suporte às coordenações e alunos' => '',
            'Organização do quadro de horários' => '',
            'Acompanhamento pedagógico' => '',
            'Gestão pedagógica e administrativa do curso técnico.' => '',
            'Segundo andar do primeiro prédio, próximo ao DAPE' => '',
            'Coordenação das atividades acadêmicas e administrativas do curso' => '',
            'Acompanhamento docente' => '',
            'Reuniões de colegiado' => '',
            'Organização de horários' => '',
            'Ações institucionais' => '',
            // Perguntas frequentes
            'Como faço para solicitar uma declaração de matrícula?' => '',
            'Você pode solicitar declarações através do SUAP ou presencialmente no CRA com documento de identificação. O prazo de emissão é de até 3 dias úteis.' => '',
            'Quais documentos são necessários para matrícula?' => '',
            'RG, CPF, comprovante de residência, histórico escolar, certificado de conclusão e 2 fotos 3x4. Documentos devem ser apresentados com originais e cópias.' => '',
            'Como faço o trancamento de matrícula?' => '',
            'Solicite através do SUAP ou presencialmente no CRA com justificativa. O pedido será analisado pela coordenação do curso em até 5 dias úteis.' => '',
            'Qual o horário de atendimento?' => '',
            'O atendimento é de segunda a sexta, das 7h às 13h e das 14h às 17h. Sem atendimento nos feriados e pontos facultativos.' => '',
        ],

        // ===== Página: Sobre o IFRO (sobre.php) — PDF págs. 13 a 15 =====
        // A seção "Diversidade e Inclusão" (PDF págs. 17 e 18) fica nesta página (sobre.php#diversidade).
        'sobre' => [
            'Conheça a história, missão, visão e valores do Campus Guajará-Mirim — referência em educação na Amazônia.' => '',
            'Nossa trajetória' => '',
            'Nossa História' => 'Pain ka ruk pain historia ne xuk',
            'O Instituto Federal de Educação, Ciência e Tecnologia de Rondônia (IFRO) foi criado em 2008, através da Lei nº 11.892, como parte da expansão da Rede Federal de Educação Profissional, Científica e Tecnológica no Brasil.' => '',
            'O Campus Guajará-Mirim iniciou suas atividades em 2010, com a missão de promover educação profissional e tecnológica de excelência, contribuindo para o desenvolvimento econômico e social da região fronteiriça entre Brasil e Bolívia.' => '',
            'Ao longo dos anos, o campus consolidou-se como referência em educação na região, oferecendo cursos técnicos integrados ao ensino médio, cursos superiores, licenciaturas e pós-graduação.' => '',
            'Hoje contamos com infraestrutura moderna, laboratórios equipados, biblioteca atualizada e corpo docente altamente qualificado.' => '',
            'Promover educação profissional, científica e tecnológica por meio do ensino, pesquisa e extensão, com foco na formação integral do cidadão e no desenvolvimento sustentável.' => '',
            'Ser referência em educação profissional e tecnológica na Amazônia, reconhecida pela qualidade, inovação e compromisso com o desenvolvimento regional.' => '',
            'Ética, transparência, inclusão social, excelência no ensino, respeito à diversidade e compromisso com o desenvolvimento sustentável da região.' => '',
            'Princípios que nos guiam' => '',
            'Nossos Valores' => '',
            'Educação pública, gratuita e de qualidade' => '',
            'Inclusão social e democratização do conhecimento' => '',
            'Ética, transparência e compromisso social' => '',
            'Inovação e excelência no ensino e na pesquisa' => '',
            'Desenvolvimento sustentável da região amazônica' => '',
            'Respeito à diversidade e aos direitos humanos' => '',
            'Momentos marcantes' => '',
            'Linha do Tempo' => '',
            'Instituto Federal de Rondônia criado pela Lei nº 11.892, como parte da expansão da Rede Federal de Educação.' => '',
            'Campus Guajará-Mirim inicia suas atividades com os primeiros cursos técnicos integrados ao ensino médio.' => '',
            'Ampliação da oferta com cursos superiores de tecnologia e licenciaturas para atender a região.' => '',
            'Inauguração de novos laboratórios modernos e reforma e expansão da biblioteca do campus.' => '',
            'Campus recebe certificação de excelência em ensino técnico-profissional pela avaliação nacional.' => '',
            'Continuamos crescendo e transformando vidas por meio de educação de qualidade para toda a região.' => '',
            // Diversidade e Inclusão
            'O Campus Guajará-Mirim recebe estudantes de diferentes origens, culturas e necessidades — o Guia IFRO é construído para refletir isso.' => '',
            'Tamanho de fonte ajustável, tradutor de Libras (VLibras) em todas as páginas e um site pensado pra ser navegável por qualquer pessoa.' => '',
            'Site disponível em Português e Espanhol, com o Oro Wari (Pakaa Nova) em desenvolvimento junto à comunidade indígena da região.' => '',
            'NAPNE e CAED oferecem suporte a estudantes com deficiência, altas habilidades e em situação de vulnerabilidade.' => '',
            'Línguas indígenas' => '',
            'O idioma Oro Wari (Pakaa Nova)' => '',
            'Apoio ao estudante' => '',
            'NAPNE e CAED' => '',
        ],

        // ===== Página: Fale Conosco (contato.php) — PDF págs. 15 a 17 =====
        'contato' => [
            'Fale Conosco' => '',
            'Tire suas dúvidas, envie sugestões ou solicite informações — nossa equipe está pronta para ajudar.' => '',
            'Envie sua mensagem' => '',
            'Preencha o formulário abaixo e retornaremos o mais breve possível.' => '',
            'Nome completo' => 'Wixun je',
            'Digite seu nome' => 'Xirao wixun',
            'E-mail' => '',
            'Telefone' => '',
            'Assunto' => '',
            'Selecione um assunto' => '',
            'Mensagem' => '',
            'Escreva sua mensagem...' => '',
            'Enviar mensagem' => '',
            'Enviando...' => '',
            'Ao enviar, você concorda com nossa Política de Privacidade.' => '',
            'Dúvida sobre matrícula' => '',
            'Assistência estudantil' => '',
            'Biblioteca' => '',
            'Estágio e SUAP' => '',
            'Solicitação de documentos' => '',
            'Sugestão ou elogio' => '',
            'Outro assunto' => '',
            'Mensagem enviada!' => '',
            'Obrigado por entrar em contato. Nossa equipe responderá o mais breve possível pelo e-mail informado.' => '',
            'Enviar outra mensagem' => '',
            'Nossa localização' => '',
            'Veja o mapa interativo do campus' => '',
            'Redes sociais' => '',
        ],

        // ===== Página: Acessibilidade (acessibilidade.php) — PDF págs. 18 e 19 =====
        'acessibilidade' => [
            'O Guia IFRO é feito para ser usado por qualquer pessoa da comunidade acadêmica. Ajuste o tamanho da fonte e escolha o idioma abaixo.' => '',
            'Leitura' => '',
            'Tamanho da fonte' => '',
            'Aumente o tamanho do texto em todo o site. A escolha fica salva no seu navegador — não precisa repetir a cada página.' => '',
            'Normal' => '',
            'Média' => '',
            'Grande' => '',
            'Veja como fica:' => '',
            'O Guia IFRO ajuda você a encontrar setores, salas e informações do campus.' => '',
            'Conforto visual' => '',
            'Tema' => '',
            'Alterne entre o tema claro e o escuro. A escolha fica salva no seu navegador.' => '',
            'Idioma' => '',
            'Idioma / Language / Idioma' => '',
            'Escolha o idioma de navegação do site. A tradução vale para o menu, botões e esta página.' => 'Ka noro mam oro idioma ye i ka nevegação pain site.ye pain tradução pain oro xijein krawa .je pain botões ye pain oro xijein página',
            'Selecionado' => 'selecionado',
            // Nomes dos idiomas: aparecem embaixo do nome original no seletor (model/Idiomas.php)
            'Português' => 'Ka tomi wa kapiyakokon wijam',
            'Español' => 'Ka tomi wa kapiyakokon tatrim',
            'Oro Wari (Pakaa Nova)' => 'Ka to ka ka oro wari nein (Pakaa Nova)',
            'Sobre o Oro Wari (Pakaa Nova)' => 'Tomi non oro wari nein (Pakaa Nova)',
            'Ainda não temos uma tradução validada para o Oro Wari. Como é a língua de uma comunidade indígena específica, o correto é traduzir o conteúdo em conjunto com falantes nativos ou um linguista — para não publicar frases incorretas em nome da língua. O site continua em português enquanto essa parceria não acontece. Essa opção fica aqui para mostrar que o suporte a línguas indígenas é um objetivo real do projeto, não só do português e espanhol.' => '',
        ],

        // ===== Página: Política de Privacidade (privacidade.php) — PDF págs. 19 a 21 =====
        'privacidade' => [
            'Como o Guia IFRO trata as informações que você compartilha ao usar o site.' => 'Kain ka ara Guia IFRO pain ka tomi xine ye pain compartilha xine ye pain ka usar xine pain site.',
            'Quais dados coletamos' => '',
            'O único formulário do site é o de contato, onde pedimos nome, e-mail e mensagem para poder responder você. Não pedimos CPF, endereço completo ou qualquer outro dado sensível.' => '',
            'Também guardamos, só no seu navegador (nunca em um servidor), duas preferências: o tamanho de fonte escolhido e o idioma selecionado na página de Acessibilidade.' => '',
            'Para que usamos' => '',
            'Os dados do formulário de contato são usados exclusivamente para responder à sua mensagem. Não vendemos, alugamos ou compartilhamos essas informações com terceiros, e não usamos os dados para propaganda.' => '',
            'Seus direitos (LGPD)' => '',
            'Cookies e ferramentas de terceiros' => '',
            'Contato sobre privacidade' => 'Mam tomi wa pain privacidade',
            'Dúvidas ou solicitações sobre seus dados podem ser enviadas para os canais de contato do campus.' => 'Ka om taxi wa ye solicitações ye pain ka tomi nein ka enviadas mam oro canais ye contato ye campus.',
        ],

        // ===== Página: Não encontrada (404.php) — PDF pág. 21 =====
        '404' => [
            'Página não encontrada' => 'Piram ram om ka mam kamain xi ne',
            'A página que você tentou acessar não existe ou foi movida. Que tal tentar um destes caminhos?' => 'Pain ka ara paxi mam om ak ne na há ne. Mayi ara xi pain wana?',
            'Ir para o início' => 'Ka xo ara xine',
            'Ver o mapa' => 'Noro wa makan',
            'Falar conosco' => 'Tomi xuk',
        ],
    ],

    'escopos' => [
        // Título da página no topo (o rodapé tem o mesmo texto com outra tradução)
        ['pagina' => 'sobre', 'seletor' => '.about-hero', 'textos' => [
            'Sobre o IFRO' => 'Pain IFRO',
        ]],
        ['pagina' => 'privacidade', 'seletor' => '.page-header', 'textos' => [
            'Política de Privacidade' => 'Ka tomi xine política de privacidade',
        ]],

        // Home › Atalhos rápidos: o documento traduziu "Título — descrição" como uma frase só.
        // O título é sigla/nome do setor e fica; a tradução entra no lugar da descrição.
        ['pagina' => 'index', 'seletor' => '.quick-card small', 'textos' => [
            'Acervo e estudos' => 'Pain ka to kaka ko param nanain ka xirao wa',
            'Apoio ao ensino e discente' => "Pain ka to kaka ko querek nonon ko xirao' nana",
            'Acessibilidade e inclusão' => "Pain ka to kaka oro ko mi nanain ka xirao'",
            'Primeiros socorros' => "Ko querek nonok oro ko xirao'",
            'Registro acadêmico' => 'Pain ka to kaka oro hrata kana',
            'Apoio pedagógico' => 'pain ka to kaka oro ko ara nana kayi karawa',
        ]],

        // Home › Diferenciais: "Acesso rápido" aqui tem outra tradução
        ['pagina' => 'index', 'seletor' => '.feature-card', 'textos' => [
            'Acesso rápido' => 'Ara wap ne',
        ]],

        // Categoria das notícias (só a etiqueta, pra não trocar a palavra em outros lugares)
        ['seletor' => '.news-meta', 'textos' => [
            'Assistência' => 'oro ka main tarahuwa pain xa i',
            'Infraestrutura' => 'Ka man ara wa pain ,ka tarahuwa xa i',
            'Acadêmico' => 'Ka pe ne ka man tomi wa xa i',
            'Ensino' => 'Ka taxi xine ka tomi hwei xine xa iss',
        ]],

        // Categorias › botão de filtro "Todos"
        ['pagina' => 'categorias', 'seletor' => '.filter-group', 'textos' => [
            'Todos' => 'Oro',
        ]],

        // Mapa (na página do mapa e na home): andares e salas da planta — PDF págs. 5 e 6
        ['seletor' => '.mapa-secao', 'textos' => [
            'Térreo' => 'Kayi wari',
            '2º Andar' => 'pawin',
            // "Escada / Entrada" => "Mam pri wa / krom wa": no mapa as duas palavras aparecem separadas
            'Escada' => 'Mam pri wa',
            'Entrada' => 'krom wa',
            // Salas do térreo
            'CRA' => 'Ka pene CRA',
            'Sala de Enfermagem' => 'Ka pene Enfermagem',
            'Sala de Atendimento' => 'Ka pene Atendimento',
            'CAED' => 'CAED',
            'Biblioteca' => 'Ka thu nei oro papel',
            'Banheiros' => 'Mam ya wa xuk',
            'NAPNE' => 'NAPNE',
            'Copa' => 'copa',
            'Espaço Coletivo de Ideias' => 'Ka ak to xo xine oro kayi krawa',
            'Auditório' => 'Ka to pan ne ka pijim to wa',
            // Salas do andar de cima (todas pendentes no documento; "Banheiros" já está acima)
            'CGAB' => '',
            'Sala de Manutenção TI' => '',
            'Direção Geral' => '',
            'CGTI' => '',
            'DEPEX' => '',
            'CGP' => '',
            'Sala de Reuniões' => '',
            'DPLAD' => '',
            'CPALM' => '',
            'CSG' => '',
            'Diretoria de Ensino' => '',
            'Coordenação de Cursos Superiores' => '',
            'Coordenação de Cursos Técnicos' => '',
            'DAPE' => '',
            'Sala Docente — Espaço Bem-Estar' => '',
            'Sala Docente — Professores' => '',
        ]],
    ],

    // Frases com número ou ano no meio. 'origem' é como está no documento.
    'padroes' => [
        ['origem' => '[número] setor', 'expr' => '^(\d+) setor$', 'troca' => '$1 nein setor'],
        ['origem' => '[número] blocos disponíveis no campus', 'expr' => '^(\d+) blocos disponíveis no campus$', 'troca' => '$1 nein blocos pain ka disponíveis nem pain campus'],
        ['origem' => 'Planta baixa — [nome do andar]', 'expr' => '^Planta baixa — (.+)$', 'troca' => 'Pain Makan — $1'],
        ['origem' => 'Última atualização: [ano atual]', 'expr' => '^Última atualização: (\d{4})\.?$', 'troca' => 'Ka main atualização: $1'],
        ['origem' => '[número] categoria(s) encontrada(s)', 'expr' => '', 'troca' => ''],
    ],
];
