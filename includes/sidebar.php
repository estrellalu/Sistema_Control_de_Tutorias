<?php
/**
 * includes/sidebar.php
 */
$role = current_role();
$active = $active ?? '';
?>
<div class="sidebar">
  <div class="brand">
    <div class="brand-text">
      <div class="brand-title">Control de Tutorías UNSIS</div>
      <div class="brand-sub"><?= $role === 'admin' ? 'Panel de administración' : 'Panel del tutor' ?></div>
    </div>
  </div>
  <nav>
    <?php if ($role === 'admin'): ?>
      <a href="/admin/dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Resumen general</a>
      <a href="/admin/profesores.php" class="<?= $active === 'profesores' ? 'active' : '' ?>">Profesores / Tutores</a>
      <a href="/admin/alumnos.php" class="<?= $active === 'alumnos' ? 'active' : '' ?>">Alumnos</a>
    <?php else: ?>
      <a href="/tutor/dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Resumen</a>
      <a href="/tutor/agenda.php" class="<?= $active === 'agenda' ? 'active' : '' ?>">Agenda de citas</a>
      <a href="/tutor/bitacora.php" class="<?= $active === 'bitacora' ? 'active' : '' ?>">Bitácora de sesiones</a>
      <a href="/tutor/canalizacion.php" class="<?= $active === 'canalizacion' ? 'active' : '' ?>">Canalizaciones</a>
      <a href="/tutor/alertas.php" class="<?= $active === 'alertas' ? 'active' : '' ?>">Alertas de riesgo</a>
    <?php endif; ?>
  </nav>
  <div class="logout-box">
    <div class="who"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></div>
    <button class="btn btn-secundario btn-block" onclick="cerrarSesion()">Cerrar sesión</button>
  </div>
</div>
<script>
async function cerrarSesion() {
  await fetch('/api/logout.php', { method: 'POST' });
  window.location.href = '/index.php';
}
</script>
