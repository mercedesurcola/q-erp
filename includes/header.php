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
    <link rel="icon" type="image/png" href="<?= asset('/assets/img/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('/assets/img/apple-touch-icon.png') ?>">
    <link rel="stylesheet" href="<?= asset('/assets/css/style.css') ?>">
</head>
<body>
<div class="qerp-shell">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="menu-overlay" id="menuOverlay" hidden></div>
    <div class="qerp-main">
        <header class="qerp-header">
            <button type="button" class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div>
                <?php if (!empty($eyebrowPagina)): ?>
                    <div class="eyebrow"><?= e($eyebrowPagina) ?></div>
                <?php endif; ?>
                <h1><?= e($tituloPagina ?? '') ?></h1>
            </div>
        </header>
        <main class="qerp-content">
