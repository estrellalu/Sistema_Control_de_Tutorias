/**
 * assets/js/common.js
 * Funciones reutilizables en todo el sistema.
 */

/** Helper para llamar a los endpoints PHP (api/*.php) */
async function apiCall(url, method = 'GET', body = null) {
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json' },
  };
  if (body !== null) opts.body = JSON.stringify(body);

  const res = await fetch(url, opts);
  let data = {};
  try { data = await res.json(); } catch (e) { /* respuesta vacía */ }

  if (!res.ok || data.error) {
    throw new Error(data.message || `Error ${res.status}`);
  }
  return data;
}

function showMsg(containerId, text, type = 'error') {
  const el = document.getElementById(containerId);
  if (!el) return;
  el.textContent = text;
  el.className = `alert-msg ${type}`;
  el.style.display = 'block';
  if (type === 'success') {
    setTimeout(() => { el.style.display = 'none'; }, 3000);
  }
}

function hideMsg(containerId) {
  const el = document.getElementById(containerId);
  if (el) el.style.display = 'none';
}

function openModal(id) {
  document.getElementById(id).classList.add('open');
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

function fmtFecha(iso) {
  if (!iso) return '-';
  const [y, m, d] = iso.split('-');
  return `${d}/${m}/${y}`;
}

function badge(valor) {
  if (!valor) return '';
  const texto = valor.replace('_', ' ');
  return `<span class="badge ${valor}">${texto}</span>`;
}

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}
