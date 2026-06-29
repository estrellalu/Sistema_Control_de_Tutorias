<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_page('admin');
$active = 'alumnos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alumnos – Control de Tutorías UNSIS</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <!-- SheetJS para leer Excel/Calc -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="main">
    <div class="toolbar">
      <div>
        <h2 style="margin-bottom:2px;">Alumnos</h2>
        <p class="page-subtitle" style="margin:0;">Administra alumnos y asígnales un tutor</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <button class="btn btn-dorado" onclick="abrirImportarExcel()">Importar Excel / Calc</button>
        <button class="btn btn-secundario" onclick="descargarPlantilla()">Descargar plantilla</button>
      </div>
    </div>

    <div id="msg" class="alert-msg" style="display:none;"></div>

    <div class="card">
      <div class="toolbar filtros" style="margin-bottom:10px;">
        <select id="filtroTutor" onchange="renderTabla()">
          <option value="">Todos los tutores</option>
        </select>
        <select id="filtroRiesgo" onchange="renderTabla()">
          <option value="">Todos los niveles de riesgo</option>
          <option value="bajo">Riesgo bajo</option>
          <option value="medio">Riesgo medio</option>
          <option value="alto">Riesgo alto</option>
        </select>
      </div>
      <table>
        <thead>
          <tr><th>Nombre</th><th>Matrícula</th><th>Carrera/Grupo</th><th>Tutor</th><th>Riesgo</th><th>Acciones</th></tr>
        </thead>
        <tbody id="tabla"><tr><td colspan="6" class="empty-state">Cargando...</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL IMPORTAR EXCEL -->
<div class="modal-overlay" id="modalImport">
  <div class="modal-box" style="max-width:640px;">
    <h3>Importar alumnos desde Excel / Calc</h3>
    <p style="font-size:.85rem;color:var(--gris-600);margin:4px 0 14px;">
      Sube un archivo <strong>.xlsx, .xls, .ods o .csv</strong> con los datos de los alumnos.<br>
      Columnas requeridas: <code>nombre</code>, <code>matricula</code>.<br>
      Columnas opcionales: <code>carrera, grupo, semestre, email, telefono, nivel_riesgo</code>.
    </p>
    <div id="importZone" class="import-zone" onclick="document.getElementById('fileInput').click()" 
         ondragover="onDragOver(event)" ondragleave="onDragLeave(event)" ondrop="onDrop(event)">
      <p><strong>Haz clic aquí o arrastra tu archivo</strong></p>
      <p style="font-size:.78rem;">Excel (.xlsx, .xls), Calc (.ods), CSV (.csv)</p>
    </div>
    <input type="file" id="fileInput" accept=".xlsx,.xls,.ods,.csv" style="display:none" onchange="leerArchivo(this.files[0])">

    <div id="importMsg" class="alert-msg" style="display:none;"></div>

    <div id="importPreview" style="display:none;">
      <div class="asistencia-header">
        <strong id="previewTitle"></strong>
        <div>
          <label style="display:inline;font-weight:normal;margin:0;">Tutor asignado:</label>
          <select id="importTutor" style="width:auto;display:inline-block;margin-left:8px;">
            <option value="">Sin asignar</option>
          </select>
        </div>
      </div>
      <div class="import-preview">
        <table>
          <thead id="previewHead"></thead>
          <tbody id="previewBody"></tbody>
        </table>
      </div>
    </div>

    <div class="modal-actions" style="margin-top:16px;">
      <button class="btn" id="btnImportar" style="display:none;" onclick="importarAlumnos()">Importar todos</button>
      <button class="btn btn-secundario" onclick="closeModal('modalImport')">Cerrar</button>
    </div>
  </div>
</div>

<script src="/assets/js/common.js"></script>
<script src="/assets/js/alumnos_admin.js"></script>
</body>
</html>
