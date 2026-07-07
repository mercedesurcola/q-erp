<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'perfiles', 'editar');

$tituloPagina = 'Permisos del perfil';
$eyebrowPagina = 'Administración · Perfiles';
$slugSeccionActual = 'perfiles';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM qerp_perfiles WHERE id = :id');
$stmt->execute([':id' => $id]);
$perfil = $stmt->fetch();

if (!$perfil) {
    header('Location: index.php');
    exit;
}

$secciones = $pdo->query('SELECT * FROM qerp_secciones ORDER BY orden')->fetchAll();

$mensaje = $_SESSION['flash_ok'] ?? null;
unset($_SESSION['flash_ok']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permisosPost = $_POST['permiso'] ?? []; // [seccion_id => [ver, crear, editar, eliminar]]

    $upsert = $pdo->prepare(
        'INSERT INTO qerp_perfil_permisos (perfil_id, seccion_id, ver, crear, editar, eliminar)
         VALUES (:perfil_id, :seccion_id, :ver, :crear, :editar, :eliminar)
         ON DUPLICATE KEY UPDATE ver = :ver2, crear = :crear2, editar = :editar2, eliminar = :eliminar2'
    );

    foreach ($secciones as $s) {
        $vals = $permisosPost[$s['id']] ?? [];
        $ver      = isset($vals['ver'])      ? 1 : 0;
        $crear    = isset($vals['crear'])    ? 1 : 0;
        $editar   = isset($vals['editar'])   ? 1 : 0;
        $eliminar = isset($vals['eliminar']) ? 1 : 0;

        $upsert->execute([
            ':perfil_id'  => $id,
            ':seccion_id' => $s['id'],
            ':ver' => $ver, ':crear' => $crear, ':editar' => $editar, ':eliminar' => $eliminar,
            ':ver2' => $ver, ':crear2' => $crear, ':editar2' => $editar, ':eliminar2' => $eliminar,
        ]);
    }

    $_SESSION['flash_ok'] = 'Permisos actualizados correctamente.';
    header('Location: permisos.php?id=' . $id);
    exit;
}

// Traer permisos actuales del perfil
$stmt = $pdo->prepare('SELECT * FROM qerp_perfil_permisos WHERE perfil_id = :id');
$stmt->execute([':id' => $id]);
$permisosActuales = [];
foreach ($stmt->fetchAll() as $row) {
    $permisosActuales[$row['seccion_id']] = $row;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Permisos de: <?= e($perfil['nombre']) ?></h3>
        <a href="index.php" class="btn btn-outline btn-sm">Volver</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-ok"><?= e($mensaje) ?></div>
    <?php endif; ?>

    <p style="color:var(--muted); margin-top:0;">
        Marcá qué puede hacer este perfil en cada sección del sistema.
    </p>

    <form method="post">
        <table class="tabla-qerp">
            <thead>
                <tr>
                    <th>Sección</th>
                    <th style="text-align:center;">Ver</th>
                    <th style="text-align:center;">Crear</th>
                    <th style="text-align:center;">Editar</th>
                    <th style="text-align:center;">Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($secciones as $s):
                    $actual = $permisosActuales[$s['id']] ?? ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0];
                ?>
                <tr>
                    <td><strong><?= e($s['nombre']) ?></strong></td>
                    <?php foreach (['ver', 'crear', 'editar', 'eliminar'] as $accion): ?>
                        <td style="text-align:center;">
                            <input type="checkbox" style="width:auto;"
                                   name="permiso[<?= (int) $s['id'] ?>][<?= $accion ?>]"
                                   <?= $actual[$accion] ? 'checked' : '' ?>>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar permisos</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
