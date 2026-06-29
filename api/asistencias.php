<?php
/**
 * api/asistencias.php
 * -----------------------------------------------------------------
 * Lista de asistencia del profesor: cada alumno tiene 8 "casillas"
 * (numero_sesion 1 a 8). Esta API guarda/lee esas marcas para poder
 * ver cuántas asistencias acumula cada alumno.
 * -----------------------------------------------------------------
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/SupabaseClient.php';

require_login_api(); // admin o profesor

$supabase = new SupabaseClient();
$method   = $_SERVER['REQUEST_METHOD'];
$role     = current_role();

switch ($method) {

    case 'GET':
        $query = ['select' => '*', 'order' => 'numero_sesion.asc'];

        if ($role === 'profesor') {
            // Un profesor solo ve la asistencia de SUS alumnos
            $query['profesor_id'] = 'eq.' . current_profesor_id();
        } elseif (!empty($_GET['profesor_id'])) {
            $query['profesor_id'] = 'eq.' . $_GET['profesor_id'];
        }
        if (!empty($_GET['alumno_id'])) {
            $query['alumno_id'] = 'eq.' . $_GET['alumno_id'];
        }

        echo json_encode($supabase->get('asistencias', $query));
        break;

    case 'POST':
        // Guarda (o actualiza) varias casillas de asistencia de un jalón.
        // Body esperado: { registros: [ { alumno_id, numero_sesion, presente }, ... ] }
        $input     = read_json_body();
        $registros = $input['registros'] ?? null;

        if (!is_array($registros) || empty($registros)) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => "El campo 'registros' es obligatorio"]);
            exit;
        }

        $profesorId = $role === 'profesor' ? current_profesor_id() : ($input['profesor_id'] ?? null);
        if (empty($profesorId)) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => "El campo 'profesor_id' es obligatorio"]);
            exit;
        }

        // Seguridad: solo se puede tomar asistencia de alumnos asignados a este profesor
        $misAlumnos = $supabase->get('alumnos', ['profesor_id' => 'eq.' . $profesorId, 'select' => 'id']);
        $idsValidos = array_column($misAlumnos, 'id');

        $filas = [];
        foreach ($registros as $r) {
            $alumnoId     = $r['alumno_id'] ?? null;
            $numeroSesion = $r['numero_sesion'] ?? null;

            if (empty($alumnoId) || empty($numeroSesion)) continue;
            if (!in_array($alumnoId, $idsValidos, true)) continue;
            if ($numeroSesion < 1 || $numeroSesion > 8) continue;

            $filas[] = [
                'alumno_id'     => $alumnoId,
                'profesor_id'   => $profesorId,
                'numero_sesion' => (int) $numeroSesion,
                'presente'      => !empty($r['presente']),
                'fecha'         => $r['fecha'] ?? date('Y-m-d'),
            ];
        }

        if (empty($filas)) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'No hay registros válidos para guardar']);
            exit;
        }

        $result = $supabase->upsert('asistencias', $filas, 'alumno_id,numero_sesion');
        if (isset($result['error']) && $result['error']) {
            http_response_code($result['status'] >= 400 ? $result['status'] : 500);
            echo json_encode(['error' => true, 'message' => $result['message']]);
            exit;
        }
        echo json_encode($result);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => true, 'message' => 'Método no permitido']);
}
