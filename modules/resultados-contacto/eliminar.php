<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'resultados-contacto', 'eliminar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

try {
    $stmt = $pdo->prepare('DELETE FROM qerp_resultados_contacto WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $_SESSION['flash_ok'] = 'Resultado eliminado correctamente.';
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $_SESSION['flash_error'] = 'No se puede eliminar: está en uso en una o más acciones de contacto.';
    } else {
        throw $e;
    }
}

header('Location: index.php');
exit;
