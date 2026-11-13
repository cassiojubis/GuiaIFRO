<?php
/**
 * DADOS INICIAIS DO BANCO
 *
 * Importa para o banco os dados de sql/dados-iniciais.php e os textos que antes
 * ficavam fixos nos controllers do admin. Quem chama é o bin/instalar-banco.php.
 *
 * Junta as duas listas que existiam separadas:
 *   - $gruposCategorias (organograma: 34 itens em 8 grupos) e
 *   - $setoresCampus (7 setores com contato, horário, equipe...).
 * Agora cada item do organograma é um SETOR, e o grupo vira a CATEGORIA.
 *
 * Nada aqui é inventado: o que não existia continua vazio (NULL) e o site
 * mostra "Não informado" / "Informações em breve".
 */

require_once __DIR__ . '/../model/Config.php'; // criarSlugTexto()

/** Apaga o conteúdo e importa tudo de novo. NÃO mexe em usuários nem em mensagens recebidas. */
function semearBanco(PDO $pdo, array $segredosAdmin): void
{
    require __DIR__ . '/dados-iniciais.php';

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach ([
        'fluxo_passo', 'fluxo_documento', 'fluxo', 'sinonimo', 'faq_setor', 'faq', 'setor_equipe', 'setor_servico', 'setor_documento', 'setor_horario',
        'setor_excecao', 'acessibilidade_sala', 'sala', 'andar', 'setor', 'categoria',
        'aviso', 'noticia', 'link_util', 'texto_institucional', 'atalho_home',
    ] as $tabela) {
        $pdo->exec("DELETE FROM {$tabela}");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    // Os dados abaixo usam os nomes curtos ('setores', 'nome'...). O $inserir troca pelo
    // nome de verdade do banco (padrão do professor: 'setor', 'seto_nome'...).
    $nomesNoBanco = [
        'categorias' => ['categoria', ['id' => 'cate_id', 'nome' => 'cate_nome', 'icone' => 'cate_icone', 'cor' => 'cate_cor', 'ordem' => 'cate_ordem', 'ativo' => 'cate_ativo', 'atualizado_em' => 'cate_atualizado_em']],
        'setores' => ['setor', ['id' => 'seto_id', 'categoria_id' => 'cate_id', 'nome' => 'seto_nome', 'sigla' => 'seto_sigla', 'icone' => 'seto_icone', 'cor' => 'seto_cor', 'descricao' => 'seto_descricao', 'descricao_curta' => 'seto_descricao_curta', 'texto_facil' => 'seto_texto_facil', 'localizacao' => 'seto_localizacao', 'bloco' => 'seto_bloco', 'sala_texto' => 'seto_sala_texto', 'telefone' => 'seto_telefone', 'email' => 'seto_email', 'responsavel' => 'seto_responsavel', 'video' => 'seto_video', 'horario_texto' => 'seto_horario_texto', 'ordem' => 'seto_ordem', 'ativo' => 'seto_ativo', 'fila_status' => 'seto_fila_status', 'fila_atualizada_em' => 'seto_fila_atualizada_em', 'atualizado_em' => 'seto_atualizado_em', 'atualizado_por' => 'seto_atualizado_por']],
        'setor_equipe' => ['setor_equipe', ['id' => 'equi_id', 'setor_id' => 'seto_id', 'nome' => 'equi_nome', 'cargo' => 'equi_cargo', 'ordem' => 'equi_ordem']],
        'setor_servicos' => ['setor_servico', ['id' => 'serv_id', 'setor_id' => 'seto_id', 'nome' => 'serv_nome', 'ordem' => 'serv_ordem']],
        'setor_documentos' => ['setor_documento', ['id' => 'docu_id', 'setor_id' => 'seto_id', 'nome' => 'docu_nome', 'arquivo' => 'docu_arquivo', 'ordem' => 'docu_ordem']],
        'setor_horarios' => ['setor_horario', ['id' => 'hora_id', 'setor_id' => 'seto_id', 'dia_semana' => 'hora_dia_semana', 'abre' => 'hora_abre', 'fecha' => 'hora_fecha', 'observacao' => 'hora_observacao']],
        'sinonimos' => ['sinonimo', ['id' => 'sino_id', 'setor_id' => 'seto_id', 'nome' => 'sino_nome', 'ordem' => 'sino_ordem']],
        'andares' => ['andar', ['id' => 'anda_id', 'nome' => 'anda_nome', 'ordem' => 'anda_ordem']],
        'salas' => ['sala', ['id' => 'sala_id', 'nome' => 'sala_nome', 'nome_curto' => 'sala_nome_curto', 'funcao' => 'sala_funcao', 'andar_id' => 'anda_id', 'ala' => 'sala_ala', 'ordem' => 'sala_ordem', 'lado' => 'sala_lado', 'ponta' => 'sala_ponta', 'vazia' => 'sala_vazia', 'setor_id' => 'seto_id']],
        'faq' => ['faq', ['id' => 'faq_id', 'pergunta' => 'faq_pergunta', 'resposta' => 'faq_resposta', 'publicado' => 'faq_publicado', 'ordem' => 'faq_ordem', 'atualizado_em' => 'faq_atualizado_em']],
        'faq_setor' => ['faq_setor', ['faq_id' => 'faq_id', 'setor_id' => 'seto_id']],
        'fluxos' => ['fluxo', ['id' => 'flux_id', 'slug' => 'flux_slug', 'titulo' => 'flux_titulo', 'resumo' => 'flux_resumo', 'setor_id' => 'seto_id', 'canal' => 'flux_canal', 'prazo' => 'flux_prazo', 'publicado' => 'flux_publicado', 'ordem' => 'flux_ordem', 'atualizado_em' => 'flux_atualizado_em']],
        'fluxo_passos' => ['fluxo_passo', ['id' => 'fpasso_id', 'fluxo_id' => 'flux_id', 'nome' => 'fpasso_nome', 'ordem' => 'fpasso_ordem']],
        'fluxo_documentos' => ['fluxo_documento', ['id' => 'fdocu_id', 'fluxo_id' => 'flux_id', 'nome' => 'fdocu_nome', 'ordem' => 'fdocu_ordem']],
        'noticias' => ['noticia', ['id' => 'noti_id', 'slug' => 'noti_slug', 'titulo' => 'noti_titulo', 'resumo' => 'noti_resumo', 'conteudo' => 'noti_conteudo', 'categoria' => 'noti_categoria', 'publicada_em' => 'noti_publicada_em', 'destaque' => 'noti_destaque', 'publicado' => 'noti_publicado', 'ordem' => 'noti_ordem']],
        'avisos' => ['aviso', ['id' => 'avis_id', 'titulo' => 'avis_titulo', 'mensagem' => 'avis_mensagem', 'nivel' => 'avis_nivel', 'inicio' => 'avis_inicio', 'fim' => 'avis_fim', 'publicado' => 'avis_publicado', 'ordem' => 'avis_ordem', 'criado_em' => 'avis_criado_em']],
        'links_uteis' => ['link_util', ['id' => 'link_id', 'titulo' => 'link_titulo', 'url' => 'link_url', 'publicado' => 'link_publicado', 'ordem' => 'link_ordem']],
        'textos_institucionais' => ['texto_institucional', ['id' => 'text_id', 'titulo' => 'text_titulo', 'conteudo' => 'text_conteudo', 'publicado' => 'text_publicado', 'ordem' => 'text_ordem']],
        'atalhos_home' => ['atalho_home', ['id' => 'atal_id', 'titulo' => 'atal_titulo', 'descricao' => 'atal_descricao', 'icone' => 'atal_icone', 'cor' => 'atal_cor', 'url' => 'atal_url', 'ordem' => 'atal_ordem']],
        'usuarios' => ['usuario', ['id' => 'usua_id', 'nome' => 'usua_nome', 'usuario' => 'usua_usuario', 'email' => 'usua_email', 'senha_hash' => 'usua_senha_hash', 'perfil_id' => 'perf_id', 'ativo' => 'usua_ativo', 'sessao_versao' => 'usua_sessao_versao', 'ultimo_login_em' => 'usua_ultimo_login_em', 'criado_em' => 'usua_criado_em']],
    ];
    $inserir = static function (string $tabela, array $dados) use ($pdo, $nomesNoBanco): void {
        [$tabelaBanco, $colunasBanco] = $nomesNoBanco[$tabela];
        $valores = [];
        foreach ($dados as $campo => $valor) {
            $valores[$colunasBanco[$campo]] = $valor;
        }
        $colunas = array_keys($valores);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $tabelaBanco,
            implode(', ', $colunas),
            implode(', ', array_map(static function ($coluna) { return ':' . $coluna; }, $colunas))
        );
        $pdo->prepare($sql)->execute($valores);
    };

    // "Não informado" vira NULL (o site mostra o aviso). "Não possui" é informação de verdade e fica.
    $texto = static function ($valor): ?string {
        $valor = trim((string) $valor);
        return ($valor === '' || $valor === 'Não informado') ? null : $valor;
    };

    // ---------- CATEGORIAS (grupos do organograma) ----------
    $grupos = [
        'Órgãos de direção e colegiados' => ['direcao-colegiados', 'building-2', 'slate'],
        'Coordenações ligadas à Direção-Geral' => ['coordenacoes-dg', 'briefcase', 'slate'],
        'Diretoria de Ensino (DE)' => ['ensino', 'graduation-cap', 'green'],
        'Pesquisa e Pós-Graduação' => ['pesquisa', 'flask-conical', 'blue'],
        'Extensão' => ['extensao', 'handshake', 'rose'],
        'Planejamento e Administração' => ['administracao', 'briefcase-business', 'slate'],
        'Setores de apoio aos estudantes' => ['apoio-estudante', 'heart-handshake', 'rose'],
        'Outros núcleos' => ['nucleos', 'users', 'green'],
    ];
    $ordem = 0;
    foreach ($grupos as $nome => [$id, $icone, $cor]) {
        $inserir('categorias', ['id' => $id, 'nome' => $nome, 'icone' => $icone, 'cor' => $cor, 'ordem' => ++$ordem]);
    }

    // ---------- SETORES ----------
    // Item do organograma que é o mesmo setor detalhado em $setoresCampus.
    $mesmoSetor = ['cra-academico' => 'cra', 'dape-ensino' => 'dape', 'cbib' => 'biblioteca'];
    // Setores detalhados que não aparecem no organograma: em qual grupo entram.
    $grupoDosAvulsos = ['enfermaria' => 'apoio-estudante', 'biotecnologia' => 'ensino'];

    $detalhados = [];
    foreach ($setoresCampus as $setor) {
        $detalhados[$setor['id']] = $setor;
    }

    $ordem = 0;
    $criados = [];
    foreach ($gruposCategorias as $nomeGrupo => $itens) {
        foreach ($itens as $item) {
            $idSetor = $mesmoSetor[$item['id']] ?? $item['id'];
            [$nome, $sigla] = separarSiglaDoTitulo($item['titulo']);
            $detalhe = $detalhados[$idSetor] ?? null;

            $inserir('setores', [
                'id' => $idSetor,
                'categoria_id' => $grupos[$nomeGrupo][0],
                // "CAED"/"NAPNE" no cadastro detalhado são só a sigla; aí o nome completo do organograma é melhor.
                'nome' => ($detalhe && $detalhe['nome'] !== $detalhe['sigla']) ? $detalhe['nome'] : $nome,
                'sigla' => $detalhe['sigla'] ?? $sigla,
                'icone' => $detalhe['icone'] ?? $item['icone'],
                'cor' => $item['cor'],
                'descricao' => $texto($detalhe['descricao'] ?? null),
                'descricao_curta' => primeiraMaiuscula($item['descricao']),
                'localizacao' => $texto($detalhe['localizacao'] ?? null),
                'bloco' => $texto($detalhe['bloco'] ?? null),
                'sala_texto' => $texto($detalhe['sala'] ?? null),
                'telefone' => $texto($detalhe['telefone'] ?? null),
                'email' => $texto($detalhe['email'] ?? null),
                'responsavel' => $texto($detalhe['responsavel'] ?? null),
                'video' => $detalhe['video'] ?? null,
                'horario_texto' => $texto($detalhe['horario'] ?? null),
                'ordem' => ++$ordem,
                'atualizado_por' => 'Importação inicial',
            ]);
            $criados[$idSetor] = true;
        }
    }

    foreach ($grupoDosAvulsos as $idSetor => $idCategoria) {
        $detalhe = $detalhados[$idSetor];
        $inserir('setores', [
            'id' => $idSetor,
            'categoria_id' => $idCategoria,
            'nome' => $detalhe['nome'],
            'sigla' => $detalhe['sigla'],
            'icone' => $detalhe['icone'],
            'cor' => $idCategoria === 'ensino' ? 'green' : 'rose',
            'descricao' => $texto($detalhe['descricao']),
            'descricao_curta' => $texto($detalhe['descricao']),
            'localizacao' => $texto($detalhe['localizacao']),
            'bloco' => $texto($detalhe['bloco']),
            'sala_texto' => $texto($detalhe['sala']),
            'telefone' => $texto($detalhe['telefone']),
            'email' => $texto($detalhe['email']),
            'responsavel' => $texto($detalhe['responsavel']),
            'video' => $detalhe['video'],
            'horario_texto' => $texto($detalhe['horario']),
            'ordem' => ++$ordem,
            'atualizado_por' => 'Importação inicial',
        ]);
        $criados[$idSetor] = true;
    }

    // Equipe, serviços e documentos dos setores detalhados.
    foreach ($detalhados as $idSetor => $setor) {
        foreach (array_values($setor['equipe']) as $posicao => $pessoa) {
            if ($pessoa === 'Não possui') {
                continue; // CRA: "Não possui" equipe — ver docs/PENDENCIAS.md
            }
            $inserir('setor_equipe', ['setor_id' => $idSetor, 'nome' => $pessoa, 'cargo' => null, 'ordem' => $posicao]);
        }
        foreach (array_values($setor['servicos']) as $posicao => $servico) {
            $inserir('setor_servicos', ['setor_id' => $idSetor, 'nome' => $servico, 'ordem' => $posicao]);
        }
        foreach (array_values($setor['documentos']) as $posicao => $documento) {
            $inserir('setor_documentos', ['setor_id' => $idSetor, 'nome' => $documento, 'ordem' => $posicao]);
        }
    }

    // ---------- HORÁRIOS: texto livre -> estruturado (B4) ----------
    // Só converte quando o texto diz os dias da semana. O que não deu fica como
    // observação (o site continua lendo o texto) e é listado no fim da instalação.
    require_once __DIR__ . '/../model/Horario.php';
    $GLOBALS['horariosNaoConvertidos'] = [];
    foreach ($pdo->query("SELECT seto_id AS id, seto_nome AS nome, seto_horario_texto AS horario_texto FROM setor WHERE COALESCE(seto_horario_texto, '') <> ''")->fetchAll() as $setor) {
        $faixas = converterHorarioTexto($setor['horario_texto']);
        if ($faixas === null) {
            $GLOBALS['horariosNaoConvertidos'][] = "{$setor['nome']}: \"{$setor['horario_texto']}\"";
            continue;
        }
        foreach ($faixas as $faixa) {
            $inserir('setor_horarios', ['setor_id' => $setor['id']] + $faixa);
        }
        $pdo->prepare('UPDATE setor SET seto_horario_texto = NULL WHERE seto_id = ?')->execute([$setor['id']]);
    }

    // ---------- SINÔNIMOS DA BUSCA (os do backlog; o resto o admin cadastra em Setores) ----------
    foreach ([['cra', 'declaração'], ['cra', 'histórico'], ['caed', 'auxílio']] as $posicao => [$idSetor, $termo]) {
        $inserir('sinonimos', ['setor_id' => $idSetor, 'nome' => $termo, 'ordem' => $posicao]);
    }

    // ---------- MAPA ----------
    $ordem = 0;
    foreach ($mapaSalas['andares'] as $idAndar => $andar) {
        $inserir('andares', ['id' => $idAndar, 'nome' => $andar['nome'], 'ordem' => ++$ordem]);
    }
    // No mapa a sala "Sala de Enfermagem" aponta pro setor 'enfermaria'; as outras já usam o mesmo id.
    foreach ($mapaSalas['salas'] as $sala) {
        $setorDaSala = $sala['setor_id'] ?? null;
        $inserir('salas', [
            'id' => $sala['id'],
            'nome' => $sala['nome'],
            'nome_curto' => $sala['nomeCurto'],
            'andar_id' => $sala['andar'],
            'ala' => $sala['ala'],
            'ordem' => $sala['ordem'],
            'lado' => $sala['lado'],
            'ponta' => $sala['ponta'] ? 1 : 0,
            'setor_id' => ($setorDaSala !== null && isset($criados[$setorDaSala])) ? $setorDaSala : null,
        ]);
    }

    // ---------- FAQ (as perguntas que existiam são todas sobre o CRA) ----------
    foreach (array_values($faqSetor) as $posicao => $item) {
        $inserir('faq', ['pergunta' => $item['pergunta'], 'resposta' => $item['resposta'], 'ordem' => $posicao]);
        $inserir('faq_setor', ['faq_id' => (int) $pdo->lastInsertId(), 'setor_id' => 'cra']);
    }

    // ---------- "COMO FAÇO PARA..." (N11) ----------
    // Só os 3 fluxos que o FAQ do CRA já explica, usando o mesmo texto do FAQ.
    // Renovação, auxílio, histórico, segunda chamada e estágio: ver docs/PENDENCIAS.md.
    $fluxos = [
        [
            'titulo' => 'Pedir declaração de matrícula', 'canal' => 'ambos', 'prazo' => 'Até 3 dias úteis',
            'resumo' => 'Você pode pedir pelo SUAP ou no CRA.',
            'passos' => ['Peça a declaração pelo SUAP ou vá pessoalmente ao CRA.', 'Se for ao CRA, leve um documento de identificação.', 'Aguarde a emissão: o prazo é de até 3 dias úteis.'],
            'documentos' => ['Documento de identificação (só se for pedir no CRA)'],
        ],
        [
            'titulo' => 'Fazer a matrícula', 'canal' => null, 'prazo' => null,
            'resumo' => 'Documentos que você precisa apresentar no CRA, com originais e cópias.',
            'passos' => ['Separe os documentos da lista, com os originais e as cópias.', 'Apresente os documentos no CRA.'],
            'documentos' => ['RG', 'CPF', 'Comprovante de residência', 'Histórico escolar', 'Certificado de conclusão', '2 fotos 3x4'],
        ],
        [
            'titulo' => 'Trancar a matrícula', 'canal' => 'ambos', 'prazo' => 'Análise em até 5 dias úteis',
            'resumo' => 'O pedido é feito pelo SUAP ou no CRA, com justificativa.',
            'passos' => ['Faça o pedido pelo SUAP ou pessoalmente no CRA.', 'Escreva a justificativa do trancamento.', 'A coordenação do curso analisa o pedido em até 5 dias úteis.'],
            'documentos' => [],
        ],
    ];
    foreach ($fluxos as $posicao => $fluxo) {
        $inserir('fluxos', [
            'slug' => criarSlugTexto($fluxo['titulo']), 'titulo' => $fluxo['titulo'], 'resumo' => $fluxo['resumo'],
            'setor_id' => 'cra', 'canal' => $fluxo['canal'], 'prazo' => $fluxo['prazo'], 'ordem' => $posicao,
        ]);
        $idFluxo = (int) $pdo->lastInsertId();
        foreach ($fluxo['passos'] as $ordem => $passo) {
            $inserir('fluxo_passos', ['fluxo_id' => $idFluxo, 'nome' => $passo, 'ordem' => $ordem]);
        }
        foreach ($fluxo['documentos'] as $ordem => $documento) {
            $inserir('fluxo_documentos', ['fluxo_id' => $idFluxo, 'nome' => $documento, 'ordem' => $ordem]);
        }
    }

    // ---------- TRADUÇÕES DA INTERFACE (I6) ----------
    // O espanhol que já existia no protótipo entra publicado (pra o site não mudar),
    // marcado pra revisão (ver docs/PENDENCIAS.md). Só cria se ainda não existe.
    $espanhol = [
        'nav_inicio' => 'Inicio', 'nav_categorias' => 'Categorías', 'nav_mapa' => 'Mapa', 'nav_sobre' => 'Acerca de',
        'nav_contato' => 'Contacto', 'busca_placeholder' => 'Buscar sector...', 'btn_fale_conosco' => 'Contáctenos',
        'rodape_navegacao' => 'Navegación', 'rodape_direitos' => 'Todos los derechos reservados.', 'rodape_acessibilidade' => 'Accesibilidad',
    ];
    $comando = $pdo->prepare("INSERT IGNORE INTO traducao (trad_entidade, trad_entidade_id, trad_campo, trad_idioma, trad_texto, trad_status, trad_validado_por) VALUES ('interface', '', ?, 'es', ?, 'publicado', 'Protótipo (revisar)')");
    foreach ($espanhol as $chave => $texto) {
        $comando->execute([$chave, $texto]);
    }

    // ---------- CONTEÚDO DO PROTÓTIPO ----------
    // Vieram do protótipo (home e admin). Ver docs/PENDENCIAS.md: confirmar ou apagar pelo admin.
    $meses = ['jan' => 1, 'fev' => 2, 'mar' => 3, 'abr' => 4, 'mai' => 5, 'jun' => 6, 'jul' => 7, 'ago' => 8, 'set' => 9, 'out' => 10, 'nov' => 11, 'dez' => 12];
    foreach (array_values($noticias) as $posicao => $noticia) {
        [$dia, $mes, $ano] = explode(' ', $noticia['data']);
        $inserir('noticias', [
            'slug' => criarSlug($noticia['titulo']),
            'titulo' => $noticia['titulo'],
            'resumo' => null,
            'conteudo' => null,
            'categoria' => $noticia['categoria'],
            'publicada_em' => sprintf('%04d-%02d-%02d', $ano, $meses[$mes], $dia),
            'destaque' => $noticia['urgente'] ? 1 : 0,
            'ordem' => $posicao,
        ]);
    }

    $avisos = [
        ['Manutenção programada no SUAP', 'O sistema ficará indisponível no sábado, das 8h às 12h.', 'urgente'],
        ['Semana de provas finais', 'As provas finais ocorrem entre os dias 10 e 14 de agosto.', 'normal'],
    ];
    foreach ($avisos as $posicao => [$titulo, $mensagem, $nivel]) {
        // publicado = 0: eram exemplos do protótipo, sem data. Só aparecem se alguém publicar no admin.
        $inserir('avisos', ['titulo' => $titulo, 'mensagem' => $mensagem, 'nivel' => $nivel, 'publicado' => 0, 'ordem' => $posicao]);
    }

    $links = [['Portal do IFRO', 'https://ifro.edu.br'], ['SUAP', 'https://suap.ifro.edu.br']];
    foreach ($links as $posicao => [$titulo, $url]) {
        $inserir('links_uteis', ['titulo' => $titulo, 'url' => $url, 'ordem' => $posicao]);
    }

    $institucional = [
        ['Missão', 'Promover educação profissional, científica e tecnológica por meio do ensino, pesquisa e extensão, com foco na formação integral do cidadão e no desenvolvimento sustentável.'],
        ['Visão', 'Ser referência em educação profissional e tecnológica na Amazônia, reconhecida pela qualidade, inovação e compromisso com o desenvolvimento regional.'],
        ['Valores', 'Ética, transparência, inclusão social, excelência no ensino, respeito à diversidade e compromisso com o desenvolvimento sustentável da região.'],
    ];
    foreach ($institucional as $posicao => [$titulo, $conteudo]) {
        $inserir('textos_institucionais', ['titulo' => $titulo, 'conteudo' => $conteudo, 'ordem' => $posicao]);
    }

    foreach (array_values($atalhosRapidos) as $posicao => $atalho) {
        $inserir('atalhos_home', [
            'titulo' => $atalho['titulo'], 'descricao' => $atalho['descricao'], 'icone' => $atalho['icone'],
            'cor' => $atalho['cor'], 'url' => $atalho['url'], 'ordem' => $posicao,
        ]);
    }

    // ---------- CONFIGURAÇÕES (só cria se ainda não existir: não apaga o que o admin mudou) ----------
    $configuracoes = [
        'contato_telefone' => '(69) 99985-4314',
        'contato_email' => 'guajara-mirim@ifro.edu.br',
        'contato_endereco' => 'Av. 15 de Novembro, s/n - Planalto, Guajará-Mirim - RO, 76850-000',
        'contato_horario' => '', // TODO: confirmar (três versões diferentes no site — ver docs/PENDENCIAS.md)
        'rede_facebook' => 'https://www.facebook.com/ifrondonia',
        'rede_instagram' => 'https://www.instagram.com/ifro.guajara/',
        'rede_youtube' => 'https://www.youtube.com/c/ifrocampusguajaramirim',
        'whatsapp_canal' => '', // TODO: link do canal de WhatsApp do campus
        'contato_email_destino' => '', // e-mail que recebe o aviso do formulário de contato (vazio = só grava no banco)
        'telefone_enfermaria' => '', // TODO (W9)
        'telefone_portaria' => '', // TODO (W9)
        'velocidade_caminhada' => '1.2', // metros por segundo (N14)
    ];
    $comando = $pdo->prepare('INSERT IGNORE INTO configuracao (conf_chave, conf_valor) VALUES (?, ?)');
    foreach ($configuracoes as $chave => $valor) {
        $comando->execute([$chave, $valor]);
    }

    // ---------- PERFIS E PRIMEIRO ADMIN ----------
    $pdo->exec("INSERT IGNORE INTO perfil (perf_id, perf_nome) VALUES ('admin_geral', 'Administrador geral'), ('responsavel_setor', 'Responsável de setor')");
    $jaTemAdmin = (int) $pdo->query("SELECT COUNT(*) FROM usuario WHERE perf_id = 'admin_geral'")->fetchColumn();
    if ($jaTemAdmin === 0 && !empty($segredosAdmin['admin_usuario']) && !empty($segredosAdmin['admin_senha_hash'])) {
        $inserir('usuarios', [
            'nome' => 'Administrador',
            'usuario' => $segredosAdmin['admin_usuario'],
            'email' => null,
            'senha_hash' => $segredosAdmin['admin_senha_hash'],
            'perfil_id' => 'admin_geral',
        ]);
    }
}

/** "CCOM – Comunicação e Eventos" -> ['Comunicação e Eventos', 'CCOM']; "Direção-Geral (DG)" -> ['Direção-Geral', 'DG']. */
function separarSiglaDoTitulo(string $titulo): array
{
    if (preg_match('/^([A-Za-zÇ]{2,})\s+–\s+(.+)$/u', $titulo, $partes)) {
        return [$partes[2], $partes[1]];
    }
    if (preg_match('/^(.+?)\s+\(([A-Z]{2,})\)$/u', $titulo, $partes)) {
        return [$partes[1], $partes[2]];
    }
    return [$titulo, ''];
}

function primeiraMaiuscula(string $texto): string
{
    return mb_strtoupper(mb_substr($texto, 0, 1)) . mb_substr($texto, 1);
}

/** "Inscrições abertas 2026" -> "inscricoes-abertas-2026" */
function criarSlug(string $texto): string
{
    return criarSlugTexto($texto);
}
