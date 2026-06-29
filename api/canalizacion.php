<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/SupabaseClient.php';

require_login_api();

$supabase = new SupabaseClient();
$method   = $_SERVER['REQUEST_METHOD'];
$role     = current_role();

function canalizacion_is_owner($supabase, $id, $role) {
    if ($role === 'admin') return true;
    $rows = $supabase->get('canalizaciones', ['id' => 'eq.' . $id, 'select' => 'profesor_id', 'limit' => 1]);
    if (empty($rows)) return false;
    return $rows[0]['profesor_id'] === current_profesor_id();
}

switch ($method) {

    case 'GET':
        $query = ['select' => '*,alumnos(nombre,matricula)', 'order' => 'fecha.desc'];
        if ($role === 'profesor') {
            $query['profesor_id'] = 'eq.' . current_profesor_id();
        } elseif (!empty($_GET['profesor_id'])) {
            $query['profesor_id'] = 'eq.' . $_GET['profesor_id'];
        }
        if (!empty($_GET['estado'])) {
            $query['estado'] = 'eq.' . $_GET['estado'];
        }
        echo json_encode($supabase->get('canalizaciones', $query));
        break;

    case 'POST':
        $input = read_json_body();
        foreach (['alumno_id', 'area_canalizacion', 'motivo'] as $f) {
            if (empty($input[$f])) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => "El campo '$f' es obligatorio"]);
                exit;
            }
        }
        $profesorId = $role === 'profesor' ? current_profesor_id() : ($input['profesor_id'] ?? null);

        $data = [
            'alumno_id'         => $input['alumno_id'],
            'profesor_id'       => $profesorId,
            'area_canalizacion' => $input['area_canalizacion'],
            'motivo'            => $input['motivo'],
            'fecha'             => $input['fecha'] ?? date('Y-m-d'),
            'estado'            => $input['estado'] ?? 'pendiente',
            'seguimiento'       => $input['seguimiento'] ?? null,
        ];

        $result = $supabase->insert('canalizaciones', $data);
        if (isset($result['error']) && $result['error']) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => $result['message']]);
            exit;
        }
        echo json_encode($result);
        break;

    case 'PUT':
        $input = read_json_body();
        $id = $input['id'] ?? null;
        if (!$id || !canalizacion_is_owner($supabase, $id, $role)) {
            http_response_code(403);
            echo json_encode(['error' => true, 'message' => 'No autorizado']);
            exit;
        }
        $data = [];
        foreach (['area_canalizacion', 'motivo', 'fecha', 'estado', 'seguimiento'] as $field) {
            if (array_key_exists($field, $input)) $data[$field] = $input[$field];
        }
        $result = $supabase->update('canalizaciones', ['id' => 'eq.' . $id], $data);
        echo json_encode($result);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id || !canalizacion_is_owner($supabase, $id, $role)) {
            http_response_code(403);
            echo json_encode(['error' => true, 'message' => 'No autorizado']);
            exit;
        }
        $supabase->delete('canalizaciones', ['id' => 'eq.' . $id]);
        echo json_encode(['error' => false]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => true, 'message' => 'Método no permitido']);
}
