document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  hideMsg('loginMsg');

  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const btn = document.getElementById('loginBtn');

  btn.disabled = true;
  btn.textContent = 'Ingresando...';

  try {
    const data = await apiCall('/api/login.php', 'POST', { email, password });
    window.location.href = data.redirect;
  } catch (err) {
    showMsg('loginMsg', err.message, 'error');
    btn.disabled = false;
    btn.textContent = 'Iniciar sesión';
  }
});
