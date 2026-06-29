let citasCache = [];
let alumnosCache = [];

async function cargarDatos() {
  try {
    [citasCache, alumnosCache] = await Promise.all([
      apiCall('/api/citas.php'),
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
  const filtro = document.getElementById('filtroEstado').value;
  let lista = citasCache;
  if (filtro) lista = lista.filter(c => c.estado === filtro);
  lista = [...lista].sort((a, b) => (a.fecha + a.hora).localeCompare(b.fecha + b.hora));

  const tbody = document.getElementById('tabla');
  if (lista.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" class="empty-state">No tienes citas registradas</td></tr>';
    return;
  }
  tbody.innerHTML = lista.map(c => `
    <tr>
      <td>${fmtFecha(c.fecha)}</td>
      <td>${c.hora}</td>
      <td>${escapeHtml(c.alumnos ? c.alumnos.nombre : '-')}</td>
      <td>${escapeHtml(c.motivo || '-')}</td>
      <td>${escapeHtml(c.lugar || '-')}</td>
      <td>${badge(c.estado)}</td>
      <td>
        <button class="btn btn-sm btn-secundario" onclick="abrirEditarCita('${c.id}')">Editar</button>
        <button class="btn btn-sm btn-peligro" onclick="eliminarCita('${c.id}')">Eliminar</button>
      </td>
    </tr>`).join('');
}

function limpiarForm() {
  document.getElementById('form').reset();
  document.getElementById('f_id').value = '';
  hideMsg('modalMsg');
}

function abrirNuevaCita() {
  limpiarForm();
  document.getElementById('modalTitulo').textContent = 'Nueva cita';
  openModal('modal');
}

function abrirEditarCita(id) {
  limpiarForm();
  const c = citasCache.find(x => x.id === id);
  if (!c) return;
  document.getElementById('modalTitulo').textContent = 'Editar cita';
  document.getElementById('f_id').value = c.id;
  document.getElementById('f_alumno_id').value = c.alumno_id;
  document.getElementById('f_fecha').value = c.fecha;
  document.getElementById('f_hora').value = c.hora;
  document.getElementById('f_motivo').value = c.motivo || '';
  document.getElementById('f_lugar').value = c.lugar || '';
  document.getElementById('f_estado').value = c.estado;
  openModal('modal');
}

document.getElementById('form').addEventListener('submit', async (e) => {
  e.preventDefault();
  hideMsg('modalMsg');
  const id = document.getElementById('f_id').value;
  const btn = document.getElementById('btnGuardar');
  btn.disabled = true;

  const payload = {
    alumno_id: document.getElementById('f_alumno_id').value,
    fecha: document.getElementById('f_fecha').value,
    hora: document.getElementById('f_hora').value,
    motivo: document.getElementById('f_motivo').value.trim(),
    lugar: document.getElementById('f_lugar').value.trim(),
    estado: document.getElementById('f_estado').value,
  };

  try {
    if (id) {
      payload.id = id;
      await apiCall('/api/citas.php', 'PUT', payload);
      showMsg('msg', 'Cita actualizada correctamente', 'success');
    } else {
      await apiCall('/api/citas.php', 'POST', payload);
      showMsg('msg', 'Cita agendada correctamente', 'success');
    }
    closeModal('modal');
    cargarDatos();
  } catch (err) {
    showMsg('modalMsg', err.message, 'error');
  } finally {
    btn.disabled = false;
  }
});

async function eliminarCita(id) {
  if (!confirm('¿Eliminar esta cita?')) return;
  try {
    await apiCall('/api/citas.php?id=' + id, 'DELETE');
    showMsg('msg', 'Cita eliminada', 'success');
    cargarDatos();
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

cargarDatos();
