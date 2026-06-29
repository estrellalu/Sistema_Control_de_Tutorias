<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/SupabaseClient.php';

require_login_api();

$supabase = new SupabaseClient();
$method   = $_SERVER['REQUEST_METHOD'];
$role     = current_role();

function check_ownership_or_admin($supabase, $table, $id, $role) {
    if ($role === 'admin') return true;
    $rows = $supabase->get($table, ['id' => 'eq.' . $id, 'select' => 'profesor_id', 'limit' => 1]);
    if (empty($rows)) return false;
    return $rows[0]['profesor_id'] === current_profesor_id();
}

switch ($method) {

    case 'GET':
        $query = ['select' => '*,alumnos(nombre,matricula)', 'order' => 'fecha.asc,hora.asc'];
        if ($role === 'profesor') {
            $query['profesor_id'] = 'eq.' . current_profesor_id();
        } elseif (!empty($_GET['profesor_id'])) {
            $query['profesor_id'] = 'eq.' . $_GET['profesor_id'];
        }
        if (!empty($_GET['alumno_id'])) {
            $query['alumno_id'] = 'eq.' . $_GET['alumno_id'];
        }
        echo json_encode($supabase->get('citas', $query));
        break;

    case 'POST':
        $input = read_json_body();
        foreach (['alumno_id', 'fecha', 'hora'] as $f) {
            if (empty($input[$f])) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => "El campo '$f' es obligatorio"]);
                exit;
            }
        }
        $profesorId = $role === 'profesor' ? current_profesor_id() : ($input['profesor_id'] ?? null);
        if (!$profesorId) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Falta el profesor responsable de la cita']);
            exit;
        }

        $data = [
            'alumno_id'   => $input['alumno_id'],
            'profesor_id' => $profesorId,
            'fecha'       => $input['fecha'],
            'hora'        => $input['hora'],
            'motivo'      => $input['motivo'] ?? null,
            'lugar'       => $input['lugar'] ?? null,
            'estado'      => $input['estado'] ?? 'pendiente',
        ];

        $result = $supabase->insert('citas', $data);
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
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Falta el id de la cita']);
            exit;
        }
        if (!check_ownership_or_admin($supabase, 'citas', $id, $role)) {
            http_response_code(403);
            echo json_encode(['error' => true, 'message' => 'No puedes modificar una cita que no es tuya']);
            exit;
        }
        $data = [];
        foreach (['fecha', 'hora', 'motivo', 'lugar', 'estado', 'alumno_id'] as $field) {
            if (array_key_exists($field, $input)) $data[$field] = $input[$field];
        }
        $result = $supabase->update('citas', ['id' => 'eq.' . $id], $data);
        if (isset($result['error']) && $result['error']) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => $result['message']]);
            exit;
        }
        echo json_encode($result);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Falta el id de la cita']);
            exit;
        }
        if (!check_ownership_or_admin($supabase, 'citas', $id, $role)) {
            http_response_code(403);
            echo json_encode(['error' => true, 'message' => 'No puedes eliminar una cita que no es tuya']);
            exit;
        }
        $result = $supabase->delete('citas', ['id' => 'eq.' . $id]);
        echo json_encode(['error' => false]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => true, 'message' => 'Método no permitido']);
}
