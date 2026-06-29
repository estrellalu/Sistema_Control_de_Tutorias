<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_page('admin');
$active = 'profesores';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profesores / Tutores – Control de Tutorías UNSIS</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="main">
    <div class="toolbar">
      <div>
        <h2 style="margin-bottom:2px;">Profesores / Tutores</h2>
        <p class="page-subtitle" style="margin:0;">Crea las cuentas con las que cada tutor iniciará sesión</p>
      </div>
      <button class="btn" onclick="abrirNuevo()">+ Nuevo profesor</button>
    </div>

    <div id="msg" class="alert-msg" style="display:none;"></div>

    <div class="card">
      <table>
        <thead>
          <tr><th>Nombre</th><th>Correo</th><th>Área</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody id="tabla"><tr><td colspan="5" class="empty-state">Cargando...</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal">
  <div class="modal-box">
    <h3 id="modalTitulo">Nuevo profesor</h3>
    <div id="modalMsg" class="alert-msg error" style="display:none;"></div>
    <form id="form">
      <input type="hidden" id="f_id">
      <label>Nombre completo</label>
      <input type="text" id="f_nombre" required>

      <label>Correo electrónico</label>
      <input type="email" id="f_email" required>

      <label id="lblPassword">Contraseña</label>
      <input type="password" id="f_password" placeholder="Mínimo 6 caracteres">
      <small id="hintPassword" style="color:var(--gris-600);"></small>

      <label>Área</label>
      <select id="f_especialidad">
        <option value="">— Selecciona un área —</option>
        <option value="Medicina">Medicina</option>
        <option value="Odontología">Odontología</option>
        <option value="Enfermería">Enfermería</option>
        <option value="Ciencias Biomédicas">Ciencias Biomédicas</option>
        <option value="Nutrición">Nutrición</option>
        <option value="Informática">Informática</option>
        <option value="Administración Pública">Administración Pública</option>
        <option value="Ciencias Empresariales">Ciencias Empresariales</option>
      </select>

      <label>Teléfono</label>
      <input type="text" id="f_telefono">

      <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" id="f_activo" style="width:auto;" checked> Cuenta activa
      </label>

      <div class="modal-actions">
        <button type="submit" class="btn" id="btnGuardar">Guardar</button>
        <button type="button" class="btn btn-secundario" onclick="closeModal('modal')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="/assets/js/common.js"></script>
<script src="/assets/js/profesores.js"></script>
</body>
</html>
