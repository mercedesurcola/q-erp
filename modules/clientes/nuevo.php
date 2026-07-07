<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'clientes', 'crear');

$tituloPagina = 'Nuevo cliente';
$eyebrowPagina = 'CRM · Clientes';
$slugSeccionActual = 'clientes';

$usuarios = $pdo->query("SELECT id, nombre, apellido FROM qerp_usuarios WHERE activo = 1 ORDER BY apellido")->fetchAll();

$errores = [];
$datos = [
    'nombre' => '', 'razon_social' => '', 'nombre_fantasia' => '', 'cuit' => '', 'mail' => '',
    'telefono' => '', 'direccion' => '', 'localidad' => '', 'provincia' => '',
    'estado' => 'prospecto', 'origen' => '', 'usuario_asignado' => (string) $_SESSION['usuario_id'], 'notas' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($datos as $campo => $v) {
        $datos[$campo] = trim($_POST[$campo] ?? '');
    }

    if ($datos['nombre'] === '') $errores[] = 'El nombre es obligatorio.';
    if ($datos['mail'] !== '' && !filter_var($datos['mail'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no es válido.';
    }

    $imagen = null;
    if (!$errores) {
        try {
            $imagen = procesarImagenCliente($_FILES['imagen'] ?? []);
        } catch (RuntimeException $e) {
            $errores[] = $e->getMessage();
        }
    }

    if (!$errores) {
        $stmt = $pdo->prepare(
            'INSERT INTO qerp_clientes (nombre, razon_social, nombre_fantasia, cuit, mail, telefono, direccion,
                                    localidad, provincia, estado, origen, usuario_asignado, notas, imagen)
             VALUES (:nombre, :razon_social, :nombre_fantasia, :cuit, :mail, :telefono, :direccion,
                     :localidad, :provincia, :estado, :origen, :usuario_asignado, :notas, :imagen)'
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
        ]);
        $_SESSION['flash_ok'] = 'Cliente creado correctamente.';
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:680px;">
    <div class="card-header"><h3>Nuevo cliente</h3></div>

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
                <input type="text" id="origen" name="origen" value="<?= e($datos['origen']) ?>" placeholder="Ej: Referido, web, feria...">
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
            <button type="submit" class="btn btn-primary">Guardar cliente</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
