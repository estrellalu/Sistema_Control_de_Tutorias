<?php
/**
 * config/config.php
 * -----------------------------------------------------------------
 * AQUÍ es donde pones los datos de tu proyecto de Supabase.
 *
 * Cómo obtenerlos:
 *  1. Entra a https://supabase.com -> tu proyecto
 *  2. Menú "Project Settings" (engranaje) -> "API"
 *  3. Copia:
 *       - "Project URL"           -> SUPABASE_URL
 *       - "service_role" secret   -> SUPABASE_SERVICE_KEY
 *
 * IMPORTANTE: la "service_role key" tiene permisos totales y NUNCA debe
 * mandarse al navegador / JavaScript del cliente. Solo se usa aquí,
 * dentro del backend PHP, que es código que corre en el servidor.
 * -----------------------------------------------------------------
 */

// ============ DATOS DE SUPABASE (reemplaza con los tuyos) ============
define('SUPABASE_URL', 'https://zndaspozlfhdjbesetli.supabase.co');
define('SUPABASE_SERVICE_KEY', 'Pon tu llave aqui');
define('SUPABASE_REST_URL', SUPABASE_URL . '/rest/v1');

// ============ ADMINISTRADOR FIJO (no vive en la base de datos) =======
// Usuario y contraseña por defecto: admin@tutorias.com / Admin123!
// El hash de abajo corresponde a "Admin123!". Para cambiar la
// contraseña, genera un nuevo hash ejecutando en una terminal:
//   php -r "echo password_hash('TU_NUEVA_CLAVE', PASSWORD_BCRYPT);"
// y pega el resultado aquí.
define('ADMIN_EMAIL', 'admin@tutorias.com');
define('ADMIN_PASSWORD_HASH', '$2b$10$8rZCAclNvwc2b38iPBdZ8ex8m8a7GGlz.JRNsg6Gb3upuQDbewlmu');

// ============ SESIONES ============
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zona horaria (ajusta a tu región si lo necesitas)
date_default_timezone_set('America/Mexico_City');

// Mostrar errores en desarrollo local (¡desactiva esto en producción!)
error_reporting(E_ALL);
ini_set('display_errors', '1');
