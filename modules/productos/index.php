<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'productos', 'ver');

$tituloPagina = 'Productos/Servicios';
$eyebrowPagina = 'Configuración';
$slugSeccionActual = 'productos';

$puedeCrear  = tienePermiso($pdo, 'productos', 'crear');
$puedeEditar = tienePermiso($pdo, 'productos', 'editar');
$puedeBorrar = tienePermiso($pdo, 'productos', 'eliminar');

$mensaje = $_SESSION['flash_ok'] ?? null;
$error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

$productos = $pdo->query('SELECT * FROM qerp_productos ORDER BY nombre')->fetchAll();
$etiquetasTipo = ['producto' => 'Producto', 'servicio' => 'Servicio'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Productos/Servicios</h3>
        <?php if ($puedeCrear): ?>
            <a href="nuevo.php" class="btn btn-primary">+ Nuevo producto</a>
        <?php endif; ?>
    </div>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-ok"><?= e($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alerta alerta-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="tabla-responsive">
    <table class="tabla-qerp tabla-filtrable">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Precio</th>
                <th>Estado</th>
                <th class="th-acciones"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td>
                    <strong><?= e($p['nombre']) ?></strong>
                    <?php if ($p['detalle']): ?><br><span style="color:var(--muted);font-size:12px;"><?= e($p['detalle']) ?></span><?php endif; ?>
                </td>
                <td><?= e($etiquetasTipo[$p['tipo']] ?? $p['tipo']) ?></td>
                <td><?= $p['precio'] !== null ? '$ ' . number_format((float) $p['precio'], 2, ',', '.') : '—' ?></td>
                <td>
                    <span class="badge <?= $p['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                        <?= $p['activo'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </td>
                <td class="th-acciones" style="text-align:right;white-space:nowrap;">
                    <?php if ($puedeEditar): ?>
                        <a href="editar.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                    <?php endif; ?>
                    <?php if ($puedeBorrar): ?>
                        <form method="post" action="eliminar.php" style="display:inline;"
                              onsubmit="return confirm('¿Eliminar este producto/servicio?');">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$productos): ?>
                <tr><td colspan="5" style="color:var(--muted);">Todavía no hay productos/servicios cargados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
