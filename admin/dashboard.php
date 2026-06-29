<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_page('admin');
$active = 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resumen general – Control de Tutorías UNSIS</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="main">
    <h2>Resumen general</h2>
    <p class="page-subtitle">Vista general del sistema de control de tutorías</p>

    <div class="cards-row" id="statsRow">
      <div class="stat-card"><div class="num" id="stTutores">-</div><div class="lbl">Tutores activos</div></div>
      <div class="stat-card"><div class="num" id="stAlumnos">-</div><div class="lbl">Alumnos registrados</div></div>
      <div class="stat-card riesgo-alto"><div class="num" id="stRiesgoAlto">-</div><div class="lbl">Alumnos en riesgo alto</div></div>
      <div class="stat-card riesgo-medio"><div class="num" id="stRiesgoMedio">-</div><div class="lbl">Alumnos en riesgo medio</div></div>
    </div>

    <div class="card">
      <h3 style="margin-top:0;">Accesos rápidos</h3>
      <p>
        <a href="/admin/profesores.php" class="btn">+ Registrar nuevo profesor/tutor</a>
        &nbsp;
        <a href="/admin/alumnos.php" class="btn btn-secundario">+ Registrar nuevo alumno</a>
      </p>
    </div>

    <div class="card">
      <h3 style="margin-top:0;">Alumnos en riesgo alto de deserción</h3>
      <table id="tablaRiesgo">
        <thead><tr><th>Alumno</th><th>Matrícula</th><th>Carrera</th><th>Tutor asignado</th></tr></thead>
        <tbody><tr><td colspan="4" class="empty-state">Cargando...</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<script src="/assets/js/common.js"></script>
<script>
async function cargarResumen() {
  try {
    const [profesores, alumnos] = await Promise.all([
      apiCall('/api/profesores.php'),
      apiCall('/api/alumnos.php'),
    ]);

    document.getElementById('stTutores').textContent = profesores.filter(p => p.activo).length;
    document.getElementById('stAlumnos').textContent = alumnos.length;
    document.getElementById('stRiesgoAlto').textContent = alumnos.filter(a => a.nivel_riesgo === 'alto').length;
    document.getElementById('stRiesgoMedio').textContent = alumnos.filter(a => a.nivel_riesgo === 'medio').length;

    const enRiesgo = alumnos.filter(a => a.nivel_riesgo === 'alto');
    const tbody = document.querySelector('#tablaRiesgo tbody');
    if (enRiesgo.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" class="empty-state">No hay alumnos en riesgo alto registrados</td></tr>';
      return;
    }
    tbody.innerHTML = enRiesgo.map(a => {
      const tutor = profesores.find(p => p.id === a.profesor_id);
      return `<tr>
        <td>${escapeHtml(a.nombre)}</td>
        <td>${escapeHtml(a.matricula)}</td>
        <td>${escapeHtml(a.carrera || '-')}</td>
        <td>${tutor ? escapeHtml(tutor.nombre) : '<em>Sin asignar</em>'}</td>
      </tr>`;
    }).join('');
  } catch (err) {
    document.querySelector('#tablaRiesgo tbody').innerHTML =
      `<tr><td colspan="4" class="empty-state">Error: ${escapeHtml(err.message)}</td></tr>`;
  }
}
cargarResumen();
</script>
</body>
</html>
