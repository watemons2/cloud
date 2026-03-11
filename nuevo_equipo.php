<?php
session_start();
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'Tecnico' && $_SESSION['rol'] !== 'Administrador')) {
    header("Location: panel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>
    (() => {
      'use strict'
      const storedTheme = localStorage.getItem('theme')
      if (storedTheme) { document.documentElement.setAttribute('data-bs-theme', storedTheme) }
    })()
  </script>
  <title>Registro de Nuevo Equipo - ControlVA</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="panel.php"><img src="logo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2"> ControlVA</a>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><img src="<?= htmlspecialchars($_SESSION['foto_perfil'] ?: 'https://via.placeholder.com/32') ?>" alt="perfil" width="32" height="32" class="rounded-circle me-2"><strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong></a>
      <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end text-small shadow">
        <li><button class="dropdown-item d-flex align-items-center theme-switch" id="theme-toggle"><i class="bi bi-moon-stars-fill me-2"></i><i class="bi bi-sun-fill me-2"></i> Cambiar Tema</button></li>
        <li><a class="dropdown-item" href="panel.php"><i class="bi bi-speedometer2 me-2"></i> Panel Principal</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5 mb-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-lg rounded-3">
        <div class="card-header"><h5 class="mb-0">Registrar Nuevo Equipo</h5></div>
        <div class="card-body">
          <form action="guardar_equipo.php" method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Equipo</label>
                <input type="text" class="form-control" name="nombre" required placeholder="Ej. Laptop Dell Latitude">
            </div>
            
            <div class="mb-3">
              <label for="serie" class="form-label">Número de Serie</label>
              <div class="input-group">
                <input type="text" class="form-control" name="serie" id="serie" required placeholder="Número de serie único">
                <button class="btn btn-outline-secondary" type="button" id="generarSerieBtn" title="Generar Número de Serie">
                  <i class="bi bi-magic"></i>
                </button>
              </div>
            </div>
            
            <div class="mb-3">
                <label for="codigo_barras" class="form-label">Código de Barras</label>
                <input type="text" class="form-control" name="codigo_barras" id="codigo_barras" placeholder="Escanear o generar">
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="categoria" class="form-label">Categoría</label>
                    <input type="text" class="form-control" name="categoria" placeholder="Ej. Computo, Impresión...">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="ubicacion" class="form-label">Ubicación</label>
                    <input type="text" class="form-control" name="ubicacion" placeholder="Ej. Oficina Central, Almacén...">
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-end">
              <a href="panel.php" class="btn btn-secondary me-2"><i class="bi bi-x-circle"></i> Cancelar</a>
              <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar Equipo</button>
            </div>
          </form>
        </div>
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

// Función para generar un número de serie aleatorio de 8 dígitos
function generarNumeroSerie() {
    return Math.floor(10000000 + Math.random() * 90000000).toString();
}

document.getElementById('generarSerieBtn').addEventListener('click', function() {
    const numeroSerie = generarNumeroSerie();
    document.getElementById('serie').value = numeroSerie;
    document.getElementById('codigo_barras').value = numeroSerie; 
});
</script>
</body>
</html>