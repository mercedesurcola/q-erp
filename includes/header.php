<?php
/**
 * QERP - Header / apertura de layout
 * Variables esperadas antes de incluir este archivo:
 *   $tituloPagina        (string) - Título mostrado en <title> y en el encabezado
 *   $eyebrowPagina        (string, opcional) - Texto pequeño sobre el título
 *   $slugSeccionActual    (string) - slug de la sección activa, para resaltar el menú
 */
requerirLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina ?? 'CUSOL') ?> · CUSOL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('/assets/css/style.css') ?>">
</head>
<body>
<div class="qerp-shell">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="qerp-main">
        <header class="qerp-header">
            <div>
                <?php if (!empty($eyebrowPagina)): ?>
                    <div class="eyebrow"><?= e($eyebrowPagina) ?></div>
                <?php endif; ?>
                <h1><?= e($tituloPagina ?? '') ?></h1>
            </div>
        </header>
        <main class="qerp-content">
