<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../includes/funciones.php';
requerirPermiso($pdo, 'crm', 'editar');

$tituloPagina = 'Editar contacto';
$eyebrowPagina = 'CRM';
$slugSeccionActual = 'crm';

$canales = [
    'llamada'      => 'Llamada Telefónica',
    'whatsapp'     => 'WhatsApp / Mensaje de texto',
    'mail'         => 'Correo Electrónico',
    'reunion'      => 'Reunión Presencial',
    'videollamada' => 'Videollamada',
];

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT a.*, c.nombre AS cliente_nombre, c.usuario_asignado FROM qerp_acciones_contacto a
     INNER JOIN qerp_clientes c ON c.id = a.cliente_id WHERE a.id = :id"
);
$stmt->execute([':id' => $id]);
$accion = $stmt->fetch();

if (!$accion) {
    header('Location: index.php');
    exit;
}

requerirAccesoCliente($accion['usuario_asignado'] !== null ? (int) $accion['usuario_asignado'] : null);

$motivos = $pdo->query('SELECT id, nombre FROM qerp_motivos_contacto WHERE activo = 1 ORDER BY nombre')->fetchAll();
$resultados = $pdo->query('SELECT id, nombre FROM qerp_resultados_contacto WHERE activo = 1 ORDER BY nombre')->fetchAll();

$stmtAdj = $pdo->prepare('SELECT * FROM qerp_adjuntos_contacto WHERE accion_id = :id ORDER BY creado_en');
$stmtAdj->execute([':id' => $id]);
$adjuntos = $stmtAdj->fetchAll();

$errores = [];
$datos = [
    'canal'            => $accion['canal'],
    'motivo_id'        => $accion['motivo_id'],
    'resultado_id'     => $accion['resultado_id'],
    'detalle'          => $accion['detalle'],
    'accion_siguiente' => $accion['accion_siguiente'],
    'fecha_proximo'    => $accion['proximo_seguimiento'] ? date('Y-m-d', strtotime($accion['proximo_seguimiento'])) : '',
    'hora_proximo'     => $accion['proximo_seguimiento'] ? date('H:i', strtotime($accion['proximo_seguimiento'])) : '',
    'prioridad'        => $accion['prioridad'],
    'temperatura'      => $accion['temperatura'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['canal']            = $_POST['canal'] ?? '';
    $datos['motivo_id']        = $_POST['motivo_id'] ?? '';
    $datos['resultado_id']     = $_POST['resultado_id'] ?? '';
    $datos['detalle']          = trim($_POST['detalle'] ?? '');
    $datos['accion_siguiente'] = trim($_POST['accion_siguiente'] ?? '');
    $datos['fecha_proximo']    = trim($_POST['fecha_proximo'] ?? '');
    $datos['hora_proximo']     = trim($_POST['hora_proximo'] ?? '');
    $datos['prioridad']        = $_POST['prioridad'] ?? '';
    $datos['temperatura']      = $_POST['temperatura'] ?? '';

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

    if (!$errores) {
        try {
            $proximoSeguimiento = null;
            if ($datos['fecha_proximo'] !== '') {
                $hora = $datos['hora_proximo'] !== '' ? $datos['hora_proximo'] : '00:00';
                $proximoSeguimiento = $datos['fecha_proximo'] . ' ' . $hora . ':00';
            }

            $stmt = $pdo->prepare(
                'UPDATE qerp_acciones_contacto SET canal = :canal, motivo_id = :motivo_id, resultado_id = :resultado_id,
                 detalle = :detalle, proximo_seguimiento = :seguimiento, accion_siguiente = :accion_siguiente,
                 prioridad = :prioridad, temperatura = :temperatura
                 WHERE id = :id'
            );
            $stmt->execute([
                ':canal'            => $datos['canal'],
                ':motivo_id'        => $datos['motivo_id'] ?: null,
                ':resultado_id'     => $datos['resultado_id'] ?: null,
                ':detalle'          => $datos['detalle'] ?: null,
                ':seguimiento'      => $proximoSeguimiento,
                ':accion_siguiente' => $datos['accion_siguiente'] ?: null,
                ':prioridad'        => $datos['prioridad'] ?: null,
                ':temperatura'      => $datos['temperatura'] ?: null,
                ':id'               => $id,
            ]);

            procesarAdjuntosContacto($pdo, $_FILES['adjuntos'] ?? [], $id);

            $_SESSION['flash_ok'] = 'Contacto actualizado correctamente.';
            header('Location: index.php');
            exit;
        } catch (RuntimeException $e) {
            $errores[] = $e->getMessage();
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width:680px;">
    <div class="card-header"><h3>Editar contacto — <?= e($accion['cliente_nombre']) ?></h3></div>

    <?php foreach ($errores as $err): ?>
        <div class="alerta alerta-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
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
            <label>Adjuntos</label>
            <?php if ($adjuntos): ?>
                <ul class="dropzone-lista" style="margin-bottom:10px;">
                    <?php foreach ($adjuntos as $a): ?>
                        <li>
                            <a href="<?= QERP_URL_BASE . e($a['ruta']) ?>" target="_blank"><?= e($a['nombre_original']) ?></a>
                            <form method="post" action="eliminar-adjunto.php" style="display:inline;"
                                  onsubmit="return confirm('¿Quitar este adjunto?');">
                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                <input type="hidden" name="accion_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-danger btn-sm" style="padding:1px 8px;">Quitar</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if (count($adjuntos) < 5): ?>
                <div class="dropzone">
                    <input type="file" name="adjuntos[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp">
                    <p style="margin:0;">Arrastrá archivos acá o hacé clic para elegirlos<br>(hasta <?= 5 - count($adjuntos) ?> más, 5MB cada uno)</p>
                    <ul class="dropzone-lista"></ul>
                </div>
            <?php endif; ?>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
