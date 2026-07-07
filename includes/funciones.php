<?php
/**
 * QERP - Funciones comunes: sesión, autenticación y permisos
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Devuelve true si hay un usuario logueado.
 */
function estaLogueado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Corta la ejecución y redirige al login si no hay sesión activa.
 */
function requerirLogin(): void
{
    if (!estaLogueado()) {
        header('Location: ' . QERP_URL_BASE . '/login/index.php');
        exit;
    }
}

/**
 * Devuelve un arreglo con los datos básicos del usuario logueado.
 */
function usuarioActual(): ?array
{
    if (!estaLogueado()) {
        return null;
    }
    return [
        'id'         => $_SESSION['usuario_id'],
        'nombre'     => $_SESSION['usuario_nombre'],
        'apellido'   => $_SESSION['usuario_apellido'],
        'mail'       => $_SESSION['usuario_mail'],
        'perfil_id'  => $_SESSION['perfil_id'],
        'perfil'     => $_SESSION['perfil_nombre'],
    ];
}

/**
 * Verifica si el perfil del usuario logueado tiene un permiso
 * determinado ('ver', 'crear', 'editar', 'eliminar') sobre una sección (slug).
 */
function tienePermiso(PDO $pdo, string $slugSeccion, string $accion = 'ver'): bool
{
    if (!estaLogueado()) {
        return false;
    }

    $accionesValidas = ['ver', 'crear', 'editar', 'eliminar'];
    if (!in_array($accion, $accionesValidas, true)) {
        return false;
    }

    static $cache = [];
    $clave = $_SESSION['perfil_id'] . '-' . $slugSeccion;

    if (!isset($cache[$clave])) {
        $stmt = $pdo->prepare(
            "SELECT pp.ver, pp.crear, pp.editar, pp.eliminar
             FROM qerp_perfil_permisos pp
             INNER JOIN qerp_secciones s ON s.id = pp.seccion_id
             WHERE pp.perfil_id = :perfil_id AND s.slug = :slug
             LIMIT 1"
        );
        $stmt->execute([
            ':perfil_id' => $_SESSION['perfil_id'],
            ':slug'      => $slugSeccion,
        ]);
        $cache[$clave] = $stmt->fetch() ?: ['ver' => 0, 'crear' => 0, 'editar' => 0, 'eliminar' => 0];
    }

    return (bool) $cache[$clave][$accion];
}

/**
 * Corta la ejecución con un error 403 si el perfil no tiene el permiso pedido.
 */
function requerirPermiso(PDO $pdo, string $slugSeccion, string $accion = 'ver'): void
{
    requerirLogin();
    if (!tienePermiso($pdo, $slugSeccion, $accion)) {
        http_response_code(403);
        include __DIR__ . '/../includes/403.php';
        exit;
    }
}

/**
 * true si el perfil del usuario logueado está limitado a ver
 * únicamente los clientes donde él figura como responsable.
 */
function restringidoASusClientes(): bool
{
    return !empty($_SESSION['solo_ve_sus_clientes']);
}

/**
 * Corta la ejecución con 403 si el usuario está restringido a "sus"
 * clientes y el cliente pasado no le pertenece (no es su responsable).
 */
function requerirAccesoCliente(?int $usuarioAsignado): void
{
    if (!restringidoASusClientes()) {
        return;
    }
    if ($usuarioAsignado !== (int) $_SESSION['usuario_id']) {
        http_response_code(403);
        include __DIR__ . '/403.php';
        exit;
    }
}

/**
 * Recibe una entrada de $_FILES, valida que sea una imagen y la guarda
 * redimensionada (máximo 400px de lado) dentro de assets/uploads/clientes.
 * Devuelve la ruta relativa a guardar en la base, o null si no se subió nada.
 * Lanza RuntimeException si el archivo no es una imagen válida.
 */
function procesarImagenCliente(array $archivo): ?string
{
    if (!isset($archivo['error']) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir la imagen.');
    }

    $info = getimagesize($archivo['tmp_name']);
    if ($info === false) {
        throw new RuntimeException('El archivo no es una imagen válida.');
    }

    [$anchoOriginal, $altoOriginal, $tipo] = $info;

    $origen = match ($tipo) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($archivo['tmp_name']),
        IMAGETYPE_PNG  => imagecreatefrompng($archivo['tmp_name']),
        IMAGETYPE_WEBP => imagecreatefromwebp($archivo['tmp_name']),
        default        => null,
    };
    if (!$origen) {
        throw new RuntimeException('Formato de imagen no soportado (usá JPG, PNG o WEBP).');
    }

    $maxLado = 400;
    $escala = min(1, $maxLado / max($anchoOriginal, $altoOriginal));
    $anchoFinal = max(1, (int) round($anchoOriginal * $escala));
    $altoFinal  = max(1, (int) round($altoOriginal * $escala));

    $destino = imagecreatetruecolor($anchoFinal, $altoFinal);
    $blanco = imagecolorallocate($destino, 255, 255, 255);
    imagefill($destino, 0, 0, $blanco);
    imagecopyresampled($destino, $origen, 0, 0, 0, 0, $anchoFinal, $altoFinal, $anchoOriginal, $altoOriginal);

    $carpetaFs = $_SERVER['DOCUMENT_ROOT'] . QERP_URL_BASE . '/assets/uploads/clientes';
    if (!is_dir($carpetaFs)) {
        mkdir($carpetaFs, 0755, true);
    }

    $nombreArchivo = 'cliente_' . uniqid() . '.jpg';
    imagejpeg($destino, $carpetaFs . '/' . $nombreArchivo, 82);

    imagedestroy($origen);
    imagedestroy($destino);

    return '/assets/uploads/clientes/' . $nombreArchivo;
}

/**
 * Devuelve la URL de un archivo estático (CSS/JS/img) con un parámetro de
 * versión basado en su fecha de modificación, para evitar que el navegador
 * sirva una copia vieja desde caché después de cada actualización.
 */
function asset(string $ruta): string
{
    $rutaFs = $_SERVER['DOCUMENT_ROOT'] . QERP_URL_BASE . $ruta;
    $version = file_exists($rutaFs) ? filemtime($rutaFs) : time();
    return QERP_URL_BASE . $ruta . '?v=' . $version;
}

/**
 * Sanitiza un string para salida segura en HTML.
 */
function e(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Trae todas las secciones activas del menú, ordenadas.
 */
function obtenerSeccionesMenu(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM qerp_secciones ORDER BY orden ASC');
    return $stmt->fetchAll();
}
