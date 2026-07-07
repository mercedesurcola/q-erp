<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'productos', 'editar');

$tituloPagina = 'Editar producto';
$eyebrowPagina = 'Configuración · Productos/Servicios';
$slugSeccionActual = 'productos';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM qerp_productos WHERE id = :id');
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: index.php');
    exit;
}

$errores = [];
$datos = [
    'nombre'  => $producto['nombre'],
    'detalle' => $producto['detalle'],
    'tipo'    => $producto['tipo'],
    'precio'  => $producto['precio'],
    'activo'  => $producto['activo'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre']  = trim($_POST['nombre'] ?? '');
    $datos['detalle'] = trim($_POST['detalle'] ?? '');
    $datos['tipo']    = $_POST['tipo'] ?? 'producto';
    $datos['precio']  = trim($_POST['precio'] ?? '');
    $datos['activo']  = isset($_POST['activo']) ? 1 : 0;

    if ($datos['nombre'] === '') $errores[] = 'El nombre es obligatorio.';
    if (!in_array($datos['tipo'], ['producto', 'servicio'], true)) $errores[] = 'El tipo no es válido.';
    if ($datos['precio'] !== '' && !is_numeric($datos['precio'])) $errores[] = 'El precio debe ser un número.';

    if (!$errores) {
        $stmt = $pdo->prepare(
            'UPDATE qerp_productos SET nombre = :nombre, detalle = :detalle, tipo = :tipo, precio = :precio, activo = :activo WHERE id = :id'
        );
        $stmt->execute([
            ':nombre'  => $datos['nombre'],
            ':detalle' => $datos['detalle'] ?: null,
            ':tipo'    => $datos['tipo'],
            ':precio'  => $datos['precio'] !== '' ? $datos['precio'] : null,
            ':activo'  => $datos['activo'],
            ':id'      => $id,
        ]);
        $_SESSION['flash_ok'] = 'Producto actualizado correctamente.';
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:560px;">
    <div class="card-header"><h3>Editar producto</h3></div>

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
                <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?= e((string) ($datos['precio'] ?? '')) ?>" placeholder="0.00">
            </div>
        </div>
        <div class="campo">
            <label for="detalle">Detalle</label>
            <textarea id="detalle" name="detalle" rows="3"><?= e($datos['detalle']) ?></textarea>
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
