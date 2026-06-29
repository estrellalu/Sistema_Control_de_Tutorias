<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/SupabaseClient.php';

require_login_api(); // admin o profesor

$supabase = new SupabaseClient();
$method   = $_SERVER['REQUEST_METHOD'];
$role     = current_role();

switch ($method) {

    case 'GET':
        $query = ['select' => '*', 'order' => 'nombre.asc'];

        if ($role === 'profesor') {
            // Un profesor solo ve a SUS alumnos asignados
            $query['profesor_id'] = 'eq.' . current_profesor_id();
        }
        // Filtro opcional ?profesor_id=xxx (solo útil para el admin)
        if ($role === 'admin' && !empty($_GET['profesor_id'])) {
            $query['profesor_id'] = 'eq.' . $_GET['profesor_id'];
        }

        $result = $supabase->get('alumnos', $query);
        echo json_encode($result);
        break;

    case 'POST':
        require_role_api('admin'); // solo el admin da de alta alumnos
        $input = read_json_body();

        $required = ['matricula', 'nombre'];
        foreach ($required as $f) {
            if (empty($input[$f])) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => "El campo '$f' es obligatorio"]);
                exit;
            }
        }

        $data = [
            'matricula'    => trim($input['matricula']),
            'nombre'       => trim($input['nombre']),
            'carrera'      => $input['carrera'] ?? null,
            'grupo'        => $input['grupo'] ?? null,
            'semestre'     => $input['semestre'] ?? null,
            'email'        => $input['email'] ?? null,
            'telefono'     => $input['telefono'] ?? null,
            'profesor_id'  => $input['profesor_id'] ?? null,
            'nivel_riesgo' => $input['nivel_riesgo'] ?? 'bajo',
            'activo'       => true,
        ];

        $result = $supabase->insert('alumnos', $data);
        if (isset($result['error']) && $result['error']) {
            http_response_code($result['status'] >= 400 ? $result['status'] : 500);
            $msg = stripos($result['message'], 'duplicate') !== false
                ? 'Ya existe un alumno con esa matrícula'
                : $result['message'];
            echo json_encode(['error' => true, 'message' => $msg]);
            exit;
        }
        echo json_encode($result);
        break;

    case 'PUT':
        require_role_api('admin');
        $input = read_json_body();
        $id = $input['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Falta el id del alumno']);
            exit;
        }

        $data = [];
        foreach (['matricula','nombre','carrera','grupo','semestre','email','telefono','profesor_id','nivel_riesgo','activo'] as $field) {
            if (array_key_exists($field, $input)) {
                $data[$field] = $input[$field];
            }
        }

        $result = $supabase->update('alumnos', ['id' => 'eq.' . $id], $data);
        if (isset($result['error']) && $result['error']) {
            http_response_code($result['status'] >= 400 ? $result['status'] : 500);
            echo json_encode(['error' => true, 'message' => $result['message']]);
            exit;
        }
        echo json_encode($result);
        break;

    case 'DELETE':
        require_role_api('admin');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Falta el id del alumno']);
            exit;
        }
        $result = $supabase->delete('alumnos', ['id' => 'eq.' . $id]);
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
