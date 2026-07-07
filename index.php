<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
requerirLogin();

$tituloPagina = 'Inicio';
$eyebrowPagina = 'Panel general';
$slugSeccionActual = '';

$restringido = restringidoASusClientes();
$filtroCliente = $restringido ? ' AND usuario_asignado = :uid' : '';
$filtroAccionCliente = $restringido ? ' AND c.usuario_asignado = :uid' : '';
$paramsUid = $restringido ? [':uid' => $_SESSION['usuario_id']] : [];

function contar(PDO $pdo, string $sql, array $params): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetch()['c'];
}

$totalClientes = contar($pdo, "SELECT COUNT(*) c FROM qerp_clientes WHERE 1=1$filtroCliente", $paramsUid);
$prospectos    = contar($pdo, "SELECT COUNT(*) c FROM qerp_clientes WHERE estado = 'prospecto'$filtroCliente", $paramsUid);
$activos       = contar($pdo, "SELECT COUNT(*) c FROM qerp_clientes WHERE estado = 'activo'$filtroCliente", $paramsUid);
$accionesHoy   = contar($pdo, "SELECT COUNT(*) c FROM qerp_acciones_contacto a INNER JOIN qerp_clientes c ON c.id = a.cliente_id WHERE DATE(a.fecha) = CURDATE()$filtroAccionCliente", $paramsUid);
$vencidos      = contar($pdo, "SELECT COUNT(*) c FROM qerp_acciones_contacto a INNER JOIN qerp_clientes c ON c.id = a.cliente_id WHERE a.proximo_seguimiento IS NOT NULL AND a.proximo_seguimiento < NOW()$filtroAccionCliente", $paramsUid);
$proximos7dias = contar($pdo, "SELECT COUNT(*) c FROM qerp_acciones_contacto a INNER JOIN qerp_clientes c ON c.id = a.cliente_id WHERE a.proximo_seguimiento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)$filtroAccionCliente", $paramsUid);
$prioridadAlta = contar($pdo, "SELECT COUNT(*) c FROM qerp_acciones_contacto a INNER JOIN qerp_clientes c ON c.id = a.cliente_id WHERE a.proximo_seguimiento >= NOW() AND a.prioridad = 'alta'$filtroAccionCliente", $paramsUid);
$productosActivos = contar($pdo, "SELECT COUNT(*) c FROM qerp_productos WHERE activo = 1", []);

$puedeVerCrm = tienePermiso($pdo, 'crm', 'ver');
$proximasAcciones = [];
if ($puedeVerCrm) {
    $sql = "SELECT a.proximo_seguimiento, a.accion_siguiente, a.prioridad, c.id AS cliente_id, c.nombre AS cliente_nombre,
                   u.nombre AS us_nombre, u.apellido AS us_apellido
            FROM qerp_acciones_contacto a
            INNER JOIN qerp_clientes c ON c.id = a.cliente_id
            INNER JOIN qerp_usuarios u ON u.id = a.usuario_id
            WHERE a.proximo_seguimiento IS NOT NULL AND a.proximo_seguimiento >= NOW()$filtroAccionCliente
            ORDER BY a.proximo_seguimiento ASC
            LIMIT 6";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($paramsUid);
    $proximasAcciones = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>

<div class="grid-kpis">
    <div class="kpi">
        <div class="valor"><?= $totalClientes ?></div>
        <div class="etiqueta">Clientes totales</div>
    </div>
    <div class="kpi">
        <div class="valor"><?= $prospectos ?></div>
        <div class="etiqueta">Prospectos</div>
    </div>
    <div class="kpi">
        <div class="valor"><?= $activos ?></div>
        <div class="etiqueta">Clientes activos</div>
    </div>
    <div class="kpi">
        <div class="valor"><?= $accionesHoy ?></div>
        <div class="etiqueta">Acciones hoy</div>
    </div>
    <div class="kpi">
        <div class="valor" style="<?= $vencidos > 0 ? 'color:var(--danger);' : '' ?>"><?= $vencidos ?></div>
        <div class="etiqueta">Seguimientos vencidos</div>
    </div>
    <div class="kpi">
        <div class="valor"><?= $proximos7dias ?></div>
        <div class="etiqueta">Próximos 7 días</div>
    </div>
    <div class="kpi">
        <div class="valor"><?= $prioridadAlta ?></div>
        <div class="etiqueta">Prioridad alta abiertas</div>
    </div>
    <div class="kpi">
        <div class="valor"><?= $productosActivos ?></div>
        <div class="etiqueta">Productos/Servicios</div>
    </div>
</div>

<?php if ($puedeVerCrm): ?>
<div class="card">
    <div class="card-header">
        <h3>Próximos seguimientos</h3>
        <a href="<?= QERP_URL_BASE ?>/modules/crm/index.php?vista=pendientes" class="btn btn-outline btn-sm">Ver todos</a>
    </div>
    <?php if (!$proximasAcciones): ?>
        <p style="color:var(--muted);">No hay seguimientos pendientes.</p>
    <?php else: ?>
        <div class="tabla-responsive">
        <table class="tabla-qerp">
            <thead>
                <tr><th>Fecha</th><th>Hora</th><th>Prioridad</th><th>Vendedor</th><th>Cliente</th><th>Acción</th></tr>
            </thead>
            <tbody>
                <?php foreach ($proximasAcciones as $a): $ts = strtotime($a['proximo_seguimiento']); ?>
                <tr>
                    <td><?= e(date('d/m/Y', $ts)) ?></td>
                    <td><?= e(date('H:i', $ts)) ?></td>
                    <td>
                        <?php if ($a['prioridad']): ?>
                            <span class="badge badge-<?= $a['prioridad'] === 'alta' ? 'inactivo' : ($a['prioridad'] === 'media' ? 'prospecto' : 'activo') ?>"><?= ucfirst(e($a['prioridad'])) ?></span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?= e($a['us_nombre'] . ' ' . $a['us_apellido']) ?></td>
                    <td><a href="<?= QERP_URL_BASE ?>/modules/clientes/ver.php?id=<?= (int) $a['cliente_id'] ?>"><?= e($a['cliente_nombre']) ?></a></td>
                    <td><?= e($a['accion_siguiente'] ?: '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Bienvenido/a, <?= e(usuarioActual()['nombre']) ?></h3>
    </div>
    <p style="color:var(--muted);">
        Usá el menú lateral para gestionar Clientes, registrar Acciones de contacto,
        y administrar Usuarios y Perfiles del sistema.
    </p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
