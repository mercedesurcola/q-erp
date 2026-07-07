<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'crm', 'eliminar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('DELETE FROM acciones_contacto WHERE id = :id');
$stmt->execute([':id' => $id]);

$_SESSION['flash_ok'] = 'Acción de contacto eliminada correctamente.';
header('Location: index.php');
exit;
