<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'usuarios', 'eliminar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id === (int) $_SESSION['usuario_id']) {
    $_SESSION['flash_ok'] = 'No podés eliminar tu propio usuario mientras estás logueado.';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM qerp_usuarios WHERE id = :id');
$stmt->execute([':id' => $id]);

$_SESSION['flash_ok'] = 'Usuario eliminado correctamente.';
header('Location: index.php');
exit;
