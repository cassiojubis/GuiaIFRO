<?php
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$tituloPagina = 'Sobre o IFRO';
$paginaAtual = 'sobre';

$valores = [
    'Educação pública, gratuita e de qualidade',
    'Inclusão social e democratização do conhecimento',
    'Ética, transparência e compromisso social',
    'Inovação e excelência no ensino e na pesquisa',
    'Desenvolvimento sustentável da região amazônica',
    'Respeito à diversidade e aos direitos humanos',
];

// [ícone, título, texto, link opcional, texto do link]. O texto é escapado na view, então link vai em campo próprio.
$pilaresInclusao = [
    ['accessibility', 'Acessibilidade', 'Tamanho de fonte ajustável e tradutor de Libras (VLibras) em todas as páginas.', 'acessibilidade.php', 'Ver opções de acessibilidade'],
    ['languages', 'Oro Wari (Pakaa Nova)', 'O povo Wari\' vive em Terras Indígenas próximas ao campus, como a TI Igarapé Lage e a TI Sagarana. A tradução do site só entra depois de validada com falantes da língua.', null, null],
    ['heart-handshake', 'NAPNE e CAED', 'O NAPNE apoia estudantes com deficiência, altas habilidades e transtornos de aprendizagem. O CAED dá suporte pedagógico, psicológico e social a todos. Os dois estão no mapa do campus.', 'mapa.php', 'Abrir o mapa'],
];

// cada marco tem uma imagem (campo 'imagem'); por enquanto aponta pra um placeholder
// em img/linha-tempo/<ano>.svg — é só trocar o arquivo pela foto real do campus quando tiver.
$linhaDoTempo = [
    ['2009', 'Primeiros Estudos', 'Início das articulações e estudos de viabilidade para a implantação do campus na região da fronteira.', 'img/linha-tempo/2008.svg'],
    ['2012', 'Doação do Terreno', 'A Câmara Municipal aprova a doação do terreno de 10 hectares destinado à construção da sede própria do campus.', 'img/linha-tempo/2010.svg'],
    ['10 de junho de 2015', 'Criação do Campus', 'Criação oficial do campus e início das atividades acadêmicas com as primeiras turmas da instituição.', 'img/linha-tempo/2014.svg'],
    ['9 de maio de 2016', 'Portaria nº 378', 'Publicação da Portaria nº 378, que formalizou o ato autorizativo de funcionamento da unidade.', 'img/linha-tempo/2018.webp'],
    ['Novembro de 2016', 'Sede Própria', 'Inauguração oficial da sede própria definitiva do campus.', 'img/linha-tempo/2022.webp'],
    ['Junho de 2025', '10 Anos de Campus', 'Celebração do marco de 10 anos de fundação e atuação da instituição no município.', 'img/linha-tempo/2026.svg'],
];

require __DIR__ . '/../view/sobre.php';
