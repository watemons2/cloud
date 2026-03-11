<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.html"); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <script>
    (() => {
      'use strict'
      const storedTheme = localStorage.getItem('theme')
      if (storedTheme) { document.documentElement.setAttribute('data-bs-theme', storedTheme) }
    })()
  </script>
  <title>Configuración - ControlVA</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="d-flex">
  <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark sidebar">
    <a href="panel.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
      <img src="logo.png" alt="Logo" style="height: 32px;" class="me-2">
      <span class="fs-4">ControlVA</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li>
        <a href="#" class="nav-link text-white" data-bs-toggle="collapse" data-bs-target="#equipos-collapse" aria-expanded="false">
          <i class="bi bi-laptop me-2"></i> Equipos
        </a>
        <div class="collapse" id="equipos-collapse">
          <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ms-4">
            <li><a href="panel.php" class="nav-link text-white"><i class="bi bi-list-task me-2"></i>Ver Equipos</a></li>
            <li><a href="nuevo_equipo.php" class="nav-link text-white"><i class="bi bi-plus-circle me-2"></i>Añadir Equipo</a></li>
          </ul>
        </div>
      </li>
    </ul>
    <hr>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="<?= htmlspecialchars($_SESSION['foto_perfil'] ?: 'https://via.placeholder.com/32') ?>" alt="perfil" width="32" height="32" class="rounded-circle me-2">
        <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
      </a>
      <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
        <li><a class="dropdown-item" href="perfil.php"><i class="bi bi-person-fill-gear me-2"></i> Perfil</a></li>
        <li><a class="dropdown-item active" href="configuracion.php"><i class="bi bi-gear-fill me-2"></i> Configuración</a></li>
        <li><button class="dropdown-item d-flex align-items-center theme-switch" id="theme-toggle"><i class="bi bi-moon-stars-fill me-2"></i><i class="bi bi-sun-fill me-2"></i> Cambiar Tema</button></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
      </ul>
    </div>
  </div>

  <div class="main-content p-4">
    <div class="card shadow-sm rounded-3">
      <div class="card-header"><h5 class="mb-0">Configuración del Sistema</h5></div>
      <div class="card-body">
        <p>Esta sección está reservada para futuras opciones de configuración.</p>
        <p>Por ejemplo: gestión de usuarios, configuración de notificaciones, etc.</p>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const themeToggle = document.getElementById('theme-toggle');
const htmlEl = document.documentElement;
themeToggle.addEventListener('click', () => {
  const currentTheme = htmlEl.getAttribute('data-bs-theme');
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  htmlEl.setAttribute('data-bs-theme', newTheme);
  localStorage.setItem('theme', newTheme);
});
</script>
</body>
</html>