<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb">
            <a href="index.php"><?= t('nav_inicio') ?></a>
            <?= icone('chevron-right') ?>
            <strong>Acessibilidade</strong>
        </nav>
        <h1>Acessibilidade</h1>
        <p>O Guia IFRO é feito para ser usado por qualquer pessoa da comunidade acadêmica. Ajuste o tamanho da fonte e escolha o idioma abaixo.</p>
    </div>
</section>

<section class="section container">
    <!-- TAMANHO DA FONTE -->
    <div class="acessibilidade-secao reveal">
        <span class="acessibilidade-kicker"><?= icone('type') ?> Leitura</span>
        <h2>Tamanho da fonte</h2>
        <p class="acessibilidade-descricao">Aumente o tamanho do texto em todo o site. A escolha fica salva no seu navegador — não precisa repetir a cada página.</p>

        <div class="fonte-cartoes">
            <button type="button" class="fonte-cartao" data-fonte="normal">
                <span class="fonte-exemplo" style="font-size: 1.1rem;">A</span>
                <strong>Normal</strong>
            </button>
            <button type="button" class="fonte-cartao" data-fonte="media">
                <span class="fonte-exemplo" style="font-size: 1.5rem;">A</span>
                <strong>Média</strong>
            </button>
            <button type="button" class="fonte-cartao" data-fonte="grande">
                <span class="fonte-exemplo" style="font-size: 1.9rem;">A</span>
                <strong>Grande</strong>
            </button>
        </div>

        <p class="acessibilidade-preview">
            Veja como fica: <em>"O Guia IFRO ajuda você a encontrar setores, salas e informações do campus."</em>
        </p>
    </div>

    <!-- TEMA -->
    <div class="acessibilidade-secao reveal">
        <span class="acessibilidade-kicker"><?= icone('moon') ?> Conforto visual</span>
        <h2>Tema</h2>
        <p class="acessibilidade-descricao">Alterne entre o tema claro e o escuro. A escolha fica salva no seu navegador.</p>
        <button type="button" class="acessibilidade-tema-botao alternar-tema" style="max-width: 220px;">
            <?= icone('moon') ?> <span>Modo escuro</span>
        </button>
    </div>

    <!-- CONFORTO VISUAL E MOVIMENTO (I1) -->
    <div class="acessibilidade-secao reveal">
        <span class="acessibilidade-kicker"><?= icone('eye') ?> Ver melhor</span>
        <h2>Contraste, fonte e movimento</h2>
        <p class="acessibilidade-descricao">Cada opção fica salva neste aparelho. Se você não escolher nada, o Guia segue o que o seu celular ou computador já pede (modo escuro, mais contraste, menos movimento).</p>
        <div class="preferencias-grade">
            <button type="button" class="preferencia-botao" data-preferencia="alto-contraste" aria-pressed="false">Alto contraste (fundo preto, letras brancas e amarelas)</button>
            <button type="button" class="preferencia-botao" data-preferencia="fonte-leitura" aria-pressed="false">Fonte de leitura fácil (Atkinson Hyperlegible)</button>
            <button type="button" class="preferencia-botao" data-preferencia="reduzir-movimento" aria-pressed="false">Menos movimento (sem animações e sem o vídeo de abertura)</button>
            <button type="button" class="preferencia-botao" data-preferencia="leitura-facil" aria-pressed="false">Leitura fácil (frases curtas, letra maior, botões grandes com desenhos)</button>
        </div>
    </div>

    <!-- IDIOMA -->
    <div class="acessibilidade-secao reveal" id="idioma">
        <span class="acessibilidade-kicker"><?= icone('languages') ?> Idioma</span>
        <h2>Idioma / Language / Idioma</h2>
        <p class="acessibilidade-descricao">Escolha o idioma de navegação do site. A tradução vale para o menu, botões e esta página.</p>

        <div class="idioma-cartoes">
            <?php foreach ($idiomasDisponiveis as $codigo => $info): ?>
                <?php $ativo = $codigo === idiomaAtual(); ?>
                <form method="post" action="acessibilidade.php" class="idioma-cartao-form">
                    <input type="hidden" name="idioma" value="<?= htmlspecialchars($codigo) ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenCsrf) ?>">
                    <button type="submit" class="idioma-cartao <?= $ativo ? 'ativo' : '' ?>">
                        <span class="idioma-bandeira" aria-hidden="true"><?= htmlspecialchars($info['bandeira'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php // O nome fica sempre no próprio idioma (quem não lê Oro Wari consegue voltar); com o Oro Wari ligado, a tradução aparece embaixo ?>
                        <strong data-sem-traducao><?= htmlspecialchars($info['nome']) ?></strong>
                        <?php $nomeEmWari = idiomaAtual() === 'wari' ? textoWari($info['nome'], 'acessibilidade') : null; ?>
                        <?php if ($nomeEmWari !== null): ?>
                            <small class="idioma-nome-traduzido" lang="pnv"><?= htmlspecialchars($nomeEmWari, ENT_QUOTES, 'UTF-8') ?></small>
                        <?php endif; ?>
                        <?php if ($ativo): ?>
                            <span class="idioma-selo"><?= icone('check') ?> Selecionado</span>
                        <?php endif; ?>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>

        <?php if (idiomaAtual() === 'wari'): ?>
            <div class="idioma-aviso">
                <?= icone('info') ?>
                <div>
                    <?php $progresso = progressoWari(); ?>
                    <strong>Sobre o Oro Wari (Pakaa Nova)</strong>
                    <p>
                        A tradução para o Oro Wari está em validação, feita junto com falantes da língua.
                        Por enquanto <?= (int) $progresso['traduzidos'] ?> de <?= (int) $progresso['total'] ?> textos do documento de tradução
                        já têm versão em Oro Wari; os outros continuam aparecendo em português até serem traduzidos e revisados.
                    </p>
                </div>
            </div>
        <?php elseif (!empty($_SESSION['aviso_wari'])): ?>
            <div class="idioma-aviso">
                <?= icone('info') ?>
                <div>
                    <strong>Sobre o Oro Wari (Pakaa Nova)</strong>
                    <p>
                        Ainda não temos uma tradução validada para o Oro Wari. Como é a língua de uma
                        comunidade indígena específica, o correto é traduzir o conteúdo em conjunto com
                        falantes nativos ou um linguista — para não publicar frases incorretas em nome
                        da língua. O site continua em português enquanto essa parceria não acontece.
                        Essa opção fica aqui para mostrar que o suporte a línguas indígenas é um objetivo
                        real do projeto, não só do português e espanhol.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
