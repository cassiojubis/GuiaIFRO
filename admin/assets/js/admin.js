/**
 * ÁREA ADMINISTRATIVA — GUIA IFRO
 * JavaScript puro. Quem salva é o servidor (formulários POST com token CSRF);
 * aqui só cuidamos da parte de tela:
 *  - abrir os formulários (modais) já preenchidos pra editar;
 *  - confirmar antes de excluir;
 *  - mostrar o aviso da última ação e reabrir o formulário quando deu erro;
 *  - filtros e busca das listas;
 *  - abas, listas dinâmicas (+ adicionar item) e prévia de ícone.
 * escaparHtml() vem de ../js/util.js (carregado antes deste arquivo).
 */

document.addEventListener('DOMContentLoaded', () => {
    atualizarIcones();
    configurarToggleSenhaLogin();
    configurarSidebarAdmin();
    configurarModaisAdmin();
    configurarFormulariosAdmin();
    configurarExclusaoAdmin();
    configurarListasDinamicas();
    configurarPreviaIcone();
    configurarFiltrosAdmin();
    configurarAbasAdmin();
    configurarFaixasHorario();
    // <select data-enviar-ao-mudar> manda o formulário assim que muda (ex.: período das estatísticas)
    document.querySelectorAll('[data-enviar-ao-mudar]').forEach((campo) => campo.addEventListener('change', () => campo.form?.submit()));
    mostrarAvisoDoServidor();
});

/** Recria os ícones Lucide após inserir novos elementos pelo JavaScript. */
function atualizarIcones() {
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

function normalizarTextoAdmin(texto) {
    return String(texto ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

/**
 * fetch do admin já com o token CSRF no cabeçalho X-CSRF-Token (o PHP confere
 * com exigirCsrf()). O token vem da <meta name="csrf-token"> do cabeçalho.
 * Devolve o JSON da resposta; se o servidor recusar, lança um erro com a mensagem dele.
 */
async function enviarAdmin(url, dados) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const resposta = await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-Token': token, Accept: 'application/json' },
        body: dados instanceof FormData ? dados : new URLSearchParams(dados),
        credentials: 'same-origin',
    });
    const json = await resposta.json().catch(() => ({}));
    if (!resposta.ok || json.ok === false) {
        throw new Error(json.erro || json.mensagem || 'Não foi possível salvar. Tente de novo.');
    }
    return json;
}

/* LOGIN (e troca de senha): mostrar/ocultar a senha digitada */
function configurarToggleSenhaLogin() {
    document.querySelectorAll('.admin-toggle-senha').forEach((botao) => {
        const campo = botao.closest('.admin-input-with-icon')?.querySelector('input');
        if (!campo) return;
        botao.addEventListener('click', () => {
            const estaMostrando = botao.getAttribute('aria-pressed') === 'true';
            botao.setAttribute('aria-pressed', String(!estaMostrando));
            botao.setAttribute('aria-label', estaMostrando ? 'Mostrar senha' : 'Ocultar senha');
            campo.type = estaMostrando ? 'password' : 'text';
        });
    });
}

/* MENU LATERAL (GAVETA NO CELULAR) */
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
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', fecharGaveta));
}

/* ==========================================================
   MODAIS
   ========================================================== */
let elementoQueAbriuModal = null;

function abrirModalAdmin(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    elementoQueAbriuModal = document.activeElement;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    // Foco no primeiro campo (quem usa teclado ou leitor de tela já cai no formulário).
    window.setTimeout(() => {
        const primeiro = modal.querySelector('input:not([type="hidden"]):not([disabled]), select, textarea, button:not([data-fechar-modal])');
        primeiro?.focus();
    }, 50);
}

function fecharModalAdmin(modal) {
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    elementoQueAbriuModal?.focus?.();
}

function configurarModaisAdmin() {
    /* Fecha ao clicar no fundo escurecido, no "x" ou em qualquer botão "Cancelar". */
    document.addEventListener('click', (evento) => {
        const gatilhoFechar = evento.target.closest('[data-fechar-modal]');
        if (gatilhoFechar) fecharModalAdmin(gatilhoFechar.closest('.admin-modal'));
    });

    /* Esc fecha; Tab fica preso dentro do modal aberto. */
    document.addEventListener('keydown', (evento) => {
        const aberto = document.querySelector('.admin-modal.open');
        if (!aberto) return;
        if (evento.key === 'Escape') {
            fecharModalAdmin(aberto);
            return;
        }
        if (evento.key === 'Tab') {
            const focaveis = Array.from(aberto.querySelectorAll('a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled]), select, textarea'))
                .filter((elemento) => elemento.offsetParent !== null);
            if (!focaveis.length) return;
            const primeiro = focaveis[0];
            const ultimo = focaveis[focaveis.length - 1];
            if (evento.shiftKey && document.activeElement === primeiro) {
                evento.preventDefault();
                ultimo.focus();
            } else if (!evento.shiftKey && document.activeElement === ultimo) {
                evento.preventDefault();
                primeiro.focus();
            }
        }
    });

    /*
     * Botões que abrem um formulário: data-abrir-modal="id-do-modal".
     * Pra editar, o botão traz data-valores='{"campo": "valor", ...}' (JSON escapado pelo PHP)
     * e data-titulo="Editar ...". Sem data-valores, o formulário abre limpo pra criar.
     */
    document.addEventListener('click', (evento) => {
        const gatilho = evento.target.closest('[data-abrir-modal]');
        if (!gatilho) return;
        evento.preventDefault();

        let valores = {};
        try {
            valores = gatilho.dataset.valores ? JSON.parse(gatilho.dataset.valores) : {};
        } catch (erro) {
            valores = {};
        }
        prepararFormulario(gatilho.dataset.abrirModal, valores, gatilho.dataset.titulo);
        abrirModalAdmin(gatilho.dataset.abrirModal);
    });
}

/** Limpa o formulário do modal e preenche com os valores (pelo name dos campos). */
function prepararFormulario(idModal, valores, titulo) {
    const modal = document.getElementById(idModal);
    const form = modal?.querySelector('form');
    if (!form) return;

    form.reset();
    limparErrosFormulario(form);
    form.querySelectorAll('input[type="hidden"][data-limpar]').forEach((campo) => { campo.value = ''; });
    form.querySelectorAll('[data-lista-dinamica]').forEach((lista) => { lista.innerHTML = ''; });

    const tituloModal = modal.querySelector('[data-titulo-modal]');
    if (tituloModal) {
        if (!tituloModal.dataset.tituloPadrao) tituloModal.dataset.tituloPadrao = tituloModal.textContent;
        tituloModal.textContent = titulo || tituloModal.dataset.tituloPadrao;
    }

    Object.entries(valores || {}).forEach(([nome, valor]) => {
        // Lista dinâmica (equipe[], servicos[]...): recria um item por valor.
        const lista = form.querySelector(`[data-lista-dinamica="${CSS.escape(nome)}"]`);
        if (lista) {
            (Array.isArray(valor) ? valor : []).forEach((item) => adicionarItemLista(lista, item));
            return;
        }
        const campos = form.querySelectorAll(`[name="${CSS.escape(nome)}"], [name="${CSS.escape(nome)}[]"]`);
        campos.forEach((campo) => {
            if (campo.type === 'checkbox') {
                campo.checked = Array.isArray(valor)
                    ? valor.map(String).includes(campo.value)
                    : (valor === true || valor === '1' || valor === 1);
            } else if (campo.type === 'radio') {
                campo.checked = String(valor) === campo.value;
            } else if (campo.tagName === 'SELECT' && campo.multiple) {
                const escolhidos = (Array.isArray(valor) ? valor : [valor]).map(String);
                Array.from(campo.options).forEach((opcao) => { opcao.selected = escolhidos.includes(opcao.value); });
            } else if (campo.type !== 'file' && campo.type !== 'password') {
                campo.value = valor ?? '';
            }
        });
    });

    form.querySelectorAll('[data-previa-icone]').forEach((campo) => campo.dispatchEvent(new Event('input')));
    form.querySelectorAll('[data-texto-toggle]').forEach((texto) => {
        const caixa = texto.closest('label')?.querySelector('input[type="checkbox"]');
        if (caixa) texto.textContent = caixa.checked ? texto.dataset.ligado : texto.dataset.desligado;
    });
    atualizarIcones();
}

function limparErrosFormulario(form) {
    form?.querySelectorAll('.admin-field-invalido').forEach((campo) => campo.classList.remove('admin-field-invalido'));
    form?.querySelectorAll('.admin-field-error[data-do-servidor]').forEach((erro) => erro.remove());
}

/* ==========================================================
   FORMULÁRIOS: validação rápida no navegador + botão "Salvando..."
   (a validação de verdade é no servidor)
   ========================================================== */
function configurarFormulariosAdmin() {
    document.querySelectorAll('form.admin-form').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            limparErrosFormulario(form);
            let valido = true;
            form.querySelectorAll('[required]').forEach((campo) => {
                if (campo.type !== 'checkbox' && String(campo.value).trim() === '') {
                    valido = false;
                    campo.closest('.admin-field')?.classList.add('admin-field-invalido');
                }
            });
            if (!valido) {
                evento.preventDefault();
                form.querySelector('.admin-field-invalido input, .admin-field-invalido textarea, .admin-field-invalido select')?.focus();
                return;
            }
            const botao = form.querySelector('.admin-botao-salvar');
            if (botao) {
                // Desabilita depois do envio começar (senão o navegador não manda o form).
                window.setTimeout(() => {
                    botao.disabled = true;
                    botao.classList.add('loading');
                    botao.innerHTML = '<span class="loading-spinner" aria-hidden="true"></span><span>Salvando...</span>';
                }, 0);
            }
        });
    });

    /* Texto ao lado dos interruptores (Ativo/Inativo) acompanha o estado. */
    document.addEventListener('change', (evento) => {
        const caixa = evento.target;
        if (caixa.type !== 'checkbox') return;
        const texto = caixa.closest('label')?.querySelector('[data-texto-toggle]');
        if (texto) texto.textContent = caixa.checked ? texto.dataset.ligado : texto.dataset.desligado;
    });
}

/* ==========================================================
   EXCLUSÃO: todo botão [data-confirmar-exclusao] fica dentro de um <form>
   de exclusão. Ele só é enviado depois de confirmar no modal.
   ========================================================== */
function configurarExclusaoAdmin() {
    const texto = document.querySelector('#modal-confirmar-exclusao-texto');
    const botaoConfirmar = document.querySelector('#botao-confirmar-exclusao');
    if (!botaoConfirmar) return;

    let formularioPendente = null;

    document.addEventListener('click', (evento) => {
        const botao = evento.target.closest('[data-confirmar-exclusao]');
        if (!botao) return;
        evento.preventDefault();
        formularioPendente = botao.closest('form');
        if (texto) {
            texto.textContent = botao.dataset.confirmarExclusao || 'Tem certeza que deseja excluir este item? Essa ação não poderá ser desfeita.';
        }
        abrirModalAdmin('modal-confirmar-exclusao');
    });

    botaoConfirmar.addEventListener('click', () => {
        if (formularioPendente) {
            botaoConfirmar.disabled = true;
            formularioPendente.submit();
        }
    });
}

/* ==========================================================
   LISTAS DINÂMICAS (equipe, serviços, documentos...)
   <ul data-lista-dinamica="servicos" data-placeholder="Nome do serviço">
   + botão [data-adicionar-item="servicos"]. Cada item vira name="servicos[]".
   ========================================================== */
function adicionarItemLista(lista, valor = '') {
    const nome = lista.dataset.listaDinamica;
    const rotulo = lista.dataset.placeholder || 'Item';
    const item = document.createElement('li');
    item.innerHTML = `
        <input type="text" name="${escaparHtml(nome)}[]" value="${escaparHtml(valor)}" placeholder="${escaparHtml(rotulo)}" maxlength="${escaparHtml(lista.dataset.maximo || '255')}" aria-label="${escaparHtml(rotulo)}">
        <button type="button" class="admin-remover-item" aria-label="Remover item"><i data-lucide="x" class="icon"></i></button>
    `;
    lista.appendChild(item);
    atualizarIcones();
    return item;
}

function configurarListasDinamicas() {
    document.addEventListener('click', (evento) => {
        const botaoAdicionar = evento.target.closest('[data-adicionar-item]');
        if (botaoAdicionar) {
            const lista = document.querySelector(`[data-lista-dinamica="${CSS.escape(botaoAdicionar.dataset.adicionarItem)}"]`);
            if (lista) adicionarItemLista(lista).querySelector('input').focus();
            return;
        }
        const botaoRemover = evento.target.closest('.admin-remover-item');
        if (botaoRemover) botaoRemover.closest('li')?.remove();
    });
}

/* Prévia do ícone Lucide digitado: <input data-previa-icone="#id-da-previa"> */
function configurarPreviaIcone() {
    document.querySelectorAll('[data-previa-icone]').forEach((campo) => {
        const previa = document.querySelector(campo.dataset.previaIcone);
        if (!previa) return;
        campo.addEventListener('input', () => {
            const nome = campo.value.trim().toLowerCase().replace(/[^a-z0-9-]/g, '') || 'shapes';
            previa.innerHTML = `<i data-lucide="${escaparHtml(nome)}" class="icon"></i>`;
            atualizarIcones();
        });
    });
}

/* ==========================================================
   FILTROS DAS LISTAS
   <input data-busca-lista="#corpo"> procura no data-busca de cada [data-linha].
   <button data-filtro="#corpo" data-filtro-chave="status" data-filtro-valor="ativo">
   <select data-filtro="#corpo" data-filtro-chave="categoria"> (valor "todos" = sem filtro)
   Contador: [data-contador-lista="#corpo"] com data-singular / data-plural.
   ========================================================== */
function configurarFiltrosAdmin() {
    const alvos = new Set();
    document.querySelectorAll('[data-busca-lista], [data-filtro]').forEach((elemento) => {
        alvos.add(elemento.dataset.buscaLista || elemento.dataset.filtro);
    });

    alvos.forEach((seletor) => {
        const corpo = document.querySelector(seletor);
        if (!corpo) return;
        const busca = document.querySelector(`[data-busca-lista="${seletor}"]`);
        const filtrosAtivos = {};

        const aplicar = () => {
            const termo = normalizarTextoAdmin(busca?.value || '');
            let visiveis = 0;
            corpo.querySelectorAll('[data-linha]').forEach((linha) => {
                const combinaTexto = !termo || normalizarTextoAdmin(linha.dataset.busca || linha.textContent).includes(termo);
                const combinaFiltros = Object.entries(filtrosAtivos).every(([chave, valor]) => valor === 'todos' || linha.dataset[chave] === valor);
                linha.hidden = !(combinaTexto && combinaFiltros);
                if (!linha.hidden) visiveis += 1;
            });
            const contador = document.querySelector(`[data-contador-lista="${seletor}"]`);
            if (contador) {
                contador.textContent = `${visiveis} ${visiveis === 1 ? contador.dataset.singular : contador.dataset.plural}`;
            }
            const vazio = document.querySelector(`[data-vazio-lista="${seletor}"]`);
            if (vazio) vazio.hidden = visiveis !== 0;
        };

        busca?.addEventListener('input', aplicar);

        document.querySelectorAll(`button[data-filtro="${seletor}"]`).forEach((chip) => {
            chip.addEventListener('click', () => {
                filtrosAtivos[chip.dataset.filtroChave] = chip.dataset.filtroValor;
                document.querySelectorAll(`button[data-filtro="${seletor}"][data-filtro-chave="${chip.dataset.filtroChave}"]`).forEach((outro) => {
                    outro.classList.toggle('active', outro === chip);
                    outro.setAttribute('aria-pressed', String(outro === chip));
                });
                aplicar();
            });
        });

        document.querySelectorAll(`select[data-filtro="${seletor}"]`).forEach((select) => {
            select.addEventListener('change', () => {
                filtrosAtivos[select.dataset.filtroChave] = select.value;
                aplicar();
            });
        });

        aplicar();
    });
}

/* ABAS (página Informações). A aba aberta fica no endereço (#avisos) pra voltar nela depois de salvar. */
function configurarAbasAdmin() {
    const botoes = Array.from(document.querySelectorAll('.admin-tab-button'));
    if (!botoes.length) return;

    const abrirAba = (alvo) => {
        if (!botoes.some((botao) => botao.dataset.abaAlvo === alvo)) return false;
        botoes.forEach((botao) => {
            const ativo = botao.dataset.abaAlvo === alvo;
            botao.classList.toggle('active', ativo);
            botao.setAttribute('aria-selected', String(ativo));
            botao.tabIndex = ativo ? 0 : -1;
        });
        document.querySelectorAll('.admin-tab-painel').forEach((painel) => {
            painel.hidden = painel.dataset.abaPainel !== alvo;
        });
        // Os formulários da página guardam a aba pra voltar nela depois de salvar.
        document.querySelectorAll('input[name="aba_retorno"]').forEach((campo) => { campo.value = alvo; });
        return true;
    };

    botoes.forEach((botao, indice) => {
        botao.addEventListener('click', () => {
            abrirAba(botao.dataset.abaAlvo);
            history.replaceState(null, '', `#${botao.dataset.abaAlvo}`);
        });
        // Setas do teclado trocam de aba (padrão de acessibilidade para tablist).
        botao.addEventListener('keydown', (evento) => {
            if (evento.key !== 'ArrowRight' && evento.key !== 'ArrowLeft') return;
            const proximo = botoes[(indice + (evento.key === 'ArrowRight' ? 1 : botoes.length - 1)) % botoes.length];
            proximo.focus();
            proximo.click();
        });
    });

    const daUrl = window.location.hash.replace('#', '');
    if (!daUrl || !abrirAba(daUrl)) abrirAba(botoes[0].dataset.abaAlvo);
}

/* ==========================================================
   AVISOS (toast) e formulário reaberto com erro
   ========================================================== */
function mostrarToastAdmin(mensagem, tipo = 'sucesso') {
    const container = document.querySelector('#admin-toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'admin-toast' + (tipo === 'erro' ? ' admin-toast-erro' : '');
    toast.setAttribute('role', tipo === 'erro' ? 'alert' : 'status');
    toast.innerHTML = `<i data-lucide="${tipo === 'erro' ? 'circle-alert' : 'check-circle-2'}" class="icon"></i><span>${escaparHtml(mensagem)}</span>`;
    container.appendChild(toast);
    atualizarIcones();

    window.setTimeout(() => {
        toast.style.transition = 'opacity .25s ease, transform .25s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(8px)';
        window.setTimeout(() => toast.remove(), 250);
    }, tipo === 'erro' ? 7000 : 3600);
}

function mostrarAvisoDoServidor() {
    const elemento = document.querySelector('#admin-aviso-dados');
    if (!elemento) return;
    let dados = {};
    try {
        dados = JSON.parse(elemento.textContent) || {};
    } catch (erro) {
        return;
    }

    if (dados.aviso?.texto) mostrarToastAdmin(dados.aviso.texto, dados.aviso.tipo);

    const reabrir = dados.reabrir;
    if (reabrir?.modal) {
        prepararFormulario(reabrir.modal, reabrir.valores || {}, reabrir.titulo);
        const form = document.getElementById(reabrir.modal)?.querySelector('form');
        Object.entries(reabrir.erros || {}).forEach(([campo, mensagem]) => {
            const entrada = form?.querySelector(`[name="${CSS.escape(campo)}"], [name="${CSS.escape(campo)}[]"]`);
            const caixa = entrada?.closest('.admin-field');
            if (!caixa) return;
            caixa.classList.add('admin-field-invalido');
            const erro = document.createElement('span');
            erro.className = 'admin-field-error';
            erro.dataset.doServidor = '';
            erro.textContent = mensagem;
            caixa.appendChild(erro);
        });
        abrirModalAdmin(reabrir.modal);
    }
}

/* ==========================================================
   HORÁRIOS DO SETOR (setor-horarios.php)
   + Período: copia o <template id="modelo-faixa"> trocando __DIA__ pelo dia.
   Copiar segunda: repete os períodos da segunda de terça a sexta.
   ========================================================== */
function configurarFaixasHorario() {
    const modelo = document.querySelector('#modelo-faixa');
    if (!modelo) return;

    const novaFaixa = (dia, abre = '', fecha = '') => {
        const lista = document.querySelector(`[data-faixas-dia="${dia}"]`);
        const item = document.createElement('template');
        item.innerHTML = modelo.innerHTML.replace(/__DIA__/g, String(Number(dia))).trim();
        const linha = item.content.firstElementChild;
        const [campoAbre, campoFecha] = linha.querySelectorAll('input');
        campoAbre.value = abre;
        campoFecha.value = fecha;
        lista.appendChild(linha);
        atualizarIcones();
        return linha;
    };

    document.addEventListener('click', (evento) => {
        const botao = evento.target.closest('[data-adicionar-faixa]');
        if (botao) {
            novaFaixa(botao.dataset.adicionarFaixa).querySelector('input').focus();
            return;
        }
        if (evento.target.closest('[data-copiar-segunda]')) {
            const faixasSegunda = Array.from(document.querySelectorAll('[data-faixas-dia="1"] .admin-faixa')).map((linha) => {
                const [abre, fecha] = linha.querySelectorAll('input');
                return [abre.value, fecha.value];
            });
            [2, 3, 4, 5].forEach((dia) => {
                document.querySelector(`[data-faixas-dia="${dia}"]`).innerHTML = '';
                faixasSegunda.forEach(([abre, fecha]) => novaFaixa(dia, abre, fecha));
            });
            mostrarToastAdmin('Copiado. Confira e clique em Salvar horário.');
        }
    });
}
