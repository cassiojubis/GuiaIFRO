# Guia IFRO — Campus Guajará-Mirim

Sistema de navegação e informações do campus, em PHP puro (sem framework) com MySQL/MariaDB.

## Estrutura (MVC)

- `model/` — acesso a dados e regras.
  - `Config.php`: configurações gerais, fuso horário (America/Porto_Velho), cabeçalhos de segurança, helpers.
  - `Banco.php`: conexão PDO (credenciais em `config/banco.php`).
  - `Repositorio.php`: **único lugar com SQL**. Controllers e views só chamam funções daqui.
  - `Horario.php`: regra do selo "Aberto agora".
  - `Autenticacao.php`: login do admin e CSRF.
  - `Idiomas.php`: idioma escolhido (português, espanhol ou Oro Wari) e as traduções publicadas.
  - `QrCode.php` e `PlantaSvg.php`: QR da porta e planta da placa impressa, gerados no servidor.
- `view/` — só HTML. `view/parciais/` tem o cabeçalho e rodapé que se repetem em toda página.
- `controller/` — um controller por página, monta os dados e manda pra view certa.
- `js/` — `mapa-campus.js` (mapa do campus: planta 2D, 3D, rotas e o editor do admin), `traducao-es.js` (espanhol), `traducao-wari.js` (Oro Wari), `script.js` (resto do site).
- `config/` — segredos e conexão com o banco desta máquina (fora do git, bloqueado no navegador).
- `sql/` — `guiaifro.sql` (tabelas), `seed.php` e `dados-iniciais.php` (o que entra num banco vazio). Desenho das tabelas em `docs/BANCO.md`.
- `bin/` — scripts de terminal (instalar banco, gerar hash de senha, redefinir senha).
- `tests/` — testes simples, sem framework.
- `docs/PENDENCIAS.md` — **o que depende de informação do campus** (confira antes de publicar).

Os arquivos na raiz (`index.php`, `mapa.php`, `categorias.php`...) são o que o navegador acessa, mas cada um só chama o controller correspondente. O admin segue o mesmo esquema, dentro de `admin/`.

## Rodando local (XAMPP)

1. Coloque a pasta em `C:\xampp\htdocs\guiaIFRO` e ligue Apache e MySQL no painel do XAMPP.
2. Crie os arquivos de configuração (eles não vão pro git):
   - copie `config/segredos.exemplo.php` para `config/segredos.php` e preencha;
   - copie `config/banco.exemplo.php` para `config/banco.php`. Confira a **porta** (no XAMPP desta máquina o MariaDB está na 3307) e invente uma senha forte para o usuário do site.
3. Crie o banco, o usuário do site e as tabelas:
   ```
   C:\xampp\php\php.exe bin\instalar-banco.php
   ```
   Ele usa o `root` só pra criar as coisas (senha do root com `--admin-senha=...`). O site usa o usuário de `config/banco.php`, que só pode ler e gravar dados (nunca criar/apagar tabela).
   Para apagar o conteúdo e importar de novo: `--recriar` (não apaga usuários nem mensagens recebidas).
4. Abra `http://localhost/guiaIFRO/`.

### Se o instalador der "Read page with wrong checksum"

É um problema conhecido do XAMPP: as tabelas de permissão do MariaDB corrompem. Faça backup dos bancos importantes e rode:
```
C:\xampp\mysql\bin\mysqlcheck.exe -h 127.0.0.1 -P 3307 -u root -p --repair mysql db tables_priv proxies_priv
```
Depois rode o instalador de novo.

## Formulário de contato

`contato.php` envia de verdade para `processar-contato.php`: valida tudo no servidor, confere o token CSRF, tem um campo-isca contra robô e aceita no máximo 3 mensagens por hora por pessoa (o IP é guardado só como hash). A mensagem fica gravada no banco e aparece no admin; se em Configurações houver um e-mail de destino, ele também recebe um aviso (se o `mail()` falhar, a mensagem continua gravada). Funciona com e sem JavaScript.

## Segurança

- `.htaccess` na raiz bloqueia listagem de pastas e o acesso a `config/`, `sql/`, `bin/`, `tests/`, `logs/`, `.git` e arquivos `.sql/.log/.env`. `model/`, `controller/` e `docs/` têm o seu próprio bloqueio.
- Cabeçalhos em toda página: Content-Security-Policy (com nonce nos scripts inline), X-Content-Type-Options, X-Frame-Options e Referrer-Policy (ver `enviarCabecalhosSeguranca()` em `model/Config.php`).
- CSRF em todo formulário POST (`exigirCsrf()` / `campoCsrf()` em `model/Autenticacao.php`); o logout também é POST com token.
- Ícones: desenhados no servidor por `icone()` (`model/Config.php`) a partir de `model/icones-lucide.json` (Lucide 0.468). O site público não baixa mais o `lucide.min.js` (349 KB); só o admin usa.
- `sitemap.xml` e `robots.txt` são gerados pelo PHP. Antes de publicar, preencha `URL_SITE_CONFIGURADA` em `model/Config.php` com o domínio real.

## Acessibilidade

Tem um botão flutuante em todas as páginas (canto inferior direito) pra aumentar a fonte, e a página `acessibilidade.php` com essa opção mais o seletor de idioma (ver "Idiomas" abaixo). O VLibras é carregado só depois da página pronta (`js/vlibras.js`), e se o site do governo cair nada quebra.

## Admin (admin/)

Tudo que se salva no admin vai pro banco e aparece no site na hora. Cada alteração fica registrada (tabela `auditoria`) e as últimas aparecem no Dashboard.

- **Perfis:** *administrador geral* (pode tudo) e *responsável de setor* (só edita os setores ligados a ele: descrição, contato, horário, equipe, serviços e documentos).
- **Telas:** Dashboard (números reais, alterações recentes, setores sem informação), Setores (+ Horários por dia e feriados/exceções), Categorias, Editor do mapa (desenho do campus: prédios, salas, calçadas, portão), Salas (ficha, QR e acessibilidade de cada sala), Como faço para…, Informações (avisos, notícias, FAQ, links, institucional), Contatos e mensagens (mensagens do formulário do site), Traduções, Usuários, Configurações e Meus dados.
- **Segurança:** senha com `password_hash`; bloqueio de 15 min depois de 5 senhas erradas por usuário+IP (e 20 por IP), guardado no banco; sessão expira em 30 min parado (7 dias com "lembrar acesso"); "sair de todos os aparelhos" em Meus dados; política de senha (10+ caracteres, letras e números); CSRF em todo formulário e `fetch`.

### Primeiro acesso e senha

O `bin/instalar-banco.php` cria o primeiro administrador com o usuário e o hash de `config/segredos.php` (`admin_usuario` / `admin_senha_hash`). Depois disso a senha é trocada em **admin › Meus dados** (o `segredos.php` não é mais lido no login).

**Troque a senha padrão logo no primeiro acesso.** O painel obriga: quem entra com a senha padrão (ou fraca) fica em Meus dados até trocar.

Ninguém consegue entrar? No terminal: `C:\xampp\php\php.exe bin\redefinir-senha.php admin` (troca a senha, reativa o usuário e zera os bloqueios).

### Horários

Cada setor tem horário por dia da semana (admin › Setores › botão do relógio), com várias faixas por dia e exceções (feriado, recesso, horário especial). Exceção marcada para o campus todo (sem setor) fecha todos os setores e aparece em "Hoje no campus". O selo da página do setor mostra "Aberto agora · fecha às 17h" / "Fechado · abre amanhã às 7h". Setor sem horário por dia continua usando o texto antigo.

## Funcionalidades para quem usa o site

| Página | O que faz |
|---|---|
| Home (`index.php`) | O mapa interativo fica logo no topo, abaixo do título e da busca: a pessoa já escolhe a sala e vê o caminho. `index.php?sala=<sala>&de=<sala>` abre com a rota, igual à página do mapa. |
| Busca (cabeçalho e `categorias.php?q=`) | Ignora acento; acha por sigla, nome, palavra de busca (sinônimo), serviço, pergunta do FAQ e descrição. Mostra o andar e "Ver no mapa". |
| `mapa.php?de=<sala>&sala=<sala>` | O mesmo mapa da home, em tela cheia (`view/parciais/mapa-interativo.php` + `controller/MapaDados.php`, usados pelas duas páginas). Rota de qualquer sala (ou do portão/entrada) até qualquer sala, no 2D e no 3D, com passo a passo, distância e tempo a pé. Em **Saindo de** a pessoa escolhe de onde sai: portão, entrada de um prédio, qualquer sala de qualquer andar ou outro lugar (cantina, bebedouro…); "Estou aqui: sair daqui" faz o mesmo a partir da sala aberta. O endereço guarda `sala` e `de`, então recarregar ou compartilhar o link mantém a rota (ver "Mapa do campus" abaixo). |
| `mapa-texto.php` | O mesmo mapa em forma de lista, para leitor de tela ou celular simples. |
| `qr.php?sala=<id>` | QR code da porta (gerado no servidor, `model/QrCode.php`). Abre a página com "Você está aqui, quero ir para…". |
| `impressao.php?sala=<id>` / `?todas=1` | Placa A4 da porta: nome, andar, horário, planta com "você está aqui" e QR. Também em admin › Salas (ficha e QR). |
| Rodapé | No celular fica curto: marca e redes numa linha, e Navegação / Acesso rápido / Contato abrem e fecham. No computador fica tudo aberto. |
| Home › Hoje no campus | Data, feriado ou horário especial do campus, setores abertos agora e a próxima data especial (exceções do campus todo). Atualiza sozinho a cada 5 min (`hoje.php`). |
| `noticias.php`, `noticia.php` | Avisos (urgente vira faixa no topo do site) e notícias com página própria. |
| Página do setor | Contato, horário com selo "Aberto agora", localização com "Ver no mapa", serviços, equipe, perguntas frequentes, "Atualizado em … por …" e os botões "Mandar dúvida" / "Avisar informação errada" (abrem o Contato com o assunto já escolhido). |
| `estrangeiros.php` | Para quem vem de outro país (português e espanhol): documentos, chegada e onde ficam a CRA e a CAED. |
| `como-faco.php` | Passo a passo (declaração, matrícula, trancamento…) com setor, prazo, documentos e caminho no mapa. |

## Idiomas

- **Espanhol**: `js/traducao-es.js` traduz os textos fixos das páginas (veio do Guia antigo) e o mapa dá o passo a passo em espanhol. Frase que não estiver no dicionário fica em português.
- **Oro Wari (Pakaa Nova)**: as traduções do documento *tradução oro wari (pakaa nova).pdf* estão em `model/idiomas/wari.php` (cada linha do documento; as que ficaram em branco estão com `''` e aparecem em português). O `js/traducao-wari.js` troca só a frase inteira que for igual à do documento — nunca pedaço de frase — e o menu/rodapé vem pronto do PHP (`t()`). Com o Oro Wari ligado aparece uma faixa avisando que a tradução está em validação. Pra ver o que falta: `C:\xampp\php\php.exe bin\relatorio-traducao-wari.php --salvar` (gera `docs/TRADUCAO-ORO-WARI.md`). Chegou tradução nova? Preencha a linha em `model/idiomas/wari.php` e rode o relatório.
- Também dá pra traduzir em admin › Traduções: cada texto passa por rascunho → revisado → publicado, e o Oro Wari só é publicado com a confirmação de que um falante revisou. O que for publicado lá ganha do arquivo. Dá pra anexar um áudio gravado pelo falante, que o botão "Ouvir" toca no lugar da voz do navegador.
- O idioma escolhido fica na sessão e num cookie `guiaifro_idioma` (1 ano, só o código do idioma).
- Textos do menu e conteúdo dos setores (nome, descrição, leitura fácil) também podem ser traduzidos por essa tela. O que não tiver tradução publicada aparece em português.

## Mapa do campus

- **Dados**: `data/mapa-campus.json` (gravado pelo admin › Editor do mapa › Salvar › "Salvar no site"). Enquanto ninguém salvar, vale o `model/mapa-campus-padrao.json`. O servidor confere tudo antes de gravar (`model/MapaCampus.php`) e guarda a versão anterior em `data/mapa-campus-anterior.json`. A pasta `data/` não abre pela URL.
- **Desenho**: `js/mapa-campus.js` + `css/mapa-campus.css`, os mesmos no site (só encontrar) e no editor. Medidas em metros; prédios feitos de partes (retângulos), com portas, escada e salas por andar.
- **Rota**: o campus vira uma grade de 0,5 m e a rota é o caminho de menor custo pelo **A\*** (calçada custa menos que grama; parede e sala bloqueiam; a rota entra pelas portas). Com "Saindo de", a rota é dividida em **um trecho por andar**: ex.: sair de uma sala do 1º andar, descer a escada, atravessar o pátio e subir em outro prédio. O 2D mostra o trecho do andar escolhido (com "↑ suba"/"↓ desça a escada"), o 3D mostra os trechos até o andar à mostra, e o passo a passo diz direita/esquerda, prédios e pátio no caminho e de que lado fica a porta.
- **Ligação com o banco**: as salas do prédio principal usam o mesmo código da tabela `sala` (cra, napne, biblioteca…), por isso `mapa.php?sala=cra`, o QR da porta e o "Estou aqui" (`?de=`) abrem a sala certa. Ficha, função, setor e acessibilidade da sala continuam no admin › Salas.
- O antigo mapa só do prédio principal (`js/mapa.js`, `mapa2d.js`, `mapa3d.js`, `css/mapa.css`) não é mais carregado pelas páginas; o `js/grafo.js` ficou só pro `tests/grafo_test.php`.

## Testes

```
C:\xampp\php\php.exe tests\horario_test.php
C:\xampp\php\php.exe tests\qrcode_test.php
C:\xampp\php\php.exe tests\grafo_test.php
```

O `grafo_test.php` abre o `tests/grafo_test.html` no Chrome (ou Edge) sem janela, porque o Dijkstra é JavaScript. Também dá pra abrir o `.html` direto no navegador.

## Escopo e pasta `_futuro/`

O Guia IFRO entrega: achar o setor certo, saber quando ele está aberto e como chegar até lá, em português, espanhol ou Oro Wari, com recursos de acessibilidade.

Recursos que fugiam disso foram tirados do site em 04/11/2026 e guardados em `_futuro/` (a pasta não abre pela URL), com o mesmo caminho de antes: calendário acadêmico e `.ics`, Primeiro dia (passaporte de selos), quadro de horários por CSV / Minha turma / Próxima aula, modo offline (PWA), estatísticas e contador, quiosque da porta, Pergunte ao setor, Informar erro (viraram assuntos do Contato) e o aviso de revisão por e-mail. As tabelas deles estão em `_futuro/sql/tabelas-recursos-futuros.sql`.

O `sw.js` que ficou na raiz só desinstala o modo offline de quem chegou a usar a versão antiga.
