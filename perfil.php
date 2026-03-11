<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['user_id'])) { 
    header("Location: index.html"); 
    exit; 
}

include 'db.php';
$stmt = $conn->prepare("SELECT username, rol, foto_perfil, nombre_completo, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario_datos = $result->fetch_assoc();

if (!$usuario_datos) {
    session_destroy();
    header("Location: index.html?error=session_invalid");
    exit;
}

// --- LÓGICA DE IMAGEN DE PERFIL ---
$foto_db = $usuario_datos['foto_perfil'];
if (!empty($foto_db) && file_exists($foto_db)) {
    $imagen_final = $foto_db;
} else {
    // Generar avatar con iniciales
    $imagen_final = "https://ui-avatars.com/api/?name=" . urlencode($usuario_datos['username']) . "&background=random&color=fff&bold=true&size=150";
}
// ----------------------------------
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
  <title>Perfil de Usuario - ControlVA</title>
  
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
  <style>
    .profile-pic { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
  </style>
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
      <li class="nav-item mb-1"><a href="panel.php" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
      <li class="nav-item mb-1"><a href="perfil.php" class="nav-link text-white active"><i class="bi bi-person-circle me-2"></i> Perfil</a></li>
      
      <?php if ($_SESSION['rol'] == 'Administrador'): ?>
      <li class="nav-item mb-1"><a href="admin_usuarios.php" class="nav-link text-white"><i class="bi bi-people-fill me-2"></i> Administrar Usuarios</a></li>
      <li class="nav-item mb-1 mt-2">
          <a href="prueba_conexion.php" class="nav-link text-warning border border-warning bg-transparent btn-sm text-start" title="Verificar estado del servidor">
              <i class="bi bi-database-check me-2"></i> Test Conexión
          </a>
      </li>
      <?php endif; ?>

    </ul>
    <hr>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="<?= htmlspecialchars($imagen_final) ?>" alt="perfil" width="32" height="32" class="rounded-circle me-2">
        <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
      </a>
      <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
        <li><button class="dropdown-item d-flex align-items-center theme-switch" id="theme-toggle"><i class="bi bi-moon-stars-fill me-2"></i><i class="bi bi-sun-fill me-2"></i> Cambiar Tema</button></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
      </ul>
    </div>
  </div>

  <div class="main-content p-4 w-100">
    <h1 class="h3 mb-4">Mi Perfil</h1>
    <div class="row">
      <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
          <div class="card-header">Información General</div>
          <div class="card-body text-center">
            <form id="formPerfil" enctype="multipart/form-data">
              
              <img id="profileImage" src="<?= htmlspecialchars($imagen_final) ?>" alt="Foto de Perfil" class="profile-pic mb-3">
              
              <div class="mb-3">
                  <label for="foto_perfil" class="form-label">Cambiar foto de perfil</label>
                  <input type="file" class="form-control form-control-sm" name="foto_perfil" id="foto_perfil" accept="image/*">
              </div>
              <div class="mb-3">
                <label for="usuario" class="form-label">Nombre de Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario" value="<?= htmlspecialchars($usuario_datos['username']) ?>" required>
              </div>
              <p class="text-muted">Rol: <?= htmlspecialchars(ucfirst($usuario_datos['rol'])) ?></p>
              <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="card shadow-sm">
          <div class="card-header">Cambiar Contraseña</div>
          <div class="card-body">
            <form id="formPassword">
              <div class="mb-3">
                <label for="password_actual" class="form-label">Contraseña Actual</label>
                <input type="password" class="form-control" name="password_actual" id="password_actual" required>
              </div>
              <div class="mb-3">
                <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                <input type="password" class="form-control" name="password_nueva" id="password_nueva" required>
              </div>
              <div class="mb-3">
                <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                <input type="password" class="form-control" name="password_confirmar" id="password_confirmar" required>
              </div>
              <button type="submit" class="btn btn-warning">Cambiar Contraseña</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const themeToggle = document.getElementById('theme-toggle');
if(themeToggle) {
    themeToggle.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-bs-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-bs-theme', newTheme);
      localStorage.setItem('theme', newTheme);
    });
}

document.getElementById('formPerfil').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    formData.append('accion', 'actualizarPerfil');
    fetch('api.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: data.message })
            .then(() => {
                document.querySelector('.dropdown strong').textContent = data.newUsername;
                // Actualizar la foto si se cambió
                if(data.newPhoto) {
                    const newPhotoUrl = data.newPhoto + '?t=' + new Date().getTime();
                    document.getElementById('profileImage').src = newPhotoUrl;
                    // También actualizamos la miniatura del menú
                    document.querySelector('.dropdown img').src = newPhotoUrl;
                }
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Oops...', text: data.message });
        }
    });
});

document.getElementById('formPassword').addEventListener('submit', function(e) {
    e.preventDefault();
    const nueva = document.getElementById('password_nueva').value;
    const confirmar = document.getElementById('password_confirmar').value;
    if (nueva !== confirmar) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Las nuevas contraseñas no coinciden.' });
        return;
    }
    if (nueva.length < 4) {
        Swal.fire({ icon: 'warning', title: 'Contraseña débil', text: 'La nueva contraseña debe tener al menos 4 caracteres.' });
        return;
    }
    let formData = new FormData(this);
    formData.append('accion', 'cambiarPassword');
    fetch('api.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: data.message });
            this.reset();
        } else {
            Swal.fire({ icon: 'error', title: 'Oops...', text: data.message });
        }
    });
});
</script>
</body>
</html>