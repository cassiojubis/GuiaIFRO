<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= count($placas) === 1 ? 'Placa: ' . htmlspecialchars($placas[0]['titulo'], ENT_QUOTES, 'UTF-8') : 'Placas das portas' ?> | <?= NOME_SISTEMA ?></title>
    <!-- Página feita pra imprimir em A4: sem menu, sem rodapé, só a placa. -->
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, Arial, sans-serif; color: #0f172a; background: #e2e8f0; }
        .barra { display: flex; gap: 12px; align-items: center; justify-content: center; flex-wrap: wrap; padding: 14px; background: #0f7a43; color: #fff; }
        .barra button { padding: 10px 18px; border: 0; border-radius: 10px; background: #fff; color: #0f7a43; font: inherit; font-weight: 700; cursor: pointer; }
        .aviso { max-width: 186mm; margin: 12px auto 0; padding: 10px 14px; border-radius: 10px; background: #fff7e6; color: #7a4b00; border: 1px solid #f5d38a; }
        .placa { width: 186mm; min-height: 270mm; margin: 12px auto; padding: 12mm; background: #fff; display: flex; flex-direction: column; gap: 8mm; page-break-after: always; break-after: page; }
        .placa:last-child { page-break-after: auto; break-after: auto; }
        .topo { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #0f7a43; padding-bottom: 4mm; font-weight: 700; color: #0f7a43; }
        .sigla { font-size: 20mm; font-weight: 900; line-height: 1; color: #0f7a43; margin: 0; }
        h1 { font-size: 11mm; line-height: 1.1; margin: 0; }
        .local { font-size: 6mm; margin: 2mm 0 0; color: #334155; }
        .horario { font-size: 5mm; margin: 0; padding: 3mm 4mm; border-radius: 3mm; background: #f1f5f9; }
        .planta svg { width: 100%; height: auto; max-height: 95mm; }
        .rodape-placa { display: flex; gap: 6mm; align-items: center; margin-top: auto; }
        .rodape-placa svg { width: 45mm; height: 45mm; flex-shrink: 0; }
        .rodape-placa p { margin: 0 0 2mm; font-size: 5mm; }
        .rodape-placa small { font-size: 3.2mm; color: #475569; word-break: break-all; }
        @media print {
            body { background: #fff; }
            .barra, .aviso { display: none; }
            .placa { margin: 0; width: auto; min-height: auto; height: 270mm; }
        }
    </style>
</head>
<body>
    <div class="barra">
        <span><?= count($placas) === 1 ? '1 placa' : count($placas) . ' placas (uma por página)' ?></span>
        <button type="button" id="botao-imprimir">Imprimir</button>
    </div>
    <?php if ($enderecoLocal): ?>
        <p class="aviso"><strong>Atenção:</strong> o QR code está apontando para <?= htmlspecialchars(urlBaseSite(), ENT_QUOTES, 'UTF-8') ?>, que só funciona neste computador. Antes de imprimir as placas de verdade, configure o endereço público do site em <code>model/Config.php</code> (URL_SITE_CONFIGURADA).</p>
    <?php endif; ?>

    <?php foreach ($placas as $placa): ?>
        <section class="placa" aria-label="Placa de <?= htmlspecialchars($placa['titulo'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="topo"><span>IFRO · <?= NOME_CAMPUS ?></span><span><?= NOME_SISTEMA ?></span></div>
            <div>
                <?php if ($placa['sigla']): ?>
                    <p class="sigla"><?= htmlspecialchars($placa['sigla'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <h1><?= htmlspecialchars($placa['titulo'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="local"><?= htmlspecialchars($placa['andar'] . ' · ala ' . $placa['sala']['ala'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php if ($placa['horario'] !== ''): ?>
                <p class="horario"><strong>Atendimento:</strong> <?= htmlspecialchars($placa['horario'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <div class="planta"><?= $placa['planta'] /* SVG gerado em model/PlantaSvg.php, com os textos já escapados */ ?></div>
            <div class="rodape-placa">
                <?= $placa['qr'] /* SVG gerado em model/QrCode.php, só retângulos */ ?>
                <div>
                    <p><strong>Aponte a câmera do celular</strong> e veja o caminho daqui até qualquer setor do campus.</p>
                    <small><?= htmlspecialchars($placa['url'], ENT_QUOTES, 'UTF-8') ?></small>
                </div>
            </div>
        </section>
    <?php endforeach; ?>

    <script src="js/impressao.js"></script>
</body>
</html>
