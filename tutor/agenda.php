<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_page('profesor');
$active = 'agenda';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agenda de citas - Tutor – Control de Tutorías UNSIS</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="main">
    <div class="toolbar">
      <div>
        <h2 style="margin-bottom:2px;">Agenda de citas</h2>
        <p class="page-subtitle" style="margin:0;">Programa y da seguimiento a tus citas con alumnos</p>
      </div>
      <button class="btn" onclick="abrirNuevaCita()">+ Nueva cita</button>
    </div>

    <div id="msg" class="alert-msg" style="display:none;"></div>

    <div class="card">
      <div class="toolbar filtros" style="margin-bottom:10px;">
        <select id="filtroEstado" onchange="renderTabla()">
          <option value="">Todos los estados</option>
          <option value="pendiente">Pendiente</option>
          <option value="confirmada">Confirmada</option>
          <option value="realizada">Realizada</option>
          <option value="cancelada">Cancelada</option>
        </select>
      </div>
      <table>
        <thead><tr><th>Fecha</th><th>Hora</th><th>Alumno</th><th>Motivo</th><th>Lugar</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody id="tabla"><tr><td colspan="7" class="empty-state">Cargando...</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal">
  <div class="modal-box">
    <h3 id="modalTitulo">Nueva cita</h3>
    <div id="modalMsg" class="alert-msg error" style="display:none;"></div>
    <form id="form">
      <input type="hidden" id="f_id">
      <label>Alumno</label>
      <select id="f_alumno_id" required></select>

      <label>Fecha</label>
      <input type="date" id="f_fecha" required>

      <label>Hora</label>
      <input type="time" id="f_hora" required>

      <label>Motivo</label>
      <input type="text" id="f_motivo" placeholder="Ej. Seguimiento académico">

      <label>Lugar</label>
      <input type="text" id="f_lugar" placeholder="Ej. Cubículo 4 / Google Meet">

      <label>Estado</label>
      <select id="f_estado">
        <option value="pendiente">Pendiente</option>
        <option value="confirmada">Confirmada</option>
        <option value="realizada">Realizada</option>
        <option value="cancelada">Cancelada</option>
      </select>

      <div class="modal-actions">
        <button type="submit" class="btn" id="btnGuardar">Guardar</button>
        <button type="button" class="btn btn-secundario" onclick="closeModal('modal')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="/assets/js/common.js"></script>
<script src="/assets/js/agenda.js"></script>
</body>
</html>
