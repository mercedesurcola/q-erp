<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'clientes', 'editar');

$tituloPagina = 'Editar cliente';
$eyebrowPagina = 'CRM · Clientes';
$slugSeccionActual = 'clientes';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM qerp_clientes WHERE id = :id');
$stmt->execute([':id' => $id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: index.php');
    exit;
}

requerirAccesoCliente($cliente['usuario_asignado'] !== null ? (int) $cliente['usuario_asignado'] : null);

$usuarios = $pdo->query("SELECT id, nombre, apellido FROM qerp_usuarios WHERE activo = 1 ORDER BY apellido")->fetchAll();
$errores = [];

$campos = ['nombre','razon_social','nombre_fantasia','cuit','mail','telefono','direccion','localidad','provincia','estado','origen','usuario_asignado','notas'];
$datos = [];
foreach ($campos as $c) {
    $datos[$c] = $cliente[$c];
}
$imagenActual = $cliente['imagen'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($campos as $c) {
        $datos[$c] = trim($_POST[$c] ?? '');
    }

    if ($datos['nombre'] === '') $errores[] = 'El nombre es obligatorio.';
    if ($datos['mail'] !== '' && !filter_var($datos['mail'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no es válido.';
    }

    $imagen = $imagenActual;
    if (!$errores) {
        try {
            $imagenNueva = procesarImagenCliente($_FILES['imagen'] ?? []);
            if ($imagenNueva !== null) {
                $imagen = $imagenNueva;
            }
        } catch (RuntimeException $e) {
            $errores[] = $e->getMessage();
        }
    }

    if (!$errores) {
        $stmt = $pdo->prepare(
            'UPDATE qerp_clientes SET nombre = :nombre, razon_social = :razon_social, nombre_fantasia = :nombre_fantasia,
             cuit = :cuit, mail = :mail, telefono = :telefono, direccion = :direccion,
             localidad = :localidad, provincia = :provincia, estado = :estado, origen = :origen,
             usuario_asignado = :usuario_asignado, notas = :notas, imagen = :imagen
             WHERE id = :id'
        );
        $stmt->execute([
            ':nombre'           => $datos['nombre'],
            ':razon_social'     => $datos['razon_social'] ?: null,
            ':nombre_fantasia'  => $datos['nombre_fantasia'] ?: null,
            ':cuit'             => $datos['cuit'] ?: null,
            ':mail'             => $datos['mail'] ?: null,
            ':telefono'         => $datos['telefono'] ?: null,
            ':direccion'        => $datos['direccion'] ?: null,
            ':localidad'        => $datos['localidad'] ?: null,
            ':provincia'        => $datos['provincia'] ?: null,
            ':estado'           => in_array($datos['estado'], ['prospecto','activo','inactivo'], true) ? $datos['estado'] : 'prospecto',
            ':origen'           => $datos['origen'] ?: null,
            ':usuario_asignado' => $datos['usuario_asignado'] ?: null,
            ':notas'            => $datos['notas'] ?: null,
            ':imagen'           => $imagen,
            ':id'               => $id,
        ]);
        $_SESSION['flash_ok'] = 'Cliente actualizado correctamente.';
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:680px;">
    <div class="card-header"><h3>Editar cliente</h3></div>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="fila-form">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" required>
            </div>
            <div class="campo">
                <label for="razon_social">Razón social</label>
                <input type="text" id="razon_social" name="razon_social" value="<?= e($datos['razon_social']) ?>">
            </div>
        </div>
        <div class="fila-form">
            <div class="campo">
                <label for="nombre_fantasia">Nombre de fantasía</label>
                <input type="text" id="nombre_fantasia" name="nombre_fantasia" value="<?= e($datos['nombre_fantasia']) ?>">
            </div>
            <div class="campo">
                <label for="imagen">Foto / logo del cliente</label>
                <?php if ($imagenActual): ?>
                    <img src="<?= QERP_URL_BASE . e($imagenActual) ?>" alt="" class="miniatura-cliente" style="margin-bottom:8px;">
                <?php endif; ?>
                <input type="file" id="imagen" name="imagen" accept="image/png,image/jpeg,image/webp">
            </div>
        </div>
        <div class="fila-form">
            <div class="campo">
                <label for="cuit">CUIT</label>
                <input type="text" id="cuit" name="cuit" value="<?= e($datos['cuit']) ?>">
            </div>
            <div class="campo">
                <label for="mail">Correo electrónico</label>
                <input type="email" id="mail" name="mail" value="<?= e($datos['mail']) ?>">
            </div>
            <div class="campo">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" value="<?= e($datos['telefono']) ?>">
            </div>
        </div>
        <div class="fila-form">
            <div class="campo">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" value="<?= e($datos['direccion']) ?>">
            </div>
            <div class="campo">
                <label for="localidad">Localidad</label>
                <input type="text" id="localidad" name="localidad" value="<?= e($datos['localidad']) ?>">
            </div>
            <div class="campo">
                <label for="provincia">Provincia</label>
                <input type="text" id="provincia" name="provincia" value="<?= e($datos['provincia']) ?>">
            </div>
        </div>
        <div class="fila-form">
            <div class="campo">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="prospecto" <?= $datos['estado'] === 'prospecto' ? 'selected' : '' ?>>Prospecto</option>
                    <option value="activo" <?= $datos['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $datos['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="campo">
                <label for="origen">Origen</label>
                <input type="text" id="origen" name="origen" value="<?= e($datos['origen']) ?>">
            </div>
            <div class="campo">
                <label for="usuario_asignado">Responsable</label>
                <select id="usuario_asignado" name="usuario_asignado">
                    <option value="">Sin asignar</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= (int) $u['id'] ?>" <?= (string) $u['id'] === (string) $datos['usuario_asignado'] ? 'selected' : '' ?>>
                            <?= e($u['nombre'] . ' ' . $u['apellido']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="campo">
            <label for="notas">Notas</label>
            <textarea id="notas" name="notas" rows="3"><?= e($datos['notas']) ?></textarea>
        </div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="ver.php?id=<?= $id ?>" class="btn btn-outline">Ver ficha</a>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
