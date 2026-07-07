<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'resultados-contacto', 'ver');

$tituloPagina = 'Resultados de contacto';
$eyebrowPagina = 'Administración';
$slugSeccionActual = 'resultados-contacto';

$puedeCrear  = tienePermiso($pdo, 'resultados-contacto', 'crear');
$puedeEditar = tienePermiso($pdo, 'resultados-contacto', 'editar');
$puedeBorrar = tienePermiso($pdo, 'resultados-contacto', 'eliminar');

$mensaje = $_SESSION['flash_ok'] ?? null;
$error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

$resultados = $pdo->query('SELECT * FROM qerp_resultados_contacto ORDER BY nombre')->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Resultados de contacto</h3>
        <?php if ($puedeCrear): ?>
            <a href="nuevo.php" class="btn btn-primary">+ Nuevo resultado</a>
        <?php endif; ?>
    </div>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-ok"><?= e($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alerta alerta-error"><?= e($error) ?></div>
    <?php endif; ?>

    <table class="tabla-qerp">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultados as $r): ?>
            <tr>
                <td><?= e($r['nombre']) ?></td>
                <td>
                    <span class="badge <?= $r['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                        <?= $r['activo'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </td>
                <td style="text-align:right;white-space:nowrap;">
                    <?php if ($puedeEditar): ?>
                        <a href="editar.php?id=<?= (int) $r['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                    <?php endif; ?>
                    <?php if ($puedeBorrar): ?>
                        <form method="post" action="eliminar.php" style="display:inline;"
                              onsubmit="return confirm('¿Eliminar este resultado?');">
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$resultados): ?>
                <tr><td colspan="3" style="color:var(--muted);">Todavía no hay resultados cargados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
