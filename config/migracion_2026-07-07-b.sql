-- =========================================================
-- QERP - Migración: registro de contacto ampliado
-- (canal, motivo, resultado, prioridad, temperatura, adjuntos)
-- IMPORTANTE: antes de correr esto, verificar que no haya filas con
-- qerp_acciones_contacto.tipo = 'otro' (se elimina ese valor del enum):
--   SELECT COUNT(*) FROM qerp_acciones_contacto WHERE tipo = 'otro';
-- Si hay filas, avisar antes de continuar.
-- =========================================================

CREATE TABLE qerp_motivos_contacto (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE qerp_resultados_contacto (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO qerp_motivos_contacto (nombre) VALUES
  ('Primer contacto / Prospección'),
  ('Presentación de producto / Demo'),
  ('Seguimiento de propuesta enviada'),
  ('Negociación de precio / Cierre'),
  ('Recuperación de contacto perdido');

INSERT INTO qerp_resultados_contacto (nombre) VALUES
  ('Exitoso'),
  ('No atendió'),
  ('Pidió que llamen más tarde'),
  ('Rechazó la propuesta');

ALTER TABLE qerp_acciones_contacto
  CHANGE COLUMN tipo canal ENUM('llamada','whatsapp','mail','reunion','videollamada') NOT NULL,
  ADD COLUMN motivo_id INT DEFAULT NULL AFTER canal,
  ADD COLUMN resultado_id INT DEFAULT NULL AFTER motivo_id,
  ADD COLUMN accion_siguiente VARCHAR(255) DEFAULT NULL AFTER proximo_seguimiento,
  ADD COLUMN prioridad ENUM('alta','media','baja') DEFAULT NULL AFTER accion_siguiente,
  ADD COLUMN temperatura ENUM('frio','tibio','caliente') DEFAULT NULL AFTER prioridad;

ALTER TABLE qerp_acciones_contacto
  ADD FOREIGN KEY (motivo_id) REFERENCES qerp_motivos_contacto(id) ON DELETE RESTRICT,
  ADD FOREIGN KEY (resultado_id) REFERENCES qerp_resultados_contacto(id) ON DELETE RESTRICT;

CREATE TABLE qerp_adjuntos_contacto (
  id INT PRIMARY KEY AUTO_INCREMENT,
  accion_id INT NOT NULL,
  nombre_original VARCHAR(255) NOT NULL,
  ruta VARCHAR(255) NOT NULL,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (accion_id) REFERENCES qerp_acciones_contacto(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO qerp_secciones (nombre, slug, icono, grupo, orden) VALUES
  ('Motivos de contacto', 'motivos-contacto', 'tag', 'Administración', 5),
  ('Resultados de contacto', 'resultados-contacto', 'flag', 'Administración', 6);

-- El perfil "Administrador" recibe acceso total a las 2 secciones nuevas
-- (si no, no aparecerían en su menú ni podría gestionarlas)
INSERT INTO qerp_perfil_permisos (perfil_id, seccion_id, ver, crear, editar, eliminar)
SELECT p.id, s.id, 1, 1, 1, 1
FROM qerp_perfiles p
CROSS JOIN qerp_secciones s
WHERE p.nombre = 'Administrador' AND s.slug IN ('motivos-contacto', 'resultados-contacto');
