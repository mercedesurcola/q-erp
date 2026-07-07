-- =========================================================
-- QERP - Migración: menú Configuración + rename sección Acciones
-- =========================================================

UPDATE qerp_secciones SET grupo = 'Configuración' WHERE slug IN ('motivos-contacto', 'resultados-contacto');
UPDATE qerp_secciones SET nombre = 'Acciones' WHERE slug = 'crm';
