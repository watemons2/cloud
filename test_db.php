<?php
// --- CONFIGURACIÓN TÉCNICA ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Datos de sesión para el sidebar
$usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Invitado';
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'Visitante';
$foto = isset($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : 'https://via.placeholder.com/32';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <title>Prueba de Conexión - ControlVA</title>
  
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">

  <style>
      /* Estilos específicos para la conexión */
      .status-card { border-left: 5px solid #dee2e6; }
      .status-success { border-left-color: #198754; }
      .status-error { border-left-color: #dc3545; }
      
      /* Ajustes del sidebar fijo */
      .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 100;
        padding: 48px 0 0;
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
      }
      .sidebar-sticky {
        position: relative;
        top: 0;
        height: calc(100vh - 48px);
        padding-top: .5rem;
        overflow-x: hidden;
        overflow-y: auto;
      }
      /* Forzar fondo blanco/gris claro en el contenido principal siempre */
      .main-content-area {
          background-color: #f8f9fa !important; /* Gris muy claro de Bootstrap */
          color: #212529 !important; /* Texto oscuro */
          min-height: 100vh;
      }
      @media (max-width: 767.98px) {
        .sidebar { position: static; height: auto; padding-top: 0; } 
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
      <li class="nav-item mb-1"><a href="panel.php" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
      <li class="nav-item mb-1"><a href="perfil.php" class="nav-link text-white"><i class="bi bi-person-circle me-2"></i> Perfil</a></li>
      
      <?php if ($rol == 'Administrador'): ?>
      <li class="nav-item mb-1"><a href="admin_usuarios.php" class="nav-link text-white"><i class="bi bi-people-fill me-2"></i> Administrar Usuarios</a></li>
      <?php endif; ?>

      <?php if ($rol == 'Tecnico' || $rol == 'Administrador'): ?>
      <li class="nav-item mb-1"><a href="nuevo_equipo.php" class="nav-link text-white"><i class="bi bi-plus-circle me-2"></i> Nuevo Equipo</a></li>
      <?php endif; ?>
      
      <li class="nav-item mb-1">
          <a href="test_db.php" class="nav-link text-warning border border-warning bg-transparent btn-sm text-start" aria-current="page"><i class="bi bi-database-check me-2"></i> Test Conexión</a>
      </li>
    </ul>
    <hr>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="<?= htmlspecialchars($foto) ?>" alt="perfil" width="32" height="32" class="rounded-circle me-2">
        <strong><?= htmlspecialchars($usuario) ?></strong>
      </a>
      <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
      </ul>
    </div>
  </div>

  <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content-area">
    
    <nav class="navbar navbar-dark bg-dark d-md-none mb-3">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
          <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="panel.php">ControlVA</a>
      </div>
    </nav>
    
    <div class="pt-4">
      
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom border-secondary">
        <h1 class="h2 text-dark">Diagnóstico del Sistema</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="panel.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left-circle me-2"></i> Regresar al Panel</a>
        </div>
      </div>

      <div class="row justify-content-center">
          <div class="col-lg-8">
              <div class="card shadow-sm mb-4 bg-white text-dark">
                  <div class="card-header bg-white border-bottom">
                      <h5 class="mb-0 text-dark">Estado de la Base de Datos</h5>
                  </div>
                  <div class="card-body p-4">
                      
                      <p class="text-muted mb-4">Verificando comunicación con el archivo <code>db.php</code>...</p>

                      <?php
                        try {
                            include 'db.php'; 

                            if (isset($conn) && $conn instanceof mysqli && !$conn->connect_errno) {
                                // ÉXITO
                                ?>
                                <div class="alert alert-success d-flex align-items-center shadow-sm status-card status-success" role="alert">
                                    <i class="bi bi-check-circle-fill fs-1 me-3 text-success"></i>
                                    <div>
                                        <h4 class="alert-heading fw-bold mb-1">¡Conexión Exitosa!</h4>
                                        <p class="mb-0">El sistema ControlVA está conectado correctamente a la base de datos.</p>
                                    </div>
                                </div>

                                <div class="card mt-3 border bg-light">
                                    <ul class="list-group list-group-flush bg-light">
                                        <li class="list-group-item d-flex justify-content-between bg-white text-dark">
                                            <span><i class="bi bi-hdd-network me-2 text-primary"></i> Host Info</span>
                                            <span class="font-monospace text-muted"><?php echo $conn->host_info; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between bg-white text-dark">
                                            <span><i class="bi bi-server me-2 text-primary"></i> Versión MySQL</span>
                                            <span class="badge bg-secondary"><?php echo $conn->server_info; ?></span>
                                        </li>
                                    </ul>
                                </div>
                                <?php
                                $conn->close();
                            } else {
                                throw new Exception("La variable de conexión no se estableció.");
                            }

                        } catch (Exception $e) {
                            // ERROR
                            ?>
                            <div class="alert alert-danger d-flex align-items-center shadow-sm status-card status-error" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-1 me-3 text-danger"></i>
                                <div>
                                    <h4 class="alert-heading fw-bold mb-1">¡Error de Conexión!</h4>
                                    <p class="mb-0">No se pudo conectar a la base de datos.</p>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3">
                                <h6><i class="bi bi-bug-fill me-1"></i> Detalle Técnico:</h6>
                                <hr>
                                <code class="text-dark">
                                    <?php 
                                    if (isset($conn) && $conn instanceof mysqli && $conn->connect_error) {
                                        echo "MySQL Error: " . $conn->connect_error;
                                    } else {
                                        echo $e->getMessage();
                                    }
                                    ?>
                                </code>
                            </div>
                            <?php
                        }
                        ?>

                  </div>
              </div>
          </div>
      </div>

    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>