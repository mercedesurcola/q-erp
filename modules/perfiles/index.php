<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'perfiles', 'ver');

$tituloPagina = 'Perfiles';
$eyebrowPagina = 'Administración';
$slugSeccionActual = 'perfiles';

$puedeCrear  = tienePermiso($pdo, 'perfiles', 'crear');
$puedeEditar = tienePermiso($pdo, 'perfiles', 'editar');
$puedeBorrar = tienePermiso($pdo, 'perfiles', 'eliminar');

$mensaje = $_SESSION['flash_ok'] ?? null;
unset($_SESSION['flash_ok']);

$perfiles = $pdo->query(
    "SELECT p.*, (SELECT COUNT(*) FROM usuarios u WHERE u.perfil_id = p.id) AS cant_usuarios
     FROM perfiles p ORDER BY p.nombre"
)->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Perfiles y permisos</h3>
        <?php if ($puedeCrear): ?>
            <a href="nuevo.php" class="btn btn-primary">+ Nuevo perfil</a>
        <?php endif; ?>
    </div>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-ok"><?= e($mensaje) ?></div>
    <?php endif; ?>

    <table class="tabla-qerp">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Usuarios</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($perfiles as $p): ?>
            <tr>
                <td><?= e($p['nombre']) ?></td>
                <td style="color:var(--muted);"><?= e($p['descripcion'] ?? '') ?></td>
                <td><?= (int) $p['cant_usuarios'] ?></td>
                <td>
                    <span class="badge <?= $p['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                        <?= $p['activo'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </td>
                <td style="text-align:right;white-space:nowrap;">
                    <?php if ($puedeEditar): ?>
                        <a href="permisos.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm">Permisos</a>
                        <a href="editar.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                    <?php endif; ?>
                    <?php if ($puedeBorrar): ?>
                        <form method="post" action="eliminar.php" style="display:inline;"
                              onsubmit="return confirm('¿Eliminar este perfil? Los usuarios que lo tengan quedarán sin perfil asignado.');">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$perfiles): ?>
                <tr><td colspan="5" style="color:var(--muted);">Todavía no hay perfiles cargados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
