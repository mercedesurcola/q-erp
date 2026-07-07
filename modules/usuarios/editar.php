<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'usuarios', 'editar');

$tituloPagina = 'Editar usuario';
$eyebrowPagina = 'Administración · Usuarios';
$slugSeccionActual = 'usuarios';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM qerp_usuarios WHERE id = :id');
$stmt->execute([':id' => $id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: index.php');
    exit;
}

$perfiles = $pdo->query("SELECT id, nombre FROM qerp_perfiles WHERE activo = 1 ORDER BY nombre")->fetchAll();
$errores = [];

$datos = [
    'nombre'    => $usuario['nombre'],
    'apellido'  => $usuario['apellido'],
    'mail'      => $usuario['mail'],
    'perfil_id' => $usuario['perfil_id'],
    'activo'    => $usuario['activo'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre']    = trim($_POST['nombre'] ?? '');
    $datos['apellido']  = trim($_POST['apellido'] ?? '');
    $datos['mail']      = trim($_POST['mail'] ?? '');
    $datos['perfil_id'] = $_POST['perfil_id'] ?? '';
    $datos['activo']    = isset($_POST['activo']) ? 1 : 0;
    $nuevaPassword      = $_POST['password'] ?? '';

    if ($datos['nombre'] === '') $errores[] = 'El nombre es obligatorio.';
    if ($datos['apellido'] === '') $errores[] = 'El apellido es obligatorio.';
    if (!filter_var($datos['mail'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
    if ($nuevaPassword !== '' && strlen($nuevaPassword) < 6) {
        $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare('SELECT id FROM qerp_usuarios WHERE mail = :mail AND id != :id');
        $stmt->execute([':mail' => $datos['mail'], ':id' => $id]);
        if ($stmt->fetch()) {
            $errores[] = 'Ya existe otro usuario con ese correo.';
        }
    }

    if (!$errores) {
        if ($nuevaPassword !== '') {
            $stmt = $pdo->prepare(
                'UPDATE qerp_usuarios SET nombre = :nombre, apellido = :apellido, mail = :mail,
                 perfil_id = :perfil_id, activo = :activo, password = :password WHERE id = :id'
            );
            $stmt->execute([
                ':nombre'    => $datos['nombre'],
                ':apellido'  => $datos['apellido'],
                ':mail'      => $datos['mail'],
                ':perfil_id' => $datos['perfil_id'] ?: null,
                ':activo'    => $datos['activo'],
                ':password'  => password_hash($nuevaPassword, PASSWORD_BCRYPT),
                ':id'        => $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE qerp_usuarios SET nombre = :nombre, apellido = :apellido, mail = :mail,
                 perfil_id = :perfil_id, activo = :activo WHERE id = :id'
            );
            $stmt->execute([
                ':nombre'    => $datos['nombre'],
                ':apellido'  => $datos['apellido'],
                ':mail'      => $datos['mail'],
                ':perfil_id' => $datos['perfil_id'] ?: null,
                ':activo'    => $datos['activo'],
                ':id'        => $id,
            ]);
        }
        $_SESSION['flash_ok'] = 'Usuario actualizado correctamente.';
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:560px;">
    <div class="card-header"><h3>Editar usuario</h3></div>

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
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" value="<?= e($datos['apellido']) ?>" required>
            </div>
        </div>
        <div class="campo">
            <label for="mail">Correo electrónico</label>
            <input type="email" id="mail" name="mail" value="<?= e($datos['mail']) ?>" required>
        </div>
        <div class="campo">
            <label for="password">Nueva contraseña</label>
            <div class="input-password">
                <input type="password" id="password" name="password" minlength="6" placeholder="Dejar en blanco para no cambiarla">
                <?php include __DIR__ . '/../../includes/boton-ojo.php'; ?>
            </div>
        </div>
        <div class="campo">
            <label for="perfil_id">Perfil</label>
            <select id="perfil_id" name="perfil_id">
                <option value="">Sin perfil asignado</option>
                <?php foreach ($perfiles as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (string) $p['id'] === (string) $datos['perfil_id'] ? 'selected' : '' ?>>
                        <?= e($p['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                <input type="checkbox" name="activo" style="width:auto;" <?= $datos['activo'] ? 'checked' : '' ?>>
                Usuario activo
            </label>
        </div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
