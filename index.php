<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . (current_role() === 'admin' ? '/admin/dashboard.php' : '/tutor/dashboard.php'));
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Control de Tutorías UNSIS – Acceso</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <h1>Control de Tutorías UNSIS</h1>
      <p class="login-inst">Universidad de la Sierra Sur • Oaxaca</p>
      <p class="subtitle">Inicia sesión con tu correo institucional</p>

      <div id="loginMsg" class="alert-msg error" style="display:none;"></div>

      <form id="loginForm">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" required autocomplete="username" placeholder="tu@correo.com">

        <label for="password">Contraseña</label>
        <input type="password" id="password" required autocomplete="current-password" placeholder="••••••••">

        <button type="submit" id="loginBtn" class="btn btn-block" style="margin-top:22px;">Iniciar sesión</button>
      </form>
    </div>
  </div>

  <script src="/assets/js/common.js"></script>
  <script src="/assets/js/login.js"></script>
</body>
</html>
