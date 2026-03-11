<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}
include "db.php";
$id = intval($_GET['id']);

// Obtener datos del equipo
$sql = "SELECT * FROM registros WHERE id=$id";
$res = $conn->query($sql);
$equipo = $res->fetch_assoc();

if (!$equipo) {
    header("Location: panel.php");
    exit;
}

// OBTENER HISTORIAL
$sql2 = "SELECT b.id as id_bitacora, b.fecha, u.username AS usuario, b.accion 
         FROM bitacora b
         INNER JOIN usuarios u ON b.usuario_id = u.id
         WHERE b.registro_id=$id ORDER BY b.fecha DESC";
$res2 = $conn->query($sql2);
$historial = [];
while ($row = $res2->fetch_assoc()) {
    $historial[] = $row;
}

$rol = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalles del Equipo - ControlVA</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css?v=<?php echo time(); ?>">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="panel.php">
        <img src="logo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2"> ControlVA
    </a>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
        <img src="<?= htmlspecialchars($_SESSION['foto_perfil'] ?: 'https://via.placeholder.com/32') ?>" alt="perfil" width="32" height="32" class="rounded-circle me-2">
        <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
      </a>
      <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
        <li><a class="dropdown-item" href="panel.php"><i class="bi bi-speedometer2 me-2"></i> Panel</a></li>
        <li><a class="dropdown-item text-danger" href="logout.php">Salir</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4 mb-5">
  
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="bi bi-laptop me-2"></i><?= htmlspecialchars($equipo['nombre']) ?></h4>
      <div>
        <?php if ($rol == "Tecnico" || $rol == "Administrador") : ?>
            <button class="btn btn-info btn-sm me-2" onclick="imprimirEtiqueta('<?= htmlspecialchars($equipo['codigo_barras'] ?: $equipo['serie']) ?>', '<?= htmlspecialchars($equipo['nombre']) ?>')">
                <i class="bi bi-printer"></i> Imprimir Etiqueta
            </button>
            <a href="editar_equipo.php?id=<?= $equipo['id'] ?>" class="btn btn-warning btn-sm me-2"><i class="bi bi-pencil"></i> Editar</a>
        <?php endif; ?>
        <a href="panel.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
      </div>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-8">
          <dl class="row">
            <dt class="col-sm-4">No. Serie:</dt><dd class="col-sm-8"><?= htmlspecialchars($equipo['serie']) ?></dd>
            <dt class="col-sm-4">Código Barras:</dt><dd class="col-sm-8"><?= htmlspecialchars($equipo['codigo_barras'] ?: 'N/A') ?></dd>
            <dt class="col-sm-4">Categoría:</dt><dd class="col-sm-8"><?= htmlspecialchars($equipo['categoria'] ?: 'N/A') ?></dd>
            <dt class="col-sm-4">Ubicación:</dt><dd class="col-sm-8"><?= htmlspecialchars($equipo['ubicacion'] ?: 'N/A') ?></dd>
            <dt class="col-sm-4">Estatus:</dt>
            <dd class="col-sm-8">
              <?php if ($equipo['estatus'] == "con falla") : ?><span class="badge bg-danger">Con Falla</span><?php else : ?><span class="badge bg-success">Sin Falla</span><?php endif; ?>
            </dd>
            <dt class="col-sm-4">Último Problema:</dt><dd class="col-sm-8"><p><?= nl2br(htmlspecialchars($equipo['problema'])) ?></p></dd>
          </dl>
          
          <?php if ($equipo['estatus'] == "sin falla") : ?>
          <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#reporteModal">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Reportar Nuevo Incidente
          </button>
          <?php endif; ?>
        </div>
        <div class="col-md-4 text-center">
          <?php if ($equipo['imagen']) : ?><img src="<?= htmlspecialchars($equipo['imagen']) ?>" class="img-fluid rounded border" style="max-height: 200px;" alt="Imagen"><?php else : ?><div class="text-muted p-4 border rounded"><i class="bi bi-image-alt fs-1"></i><p>Sin imagen</p></div><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#ultima">Última Solución</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#historial">Historial Completo</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#grafica">Gráfica</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="ultima">
              <?php if (!empty($equipo['solucion'])) : ?>
                <h5><i class="bi bi-tools me-2"></i> Última solución registrada</h5>
                <div class="row mt-3">
                    <div class="col-md-8">
                        <p class="p-3 bg-light border rounded"><?= nl2br(htmlspecialchars($equipo['solucion'])) ?></p>
                        <small class="text-muted"><i class="bi bi-info-circle"></i> Esta información es la del estado actual del equipo. Si borraste el historial, esto no se borra automáticamente.</small>
                    </div>
                    <div class="col-md-4">
                        <?php if ($equipo['imagen_solucion']) : ?><img src="<?= htmlspecialchars($equipo['imagen_solucion']) ?>" class="img-fluid rounded border" alt="Solución"><?php endif; ?>
                    </div>
                </div>
              <?php else : ?><p class="text-muted">No hay solución registrada actualmente.</p><?php endif; ?>
            </div>

            <div class="tab-pane fade" id="historial">
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Acción / Movimiento</th>
                            <?php if ($rol == 'Administrador'): ?>
                            <th class="text-end">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                        // --- FILTRO MEJORADO ---
                        // Usamos 'stripos' para buscar la palabra clave sin importar mayúsculas/minúsculas ni espacios exactos
                        $historialVisible = array_filter($historial, function($h) {
                            $accion = trim($h['accion']); // Quitamos espacios extra
                            // Si contiene "Registro Inicial", lo ocultamos
                            return stripos($accion, 'Registro Inicial') === false;
                        });
                    ?>

                    <?php if (count($historialVisible) > 0) : ?>
                        <?php foreach ($historialVisible as $h) : ?>
                            <tr>
                                <td><?= date("d/m/Y H:i", strtotime($h['fecha'])) ?></td>
                                <td><?= htmlspecialchars($h['usuario']) ?></td>
                                <td><?= htmlspecialchars($h['accion']) ?></td>
                                
                                <?php if ($rol == 'Administrador'): ?>
                                <td class="text-end">
                                    <button onclick="eliminarHistorial(<?= $h['id_bitacora'] ?>)" class="btn btn-danger btn-sm" title="Eliminar registro">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted p-4">
                            <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                            Aún no hay reparaciones o incidentes registrados en el historial.
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
              </div>
            </div>

            <div class="tab-pane fade" id="grafica">
                <canvas id="graficaCanvas" style="max-height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
  </div>

  <?php if ($rol == "Tecnico" || $rol == "Administrador") : ?>
    <div class="card shadow-sm mt-4">
      <div class="card-header"><h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i> Registrar Reparación / Actualización</h5></div>
      <div class="card-body">
        <form id="formReparacion" enctype="multipart/form-data">
          <div class="mb-3"><label class="form-label">Descripción de la solución:</label><textarea name="solucion" class="form-control" rows="3" required></textarea></div>
          <div class="mb-3"><label class="form-label">Imagen (Opcional):</label><input type="file" name="imagen_solucion" class="form-control" accept="image/*"></div>
          <button type="button" onclick="guardarReparacion(<?= $id ?>)" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>

<div class="modal fade" id="reporteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Reportar Incidente</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <form id="formReporteIncidente">
            <div class="mb-3"><label class="form-label">Problema:</label><textarea class="form-control" name="nuevo_problema" rows="3" required></textarea></div>
            <div class="mb-3"><label class="form-label">Imagen:</label><input class="form-control" type="file" name="nueva_imagen_problema" accept="image/*"></div>
        </form>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-danger" onclick="enviarReporte(<?= $id ?>)">Reportar Falla</button></div>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

document.addEventListener("DOMContentLoaded", function() {
    const historialData = <?= json_encode($historial) ?>;
    const conteoPorFecha = {};
    
    if (historialData.length > 0) {
        historialData.forEach(item => {
            // --- FILTRO JS ROBUSTO ---
            // Convertimos a minúsculas para comparar seguro
            let accion = item.accion.toLowerCase();
            if (accion.includes("registro inicial")) {
                return; 
            }
            // -------------------------

            const fecha = item.fecha.substring(0, 10);
            conteoPorFecha[fecha] = (conteoPorFecha[fecha] || 0) + 1;
        });

        const etiquetas = Object.keys(conteoPorFecha).sort();
        const datos = etiquetas.map(fecha => conteoPorFecha[fecha]);

        const ctx = document.getElementById('graficaCanvas');
        if (ctx) {
            new Chart(ctx, {
                type: 'line', 
                data: {
                    labels: etiquetas, 
                    datasets: [{
                        label: 'Intervenciones',
                        data: datos, 
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3, 
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
    }
});

function imprimirEtiqueta(codigo, nombre) {
    if (!codigo) { Swal.fire('Error', 'Sin código para imprimir.', 'error'); return; }
    window.open(`etiqueta_print.php?serie=${encodeURIComponent(codigo)}&nombre=${encodeURIComponent(nombre)}`, '_blank');
}

function guardarReparacion(id) {
  let form = document.getElementById("formReparacion");
  if(!form.checkValidity()){ form.reportValidity(); return; }
  
  let datos = new FormData(form);
  datos.append("accion", "guardarReparacion");
  datos.append("id", id);
  
  fetch("api.php", { method: "POST", body: datos }).then(res => res.json())
  .then(data => {
    if (data.success) { Swal.fire("Guardado", "Reparación registrada", "success").then(() => location.reload()); } 
    else { Swal.fire("Error", data.message, "error"); }
  });
}

function enviarReporte(id) {
    let form = document.getElementById("formReporteIncidente");
    if(!form.checkValidity()){ form.reportValidity(); return; }

    let datos = new FormData(form);
    datos.append("accion", "reportarIncidente");
    datos.append("id", id);
    fetch("api.php", { method: "POST", body: datos }).then(res => res.json())
    .then(data => {
        if (data.success) { Swal.fire("Reportado", "Incidente registrado", "success").then(() => location.reload()); }
        else { Swal.fire("Error", data.message, "error"); }
    });
}

function eliminarHistorial(idBitacora) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: "Solo debes borrarlo si fue un error. No podrás recuperarlo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, borrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            let datos = new FormData();
            datos.append("accion", "eliminarHistorial");
            datos.append("id_bitacora", idBitacora);
            
            fetch("api.php", { method: "POST", body: datos })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Eliminado', 'El registro ha sido eliminado.', 'success')
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No tienes permisos o ocurrió un error.', 'error');
                }
            });
        }
    })
}
</script>
</body>
</html>