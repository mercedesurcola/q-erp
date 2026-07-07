<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'clientes', 'eliminar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT usuario_asignado FROM qerp_clientes WHERE id = :id');
$stmt->execute([':id' => $id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: index.php');
    exit;
}

requerirAccesoCliente($cliente['usuario_asignado'] !== null ? (int) $cliente['usuario_asignado'] : null);

$stmt = $pdo->prepare('DELETE FROM qerp_clientes WHERE id = :id');
$stmt->execute([':id' => $id]);

$_SESSION['flash_ok'] = 'Cliente eliminado correctamente.';
header('Location: index.php');
exit;
