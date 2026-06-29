<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/SupabaseClient.php';

require_login_api();

$supabase = new SupabaseClient();
$method   = $_SERVER['REQUEST_METHOD'];
$role     = current_role();

function alerta_is_owner($supabase, $id, $role) {
    if ($role === 'admin') return true;
    $rows = $supabase->get('alertas', ['id' => 'eq.' . $id, 'select' => 'profesor_id', 'limit' => 1]);
    if (empty($rows)) return false;
    return $rows[0]['profesor_id'] === current_profesor_id();
}

switch ($method) {

    case 'GET':
        $query = ['select' => '*,alumnos(nombre,matricula,carrera)', 'order' => 'created_at.desc'];
        if ($role === 'profesor') {
            $query['profesor_id'] = 'eq.' . current_profesor_id();
        } elseif (!empty($_GET['profesor_id'])) {
            $query['profesor_id'] = 'eq.' . $_GET['profesor_id'];
        }
        if (!empty($_GET['atendida'])) {
            $query['atendida'] = 'eq.' . $_GET['atendida'];
        }
        if (!empty($_GET['nivel_riesgo'])) {
            $query['nivel_riesgo'] = 'eq.' . $_GET['nivel_riesgo'];
        }
        echo json_encode($supabase->get('alertas', $query));
        break;

    case 'POST':
        $input = read_json_body();
        foreach (['alumno_id', 'tipo_alerta'] as $f) {
            if (empty($input[$f])) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => "El campo '$f' es obligatorio"]);
                exit;
            }
        }
        $profesorId = $role === 'profesor' ? current_profesor_id() : ($input['profesor_id'] ?? null);
        $nivel = $input['nivel_riesgo'] ?? 'medio';

        $data = [
            'alumno_id'    => $input['alumno_id'],
            'profesor_id'  => $profesorId,
            'tipo_alerta'  => $input['tipo_alerta'],
            'nivel_riesgo' => $nivel,
            'descripcion'  => $input['descripcion'] ?? null,
            'fecha'        => $input['fecha'] ?? date('Y-m-d'),
            'atendida'     => false,
        ];

        $result = $supabase->insert('alertas', $data);
        if (isset($result['error']) && $result['error']) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => $result['message']]);
            exit;
        }

        // Sincroniza el nivel de riesgo general del alumno con la última alerta
        $supabase->update('alumnos', ['id' => 'eq.' . $input['alumno_id']], ['nivel_riesgo' => $nivel]);

        echo json_encode($result);
        break;

    case 'PUT':
        $input = read_json_body();
        $id = $input['id'] ?? null;
        if (!$id || !alerta_is_owner($supabase, $id, $role)) {
            http_response_code(403);
            echo json_encode(['error' => true, 'message' => 'No autorizado']);
            exit;
        }
        $data = [];
        foreach (['tipo_alerta', 'nivel_riesgo', 'descripcion', 'fecha', 'atendida'] as $field) {
            if (array_key_exists($field, $input)) $data[$field] = $input[$field];
        }
        $result = $supabase->update('alertas', ['id' => 'eq.' . $id], $data);
        echo json_encode($result);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id || !alerta_is_owner($supabase, $id, $role)) {
            http_response_code(403);
            echo json_encode(['error' => true, 'message' => 'No autorizado']);
            exit;
        }
        $supabase->delete('alertas', ['id' => 'eq.' . $id]);
        echo json_encode(['error' => false]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => true, 'message' => 'Método no permitido']);
}
