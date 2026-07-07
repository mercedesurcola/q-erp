<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sin permisos · CUSOL</title>
    <link rel="icon" type="image/png" href="<?= asset('/assets/img/favicon.png') ?>">
    <link rel="stylesheet" href="<?= asset('/assets/css/style.css') ?>">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg);">
    <div class="card" style="max-width:420px;text-align:center;">
        <h2>No tenés permisos</h2>
        <p style="color:var(--muted);">Tu perfil no tiene acceso a esta sección. Si creés que es un error, contactá al administrador del sistema.</p>
        <a class="btn btn-outline" href="<?= QERP_URL_BASE ?>/index.php">Volver al inicio</a>
    </div>
</body>
</html>
