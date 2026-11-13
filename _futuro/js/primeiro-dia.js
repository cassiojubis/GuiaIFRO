// "PRIMEIRO DIA" (primeiro-dia.php): marca as paradas feitas e guarda os selos
// do passaporte. Tudo fica só no aparelho (localStorage), nada vai pro servidor.
(() => {
    const paradas = Array.from(document.querySelectorAll('[data-parada]'));
    if (!paradas.length) return;

    const CHAVE_FEITAS = 'guiaifro-primeiro-dia';
    const CHAVE_SELOS = 'guiaifro-passaporte';
    const ler = (chave) => {
        try {
            const valor = JSON.parse(localStorage.getItem(chave));
            return Array.isArray(valor) ? valor.map(String) : [];
        } catch (erro) {
            return [];
        }
    };
    const gravar = (chave, lista) => {
        try { localStorage.setItem(chave, JSON.stringify(lista)); } catch (erro) { /* modo privado: só não salva */ }
    };

    // Abriu pelo QR escondido? Guarda o selo (e conta a parada como feita).
    const seloGanho = document.getElementById('selo-ganho');
    if (seloGanho) {
        const id = seloGanho.dataset.seloId;
        gravar(CHAVE_SELOS, Array.from(new Set([...ler(CHAVE_SELOS), id])));
        gravar(CHAVE_FEITAS, Array.from(new Set([...ler(CHAVE_FEITAS), id])));
    }

    const atualizar = () => {
        const feitas = ler(CHAVE_FEITAS);
        const selos = ler(CHAVE_SELOS);
        paradas.forEach((parada) => {
            const id = parada.dataset.parada;
            const feita = feitas.includes(id);
            parada.classList.toggle('feita', feita);
            const botao = parada.querySelector('.primeiro-dia-feito');
            botao.hidden = false;
            botao.setAttribute('aria-pressed', String(feita));
            botao.querySelector('span').textContent = feita ? 'Feito!' : 'Marcar como feito';
            parada.querySelector('.primeiro-dia-selo').hidden = !selos.includes(id);
        });
        const total = paradas.length;
        const quantas = paradas.filter((parada) => feitas.includes(parada.dataset.parada)).length;
        document.getElementById('primeiro-dia-progresso').hidden = false;
        document.getElementById('primeiro-dia-contagem').textContent = `${quantas} de ${total}`;
        document.getElementById('primeiro-dia-selos').textContent = String(paradas.filter((parada) => selos.includes(parada.dataset.parada)).length);
        document.getElementById('primeiro-dia-barra').style.width = `${Math.round((quantas / total) * 100)}%`;
        document.querySelector('.primeiro-dia-barra').setAttribute('aria-valuenow', String(quantas));
        document.getElementById('primeiro-dia-fim').hidden = quantas < total;
    };

    paradas.forEach((parada) => {
        parada.querySelector('.primeiro-dia-feito').addEventListener('click', () => {
            const id = parada.dataset.parada;
            const feitas = ler(CHAVE_FEITAS);
            gravar(CHAVE_FEITAS, feitas.includes(id) ? feitas.filter((item) => item !== id) : [...feitas, id]);
            atualizar();
        });
    });

    document.getElementById('primeiro-dia-recomecar')?.addEventListener('click', () => {
        gravar(CHAVE_FEITAS, []);
        gravar(CHAVE_SELOS, []);
        atualizar();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    atualizar();
})();
