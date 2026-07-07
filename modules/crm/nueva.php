<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'crm', 'crear');

$tituloPagina = 'Registrar contacto';
$eyebrowPagina = 'CRM';
$slugSeccionActual = 'crm';

$canales = [
    'llamada'     => 'Llamada Telefónica',
    'whatsapp'    => 'WhatsApp / Mensaje de texto',
    'mail'        => 'Correo Electrónico',
    'reunion'     => 'Reunión Presencial',
    'videollamada'=> 'Videollamada',
];

$motivos = $pdo->query('SELECT id, nombre FROM qerp_motivos_contacto WHERE activo = 1 ORDER BY nombre')->fetchAll();
$resultados = $pdo->query('SELECT id, nombre FROM qerp_resultados_contacto WHERE activo = 1 ORDER BY nombre')->fetchAll();

$errores = [];
$datos = [
    'cliente_id'       => (int) ($_GET['cliente_id'] ?? 0),
    'canal'            => 'llamada',
    'motivo_id'        => '',
    'resultado_id'     => '',
    'detalle'          => '',
    'accion_siguiente' => '',
    'fecha_proximo'    => '',
    'hora_proximo'     => '',
    'prioridad'        => '',
    'temperatura'      => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['cliente_id']       = (int) ($_POST['cliente_id'] ?? 0);
    $datos['canal']            = $_POST['canal'] ?? '';
    $datos['motivo_id']        = $_POST['motivo_id'] ?? '';
    $datos['resultado_id']     = $_POST['resultado_id'] ?? '';
    $datos['detalle']          = trim($_POST['detalle'] ?? '');
    $datos['accion_siguiente'] = trim($_POST['accion_siguiente'] ?? '');
    $datos['fecha_proximo']    = trim($_POST['fecha_proximo'] ?? '');
    $datos['hora_proximo']     = trim($_POST['hora_proximo'] ?? '');
    $datos['prioridad']        = $_POST['prioridad'] ?? '';
    $datos['temperatura']      = $_POST['temperatura'] ?? '';

    if (!$datos['cliente_id']) $errores[] = 'Elegí un cliente del buscador.';
    if (!isset($canales[$datos['canal']])) $errores[] = 'Elegí un canal de contacto válido.';
    if ($datos['accion_siguiente'] === '') {
        $errores[] = 'La acción del próximo paso es obligatoria: ningún contacto puede quedar sin definir qué sigue.';
    }
    if ($datos['fecha_proximo'] === '') {
        $errores[] = 'La fecha del próximo contacto es obligatoria.';
    }
    if ($datos['motivo_id'] !== '' && !in_array((int) $datos['motivo_id'], array_column($motivos, 'id'), true)) {
        $errores[] = 'El motivo seleccionado no es válido.';
    }
    if ($datos['resultado_id'] !== '' && !in_array((int) $datos['resultado_id'], array_column($resultados, 'id'), true)) {
        $errores[] = 'El resultado seleccionado no es válido.';
    }
    if ($datos['prioridad'] !== '' && !in_array($datos['prioridad'], ['alta', 'media', 'baja'], true)) {
        $errores[] = 'La prioridad seleccionada no es válida.';
    }
    if ($datos['temperatura'] !== '' && !in_array($datos['temperatura'], ['frio', 'tibio', 'caliente'], true)) {
        $errores[] = 'La temperatura seleccionada no es válida.';
    }
}

// El cliente elegido (por GET al entrar, o por POST al reintentar tras un error)
// siempre se valida contra el permiso de "solo ve sus clientes".
$cliente = null;
if ($datos['cliente_id']) {
    $stmt = $pdo->prepare('SELECT id, nombre, imagen, usuario_asignado FROM qerp_clientes WHERE id = :id');
    $stmt->execute([':id' => $datos['cliente_id']]);
    $cliente = $stmt->fetch();
    if (!$cliente) {
        $datos['cliente_id'] = 0;
    } else {
        requerirAccesoCliente($cliente['usuario_asignado'] !== null ? (int) $cliente['usuario_asignado'] : null);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$cliente && !$errores) {
    $errores[] = 'Elegí un cliente del buscador.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errores) {
    $proximoSeguimiento = null;
    if ($datos['fecha_proximo'] !== '') {
        $hora = $datos['hora_proximo'] !== '' ? $datos['hora_proximo'] : '00:00';
        $proximoSeguimiento = $datos['fecha_proximo'] . ' ' . $hora . ':00';
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO qerp_acciones_contacto
                (cliente_id, usuario_id, canal, motivo_id, resultado_id, detalle,
                 proximo_seguimiento, accion_siguiente, prioridad, temperatura)
             VALUES
                (:cliente_id, :usuario_id, :canal, :motivo_id, :resultado_id, :detalle,
                 :proximo_seguimiento, :accion_siguiente, :prioridad, :temperatura)'
        );
        $stmt->execute([
            ':cliente_id'          => $datos['cliente_id'],
            ':usuario_id'          => $_SESSION['usuario_id'],
            ':canal'               => $datos['canal'],
            ':motivo_id'           => $datos['motivo_id'] ?: null,
            ':resultado_id'        => $datos['resultado_id'] ?: null,
            ':detalle'             => $datos['detalle'] ?: null,
            ':proximo_seguimiento' => $proximoSeguimiento,
            ':accion_siguiente'    => $datos['accion_siguiente'] ?: null,
            ':prioridad'           => $datos['prioridad'] ?: null,
            ':temperatura'         => $datos['temperatura'] ?: null,
        ]);
        $accionId = (int) $pdo->lastInsertId();

        procesarAdjuntosContacto($pdo, $_FILES['adjuntos'] ?? [], $accionId);

        $_SESSION['flash_ok'] = 'Contacto registrado correctamente.';
        header('Location: ../clientes/ver.php?id=' . $datos['cliente_id']);
        exit;
    } catch (RuntimeException $e) {
        $errores[] = $e->getMessage();
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:680px;">
    <div class="card-header"><h3>Registrar contacto</h3></div>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="tarjeta-cliente-flotante" id="tarjetaClienteFlotante" <?= $cliente ? '' : 'hidden' ?>>
        <img src="<?= $cliente && $cliente['imagen'] ? QERP_URL_BASE . e($cliente['imagen']) : '' ?>"
             alt="" class="miniatura-cliente" <?= $cliente && $cliente['imagen'] ? '' : 'hidden' ?>>
        <span class="nombre"><?= $cliente ? e($cliente['nombre']) : '' ?></span>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="campo buscador-cliente" data-buscar-url="../clientes/buscar.php">
            <label for="buscador_cliente_input">Buscar prospecto / cliente</label>
            <input type="text" id="buscador_cliente_input" autocomplete="off"
                   placeholder="Escribí nombre, razón social o CUIT..."
                   value="<?= $cliente ? e($cliente['nombre']) : '' ?>">
            <div class="buscador-resultados" hidden></div>
            <input type="hidden" name="cliente_id" value="<?= (int) $datos['cliente_id'] ?>">
        </div>

        <div class="campo">
            <label>Vendedor / Responsable</label>
            <input type="text" value="<?= e($_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido']) ?>" disabled>
        </div>

        <div class="fila-form">
            <div class="campo">
                <label for="canal">Canal de contacto</label>
                <select id="canal" name="canal" required>
                    <?php foreach ($canales as $valor => $etiqueta): ?>
                        <option value="<?= e($valor) ?>" <?= $datos['canal'] === $valor ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label for="motivo_id">Intención / Motivo</label>
                <select id="motivo_id" name="motivo_id">
                    <option value="">Sin especificar</option>
                    <?php foreach ($motivos as $m): ?>
                        <option value="<?= (int) $m['id'] ?>" <?= (string) $m['id'] === (string) $datos['motivo_id'] ? 'selected' : '' ?>><?= e($m['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label for="resultado_id">Resultado / Estado</label>
                <select id="resultado_id" name="resultado_id">
                    <option value="">Sin especificar</option>
                    <?php foreach ($resultados as $r): ?>
                        <option value="<?= (int) $r['id'] ?>" <?= (string) $r['id'] === (string) $datos['resultado_id'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="campo">
            <label for="detalle">Detalle / Notas</label>
            <div class="editor-lite">
                <div class="editor-lite-toolbar">
                    <button type="button" data-formato="negrita"><strong>N</strong></button>
                    <button type="button" data-formato="lista">☰ Lista</button>
                </div>
                <textarea id="detalle" name="detalle" rows="5"
                          placeholder="Escribe aquí los puntos clave, objeciones del cliente o comentarios relevantes..."><?= e($datos['detalle']) ?></textarea>
            </div>
        </div>

        <div class="fila-form">
            <div class="campo">
                <label for="accion_siguiente">Acción del próximo paso</label>
                <input type="text" id="accion_siguiente" name="accion_siguiente" value="<?= e($datos['accion_siguiente']) ?>"
                       placeholder="Ej: Llamar para presupuestar, enviar folleto..." required>
            </div>
            <div class="campo">
                <label for="fecha_proximo">Fecha del próximo contacto</label>
                <input type="date" id="fecha_proximo" name="fecha_proximo" value="<?= e($datos['fecha_proximo']) ?>" required>
            </div>
            <div class="campo">
                <label for="hora_proximo">Hora (opcional)</label>
                <input type="time" id="hora_proximo" name="hora_proximo" value="<?= e($datos['hora_proximo']) ?>">
            </div>
        </div>

        <div class="campo">
            <label>Prioridad</label>
            <div class="radio-pill-group">
                <input type="radio" class="radio-pill" id="prioridad_alta" name="prioridad" value="alta" <?= $datos['prioridad'] === 'alta' ? 'checked' : '' ?>>
                <label for="prioridad_alta" class="prioridad-alta">Alta</label>
                <input type="radio" class="radio-pill" id="prioridad_media" name="prioridad" value="media" <?= $datos['prioridad'] === 'media' ? 'checked' : '' ?>>
                <label for="prioridad_media" class="prioridad-media">Media</label>
                <input type="radio" class="radio-pill" id="prioridad_baja" name="prioridad" value="baja" <?= $datos['prioridad'] === 'baja' ? 'checked' : '' ?>>
                <label for="prioridad_baja" class="prioridad-baja">Baja</label>
            </div>
        </div>

        <div class="campo">
            <label>Temperatura del prospecto</label>
            <div class="radio-semaforo-group">
                <input type="radio" class="radio-semaforo" id="temp_frio" name="temperatura" value="frio" <?= $datos['temperatura'] === 'frio' ? 'checked' : '' ?>>
                <label for="temp_frio" class="semaforo-frio" title="Frío"></label>
                <input type="radio" class="radio-semaforo" id="temp_tibio" name="temperatura" value="tibio" <?= $datos['temperatura'] === 'tibio' ? 'checked' : '' ?>>
                <label for="temp_tibio" class="semaforo-tibio" title="Tibio"></label>
                <input type="radio" class="radio-semaforo" id="temp_caliente" name="temperatura" value="caliente" <?= $datos['temperatura'] === 'caliente' ? 'checked' : '' ?>>
                <label for="temp_caliente" class="semaforo-caliente" title="Caliente"></label>
            </div>
        </div>

        <div class="campo">
            <label>Adjuntar archivos</label>
            <div class="dropzone">
                <input type="file" name="adjuntos[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp">
                <p style="margin:0;">Arrastrá archivos acá o hacé clic para elegirlos<br>(máximo 5, hasta 5MB cada uno — PDF, JPG, PNG o WEBP)</p>
                <ul class="dropzone-lista"></ul>
            </div>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
