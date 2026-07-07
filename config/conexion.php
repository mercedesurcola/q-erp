<?php
/**
 * QERP - Conexión a la base de datos (MySQL - Hosting Donweb)
 * Completar con los datos reales que provee Donweb (cPanel > MySQL Databases).
 */

// ---- Datos de conexión ----
define('DB_HOST', 'localhost');       // Donweb generalmente usa 'localhost'

$esLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true);

if ($esLocal) {
    // WAMP local: MariaDB de WAMP escucha en el puerto 3307 (el 3306 lo usa otro MySQL instalado aparte)
    define('DB_PORT', '3307');
    define('DB_NAME', 'qerp_local');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_PORT', '3306');
    define('DB_NAME', 'a0141120_cusol'); // ej: cpanelusuario_qerp
    define('DB_USER', 'a0141120_admin');       // ej: cpanelusuario_qerp
    define('DB_PASS', 'Sopap@31502021*');
}

// ---- Configuración general del sistema ----
define('QERP_NOMBRE', 'Qerp');
define('QERP_URL_BASE', '/q-erp'); // ajustar si el ERP no está en la raíz

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // En producción no mostrar el detalle del error al usuario final
    error_log('Error de conexión QERP: ' . $e->getMessage());
    die('No se pudo conectar a la base de datos. Contactá al administrador del sistema.');
}
