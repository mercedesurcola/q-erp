<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'perfiles', 'editar');

$tituloPagina = 'Editar perfil';
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

$errores = [];
$datos = [
    'nombre'               => $perfil['nombre'],
    'descripcion'          => $perfil['descripcion'],
    'activo'               => $perfil['activo'],
    'solo_ve_sus_clientes' => $perfil['solo_ve_sus_clientes'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre']               = trim($_POST['nombre'] ?? '');
    $datos['descripcion']          = trim($_POST['descripcion'] ?? '');
    $datos['activo']               = isset($_POST['activo']) ? 1 : 0;
    $datos['solo_ve_sus_clientes'] = isset($_POST['solo_ve_sus_clientes']) ? 1 : 0;

    if ($datos['nombre'] === '') $errores[] = 'El nombre del perfil es obligatorio.';

    if (!$errores) {
        $stmt = $pdo->prepare(
            'UPDATE qerp_perfiles SET nombre = :nombre, descripcion = :descripcion, activo = :activo,
             solo_ve_sus_clientes = :solo_ve_sus_clientes WHERE id = :id'
        );
        $stmt->execute([
            ':nombre'               => $datos['nombre'],
            ':descripcion'          => $datos['descripcion'],
            ':activo'               => $datos['activo'],
            ':solo_ve_sus_clientes' => $datos['solo_ve_sus_clientes'],
            ':id'                   => $id,
        ]);
        $_SESSION['flash_ok'] = 'Perfil actualizado correctamente.';
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:520px;">
    <div class="card-header"><h3>Editar perfil</h3></div>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div class="campo">
            <label for="nombre">Nombre del perfil</label>
            <input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" required>
        </div>
        <div class="campo">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3"><?= e($datos['descripcion']) ?></textarea>
        </div>
        <div class="campo">
            <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                <input type="checkbox" name="activo" style="width:auto;" <?= $datos['activo'] ? 'checked' : '' ?>>
                Perfil activo
            </label>
        </div>
        <div class="campo">
            <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                <input type="checkbox" name="solo_ve_sus_clientes" style="width:auto;" <?= $datos['solo_ve_sus_clientes'] ? 'checked' : '' ?>>
                Solo ve sus clientes
            </label>
            <p style="color:var(--muted); font-size:12.5px; margin:4px 0 0;">
                Si está activado, los usuarios con este perfil solo ven en Clientes y CRM los registros donde figuran como responsable.
            </p>
        </div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="permisos.php?id=<?= $id ?>" class="btn btn-outline">Configurar permisos</a>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
