<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'crm', 'ver');

$tituloPagina = 'CRM · Acciones de contacto';
$eyebrowPagina = 'CRM';
$slugSeccionActual = 'crm';

$puedeCrear  = tienePermiso($pdo, 'crm', 'crear');
$puedeEditar = tienePermiso($pdo, 'crm', 'editar');
$puedeBorrar = tienePermiso($pdo, 'crm', 'eliminar');

$mensaje = $_SESSION['flash_ok'] ?? null;
unset($_SESSION['flash_ok']);

$vista = $_GET['vista'] ?? 'pendientes'; // pendientes | todas

$sql = "SELECT a.*, c.nombre AS cliente_nombre, u.nombre AS us_nombre, u.apellido AS us_apellido,
               m.nombre AS motivo_nombre, r.nombre AS resultado_nombre
        FROM qerp_acciones_contacto a
        INNER JOIN qerp_clientes c ON c.id = a.cliente_id
        INNER JOIN qerp_usuarios u ON u.id = a.usuario_id
        LEFT JOIN qerp_motivos_contacto m ON m.id = a.motivo_id
        LEFT JOIN qerp_resultados_contacto r ON r.id = a.resultado_id
        WHERE 1=1";
$params = [];

if (restringidoASusClientes()) {
    $sql .= " AND c.usuario_asignado = :uid";
    $params[':uid'] = $_SESSION['usuario_id'];
}

if ($vista === 'pendientes') {
    $sql .= " AND a.proximo_seguimiento IS NOT NULL AND a.proximo_seguimiento >= NOW()
              ORDER BY a.proximo_seguimiento ASC";
} else {
    $sql .= " ORDER BY a.fecha DESC LIMIT 200";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$acciones = $stmt->fetchAll();

$etiquetasCanal = [
    'llamada' => 'Llamada', 'mail' => 'Correo', 'reunion' => 'Reunión',
    'whatsapp' => 'WhatsApp', 'videollamada' => 'Videollamada',
];
$etiquetasTemperatura = ['frio' => 'Frío', 'tibio' => 'Tibio', 'caliente' => 'Caliente'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Acciones de contacto</h3>
        <?php if ($puedeCrear): ?>
            <a href="nueva.php" class="btn btn-primary">+ Nueva actividad</a>
        <?php endif; ?>
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
                <th>Canal</th>
                <th>Motivo</th>
                <th>Resultado</th>
                <th>Prioridad</th>
                <th>Temp.</th>
                <th>Fecha</th>
                <th>Seguimiento</th>
                <th>Registrado por</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($acciones as $a): ?>
            <tr>
                <td><a href="../clientes/ver.php?id=<?= (int) $a['cliente_id'] ?>"><?= e($a['cliente_nombre']) ?></a></td>
                <td><?= e($etiquetasCanal[$a['canal']] ?? $a['canal']) ?></td>
                <td><?= e($a['motivo_nombre'] ?: '—') ?></td>
                <td><?= e($a['resultado_nombre'] ?: '—') ?></td>
                <td>
                    <?php if ($a['prioridad']): ?>
                        <span class="badge badge-<?= $a['prioridad'] === 'alta' ? 'inactivo' : ($a['prioridad'] === 'media' ? 'prospecto' : 'activo') ?>"><?= ucfirst(e($a['prioridad'])) ?></span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td><?= $a['temperatura'] ? e($etiquetasTemperatura[$a['temperatura']]) : '—' ?></td>
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
                <tr><td colspan="10" style="color:var(--muted);">
                    <?= $vista === 'pendientes' ? 'No hay seguimientos pendientes.' : 'Todavía no hay acciones registradas.' ?>
                </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
