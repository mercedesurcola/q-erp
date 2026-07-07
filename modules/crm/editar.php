<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'crm', 'editar');

$tituloPagina = 'Editar contacto';
$eyebrowPagina = 'CRM';
$slugSeccionActual = 'crm';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT a.*, c.nombre AS cliente_nombre, c.usuario_asignado FROM qerp_acciones_contacto a
     INNER JOIN qerp_clientes c ON c.id = a.cliente_id WHERE a.id = :id"
);
$stmt->execute([':id' => $id]);
$accion = $stmt->fetch();

if (!$accion) {
    header('Location: index.php');
    exit;
}

requerirAccesoCliente($accion['usuario_asignado'] !== null ? (int) $accion['usuario_asignado'] : null);

$errores = [];
$datos = [
    'tipo' => $accion['tipo'],
    'detalle' => $accion['detalle'],
    'proximo_seguimiento' => $accion['proximo_seguimiento'] ? date('Y-m-d\TH:i', strtotime($accion['proximo_seguimiento'])) : '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['tipo']    = $_POST['tipo'] ?? 'llamada';
    $datos['detalle'] = trim($_POST['detalle'] ?? '');
    $datos['proximo_seguimiento'] = trim($_POST['proximo_seguimiento'] ?? '');

    if (!in_array($datos['tipo'], ['llamada','mail','reunion','whatsapp','otro'], true)) {
        $errores[] = 'El tipo de contacto no es válido.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare(
            'UPDATE qerp_acciones_contacto SET tipo = :tipo, detalle = :detalle, proximo_seguimiento = :seguimiento
             WHERE id = :id'
        );
        $stmt->execute([
            ':tipo'        => $datos['tipo'],
            ':detalle'     => $datos['detalle'] ?: null,
            ':seguimiento' => $datos['proximo_seguimiento'] ?: null,
            ':id'          => $id,
        ]);
        $_SESSION['flash_ok'] = 'Contacto actualizado correctamente.';
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:560px;">
    <div class="card-header"><h3>Editar contacto — <?= e($accion['cliente_nombre']) ?></h3></div>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div class="campo">
            <label for="tipo">Tipo de contacto</label>
            <select id="tipo" name="tipo">
                <option value="llamada" <?= $datos['tipo'] === 'llamada' ? 'selected' : '' ?>>Llamada</option>
                <option value="mail" <?= $datos['tipo'] === 'mail' ? 'selected' : '' ?>>Correo</option>
                <option value="reunion" <?= $datos['tipo'] === 'reunion' ? 'selected' : '' ?>>Reunión</option>
                <option value="whatsapp" <?= $datos['tipo'] === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                <option value="otro" <?= $datos['tipo'] === 'otro' ? 'selected' : '' ?>>Otro</option>
            </select>
        </div>
        <div class="campo">
            <label for="detalle">Detalle</label>
            <textarea id="detalle" name="detalle" rows="4"><?= e($datos['detalle']) ?></textarea>
        </div>
        <div class="campo">
            <label for="proximo_seguimiento">Próximo seguimiento</label>
            <input type="datetime-local" id="proximo_seguimiento" name="proximo_seguimiento" value="<?= e($datos['proximo_seguimiento']) ?>">
        </div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
