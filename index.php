<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/funciones.php';
requerirLogin();

$tituloPagina = 'Inicio';
$eyebrowPagina = 'Panel general';
$slugSeccionActual = '';

$totalClientes = (int) $pdo->query("SELECT COUNT(*) c FROM qerp_clientes")->fetch()['c'];
$prospectos     = (int) $pdo->query("SELECT COUNT(*) c FROM qerp_clientes WHERE estado = 'prospecto'")->fetch()['c'];
$activos        = (int) $pdo->query("SELECT COUNT(*) c FROM qerp_clientes WHERE estado = 'activo'")->fetch()['c'];
$accionesHoy    = (int) $pdo->query("SELECT COUNT(*) c FROM qerp_acciones_contacto WHERE DATE(fecha) = CURDATE()")->fetch()['c'];

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
        <div class="etiqueta">Acciones de contacto hoy</div>
    </div>
</div>

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
