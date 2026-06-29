let alertasCache = [];
let alumnosCache = [];

async function cargarDatos() {
  try {
    [alertasCache, alumnosCache] = await Promise.all([
      apiCall('/api/alertas.php'),
      apiCall('/api/alumnos.php'),
    ]);
    const opciones = alumnosCache.map(a => `<option value="${a.id}">${escapeHtml(a.nombre)} (${escapeHtml(a.matricula)})</option>`).join('');
    document.getElementById('f_alumno_id').innerHTML = opciones || '<option value="">No tienes alumnos asignados</option>';
    renderTabla();
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

function renderTabla() {
  const nivelFiltro = document.getElementById('filtroNivel').value;
  const atendidaFiltro = document.getElementById('filtroAtendida').value;

  let lista = alertasCache;
  if (nivelFiltro) lista = lista.filter(a => a.nivel_riesgo === nivelFiltro);
  if (atendidaFiltro !== '') {
    const val = atendidaFiltro === 'true';
    lista = lista.filter(a => a.atendida === val);
  }
  lista = [...lista].sort((a, b) => b.fecha.localeCompare(a.fecha));

  const tbody = document.getElementById('tabla');
  if (lista.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No hay alertas que coincidan</td></tr>';
    return;
  }
  tbody.innerHTML = lista.map(a => `
    <tr>
      <td>${fmtFecha(a.fecha)}</td>
      <td>${escapeHtml(a.alumnos ? a.alumnos.nombre : '-')}</td>
      <td>${escapeHtml(a.tipo_alerta)}</td>
      <td>${badge(a.nivel_riesgo)}</td>
      <td>${a.atendida ? '<span class="badge atendida">Sí</span>' : '<span class="badge pendiente">No</span>'}</td>
      <td>
        ${!a.atendida ? `<button class="btn btn-sm btn-secundario" onclick="marcarAtendida('${a.id}')">Marcar atendida</button>` : ''}
        <button class="btn btn-sm btn-peligro" onclick="eliminarAlerta('${a.id}')">Eliminar</button>
      </td>
    </tr>`).join('');
}

function limpiarForm() {
  document.getElementById('form').reset();
  document.getElementById('f_id').value = '';
  document.getElementById('f_fecha').value = new Date().toISOString().slice(0, 10);
  hideMsg('modalMsg');
}

function abrirNuevaAlerta() {
  limpiarForm();
  document.getElementById('modalTitulo').textContent = 'Nueva alerta';
  openModal('modal');
}

document.getElementById('form').addEventListener('submit', async (e) => {
  e.preventDefault();
  hideMsg('modalMsg');
  const btn = document.getElementById('btnGuardar');
  btn.disabled = true;

  const payload = {
    alumno_id: document.getElementById('f_alumno_id').value,
    tipo_alerta: document.getElementById('f_tipo').value,
    nivel_riesgo: document.getElementById('f_nivel').value,
    descripcion: document.getElementById('f_descripcion').value.trim(),
    fecha: document.getElementById('f_fecha').value,
    atendida: document.getElementById('f_atendida').checked,
  };

  try {
    await apiCall('/api/alertas.php', 'POST', payload);
    showMsg('msg', 'Alerta registrada correctamente. El nivel de riesgo del alumno se actualizó.', 'success');
    closeModal('modal');
    cargarDatos();
  } catch (err) {
    showMsg('modalMsg', err.message, 'error');
  } finally {
    btn.disabled = false;
  }
});

async function marcarAtendida(id) {
  try {
    await apiCall('/api/alertas.php', 'PUT', { id, atendida: true });
    showMsg('msg', 'Alerta marcada como atendida', 'success');
    cargarDatos();
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

async function eliminarAlerta(id) {
  if (!confirm('¿Eliminar esta alerta?')) return;
  try {
    await apiCall('/api/alertas.php?id=' + id, 'DELETE');
    showMsg('msg', 'Alerta eliminada', 'success');
    cargarDatos();
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

cargarDatos();
