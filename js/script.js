// JS puro, sem framework. Cada função cuida de uma parte da página.

document.addEventListener('DOMContentLoaded', () => {
    /* Converte todas as tags data-lucide em ícones SVG. */
    atualizarIcones();

    configurarNavbar();
    configurarMenuCompacto();
    configurarAnimacoesDeRolagem();
    configurarBuscaDaHome();
    configurarFiltrosDeCategorias();
    configurarAcordeoes();
    configurarFormularioDeContato();
    configurarAcessibilidade();
    configurarBuscaAoVivo();
    configurarFavoritosERecentes();
    configurarVideoDaHome();
    configurarRodape();
    configurarHojeNoCampus();
    configurarPreferencias();
});

/** Os ícones já vêm prontos do servidor (icone() em model/Config.php).
    Isto só age se alguma página ainda carregar o lucide.min.js (o admin carrega). */
function atualizarIcones() {
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

/* NAVBAR FIXA E MENU MOBILE */
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

/* MENU QUE CABE NO CABEÇALHO (computador): se os links não cabem (Oro Wari, tela média),
   vai liberando espaço aos poucos, sem trocar pelo botão ☰ (ver css/responsivo.css):
     menu-justo     -> menos espaço entre os links
     menu-sem-botao -> esconde o botão "Fale conosco" (o link "Contato" continua no menu)
     menu-sem-busca -> esconde a caixa de busca do topo (a página inicial e Categorias têm busca)
     menu-compacto  -> último recurso: botão ☰ (o mesmo do celular)
   Mede de novo quando a janela muda de tamanho ou quando muda a fonte/leitura fácil. */
function configurarMenuCompacto() {
    const html = document.documentElement;
    const navbar = document.querySelector('.navbar');
    const menu = document.querySelector('.desktop-nav');
    if (!navbar || !menu) return;

    const etapas = ['menu-justo', 'menu-sem-botao', 'menu-sem-busca', 'menu-compacto'];
    const semEtapas = () => html.className.replace(/\bmenu-(justo|sem-botao|sem-busca|compacto)\b/g, '').replace(/\s+/g, ' ').trim();
    const naoCabe = () => navbar.scrollWidth > navbar.clientWidth + 1 || navbar.scrollHeight > navbar.clientHeight + 1;

    let medindo = false;
    const medir = () => {
        if (medindo) return;
        medindo = true;
        html.classList.remove(...etapas);
        // No celular (até 900px) o CSS já mostra o botão ☰; aí não precisa medir.
        if (getComputedStyle(menu).display !== 'none') {
            for (const etapa of etapas) {
                if (!naoCabe()) break;
                html.classList.add(etapa);
            }
        }
        medindo = false;
    };

    medir();
    window.addEventListener('resize', medir, { passive: true });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(medir);
    // Fonte, leitura fácil etc. são classes no <html>; as classes das etapas são ignoradas.
    let ultimasClasses = semEtapas();
    new MutationObserver(() => {
        const classes = semEtapas();
        if (classes === ultimasClasses) return;
        ultimasClasses = classes;
        medir();
    }).observe(html, { attributes: true, attributeFilter: ['class'] });
    // O Oro Wari troca o texto do menu depois: mede de novo quando os links mudam.
    new MutationObserver(medir).observe(menu, { subtree: true, characterData: true, childList: true });
}

/* ANIMAÇÕES DE ENTRADA AO ROLAR A PÁGINA */
function configurarAnimacoesDeRolagem() {
    const elementos = document.querySelectorAll('.reveal');

    if (!elementos.length) return;

    /* Só ativa a classe que esconde o conteúdo se o JavaScript realmente
       chegou até aqui — evita que o conteúdo fique invisível para sempre
       caso o script pare de rodar por algum outro motivo. */
    document.documentElement.classList.add('js-reveal');

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

    /* Rede de segurança: se por algum motivo o observer não revelar um
       elemento (ex.: ambiente com comportamento de viewport atípico),
       garante que o conteúdo apareça de qualquer forma depois de um tempo. */
    window.setTimeout(() => {
        elementos.forEach((elemento) => elemento.classList.add('visible'));
    }, 2000);
}

/* BUSCA PRINCIPAL DA HOME */
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

/* BUSCA, FILTROS E EXPANSÃO DOS CARDS DE CATEGORIAS */
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

/* escaparHtml() fica em js/util.js (carregado antes deste arquivo). */

/* PERGUNTAS FREQUENTES */
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

/* FORMULÁRIO DE CONTATO
   Envia de verdade (fetch para processar-contato.php) e só mostra
   "Mensagem enviada!" se o servidor responder ok. Sem JavaScript o
   formulário continua funcionando com o POST normal. */
function configurarFormularioDeContato() {
    const formulario = document.querySelector('#contact-form');
    const wrapper = document.querySelector('#contact-form-wrapper');
    const sucesso = document.querySelector('#form-success');
    const caixaErro = document.querySelector('#contact-form-erro');

    if (!formulario || !wrapper || !sucesso) return;

    const limparErros = () => {
        formulario.querySelectorAll('.campo-erro').forEach((erro) => erro.remove());
        formulario.querySelectorAll('[aria-invalid]').forEach((campo) => {
            campo.removeAttribute('aria-invalid');
            campo.removeAttribute('aria-describedby');
        });
        if (caixaErro) caixaErro.hidden = true;
    };

    const mostrarErros = (mensagem, erros) => {
        if (caixaErro) {
            caixaErro.textContent = mensagem;
            caixaErro.hidden = false;
        }
        Object.entries(erros || {}).forEach(([nomeCampo, texto]) => {
            const campo = formulario.querySelector(`[name="${nomeCampo}"]`);
            if (!campo) return;
            const aviso = document.createElement('small');
            aviso.className = 'campo-erro';
            aviso.id = `erro-${nomeCampo}`;
            aviso.textContent = texto;
            campo.insertAdjacentElement('afterend', aviso);
            campo.setAttribute('aria-invalid', 'true');
            campo.setAttribute('aria-describedby', aviso.id);
        });
        const primeiroInvalido = formulario.querySelector('[aria-invalid="true"]');
        (primeiroInvalido || caixaErro)?.focus?.();
    };

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        limparErros();

        if (!formulario.checkValidity()) {
            formulario.reportValidity();
            return;
        }

        const botao = formulario.querySelector('.contact-submit');
        const conteudoOriginal = botao.innerHTML;
        botao.disabled = true;
        botao.classList.add('loading');
        botao.innerHTML = '<span class="loading-spinner" aria-hidden="true"></span><span>Enviando...</span>';

        try {
            const resposta = await fetch(formulario.action, {
                method: 'POST',
                body: new FormData(formulario),
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const dados = await resposta.json().catch(() => null);

            if (resposta.ok && dados && dados.ok) {
                wrapper.hidden = true;
                sucesso.hidden = false;
                sucesso.focus?.();
                formulario.reset();
            } else {
                mostrarErros(dados?.mensagem || 'Não foi possível enviar agora. Tente de novo em alguns minutos.', dados?.erros);
            }
        } catch (erro) {
            mostrarErros('Sem conexão com o servidor. Verifique sua internet e tente de novo.', {});
        } finally {
            botao.disabled = false;
            botao.classList.remove('loading');
            botao.innerHTML = conteudoOriginal;
            atualizarIcones();
        }
    });

    /* "Enviar outra mensagem": com JS volta pro formulário sem recarregar. */
    document.querySelector('#send-another-message')?.addEventListener('click', (evento) => {
        evento.preventDefault();
        limparErros();
        wrapper.hidden = false;
        sucesso.hidden = true;
    });
}
/* WIDGET DE ACESSIBILIDADE (botão flutuante + página completa) */
function configurarAcessibilidade() {
    const botao = document.querySelector('#acessibilidade-botao');
    const painel = document.querySelector('#acessibilidade-painel');

    if (botao && painel) {
        botao.addEventListener('click', () => {
            const aberto = !painel.hidden;
            painel.hidden = aberto;
            botao.setAttribute('aria-expanded', String(!aberto));
        });

        document.addEventListener('click', (evento) => {
            const widget = document.querySelector('#acessibilidade-widget');
            if (widget && !widget.contains(evento.target)) {
                painel.hidden = true;
                botao.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* Botões de tamanho de fonte — tanto os do widget flutuante quanto
       os da página /acessibilidade.php usam o mesmo atributo data-fonte. */
    const botoesFonte = document.querySelectorAll('[data-fonte]');
    if (!botoesFonte.length) return;

    const aplicarFonte = (tamanho) => {
        document.documentElement.classList.remove('fonte-media', 'fonte-grande');
        if (tamanho === 'media' || tamanho === 'grande') {
            document.documentElement.classList.add('fonte-' + tamanho);
        }
        localStorage.setItem('guiaifro-fonte', tamanho);

        botoesFonte.forEach((btn) => {
            btn.classList.toggle('ativo', btn.dataset.fonte === tamanho);
        });
    };

    const salvo = localStorage.getItem('guiaifro-fonte') || 'normal';
    botoesFonte.forEach((btn) => {
        btn.classList.toggle('ativo', btn.dataset.fonte === salvo);
        btn.addEventListener('click', () => aplicarFonte(btn.dataset.fonte));
    });

    /* Modo escuro (pode haver mais de um botão na página) */
    const botoesTema = document.querySelectorAll('.alternar-tema');
    if (botoesTema.length) {
        const atualizarBotoesTema = () => {
            const escuro = document.documentElement.classList.contains('tema-escuro');
            botoesTema.forEach((btn) => {
                const texto = btn.querySelector('span');
                if (texto) texto.textContent = escuro ? 'Modo claro' : 'Modo escuro';
            });
        };
        atualizarBotoesTema();

        botoesTema.forEach((btn) => {
            btn.addEventListener('click', () => {
                const escuro = document.documentElement.classList.toggle('tema-escuro');
                localStorage.setItem('guiaifro-tema', escuro ? 'escuro' : 'claro');
                atualizarBotoesTema();
            });
        });
    }
}

/* ==========================================================
   BUSCA COM RESULTADO AO VIVO (usa os dados de #setores-busca-data)
   ========================================================== */
function obterSetoresParaBusca() {
    const elemento = document.querySelector('#setores-busca-data');
    if (!elemento) return [];
    try {
        const setores = JSON.parse(elemento.textContent);
        // Em espanhol (js/traducao-es.js), os nomes aparecem e são buscados traduzidos.
        if (!window.traduzirTexto) return setores;
        return setores.map((setor) => ({ ...setor, nome: window.traduzirTexto(setor.nome) }));
    } catch (erro) {
        return [];
    }
}

function configurarBuscaAoVivo() {
    const setoresLocais = obterSetoresParaBusca();

    document.querySelectorAll('[data-busca-ao-vivo]').forEach((input) => {
        const wrapper = input.closest('.busca-com-sugestoes');
        const resultados = wrapper ? wrapper.querySelector('.busca-resultados') : null;
        if (!resultados) return;

        let timerBusca = 0;
        let pedidoAtual = 0;

        const fechar = () => {
            resultados.hidden = true;
            resultados.innerHTML = '';
        };

        const mostrar = (lista, termo) => {
            if (!lista.length) {
                resultados.innerHTML = `<p class="busca-resultado-vazio">Nada encontrado. <a href="categorias.php?q=${encodeURIComponent(termo)}">Ver a busca completa</a></p>`;
            } else {
                resultados.innerHTML = lista.map((item) => {
                    const detalhe = [item.andar, item.motivo].filter(Boolean).join(' · ');
                    const mapa = item.url_mapa
                        ? `<a href="${escaparHtml(item.url_mapa)}" class="busca-resultado-mapa" aria-label="Ver ${escaparHtml(item.nome)} no mapa">Mapa</a>`
                        : '';
                    return `<div class="busca-resultado-linha">
                        <a href="${escaparHtml(item.url)}" class="busca-resultado-item">
                            ${item.sigla ? `<strong>${escaparHtml(item.sigla)}</strong> ` : ''}${escaparHtml(item.nome)}
                            ${detalhe ? `<small>${escaparHtml(detalhe)}</small>` : ''}
                        </a>${mapa}</div>`;
                }).join('');
            }
            resultados.hidden = false;
        };

        /* Se o servidor não responder, procura na lista que já veio na página (só nome e sigla). */
        const buscarLocal = (termo) => {
            const t = normalizarTexto(termo);
            return setoresLocais
                .filter((setor) => normalizarTexto(setor.nome).includes(t) || normalizarTexto(setor.sigla).includes(t))
                .slice(0, 6)
                .map((setor) => ({ nome: setor.nome, sigla: setor.sigla, url: setor.url }));
        };

        input.addEventListener('input', () => {
            const termo = input.value.trim();
            clearTimeout(timerBusca);
            if (termo.length < 2) {
                fechar();
                return;
            }
            timerBusca = setTimeout(async () => {
                const pedido = ++pedidoAtual;
                try {
                    const resposta = await fetch(`busca.php?q=${encodeURIComponent(termo)}`, { headers: { Accept: 'application/json' } });
                    const dados = await resposta.json();
                    if (pedido === pedidoAtual) mostrar(dados.resultados || [], termo);
                } catch (erro) {
                    if (pedido === pedidoAtual) mostrar(buscarLocal(termo), termo);
                }
            }, 250);
        });

        input.addEventListener('keydown', (evento) => {
            if (evento.key === 'Escape') fechar();
            // Seta pra baixo entra na lista de resultados.
            if (evento.key === 'ArrowDown') {
                const primeiro = resultados.querySelector('a');
                if (primeiro) {
                    evento.preventDefault();
                    primeiro.focus();
                }
            }
        });

        resultados.addEventListener('keydown', (evento) => {
            const links = Array.from(resultados.querySelectorAll('a'));
            const posicao = links.indexOf(document.activeElement);
            if (evento.key === 'ArrowDown' && posicao < links.length - 1) {
                evento.preventDefault();
                links[posicao + 1].focus();
            } else if (evento.key === 'ArrowUp') {
                evento.preventDefault();
                (posicao > 0 ? links[posicao - 1] : input).focus();
            } else if (evento.key === 'Escape') {
                fechar();
                input.focus();
            }
        });

        document.addEventListener('click', (evento) => {
            if (wrapper && !wrapper.contains(evento.target)) fechar();
        });
    });
}

/* ==========================================================
   FAVORITOS E RECENTES (localStorage, sem precisar de backend)
   ========================================================== */
function lerListaSalva(chave) {
    try {
        const valor = JSON.parse(localStorage.getItem(chave));
        return Array.isArray(valor) ? valor : [];
    } catch (erro) {
        return [];
    }
}

function salvarListaFavoritos(lista) {
    localStorage.setItem('guiaifro-favoritos', JSON.stringify(lista));
}

function salvarListaRecentes(lista) {
    localStorage.setItem('guiaifro-recentes', JSON.stringify(lista));
}

function montarCardSetor(setor) {
    const link = document.createElement('a');
    link.href = setor.url;
    link.className = 'quick-card';
    link.innerHTML = `
        <span class="quick-icon color-green"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-building-2 icon"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg></span>
        <strong>${escaparHtml(setor.nome)}</strong>
        <small>${escaparHtml(setor.sigla)}</small>
    `;
    return link;
}

function configurarFavoritosERecentes() {
    const setores = obterSetoresParaBusca();
    const setoresPorId = {};
    setores.forEach((setor) => { setoresPorId[setor.id] = setor; });

    /* Registra a visita atual (se estivermos numa página de setor) */
    const marcador = document.querySelector('#setor-atual-id');
    if (marcador) {
        const idAtual = marcador.dataset.id;
        let recentes = lerListaSalva('guiaifro-recentes').filter((id) => id !== idAtual);
        recentes.unshift(idAtual);
        recentes = recentes.slice(0, 6);
        salvarListaRecentes(recentes);
    }

    /* Botão de favoritar (existe só na página de setor) */
    const botaoFavoritar = document.querySelector('[data-favoritar-id]');
    if (botaoFavoritar) {
        const idBotao = botaoFavoritar.dataset.favoritarId;
        const atualizarBotao = () => {
            const favoritos = lerListaSalva('guiaifro-favoritos');
            const ativo = favoritos.includes(idBotao);
            botaoFavoritar.classList.toggle('ativo', ativo);
            botaoFavoritar.setAttribute('aria-pressed', String(ativo));
        };
        atualizarBotao();

        botaoFavoritar.addEventListener('click', () => {
            let favoritos = lerListaSalva('guiaifro-favoritos');
            if (favoritos.includes(idBotao)) {
                favoritos = favoritos.filter((id) => id !== idBotao);
            } else {
                favoritos.push(idBotao);
            }
            salvarListaFavoritos(favoritos);
            atualizarBotao();
        });
    }

    /* Preenche as seções da home, se existirem nesta página */
    const secaoFavoritos = document.querySelector('#secao-favoritos');
    const listaFavoritos = document.querySelector('#lista-favoritos');
    if (secaoFavoritos && listaFavoritos) {
        const favoritos = lerListaSalva('guiaifro-favoritos').map((id) => setoresPorId[id]).filter(Boolean);
        if (favoritos.length) {
            favoritos.forEach((setor) => listaFavoritos.appendChild(montarCardSetor(setor)));
            secaoFavoritos.hidden = false;
        }
    }

    const secaoRecentes = document.querySelector('#secao-recentes');
    const listaRecentes = document.querySelector('#lista-recentes');
    if (secaoRecentes && listaRecentes) {
        const recentes = lerListaSalva('guiaifro-recentes').map((id) => setoresPorId[id]).filter(Boolean);
        if (recentes.length) {
            recentes.forEach((setor) => listaRecentes.appendChild(montarCardSetor(setor)));
            secaoRecentes.hidden = false;
        }
    }

    atualizarIcones();
}

/* VÍDEO DO HERO DA HOME
   O vídeo já toca sozinho (autoplay no HTML) e para no logo.
   Aqui só: passar o mouse toca de novo e inclina o card. */
function configurarVideoDaHome() {
    const video = document.querySelector('#hero-video');
    const card = document.querySelector('#hero-video-card');
    if (!video || !card) return;

    // "Menos movimento" ligado no Guia ou no aparelho: mostra só a imagem parada
    if (document.documentElement.classList.contains('reduzir-movimento') || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        video.pause();
        video.removeAttribute('autoplay');
        video.poster = video.dataset.poster;
        video.preload = 'none';
        return;
    }

    card.addEventListener('mouseenter', () => {
        if (video.ended) {
            video.currentTime = 0;
            video.play().catch(() => {});
        }
    });

    card.addEventListener('mousemove', (evento) => {
        const area = card.getBoundingClientRect();
        const x = (evento.clientX - area.left) / area.width - 0.5;
        const y = (evento.clientY - area.top) / area.height - 0.5;
        card.style.setProperty('--tilt-y', (x * 16).toFixed(1) + 'deg');
        card.style.setProperty('--tilt-x', (-y * 12).toFixed(1) + 'deg');
    });

    card.addEventListener('mouseleave', () => {
        card.style.removeProperty('--tilt-y');
        card.style.removeProperty('--tilt-x');
    });
}

/* RODAPÉ: no celular cada bloco (Navegação, Acesso rápido, Contato) vira um item que abre e fecha.
   No computador fica tudo aberto e não fecha. Sem JavaScript, fica tudo aberto. */
function configurarRodape() {
    const blocos = document.querySelectorAll('.footer-sec');
    if (!blocos.length || !window.matchMedia) return;
    const celular = window.matchMedia('(max-width: 680px)');
    const ajustar = () => blocos.forEach((bloco) => { bloco.open = !celular.matches; });
    ajustar();
    if (celular.addEventListener) celular.addEventListener('change', ajustar);
    else if (celular.addListener) celular.addListener(ajustar);
    blocos.forEach((bloco) => {
        bloco.querySelector('summary')?.addEventListener('click', (evento) => {
            if (!celular.matches) evento.preventDefault();
        });
    });
}

/* HOJE NO CAMPUS (home): atualiza sozinho a cada 5 minutos, sem recarregar a página. */
function configurarHojeNoCampus() {
    const secao = document.querySelector('[data-atualizar-hoje]');
    if (!secao) return;
    window.setInterval(async () => {
        if (document.hidden || !navigator.onLine) return;
        try {
            const resposta = await fetch('hoje.php', { cache: 'no-store' });
            if (!resposta.ok) return;
            // HTML montado pelo nosso servidor (view/parciais/hoje-no-campus.php), com tudo já escapado.
            const html = await resposta.text();
            const atual = secao.querySelector('#hoje-no-campus-conteudo');
            if (atual && html.includes('hoje-no-campus-conteudo')) {
                atual.outerHTML = html;
                atualizarIcones();
            }
        } catch (erro) {
            // sem internet: fica o que já estava na tela
        }
    }, 5 * 60 * 1000);
}

/* PREFERÊNCIAS DE ACESSIBILIDADE (I1): alto contraste, fonte de leitura fácil,
   menos movimento. Classe no <html> + localStorage ('1' ligado, '0' desligado).
   Sem escolha salva, o <head> já aplicou o que o aparelho pede. */
function configurarPreferencias() {
    const html = document.documentElement;
    const botoes = document.querySelectorAll('[data-preferencia]');
    if (!botoes.length) return;

    const atualizarBotoes = () => {
        botoes.forEach((botao) => botao.setAttribute('aria-pressed', String(html.classList.contains(botao.dataset.preferencia))));
    };

    botoes.forEach((botao) => {
        botao.addEventListener('click', () => {
            const nome = botao.dataset.preferencia;
            const ligado = html.classList.toggle(nome);
            try { localStorage.setItem(`guiaifro-pref-${nome}`, ligado ? '1' : '0'); } catch (erro) { /* modo privado */ }
            if (nome === 'fonte-leitura' && ligado && !document.querySelector('link[href*="Atkinson"]')) {
                const folha = document.createElement('link');
                folha.rel = 'stylesheet';
                folha.href = 'https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&display=swap';
                document.head.appendChild(folha);
            }
            if (nome === 'reduzir-movimento' && ligado) {
                document.querySelector('#hero-video')?.pause();
            }
            atualizarBotoes();
        });
    });
    atualizarBotoes();
}

/* Campo que manda o formulário assim que muda (ex.: filtro "só sem escada" da busca) */
document.addEventListener('change', (evento) => {
    if (evento.target.matches?.('[data-enviar-ao-mudar]') && evento.target.form) evento.target.form.submit();
});
