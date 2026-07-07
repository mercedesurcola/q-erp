<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirLogin();

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, nombre, razon_social, imagen FROM qerp_clientes WHERE (nombre LIKE :q1 OR razon_social LIKE :q2 OR cuit LIKE :q3)";
$comodin = '%' . $q . '%';
$params = [':q1' => $comodin, ':q2' => $comodin, ':q3' => $comodin];

if (restringidoASusClientes()) {
    $sql .= " AND usuario_asignado = :uid";
    $params[':uid'] = $_SESSION['usuario_id'];
}
$sql .= " ORDER BY nombre LIMIT 10";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$resultados = array_map(function ($c) {
    return [
        'id'     => (int) $c['id'],
        'nombre' => $c['nombre'],
        'imagen' => $c['imagen'] ? QERP_URL_BASE . $c['imagen'] : null,
    ];
}, $stmt->fetchAll());

echo json_encode($resultados);
