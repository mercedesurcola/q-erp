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
    'tag'         => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.6 12.6 12 21.2a2 2 0 0 1-2.8 0l-7.4-7.4a2 2 0 0 1 0-2.8L10.4 2.4a2 2 0 0 1 1.4-.6H19a2 2 0 0 1 2 2v6.6a2 2 0 0 1-.6 1.4z"/><circle cx="15" cy="7" r="1.5"/></svg>',
    'flag'        => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22V4a1 1 0 0 1 1.4-.9C7.6 4 10.4 5.5 13 4.5c2.6-1 5-1 7 0v10c-2-1-4.4-1-7 0-2.6 1-5.4-.5-7.6-1.4"/></svg>',
    'box'         => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8 12 3 3 8v8l9 5 9-5V8z"/><path d="M3 8l9 5 9-5M12 13v8"/></svg>',
];

$slugActual = $slugSeccionActual ?? '';

$grupos = [];
foreach ($secciones as $s) {
    $grupos[$s['grupo'] ?? 'General'][] = $s;
}
$ordenGrupos = ['CRM', 'Configuración', 'Administración'];
foreach (array_keys($grupos) as $g) {
    if (!in_array($g, $ordenGrupos, true)) {
        $ordenGrupos[] = $g;
    }
}
?>
<aside class="qerp-sidebar" id="qerpSidebar">
    <div class="brand">
        <img src="<?= QERP_URL_BASE ?>/assets/img/logo-cusol.png" alt="CUSOL" class="logo-sidebar">
    </div>
    <nav class="qerp-nav">
        <?php foreach ($ordenGrupos as $grupo): ?>
            <?php
                $items = array_filter($grupos[$grupo] ?? [], fn($s) => tienePermiso($pdo, $s['slug'], 'ver'));
                if (!$items) continue;
            ?>
            <div class="seccion-titulo"><?= e($grupo) ?></div>
            <?php foreach ($items as $s): ?>
                <a href="<?= QERP_URL_BASE ?>/modules/<?= e($s['slug']) ?>/index.php"
                   class="<?= $slugActual === $s['slug'] ? 'activo' : '' ?>">
                    <?= $iconos[$s['icono']] ?? '' ?>
                    <?= e($s['nombre']) ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <div class="usuario-box">
        <div class="nombre"><?= e($usuario['nombre'] . ' ' . $usuario['apellido']) ?></div>
        <div class="perfil"><?= e($usuario['perfil']) ?></div>
        <a class="salir" href="<?= QERP_URL_BASE ?>/login/logout.php">Cerrar sesión</a>
    </div>
</aside>
