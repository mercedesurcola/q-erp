<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'productos', 'eliminar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

try {
    $stmt = $pdo->prepare('DELETE FROM qerp_productos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $_SESSION['flash_ok'] = 'Producto eliminado correctamente.';
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $_SESSION['flash_error'] = 'No se puede eliminar: está asociado a una o más acciones de contacto.';
    } else {
        throw $e;
    }
}

header('Location: index.php');
exit;
