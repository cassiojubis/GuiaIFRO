// Botão "Imprimir" da página de placas (impressao.php). Fica num arquivo porque a CSP não deixa script inline.
document.getElementById('botao-imprimir')?.addEventListener('click', () => window.print());
