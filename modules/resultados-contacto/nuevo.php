<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'resultados-contacto', 'crear');

$tituloPagina = 'Nuevo resultado';
$eyebrowPagina = 'Administración · Resultados de contacto';
$slugSeccionActual = 'resultados-contacto';

$errores = [];
$datos = ['nombre' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre'] = trim($_POST['nombre'] ?? '');

    if ($datos['nombre'] === '') $errores[] = 'El nombre es obligatorio.';

    if (!$errores) {
        $stmt = $pdo->prepare('INSERT INTO qerp_resultados_contacto (nombre) VALUES (:nombre)');
        $stmt->execute([':nombre' => $datos['nombre']]);
        $_SESSION['flash_ok'] = 'Resultado creado correctamente.';
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:480px;">
    <div class="card-header"><h3>Nuevo resultado</h3></div>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" required>
        </div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar resultado</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
