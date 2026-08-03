/**
 * ÁREA ADMINISTRATIVA — GUIA IFRO
 * JavaScript puro. Sem backend por enquanto: autenticação, listagens e
 * formulários abaixo são simulados inteiramente no navegador.
 *
 * Cada bloco fica separado e comentado para facilitar a manutenção pelo
 * grupo e a futura integração com o backend.
 */

document.addEventListener('DOMContentLoaded', () => {
    atualizarIcones();
    verificarAutenticacaoAdmin();
    configurarLoginAdmin();
    configurarSidebarAdmin();
    configurarLogoutAdmin();
    configurarNomeAdminTopbar();
    configurarModaisAdmin();
    configurarExclusaoAdmin();
    configurarCategorias();
    configurarSetores();
    configurarInformacoes();
    configurarContatos();
    configurarConfiguracoes();
});

/** Recria os ícones Lucide após inserir novos elementos pelo JavaScript. */
function atualizarIcones() {
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

/** Evita que textos inseridos pelo JavaScript sejam interpretados como HTML. */
function escaparHtmlAdmin(valor) {
    const elemento = document.createElement('div');
    elemento.textContent = String(valor ?? '');
    return elemento.innerHTML;
}

function normalizarTextoAdmin(texto) {
    return String(texto)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

/* ==========================================================
   AUTENTICAÇÃO SIMULADA (SEM BACKEND)
   ========================================================== */
const CHAVE_SESSAO_ADMIN = 'ifroAdminLogado';
const CHAVE_NOME_ADMIN = 'ifroAdminNome';

/*
 * INTEGRAÇÃO FUTURA:
 * Usuário e senha temporários, usados apenas para demonstrar o layout do
 * painel. Isso deverá ser REMOVIDO assim que o backend de autenticação real
 * for implementado (validação no servidor, senha com hash, sessão PHP etc.).
 */
const USUARIO_TEMPORARIO = 'admin';
const SENHA_TEMPORARIA = 'admin123';

function estaAutenticadoAdmin() {
    return sessionStorage.getItem(CHAVE_SESSAO_ADMIN) === '1' || localStorage.getItem(CHAVE_SESSAO_ADMIN) === '1';
}

/** Em qualquer página do painel (exceto o login), redireciona quem não estiver "logado". */
function verificarAutenticacaoAdmin() {
    const body = document.body;
    if (!body.classList.contains('admin-body') || body.classList.contains('admin-login-page')) {
        return;
    }
    if (!estaAutenticadoAdmin()) {
        window.location.href = 'login.php';
    }
}

function configurarLoginAdmin() {
    const formulario = document.querySelector('#admin-login-form');
    if (!formulario) return;

    const campoUsuario = document.querySelector('#login-usuario');
    const campoSenha = document.querySelector('#login-senha');
    const campoLembrar = document.querySelector('#login-lembrar');
    const alertaErro = document.querySelector('#login-error');
    const textoErro = document.querySelector('#login-error-texto');
    const botaoToggleSenha = document.querySelector('#toggle-senha');
    const botaoSubmit = document.querySelector('#admin-login-submit');

    /* Se já estiver "autenticado" e cair aqui de novo, manda direto pro dashboard. */
    if (estaAutenticadoAdmin()) {
        window.location.href = 'dashboard.php';
        return;
    }

    botaoToggleSenha?.addEventListener('click', () => {
        const estaMostrando = botaoToggleSenha.getAttribute('aria-pressed') === 'true';
        botaoToggleSenha.setAttribute('aria-pressed', String(!estaMostrando));
        botaoToggleSenha.setAttribute('aria-label', estaMostrando ? 'Mostrar senha' : 'Ocultar senha');
        campoSenha.type = estaMostrando ? 'password' : 'text';
    });

    formulario.addEventListener('submit', (evento) => {
        evento.preventDefault();
        if (alertaErro) alertaErro.hidden = true;

        const usuario = campoUsuario.value.trim();
        const senha = campoSenha.value;
        const conteudoOriginalBotao = botaoSubmit.innerHTML;

        botaoSubmit.classList.add('loading');
        botaoSubmit.innerHTML = '<span class="loading-spinner" aria-hidden="true"></span><span>Entrando...</span>';

        /* Simula o tempo de uma requisição real. O backend substituirá este trecho. */
        window.setTimeout(() => {
            if (usuario === USUARIO_TEMPORARIO && senha === SENHA_TEMPORARIA) {
                sessionStorage.setItem(CHAVE_SESSAO_ADMIN, '1');
                if (campoLembrar?.checked) {
                    localStorage.setItem(CHAVE_SESSAO_ADMIN, '1');
                }
                window.location.href = 'dashboard.php';
                return;
            }

            botaoSubmit.classList.remove('loading');
            botaoSubmit.innerHTML = conteudoOriginalBotao;
            if (alertaErro && textoErro) {
                textoErro.textContent = 'Usuário ou senha inválidos. Tente novamente.';
                alertaErro.hidden = false;
            }
        }, 700);
    });
}

function configurarLogoutAdmin() {
    const sair = () => {
        sessionStorage.removeItem(CHAVE_SESSAO_ADMIN);
        localStorage.removeItem(CHAVE_SESSAO_ADMIN);
        window.location.href = 'login.php';
    };

    document.querySelector('#admin-logout-button')?.addEventListener('click', sair);
    document.querySelector('#admin-sidebar-logout-button')?.addEventListener('click', sair);
}

function configurarNomeAdminTopbar() {
    const nomeSalvo = localStorage.getItem(CHAVE_NOME_ADMIN);
    const elemento = document.querySelector('#admin-user-name');
    if (nomeSalvo && elemento) {
        elemento.textContent = nomeSalvo;
    }
}

/* ==========================================================
   MENU LATERAL (GAVETA NO CELULAR)
   ========================================================== */
function configurarSidebarAdmin() {
    const sidebar = document.querySelector('#admin-sidebar');
    const overlay = document.querySelector('#admin-overlay');
    const botaoMenu = document.querySelector('#admin-menu-button');

    if (!sidebar || !botaoMenu) return;

    const abrirGaveta = () => {
        sidebar.classList.add('open');
        overlay?.classList.add('visivel');
        botaoMenu.setAttribute('aria-expanded', 'true');
        document.body.classList.add('menu-open');
    };

    const fecharGaveta = () => {
        sidebar.classList.remove('open');
        overlay?.classList.remove('visivel');
        botaoMenu.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
    };

    botaoMenu.addEventListener('click', () => {
        sidebar.classList.contains('open') ? fecharGaveta() : abrirGaveta();
    });

    overlay?.addEventListener('click', fecharGaveta);

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', fecharGaveta);
    });
}

/* ==========================================================
   MODAIS / DRAWERS GENÉRICOS
   ========================================================== */
function abrirModalAdmin(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
}

function fecharModalAdmin(modal) {
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
}

function limparErrosFormulario(formulario) {
    formulario?.querySelectorAll('.admin-field-invalido').forEach((campo) => {
        campo.classList.remove('admin-field-invalido');
    });
}

/** Marca visualmente os campos obrigatórios não preenchidos de um formulário. */
function validarFormularioAdmin(formulario) {
    limparErrosFormulario(formulario);
    let valido = true;

    formulario.querySelectorAll('[required]').forEach((campo) => {
        const wrapper = campo.closest('.admin-field') || campo.closest('.admin-input-with-icon')?.closest('.admin-field');
        const preenchido = campo.type === 'checkbox' ? true : String(campo.value).trim().length > 0;

        if (!preenchido) {
            valido = false;
            wrapper?.classList.add('admin-field-invalido');
        }
    });

    return valido;
}

function configurarModaisAdmin() {
    /* Fecha ao clicar no fundo escurecido, no "x" ou em qualquer botão "Cancelar". */
    document.addEventListener('click', (evento) => {
        const gatilhoFechar = evento.target.closest('[data-fechar-modal]');
        if (gatilhoFechar) {
            const modal = gatilhoFechar.closest('.admin-modal');
            fecharModalAdmin(modal);
        }
    });

    /* Fecha o modal aberto ao pressionar Esc. */
    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            document.querySelectorAll('.admin-modal.open').forEach(fecharModalAdmin);
        }
    });
}

/* ==========================================================
   CONFIRMAÇÃO DE EXCLUSÃO (COMPARTILHADA POR TODAS AS LISTAS)
   ========================================================== */
let callbackExclusaoAdminAtual = null;

const RESUMO_EXCLUSAO_POR_TIPO = {
    categoria: { artigo: 'a categoria', rotulo: 'Categoria' },
    setor: { artigo: 'o setor', rotulo: 'Setor' },
    'contato-setor': { artigo: 'o contato de', rotulo: 'Contato' },
    informacao: { artigo: 'o item', rotulo: 'Item' },
};

function configurarExclusaoAdmin() {
    const modal = document.querySelector('#modal-confirmar-exclusao');
    const textoModal = document.querySelector('#modal-confirmar-exclusao-texto');
    const botaoConfirmar = document.querySelector('#botao-confirmar-exclusao');
    if (!modal) return;

    document.addEventListener('click', (evento) => {
        const botao = evento.target.closest('.admin-abrir-exclusao, .admin-excluir-item-informacao');
        if (!botao) return;

        const linha = botao.closest('[data-linha-categoria], [data-linha-setor], [data-linha-contato-setor], .admin-item-informacao');
        if (!linha) return;

        const tipo = botao.dataset.tipoExclusao || (linha.classList.contains('admin-item-informacao') ? 'informacao' : 'item');
        const resumo = RESUMO_EXCLUSAO_POR_TIPO[tipo] || { artigo: 'o item', rotulo: 'Item' };
        const nome = linha.dataset.nome || linha.dataset.setor || linha.querySelector('strong')?.textContent || 'selecionado';

        if (textoModal) {
            textoModal.textContent = `Tem certeza que deseja excluir ${resumo.artigo} "${nome}"? Essa ação não poderá ser desfeita.`;
        }

        callbackExclusaoAdminAtual = () => {
            linha.remove();
            mostrarToastAdmin(`${resumo.rotulo} excluído com sucesso.`);
        };

        abrirModalAdmin('modal-confirmar-exclusao');
    });

    botaoConfirmar?.addEventListener('click', () => {
        callbackExclusaoAdminAtual?.();
        callbackExclusaoAdminAtual = null;
        fecharModalAdmin(modal);
    });
}

/* ==========================================================
   TOASTS DE FEEDBACK
   ========================================================== */
function mostrarToastAdmin(mensagem, tipo = 'sucesso') {
    const container = document.querySelector('#admin-toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'admin-toast' + (tipo === 'erro' ? ' admin-toast-erro' : '');
    toast.innerHTML = `<i data-lucide="${tipo === 'erro' ? 'circle-alert' : 'check-circle-2'}" class="icon"></i><span>${escaparHtmlAdmin(mensagem)}</span>`;
    container.appendChild(toast);
    atualizarIcones();

    window.setTimeout(() => {
        toast.style.transition = 'opacity .25s ease, transform .25s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(8px)';
        window.setTimeout(() => toast.remove(), 250);
    }, 3200);
}

/** Simula o tempo de uma requisição, alternando o botão para o estado de carregamento. */
function simularEnvioFormulario(botao, aoConcluir) {
    if (!botao) {
        aoConcluir();
        return;
    }
    const conteudoOriginal = botao.innerHTML;
    botao.classList.add('loading');
    botao.innerHTML = '<span class="loading-spinner" aria-hidden="true"></span><span>Salvando...</span>';

    window.setTimeout(() => {
        botao.classList.remove('loading');
        botao.innerHTML = conteudoOriginal;
        aoConcluir();
    }, 600);
}

function dataAtualFormatadaAdmin() {
    const agora = new Date();
    const dois = (numero) => String(numero).padStart(2, '0');
    return `${dois(agora.getDate())}/${dois(agora.getMonth() + 1)}/${agora.getFullYear()}`;
}

let contadorIdSimuladoAdmin = 1000;
function proximoIdSimuladoAdmin() {
    contadorIdSimuladoAdmin += 1;
    return contadorIdSimuladoAdmin;
}

/* ==========================================================
   CATEGORIAS
   ========================================================== */
function configurarCategorias() {
    const tabela = document.querySelector('#corpo-tabela-categorias');
    if (!tabela) return; // Só executa na página categorias.php

    const busca = document.querySelector('#busca-categorias');
    const chipsFiltro = document.querySelectorAll('[data-filtro-status]');
    const contador = document.querySelector('#contador-categorias');
    const estadoVazio = document.querySelector('#estado-vazio-categorias');
    const modal = document.querySelector('#modal-categoria');
    const form = document.querySelector('#form-categoria');
    const tituloModal = document.querySelector('#modal-categoria-titulo');
    const campoIconePreview = document.querySelector('#categoria-icone-preview');

    let filtroStatusAtivo = 'todos';

    function aplicarFiltrosCategorias() {
        const termo = normalizarTextoAdmin(busca?.value || '');
        let visiveis = 0;

        tabela.querySelectorAll('[data-linha-categoria]').forEach((linha) => {
            const textoCompleto = normalizarTextoAdmin(`${linha.dataset.nome} ${linha.dataset.descricao}`);
            const combinaTexto = !termo || textoCompleto.includes(termo);
            const combinaStatus = filtroStatusAtivo === 'todos' || linha.dataset.status === filtroStatusAtivo;
            const deveMostrar = combinaTexto && combinaStatus;

            linha.hidden = !deveMostrar;
            if (deveMostrar) visiveis += 1;
        });

        if (contador) {
            contador.textContent = `${visiveis} ${visiveis === 1 ? 'categoria encontrada' : 'categorias encontradas'}`;
        }
        if (estadoVazio) estadoVazio.hidden = visiveis !== 0;
    }

    busca?.addEventListener('input', aplicarFiltrosCategorias);

    chipsFiltro.forEach((chip) => {
        chip.addEventListener('click', () => {
            filtroStatusAtivo = chip.dataset.filtroStatus;
            chipsFiltro.forEach((item) => item.classList.toggle('active', item === chip));
            aplicarFiltrosCategorias();
        });
    });

    /* Alterna ativo/inativo diretamente na tabela. */
    tabela.addEventListener('click', (evento) => {
        const botaoStatus = evento.target.closest('[data-alternar-status]');
        if (!botaoStatus) return;

        const linha = botaoStatus.closest('[data-linha-categoria]');
        const novoStatus = linha.dataset.status === 'ativo' ? 'inativo' : 'ativo';
        linha.dataset.status = novoStatus;

        botaoStatus.classList.toggle('admin-status-ativo', novoStatus === 'ativo');
        botaoStatus.classList.toggle('admin-status-inativo', novoStatus === 'inativo');
        botaoStatus.setAttribute('aria-pressed', String(novoStatus === 'ativo'));
        botaoStatus.querySelector('[data-texto-status]').textContent = novoStatus === 'ativo' ? 'Ativa' : 'Inativa';
        botaoStatus.innerHTML = botaoStatus.innerHTML.replace(/data-lucide="[^"]*"/, `data-lucide="${novoStatus === 'ativo' ? 'check-circle-2' : 'circle-slash'}"`);
        atualizarIcones();

        aplicarFiltrosCategorias();
    });

    /* Abre o formulário em modo "criar" ou "editar". */
    document.addEventListener('click', (evento) => {
        const gatilho = evento.target.closest('.admin-abrir-modal[data-modal-alvo="modal-categoria"]');
        if (!gatilho) return;

        limparErrosFormulario(form);
        const modo = gatilho.dataset.modo;
        form.dataset.modo = modo;

        if (modo === 'editar') {
            const linha = gatilho.closest('[data-linha-categoria]');
            form.dataset.idEdicao = linha.dataset.id;
            tituloModal.textContent = 'Editar categoria';
            form.querySelector('#categoria-nome').value = linha.dataset.nome;
            form.querySelector('#categoria-descricao').value = linha.dataset.descricao;
            form.querySelector('#categoria-icone').value = linha.dataset.icone;
            form.querySelector('#categoria-cor').value = linha.dataset.cor;
            form.querySelector('#categoria-status').checked = linha.dataset.status === 'ativo';
            campoIconePreview.innerHTML = `<i data-lucide="${linha.dataset.icone}" class="icon"></i>`;
        } else {
            delete form.dataset.idEdicao;
            tituloModal.textContent = 'Nova categoria';
            form.reset();
            form.querySelector('#categoria-status').checked = true;
            campoIconePreview.innerHTML = '<i data-lucide="shapes" class="icon"></i>';
        }

        document.querySelector('#form-categoria [data-texto-toggle-status]').textContent = form.querySelector('#categoria-status').checked ? 'Ativa' : 'Inativa';
        atualizarIcones();
        abrirModalAdmin('modal-categoria');
    });

    /* Pré-visualização do ícone digitado. */
    form.querySelector('#categoria-icone')?.addEventListener('input', (evento) => {
        campoIconePreview.innerHTML = `<i data-lucide="${evento.target.value.trim() || 'shapes'}" class="icon"></i>`;
        atualizarIcones();
    });

    form.querySelector('#categoria-status')?.addEventListener('change', (evento) => {
        form.querySelector('[data-texto-toggle-status]').textContent = evento.target.checked ? 'Ativa' : 'Inativa';
    });

    form.addEventListener('submit', (evento) => {
        evento.preventDefault();
        if (!validarFormularioAdmin(form)) return;

        const botaoSalvar = form.querySelector('.admin-botao-salvar');

        simularEnvioFormulario(botaoSalvar, () => {
            const dados = {
                nome: form.querySelector('#categoria-nome').value.trim(),
                descricao: form.querySelector('#categoria-descricao').value.trim(),
                icone: form.querySelector('#categoria-icone').value.trim() || 'shapes',
                cor: form.querySelector('#categoria-cor').value,
                status: form.querySelector('#categoria-status').checked ? 'ativo' : 'inativo',
            };

            if (form.dataset.idEdicao) {
                const linha = tabela.querySelector(`[data-linha-categoria][data-id="${form.dataset.idEdicao}"]`);
                atualizarLinhaCategoria(linha, dados);
                mostrarToastAdmin('Categoria atualizada com sucesso.');
            } else {
                const novaLinha = criarLinhaCategoria({ id: proximoIdSimuladoAdmin(), setores: 0, atualizadoEm: dataAtualFormatadaAdmin(), ...dados });
                tabela.prepend(novaLinha);
                atualizarIcones();
                mostrarToastAdmin('Categoria criada com sucesso.');
            }

            fecharModalAdmin(modal);
            aplicarFiltrosCategorias();
        });
    });

    function atualizarLinhaCategoria(linha, dados) {
        linha.dataset.nome = dados.nome;
        linha.dataset.descricao = dados.descricao;
        linha.dataset.icone = dados.icone;
        linha.dataset.cor = dados.cor;
        linha.dataset.status = dados.status;

        const celulaIcone = linha.querySelector('.admin-cell-icone');
        celulaIcone.className = `admin-cell-icone color-solid-${dados.cor}`;
        celulaIcone.innerHTML = `<i data-lucide="${dados.icone}" class="icon"></i>`;
        linha.querySelector('.admin-cell-categoria strong').textContent = dados.nome;
        linha.querySelector('.admin-cell-descricao').textContent = dados.descricao;

        const botaoStatus = linha.querySelector('[data-alternar-status]');
        botaoStatus.classList.toggle('admin-status-ativo', dados.status === 'ativo');
        botaoStatus.classList.toggle('admin-status-inativo', dados.status === 'inativo');
        botaoStatus.setAttribute('aria-pressed', String(dados.status === 'ativo'));
        botaoStatus.innerHTML = `<i data-lucide="${dados.status === 'ativo' ? 'check-circle-2' : 'circle-slash'}" class="icon"></i><span data-texto-status>${dados.status === 'ativo' ? 'Ativa' : 'Inativa'}</span>`;

        linha.querySelector('[data-rotulo="Atualizado em"]').textContent = dataAtualFormatadaAdmin();
        atualizarIcones();
    }

    function criarLinhaCategoria(dados) {
        const linha = document.createElement('tr');
        linha.dataset.linhaCategoria = '';
        linha.dataset.id = dados.id;
        linha.dataset.nome = dados.nome;
        linha.dataset.descricao = dados.descricao;
        linha.dataset.icone = dados.icone;
        linha.dataset.cor = dados.cor;
        linha.dataset.setores = dados.setores;
        linha.dataset.status = dados.status;

        linha.innerHTML = `
            <td data-rotulo="Categoria">
                <div class="admin-cell-categoria">
                    <span class="admin-cell-icone color-solid-${dados.cor}"><i data-lucide="${dados.icone}" class="icon"></i></span>
                    <strong>${escaparHtmlAdmin(dados.nome)}</strong>
                </div>
            </td>
            <td data-rotulo="Descrição" class="admin-cell-descricao">${escaparHtmlAdmin(dados.descricao)}</td>
            <td data-rotulo="Setores">${dados.setores}</td>
            <td data-rotulo="Status">
                <button type="button" class="admin-status-badge admin-status-${dados.status}" data-alternar-status aria-pressed="${dados.status === 'ativo'}">
                    <i data-lucide="${dados.status === 'ativo' ? 'check-circle-2' : 'circle-slash'}" class="icon"></i>
                    <span data-texto-status>${dados.status === 'ativo' ? 'Ativa' : 'Inativa'}</span>
                </button>
            </td>
            <td data-rotulo="Atualizado em">${dados.atualizadoEm}</td>
            <td data-rotulo="Ações" class="admin-table-acoes-col">
                <div class="admin-row-actions">
                    <button type="button" class="admin-icon-button admin-abrir-modal" data-modal-alvo="modal-categoria" data-modo="editar" aria-label="Editar categoria"><i data-lucide="pencil" class="icon"></i></button>
                    <button type="button" class="admin-icon-button admin-icon-button-danger admin-abrir-exclusao" data-tipo-exclusao="categoria" aria-label="Excluir categoria"><i data-lucide="trash-2" class="icon"></i></button>
                </div>
            </td>
        `;
        return linha;
    }

    aplicarFiltrosCategorias();
}

/* ==========================================================
   SETORES
   ========================================================== */
function configurarSetores() {
    const tabela = document.querySelector('#corpo-tabela-setores');
    if (!tabela) return; // Só executa na página setores.php

    const busca = document.querySelector('#busca-setores');
    const filtroCategoria = document.querySelector('#filtro-categoria-setores');
    const contador = document.querySelector('#contador-setores');
    const estadoVazio = document.querySelector('#estado-vazio-setores');

    const modal = document.querySelector('#modal-setor');
    const form = document.querySelector('#form-setor');
    const tituloModal = document.querySelector('#modal-setor-titulo');
    const modalDetalhes = document.querySelector('#modal-detalhes-setor');
    const conteudoDetalhes = document.querySelector('#conteudo-detalhes-setor');

    /* Mapa categoria -> rótulo, construído a partir do próprio <select> para não duplicar dados. */
    const rotuloCategorias = {};
    form.querySelector('#setor-categoria').querySelectorAll('option').forEach((opcao) => {
        rotuloCategorias[opcao.value] = opcao.textContent;
    });

    function aplicarFiltrosSetores() {
        const termo = normalizarTextoAdmin(busca?.value || '');
        const categoriaAtiva = filtroCategoria?.value || 'todos';
        let visiveis = 0;

        tabela.querySelectorAll('[data-linha-setor]').forEach((linha) => {
            const textoCompleto = normalizarTextoAdmin(`${linha.dataset.nome} ${linha.dataset.sigla} ${linha.dataset.descricao}`);
            const combinaTexto = !termo || textoCompleto.includes(termo);
            const combinaCategoria = categoriaAtiva === 'todos' || linha.dataset.categoria === categoriaAtiva;
            const deveMostrar = combinaTexto && combinaCategoria;

            linha.hidden = !deveMostrar;
            if (deveMostrar) visiveis += 1;
        });

        if (contador) {
            contador.textContent = `${visiveis} ${visiveis === 1 ? 'setor encontrado' : 'setores encontrados'}`;
        }
        if (estadoVazio) estadoVazio.hidden = visiveis !== 0;
    }

    busca?.addEventListener('input', aplicarFiltrosSetores);
    filtroCategoria?.addEventListener('change', aplicarFiltrosSetores);

    /* Alterna ativo/inativo diretamente na tabela. */
    tabela.addEventListener('click', (evento) => {
        const botaoStatus = evento.target.closest('[data-alternar-status]');
        if (!botaoStatus) return;

        const linha = botaoStatus.closest('[data-linha-setor]');
        const novoStatus = linha.dataset.status === 'ativo' ? 'inativo' : 'ativo';
        linha.dataset.status = novoStatus;

        botaoStatus.classList.toggle('admin-status-ativo', novoStatus === 'ativo');
        botaoStatus.classList.toggle('admin-status-inativo', novoStatus === 'inativo');
        botaoStatus.setAttribute('aria-pressed', String(novoStatus === 'ativo'));
        botaoStatus.innerHTML = `<i data-lucide="${novoStatus === 'ativo' ? 'check-circle-2' : 'circle-slash'}" class="icon"></i><span data-texto-status>${novoStatus === 'ativo' ? 'Ativo' : 'Inativo'}</span>`;
        atualizarIcones();

        aplicarFiltrosSetores();
    });

    /* Visualização somente leitura. */
    tabela.addEventListener('click', (evento) => {
        const botao = evento.target.closest('.admin-abrir-detalhes-setor');
        if (!botao) return;

        const linha = botao.closest('[data-linha-setor]');
        const servicos = linha.dataset.servicos ? linha.dataset.servicos.split('|').filter(Boolean) : [];
        const documentos = linha.dataset.documentos ? linha.dataset.documentos.split('|').filter(Boolean) : [];

        conteudoDetalhes.innerHTML = `
            <dt>Setor</dt><dd>${escaparHtmlAdmin(linha.dataset.nome)} (${escaparHtmlAdmin(linha.dataset.sigla)})</dd>
            <dt>Categoria</dt><dd>${escaparHtmlAdmin(rotuloCategorias[linha.dataset.categoria] || linha.dataset.categoria)}</dd>
            <dt>Descrição</dt><dd>${escaparHtmlAdmin(linha.dataset.descricao)}</dd>
            <dt>Localização</dt><dd>${escaparHtmlAdmin(linha.dataset.bloco)} · ${escaparHtmlAdmin(linha.dataset.sala)}</dd>
            <dt>Telefone</dt><dd>${escaparHtmlAdmin(linha.dataset.telefone)}</dd>
            <dt>E-mail</dt><dd>${escaparHtmlAdmin(linha.dataset.email)}</dd>
            <dt>Horário</dt><dd>${escaparHtmlAdmin(linha.dataset.horario)}</dd>
            <dt>Serviços oferecidos</dt><dd>${servicos.length ? escaparHtmlAdmin(servicos.join(', ')) : 'Nenhum cadastrado'}</dd>
            <dt>Documentos necessários</dt><dd>${documentos.length ? escaparHtmlAdmin(documentos.join(', ')) : 'Nenhum cadastrado'}</dd>
            <dt>Status</dt><dd>${linha.dataset.status === 'ativo' ? 'Ativo' : 'Inativo'}</dd>
        `;
        abrirModalAdmin('modal-detalhes-setor');
    });

    /* Listas dinâmicas: serviços e documentos. */
    function adicionarItemListaDinamica(idContainer, valor = '', placeholder = 'Item') {
        const lista = document.getElementById(idContainer);
        const item = document.createElement('li');
        item.innerHTML = `
            <input type="text" value="${escaparHtmlAdmin(valor)}" placeholder="${escaparHtmlAdmin(placeholder)}">
            <button type="button" class="admin-remover-item" aria-label="Remover item"><i data-lucide="x" class="icon"></i></button>
        `;
        lista.appendChild(item);
        atualizarIcones();
        if (!valor) item.querySelector('input').focus();
    }

    document.addEventListener('click', (evento) => {
        const botaoAdicionar = evento.target.closest('[data-adicionar-item]');
        if (botaoAdicionar) {
            adicionarItemListaDinamica(botaoAdicionar.dataset.adicionarItem, '', botaoAdicionar.dataset.placeholder);
            return;
        }
        const botaoRemover = evento.target.closest('.admin-remover-item');
        if (botaoRemover) {
            botaoRemover.closest('li').remove();
        }
    });

    function valoresDaListaDinamica(idContainer) {
        return Array.from(document.getElementById(idContainer).querySelectorAll('input'))
            .map((campo) => campo.value.trim())
            .filter(Boolean);
    }

    /* Abre o formulário em modo "criar" ou "editar". */
    document.addEventListener('click', (evento) => {
        const gatilho = evento.target.closest('.admin-abrir-modal[data-modal-alvo="modal-setor"]');
        if (!gatilho) return;

        limparErrosFormulario(form);
        const modo = gatilho.dataset.modo;
        form.dataset.modo = modo;

        document.getElementById('lista-servicos-setor').innerHTML = '';
        document.getElementById('lista-documentos-setor').innerHTML = '';

        if (modo === 'editar') {
            const linha = gatilho.closest('[data-linha-setor]');
            form.dataset.idEdicao = linha.dataset.id;
            tituloModal.textContent = 'Editar setor';

            form.querySelector('#setor-nome').value = linha.dataset.nome;
            form.querySelector('#setor-sigla').value = linha.dataset.sigla;
            form.querySelector('#setor-categoria').value = linha.dataset.categoria;
            form.querySelector('#setor-descricao').value = linha.dataset.descricao;
            form.querySelector('#setor-bloco').value = linha.dataset.bloco;
            form.querySelector('#setor-sala').value = linha.dataset.sala;
            form.querySelector('#setor-telefone').value = linha.dataset.telefone;
            form.querySelector('#setor-email').value = linha.dataset.email;
            form.querySelector('#setor-horario').value = linha.dataset.horario;
            form.querySelector('#setor-status').checked = linha.dataset.status === 'ativo';

            (linha.dataset.servicos ? linha.dataset.servicos.split('|').filter(Boolean) : []).forEach((valor) => {
                adicionarItemListaDinamica('lista-servicos-setor', valor, 'Nome do serviço');
            });
            (linha.dataset.documentos ? linha.dataset.documentos.split('|').filter(Boolean) : []).forEach((valor) => {
                adicionarItemListaDinamica('lista-documentos-setor', valor, 'Nome do documento');
            });
        } else {
            delete form.dataset.idEdicao;
            tituloModal.textContent = 'Novo setor';
            form.reset();
            form.querySelector('#setor-status').checked = true;
        }

        form.querySelector('[data-texto-toggle-status]').textContent = form.querySelector('#setor-status').checked ? 'Ativo' : 'Inativo';
        abrirModalAdmin('modal-setor');
    });

    form.querySelector('#setor-status')?.addEventListener('change', (evento) => {
        form.querySelector('[data-texto-toggle-status]').textContent = evento.target.checked ? 'Ativo' : 'Inativo';
    });

    form.addEventListener('submit', (evento) => {
        evento.preventDefault();
        if (!validarFormularioAdmin(form)) return;

        const botaoSalvar = form.querySelector('.admin-botao-salvar');

        simularEnvioFormulario(botaoSalvar, () => {
            const dados = {
                nome: form.querySelector('#setor-nome').value.trim(),
                sigla: form.querySelector('#setor-sigla').value.trim(),
                categoria: form.querySelector('#setor-categoria').value,
                descricao: form.querySelector('#setor-descricao').value.trim(),
                bloco: form.querySelector('#setor-bloco').value.trim(),
                sala: form.querySelector('#setor-sala').value.trim(),
                telefone: form.querySelector('#setor-telefone').value.trim(),
                email: form.querySelector('#setor-email').value.trim(),
                horario: form.querySelector('#setor-horario').value.trim(),
                status: form.querySelector('#setor-status').checked ? 'ativo' : 'inativo',
                servicos: valoresDaListaDinamica('lista-servicos-setor'),
                documentos: valoresDaListaDinamica('lista-documentos-setor'),
            };

            if (form.dataset.idEdicao) {
                const linha = tabela.querySelector(`[data-linha-setor][data-id="${form.dataset.idEdicao}"]`);
                atualizarLinhaSetor(linha, dados, rotuloCategorias);
                mostrarToastAdmin('Setor atualizado com sucesso.');
            } else {
                const novaLinha = criarLinhaSetor({ id: proximoIdSimuladoAdmin(), ...dados }, rotuloCategorias);
                tabela.prepend(novaLinha);
                mostrarToastAdmin('Setor criado com sucesso.');
            }

            fecharModalAdmin(modal);
            aplicarFiltrosSetores();
        });
    });

    function atualizarLinhaSetor(linha, dados, mapaRotulos) {
        Object.assign(linha.dataset, {
            nome: dados.nome, sigla: dados.sigla, categoria: dados.categoria, descricao: dados.descricao,
            bloco: dados.bloco, sala: dados.sala, telefone: dados.telefone, email: dados.email,
            horario: dados.horario, status: dados.status,
            servicos: dados.servicos.join('|'), documentos: dados.documentos.join('|'),
        });

        linha.querySelector('.admin-cell-categoria strong').textContent = dados.nome;
        linha.querySelector('.admin-cell-subtexto').textContent = dados.sigla;
        linha.querySelector('[data-rotulo="Categoria"]').textContent = mapaRotulos[dados.categoria] || dados.categoria;
        linha.querySelector('[data-rotulo="Bloco / Sala"]').textContent = `${dados.bloco} · ${dados.sala}`;
        linha.querySelector('[data-rotulo="Contato"]').textContent = dados.telefone;

        const botaoStatus = linha.querySelector('[data-alternar-status]');
        botaoStatus.classList.toggle('admin-status-ativo', dados.status === 'ativo');
        botaoStatus.classList.toggle('admin-status-inativo', dados.status === 'inativo');
        botaoStatus.setAttribute('aria-pressed', String(dados.status === 'ativo'));
        botaoStatus.innerHTML = `<i data-lucide="${dados.status === 'ativo' ? 'check-circle-2' : 'circle-slash'}" class="icon"></i><span data-texto-status>${dados.status === 'ativo' ? 'Ativo' : 'Inativo'}</span>`;
        atualizarIcones();
    }

    function criarLinhaSetor(dados, mapaRotulos) {
        const linha = document.createElement('tr');
        linha.dataset.linhaSetor = '';
        Object.assign(linha.dataset, {
            id: dados.id, nome: dados.nome, sigla: dados.sigla, categoria: dados.categoria, descricao: dados.descricao,
            bloco: dados.bloco, sala: dados.sala, telefone: dados.telefone, email: dados.email,
            horario: dados.horario, status: dados.status,
            servicos: dados.servicos.join('|'), documentos: dados.documentos.join('|'),
        });

        linha.innerHTML = `
            <td data-rotulo="Setor">
                <div class="admin-cell-categoria">
                    <strong>${escaparHtmlAdmin(dados.nome)}</strong>
                    <span class="admin-cell-subtexto">${escaparHtmlAdmin(dados.sigla)}</span>
                </div>
            </td>
            <td data-rotulo="Categoria">${escaparHtmlAdmin(mapaRotulos[dados.categoria] || dados.categoria)}</td>
            <td data-rotulo="Bloco / Sala">${escaparHtmlAdmin(dados.bloco)} · ${escaparHtmlAdmin(dados.sala)}</td>
            <td data-rotulo="Contato" class="admin-cell-descricao">${escaparHtmlAdmin(dados.telefone)}</td>
            <td data-rotulo="Status">
                <button type="button" class="admin-status-badge admin-status-${dados.status}" data-alternar-status aria-pressed="${dados.status === 'ativo'}">
                    <i data-lucide="${dados.status === 'ativo' ? 'check-circle-2' : 'circle-slash'}" class="icon"></i>
                    <span data-texto-status>${dados.status === 'ativo' ? 'Ativo' : 'Inativo'}</span>
                </button>
            </td>
            <td data-rotulo="Ações" class="admin-table-acoes-col">
                <div class="admin-row-actions">
                    <button type="button" class="admin-icon-button admin-abrir-detalhes-setor" aria-label="Visualizar detalhes do setor"><i data-lucide="eye" class="icon"></i></button>
                    <button type="button" class="admin-icon-button admin-abrir-modal" data-modal-alvo="modal-setor" data-modo="editar" aria-label="Editar setor"><i data-lucide="pencil" class="icon"></i></button>
                    <button type="button" class="admin-icon-button admin-icon-button-danger admin-abrir-exclusao" data-tipo-exclusao="setor" aria-label="Excluir setor"><i data-lucide="trash-2" class="icon"></i></button>
                </div>
            </td>
        `;
        atualizarIcones();
        return linha;
    }

    aplicarFiltrosSetores();
}

/* ==========================================================
   INFORMAÇÕES (AVISOS, NOTÍCIAS, CALENDÁRIO, HORÁRIOS, FAQ, LINKS, INSTITUCIONAL)
   ========================================================== */
const CONFIG_ABAS_INFORMACOES = {
    avisos: { rotulo: 'Aviso', destaque: true, campos: [
        { id: 'titulo', label: 'Título', tipo: 'text' },
        { id: 'mensagem', label: 'Mensagem', tipo: 'textarea' },
    ] },
    noticias: { rotulo: 'Notícia', destaque: true, campos: [
        { id: 'titulo', label: 'Título', tipo: 'text' },
        { id: 'data', label: 'Data', tipo: 'text' },
        { id: 'categoria', label: 'Categoria', tipo: 'text' },
    ] },
    calendario: { rotulo: 'Data do calendário', campos: [
        { id: 'evento', label: 'Evento', tipo: 'text' },
        { id: 'data', label: 'Data', tipo: 'text' },
        { id: 'periodo', label: 'Período letivo', tipo: 'text' },
    ] },
    horarios: { rotulo: 'Horário', campos: [
        { id: 'servico', label: 'Serviço / setor', tipo: 'text' },
        { id: 'dias', label: 'Dias', tipo: 'text' },
        { id: 'horario', label: 'Horário', tipo: 'text' },
    ] },
    faq: { rotulo: 'Pergunta', campos: [
        { id: 'pergunta', label: 'Pergunta', tipo: 'text' },
        { id: 'resposta', label: 'Resposta', tipo: 'textarea' },
    ] },
    links: { rotulo: 'Link', campos: [
        { id: 'titulo', label: 'Título do link', tipo: 'text' },
        { id: 'url', label: 'URL', tipo: 'text' },
    ] },
    institucional: { rotulo: 'Texto institucional', campos: [
        { id: 'titulo', label: 'Título', tipo: 'text' },
        { id: 'conteudo', label: 'Conteúdo', tipo: 'textarea' },
    ] },
};

function configurarInformacoes() {
    const tabsContainer = document.querySelector('.admin-tabs');
    if (!tabsContainer) return; // Só executa na página informacoes.php

    /* Dá um identificador único a cada item já existente na página. */
    let contadorItemInformacao = 0;
    document.querySelectorAll('.admin-item-informacao').forEach((item) => {
        contadorItemInformacao += 1;
        item.dataset.itemId = `item-${contadorItemInformacao}`;
    });

    /* Alternância das abas. */
    const botoesAba = document.querySelectorAll('.admin-tab-button');
    botoesAba.forEach((botao) => {
        botao.addEventListener('click', () => {
            const alvo = botao.dataset.abaAlvo;
            botoesAba.forEach((item) => {
                item.classList.toggle('active', item === botao);
                item.setAttribute('aria-selected', String(item === botao));
            });
            document.querySelectorAll('.admin-tab-painel').forEach((painel) => {
                painel.hidden = painel.dataset.abaPainel !== alvo;
            });
        });
    });

    const modal = document.querySelector('#modal-informacao');
    const form = document.querySelector('#form-informacao');
    const tituloModal = document.querySelector('#modal-informacao-titulo');
    const camposDinamicos = document.querySelector('#campos-informacao');

    function montarCampos(aba, valoresIniciais = {}) {
        const config = CONFIG_ABAS_INFORMACOES[aba];
        let html = '';

        config.campos.forEach((campo) => {
            const valor = escaparHtmlAdmin(valoresIniciais[campo.id] || '');
            html += `<div class="admin-field">
                <label for="campo-info-${campo.id}">${campo.label}</label>
                ${campo.tipo === 'textarea'
                    ? `<textarea id="campo-info-${campo.id}" data-campo-info="${campo.id}" rows="3" required>${valor}</textarea>`
                    : `<input type="text" id="campo-info-${campo.id}" data-campo-info="${campo.id}" required value="${valor}">`}
                <span class="admin-field-error">Este campo é obrigatório.</span>
            </div>`;
        });

        if (config.destaque) {
            const marcado = valoresIniciais.destaque ? 'checked' : '';
            html += `<label class="admin-checkbox">
                <input type="checkbox" id="campo-info-destaque" data-campo-info="destaque" ${marcado}>
                <span>Marcar como urgente</span>
            </label>`;
        }

        camposDinamicos.innerHTML = html;
    }

    /* Abrir para adicionar. */
    document.querySelectorAll('.admin-adicionar-informacao').forEach((botao) => {
        botao.addEventListener('click', () => {
            const aba = botao.dataset.aba;
            form.dataset.aba = aba;
            delete form.dataset.itemAlvo;
            tituloModal.textContent = `Adicionar ${CONFIG_ABAS_INFORMACOES[aba].rotulo.toLowerCase()}`;
            montarCampos(aba);
            abrirModalAdmin('modal-informacao');
        });
    });

    /* Abrir para editar. */
    document.addEventListener('click', (evento) => {
        const botaoEditar = evento.target.closest('.admin-editar-item-informacao');
        if (!botaoEditar) return;

        const item = botaoEditar.closest('.admin-item-informacao');
        const aba = item.closest('[data-lista-informacao]').dataset.listaInformacao;
        form.dataset.aba = aba;
        form.dataset.itemAlvo = item.dataset.itemId;

        tituloModal.textContent = `Editar ${CONFIG_ABAS_INFORMACOES[aba].rotulo.toLowerCase()}`;
        montarCampos(aba, item.dataset);
        abrirModalAdmin('modal-informacao');
    });

    /* Mover item para cima/baixo dentro da mesma lista. */
    document.addEventListener('click', (evento) => {
        const botaoMover = evento.target.closest('[data-mover]');
        if (!botaoMover) return;

        const item = botaoMover.closest('.admin-item-informacao');
        if (botaoMover.dataset.mover === 'cima' && item.previousElementSibling) {
            item.parentElement.insertBefore(item, item.previousElementSibling);
        } else if (botaoMover.dataset.mover === 'baixo' && item.nextElementSibling) {
            item.parentElement.insertBefore(item.nextElementSibling, item);
        }
    });

    /* Publicar / ocultar. */
    document.addEventListener('click', (evento) => {
        const botaoPublicar = evento.target.closest('[data-alternar-publicacao]');
        if (!botaoPublicar) return;

        const item = botaoPublicar.closest('.admin-item-informacao');
        const publicado = item.dataset.status !== 'publicado';
        item.dataset.status = publicado ? 'publicado' : 'oculto';

        botaoPublicar.classList.toggle('admin-status-ativo', publicado);
        botaoPublicar.classList.toggle('admin-status-inativo', !publicado);
        botaoPublicar.setAttribute('aria-pressed', String(publicado));
        botaoPublicar.innerHTML = `<i data-lucide="${publicado ? 'eye' : 'eye-off'}" class="icon"></i><span data-texto-status>${publicado ? 'Publicado' : 'Oculto'}</span>`;
        atualizarIcones();
    });

    form.addEventListener('submit', (evento) => {
        evento.preventDefault();
        if (!validarFormularioAdmin(form)) return;

        const aba = form.dataset.aba;
        const config = CONFIG_ABAS_INFORMACOES[aba];
        const botaoSalvar = form.querySelector('.admin-botao-salvar');

        simularEnvioFormulario(botaoSalvar, () => {
            const valores = {};
            config.campos.forEach((campo) => {
                valores[campo.id] = camposDinamicos.querySelector(`[data-campo-info="${campo.id}"]`).value.trim();
            });
            if (config.destaque) {
                valores.destaque = camposDinamicos.querySelector('[data-campo-info="destaque"]').checked;
            }

            const lista = document.querySelector(`[data-lista-informacao="${aba}"]`);

            if (form.dataset.itemAlvo) {
                const item = lista.querySelector(`[data-item-id="${form.dataset.itemAlvo}"]`);
                preencherItemInformacao(item, aba, valores);
                mostrarToastAdmin(`${config.rotulo} atualizado com sucesso.`);
            } else {
                contadorItemInformacao += 1;
                const item = document.createElement('li');
                item.className = 'admin-item-informacao';
                item.dataset.itemId = `item-${contadorItemInformacao}`;
                item.dataset.status = 'publicado';
                item.innerHTML = gerarMarcacaoItemInformacao();
                lista.prepend(item);
                preencherItemInformacao(item, aba, valores);
                atualizarIcones();
                mostrarToastAdmin(`${config.rotulo} adicionado com sucesso.`);
            }

            fecharModalAdmin(modal);
        });
    });

    function gerarMarcacaoItemInformacao() {
        return `
            <div class="admin-item-informacao-corpo"></div>
            <div class="admin-item-informacao-acoes">
                <button type="button" class="admin-icon-button" data-mover="cima" aria-label="Mover para cima"><i data-lucide="chevron-up" class="icon"></i></button>
                <button type="button" class="admin-icon-button" data-mover="baixo" aria-label="Mover para baixo"><i data-lucide="chevron-down" class="icon"></i></button>
                <button type="button" class="admin-status-badge admin-status-ativo" data-alternar-publicacao aria-pressed="true">
                    <i data-lucide="eye" class="icon"></i><span data-texto-status>Publicado</span>
                </button>
                <button type="button" class="admin-icon-button admin-editar-item-informacao" aria-label="Editar item"><i data-lucide="pencil" class="icon"></i></button>
                <button type="button" class="admin-icon-button admin-icon-button-danger admin-excluir-item-informacao" aria-label="Excluir item"><i data-lucide="trash-2" class="icon"></i></button>
            </div>
        `;
    }

    function preencherItemInformacao(item, aba, valores) {
        CONFIG_ABAS_INFORMACOES[aba].campos.forEach((campo) => {
            item.dataset[campo.id] = valores[campo.id];
        });
        if ('destaque' in valores) item.dataset.destaque = valores.destaque ? '1' : '0';

        const tituloCampo = valores.titulo || valores.evento || valores.servico || valores.pergunta || '';
        const linhas = CONFIG_ABAS_INFORMACOES[aba].campos.filter((campo) => !['titulo', 'evento', 'servico', 'pergunta'].includes(campo.id))
            .map((campo) => valores[campo.id])
            .filter(Boolean)
            .join(' · ');

        const badge = valores.destaque ? '<span class="admin-badge-destaque">Urgente</span>' : '';
        item.querySelector('.admin-item-informacao-corpo').innerHTML = `
            <strong>${escaparHtmlAdmin(tituloCampo)}</strong>${badge}
            <p>${escaparHtmlAdmin(linhas)}</p>
        `;
    }
}

/* ==========================================================
   CONTATOS
   ========================================================== */
function configurarContatos() {
    const formContatos = document.querySelector('#form-contatos');
    if (!formContatos) return; // Só executa na página contatos.php

    function atualizarPreVisualizacao() {
        document.querySelector('#preview-telefone').textContent = formContatos.querySelector('#contato-telefone').value.trim();
        document.querySelector('#preview-email').textContent = formContatos.querySelector('#contato-email').value.trim();
        document.querySelector('#preview-endereco').textContent = formContatos.querySelector('#contato-endereco').value.trim();
        document.querySelector('#preview-horario').textContent = formContatos.querySelector('#contato-horario').value.trim();
    }

    document.querySelector('#botao-visualizar-contato')?.addEventListener('click', atualizarPreVisualizacao);

    formContatos.addEventListener('submit', (evento) => {
        evento.preventDefault();
        if (!validarFormularioAdmin(formContatos)) return;

        simularEnvioFormulario(formContatos.querySelector('.admin-botao-salvar'), () => {
            atualizarPreVisualizacao();
            mostrarToastAdmin('Contatos atualizados com sucesso.');
        });
    });

    /* Contatos específicos dos setores. */
    const tabela = document.querySelector('#corpo-tabela-contatos-setores');
    const modal = document.querySelector('#modal-contato-setor');
    const form = document.querySelector('#form-contato-setor');
    const tituloModal = document.querySelector('#modal-contato-setor-titulo');
    if (!tabela) return;

    document.addEventListener('click', (evento) => {
        const gatilho = evento.target.closest('.admin-abrir-modal[data-modal-alvo="modal-contato-setor"]');
        if (!gatilho) return;

        limparErrosFormulario(form);
        const modo = gatilho.dataset.modo;
        form.dataset.modo = modo;

        if (modo === 'editar') {
            const linha = gatilho.closest('[data-linha-contato-setor]');
            form.dataset.idEdicao = linha.dataset.id;
            tituloModal.textContent = 'Editar contato de setor';
            form.querySelector('#contato-setor-nome').value = linha.dataset.setor;
            form.querySelector('#contato-setor-telefone').value = linha.dataset.telefone;
            form.querySelector('#contato-setor-email').value = linha.dataset.email;
        } else {
            delete form.dataset.idEdicao;
            tituloModal.textContent = 'Novo contato de setor';
            form.reset();
        }

        abrirModalAdmin('modal-contato-setor');
    });

    form.addEventListener('submit', (evento) => {
        evento.preventDefault();
        if (!validarFormularioAdmin(form)) return;

        simularEnvioFormulario(form.querySelector('.admin-botao-salvar'), () => {
            const dados = {
                setor: form.querySelector('#contato-setor-nome').value.trim(),
                telefone: form.querySelector('#contato-setor-telefone').value.trim(),
                email: form.querySelector('#contato-setor-email').value.trim(),
            };

            if (form.dataset.idEdicao) {
                const linha = tabela.querySelector(`[data-linha-contato-setor][data-id="${form.dataset.idEdicao}"]`);
                Object.assign(linha.dataset, dados);
                linha.querySelector('[data-rotulo="Setor"]').textContent = dados.setor;
                linha.querySelector('[data-rotulo="Telefone"]').textContent = dados.telefone;
                linha.querySelector('[data-rotulo="E-mail"]').textContent = dados.email;
                mostrarToastAdmin('Contato atualizado com sucesso.');
            } else {
                const linha = document.createElement('tr');
                linha.dataset.linhaContatoSetor = '';
                linha.dataset.id = proximoIdSimuladoAdmin();
                Object.assign(linha.dataset, dados);
                linha.innerHTML = `
                    <td data-rotulo="Setor"><strong>${escaparHtmlAdmin(dados.setor)}</strong></td>
                    <td data-rotulo="Telefone">${escaparHtmlAdmin(dados.telefone)}</td>
                    <td data-rotulo="E-mail">${escaparHtmlAdmin(dados.email)}</td>
                    <td data-rotulo="Ações" class="admin-table-acoes-col">
                        <div class="admin-row-actions">
                            <button type="button" class="admin-icon-button admin-abrir-modal" data-modal-alvo="modal-contato-setor" data-modo="editar" aria-label="Editar contato do setor"><i data-lucide="pencil" class="icon"></i></button>
                            <button type="button" class="admin-icon-button admin-icon-button-danger admin-abrir-exclusao" data-tipo-exclusao="contato-setor" aria-label="Excluir contato do setor"><i data-lucide="trash-2" class="icon"></i></button>
                        </div>
                    </td>
                `;
                tabela.prepend(linha);
                atualizarIcones();
                mostrarToastAdmin('Contato adicionado com sucesso.');
            }

            fecharModalAdmin(modal);
        });
    });
}

/* ==========================================================
   CONFIGURAÇÕES
   ========================================================== */
function configurarConfiguracoes() {
    const form = document.querySelector('#form-configuracoes');
    if (!form) return; // Só executa na página configuracoes.php

    /* Pré-visualização do logotipo — só no navegador, nada é enviado a um servidor. */
    const inputLogo = document.querySelector('#config-logo');
    const caixaPreview = document.querySelector('#logo-preview-caixa');

    inputLogo?.addEventListener('change', () => {
        const arquivo = inputLogo.files?.[0];
        if (!arquivo) return;

        const leitor = new FileReader();
        leitor.onload = () => {
            caixaPreview.innerHTML = `<img src="${leitor.result}" alt="Pré-visualização do logotipo">`;
        };
        leitor.readAsDataURL(arquivo);
    });

    /* Cor principal: aplica uma prévia imediata na própria tela de configurações. */
    const mapaCoresPrincipais = { green: '#0f7a43', blue: '#1d63d6', teal: '#0f8a7a', slate: '#334155' };
    document.querySelectorAll('input[name="corPrincipal"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            if (radio.checked && mapaCoresPrincipais[radio.value]) {
                document.documentElement.style.setProperty('--primary', mapaCoresPrincipais[radio.value]);
            }
        });
    });

    form.addEventListener('submit', (evento) => {
        evento.preventDefault();
        if (!validarFormularioAdmin(form)) return;

        simularEnvioFormulario(form.querySelector('.admin-botao-salvar'), () => {
            const nomeAdmin = form.querySelector('#config-admin-nome').value.trim();
            if (nomeAdmin) {
                localStorage.setItem(CHAVE_NOME_ADMIN, nomeAdmin);
                const elementoNome = document.querySelector('#admin-user-name');
                if (elementoNome) elementoNome.textContent = nomeAdmin;
            }
            mostrarToastAdmin('Configurações salvas com sucesso.');
        });
    });
}
