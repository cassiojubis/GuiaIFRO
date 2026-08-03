<?php
/**
 * DADOS TEMPORÁRIOS DO FRONT-END
 *
 * Estes arrays simulam informações que futuramente poderão vir do banco.
 * Manter os dados separados do HTML facilita a integração com o backend.
 */

$atalhosRapidos = [
    ['icone' => 'graduation-cap', 'titulo' => 'Matrícula', 'descricao' => 'Processos e documentos', 'url' => 'setor.php?id=cra', 'cor' => 'green'],
    ['icone' => 'book-open', 'titulo' => 'Biblioteca', 'descricao' => 'Acervo e horários', 'url' => 'setor.php?id=biblioteca', 'cor' => 'blue'],
    ['icone' => 'heart', 'titulo' => 'Assistência', 'descricao' => 'Apoio estudantil', 'url' => 'setor.php?id=assistencia', 'cor' => 'rose'],
    ['icone' => 'file-text', 'titulo' => 'SUAP', 'descricao' => 'Sistema acadêmico', 'url' => 'setor.php?id=suap', 'cor' => 'amber'],
    ['icone' => 'users', 'titulo' => 'CRA', 'descricao' => 'Registro acadêmico', 'url' => 'setor.php?id=cra', 'cor' => 'purple'],
    ['icone' => 'briefcase-business', 'titulo' => 'Estágios', 'descricao' => 'Oportunidades', 'url' => 'setor.php?id=estagios', 'cor' => 'teal'],
];

$categorias = [
    [
        'id' => 'direcao', 'icone' => 'building-2', 'titulo' => 'Direção Geral',
        'descricaoCurta' => 'Gestão e administração',
        'descricao' => 'Gestão administrativa do campus, planejamento estratégico e coordenação geral das atividades institucionais.',
        'quantidade' => 3, 'tag' => 'Administração', 'cor' => 'slate',
        'setores' => ['Gabinete do Diretor', 'Secretaria Geral', 'Assessoria de Comunicação'],
    ],
    [
        'id' => 'ensino', 'icone' => 'graduation-cap', 'titulo' => 'Diretoria de Ensino',
        'descricaoCurta' => 'Coordenação pedagógica',
        'descricao' => 'Coordenação pedagógica, supervisão de cursos e processos de ensino-aprendizagem.',
        'quantidade' => 5, 'tag' => 'Ensino', 'cor' => 'green',
        'setores' => ['DEPESP', 'NAPNE', 'Supervisão Pedagógica', 'Monitoria', 'CPA'],
    ],
    [
        'id' => 'coordenacoes', 'icone' => 'users', 'titulo' => 'Coordenações de Cursos',
        'descricaoCurta' => 'Coordenações de cursos',
        'descricao' => 'Coordenações dos cursos técnicos, graduação e pós-graduação oferecidos no campus.',
        'quantidade' => 8, 'tag' => 'Ensino', 'cor' => 'green',
        'setores' => ['Informática', 'Administração', 'Agropecuária', 'Enfermagem', 'Florestas', 'ADS', 'Gestão Ambiental', 'Pós-Grad.'],
    ],
    [
        'id' => 'pesquisa', 'icone' => 'file-text', 'titulo' => 'Pesquisa e Inovação',
        'descricaoCurta' => 'Iniciação científica',
        'descricao' => 'Projetos de pesquisa, iniciação científica e inovação tecnológica.',
        'quantidade' => 4, 'tag' => 'Pesquisa', 'cor' => 'blue',
        'setores' => ['PROPPG', 'PIBIC', 'PIBID', 'Lab. de Pesquisa'],
    ],
    [
        'id' => 'extensao', 'icone' => 'heart', 'titulo' => 'Extensão',
        'descricaoCurta' => 'Programas comunitários',
        'descricao' => 'Programas de extensão, projetos comunitários e parcerias com a sociedade.',
        'quantidade' => 6, 'tag' => 'Extensão', 'cor' => 'rose',
        'setores' => ['PROEX', 'Incubadora', 'Eventos', 'Parcerias', 'Voluntariado', 'PIBEX'],
    ],
    [
        'id' => 'biblioteca', 'icone' => 'book-open', 'titulo' => 'Biblioteca',
        'descricaoCurta' => 'Acervo e serviços',
        'descricao' => 'Acervo bibliográfico, empréstimos, renovações e serviços de pesquisa.',
        'quantidade' => 2, 'tag' => 'Serviços', 'cor' => 'amber',
        'setores' => ['Acervo Geral', 'Periódicos e Bases'],
    ],
    [
        'id' => 'assistencia', 'icone' => 'heart', 'titulo' => 'Assistência Estudantil',
        'descricaoCurta' => 'Apoio aos estudantes',
        'descricao' => 'Programas de apoio ao estudante, auxílios, bolsas e atendimento psicossocial.',
        'quantidade' => 4, 'tag' => 'Estudante', 'cor' => 'purple',
        'setores' => ['PNAES', 'Psicologia', 'Assistência Social', 'Bolsas'],
    ],
    [
        'id' => 'ti', 'icone' => 'laptop', 'titulo' => 'Tecnologia da Informação',
        'descricaoCurta' => 'Tecnologia e suporte',
        'descricao' => 'Suporte técnico, infraestrutura de TI e sistemas institucionais.',
        'quantidade' => 3, 'tag' => 'Administração', 'cor' => 'teal',
        'setores' => ['Helpdesk', 'Infraestrutura', 'SUAP/Sistemas'],
    ],
    [
        'id' => 'administracao', 'icone' => 'briefcase-business', 'titulo' => 'Administração',
        'descricaoCurta' => 'Gestão administrativa',
        'descricao' => 'Setor administrativo, compras, patrimônio e recursos humanos.',
        'quantidade' => 5, 'tag' => 'Administração', 'cor' => 'slate',
        'setores' => ['Compras', 'Patrimônio', 'RH', 'Finanças', 'Protocolo'],
    ],
    [
        'id' => 'cra', 'icone' => 'users', 'titulo' => 'Registro Acadêmico',
        'descricaoCurta' => 'Documentação acadêmica',
        'descricao' => 'CRA, matrículas, documentação acadêmica e históricos escolares.',
        'quantidade' => 3, 'tag' => 'Estudante', 'cor' => 'purple',
        'setores' => ['CRA', 'Matrículas', 'Diplomas/Certificados'],
    ],
    [
        'id' => 'estagios', 'icone' => 'graduation-cap', 'titulo' => 'Estágios e Egressos',
        'descricaoCurta' => 'Oportunidades profissionais',
        'descricao' => 'Acompanhamento de estágios, parcerias com empresas e egressos.',
        'quantidade' => 2, 'tag' => 'Estudante', 'cor' => 'green',
        'setores' => ['COEST', 'Egressos'],
    ],
    [
        'id' => 'comunicacao', 'icone' => 'file-text', 'titulo' => 'Comunicação',
        'descricaoCurta' => 'Eventos e divulgação',
        'descricao' => 'Assessoria de comunicação, eventos institucionais e divulgação.',
        'quantidade' => 2, 'tag' => 'Administração', 'cor' => 'slate',
        'setores' => ['Ascom', 'Portal/Mídias'],
    ],
];

$noticias = [
    ['titulo' => 'Inscrições abertas para auxílio estudantil 2026', 'data' => '24 mai 2026', 'categoria' => 'Assistência', 'urgente' => true],
    ['titulo' => 'Novo laboratório de informática inaugurado', 'data' => '22 mai 2026', 'categoria' => 'Infraestrutura', 'urgente' => false],
    ['titulo' => 'Calendário acadêmico 2026/2 disponível no SUAP', 'data' => '20 mai 2026', 'categoria' => 'Acadêmico', 'urgente' => false],
    ['titulo' => 'Processo seletivo para monitoria — edital publicado', 'data' => '18 mai 2026', 'categoria' => 'Ensino', 'urgente' => false],
];

$blocosMapa = [
    [
        'id' => 'administrativo', 'nome' => 'Bloco Administrativo', 'sigla' => 'ADM',
        'icone' => 'building-2', 'cor' => 'slate', 'posicao' => 'map-pos-1',
        'setores' => [
            ['nome' => 'Direção Geral', 'telefone' => '(69) 3541-5282', 'horario' => '7h–13h', 'andar' => 'Térreo'],
            ['nome' => 'Secretaria', 'telefone' => '(69) 3541-5283', 'horario' => '7h–17h', 'andar' => 'Térreo'],
            ['nome' => 'Recursos Humanos', 'telefone' => '(69) 3541-5284', 'horario' => '7h–13h', 'andar' => '1º andar'],
        ],
    ],
    [
        'id' => 'pedagogico', 'nome' => 'Bloco Pedagógico', 'sigla' => 'PED',
        'icone' => 'presentation', 'cor' => 'green', 'posicao' => 'map-pos-2',
        'setores' => [
            ['nome' => 'Diretoria de Ensino', 'telefone' => '(69) 3541-5285', 'horario' => '7h–17h', 'andar' => 'Térreo'],
            ['nome' => 'Coordenações', 'telefone' => '(69) 3541-5286', 'horario' => '7h–17h', 'andar' => '1º andar'],
            ['nome' => 'CRA', 'telefone' => '(69) 3541-5287', 'horario' => '7h–17h', 'andar' => 'Térreo'],
        ],
    ],
    [
        'id' => 'biblioteca', 'nome' => 'Biblioteca', 'sigla' => 'BIB',
        'icone' => 'book-open', 'cor' => 'blue', 'posicao' => 'map-pos-3',
        'setores' => [
            ['nome' => 'Acervo Geral', 'telefone' => '(69) 3541-5288', 'horario' => '7h–21h', 'andar' => 'Único'],
            ['nome' => 'Empréstimos', 'telefone' => '(69) 3541-5288', 'horario' => '7h–21h', 'andar' => 'Único'],
        ],
    ],
    [
        'id' => 'laboratorios', 'nome' => 'Laboratórios', 'sigla' => 'LAB',
        'icone' => 'flask-conical', 'cor' => 'orange', 'posicao' => 'map-pos-4',
        'setores' => [
            ['nome' => 'Lab. Informática', 'telefone' => '(69) 3541-5289', 'horario' => '7h–22h', 'andar' => 'Térreo'],
            ['nome' => 'Lab. Química', 'telefone' => '(69) 3541-5290', 'horario' => '7h–17h', 'andar' => '1º andar'],
            ['nome' => 'Lab. Física', 'telefone' => '(69) 3541-5291', 'horario' => '7h–17h', 'andar' => '1º andar'],
        ],
    ],
    [
        'id' => 'refeitorio', 'nome' => 'Refeitório', 'sigla' => 'REF',
        'icone' => 'utensils-crossed', 'cor' => 'rose', 'posicao' => 'map-pos-5',
        'setores' => [
            ['nome' => 'Cantina', 'telefone' => '(69) 3541-5292', 'horario' => '7h–20h', 'andar' => 'Único'],
            ['nome' => 'Restaurante', 'telefone' => '(69) 3541-5293', 'horario' => '11h–14h', 'andar' => 'Único'],
        ],
    ],
    [
        'id' => 'auditorio', 'nome' => 'Auditório', 'sigla' => 'AUD',
        'icone' => 'presentation', 'cor' => 'indigo', 'posicao' => 'map-pos-6',
        'setores' => [
            ['nome' => 'Eventos', 'telefone' => '(69) 3541-5294', 'horario' => 'Sob agendamento', 'andar' => 'Único'],
        ],
    ],
    [
        'id' => 'quadra', 'nome' => 'Quadra Esportiva', 'sigla' => 'EDF',
        'icone' => 'dumbbell', 'cor' => 'teal', 'posicao' => 'map-pos-7',
        'setores' => [
            ['nome' => 'Educação Física', 'telefone' => '(69) 3541-5295', 'horario' => '7h–17h', 'andar' => 'Único'],
        ],
    ],
];

$faqSetor = [
    ['pergunta' => 'Como faço para solicitar uma declaração de matrícula?', 'resposta' => 'Você pode solicitar declarações através do SUAP ou presencialmente no CRA com documento de identificação. O prazo de emissão é de até 3 dias úteis.'],
    ['pergunta' => 'Quais documentos são necessários para matrícula?', 'resposta' => 'RG, CPF, comprovante de residência, histórico escolar, certificado de conclusão e 2 fotos 3x4. Documentos devem ser apresentados com originais e cópias.'],
    ['pergunta' => 'Como faço o trancamento de matrícula?', 'resposta' => 'Solicite através do SUAP ou presencialmente no CRA com justificativa. O pedido será analisado pela coordenação do curso em até 5 dias úteis.'],
    ['pergunta' => 'Qual o horário de atendimento?', 'resposta' => 'O atendimento é de segunda a sexta, das 7h às 13h e das 14h às 17h. Sem atendimento nos feriados e pontos facultativos.'],
];