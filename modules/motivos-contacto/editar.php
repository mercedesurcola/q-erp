<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'motivos-contacto', 'editar');

$tituloPagina = 'Editar motivo';
$eyebrowPagina = 'Administración · Motivos de contacto';
$slugSeccionActual = 'motivos-contacto';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM qerp_motivos_contacto WHERE id = :id');
$stmt->execute([':id' => $id]);
$motivo = $stmt->fetch();

if (!$motivo) {
    header('Location: index.php');
    exit;
}

$errores = [];
$datos = ['nombre' => $motivo['nombre'], 'activo' => $motivo['activo']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre'] = trim($_POST['nombre'] ?? '');
    $datos['activo'] = isset($_POST['activo']) ? 1 : 0;

    if ($datos['nombre'] === '') $errores[] = 'El nombre es obligatorio.';

    if (!$errores) {
        $stmt = $pdo->prepare('UPDATE qerp_motivos_contacto SET nombre = :nombre, activo = :activo WHERE id = :id');
        $stmt->execute([':nombre' => $datos['nombre'], ':activo' => $datos['activo'], ':id' => $id]);
        $_SESSION['flash_ok'] = 'Motivo actualizado correctamente.';
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:480px;">
    <div class="card-header"><h3>Editar motivo</h3></div>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" required>
        </div>
        <div class="campo">
            <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                <input type="checkbox" name="activo" style="width:auto;" <?= $datos['activo'] ? 'checked' : '' ?>>
                Activo
            </label>
        </div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
