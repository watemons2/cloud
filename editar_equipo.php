<?php
session_start();
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'Tecnico' && $_SESSION['rol'] !== 'Administrador')) {
    header("Location: index.html");
    exit;
}
include "db.php";

// 1. Obtener datos del equipo
if (!isset($_GET['id'])) { header("Location: panel.php"); exit; }
$id = intval($_GET['id']);
$sql = "SELECT * FROM registros WHERE id = $id";
$res = $conn->query($sql);
$equipo = $res->fetch_assoc();

if (!$equipo) { header("Location: panel.php"); exit; }

// 2. Listas para Datalist
$sql_ubicaciones = "SELECT DISTINCT ubicacion FROM registros WHERE ubicacion != '' ORDER BY ubicacion ASC";
$res_ubicaciones = $conn->query($sql_ubicaciones);

$sql_categorias = "SELECT DISTINCT categoria FROM registros WHERE categoria != '' ORDER BY categoria ASC";
$res_categorias = $conn->query($sql_categorias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Equipo - ControlVA</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="d-flex">
  <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark sidebar offcanvas-md offcanvas-start" style="min-height: 100vh;">
    <a href="panel.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
      <img src="logo.png" alt="Logo" style="height: 32px;" class="me-2">
      <span class="fs-4">ControlVA</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item mb-1"><a href="panel.php" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
      <li class="nav-item mb-1"><a href="perfil.php" class="nav-link text-white"><i class="bi bi-person-circle me-2"></i> Perfil</a></li>
    </ul>
  </div>

  <div class="main-content w-100 p-4 bg-light">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil-square me-2"></i> Editar Equipo</h2>
        <a href="panel.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Cancelar</a>
    </div>

    <div class="card shadow-sm" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body p-4">
            
            <form id="formEditarEquipo">
                <input type="hidden" name="id" value="<?= $equipo['id'] ?>">

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Nombre del Equipo / Modelo</label>
                        <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($equipo['nombre']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Número de Serie</label>
                        <input type="text" class="form-control" name="serie" value="<?= htmlspecialchars($equipo['serie']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Código de Barras <small class="text-muted">(Dejar vacío para usar Serie)</small></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" class="form-control" name="codigo_barras" value="<?= htmlspecialchars($equipo['codigo_barras']) ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Categoría</label>
                        <input type="text" 
                               id="inputCategoria"
                               class="form-control" 
                               name="categoria" 
                               list="listaCategorias" 
                               value="<?= htmlspecialchars($equipo['categoria']) ?>" 
                               placeholder="Selecciona o escribe..." 
                               required>
                        
                        <datalist id="listaCategorias">
                            <option value="Computo">
                            <option value="Impresion">
                            <option value="Redes">
                            <option value="Radios"> <option value="Perifericos">
                            <option value="Otros">
                            <?php 
                            if ($res_categorias) {
                                $res_categorias->data_seek(0);
                                while($cat = $res_categorias->fetch_assoc()): 
                                    if(!in_array($cat['categoria'], ['Computo','Impresion','Redes','Radios','Perifericos','Otros'])):
                            ?>
                                <option value="<?= htmlspecialchars($cat['categoria']) ?>">
                            <?php 
                                    endif;
                                endwhile; 
                            }
                            ?>
                        </datalist>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ubicación</label>
                        <input type="text" 
                               id="inputUbicacion"
                               class="form-control" 
                               name="ubicacion" 
                               list="listaUbicaciones" 
                               value="<?= htmlspecialchars($equipo['ubicacion']) ?>" 
                               placeholder="Selecciona o escribe..." 
                               required>
                        
                        <datalist id="listaUbicaciones">
                            <option value="Nula (Personal)">
                            <?php 
                            if ($res_ubicaciones) {
                                $res_ubicaciones->data_seek(0);
                                while($ub = $res_ubicaciones->fetch_assoc()): 
                            ?>
                                <option value="<?= htmlspecialchars($ub['ubicacion']) ?>">
                            <?php endwhile; } ?>
                        </datalist>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-warning w-100 py-2 fw-bold">
                            <i class="bi bi-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- 1. LÓGICA AUTOMÁTICA PARA RADIOS ---
    const categoriaInput = document.getElementById('inputCategoria');
    const ubicacionInput = document.getElementById('inputUbicacion');

    categoriaInput.addEventListener('input', function() {
        // Convertimos a minúsculas para detectar "Radio", "Radios", "RADIO", etc.
        const valor = this.value.toLowerCase();

        // Si la categoría contiene la palabra "radio"
        if (valor.includes('radio')) {
            ubicacionInput.value = 'Nula'; // O puedes poner 'Personal Operativo'
            
            // Opcional: Hacer el campo de solo lectura para que no lo cambien por error
            // ubicacionInput.readOnly = true; 
            // ubicacionInput.classList.add('bg-light'); // Darle un tono gris
        } else {
            // Si cambian de opinión y borran "Radio", liberamos el campo
            // ubicacionInput.readOnly = false;
            // ubicacionInput.classList.remove('bg-light');
            
            // Nota: No borramos el valor automáticamente por si era una ubicación válida antes
        }
    });


    // --- 2. LÓGICA DE GUARDADO AJAX ---
    document.getElementById('formEditarEquipo').addEventListener('submit', function(e) {
        e.preventDefault(); 
        let formData = new FormData(this);

        fetch('actualizar_equipo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) 
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Actualizado!',
                    text: 'Los cambios se guardaron correctamente.',
                    icon: 'success',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Volver al Panel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'panel.php';
                    }
                });
            } else {
                if (data.type === 'duplicate') {
                    Swal.fire({
                        title: '¡Código Duplicado!',
                        text: 'El Código de Barras o No. Serie ya está siendo usado por otro equipo.',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Ocurrió un problema de conexión con el servidor.', 'error');
        });
    });
</script>

</body>
</html>