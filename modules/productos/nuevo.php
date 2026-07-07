<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'productos', 'crear');

$tituloPagina = 'Nuevo producto';
$eyebrowPagina = 'Configuración · Productos/Servicios';
$slugSeccionActual = 'productos';

$errores = [];
$datos = ['nombre' => '', 'detalle' => '', 'tipo' => 'producto', 'precio' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre']  = trim($_POST['nombre'] ?? '');
    $datos['detalle'] = trim($_POST['detalle'] ?? '');
    $datos['tipo']    = $_POST['tipo'] ?? 'producto';
    $datos['precio']  = trim($_POST['precio'] ?? '');

    if ($datos['nombre'] === '') $errores[] = 'El nombre es obligatorio.';
    if (!in_array($datos['tipo'], ['producto', 'servicio'], true)) $errores[] = 'El tipo no es válido.';
    if ($datos['precio'] !== '' && !is_numeric($datos['precio'])) $errores[] = 'El precio debe ser un número.';

    if (!$errores) {
        $stmt = $pdo->prepare(
            'INSERT INTO qerp_productos (nombre, detalle, tipo, precio) VALUES (:nombre, :detalle, :tipo, :precio)'
        );
        $stmt->execute([
            ':nombre'  => $datos['nombre'],
            ':detalle' => $datos['detalle'] ?: null,
            ':tipo'    => $datos['tipo'],
            ':precio'  => $datos['precio'] !== '' ? $datos['precio'] : null,
        ]);
        $_SESSION['flash_ok'] = 'Producto creado correctamente.';
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:560px;">
    <div class="card-header"><h3>Nuevo producto</h3></div>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div class="fila-form">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" required>
            </div>
            <div class="campo">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo">
                    <option value="producto" <?= $datos['tipo'] === 'producto' ? 'selected' : '' ?>>Producto</option>
                    <option value="servicio" <?= $datos['tipo'] === 'servicio' ? 'selected' : '' ?>>Servicio</option>
                </select>
            </div>
            <div class="campo">
                <label for="precio">Precio</label>
                <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?= e($datos['precio']) ?>" placeholder="0.00">
            </div>
        </div>
        <div class="campo">
            <label for="detalle">Detalle</label>
            <textarea id="detalle" name="detalle" rows="3"><?= e($datos['detalle']) ?></textarea>
        </div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar producto</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
