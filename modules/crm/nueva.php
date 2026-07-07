<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'crm', 'crear');

$clienteId = (int) ($_GET['cliente_id'] ?? $_POST['cliente_id'] ?? 0);
$stmt = $pdo->prepare('SELECT id, razon_social FROM clientes WHERE id = :id');
$stmt->execute([':id' => $clienteId]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Registrar contacto';
$eyebrowPagina = 'CRM · ' . $cliente['razon_social'];
$slugSeccionActual = 'crm';

$errores = [];
$datos = ['tipo' => 'llamada', 'detalle' => '', 'proximo_seguimiento' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['tipo']    = $_POST['tipo'] ?? 'llamada';
    $datos['detalle'] = trim($_POST['detalle'] ?? '');
    $datos['proximo_seguimiento'] = trim($_POST['proximo_seguimiento'] ?? '');

    if (!in_array($datos['tipo'], ['llamada','mail','reunion','whatsapp','otro'], true)) {
        $errores[] = 'El tipo de contacto no es válido.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare(
            'INSERT INTO acciones_contacto (cliente_id, usuario_id, tipo, detalle, proximo_seguimiento)
             VALUES (:cliente_id, :usuario_id, :tipo, :detalle, :seguimiento)'
        );
        $stmt->execute([
            ':cliente_id'  => $clienteId,
            ':usuario_id'  => $_SESSION['usuario_id'],
            ':tipo'        => $datos['tipo'],
            ':detalle'     => $datos['detalle'] ?: null,
            ':seguimiento' => $datos['proximo_seguimiento'] ?: null,
        ]);
        $_SESSION['flash_ok'] = 'Contacto registrado correctamente.';
        header('Location: ../clientes/ver.php?id=' . $clienteId);
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:560px;">
    <div class="card-header"><h3>Registrar contacto — <?= e($cliente['razon_social']) ?></h3></div>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="cliente_id" value="<?= $clienteId ?>">
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
            <textarea id="detalle" name="detalle" rows="4" placeholder="¿De qué se habló? ¿Qué se acordó?"><?= e($datos['detalle']) ?></textarea>
        </div>
        <div class="campo">
            <label for="proximo_seguimiento">Próximo seguimiento (opcional)</label>
            <input type="datetime-local" id="proximo_seguimiento" name="proximo_seguimiento" value="<?= e($datos['proximo_seguimiento']) ?>">
        </div>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="../clientes/ver.php?id=<?= $clienteId ?>" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
