<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirLogin();

header('Content-Type: application/json; charset=utf-8');

$clienteId = (int) ($_GET['cliente_id'] ?? 0);
$stmt = $pdo->prepare('SELECT usuario_asignado FROM qerp_clientes WHERE id = :id');
$stmt->execute([':id' => $clienteId]);
$cliente = $stmt->fetch();

if (!$cliente) {
    echo json_encode([]);
    exit;
}

if (restringidoASusClientes() && (int) $cliente['usuario_asignado'] !== (int) $_SESSION['usuario_id']) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT a.fecha, a.canal, a.detalle, a.accion_siguiente, a.proximo_seguimiento,
            u.nombre AS us_nombre, u.apellido AS us_apellido,
            m.nombre AS motivo_nombre, r.nombre AS resultado_nombre
     FROM qerp_acciones_contacto a
     INNER JOIN qerp_usuarios u ON u.id = a.usuario_id
     LEFT JOIN qerp_motivos_contacto m ON m.id = a.motivo_id
     LEFT JOIN qerp_resultados_contacto r ON r.id = a.resultado_id
     WHERE a.cliente_id = :id
     ORDER BY a.fecha DESC
     LIMIT 50"
);
$stmt->execute([':id' => $clienteId]);

$etiquetasCanal = [
    'llamada' => 'Llamada', 'mail' => 'Correo', 'reunion' => 'Reunión',
    'whatsapp' => 'WhatsApp', 'videollamada' => 'Videollamada',
];

$resultado = array_map(function ($a) use ($etiquetasCanal) {
    return [
        'fecha'      => date('d/m/Y H:i', strtotime($a['fecha'])),
        'canal'      => $etiquetasCanal[$a['canal']] ?? $a['canal'],
        'motivo'     => $a['motivo_nombre'] ?: '—',
        'resultado'  => $a['resultado_nombre'] ?: '—',
        'detalle'    => $a['detalle'] ?: '',
        'vendedor'   => trim($a['us_nombre'] . ' ' . $a['us_apellido']),
    ];
}, $stmt->fetchAll());

echo json_encode($resultado);
