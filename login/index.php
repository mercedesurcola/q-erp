<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/funciones.php';

if (estaLogueado()) {
    header('Location: ' . QERP_URL_BASE . '/index.php');
    exit;
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar · CUSOL</title>
    <link rel="icon" type="image/png" href="<?= asset('/assets/img/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('/assets/img/apple-touch-icon.png') ?>">
    <link rel="stylesheet" href="<?= asset('/assets/css/style.css') ?>">
</head>
<body>
    <div class="login-shell">
        <div class="login-card">
            <img src="<?= QERP_URL_BASE ?>/assets/img/logo-cusol.png" alt="CUSOL" class="logo-login">
            <div class="sub">Ingresá con tu cuenta para continuar</div>

            <?php if ($error): ?>
                <div class="alerta alerta-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="procesar.php">
                <div class="campo">
                    <label for="mail">Correo electrónico</label>
                    <input type="email" id="mail" name="mail" required autofocus>
                </div>
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <div class="input-password">
                        <input type="password" id="password" name="password" required>
                        <?php include __DIR__ . '/../includes/boton-ojo.php'; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Ingresar</button>
            </form>
        </div>
    </div>
    <script src="<?= asset('/assets/js/main.js') ?>"></script>
</body>
</html>
