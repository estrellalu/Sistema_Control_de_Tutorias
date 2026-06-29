<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_page('profesor');
$active = 'canalizacion';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Canalizaciones - Tutor – Control de Tutorías UNSIS</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="main">
    <div class="toolbar">
      <div>
        <h2 style="margin-bottom:2px;">Reportes de canalización</h2>
        <p class="page-subtitle" style="margin:0;">Deriva alumnos a apoyo psicológico, médico, académico, etc.</p>
      </div>
      <button class="btn" onclick="abrirNuevaCanalizacion()">+ Nueva canalización</button>
    </div>

    <div id="msg" class="alert-msg" style="display:none;"></div>

    <div class="card">
      <div class="toolbar filtros" style="margin-bottom:10px;">
        <select id="filtroEstado" onchange="renderTabla()">
          <option value="">Todos los estados</option>
          <option value="pendiente">Pendiente</option>
          <option value="en_proceso">En proceso</option>
          <option value="atendida">Atendida</option>
        </select>
      </div>
      <table>
        <thead><tr><th>Fecha</th><th>Alumno</th><th>Área</th><th>Motivo</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody id="tabla"><tr><td colspan="6" class="empty-state">Cargando...</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal">
  <div class="modal-box">
    <h3 id="modalTitulo">Nueva canalización</h3>
    <div id="modalMsg" class="alert-msg error" style="display:none;"></div>
    <form id="form">
      <input type="hidden" id="f_id">
      <label>Alumno</label>
      <select id="f_alumno_id" required></select>

      <label>Área de canalización</label>
      <select id="f_area" required>
        <option value="Psicológico">Psicológico</option>
        <option value="Médico">Médico</option>
        <option value="Académico">Académico / Asesorías</option>
        <option value="Económico">Económico / Becas</option>
        <option value="Servicios escolares">Servicios escolares</option>
        <option value="Otro">Otro</option>
      </select>

      <label>Motivo</label>
      <textarea id="f_motivo" required placeholder="Describe la razón de la canalización..."></textarea>

      <label>Fecha</label>
      <input type="date" id="f_fecha" required>

      <label>Estado</label>
      <select id="f_estado">
        <option value="pendiente">Pendiente</option>
        <option value="en_proceso">En proceso</option>
        <option value="atendida">Atendida</option>
      </select>

      <label>Seguimiento</label>
      <textarea id="f_seguimiento" placeholder="Notas de seguimiento (opcional)"></textarea>

      <div class="modal-actions">
        <button type="submit" class="btn" id="btnGuardar">Guardar</button>
        <button type="button" class="btn btn-secundario" onclick="closeModal('modal')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="/assets/js/common.js"></script>
<script src="/assets/js/canalizacion.js"></script>
</body>
</html>
