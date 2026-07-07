<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'usuarios', 'crear');

$tituloPagina = 'Nuevo usuario';
$eyebrowPagina = 'Administración · Usuarios';
$slugSeccionActual = 'usuarios';

$errores = [];
$datos = ['nombre' => '', 'apellido' => '', 'mail' => '', 'perfil_id' => ''];

$perfiles = $pdo->query("SELECT id, nombre FROM qerp_perfiles WHERE activo = 1 ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre']    = trim($_POST['nombre'] ?? '');
    $datos['apellido']  = trim($_POST['apellido'] ?? '');
    $datos['mail']      = trim($_POST['mail'] ?? '');
    $datos['perfil_id'] = $_POST['perfil_id'] ?? '';
    $password           = $_POST['password'] ?? '';

    if ($datos['nombre'] === '') $errores[] = 'El nombre es obligatorio.';
    if ($datos['apellido'] === '') $errores[] = 'El apellido es obligatorio.';
    if (!filter_var($datos['mail'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
    if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';

    if (!$errores) {
        $stmt = $pdo->prepare('SELECT id FROM qerp_usuarios WHERE mail = :mail');
        $stmt->execute([':mail' => $datos['mail']]);
        if ($stmt->fetch()) {
            $errores[] = 'Ya existe un usuario con ese correo.';
        }
    }

    if (!$errores) {
        $stmt = $pdo->prepare(
            'INSERT INTO qerp_usuarios (nombre, apellido, mail, password, perfil_id, activo)
             VALUES (:nombre, :apellido, :mail, :password, :perfil_id, 1)'
        );
        $stmt->execute([
            ':nombre'    => $datos['nombre'],
            ':apellido'  => $datos['apellido'],
            ':mail'      => $datos['mail'],
            ':password'  => password_hash($password, PASSWORD_BCRYPT),
            ':perfil_id' => $datos['perfil_id'] ?: null,
        ]);
        $_SESSION['flash_ok'] = 'Usuario creado correctamente.';
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:560px;">
    <div class="card-header"><h3>Nuevo usuario</h3></div>

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
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required minlength="6">
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
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar usuario</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
