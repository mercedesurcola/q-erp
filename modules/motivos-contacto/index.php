<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'motivos-contacto', 'ver');

$tituloPagina = 'Motivos de contacto';
$eyebrowPagina = 'Administración';
$slugSeccionActual = 'motivos-contacto';

$puedeCrear  = tienePermiso($pdo, 'motivos-contacto', 'crear');
$puedeEditar = tienePermiso($pdo, 'motivos-contacto', 'editar');
$puedeBorrar = tienePermiso($pdo, 'motivos-contacto', 'eliminar');

$mensaje = $_SESSION['flash_ok'] ?? null;
$error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_ok'], $_SESSION['flash_error']);

$motivos = $pdo->query('SELECT * FROM qerp_motivos_contacto ORDER BY nombre')->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Motivos de contacto</h3>
        <?php if ($puedeCrear): ?>
            <a href="nuevo.php" class="btn btn-primary">+ Nuevo motivo</a>
        <?php endif; ?>
    </div>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-ok"><?= e($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alerta alerta-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="tabla-responsive">
    <table class="tabla-qerp">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($motivos as $m): ?>
            <tr>
                <td><?= e($m['nombre']) ?></td>
                <td>
                    <span class="badge <?= $m['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                        <?= $m['activo'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </td>
                <td style="text-align:right;white-space:nowrap;">
                    <?php if ($puedeEditar): ?>
                        <a href="editar.php?id=<?= (int) $m['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                    <?php endif; ?>
                    <?php if ($puedeBorrar): ?>
                        <form method="post" action="eliminar.php" style="display:inline;"
                              onsubmit="return confirm('¿Eliminar este motivo?');">
                            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$motivos): ?>
                <tr><td colspan="3" style="color:var(--muted);">Todavía no hay motivos cargados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
