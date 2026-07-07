<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'clientes', 'ver');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT c.*, u.nombre AS resp_nombre, u.apellido AS resp_apellido
     FROM qerp_clientes c LEFT JOIN qerp_usuarios u ON u.id = c.usuario_asignado
     WHERE c.id = :id"
);
$stmt->execute([':id' => $id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: index.php');
    exit;
}

$tituloPagina = $cliente['razon_social'];
$eyebrowPagina = 'CRM · Ficha de cliente';
$slugSeccionActual = 'clientes';

$puedeVerCrm    = tienePermiso($pdo, 'crm', 'ver');
$puedeCrearCrm  = tienePermiso($pdo, 'crm', 'crear');

$mensaje = $_SESSION['flash_ok'] ?? null;
unset($_SESSION['flash_ok']);

$acciones = [];
if ($puedeVerCrm) {
    $stmt = $pdo->prepare(
        "SELECT a.*, u.nombre AS us_nombre, u.apellido AS us_apellido
         FROM qerp_acciones_contacto a
         INNER JOIN qerp_usuarios u ON u.id = a.usuario_id
         WHERE a.cliente_id = :id
         ORDER BY a.fecha DESC"
    );
    $stmt->execute([':id' => $id]);
    $acciones = $stmt->fetchAll();
}

$etiquetasTipo = [
    'llamada' => 'Llamada', 'mail' => 'Correo', 'reunion' => 'Reunión',
    'whatsapp' => 'WhatsApp', 'otro' => 'Otro',
];

include __DIR__ . '/../../includes/header.php';
?>

<?php if ($mensaje): ?>
    <div class="alerta alerta-ok"><?= e($mensaje) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Datos del cliente</h3>
        <div>
            <span class="badge badge-<?= e($cliente['estado']) ?>"><?= ucfirst(e($cliente['estado'])) ?></span>
            <a href="editar.php?id=<?= $id ?>" class="btn btn-outline btn-sm" style="margin-left:8px;">Editar</a>
        </div>
    </div>
    <div class="fila-form">
        <div><strong>CUIT:</strong> <?= e($cliente['cuit'] ?: '—') ?></div>
        <div><strong>Correo:</strong> <?= e($cliente['mail'] ?: '—') ?></div>
        <div><strong>Teléfono:</strong> <?= e($cliente['telefono'] ?: '—') ?></div>
    </div>
    <div class="fila-form" style="margin-top:12px;">
        <div><strong>Dirección:</strong> <?= e(trim(($cliente['direccion'] ?: '') . ' ' . ($cliente['localidad'] ?: '') . ' ' . ($cliente['provincia'] ?: '')) ?: '—') ?></div>
        <div><strong>Responsable:</strong> <?= $cliente['resp_nombre'] ? e($cliente['resp_nombre'] . ' ' . $cliente['resp_apellido']) : '—' ?></div>
        <div><strong>Origen:</strong> <?= e($cliente['origen'] ?: '—') ?></div>
    </div>
    <?php if ($cliente['notas']): ?>
        <div style="margin-top:12px;"><strong>Notas:</strong><br><?= nl2br(e($cliente['notas'])) ?></div>
    <?php endif; ?>
</div>

<?php if ($puedeVerCrm): ?>
<div class="card">
    <div class="card-header">
        <h3>Historial de contacto</h3>
        <?php if ($puedeCrearCrm): ?>
            <a href="../crm/nueva.php?cliente_id=<?= $id ?>" class="btn btn-primary btn-sm">+ Registrar contacto</a>
        <?php endif; ?>
    </div>

    <?php if (!$acciones): ?>
        <p style="color:var(--muted);">Todavía no hay acciones de contacto registradas para este cliente.</p>
    <?php else: ?>
        <table class="tabla-qerp">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Detalle</th>
                    <th>Realizado por</th>
                    <th>Próximo seguimiento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($acciones as $a): ?>
                <tr>
                    <td><?= e(date('d/m/Y H:i', strtotime($a['fecha']))) ?></td>
                    <td><?= e($etiquetasTipo[$a['tipo']] ?? $a['tipo']) ?></td>
                    <td><?= e($a['detalle'] ?: '—') ?></td>
                    <td><?= e($a['us_nombre'] . ' ' . $a['us_apellido']) ?></td>
                    <td><?= $a['proximo_seguimiento'] ? e(date('d/m/Y H:i', strtotime($a['proximo_seguimiento']))) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
