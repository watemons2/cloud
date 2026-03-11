<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.html"); exit; }
include "db.php";

// --- LÓGICA PHP (Solo carga inicial y filtro de ubicación) ---
// La búsqueda de texto la dejaremos 100% al JavaScript para que sea instantánea

$sql_ubicaciones = "SELECT DISTINCT ubicacion FROM registros WHERE ubicacion IS NOT NULL AND ubicacion != '' ORDER BY ubicacion ASC";
$res_ubicaciones = $conn->query($sql_ubicaciones);

$filtro_ubicacion = isset($_GET['ubicacion']) ? $_GET['ubicacion'] : '';

$sql = "SELECT * FROM registros WHERE 1=1";

if (!empty($filtro_ubicacion)) {
    $ubicacion_safe = $conn->real_escape_string($filtro_ubicacion);
    $sql .= " AND ubicacion = '$ubicacion_safe'";
}

$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);
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
  <title>Panel de Control - ControlVA</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
  
  <style>
      .fila-clickeable { cursor: pointer; }
      
      /* Animación suave para cuando las filas se filtran */
      .mostrar-fila {
          animation: fadeIn 0.3s ease-in;
      }
      @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
      }
  </style>
</head>
<body>

<div class="d-flex">
  
  <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark sidebar offcanvas-md offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header d-md-none">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">Menú</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>
    <a href="panel.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
      <img src="logo.png" alt="Logo" style="height: 32px;" class="me-2">
      <span class="fs-4">ControlVA</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item mb-1"><a href="panel.php" class="nav-link text-white active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
      <li class="nav-item mb-1"><a href="perfil.php" class="nav-link text-white"><i class="bi bi-person-circle me-2"></i> Perfil</a></li>
      <?php if ($_SESSION['rol'] == 'Administrador'): ?>
      <li class="nav-item mb-1"><a href="admin_usuarios.php" class="nav-link text-white"><i class="bi bi-people-fill me-2"></i> Administrar Usuarios</a></li>
      <li class="nav-item mb-1 mt-2">
          <a href="test_db.php" class="nav-link text-warning border border-warning bg-transparent btn-sm text-start" title="Verificar estado del servidor">
              <i class="bi bi-database-check me-2"></i> Test Conexión
          </a>
      </li>
      <?php endif; ?>
    </ul>
    <hr>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="<?= htmlspecialchars($_SESSION['foto_perfil'] ?: 'https://via.placeholder.com/32') ?>" alt="perfil" width="32" height="32" class="rounded-circle me-2">
        <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
      </a>
      <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
        <li><button class="dropdown-item d-flex align-items-center theme-switch" id="theme-toggle"><i class="bi bi-moon-stars-fill me-2"></i><i class="bi bi-sun-fill me-2"></i> Cambiar Tema</button></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
      </ul>
    </div>
  </div>

  <div class="main-content w-100">
    <nav class="navbar navbar-dark bg-dark d-md-none">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
          <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="panel.php">ControlVA</a>
      </div>
    </nav>
    
    <div class="p-4">
      <div class="card shadow-sm rounded-3">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Equipos Registrados</h5>
            <?php if ($_SESSION['rol'] == 'Tecnico' || $_SESSION['rol'] == 'Administrador'): ?>
            <a href="nuevo_equipo.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Añadir Equipo</a>
            <?php endif; ?>
          </div>
          <div class="card-body">
              
              <div class="d-flex flex-wrap mb-3 gap-2 align-items-center">
                
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" id="buscadorVivo" class="form-control" placeholder="Escribe para buscar..." autocomplete="off">
                </div>
                
                <form action="panel.php" method="GET" class="d-flex">
                    <select class="form-select" name="ubicacion" onchange="this.form.submit()" style="width: 220px;">
                        <option value="">📍 Todas las Ubicaciones</option>
                        <?php 
                        if ($res_ubicaciones) {
                            $res_ubicaciones->data_seek(0);
                            while ($row = $res_ubicaciones->fetch_assoc()): 
                        ?>
                            <option value="<?= htmlspecialchars($row['ubicacion']) ?>" <?= ($filtro_ubicacion == $row['ubicacion']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['ubicacion']) ?>
                            </option>
                        <?php endwhile; } ?>
                    </select>
                </form>

                <?php if(!empty($filtro_ubicacion)): ?>
                    <a href="panel.php" class="btn btn-outline-secondary" title="Ver todo"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>

                <div class="input-group ms-auto" style="width: 250px;">
                  <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                  <input type="text" id="escaner_input" class="form-control" placeholder="Escanear código...">
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-warning">
                      <tr>
                          <th>Nombre</th>
                          <th>No. Serie</th>
                          <th>Código Barras</th>
                          <th>Ubicación</th>
                          <th>Estatus</th>
                          <th>Operaciones</th>
                      </tr>
                  </thead>
                  
                  <tbody id="tablaCuerpo">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($eq = $result->fetch_assoc()): ?>
                        
                        <tr class="fila-clickeable fila-item mostrar-fila" onclick="window.location='equipo.php?id=<?= $eq['id'] ?>'">
                          
                          <td class="dato-nombre">
                              <div class="fw-bold"><?= htmlspecialchars($eq['nombre']) ?></div>
                              <small class="text-muted"><?= htmlspecialchars($eq['categoria']) ?></small>
                          </td>
                          <td class="dato-serie"><?= htmlspecialchars($eq['serie']) ?></td>
                          <td class="dato-codigo"><?= htmlspecialchars($eq['codigo_barras'] ?: 'N/A') ?></td>
                          
                          <td>
                              <?php if (!empty($eq['ubicacion'])): ?>
                                <span class="badge bg-light text-dark border"><i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($eq['ubicacion']) ?></span>
                              <?php else: ?>
                                <span class="text-muted small">Sin asignar</span>
                              <?php endif; ?>
                          </td>

                          <td>
                            <?php if($eq['estatus']=="con falla"): ?>
                                <span class="badge bg-danger">Con Falla</span>
                            <?php else: ?>
                                <span class="badge bg-success">Sin Falla</span>
                            <?php endif; ?>
                          </td>
                          
                          <td>
                            <a href="equipo.php?id=<?= $eq['id'] ?>" onclick="event.stopPropagation();" class="btn btn-outline-secondary btn-sm" title="Ver Detalles"><i class="bi bi-eye"></i></a>
                            <?php if($_SESSION['rol'] == "Tecnico" || $_SESSION['rol'] == "Administrador"): ?>
                            <a href="editar_equipo.php?id=<?= $eq['id'] ?>" onclick="event.stopPropagation();" class="btn btn-outline-primary btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                            <button onclick="borrarEquipo(<?= $eq['id'] ?>, event)" class="btn btn-outline-danger btn-sm" title="Borrar"><i class="bi bi-trash"></i></button>
                            <?php endif; ?>
                          </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center p-4 text-muted">No se encontraron equipos.</td></tr>
                    <?php endif; ?>
                    
                    <tr id="sinResultados" class="d-none">
                        <td colspan="6" class="text-center p-5 text-muted">
                            <i class="bi bi-search fs-1 d-block mb-3 opacity-25"></i>
                            <h5>Sin coincidencias</h5>
                            <p class="small">Intenta buscar otro término.</p>
                        </td>
                    </tr>

                  </tbody>
                </table>
              </div>
          </div>
          <div class="card-footer text-end"><small class="text-muted"><i class="bi bi-geo-alt-fill"></i> Sucursal Comercial VA</small></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// TEMA
const themeToggle = document.getElementById('theme-toggle');
const htmlEl = document.documentElement;
themeToggle.addEventListener('click', () => {
  const currentTheme = htmlEl.getAttribute('data-bs-theme');
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  htmlEl.setAttribute('data-bs-theme', newTheme);
  localStorage.setItem('theme', newTheme);
});

// --- BÚSQUEDA EN VIVO CORREGIDA ---
document.getElementById('buscadorVivo').addEventListener('keyup', function() {
    let texto = this.value.toLowerCase(); // Convertir lo escrito a minúsculas
    let filas = document.querySelectorAll('.fila-item'); // Seleccionar todas las filas con datos
    let hayResultados = false;

    filas.forEach(function(fila) {
        // Obtener el texto de las 3 columnas importantes
        let nombre = fila.querySelector('.dato-nombre').textContent.toLowerCase();
        let serie = fila.querySelector('.dato-serie').textContent.toLowerCase();
        let codigo = fila.querySelector('.dato-codigo').textContent.toLowerCase();

        // Si alguna coincide, mostramos la fila. Si no, agregamos la clase d-none (ocultar de Bootstrap)
        if (nombre.includes(texto) || serie.includes(texto) || codigo.includes(texto)) {
            fila.classList.remove('d-none');
            hayResultados = true;
        } else {
            fila.classList.add('d-none');
        }
    });

    // Controlar el mensaje de "Sin resultados"
    let mensaje = document.getElementById('sinResultados');
    if (hayResultados) {
        mensaje.classList.add('d-none');
    } else {
        mensaje.classList.remove('d-none');
    }
});

// ESCANER
const escanerInput = document.getElementById('escaner_input');
escanerInput.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault(); 
        let codigo = this.value.trim();
        if (codigo) { buscarPorCodigo(codigo); }
    }
});

function buscarPorCodigo(codigo) {
    let datos = new FormData();
    datos.append("accion", "buscarPorCodigo");
    datos.append("codigo_barras", codigo);
    fetch("api.php", { method: "POST", body: datos })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'equipo.php?id=' + data.id;
        } else {
            Swal.fire({ icon: 'error', title: 'No Encontrado', text: 'Código no registrado.' });
            escanerInput.value = '';
        }
    })
    .catch(() => { Swal.fire('Error', 'Error de conexión.', 'error'); });
}

function borrarEquipo(id, event) {
    event.stopPropagation();
    Swal.fire({ title: '¿Estás seguro?', text: "Se borrará todo el historial.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#d33', confirmButtonText: 'Borrar', cancelButtonText: 'Cancelar' })
    .then((result) => {
        if (result.isConfirmed) {
            let datos = new FormData();
            datos.append("accion", "borrarEquipo");
            datos.append("id", id);
            fetch("api.php", { method: "POST", body: datos }).then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Borrado!', 'Equipo eliminado.', 'success')
                    .then(() => { location.reload(); });
                } else {
                    Swal.fire('Error', data.message || 'Error al borrar.', 'error');
                }
            });
        }
    });
}
</script>
</body>
</html>