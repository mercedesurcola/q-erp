<?php
/**
 * QERP - Sidebar de navegación
 * Requiere que $pdo y la sesión ya estén disponibles (incluir después de conexion.php y funciones.php)
 */
$usuario = usuarioActual();
$secciones = obtenerSeccionesMenu($pdo);

// Iconos simples (SVG inline, sin dependencias externas)
$iconos = [
    'users'       => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"/><circle cx="17" cy="7" r="3"/><path d="M22 21v-2a3.5 3.5 0 0 0-2-3.2"/></svg>',
    'shield'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"/></svg>',
    'briefcase'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
    'phone-call'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.1-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2z"/></svg>',
];

$slugActual = $slugSeccionActual ?? '';
?>
<aside class="qerp-sidebar" id="qerpSidebar">
    <div class="brand">
        <img src="<?= QERP_URL_BASE ?>/assets/img/logo-cusol.png" alt="CUSOL" class="logo-sidebar">
    </div>
    <nav class="qerp-nav">
        <div class="seccion-titulo">CRM</div>
        <?php foreach ($secciones as $s): ?>
            <?php if (!tienePermiso($pdo, $s['slug'], 'ver')) continue; ?>
            <a href="<?= QERP_URL_BASE ?>/modules/<?= e($s['slug']) ?>/index.php"
               class="<?= $slugActual === $s['slug'] ? 'activo' : '' ?>">
                <?= $iconos[$s['icono']] ?? '' ?>
                <?= e($s['nombre']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="usuario-box">
        <div class="nombre"><?= e($usuario['nombre'] . ' ' . $usuario['apellido']) ?></div>
        <div class="perfil"><?= e($usuario['perfil']) ?></div>
        <a class="salir" href="<?= QERP_URL_BASE ?>/login/logout.php">Cerrar sesión</a>
    </div>
</aside>
