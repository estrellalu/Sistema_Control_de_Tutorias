let bitacoraCache = [];
let alumnosCache = [];

async function cargarDatos() {
  try {
    [bitacoraCache, alumnosCache] = await Promise.all([
      apiCall('/api/bitacora.php'),
      apiCall('/api/alumnos.php'),
    ]);
    const opciones = alumnosCache.map(a => `<option value="${a.id}">${escapeHtml(a.nombre)} (${escapeHtml(a.matricula)})</option>`).join('');
    document.getElementById('f_alumno_id').innerHTML = opciones || '<option value="">No tienes alumnos asignados</option>';
    renderLista();
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

function renderLista() {
  const cont = document.getElementById('lista');
  if (bitacoraCache.length === 0) {
    cont.innerHTML = '<p class="empty-state">Aún no tienes registros en la bitácora</p>';
    return;
  }
  const ordenado = [...bitacoraCache].sort((a, b) => b.fecha_sesion.localeCompare(a.fecha_sesion));
  cont.innerHTML = ordenado.map(b => `
    <div class="card" style="border-left:4px solid var(--azul); margin-bottom:14px;">
      <div style="display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
          <strong>${escapeHtml(b.tema)}</strong><br>
          <small style="color:var(--gris-600);">
            ${escapeHtml(b.alumnos ? b.alumnos.nombre : '')} &middot; ${fmtFecha(b.fecha_sesion)}
          </small>
        </div>
        <div>
          <button class="btn btn-sm btn-secundario" onclick="abrirEditarBitacora('${b.id}')">Editar</button>
          <button class="btn btn-sm btn-peligro" onclick="eliminarBitacora('${b.id}')">Eliminar</button>
        </div>
      </div>
      ${b.observaciones ? `<p style="margin:10px 0 4px;"><strong>Observaciones:</strong> ${escapeHtml(b.observaciones)}</p>` : ''}
      ${b.acuerdos ? `<p style="margin:4px 0 0;"><strong>Acuerdos:</strong> ${escapeHtml(b.acuerdos)}</p>` : ''}
    </div>
  `).join('');
}

function limpiarForm() {
  document.getElementById('form').reset();
  document.getElementById('f_id').value = '';
  hideMsg('modalMsg');
}

function abrirNuevaBitacora() {
  limpiarForm();
  document.getElementById('modalTitulo').textContent = 'Nuevo registro de bitácora';
  openModal('modal');
}

function abrirEditarBitacora(id) {
  limpiarForm();
  const b = bitacoraCache.find(x => x.id === id);
  if (!b) return;
  document.getElementById('modalTitulo').textContent = 'Editar registro';
  document.getElementById('f_id').value = b.id;
  document.getElementById('f_alumno_id').value = b.alumno_id;
  document.getElementById('f_fecha_sesion').value = b.fecha_sesion;
  document.getElementById('f_tema').value = b.tema;
  document.getElementById('f_observaciones').value = b.observaciones || '';
  document.getElementById('f_acuerdos').value = b.acuerdos || '';
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
    fecha_sesion: document.getElementById('f_fecha_sesion').value,
    tema: document.getElementById('f_tema').value.trim(),
    observaciones: document.getElementById('f_observaciones').value.trim(),
    acuerdos: document.getElementById('f_acuerdos').value.trim(),
  };

  try {
    if (id) {
      payload.id = id;
      await apiCall('/api/bitacora.php', 'PUT', payload);
      showMsg('msg', 'Registro actualizado correctamente', 'success');
    } else {
      await apiCall('/api/bitacora.php', 'POST', payload);
      showMsg('msg', 'Registro guardado correctamente', 'success');
    }
    closeModal('modal');
    cargarDatos();
  } catch (err) {
    showMsg('modalMsg', err.message, 'error');
  } finally {
    btn.disabled = false;
  }
});

async function eliminarBitacora(id) {
  if (!confirm('¿Eliminar este registro de bitácora?')) return;
  try {
    await apiCall('/api/bitacora.php?id=' + id, 'DELETE');
    showMsg('msg', 'Registro eliminado', 'success');
    cargarDatos();
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

cargarDatos();
