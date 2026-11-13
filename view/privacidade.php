<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header compact-header">
    <div class="container reveal">
        <nav class="breadcrumb">
            <a href="index.php"><?= t('nav_inicio') ?></a>
            <?= icone('chevron-right') ?>
            <strong>Política de Privacidade</strong>
        </nav>
        <h1>Política de Privacidade</h1>
        <p>Como o Guia IFRO trata as informações que você compartilha ao usar o site.</p>
    </div>
</section>

<section class="section container">
    <div class="acessibilidade-secao reveal">
        <h2>Quais dados coletamos</h2>
        <p class="acessibilidade-descricao">
            No formulário de <a href="contato.php">contato</a> pedimos só o necessário para responder:
            nome, e-mail e a mensagem (telefone é opcional). Não pedimos CPF, endereço completo ou
            qualquer outro dado sensível.
        </p>
        <p class="acessibilidade-descricao">
            Para evitar abuso (robôs mandando mensagens), contamos quantos envios cada conexão fez na
            última hora. Para isso guardamos um <strong>código embaralhado</strong> do endereço de internet
            (IP) — não dá para descobrir o IP a partir dele — e apagamos em até 1 dia.
        </p>
        <p class="acessibilidade-descricao">
            Ficam <strong>só no seu aparelho</strong>: o tamanho de fonte, o modo escuro, seus setores
            favoritos e os visitados recentemente. O idioma escolhido fica na sua sessão enquanto você navega.
        </p>
    </div>

    <div class="acessibilidade-secao reveal">
        <h2>Estatísticas de uso</h2>
        <p class="acessibilidade-descricao">
            O Guia <strong>não registra</strong> as páginas que você abre, o que você busca nem as rotas
            que pede no mapa, e não usa ferramentas de análise de terceiros.
        </p>
    </div>

    <div class="acessibilidade-secao reveal">
        <h2>Para que usamos</h2>
        <p class="acessibilidade-descricao">
            Os dados do formulário de contato são usados exclusivamente para responder à sua
            mensagem. Não vendemos, alugamos ou compartilhamos essas informações com terceiros,
            e não usamos os dados para propaganda.
        </p>
    </div>

    <div class="acessibilidade-secao reveal">
        <h2>Seus direitos (LGPD)</h2>
        <p class="acessibilidade-descricao">
            Como previsto na Lei Geral de Proteção de Dados (Lei nº 13.709/2018), você pode
            solicitar a qualquer momento: confirmação de que tratamos seus dados, acesso a
            eles, correção de informações incompletas ou desatualizadas, e exclusão dos dados
            que você nos enviou. Basta entrar em contato pelos canais abaixo.
        </p>
    </div>

    <div class="acessibilidade-secao reveal">
        <h2>Cookies e ferramentas de terceiros</h2>
        <p class="acessibilidade-descricao">
            Usamos o <strong>VLibras</strong>, ferramenta gratuita do governo federal que traduz
            o conteúdo do site para Libras. Ao ser carregada, ela se conecta aos servidores do
            VLibras (vlibras.gov.br) para funcionar — não temos controle sobre esse
            processamento, que segue a política de privacidade do próprio VLibras.
        </p>
    </div>

    <div class="acessibilidade-secao reveal">
        <h2>Contato sobre privacidade</h2>
        <p class="acessibilidade-descricao">
            Dúvidas ou solicitações sobre seus dados podem ser enviadas para
            <strong>guajara-mirim@ifro.edu.br</strong> ou pelo telefone
            <strong>(69) 99985-4314</strong>.
        </p>
        <p class="acessibilidade-descricao" style="font-size: 0.85rem; color: var(--muted);">
            Última atualização: <?= ANO_ATUAL ?>.
        </p>
    </div>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
