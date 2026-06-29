<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/SupabaseClient.php';

header('Content-Type: application/json');

$input = read_json_body();
$email    = trim($input['email'] ?? '');
$password = (string)($input['password'] ?? '');

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Correo y contraseña son obligatorios']);
    exit;
}

// 1) ¿Es el admin fijo?
if (strcasecmp($email, ADMIN_EMAIL) === 0) {
    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        login_admin();
        echo json_encode(['error' => false, 'role' => 'admin', 'redirect' => '/admin/dashboard.php']);
        exit;
    }
    http_response_code(401);
    echo json_encode(['error' => true, 'message' => 'Contraseña incorrecta']);
    exit;
}

// 2) ¿Es un profesor registrado en Supabase?
$supabase = new SupabaseClient();
$result = $supabase->get('profesores', [
    'email'  => 'eq.' . $email,
    'select' => '*',
    'limit'  => 1,
]);

if (isset($result['error']) && $result['error']) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $result['message']]);
    exit;
}

if (empty($result)) {
    http_response_code(401);
    echo json_encode(['error' => true, 'message' => 'Correo o contraseña incorrectos']);
    exit;
}

$profesor = $result[0];

if (!$profesor['activo']) {
    http_response_code(403);
    echo json_encode(['error' => true, 'message' => 'Tu cuenta está desactivada. Contacta al administrador.']);
    exit;
}

if (!password_verify($password, $profesor['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => true, 'message' => 'Correo o contraseña incorrectos']);
    exit;
}

login_profesor($profesor);
echo json_encode(['error' => false, 'role' => 'profesor', 'redirect' => '/tutor/dashboard.php']);
