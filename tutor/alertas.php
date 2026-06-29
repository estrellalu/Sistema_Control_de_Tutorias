<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_page('profesor');
$active = 'alertas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alertas de riesgo - Tutor – Control de Tutorías UNSIS</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="main">
    <div class="toolbar">
      <div>
        <h2 style="margin-bottom:2px;">Alertas de riesgo de deserción</h2>
        <p class="page-subtitle" style="margin:0;">Registra señales de alerta sobre tus alumnos</p>
      </div>
      <button class="btn" onclick="abrirNuevaAlerta()">+ Nueva alerta</button>
    </div>

    <div id="msg" class="alert-msg" style="display:none;"></div>

    <div class="card">
      <div class="toolbar filtros" style="margin-bottom:10px;">
        <select id="filtroNivel" onchange="renderTabla()">
          <option value="">Todos los niveles</option>
          <option value="bajo">Bajo</option>
          <option value="medio">Medio</option>
          <option value="alto">Alto</option>
        </select>
        <select id="filtroAtendida" onchange="renderTabla()">
          <option value="">Atendidas y sin atender</option>
          <option value="false">Solo sin atender</option>
          <option value="true">Solo atendidas</option>
        </select>
      </div>
      <table>
        <thead><tr><th>Fecha</th><th>Alumno</th><th>Tipo</th><th>Riesgo</th><th>Atendida</th><th>Acciones</th></tr></thead>
        <tbody id="tabla"><tr><td colspan="6" class="empty-state">Cargando...</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal">
  <div class="modal-box">
    <h3 id="modalTitulo">Nueva alerta</h3>
    <div id="modalMsg" class="alert-msg error" style="display:none;"></div>
    <form id="form">
      <input type="hidden" id="f_id">
      <label>Alumno</label>
      <select id="f_alumno_id" required></select>

      <label>Tipo de alerta</label>
      <select id="f_tipo" required>
        <option value="Inasistencias">Inasistencias recurrentes</option>
        <option value="Bajo rendimiento">Bajo rendimiento académico</option>
        <option value="Económico">Problema económico</option>
        <option value="Emocional">Problema emocional / familiar</option>
        <option value="Riesgo de baja">Riesgo de baja temporal/definitiva</option>
        <option value="Otro">Otro</option>
      </select>

      <label>Nivel de riesgo</label>
      <select id="f_nivel">
        <option value="bajo">Bajo</option>
        <option value="medio" selected>Medio</option>
        <option value="alto">Alto</option>
      </select>

      <label>Descripción</label>
      <textarea id="f_descripcion" placeholder="Detalla la situación observada..."></textarea>

      <label>Fecha</label>
      <input type="date" id="f_fecha" required>

      <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" id="f_atendida" style="width:auto;"> Ya fue atendida
      </label>

      <div class="modal-actions">
        <button type="submit" class="btn" id="btnGuardar">Guardar</button>
        <button type="button" class="btn btn-secundario" onclick="closeModal('modal')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="/assets/js/common.js"></script>
<script src="/assets/js/alertas.js"></script>
</body>
</html>
