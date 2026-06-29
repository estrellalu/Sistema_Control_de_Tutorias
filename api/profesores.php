<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/SupabaseClient.php';

require_role_api('admin'); // solo el admin gestiona profesores

$supabase = new SupabaseClient();
$method   = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $result = $supabase->get('profesores', [
            'select' => '*',
            'order'  => 'nombre.asc',
        ]);
        echo json_encode($result);
        break;

    case 'POST':
        $input = read_json_body();
        $nombre   = trim($input['nombre'] ?? '');
        $email    = trim($input['email'] ?? '');
        $password = (string)($input['password'] ?? '');
        $especialidad = trim($input['especialidad'] ?? '');
        $telefono     = trim($input['telefono'] ?? '');

        if ($nombre === '' || $email === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Nombre, correo y contraseña son obligatorios']);
            exit;
        }

        $data = [
            'nombre'        => $nombre,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'especialidad'  => $especialidad,
            'telefono'      => $telefono,
            'activo'        => true,
        ];

        $result = $supabase->insert('profesores', $data);

        if (isset($result['error']) && $result['error']) {
            http_response_code($result['status'] >= 400 ? $result['status'] : 500);
            $msg = stripos($result['message'], 'duplicate') !== false
                ? 'Ya existe un profesor con ese correo'
                : $result['message'];
            echo json_encode(['error' => true, 'message' => $msg]);
            exit;
        }

        echo json_encode($result);
        break;

    case 'PUT':
        $input = read_json_body();
        $id = $input['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Falta el id del profesor']);
            exit;
        }

        $data = [];
        foreach (['nombre', 'email', 'especialidad', 'telefono', 'activo'] as $field) {
            if (array_key_exists($field, $input)) {
                $data[$field] = $input[$field];
            }
        }
        // Si viene una nueva contraseña, se vuelve a hashear
        if (!empty($input['password'])) {
            $data['password_hash'] = password_hash($input['password'], PASSWORD_BCRYPT);
        }

        $result = $supabase->update('profesores', ['id' => 'eq.' . $id], $data);

        if (isset($result['error']) && $result['error']) {
            http_response_code($result['status'] >= 400 ? $result['status'] : 500);
            echo json_encode(['error' => true, 'message' => $result['message']]);
            exit;
        }

        echo json_encode($result);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Falta el id del profesor']);
            exit;
        }

        $result = $supabase->delete('profesores', ['id' => 'eq.' . $id]);

        if (isset($result['error']) && $result['error']) {
            http_response_code($result['status'] >= 400 ? $result['status'] : 500);
            echo json_encode(['error' => true, 'message' => $result['message']]);
            exit;
        }

        echo json_encode(['error' => false]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => true, 'message' => 'Método no permitido']);
}
