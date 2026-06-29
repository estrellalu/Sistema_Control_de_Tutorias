<?php
/**
 * includes/auth.php
 * Funciones de sesión y control de acceso.
 * role: 'admin' | 'profesor'
 */

require_once __DIR__ . '/../config/config.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user_role']);
}

function current_role(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

function current_profesor_id(): ?string
{
    return $_SESSION['profesor_id'] ?? null;
}

function login_admin(): void
{
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Administrador';
}

function login_profesor(array $profesor): void
{
    $_SESSION['user_role']   = 'profesor';
    $_SESSION['profesor_id'] = $profesor['id'];
    $_SESSION['user_name']   = $profesor['nombre'];
    $_SESSION['user_email']  = $profesor['email'];
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}

/** Corta la ejecución de una página HTML si no hay sesión válida */
function require_role_page(string $role): void
{
    if (!is_logged_in() || current_role() !== $role) {
        header('Location: /index.php');
        exit;
    }
}

/** Corta la ejecución de un endpoint API (responde JSON 401/403) */
function require_role_api(string $role): void
{
    header('Content-Type: application/json');
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['error' => true, 'message' => 'No has iniciado sesión']);
        exit;
    }
    if (current_role() !== $role) {
        http_response_code(403);
        echo json_encode(['error' => true, 'message' => 'No tienes permiso para esta acción']);
        exit;
    }
}

/** Permite admin o profesor logueado (cualquiera de los dos) */
function require_login_api(): void
{
    header('Content-Type: application/json');
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['error' => true, 'message' => 'No has iniciado sesión']);
        exit;
    }
}

/** Lee el body JSON de una petición fetch() */
function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
