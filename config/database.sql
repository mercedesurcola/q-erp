-- =========================================================
-- QERP - Esquema inicial de Base de Datos
-- Ejecutar completo en phpMyAdmin sobre una BD vacía
-- =========================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------
-- Perfiles (roles): Administrador, Vendedor, etc.
-- ---------------------------------------------------------
CREATE TABLE perfiles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  descripcion VARCHAR(255) DEFAULT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Secciones del sistema (módulos): usuarios, clientes, crm...
-- Sirve para armar el menú y la matriz de permisos.
-- ---------------------------------------------------------
CREATE TABLE secciones (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  icono VARCHAR(50) DEFAULT NULL,
  orden INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Matriz de permisos: qué puede hacer cada perfil en cada sección
-- ---------------------------------------------------------
CREATE TABLE perfil_permisos (
  perfil_id INT NOT NULL,
  seccion_id INT NOT NULL,
  ver TINYINT(1) NOT NULL DEFAULT 0,
  crear TINYINT(1) NOT NULL DEFAULT 0,
  editar TINYINT(1) NOT NULL DEFAULT 0,
  eliminar TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (perfil_id, seccion_id),
  FOREIGN KEY (perfil_id) REFERENCES perfiles(id) ON DELETE CASCADE,
  FOREIGN KEY (seccion_id) REFERENCES secciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Usuarios del sistema
-- ---------------------------------------------------------
CREATE TABLE usuarios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  mail VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  perfil_id INT DEFAULT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  ultimo_acceso DATETIME DEFAULT NULL,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (perfil_id) REFERENCES perfiles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Clientes (CRM)
-- ---------------------------------------------------------
CREATE TABLE clientes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  razon_social VARCHAR(150) NOT NULL,
  nombre_fantasia VARCHAR(150) DEFAULT NULL,
  cuit VARCHAR(20) DEFAULT NULL,
  mail VARCHAR(150) DEFAULT NULL,
  telefono VARCHAR(50) DEFAULT NULL,
  direccion VARCHAR(255) DEFAULT NULL,
  localidad VARCHAR(100) DEFAULT NULL,
  provincia VARCHAR(100) DEFAULT NULL,
  estado ENUM('prospecto','activo','inactivo') NOT NULL DEFAULT 'prospecto',
  origen VARCHAR(100) DEFAULT NULL,
  usuario_asignado INT DEFAULT NULL,
  notas TEXT DEFAULT NULL,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_asignado) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Acciones de contacto (llamadas, mails, reuniones, etc.)
-- ---------------------------------------------------------
CREATE TABLE acciones_contacto (
  id INT PRIMARY KEY AUTO_INCREMENT,
  cliente_id INT NOT NULL,
  usuario_id INT NOT NULL,
  tipo ENUM('llamada','mail','reunion','whatsapp','otro') NOT NULL,
  detalle TEXT DEFAULT NULL,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  proximo_seguimiento DATETIME DEFAULT NULL,
  completado TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------
-- Datos iniciales
-- ---------------------------------------------------------

INSERT INTO perfiles (nombre, descripcion) VALUES
  ('Administrador', 'Acceso total al sistema'),
  ('Vendedor', 'Gestión de clientes y CRM');

INSERT INTO secciones (nombre, slug, icono, orden) VALUES
  ('Usuarios', 'usuarios', 'users', 1),
  ('Perfiles', 'perfiles', 'shield', 2),
  ('Clientes', 'clientes', 'briefcase', 3),
  ('CRM - Acciones de contacto', 'crm', 'phone-call', 4);

-- Administrador: acceso total a todas las secciones
INSERT INTO perfil_permisos (perfil_id, seccion_id, ver, crear, editar, eliminar)
SELECT 1, id, 1, 1, 1, 1 FROM secciones;

-- Vendedor: solo ve/edita clientes y crm, no administra usuarios/perfiles
INSERT INTO perfil_permisos (perfil_id, seccion_id, ver, crear, editar, eliminar)
SELECT 2, id, 1, 1, 1, 0 FROM secciones WHERE slug IN ('clientes','crm');

-- Usuario administrador inicial -> password: Qerp2026! (cambiar luego)
-- Hash bcrypt real, compatible con password_verify() de PHP
INSERT INTO usuarios (nombre, apellido, mail, password, perfil_id) VALUES
  ('Admin', 'Qerp', 'admin@qerp.local', '$2b$10$pL.4gp7LQf5fpkTpLGJ6PecYIfPmtb44tvi5.APWpVW9Nf9/0JSry', 1);
