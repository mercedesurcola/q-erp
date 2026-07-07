<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'clientes', 'ver');

$tituloPagina = 'Clientes';
$eyebrowPagina = 'CRM';
$slugSeccionActual = 'clientes';

$puedeCrear  = tienePermiso($pdo, 'clientes', 'crear');
$puedeEditar = tienePermiso($pdo, 'clientes', 'editar');
$puedeBorrar = tienePermiso($pdo, 'clientes', 'eliminar');

$mensaje = $_SESSION['flash_ok'] ?? null;
unset($_SESSION['flash_ok']);

$busqueda = trim($_GET['q'] ?? '');
$estadoFiltro = $_GET['estado'] ?? '';

$sql = "SELECT c.*, u.nombre AS resp_nombre, u.apellido AS resp_apellido
        FROM clientes c
        LEFT JOIN usuarios u ON u.id = c.usuario_asignado
        WHERE 1=1";
$params = [];

if ($busqueda !== '') {
    $sql .= " AND (c.razon_social LIKE :q OR c.mail LIKE :q OR c.cuit LIKE :q)";
    $params[':q'] = '%' . $busqueda . '%';
}
if ($estadoFiltro !== '' && in_array($estadoFiltro, ['prospecto', 'activo', 'inactivo'], true)) {
    $sql .= " AND c.estado = :estado";
    $params[':estado'] = $estadoFiltro;
}
$sql .= " ORDER BY c.razon_social";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Clientes</h3>
        <?php if ($puedeCrear): ?>
            <a href="nuevo.php" class="btn btn-primary">+ Nuevo cliente</a>
        <?php endif; ?>
    </div>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-ok"><?= e($mensaje) ?></div>
    <?php endif; ?>

    <form method="get" style="display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap;">
        <input type="text" name="q" value="<?= e($busqueda) ?>" placeholder="Buscar por razón social, mail o CUIT..." style="max-width:320px;">
        <select name="estado" style="max-width:180px;">
            <option value="">Todos los estados</option>
            <option value="prospecto" <?= $estadoFiltro === 'prospecto' ? 'selected' : '' ?>>Prospecto</option>
            <option value="activo" <?= $estadoFiltro === 'activo' ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= $estadoFiltro === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
        <button type="submit" class="btn btn-outline">Filtrar</button>
    </form>

    <table class="tabla-qerp">
        <thead>
            <tr>
                <th>Razón social</th>
                <th>Contacto</th>
                <th>Estado</th>
                <th>Responsable</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientes as $c): ?>
            <tr>
                <td>
                    <a href="ver.php?id=<?= (int) $c['id'] ?>"><strong><?= e($c['razon_social']) ?></strong></a>
                    <?php if ($c['cuit']): ?><br><span style="color:var(--muted);font-size:12px;">CUIT <?= e($c['cuit']) ?></span><?php endif; ?>
                </td>
                <td>
                    <?= e($c['mail'] ?? '—') ?><br>
                    <span style="color:var(--muted);"><?= e($c['telefono'] ?? '') ?></span>
                </td>
                <td><span class="badge badge-<?= e($c['estado']) ?>"><?= ucfirst(e($c['estado'])) ?></span></td>
                <td><?= $c['resp_nombre'] ? e($c['resp_nombre'] . ' ' . $c['resp_apellido']) : '—' ?></td>
                <td style="text-align:right;white-space:nowrap;">
                    <a href="ver.php?id=<?= (int) $c['id'] ?>" class="btn btn-outline btn-sm">Ver</a>
                    <?php if ($puedeEditar): ?>
                        <a href="editar.php?id=<?= (int) $c['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
                    <?php endif; ?>
                    <?php if ($puedeBorrar): ?>
                        <form method="post" action="eliminar.php" style="display:inline;"
                              onsubmit="return confirm('¿Eliminar este cliente? También se borrarán sus acciones de contacto.');">
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$clientes): ?>
                <tr><td colspan="5" style="color:var(--muted);">No se encontraron clientes.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
