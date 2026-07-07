-- =========================================================
-- QERP - Migración: grupos de menú, restricción de vendedor,
-- nombre/razón social separados e imagen de cliente
-- Ejecutar una sola vez sobre la base ya existente.
-- =========================================================

ALTER TABLE qerp_secciones
  ADD COLUMN grupo VARCHAR(50) NOT NULL DEFAULT 'General' AFTER icono;

UPDATE qerp_secciones SET grupo = 'Administración' WHERE slug IN ('usuarios', 'perfiles');
UPDATE qerp_secciones SET grupo = 'CRM' WHERE slug IN ('clientes', 'crm');

ALTER TABLE qerp_perfiles
  ADD COLUMN solo_ve_sus_clientes TINYINT(1) NOT NULL DEFAULT 0 AFTER activo;

ALTER TABLE qerp_clientes
  CHANGE COLUMN razon_social nombre VARCHAR(150) NOT NULL;

ALTER TABLE qerp_clientes
  ADD COLUMN razon_social VARCHAR(150) DEFAULT NULL AFTER nombre,
  ADD COLUMN imagen VARCHAR(255) DEFAULT NULL AFTER notas;
