<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'crm', 'eliminar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT c.usuario_asignado FROM qerp_acciones_contacto a
     INNER JOIN qerp_clientes c ON c.id = a.cliente_id WHERE a.id = :id'
);
$stmt->execute([':id' => $id]);
$accion = $stmt->fetch();

if (!$accion) {
    header('Location: index.php');
    exit;
}

requerirAccesoCliente($accion['usuario_asignado'] !== null ? (int) $accion['usuario_asignado'] : null);

$stmt = $pdo->prepare('DELETE FROM qerp_acciones_contacto WHERE id = :id');
$stmt->execute([':id' => $id]);

$_SESSION['flash_ok'] = 'Acción de contacto eliminada correctamente.';
header('Location: index.php');
exit;
