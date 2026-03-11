<?php
// Reporte de errores activado por seguridad
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1. Verificación de seguridad
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'Tecnico' && $_SESSION['rol'] !== 'Administrador')) {
    header("Location: index.html");
    exit;
}

include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Recibir datos del formulario
    $nombre = $_POST['nombre'];
    $serie = $_POST['serie'];
    $codigo_barras = !empty($_POST['codigo_barras']) ? $_POST['codigo_barras'] : $serie; 
    $categoria = $_POST['categoria'];
    $ubicacion = $_POST['ubicacion'];

    // 3. Valores por defecto
    $estatus = 'sin falla';
    $problema = 'Alta inicial de equipo'; 
    $rutaImagen = ''; 

    // 4. Insertar en la tabla 'registros'
    // IMPORTANTE: Aquí usamos 'fecha_registro' porque ya arreglaste la base de datos
    $sql = "INSERT INTO registros (nombre, serie, codigo_barras, categoria, ubicacion, problema, estatus, imagen, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) { die("Error SQL: " . $conn->error); }
    
    $stmt->bind_param("ssssssss", $nombre, $serie, $codigo_barras, $categoria, $ubicacion, $problema, $estatus, $rutaImagen);

    if ($stmt->execute()) {
        
        // --- INTENTO DE GUARDAR EN HISTORIAL (BITÁCORA) ---
        // Lo ponemos en try-catch para que si falla el historial, no detenga la alerta de éxito
        try {
            $registro_id = $conn->insert_id; 
            $usuario_id = $_SESSION['user_id']; 
            $accion = "Registro Inicial";
            
            $sql_bitacora = "INSERT INTO bitacora (registro_id, usuario_id, accion, fecha) VALUES (?, ?, ?, NOW())";
            $stmt_b = $conn->prepare($sql_bitacora);
            if ($stmt_b) {
                $stmt_b->bind_param("iis", $registro_id, $usuario_id, $accion);
                $stmt_b->execute();
                $stmt_b->close();
            }
        } catch (Exception $e) {
            // Silencioso: Si falla la bitácora, el usuario no necesita saberlo ahora
        }

        // --- AQUÍ COMIENZA LA MAGIA DE SWEETALERT ---
        // Cerramos PHP un momento para escribir HTML limpio
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Guardando...</title>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <style> body { font-family: sans-serif; background-color: #f4f6f9; } </style>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: '¡Equipo Registrado!',
                    text: 'El equipo se ha guardado correctamente en la base de datos.',
                    icon: 'success',
                    confirmButtonText: 'Ir al Panel',
                    confirmButtonColor: '#3085d6',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redireccionar al panel cuando el usuario da click
                        window.location.href = 'panel.php';
                    }
                });
            </script>
        </body>
        </html>
        <?php
        // Terminamos el script aquí para que no se ejecute nada más
        exit;
        
    } else {
        // Si falla el INSERT principal
        echo "Error al guardar el registro: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: nuevo_equipo.php");
    exit;
}
?>