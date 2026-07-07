<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$mail = trim($_POST['mail'] ?? '');
$password = $_POST['password'] ?? '';

if ($mail === '' || $password === '') {
    $_SESSION['login_error'] = 'Completá correo y contraseña.';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT u.id, u.nombre, u.apellido, u.mail, u.password, u.activo,
            u.perfil_id, p.nombre AS perfil_nombre
     FROM usuarios u
     LEFT JOIN perfiles p ON p.id = u.perfil_id
     WHERE u.mail = :mail
     LIMIT 1"
);
$stmt->execute([':mail' => $mail]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($password, $usuario['password'])) {
    $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
    header('Location: index.php');
    exit;
}

if ((int) $usuario['activo'] !== 1) {
    $_SESSION['login_error'] = 'Tu usuario está inactivo. Contactá al administrador.';
    header('Location: index.php');
    exit;
}

// Login correcto: regenerar el ID de sesión (previene session fixation)
session_regenerate_id(true);

$_SESSION['usuario_id']       = $usuario['id'];
$_SESSION['usuario_nombre']   = $usuario['nombre'];
$_SESSION['usuario_apellido'] = $usuario['apellido'];
$_SESSION['usuario_mail']     = $usuario['mail'];
$_SESSION['perfil_id']        = $usuario['perfil_id'];
$_SESSION['perfil_nombre']    = $usuario['perfil_nombre'] ?? 'Sin perfil';

$pdo->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id')
    ->execute([':id' => $usuario['id']]);

header('Location: ' . QERP_URL_BASE . '/index.php');
exit;
