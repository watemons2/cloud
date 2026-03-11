<?php
session_start();
// Solo el Administrador puede acceder a esta página
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: panel.php");
    exit;
}
include "db.php";
$result = $conn->query("SELECT id, nombre_completo, username, email, rol FROM usuarios");
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
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
  <title>Administración de Usuarios - ControlVA</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      <li class="nav-item mb-1"><a href="panel.php" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
      <li class="nav-item mb-1"><a href="perfil.php" class="nav-link text-white"><i class="bi bi-person-circle me-2"></i> Perfil</a></li>
      <?php if ($_SESSION['rol'] == 'Administrador'): ?>
      <li class="nav-item mb-1"><a href="admin_usuarios.php" class="nav-link text-white active"><i class="bi bi-people-fill me-2"></i> Administrar Usuarios</a></li>
      <li class="nav-item mb-1 mt-2">
          <a href="test_db.php" class="nav-link text-warning border border-warning bg-transparent btn-sm text-start" title="Verificar estado del servidor">
              <i class="bi bi-database-check me-2"></i> Test Conexión
          </a>
      </li>
      <?php endif; ?>
    </ul>
    <hr>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="<?= htmlspecialchars($_SESSION['foto_perfil'] ?: 'https://via.placeholder.com/32') ?>" alt="perfil" width="32" height="32" class="rounded-circle me-2">
        <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
      </a>
      <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
        <li><button class="dropdown-item d-flex align-items-center theme-switch" id="theme-toggle"><i class="bi bi-moon-stars-fill me-2"></i><i class="bi bi-sun-fill me-2"></i> Cambiar Tema</button></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
      </ul>
    </div>
  </div>

  <div class="main-content p-4">
    <div class="card shadow-sm rounded-3">
        <div class="card-header"><h5 class="mb-0">Gestión de Usuarios del Sistema</h5></div>
        <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead><tr><th>ID</th><th>Nombre Completo</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Operaciones</th></tr></thead>
                <tbody>
                  <?php foreach($usuarios as $user): ?>
                  <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['rol']) ?></td>
                    <td>
                      <button class="btn btn-outline-primary btn-sm" onclick="abrirModalEditar('<?= $user['id'] ?>', '<?= htmlspecialchars($user['username']) ?>', '<?= $user['rol'] ?>')"><i class="bi bi-pencil"></i> Cambiar Rol</button>
                      <?php if ($user['id'] != $_SESSION['user_id']): ?>
                      <button class="btn btn-outline-danger btn-sm" onclick="eliminarUsuario(<?= $user['id'] ?>)"><i class="bi bi-trash"></i> Eliminar</button>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
        </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editarRolModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Cambiar Rol de Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
        <form id="formEditarRol"><p>Usuario: <strong id="usernameModal"></strong></p><input type="hidden" id="userIdModal" name="user_id"><div class="mb-3"><label for="rolModal" class="form-label">Nuevo Rol</label><select class="form-select" id="rolModal" name="rol"><option value="Usuario">Usuario</option><option value="Tecnico">Tecnico</option><option value="Administrador">Administrador</option></select></div></form>
      </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" onclick="guardarRol()">Guardar Cambios</button></div></div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let editarModal = new bootstrap.Modal(document.getElementById('editarRolModal'));

function abrirModalEditar(id, username, rol) {
    document.getElementById('userIdModal').value = id;
    document.getElementById('usernameModal').textContent = username;
    document.getElementById('rolModal').value = rol;
    editarModal.show();
}

function guardarRol() {
    let form = document.getElementById('formEditarRol');
    let datos = new FormData(form);
    datos.append('accion', 'actualizarRol');

    fetch('api.php', { method: 'POST', body: datos }).then(res => res.json())
    .then(data => {
        if(data.success) { Swal.fire('¡Éxito!', 'El rol ha sido actualizado.', 'success').then(() => location.reload()); } 
        else { Swal.fire('Error', data.message, 'error'); }
    });
}

function eliminarUsuario(id) {
    Swal.fire({ title: '¿Estás seguro?', text: "¡Esta acción no se puede revertir!", icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡elimínalo!', cancelButtonText: 'Cancelar' })
    .then((result) => {
        if (result.isConfirmed) {
            let datos = new FormData();
            datos.append('accion', 'eliminarUsuario');
            datos.append('id', id);

            fetch('api.php', { method: 'POST', body: datos }).then(res => res.json())
            .then(data => {
                if(data.success) { Swal.fire('¡Eliminado!', 'El usuario ha sido eliminado.', 'success').then(() => location.reload()); } 
                else { Swal.fire('Error', data.message, 'error'); }
            });
        }
    });
}

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