<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'perfiles', 'crear');

$tituloPagina = 'Nuevo perfil';
$eyebrowPagina = 'Administración · Perfiles';
$slugSeccionActual = 'perfiles';

$errores = [];
$datos = ['nombre' => '', 'descripcion' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre']      = trim($_POST['nombre'] ?? '');
    $datos['descripcion'] = trim($_POST['descripcion'] ?? '');

    if ($datos['nombre'] === '') $errores[] = 'El nombre del perfil es obligatorio.';

    if (!$errores) {
        $stmt = $pdo->prepare('INSERT INTO perfiles (nombre, descripcion) VALUES (:nombre, :descripcion)');
        $stmt->execute([':nombre' => $datos['nombre'], ':descripcion' => $datos['descripcion']]);
        $nuevoId = (int) $pdo->lastInsertId();
        $_SESSION['flash_ok'] = 'Perfil creado. Ahora configurá sus permisos.';
        header('Location: permisos.php?id=' . $nuevoId);
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:520px;">
    <div class="card-header"><h3>Nuevo perfil</h3></div>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div class="campo">
            <label for="nombre">Nombre del perfil</label>
            <input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" required placeholder="Ej: Supervisor comercial">
        </div>
        <div class="campo">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3"><?= e($datos['descripcion']) ?></textarea>
        </div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Crear y configurar permisos</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
