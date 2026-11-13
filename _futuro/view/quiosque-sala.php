<?php $e = static function ($texto): string { return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8'); }; ?>
<div class="quiosque-destino">
    <div class="quiosque-destino-texto">
        <p class="quiosque-andar"><?= $e(($mapa['andares'][$sala['andar']]['nome'] ?? '') . ' · ala ' . $sala['ala']) ?></p>
        <h2><?= $e($setor['nome'] ?? $sala['nome']) ?></h2>
        <?php if ($setor && $setor['horario']): ?>
            <p class="quiosque-horario"><?= $e('Horário: ' . $setor['horario']) ?></p>
        <?php endif; ?>
        <ol class="quiosque-passos">
            <?php foreach ($passos as $passo): ?>
                <li><?= $e($passo) ?></li>
            <?php endforeach; ?>
        </ol>
        <div class="quiosque-qr">
            <?= qrCodeSvg($urlCelular, 170, 'QR code para ver este caminho no celular') ?>
            <p><strong>Leve o caminho no celular:</strong> aponte a câmera para o código.</p>
        </div>
    </div>
    <div class="quiosque-planta"><?= plantaSvg($mapa['salas'], $sala['andar'], $sala['id']) ?></div>
</div>
