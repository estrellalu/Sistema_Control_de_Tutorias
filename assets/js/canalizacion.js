let canalizacionesCache = [];
let alumnosCache = [];

async function cargarDatos() {
  try {
    [canalizacionesCache, alumnosCache] = await Promise.all([
      apiCall('/api/canalizacion.php'),
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
  let lista = canalizacionesCache;
  if (filtro) lista = lista.filter(c => c.estado === filtro);
  lista = [...lista].sort((a, b) => b.fecha.localeCompare(a.fecha));

  const tbody = document.getElementById('tabla');
  if (lista.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No tienes canalizaciones registradas</td></tr>';
    return;
  }
  tbody.innerHTML = lista.map(c => `
    <tr>
      <td>${fmtFecha(c.fecha)}</td>
      <td>${escapeHtml(c.alumnos ? c.alumnos.nombre : '-')}</td>
      <td>${escapeHtml(c.area_canalizacion)}</td>
      <td>${escapeHtml((c.motivo || '').slice(0, 60))}${(c.motivo || '').length > 60 ? '…' : ''}</td>
      <td>${badge(c.estado)}</td>
      <td>
        <button class="btn btn-sm btn-secundario" onclick="abrirEditarCanalizacion('${c.id}')">Editar</button>
        <button class="btn btn-sm btn-peligro" onclick="eliminarCanalizacion('${c.id}')">Eliminar</button>
      </td>
    </tr>`).join('');
}

function limpiarForm() {
  document.getElementById('form').reset();
  document.getElementById('f_id').value = '';
  document.getElementById('f_fecha').value = new Date().toISOString().slice(0, 10);
  hideMsg('modalMsg');
}

function abrirNuevaCanalizacion() {
  limpiarForm();
  document.getElementById('modalTitulo').textContent = 'Nueva canalización';
  openModal('modal');
}

function abrirEditarCanalizacion(id) {
  limpiarForm();
  const c = canalizacionesCache.find(x => x.id === id);
  if (!c) return;
  document.getElementById('modalTitulo').textContent = 'Editar canalización';
  document.getElementById('f_id').value = c.id;
  document.getElementById('f_alumno_id').value = c.alumno_id;
  document.getElementById('f_area').value = c.area_canalizacion;
  document.getElementById('f_motivo').value = c.motivo;
  document.getElementById('f_fecha').value = c.fecha;
  document.getElementById('f_estado').value = c.estado;
  document.getElementById('f_seguimiento').value = c.seguimiento || '';
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
    area_canalizacion: document.getElementById('f_area').value,
    motivo: document.getElementById('f_motivo').value.trim(),
    fecha: document.getElementById('f_fecha').value,
    estado: document.getElementById('f_estado').value,
    seguimiento: document.getElementById('f_seguimiento').value.trim(),
  };

  try {
    if (id) {
      payload.id = id;
      await apiCall('/api/canalizacion.php', 'PUT', payload);
      showMsg('msg', 'Canalización actualizada correctamente', 'success');
    } else {
      await apiCall('/api/canalizacion.php', 'POST', payload);
      showMsg('msg', 'Canalización registrada correctamente', 'success');
    }
    closeModal('modal');
    cargarDatos();
  } catch (err) {
    showMsg('modalMsg', err.message, 'error');
  } finally {
    btn.disabled = false;
  }
});

async function eliminarCanalizacion(id) {
  if (!confirm('¿Eliminar esta canalización?')) return;
  try {
    await apiCall('/api/canalizacion.php?id=' + id, 'DELETE');
    showMsg('msg', 'Canalización eliminada', 'success');
    cargarDatos();
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

cargarDatos();
