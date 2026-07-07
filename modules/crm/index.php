<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'crm', 'ver');

$tituloPagina = 'CRM · Acciones de contacto';
$eyebrowPagina = 'CRM';
$slugSeccionActual = 'crm';

$puedeEditar = tienePermiso($pdo, 'crm', 'editar');
$puedeBorrar = tienePermiso($pdo, 'crm', 'eliminar');

$mensaje = $_SESSION['flash_ok'] ?? null;
unset($_SESSION['flash_ok']);

$vista = $_GET['vista'] ?? 'pendientes'; // pendientes | todas

$sql = "SELECT a.*, c.razon_social, u.nombre AS us_nombre, u.apellido AS us_apellido
        FROM qerp_acciones_contacto a
        INNER JOIN qerp_clientes c ON c.id = a.cliente_id
        INNER JOIN qerp_usuarios u ON u.id = a.usuario_id";

if ($vista === 'pendientes') {
    $sql .= " WHERE a.proximo_seguimiento IS NOT NULL AND a.proximo_seguimiento >= NOW()
              ORDER BY a.proximo_seguimiento ASC";
} else {
    $sql .= " ORDER BY a.fecha DESC LIMIT 200";
}

$acciones = $pdo->query($sql)->fetchAll();

$etiquetasTipo = [
    'llamada' => 'Llamada', 'mail' => 'Correo', 'reunion' => 'Reunión',
    'whatsapp' => 'WhatsApp', 'otro' => 'Otro',
];

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Acciones de contacto</h3>
    </div>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-ok"><?= e($mensaje) ?></div>
    <?php endif; ?>

    <div style="display:flex; gap:8px; margin-bottom:16px;">
        <a href="?vista=pendientes" class="btn <?= $vista === 'pendientes' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Próximos seguimientos</a>
        <a href="?vista=todas" class="btn <?= $vista === 'todas' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Historial reciente</a>
    </div>

    <table class="tabla-qerp">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Detalle</th>
                <th>Fecha</th>
                <th>Seguimiento</th>
                <th>Registrado por</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($acciones as $a): ?>
            <tr>
                <td><a href="../clientes/ver.php?id=<?= (int) $a['cliente_id'] ?>"><?= e($a['razon_social']) ?></a></td>
                <td><?= e($etiquetasTipo[$a['tipo']] ?? $a['tipo']) ?></td>
                <td><?= e($a['detalle'] ?: '—') ?></td>
                <td><?= e(date('d/m/Y H:i', strtotime($a['fecha']))) ?></td>
                <td><?= $a['proximo_seguimiento'] ? e(date('d/m/Y H:i', strtotime($a['proximo_seguimiento']))) : '—' ?></td>
                <td><?= e($a['us_nombre'] . ' ' . $a['us_apellido']) ?></td>
                <td style="text-align:right;white-space:nowrap;">
                    <?php if ($puedeEditar): ?>
                        <a href="editar.php?id=<?= (int) $a['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                    <?php endif; ?>
                    <?php if ($puedeBorrar): ?>
                        <form method="post" action="eliminar.php" style="display:inline;"
                              onsubmit="return confirm('¿Eliminar esta acción de contacto?');">
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$acciones): ?>
                <tr><td colspan="7" style="color:var(--muted);">
                    <?= $vista === 'pendientes' ? 'No hay seguimientos pendientes.' : 'Todavía no hay acciones registradas.' ?>
                </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
