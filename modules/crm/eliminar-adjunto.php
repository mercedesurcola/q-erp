<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'crm', 'editar');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$accionId = (int) ($_POST['accion_id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT ad.ruta, c.usuario_asignado
     FROM qerp_adjuntos_contacto ad
     INNER JOIN qerp_acciones_contacto a ON a.id = ad.accion_id
     INNER JOIN qerp_clientes c ON c.id = a.cliente_id
     WHERE ad.id = :id AND ad.accion_id = :accion_id'
);
$stmt->execute([':id' => $id, ':accion_id' => $accionId]);
$adjunto = $stmt->fetch();

if ($adjunto) {
    requerirAccesoCliente($adjunto['usuario_asignado'] !== null ? (int) $adjunto['usuario_asignado'] : null);

    $rutaFs = $_SERVER['DOCUMENT_ROOT'] . QERP_URL_BASE . $adjunto['ruta'];
    if (is_file($rutaFs)) {
        unlink($rutaFs);
    }
    $pdo->prepare('DELETE FROM qerp_adjuntos_contacto WHERE id = :id')->execute([':id' => $id]);
}

header('Location: editar.php?id=' . $accionId);
exit;
