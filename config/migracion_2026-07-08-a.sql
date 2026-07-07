-- =========================================================
-- QERP - Migración: ABM Productos/Servicios + asociación a acciones
-- =========================================================

CREATE TABLE qerp_productos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(150) NOT NULL,
  detalle TEXT DEFAULT NULL,
  tipo ENUM('producto', 'servicio') NOT NULL DEFAULT 'producto',
  precio DECIMAL(12,2) DEFAULT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE qerp_accion_productos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  accion_id INT NOT NULL,
  producto_id INT NOT NULL,
  comentario VARCHAR(255) DEFAULT NULL,
  valor DECIMAL(12,2) DEFAULT NULL,
  FOREIGN KEY (accion_id) REFERENCES qerp_acciones_contacto(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES qerp_productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO qerp_secciones (nombre, slug, icono, grupo, orden) VALUES
  ('Productos/Servicios', 'productos', 'box', 'Configuración', 7);

INSERT INTO qerp_perfil_permisos (perfil_id, seccion_id, ver, crear, editar, eliminar)
SELECT p.id, s.id, 1, 1, 1, 1
FROM qerp_perfiles p
CROSS JOIN qerp_secciones s
WHERE p.nombre = 'Administrador' AND s.slug = 'productos';
