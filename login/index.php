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
    <title>Ingresar · QERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= QERP_URL_BASE ?>/assets/css/style.css">
</head>
<body>
    <div class="login-shell">
        <div class="login-card">
            <div class="brand"><span style="color:var(--accent)">●</span> QERP</div>
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
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>
