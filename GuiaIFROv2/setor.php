<?php
$tituloPagina = 'Detalhes do Setor';
$paginaAtual = 'categorias';
require __DIR__ . '/componentes/cabecalho.php';

/**
 * O identificador chega pela URL: setor.php?id=cra
 * A validação abaixo impede o uso direto de valores desconhecidos.
 */
$idSolicitado = isset($_GET['id']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $_GET['id']) : 'cra';
$categoriaSelecionada = null;

foreach ($categorias as $categoria) {
    if ($categoria['id'] === $idSolicitado) {
        $categoriaSelecionada = $categoria;
        break;
    }
}

if ($categoriaSelecionada === null) {
    foreach ($categorias as $categoria) {
        if ($categoria['id'] === 'cra') {
            $categoriaSelecionada = $categoria;
            break;
        }
    }
}

$nomeSetor = $categoriaSelecionada['id'] === 'cra'
    ? 'Coordenação de Registro Acadêmico'
    : $categoriaSelecionada['titulo'];

$siglaSetor = $categoriaSelecionada['id'] === 'cra'
    ? 'CRA'
    : strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $categoriaSelecionada['titulo']), 0, 4));

$descricaoSetor = $categoriaSelecionada['id'] === 'cra'
    ? 'Responsável por processos de matrícula, documentação acadêmica e registros escolares.'
    : $categoriaSelecionada['descricao'];

$equipe = [
    ['iniciais' => 'MS', 'nome' => 'Maria Silva Santos', 'cargo' => 'Coordenadora'],
    ['iniciais' => 'JP', 'nome' => 'João Pedro Costa', 'cargo' => 'Assistente Administrativo'],
    ['iniciais' => 'AC', 'nome' => 'Ana Carolina Lima', 'cargo' => 'Técnica em Assuntos Educacionais'],
];

$servicos = [
    'Matrícula de novos alunos', 'Emissão de declarações e atestados',
    'Solicitação de histórico escolar', 'Trancamento e cancelamento de matrícula',
    'Transferência entre cursos', 'Aproveitamento de estudos',
    'Revisão de notas e faltas', 'Certificados e diplomas',
];

$documentos = [
    ['nome' => 'Manual do Estudante 2026', 'tamanho' => '2.4 MB'],
    ['nome' => 'Calendário Acadêmico 2026/2', 'tamanho' => '1.1 MB'],
    ['nome' => 'Formulário de Matrícula', 'tamanho' => '850 KB'],
    ['nome' => 'Regulamento Acadêmico', 'tamanho' => '3.2 MB'],
];
?>

<section class="page-header light-header sector-header">
    <div class="container reveal">
        <nav class="breadcrumb">
            <a href="index.php"><?= icone('arrow-left') ?> Início</a>
            <?= icone('chevron-right') ?>
            <a href="categorias.php">Categorias</a>
            <?= icone('chevron-right') ?>
            <strong><?= htmlspecialchars($siglaSetor) ?></strong>
        </nav>

        <div class="sector-title-row">
            <span class="sector-main-icon"><?= icone($categoriaSelecionada['icone']) ?></span>
            <div>
                <div class="sector-name-line">
                    <h1><?= htmlspecialchars($nomeSetor) ?></h1>
                    <span><?= htmlspecialchars($siglaSetor) ?></span>
                </div>
                <p><?= htmlspecialchars($descricaoSetor) ?></p>
            </div>
        </div>
    </div>
</section>

<section class="section container">
    <!-- Informações principais do setor -->
    <div class="info-chip-grid">
        <?php
        $informacoes = [
            ['phone', 'Telefone', '(69) 3541-5287'],
            ['mail', 'E-mail', 'cra.gm@ifro.edu.br'],
            ['clock', 'Horário', 'Seg–Sex, 7h às 17h'],
            ['map-pin', 'Localização', 'Bloco Pedagógico'],
        ];
        ?>
        <?php foreach ($informacoes as $indice => [$iconeInfo, $rotuloInfo, $valorInfo]): ?>
            <article class="info-chip reveal" style="--delay: <?= $indice * 80 ?>ms">
                <span><?= icone($iconeInfo) ?></span>
                <div><small><?= $rotuloInfo ?></small><strong><?= $valorInfo ?></strong></div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="sector-layout">
        <div class="sector-main-column">
            <!-- Responsável principal -->
            <article class="content-card reveal">
                <h2>Responsável</h2>
                <div class="responsible-person">
                    <span class="avatar-large">MS</span>
                    <div>
                        <h3>Maria Silva Santos</h3>
                        <p>Coordenadora de Registro Acadêmico</p>
                        <div><a href="mailto:cra.gm@ifro.edu.br">cra.gm@ifro.edu.br</a><i>·</i><span>(69) 3541-5287</span></div>
                    </div>
                </div>
            </article>

            <!-- Equipe -->
            <article class="content-card reveal">
                <h2><?= icone('star') ?> Equipe</h2>
                <div class="team-list">
                    <?php foreach ($equipe as $membro): ?>
                        <div class="team-member">
                            <span class="avatar-small"><?= htmlspecialchars($membro['iniciais']) ?></span>
                            <div><strong><?= htmlspecialchars($membro['nome']) ?></strong><small><?= htmlspecialchars($membro['cargo']) ?></small></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <!-- FAQ em formato de acordeão controlado por JavaScript -->
            <article class="content-card reveal">
                <h2>Perguntas Frequentes</h2>
                <div class="accordion" data-accordion>
                    <?php foreach ($faqSetor as $indice => $item): ?>
                        <div class="accordion-item <?= $indice === 0 ? 'open' : '' ?>">
                            <button type="button" class="accordion-trigger" aria-expanded="<?= $indice === 0 ? 'true' : 'false' ?>">
                                <span><?= htmlspecialchars($item['pergunta']) ?></span>
                                <?= icone('chevron-down') ?>
                            </button>
                            <div class="accordion-content">
                                <p><?= htmlspecialchars($item['resposta']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        </div>

        <aside class="sector-sidebar">
            <article class="content-card sticky-card reveal">
                <h2><?= icone('file-text') ?> Serviços Oferecidos</h2>
                <ul class="check-list">
                    <?php foreach ($servicos as $servico): ?>
                        <li><?= icone('circle-check-big') ?><span><?= htmlspecialchars($servico) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </article>

            <article class="content-card reveal">
                <h2>Documentos Úteis</h2>
                <div class="document-list">
                    <?php foreach ($documentos as $documento): ?>
                        <button type="button" class="document-item">
                            <span><strong><?= htmlspecialchars($documento['nome']) ?></strong><small>PDF · <?= htmlspecialchars($documento['tamanho']) ?></small></span>
                            <?= icone('download') ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="sidebar-green-card reveal">
                <h2>Dúvidas?</h2>
                <p>Entre em contato diretamente com nossa equipe.</p>
                <a href="contato.php" class="button button-white button-full">Fale conosco</a>
            </article>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/componentes/rodape.php'; ?>