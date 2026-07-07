<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'usuarios', 'ver');

$tituloPagina = 'Usuarios';
$eyebrowPagina = 'Administración';
$slugSeccionActual = 'usuarios';

$puedeCrear   = tienePermiso($pdo, 'usuarios', 'crear');
$puedeEditar  = tienePermiso($pdo, 'usuarios', 'editar');
$puedeBorrar  = tienePermiso($pdo, 'usuarios', 'eliminar');

$mensaje = $_SESSION['flash_ok'] ?? null;
unset($_SESSION['flash_ok']);

$usuarios = $pdo->query(
    "SELECT u.id, u.nombre, u.apellido, u.mail, u.activo, u.ultimo_acceso,
            p.nombre AS perfil_nombre
     FROM qerp_usuarios u
     LEFT JOIN qerp_perfiles p ON p.id = u.perfil_id
     ORDER BY u.apellido, u.nombre"
)->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Usuarios del sistema</h3>
        <?php if ($puedeCrear): ?>
            <a href="nuevo.php" class="btn btn-primary">+ Nuevo usuario</a>
        <?php endif; ?>
    </div>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-ok"><?= e($mensaje) ?></div>
    <?php endif; ?>

    <div class="tabla-responsive">
    <table class="tabla-qerp">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Perfil</th>
                <th>Estado</th>
                <th>Último acceso</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= e($u['nombre'] . ' ' . $u['apellido']) ?></td>
                <td><?= e($u['mail']) ?></td>
                <td><?= e($u['perfil_nombre'] ?? '—') ?></td>
                <td>
                    <span class="badge <?= $u['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                        <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </td>
                <td><?= $u['ultimo_acceso'] ? e(date('d/m/Y H:i', strtotime($u['ultimo_acceso']))) : '—' ?></td>
                <td style="text-align:right;white-space:nowrap;">
                    <?php if ($puedeEditar): ?>
                        <a href="editar.php?id=<?= (int) $u['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                    <?php endif; ?>
                    <?php if ($puedeBorrar): ?>
                        <form method="post" action="eliminar.php" style="display:inline;"
                              onsubmit="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.');">
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$usuarios): ?>
                <tr><td colspan="6" style="color:var(--muted);">Todavía no hay usuarios cargados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
