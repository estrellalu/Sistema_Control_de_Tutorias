<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_page('profesor');
$active = 'bitacora';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bitácora de sesiones - Tutor – Control de Tutorías UNSIS</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="main">
    <div class="toolbar">
      <div>
        <h2 style="margin-bottom:2px;">Bitácora de sesiones</h2>
        <p class="page-subtitle" style="margin:0;">Registra lo tratado en cada sesión con tus alumnos</p>
      </div>
      <button class="btn" onclick="abrirNuevaBitacora()">+ Nuevo registro</button>
    </div>

    <div id="msg" class="alert-msg" style="display:none;"></div>

    <div class="card">
      <div id="lista"><p class="empty-state">Cargando...</p></div>
    </div>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal">
  <div class="modal-box">
    <h3 id="modalTitulo">Nuevo registro de bitácora</h3>
    <div id="modalMsg" class="alert-msg error" style="display:none;"></div>
    <form id="form">
      <input type="hidden" id="f_id">
      <label>Alumno</label>
      <select id="f_alumno_id" required></select>

      <label>Fecha de la sesión</label>
      <input type="date" id="f_fecha_sesion" required>

      <label>Tema tratado</label>
      <input type="text" id="f_tema" required placeholder="Ej. Bajo rendimiento en Cálculo II">

      <label>Observaciones</label>
      <textarea id="f_observaciones" placeholder="Detalles de lo conversado..."></textarea>

      <label>Acuerdos / compromisos</label>
      <textarea id="f_acuerdos" placeholder="Acciones a seguir..."></textarea>

      <div class="modal-actions">
        <button type="submit" class="btn" id="btnGuardar">Guardar</button>
        <button type="button" class="btn btn-secundario" onclick="closeModal('modal')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="/assets/js/common.js"></script>
<script src="/assets/js/bitacora.js"></script>
</body>
</html>
