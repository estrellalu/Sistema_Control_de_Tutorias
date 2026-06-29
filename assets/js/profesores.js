let profesoresCache = [];

async function cargarProfesores() {
  try {
    profesoresCache = await apiCall('/api/profesores.php');
    const tbody = document.getElementById('tabla');
    if (profesoresCache.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="empty-state">Aún no hay profesores registrados</td></tr>';
      return;
    }
    tbody.innerHTML = profesoresCache.map(p => `
      <tr>
        <td>${escapeHtml(p.nombre)}</td>
        <td>${escapeHtml(p.email)}</td>
        <td>${escapeHtml(p.especialidad || '-')}</td>
        <td>${p.activo ? '<span class="badge atendida">Activo</span>' : '<span class="badge cancelada">Inactivo</span>'}</td>
        <td>
          <button class="btn btn-sm btn-secundario" onclick="abrirEditar('${p.id}')">Editar</button>
          <button class="btn btn-sm btn-peligro" onclick="eliminarProfesor('${p.id}')">Eliminar</button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

function limpiarForm() {
  document.getElementById('form').reset();
  document.getElementById('f_id').value = '';
  document.getElementById('f_activo').checked = true;
  hideMsg('modalMsg');
}

function abrirNuevo() {
  limpiarForm();
  document.getElementById('modalTitulo').textContent = 'Nuevo profesor';
  document.getElementById('f_password').required = true;
  document.getElementById('hintPassword').textContent = 'El profesor usará esta contraseña para iniciar sesión.';
  openModal('modal');
}

function abrirEditar(id) {
  limpiarForm();
  const p = profesoresCache.find(x => x.id === id);
  if (!p) return;
  document.getElementById('modalTitulo').textContent = 'Editar profesor';
  document.getElementById('f_id').value = p.id;
  document.getElementById('f_nombre').value = p.nombre;
  document.getElementById('f_email').value = p.email;
  document.getElementById('f_especialidad').value = p.especialidad || '';
  document.getElementById('f_telefono').value = p.telefono || '';
  document.getElementById('f_activo').checked = !!p.activo;
  document.getElementById('f_password').required = false;
  document.getElementById('hintPassword').textContent = 'Déjalo vacío para mantener la contraseña actual.';
  openModal('modal');
}

document.getElementById('form').addEventListener('submit', async (e) => {
  e.preventDefault();
  hideMsg('modalMsg');
  const id = document.getElementById('f_id').value;
  const btn = document.getElementById('btnGuardar');
  btn.disabled = true;

  const payload = {
    nombre: document.getElementById('f_nombre').value.trim(),
    email: document.getElementById('f_email').value.trim(),
    especialidad: document.getElementById('f_especialidad').value.trim(),
    telefono: document.getElementById('f_telefono').value.trim(),
    activo: document.getElementById('f_activo').checked,
  };
  const password = document.getElementById('f_password').value;
  if (password) payload.password = password;

  try {
    if (id) {
      payload.id = id;
      await apiCall('/api/profesores.php', 'PUT', payload);
      showMsg('msg', 'Profesor actualizado correctamente', 'success');
    } else {
      if (!password || password.length < 6) {
        throw new Error('La contraseña debe tener al menos 6 caracteres');
      }
      await apiCall('/api/profesores.php', 'POST', payload);
      showMsg('msg', 'Profesor creado correctamente', 'success');
    }
    closeModal('modal');
    cargarProfesores();
  } catch (err) {
    showMsg('modalMsg', err.message, 'error');
  } finally {
    btn.disabled = false;
  }
});

async function eliminarProfesor(id) {
  if (!confirm('¿Eliminar a este profesor? Sus alumnos quedarán sin tutor asignado.')) return;
  try {
    await apiCall('/api/profesores.php?id=' + id, 'DELETE');
    showMsg('msg', 'Profesor eliminado', 'success');
    cargarProfesores();
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

cargarProfesores();
