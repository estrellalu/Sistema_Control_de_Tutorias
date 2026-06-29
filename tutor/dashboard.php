<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_page('profesor');
$active = 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi resumen – Control de Tutorías UNSIS</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="main">
    <h2>Hola, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
    <p class="page-subtitle">Este es el resumen de tu labor tutorial</p>

    <div class="cards-row">
      <div class="stat-card"><div class="num" id="stAlumnos">-</div><div class="lbl">Alumnos asignados</div></div>
      <div class="stat-card"><div class="num" id="stCitas">-</div><div class="lbl">Citas próximas</div></div>
      <div class="stat-card riesgo-alto"><div class="num" id="stRiesgoAlto">-</div><div class="lbl">Alumnos en riesgo alto</div></div>
      <div class="stat-card"><div class="num" id="stAlertasPend">-</div><div class="lbl">Alertas sin atender</div></div>
    </div>

    <div class="card">
      <h3 style="margin-top:0;">Próximas citas</h3>
      <table id="tablaCitas">
        <thead><tr><th>Fecha</th><th>Hora</th><th>Alumno</th><th>Motivo</th><th>Estado</th></tr></thead>
        <tbody><tr><td colspan="5" class="empty-state">Cargando...</td></tr></tbody>
      </table>
    </div>

    <div class="card">
      <h3 style="margin-top:0;">Mis alumnos en riesgo</h3>
      <table id="tablaRiesgo">
        <thead><tr><th>Alumno</th><th>Matrícula</th><th>Carrera/Grupo</th><th>Riesgo</th></tr></thead>
        <tbody><tr><td colspan="4" class="empty-state">Cargando...</td></tr></tbody>
      </table>
    </div>

    <!-- LISTA DE ASISTENCIA -->
    <div class="card">
      <div class="asistencia-header">
        <h3 style="margin:0;">Lista de asistencia</h3>
        <button class="btn btn-sm" onclick="guardarAsistencia()">Guardar asistencia</button>
      </div>
      <p style="font-size:.82rem;color:var(--gris-600);margin:8px 0 12px;">
        Marca la casilla de cada sesión en la que el alumno estuvo <strong>presente</strong>.
        La columna <strong>Total</strong> muestra cuántas asistencias lleva acumuladas.
      </p>
      <div style="overflow-x:auto;">
        <table class="asistencia-tabla">
          <thead>
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Matrícula</th>
              <th>Carrera / Grupo</th>
              <th style="text-align:center;">S1</th>
              <th style="text-align:center;">S2</th>
              <th style="text-align:center;">S3</th>
              <th style="text-align:center;">S4</th>
              <th style="text-align:center;">S5</th>
              <th style="text-align:center;">S6</th>
              <th style="text-align:center;">S7</th>
              <th style="text-align:center;">S8</th>
              <th style="text-align:center;">Total</th>
            </tr>
          </thead>
          <tbody id="tablaAsistencia">
            <tr><td colspan="13" class="empty-state">Cargando alumnos...</td></tr>
          </tbody>
        </table>
      </div>
      <div id="msgAsistencia" class="alert-msg" style="display:none;margin-top:12px;"></div>
    </div>
  </div>
</div>

<script src="/assets/js/common.js"></script>
<script>
const TOTAL_SESIONES = 8;
let alumnosGlobal = [];
let asistenciasGlobal = {}; // { alumno_id: { numero_sesion: presente_bool } }

async function cargar() {
  try {
    const [alumnos, citas, alertas, asistencias] = await Promise.all([
      apiCall('/api/alumnos.php'),
      apiCall('/api/citas.php'),
      apiCall('/api/alertas.php'),
      apiCall('/api/asistencias.php'),
    ]);

    alumnosGlobal = alumnos;

    asistenciasGlobal = {};
    asistencias.forEach(a => {
      if (!asistenciasGlobal[a.alumno_id]) asistenciasGlobal[a.alumno_id] = {};
      asistenciasGlobal[a.alumno_id][a.numero_sesion] = !!a.presente;
    });

    document.getElementById('stAlumnos').textContent = alumnos.length;
    document.getElementById('stRiesgoAlto').textContent = alumnos.filter(a => a.nivel_riesgo === 'alto').length;
    document.getElementById('stAlertasPend').textContent = alertas.filter(a => !a.atendida).length;

    const hoy = new Date().toISOString().slice(0, 10);
    const proximas = citas.filter(c => c.fecha >= hoy && c.estado !== 'cancelada').slice(0, 8);
    document.getElementById('stCitas').textContent = proximas.length;

    const tbodyCitas = document.querySelector('#tablaCitas tbody');
    tbodyCitas.innerHTML = proximas.length ? proximas.map(c => `
      <tr>
        <td>${fmtFecha(c.fecha)}</td>
        <td>${c.hora}</td>
        <td>${escapeHtml(c.alumnos ? c.alumnos.nombre : '')}</td>
        <td>${escapeHtml(c.motivo || '-')}</td>
        <td>${badge(c.estado)}</td>
      </tr>`).join('') : '<tr><td colspan="5" class="empty-state">No tienes citas próximas</td></tr>';

    const enRiesgo = alumnos.filter(a => a.nivel_riesgo === 'alto' || a.nivel_riesgo === 'medio');
    const tbodyRiesgo = document.querySelector('#tablaRiesgo tbody');
    tbodyRiesgo.innerHTML = enRiesgo.length ? enRiesgo.map(a => `
      <tr>
        <td>${escapeHtml(a.nombre)}</td>
        <td>${escapeHtml(a.matricula)}</td>
        <td>${escapeHtml(a.carrera || '-')} ${a.grupo ? '/ ' + escapeHtml(a.grupo) : ''}</td>
        <td>${badge(a.nivel_riesgo)}</td>
      </tr>`).join('') : '<tr><td colspan="4" class="empty-state">No tienes alumnos en riesgo</td></tr>';

    renderAsistencia();
  } catch (err) {
    alert('Error: ' + err.message);
  }
}

function contarSesiones(sesiones) {
  let total = 0;
  for (let s = 1; s <= TOTAL_SESIONES; s++) {
    if (sesiones[s]) total++;
  }
  return total;
}

function renderAsistencia() {
  const tbody = document.getElementById('tablaAsistencia');
  if (alumnosGlobal.length === 0) {
    tbody.innerHTML = '<tr><td colspan="13" class="empty-state">No tienes alumnos asignados.</td></tr>';
    return;
  }
  tbody.innerHTML = alumnosGlobal.map((a, i) => {
    const sesiones = asistenciasGlobal[a.id] || {};
    let celdas = '';
    for (let s = 1; s <= TOTAL_SESIONES; s++) {
      const marcado = sesiones[s] ? 'checked' : '';
      celdas += `<td style="text-align:center;">
        <input type="checkbox" class="check-asistencia" data-alumno="${a.id}" data-sesion="${s}" ${marcado} onchange="actualizarTotal('${a.id}')">
      </td>`;
    }
    return `
    <tr>
      <td style="color:var(--gris-600);">${i + 1}</td>
      <td>${escapeHtml(a.nombre)}</td>
      <td>${escapeHtml(a.matricula)}</td>
      <td>${escapeHtml(a.carrera || '-')} ${a.grupo ? '/ ' + escapeHtml(a.grupo) : ''}</td>
      ${celdas}
      <td style="text-align:center;font-weight:700;" id="total-${a.id}">${contarSesiones(sesiones)}</td>
    </tr>`;
  }).join('');
}

function actualizarTotal(alumnoId) {
  const checks = document.querySelectorAll('.check-asistencia[data-alumno="' + alumnoId + '"]');
  let total = 0;
  checks.forEach(c => { if (c.checked) total++; });
  const el = document.getElementById('total-' + alumnoId);
  if (el) el.textContent = total;
}

async function guardarAsistencia() {
  const checks = document.querySelectorAll('.check-asistencia');
  if (checks.length === 0) return;

  const registros = [];
  checks.forEach(c => {
    registros.push({
      alumno_id: c.dataset.alumno,
      numero_sesion: parseInt(c.dataset.sesion, 10),
      presente: c.checked,
    });
  });

  const el = document.getElementById('msgAsistencia');
  try {
    await apiCall('/api/asistencias.php', 'POST', { registros });
    el.textContent = 'Asistencia guardada correctamente.';
    el.className = 'alert-msg success';
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 4000);
  } catch (err) {
    el.textContent = 'Error al guardar: ' + err.message;
    el.className = 'alert-msg error';
    el.style.display = 'block';
  }
}

cargar();
</script>
</body>
</html>
