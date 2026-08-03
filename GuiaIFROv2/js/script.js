/**
 * GUIA IFRO — INTERAÇÕES DO FRONT-END
 *
 * Este arquivo utiliza JavaScript puro. Cada bloco está separado e comentado
 * para facilitar a manutenção pelo grupo e a futura integração com o backend.
 */

document.addEventListener('DOMContentLoaded', () => {
    /* Converte todas as tags data-lucide em ícones SVG. */
    atualizarIcones();

    configurarNavbar();
    configurarAnimacoesDeRolagem();
    configurarBuscaDaHome();
    configurarFiltrosDeCategorias();
    configurarMapaInterativo();
    configurarAcordeoes();
    configurarFormularioDeContato();
});

/** Recria os ícones após inserir novos elementos pelo JavaScript. */
function atualizarIcones() {
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

/* ==========================================================
   NAVBAR FIXA E MENU MOBILE
   ========================================================== */
function configurarNavbar() {
    const header = document.querySelector('#site-header');
    const menuButton = document.querySelector('#menu-button');
    const mobileMenu = document.querySelector('#mobile-menu');

    if (header) {
        const atualizarEstadoNavbar = () => {
            header.classList.toggle('scrolled', window.scrollY > 12);
        };

        window.addEventListener('scroll', atualizarEstadoNavbar, { passive: true });
        atualizarEstadoNavbar();
    }

    if (!menuButton || !mobileMenu) return;

    menuButton.addEventListener('click', () => {
        const menuAberto = mobileMenu.classList.toggle('open');

        menuButton.classList.toggle('active', menuAberto);
        menuButton.setAttribute('aria-expanded', String(menuAberto));
        document.body.classList.toggle('menu-open', menuAberto);
    });

    /* Fecha o menu depois que o usuário escolhe uma página. */
    mobileMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('open');
            menuButton.classList.remove('active');
            menuButton.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('menu-open');
        });
    });
}

/* ==========================================================
   ANIMAÇÕES DE ENTRADA AO ROLAR A PÁGINA
   ========================================================== */
function configurarAnimacoesDeRolagem() {
    const elementos = document.querySelectorAll('.reveal');

    if (!elementos.length) return;

    /* Fallback para navegadores antigos sem IntersectionObserver. */
    if (!('IntersectionObserver' in window)) {
        elementos.forEach((elemento) => elemento.classList.add('visible'));
        return;
    }

    const observador = new IntersectionObserver((entradas, observer) => {
        entradas.forEach((entrada) => {
            if (entrada.isIntersecting) {
                entrada.target.classList.add('visible');
                observer.unobserve(entrada.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -35px 0px',
    });

    elementos.forEach((elemento) => observador.observe(elemento));
}

/* ==========================================================
   BUSCA PRINCIPAL DA HOME
   ========================================================== */
function configurarBuscaDaHome() {
    const input = document.querySelector('#hero-search-input');
    const chips = document.querySelectorAll('[data-search-chip]');

    if (!input || !chips.length) return;

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            input.value = chip.dataset.searchChip || '';
            input.focus();
        });
    });
}

/* Remove acentos e padroniza o texto para melhorar a busca. */
function normalizarTexto(texto) {
    return String(texto)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

/* ==========================================================
   BUSCA, FILTROS E EXPANSÃO DOS CARDS DE CATEGORIAS
   ========================================================== */
function configurarFiltrosDeCategorias() {
    const input = document.querySelector('#category-search');
    const campoBusca = input?.closest('.category-search');
    const botaoLimparBusca = document.querySelector('#clear-category-search');
    const filtros = document.querySelectorAll('[data-category-filter]');
    const cards = document.querySelectorAll('[data-category-card]');
    const contador = document.querySelector('#category-result-count');
    const estadoVazio = document.querySelector('#category-empty-state');
    const botaoReset = document.querySelector('#reset-category-filters');
    const botaoMostrarTodos = document.querySelector('#show-all-categories');

    if (!input || !cards.length) return;

    let filtroAtivo = 'Todos';

    const atualizarCampoBusca = () => {
        campoBusca?.classList.toggle('has-value', input.value.length > 0);
    };

    const aplicarFiltros = () => {
        const termo = normalizarTexto(input.value);
        let quantidadeVisivel = 0;

        cards.forEach((card) => {
            const textoCompleto = normalizarTexto([
                card.dataset.title,
                card.dataset.description,
                card.dataset.sectors,
            ].join(' '));

            const correspondeAoTexto = !termo || textoCompleto.includes(termo);
            const correspondeATag = filtroAtivo === 'Todos' || card.dataset.tag === filtroAtivo;
            const deveMostrar = correspondeAoTexto && correspondeATag;

            card.hidden = !deveMostrar;
            if (deveMostrar) quantidadeVisivel += 1;
        });

        if (contador) {
            contador.innerHTML = `<strong>${quantidadeVisivel}</strong> ${quantidadeVisivel === 1 ? 'categoria encontrada' : 'categorias encontradas'} <button type="button" id="reset-category-filters-inline">Limpar filtros</button>`;
            contador.classList.toggle('filtered', Boolean(termo) || filtroAtivo !== 'Todos');

            contador.querySelector('button')?.addEventListener('click', limparFiltros);
        }

        if (estadoVazio) estadoVazio.hidden = quantidadeVisivel !== 0;
        atualizarCampoBusca();
    };

    function limparFiltros() {
        input.value = '';
        filtroAtivo = 'Todos';

        filtros.forEach((filtro) => {
            filtro.classList.toggle('active', filtro.dataset.categoryFilter === 'Todos');
        });

        aplicarFiltros();
    }

    input.addEventListener('input', aplicarFiltros);
    botaoLimparBusca?.addEventListener('click', () => {
        input.value = '';
        input.focus();
        aplicarFiltros();
    });

    filtros.forEach((filtro) => {
        filtro.addEventListener('click', () => {
            filtroAtivo = filtro.dataset.categoryFilter || 'Todos';
            filtros.forEach((item) => item.classList.toggle('active', item === filtro));
            aplicarFiltros();
        });
    });

    botaoReset?.addEventListener('click', limparFiltros);
    botaoMostrarTodos?.addEventListener('click', limparFiltros);

    /* Mostra ou oculta os nomes dos setores dentro de cada card. */
    document.querySelectorAll('.toggle-sectors').forEach((botao) => {
        botao.addEventListener('click', () => {
            const card = botao.closest('.category-card');
            const lista = card?.querySelector('.sector-tags');

            if (!lista) return;

            const estaAberta = !lista.hidden;
            lista.hidden = estaAberta;
            botao.setAttribute('aria-expanded', String(!estaAberta));

            if (estaAberta) {
                const quantidade = lista.children.length;
                botao.textContent = `${quantidade} ${quantidade === 1 ? 'setor' : 'setores'}`;
            } else {
                botao.textContent = 'Ocultar setores';
            }
        });
    });

    aplicarFiltros();
}

/* ==========================================================
   MAPA INTERATIVO
   ========================================================== */
function configurarMapaInterativo() {
    const dadosElemento = document.querySelector('#map-data');
    const botoesBloco = document.querySelectorAll('[data-map-block]');
    const atalhos = document.querySelectorAll('[data-map-shortcut]');
    const estadoVazio = document.querySelector('#map-empty');
    const conteudo = document.querySelector('#map-detail-content');
    const nome = document.querySelector('#map-detail-name');
    const quantidade = document.querySelector('#map-detail-count');
    const icone = document.querySelector('#map-detail-icon');
    const lista = document.querySelector('#map-sector-list');
    const link = document.querySelector('#map-detail-link');
    const fechar = document.querySelector('#close-map-detail');

    if (!dadosElemento || !botoesBloco.length) return;

    let blocos = [];

    try {
        blocos = JSON.parse(dadosElemento.textContent || '[]');
    } catch (erro) {
        console.error('Não foi possível ler os dados do mapa:', erro);
        return;
    }

    const selecionarBloco = (id) => {
        const bloco = blocos.find((item) => item.id === id);
        if (!bloco) return;

        botoesBloco.forEach((botao) => botao.classList.toggle('active', botao.dataset.mapBlock === id));
        atalhos.forEach((atalho) => atalho.classList.toggle('active', atalho.dataset.mapShortcut === id));

        if (estadoVazio) estadoVazio.hidden = true;
        if (conteudo) conteudo.hidden = false;
        if (nome) nome.textContent = bloco.nome;
        if (quantidade) quantidade.textContent = `${bloco.setores.length} ${bloco.setores.length === 1 ? 'setor' : 'setores'}`;

        if (icone) {
            icone.className = `color-solid-${bloco.cor}`;
            icone.innerHTML = `<i data-lucide="${bloco.icone}" class="icon"></i>`;
        }

        if (lista) {
            lista.innerHTML = bloco.setores.map((setor) => `
                <article class="map-sector-item">
                    <h3>${escaparHtml(setor.nome)}</h3>
                    <p><i data-lucide="phone" class="icon"></i>${escaparHtml(setor.telefone)}</p>
                    <p><i data-lucide="clock" class="icon"></i>${escaparHtml(setor.horario)}</p>
                    <p><i data-lucide="map-pin" class="icon"></i>${escaparHtml(setor.andar)}</p>
                </article>
            `).join('');
        }

        if (link) link.href = `setor.php?id=${encodeURIComponent(bloco.id)}`;
        atualizarIcones();
    };

    const limparSelecao = () => {
        botoesBloco.forEach((botao) => botao.classList.remove('active'));
        atalhos.forEach((atalho) => atalho.classList.remove('active'));
        if (estadoVazio) estadoVazio.hidden = false;
        if (conteudo) conteudo.hidden = true;
    };

    botoesBloco.forEach((botao) => {
        botao.addEventListener('click', () => selecionarBloco(botao.dataset.mapBlock));
    });

    atalhos.forEach((atalho) => {
        atalho.addEventListener('click', () => selecionarBloco(atalho.dataset.mapShortcut));
    });

    fechar?.addEventListener('click', limparSelecao);
}

/* Evita que textos inseridos pelo JavaScript sejam interpretados como HTML. */
function escaparHtml(valor) {
    const elemento = document.createElement('div');
    elemento.textContent = String(valor);
    return elemento.innerHTML;
}

/* ==========================================================
   PERGUNTAS FREQUENTES
   ========================================================== */
function configurarAcordeoes() {
    document.querySelectorAll('.accordion-trigger').forEach((botao) => {
        botao.addEventListener('click', () => {
            const item = botao.closest('.accordion-item');
            if (!item) return;

            const vaiAbrir = !item.classList.contains('open');
            item.classList.toggle('open', vaiAbrir);
            botao.setAttribute('aria-expanded', String(vaiAbrir));
        });
    });
}

/* ==========================================================
   FORMULÁRIO DE CONTATO — SIMULAÇÃO FRONT-END
   ========================================================== */
function configurarFormularioDeContato() {
    const formulario = document.querySelector('#contact-form');
    const wrapper = document.querySelector('#contact-form-wrapper');
    const sucesso = document.querySelector('#form-success');
    const botaoOutro = document.querySelector('#send-another-message');

    if (!formulario || !wrapper || !sucesso) return;

    formulario.addEventListener('submit', (evento) => {
        evento.preventDefault();

        if (!formulario.checkValidity()) {
            formulario.reportValidity();
            return;
        }

        const botao = formulario.querySelector('.contact-submit');
        if (!botao) return;

        const conteudoOriginal = botao.innerHTML;
        botao.classList.add('loading');
        botao.innerHTML = '<span class="loading-spinner" aria-hidden="true"></span><span>Enviando...</span>';

        /* Simula o tempo de uma requisição. O backend substituirá este trecho. */
        window.setTimeout(() => {
            botao.classList.remove('loading');
            botao.innerHTML = conteudoOriginal;
            wrapper.hidden = true;
            sucesso.hidden = false;
            atualizarIcones();
        }, 900);
    });

    botaoOutro?.addEventListener('click', () => {
        formulario.reset();
        wrapper.hidden = false;
        sucesso.hidden = true;
    });
}