let alumnosCache = [];
let profesoresCache = [];
let importRows = []; // filas parseadas del archivo

async function cargarDatos() {
  try {
    [alumnosCache, profesoresCache] = await Promise.all([
      apiCall('/api/alumnos.php'),
      apiCall('/api/profesores.php'),
    ]);
    llenarSelectsTutor();
    renderTabla();
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

function llenarSelectsTutor() {
  const opciones = profesoresCache.map(p => `<option value="${p.id}">${escapeHtml(p.nombre)}</option>`).join('');

  document.getElementById('filtroTutor').innerHTML = '<option value="">Todos los tutores</option>' + opciones;

  // Select en modal importar
  const impTutor = document.getElementById('importTutor');
  if (impTutor) impTutor.innerHTML = '<option value="">Sin asignar</option>' + opciones;
}

function renderTabla() {
  const tutorFiltro = document.getElementById('filtroTutor').value;
  const riesgoFiltro = document.getElementById('filtroRiesgo').value;

  let lista = alumnosCache;
  if (tutorFiltro) lista = lista.filter(a => a.profesor_id === tutorFiltro);
  if (riesgoFiltro) lista = lista.filter(a => a.nivel_riesgo === riesgoFiltro);

  const tbody = document.getElementById('tabla');
  if (lista.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No hay alumnos que coincidan</td></tr>';
    return;
  }

  tbody.innerHTML = lista.map(a => {
    const tutor = profesoresCache.find(p => p.id === a.profesor_id);
    return `<tr>
      <td>${escapeHtml(a.nombre)}</td>
      <td>${escapeHtml(a.matricula)}</td>
      <td>${escapeHtml(a.carrera || '-')} ${a.grupo ? '/ ' + escapeHtml(a.grupo) : ''}</td>
      <td>${tutor ? escapeHtml(tutor.nombre) : '<em>Sin asignar</em>'}</td>
      <td>${badge(a.nivel_riesgo)}</td>
      <td>
        <button class="btn btn-sm btn-secundario" onclick="abrirEditarAlumno('${a.id}')">Editar</button>
        <button class="btn btn-sm btn-peligro" onclick="eliminarAlumno('${a.id}')">Eliminar</button>
      </td>
    </tr>`;
  }).join('');
}

// ─── EDICIÓN individual (sin agregar uno a uno) ─────────────────────────────

function abrirEditarAlumno(id) {
  const a = alumnosCache.find(x => x.id === id);
  if (!a) return;
  // Usa prompt simple para edición rápida (o abre un pequeño modal inline)
  const nuevoNombre = prompt('Editar nombre completo:', a.nombre);
  if (nuevoNombre === null) return;
  apiCall('/api/alumnos.php', 'PUT', { id: a.id, nombre: nuevoNombre.trim() })
    .then(() => { showMsg('msg', 'Alumno actualizado', 'success'); cargarDatos(); })
    .catch(err => showMsg('msg', err.message, 'error'));
}

async function eliminarAlumno(id) {
  if (!confirm('¿Eliminar a este alumno? Se borrará también su historial (citas, bitácora, etc).')) return;
  try {
    await apiCall('/api/alumnos.php?id=' + id, 'DELETE');
    showMsg('msg', 'Alumno eliminado', 'success');
    cargarDatos();
  } catch (err) {
    showMsg('msg', err.message, 'error');
  }
}

// ─── IMPORTAR EXCEL / CALC / CSV ────────────────────────────────────────────

function abrirImportarExcel() {
  importRows = [];
  document.getElementById('importPreview').style.display = 'none';
  document.getElementById('btnImportar').style.display = 'none';
  document.getElementById('fileInput').value = '';
  hideMsg('importMsg');
  openModal('modalImport');
}

function onDragOver(e) { e.preventDefault(); document.getElementById('importZone').classList.add('drag-over'); }
function onDragLeave(e) { document.getElementById('importZone').classList.remove('drag-over'); }
function onDrop(e) {
  e.preventDefault();
  document.getElementById('importZone').classList.remove('drag-over');
  const file = e.dataTransfer.files[0];
  if (file) leerArchivo(file);
}

function leerArchivo(file) {
  if (!file) return;
  hideMsg('importMsg');
  const reader = new FileReader();
  reader.onload = function(e) {
    try {
      const data = new Uint8Array(e.target.result);
      const wb = XLSX.read(data, { type: 'array' });
      const ws = wb.Sheets[wb.SheetNames[0]];
      const json = XLSX.utils.sheet_to_json(ws, { defval: '' });

      if (json.length === 0) {
        showMsg('importMsg', 'El archivo está vacío o no tiene datos válidos.', 'error');
        return;
      }

      // Normalizar claves a minúsculas sin tildes
      importRows = json.map(row => {
        const norm = {};
        for (const k in row) {
          const key = k.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').trim();
          norm[key] = String(row[k]).trim();
        }
        return norm;
      });

      // Verificar columna nombre y matricula
      if (!importRows[0].hasOwnProperty('nombre') || !importRows[0].hasOwnProperty('matricula')) {
        showMsg('importMsg', 'El archivo debe tener columnas "nombre" y "matricula".', 'error');
        importRows = [];
        return;
      }

      mostrarPreview(importRows);
    } catch(err) {
      showMsg('importMsg', 'Error al leer el archivo: ' + err.message, 'error');
    }
  };
  reader.readAsArrayBuffer(file);
}

function mostrarPreview(rows) {
  const cols = Object.keys(rows[0]);
  document.getElementById('previewTitle').textContent = `Vista previa: ${rows.length} alumno(s) encontrado(s)`;
  document.getElementById('previewHead').innerHTML =
    '<tr>' + cols.map(c => `<th>${escapeHtml(c)}</th>`).join('') + '</tr>';
  document.getElementById('previewBody').innerHTML = rows.slice(0, 10).map(r =>
    '<tr>' + cols.map(c => `<td>${escapeHtml(r[c])}</td>`).join('') + '</tr>'
  ).join('') + (rows.length > 10 ? `<tr><td colspan="${cols.length}" style="text-align:center;color:var(--gris-600);">... y ${rows.length - 10} más</td></tr>` : '');

  document.getElementById('importPreview').style.display = 'block';
  document.getElementById('btnImportar').style.display = 'inline-block';
}

async function importarAlumnos() {
  if (importRows.length === 0) return;
  const btn = document.getElementById('btnImportar');
  btn.disabled = true;
  btn.textContent = 'Importando...';
  const tutorId = document.getElementById('importTutor').value || null;

  let ok = 0, fail = 0;
  for (const row of importRows) {
    const payload = {
      nombre: row.nombre || '',
      matricula: row.matricula || '',
      carrera: row.carrera || '',
      grupo: row.grupo || '',
      semestre: row.semestre ? parseInt(row.semestre) || null : null,
      email: row.email || row.correo || '',
      telefono: row.telefono || '',
      profesor_id: tutorId,
      nivel_riesgo: ['bajo','medio','alto'].includes(row.nivel_riesgo) ? row.nivel_riesgo : 'bajo',
    };
    try {
      await apiCall('/api/alumnos.php', 'POST', payload);
      ok++;
    } catch(e) {
      fail++;
    }
  }

  btn.disabled = false;
  btn.textContent = 'Importar todos';
  const msgTxt = `Importación completada: ${ok} alumno(s) registrado(s)${fail > 0 ? `, ${fail} con error (matrícula duplicada u otro)` : ''}.`;
  showMsg('importMsg', msgTxt, ok > 0 ? 'success' : 'error');
  importRows = [];
  document.getElementById('importPreview').style.display = 'none';
  document.getElementById('btnImportar').style.display = 'none';
  cargarDatos();
}

// ─── PLANTILLA DE EJEMPLO ────────────────────────────────────────────────────
function descargarPlantilla() {
  const ws = XLSX.utils.aoa_to_sheet([
    ['nombre','matricula','carrera','grupo','semestre','email','telefono','nivel_riesgo'],
    ['Juan Pérez López','2024001','Medicina','A','3','juan@unsis.edu.mx','9511234567','bajo'],
    ['María García Ruiz','2024002','Enfermería','B','1','maria@unsis.edu.mx','9519876543','medio'],
  ]);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Alumnos');
  XLSX.writeFile(wb, 'plantilla_alumnos_unsis.xlsx');
}

cargarDatos();
