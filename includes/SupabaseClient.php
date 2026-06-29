<?php
/**
 * includes/SupabaseClient.php
 * -----------------------------------------------------------------
 * Pequeño cliente para consumir PostgREST (la API REST que Supabase
 * expone automáticamente sobre tu base de datos Postgres).
 *
 * No requiere Composer ni librerías externas: solo usa la extensión
 * cURL que ya viene integrada en PHP.
 *
 * Documentación de filtros PostgREST: https://docs.postgrest.org
 * -----------------------------------------------------------------
 */

class SupabaseClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = SUPABASE_REST_URL;
        $this->apiKey  = SUPABASE_SERVICE_KEY;
    }

    /**
     * GET /tabla?columna=eq.valor&select=*&order=columna.desc
     */
    public function get(string $table, array $query = []): array
    {
        $url = $this->baseUrl . '/' . $table;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        return $this->request('GET', $url);
    }

    /**
     * POST /tabla  (crea uno o varios registros)
     * Devuelve el/los registros creados gracias al header Prefer: return=representation
     */
    public function insert(string $table, array $data): array
    {
        $url = $this->baseUrl . '/' . $table;
        return $this->request('POST', $url, $data);
    }

    /**
     * PATCH /tabla?id=eq.xxx  (actualiza registros que cumplan el filtro)
     */
    public function update(string $table, array $filters, array $data): array
    {
        $url = $this->baseUrl . '/' . $table . '?' . http_build_query($filters);
        return $this->request('PATCH', $url, $data);
    }

    /**
     * DELETE /tabla?id=eq.xxx
     */
    public function delete(string $table, array $filters): array
    {
        $url = $this->baseUrl . '/' . $table . '?' . http_build_query($filters);
        return $this->request('DELETE', $url);
    }

    /**
     * POST /tabla?on_conflict=col1,col2  (crea o actualiza si ya existe)
     * Usa "Prefer: resolution=merge-duplicates" para que PostgREST
     * actualice la fila si ya existe una con esas columnas (en vez de
     * fallar por duplicado). Útil para guardar varias filas de un
     * jalón, como la lista de asistencia.
     */
    public function upsert(string $table, array $rows, string $onConflict): array
    {
        $url = $this->baseUrl . '/' . $table . '?on_conflict=' . $onConflict;
        return $this->request('POST', $url, $rows, 'resolution=merge-duplicates,return=representation');
    }

    /**
     * Ejecuta la petición HTTP contra PostgREST
     */
    private function request(string $method, string $url, ?array $body = null, string $prefer = 'return=representation'): array
    {
        $ch = curl_init($url);

        $headers = [
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Prefer: ' . $prefer,
        ];

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => true, 'message' => 'Error de conexión con Supabase: ' . $error, 'status' => 0];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            return [
                'error'   => true,
                'message' => $decoded['message'] ?? 'Error desconocido de Supabase',
                'status'  => $httpCode,
                'raw'     => $decoded,
            ];
        }

        // DELETE / PATCH sin filas afectadas puede regresar null o []
        if ($decoded === null) {
            return [];
        }

        return $decoded;
    }
}
